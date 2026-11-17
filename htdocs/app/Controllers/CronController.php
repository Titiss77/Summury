<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\CronLogModel;
use App\Models\ItemModel;
use App\Models\WhitelistModel;
use Config\Services;

class CronController extends BaseController
{
    public function run()
    {
        $itemModel = new ItemModel();
        $cronLogModel = new CronLogModel();
        $whitelistModel = new WhitelistModel();

        // 1. Récupérer les domaines de confiance pour les ignorer
        $whitelistedDomains = $whitelistModel->findColumn('domain') ?? [];

        // 2. Limiter la requête pour éviter le Timeout de Byethost (Max 25 liens par scan)
        $items = $itemModel->where('link_status !=', 'dead')
                           ->where('lien IS NOT NULL')
                           ->where('lien !=', '')
                           ->orderBy('updated_at', 'ASC') // Traite les liens les plus anciens en premier
                           ->limit(25)
                           ->findAll();

        if (empty($items)) {
            return $this->response->setJSON(['status' => 'skipped', 'message' => 'Aucun lien à vérifier.']);
        }

        $client = Services::curlrequest([
            'timeout'         => 4,  // Délai court pour ne pas bloquer le script
            'connect_timeout' => 3,
            'http_errors'     => false,
            'allow_redirects' => ['max' => 2],
            'verify'          => false,
            'user_agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36'
        ]);

        $deadCount = 0;
        $urlsChecked = 0;

        foreach ($items as $item) {
            $parsed = parse_url($item->lien);
            $host = $parsed['host'] ?? '';

            // Ignorer si pas de domaine ou si domaine whitelisté
            if (empty($host) || in_array($host, $whitelistedDomains, true)) {
                // On met à jour la date pour ne pas boucler indéfiniment sur les mêmes
                $itemModel->builder()->where('id', $item->id)->set('updated_at', date('Y-m-d H:i:s'))->update();
                continue;
            }

            $urlsChecked++;
            $isDead = false;
            $statusCode = 0;

            try {
                // ETAPE 1 : Vérification DNS native (Non bloquée par le pare-feu HTTP Byethost)
                if (!checkdnsrr($host, "A") && !checkdnsrr($host, "CNAME")) {
                    $isDead = true;
                    $statusCode = 404;
                }

                // ETAPE 2 : Si DNS OK, vérification HTTP légère
                if (!$isDead) {
                    $urlToTest = ($parsed['scheme'] ?? 'https') . '://' . $host;
                    $httpResponse = $client->request('GET', $urlToTest, ['headers' => ['Range' => 'bytes=0-1024']]);
                    $statusCode = $httpResponse->getStatusCode();

                    // Code de pages introuvables ou erreurs serveurs (ex: Cloudflare 521/522/523)
                    if (in_array($statusCode, [404, 410, 521, 522, 523], true)) {
                        $isDead = true;
                    }
                }
            } catch (\Exception $e) {
                $errMsg = $e->getMessage();
                
                // DÉTECTION DU BLOCAGE PARE-FEU BYETHOST
                if (str_contains($errMsg, 'timed out') || str_contains($errMsg, 'Connection refused')) {
                    log_message('error', '[Cron] Blocage Byethost esquivé sur ' . $host . ' : ' . $errMsg);
                    // On met à jour updated_at pour reléguer ce lien à la fin de la file d'attente
                    $itemModel->builder()->where('id', $item->id)->set('updated_at', date('Y-m-d H:i:s'))->update();
                    continue; 
                }

                // Pour tout autre crash réseau inattendu, on considère le lien inaccessible
                $isDead = true;
                $statusCode = 0;
            }

            // Mettre à jour la base de données
            if ($isDead) {
                $itemModel->update($item->id, ['link_status' => 'dead']);
                
                $cronLogModel->insert([
                    'task_name'   => 'check_dead_links',
                    'last_run'    => date('Y-m-d H:i:s'),
                    'item_id'     => $item->id,
                    'titre'       => $item->titre,
                    'url_testee'  => ($parsed['scheme'] ?? 'https') . '://' . $host,
                    'code_erreur' => $statusCode,
                ]);
                
                $deadCount++;
            } else {
                // Le lien est sain, on actualise sa date pour qu'il reparte en fin de file
                $itemModel->builder()->where('id', $item->id)->set('updated_at', date('Y-m-d H:i:s'))->update();
            }
        }

        if ($deadCount > 0) {
            (new AuditLogModel())->logAction('Cron', "Vérification auto : {$deadCount} nouveaux liens morts détectés.");
        }

        // Réponse formatée pour être comprise par votre /assets/script.js
        return $this->response->setJSON([
            'status'      => 'executed',
            'total_cards' => count($items),
            'unique_urls' => $urlsChecked,
            'dead_count'  => $deadCount
        ]);
    }
}
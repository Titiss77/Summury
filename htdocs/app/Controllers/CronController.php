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

        // 2. Limiter la requête pour éviter le Timeout du serveur (Max 25 liens par scan)
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
            'timeout'         => 5,
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
                $itemModel->builder()->where('id', $item->id)->set('updated_at', date('Y-m-d H:i:s'))->update();
                continue;
            }

            $urlsChecked++;
            $isDead = false;
            $statusCode = 0;

            try {
                // ÉTAPE 1 : API DNS de Google (Contourne le DNS Hijacking local/Byethost)
                // Cela garantit de détecter les domaines réellement expirés (ex: .lol, .rip)
                $dnsResponse = $client->get("https://dns.google/resolve?name=" . urlencode($host) . "&type=A");
                
                if ($dnsResponse->getStatusCode() === 200) {
                    $dnsData = json_decode((string) $dnsResponse->getBody(), true);
                    
                    // Status 3 = NXDOMAIN (Le domaine n'existe plus de manière globale)
                    if (isset($dnsData['Status']) && $dnsData['Status'] === 3) {
                        $isDead = true;
                        $statusCode = 404;
                    }
                }

                // ÉTAPE 2 : Si le domaine existe (DNS OK), on tente la connexion HTTP légère
                if (!$isDead) {
                    $urlToTest = ($parsed['scheme'] ?? 'https') . '://' . $host;
                    $httpResponse = $client->request('GET', $urlToTest, ['headers' => ['Range' => 'bytes=0-1024']]);
                    $statusCode = $httpResponse->getStatusCode();

                    // Codes de pages introuvables ou d'erreurs serveurs définitives
                    if (in_array($statusCode, [404, 410, 521, 522, 523], true)) {
                        $isDead = true;
                    }
                }
            } catch (\Exception $e) {
                $errMsg = $e->getMessage();
                
                // DÉTECTION DU BLOCAGE PARE-FEU HÉBERGEUR (Byethost/iFastNet)
                // Si la requête HTTP est bloquée, on l'ignore pour éviter les faux positifs massifs
                if (str_contains($errMsg, 'timed out') || str_contains($errMsg, 'Connection refused')) {
                    log_message('error', '[Cron] Blocage réseau esquivé sur ' . $host . ' : ' . $errMsg);
                    
                    // Actualisation de la date pour le renvoyer en fin de file d'attente
                    $itemModel->builder()->where('id', $item->id)->set('updated_at', date('Y-m-d H:i:s'))->update();
                    continue; 
                }

                // Tout autre crash inattendu (hôte introuvable sans API Google) est considéré comme mort
                $isDead = true;
                $statusCode = 0;
            }

            // ÉTAPE 3 : Mise à jour de la base de données
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

        // Journalisation de l'audit
        if ($deadCount > 0) {
            (new AuditLogModel())->logAction('Cron', "Vérification auto : {$deadCount} nouveaux liens morts détectés.");
        }

        // Renvoi de la réponse JSON au format attendu par script.js
        return $this->response->setJSON([
            'status'      => 'executed',
            'total_cards' => count($items),
            'unique_urls' => $urlsChecked,
            'dead_count'  => $deadCount
        ]);
    }
}
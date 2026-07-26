<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\CronLogModel;
use App\Models\ItemModel;
use Config\Services;

class CronController extends BaseController
{
    public function run()
    {
        ini_set('max_execution_time', '0');

        $cronModel = new CronLogModel();
        $lastRunRow = $cronModel->orderBy('last_run', 'DESC')->first();

        $now = time();
        $shouldRun = false;
        $isForced = '1' === $this->request->getGet('force');

        if (!$lastRunRow) {
            $shouldRun = true;
        } else {
            $lastRunDate = strtotime($lastRunRow['last_run']);
            if (($now - $lastRunDate) >= 604800 || $isForced) {
                $shouldRun = true;
            }
        }

        if (!$shouldRun) {
            return $this->response->setJSON(['status' => 'skipped', 'message' => 'Délai de 7 jours non écoulé.']);
        }

        $cronModel->truncate();

        $itemModel = new ItemModel();
        $items = $itemModel->where('lien !=', '')->where('lien IS NOT NULL')->findAll();

        $client = Services::curlrequest([
            'timeout' => 10,
            'connect_timeout' => 5,  // NOUVEAU : Force l'arrêt rapide si le DNS est introuvable
            'http_errors' => false,
            'allow_redirects' => true,
            'verify' => false,  // NOUVEAU CRUCIAL : Ignore les erreurs HTTPS/SSL des sites morts
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'headers' => [
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7',
                'Upgrade-Insecure-Requests' => '1',
            ],
        ]);

        $deadCount = 0;
        $totalChecked = 0;
        $currentTimestamp = date('Y-m-d H:i:s');
        $deadLinksDetails = [];
        $checkedDomains = [];

        foreach ($items as $item) {
            // 1. Formatage du lien complet pour l'affichage (Lien avec épisodes et saisons remplacés)
            $ep = $item->episode ?: '1';
            $ep2 = str_pad((string) $ep, 2, '0', STR_PAD_LEFT);
            $s = $item->saison ?: '1';
            $s2 = str_pad((string) $s, 2, '0', STR_PAD_LEFT);

            $urlToTest = str_replace(
                ['{ep}', '{ep2}', '{s}', '{s2}'],
                [$ep, $ep2, $s, $s2],
                $item->lien
            );

            // 2. Extraction du domaine principal pour le test serveur (ex: https://sushiscan.net)
            $parsedUrl = parse_url($item->lien);

            if (!isset($parsedUrl['host'])) {
                continue;
            }

            $scheme = $parsedUrl['scheme'] ?? 'https';
            $domainToTest = $scheme.'://'.$parsedUrl['host'];

            ++$totalChecked;
            $statusCode = null;

            // 3. Vérification du domaine (avec cache pour ne pas tester 10 fois sushiscan.net)
            if (array_key_exists($domainToTest, $checkedDomains)) {
                $statusCode = $checkedDomains[$domainToTest];
            } else {
                try {
                    $response = $client->get($domainToTest);
                    $statusCode = $response->getStatusCode();
                } catch (\Throwable $e) {
                    // On attrape les crashs réseau profonds et on définit le code sur 0
                    $statusCode = 0;
                }
                $checkedDomains[$domainToTest] = $statusCode;
            }

            // 4. Si le DOMAINE est mort, on flague la carte et on enregistre son LIEN COMPLET
            if (0 === $statusCode || 404 === $statusCode) {
                $itemModel->update($item->id, ['link_status' => 'dead']);
                ++$deadCount;

                $cronModel->insert([
                    'task_name' => 'check_dead_links',
                    'last_run' => $currentTimestamp,
                    'item_id' => $item->id,
                    'titre' => $item->titre,
                    'url_testee' => $urlToTest,  // <-- CORRECTION : Affiche le vrai lien de la carte (ex: /chainsaw-man-chapitre-52)
                    'code_erreur' => $statusCode,
                ]);

                $deadLinksDetails[] = [
                    'id' => $item->id,
                    'titre' => $item->titre,
                    'url_testee' => $urlToTest,
                    'code_erreur' => $statusCode,
                ];
            } else {
                if ('dead' === $item->link_status) {
                    $itemModel->update($item->id, ['link_status' => 'ok']);
                }
            }
        }

        if (0 === $deadCount) {
            $cronModel->insert([
                'task_name' => 'check_dead_links',
                'last_run' => $currentTimestamp,
                'item_id' => null,
                'titre' => 'Aucun lien mort détecté',
                'url_testee' => null,
                'code_erreur' => 200,
            ]);
        }

        if ($deadCount > 0) {
            $audit = new AuditLogModel();
            $uniqueDomainsCount = count($checkedDomains);
            $message = $isForced ? 'Scan FORCÉ de liens' : 'Scan de liens en arrière-plan';
            $audit->logAction('Maintenance Système', "{$message} : {$uniqueDomainsCount} domaines uniques testés pour {$totalChecked} cartes. {$deadCount} carte(s) impactée(s).");
        }

        return $this->response->setJSON([
            'status' => 'executed',
            'forced' => $isForced,
            'total_cards' => $totalChecked,
            'unique_domains' => count($checkedDomains),
            'dead_count' => $deadCount,
            'dead_links' => $deadLinksDetails,
        ]);
    }
}

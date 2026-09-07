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
        $isForced = $this->request->getGet('force') == 1;

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
            'connect_timeout' => 5,
            'http_errors' => false,
            'allow_redirects' => true,
            // 'verify' => false a été supprimé pour détecter les sites non sécurisés
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
        $domainStatus = []; // Cache pour ne pas tester 100 fois un domaine totalement mort

        foreach ($items as $item) {
            $ep = '1';
            $ep2 = '01';
            $s = '1';
            $s2 = '01';
            $urlToTest = str_replace(
                ['{ep}', '{ep2}', '{s}', '{s2}'],
                [$ep, $ep2, $s, $s2],
                $item->lien
            );

            $parsedUrl = parse_url($urlToTest);
            if (!isset($parsedUrl['host'])) {
                continue;
            }
            $domain = $parsedUrl['host'];

            ++$totalChecked;
            $statusCode = null;
            $isDead = false;

            // Si le domaine a déjà renvoyé une erreur réseau critique (ex: DNS crash)
            if (isset($domainStatus[$domain]) && $domainStatus[$domain] === 'dead') {
                $isDead = true;
                $statusCode = 0;
            } else {
                try {
                    $response = $client->get($urlToTest);
                    $statusCode = $response->getStatusCode();
                    
                    // On inclut les erreurs 404 ET les erreurs serveurs (500+)
                    if ($statusCode === 404 || $statusCode >= 500) {
                        $isDead = true;
                    } else {
                        $domainStatus[$domain] = 'ok';
                    }
                } catch (\Throwable $e) {
                    // CATCH : Attrape les erreurs DNS (NXDOMAIN), Timeouts et certificats SSL expirés
                    $isDead = true;
                    $statusCode = 0;
                    $domainStatus[$domain] = 'dead';
                }
            }

            if ($isDead) {
                $itemModel->update($item->id, ['link_status' => 'dead']);
                ++$deadCount;
                $cronModel->insert([
                    'task_name' => 'check_dead_links',
                    'last_run' => $currentTimestamp,
                    'item_id' => $item->id,
                    'titre' => $item->titre,
                    'url_testee' => $urlToTest,
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
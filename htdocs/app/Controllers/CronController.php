<?php declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\ExternalUrlGuard;
use App\Models\AuditLogModel;
use App\Models\CronLogModel;
use App\Models\ItemModel;

class CronController extends BaseController
{
    public function run()
    {
        ini_set('max_execution_time', '0');
        $cronModel = new CronLogModel();

        $isForced = '1' === $this->request->getGet('force');
        if ($isForced && (!auth()->loggedIn() || !auth()->user()->inGroup('admin', 'superadmin'))) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'forbidden',
                'message' => 'Seuls les administrateurs peuvent forcer cette vérification.',
            ]);
        }

        $lockPath = WRITEPATH . 'cache' . DIRECTORY_SEPARATOR . 'check_dead_links.lock';
        $lockHandle = @fopen($lockPath, 'c');
        if (false === $lockHandle) {
            return $this->response->setStatusCode(503)->setJSON([
                'status' => 'error',
                'message' => 'Impossible de créer le verrou du cron.',
            ]);
        }

        if (!flock($lockHandle, LOCK_EX | LOCK_NB)) {
            fclose($lockHandle);
            return $this->response->setJSON(['status' => 'running', 'message' => 'La vérification est déjà en cours.']);
        }

        $lastRunRow = $cronModel->where('task_name', 'check_dead_links')->orderBy('last_run', 'DESC')->first();
        $now = time();
        $shouldRun = false;

        if (!$lastRunRow) {
            $shouldRun = true;
        } else {
            $lastRunDate = strtotime($lastRunRow['last_run']);
            if (($now - $lastRunDate) >= 604800 || $isForced) {
                $shouldRun = true;
            }
        }

        if (!$shouldRun) {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
            return $this->response->setJSON(['status' => 'skipped', 'message' => 'Délai de 7 jours non écoulé.']);
        }

        // Nettoyage des logs de la session précédente
        (new CronLogModel())->where('task_name', 'check_dead_links')->delete();

        $itemModel = new ItemModel();
        $items = $itemModel->where('lien !=', '')->where('lien IS NOT NULL')->findAll();

        $deadCount = 0;
        $totalChecked = 0;
        $currentTimestamp = date('Y-m-d H:i:s');

        $domainsToProcess = [];
        $whitelistedDomains = [];

        // Liste blanche des plateformes officielles qui bloquent cURL ou redirigent vers une page de connexion
        $whitelist = [
            'netflix.com', 'primevideo.com', 'canalplus.com', 'disneyplus.com',
            'crunchyroll.com', 'adn.com', 'youtube.com', 'apple.com', 'paramountplus.com'
        ];

        // 1. Regrouper les cartes par DOMAINE RACINE
        foreach ($items as $item) {
            $rawLien = trim($item->lien);
            if (!preg_match('#^https?://#i', $rawLien)) {
                $rawLien = 'https://' . $rawLien;
            }

            $parsed = parse_url($rawLien);
            $scheme = $parsed['scheme'] ?? 'https';
            $host = $parsed['host'] ?? '';

            if (empty($host) || !ExternalUrlGuard::isPublicHttpUrl($scheme . '://' . $host)) {
                continue;
            }

            $domainToTest = $scheme . '://' . $host;

            $isWhitelisted = false;
            foreach ($whitelist as $wl) {
                if ($host === $wl || str_ends_with($host, '.' . $wl)) {
                    $isWhitelisted = true;
                    break;
                }
            }

            if ($isWhitelisted) {
                $whitelistedDomains[$domainToTest][] = $item;
            } else {
                $domainsToProcess[$domainToTest][] = $item;
            }
            $totalChecked++;
        }

        // Restauration automatique des liens sur liste blanche
        foreach ($whitelistedDomains as $url => $itemsList) {
            foreach ($itemsList as $item) {
                if ($item->link_status === 'dead') {
                    $itemModel->update($item->id, ['link_status' => 'ok']);
                }
            }
        }

        $checkedDomains = [];
        $batchSize = 25;
        $domainChunks = array_chunk(array_keys($domainsToProcess), $batchSize);

        // 2. Traitement asynchrone par lots (Batch processing)
        foreach ($domainChunks as $chunk) {
            $mh = curl_multi_init();
            $curlHandles = [];

            foreach ($chunk as $url) {
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS => 3,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    CURLOPT_NOBODY => true,
                ]);
                curl_multi_add_handle($mh, $ch);
                $curlHandles[$url] = $ch;
            }

            $active = null;
            do {
                $mrc = curl_multi_exec($mh, $active);
                if ($active) {
                    curl_multi_select($mh);
                }
            } while ($active && $mrc == CURLM_OK);

            foreach ($curlHandles as $url => $ch) {
                $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if ($statusCode === 0 || $statusCode >= 400) {
                    curl_setopt($ch, CURLOPT_NOBODY, false);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Range: bytes=0-2048']);
                    $statusCode = curl_exec($ch) !== false ? curl_getinfo($ch, CURLINFO_HTTP_CODE) : 0;
                }

                $checkedDomains[$url] = $statusCode;

                $isDead = in_array($statusCode, [0, 404, 410, 521, 522, 523], true);

                foreach ($domainsToProcess[$url] as $item) {
                    if ($isDead) {
                        $itemModel->update($item->id, ['link_status' => 'dead']);
                        $deadCount++;
                        $cronModel->insert([
                            'task_name' => 'check_dead_links',
                            'last_run' => $currentTimestamp,
                            'item_id' => $item->id,
                            'titre' => $item->titre,
                            'url_testee' => $url,
                            'code_erreur' => $statusCode,
                        ]);
                    } elseif ($item->link_status === 'dead') {
                        $itemModel->update($item->id, ['link_status' => 'ok']);
                    }
                }

                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);
            }
            curl_multi_close($mh);
        }

        // 3. Clôture et rapports
        if ($deadCount === 0) {
            $cronModel->insert([
                'task_name' => 'check_dead_links',
                'last_run' => $currentTimestamp,
                'code_erreur' => 200,
            ]);
        } else {
            $audit = new AuditLogModel();
            $uniqueDomainsCount = count($checkedDomains);
            $message = $isForced ? 'Scan FORCÉ de domaines' : 'Scan de domaines en arrière-plan';
            $audit->logAction('Maintenance Système', "{$message} : {$uniqueDomainsCount} domaine(s) unique(s) testé(s). {$deadCount} carte(s) impactée(s).");
        }

        $result = $this->response->setJSON([
            'status' => 'executed',
            'forced' => $isForced,
            'total_cards' => $totalChecked,
            'unique_urls' => count($checkedDomains),
            'dead_count' => $deadCount,
        ]);

        flock($lockHandle, LOCK_UN);
        fclose($lockHandle);

        return $result;
    }
}
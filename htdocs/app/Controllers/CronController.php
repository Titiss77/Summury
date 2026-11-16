<?php declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\ExternalUrlGuard;
use App\Models\AuditLogModel;
use App\Models\CronLogModel;
use App\Models\ItemModel;
use Config\Services;

class CronController extends BaseController
{
    public function run()
    {
        @ini_set('max_execution_time', '120');

        $cronModel = new CronLogModel();

        $isForced = '1' === $this->request->getGet('force');
        if ($isForced && (!auth()->loggedIn() || !auth()->user()->inGroup('admin', 'superadmin'))) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'forbidden',
                'message' => 'Seuls les administrateurs peuvent forcer cette vérification.',
            ]);
        }

        $lockKey = 'cron_dead_links_lock';
        if (cache($lockKey)) {
            return $this->response->setJSON(['status' => 'running', 'message' => 'La vérification est déjà en cours.']);
        }

        cache()->save($lockKey, true, 180);

        try {
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
                cache()->delete($lockKey);
                return $this->response->setJSON(['status' => 'skipped', 'message' => 'Délai de 7 jours non écoulé.']);
            }

            (new CronLogModel())->where('task_name', 'check_dead_links')->delete();

            $itemModel = new ItemModel();
            $items = $itemModel->where('lien !=', '')->where('lien IS NOT NULL')->findAll();

            $deadCount = 0;
            $totalChecked = 0;
            $currentTimestamp = date('Y-m-d H:i:s');

            $domainsToProcess = [];
            $whitelistedDomains = [];

            $whitelist = [
                'netflix.com', 'primevideo.com', 'canalplus.com', 'disneyplus.com',
                'crunchyroll.com', 'adn.com', 'youtube.com', 'apple.com', 'paramountplus.com'
            ];

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

            foreach ($whitelistedDomains as $url => $itemsList) {
                foreach ($itemsList as $item) {
                    if ($item->link_status === 'dead') {
                        $itemModel->update($item->id, ['link_status' => 'ok']);
                    }
                }
            }

            $checkedDomains = [];

            // Client natif CodeIgniter
            $client = Services::curlrequest([
                'timeout' => 6,
                'connect_timeout' => 3,
                'http_errors' => false,
                'allow_redirects' => true,  // CI gère mieux les redirections ainsi
                'verify' => false,  // Équivalent de CURLOPT_SSL_VERIFYPEER => false
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]);

            // Traitement séquentiel rapide
            // Traitement séquentiel rapide
            foreach ($domainsToProcess as $url => $itemsList) {
                $parsed = parse_url($url);
                $host = $parsed['host'] ?? '';

                $isDead = false;
                $statusCode = 0;

                // ÉTAPE 1 : Vérification DNS (NXDOMAIN) - Exactement comme sur ton image
                // La fonction native checkdnsrr() est super rapide pour un Cron
                if (!empty($host) && !checkdnsrr($host, 'A') && !checkdnsrr($host, 'CNAME')) {
                    $isDead = true;
                    $statusCode = 404;  // On force 404 (ou 0) car le domaine a disparu
                } else {
                    // ÉTAPE 2 : Si le domaine existe, on fait le test HTTP classique
                    try {
                        $response = $client->request('HEAD', $url);
                        $statusCode = $response->getStatusCode();

                        if ($statusCode === 0 || $statusCode >= 400) {
                            $response = $client->request('GET', $url);
                            $statusCode = $response->getStatusCode();
                        }
                    } catch (\Exception $e) {
                        $statusCode = 0;  // Timeout ou blocage Byethost/Cloudflare
                    }

                    // On ne considère mort que les vraies erreurs HTTP (404, 410).
                    // On retire le 0 de cette liste pour éviter les faux positifs de Byethost !
                    if (in_array($statusCode, [404, 410, 521, 522, 523], true)) {
                        $isDead = true;
                    }
                }

                $checkedDomains[$url] = $statusCode;

                // Application aux cartes
                foreach ($itemsList as $item) {
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
            }

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

            return $result;
        } catch (\Throwable $e) {
            log_message('error', 'Cron Error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ]);
        } finally {
            cache()->delete($lockKey);
        }
    }
}

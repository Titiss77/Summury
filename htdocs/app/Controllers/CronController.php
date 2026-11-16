<?php declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\ExternalUrlGuard;
use App\Models\AuditLogModel;
use App\Models\CronLogModel;
use App\Models\ItemModel;
use App\Models\WhitelistModel;
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
            
            // Récupération dynamique de la whitelist depuis la base de données
            $whitelistModel = new WhitelistModel();
            $whitelistEntries = $whitelistModel->findAll();
            $whitelist = array_column($whitelistEntries, 'domain');
            
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
                'timeout'         => 6,
                'connect_timeout' => 3,
                'http_errors'     => false,
                'allow_redirects' => ['max' => 2, 'strict' => true],
                'verify'          => false,
                'user_agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]);

            // Traitement séquentiel avec résolution DNS Google
            foreach ($domainsToProcess as $url => $itemsList) {
                $parsed = parse_url($url);
                $host = $parsed['host'] ?? '';
                
                $isDead = false;
                $statusCode = 0;

                // ÉTAPE 1 : Résolution DNS fiable via Google (Contourne le cache menteur de Byethost)
                try {
                    $dnsResp = $client->get("https://dns.google/resolve?name=" . urlencode($host) . "&type=A");
                    if ($dnsResp->getStatusCode() === 200) {
                        $dnsData = json_decode((string)$dnsResp->getBody(), true);
                        
                        // Status 3 = NXDOMAIN (Le domaine n'existe plus sur internet)
                        if (isset($dnsData['Status']) && $dnsData['Status'] === 3) {
                            $isDead = true;
                            $statusCode = 404;
                        }
                    }
                } catch (\Exception $e) {
                    // Fallback silencieux en cas d'erreur de l'API Google
                }

                // ÉTAPE 2 : Test HTTP (Uniquement si le domaine existe toujours)
                if (!$isDead) {
                    try {
                        // Test rapide avec HEAD
                        $response = $client->request('HEAD', $url);
                        $statusCode = $response->getStatusCode();
                        
                        if ($statusCode === 0 || $statusCode >= 400) {
                            // Fallback avec GET si HEAD est refusé
                            $response = $client->request('GET', $url);
                            $statusCode = $response->getStatusCode();
                        }
                    } catch (\Exception $e) {
                        $statusCode = 0; // Timeout ou erreur fatale de connexion
                    }

                    // Seules les vraies erreurs fatales sont classées comme mortes (exclut le 0 timeout)
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
                            'task_name'   => 'check_dead_links',
                            'last_run'    => $currentTimestamp,
                            'item_id'     => $item->id,
                            'titre'       => $item->titre,
                            'url_testee'  => $url, 
                            'code_erreur' => $statusCode,
                        ]);
                    } elseif ($item->link_status === 'dead') {
                        $itemModel->update($item->id, ['link_status' => 'ok']);
                    }
                }
            }

            if ($deadCount === 0) {
                $cronModel->insert([
                    'task_name'   => 'check_dead_links',
                    'last_run'    => $currentTimestamp,
                    'code_erreur' => 200,
                ]);
            } else {
                $audit = new AuditLogModel();
                $uniqueDomainsCount = count($checkedDomains);
                $message = $isForced ? 'Scan FORCÉ de domaines' : 'Scan de domaines en arrière-plan';
                $audit->logAction('Maintenance Système', "{$message} : {$uniqueDomainsCount} domaine(s) unique(s) testé(s). {$deadCount} carte(s) impactée(s).");
            }

            $result = $this->response->setJSON([
                'status'      => 'executed',
                'forced'      => $isForced,
                'total_cards' => $totalChecked,
                'unique_urls' => count($checkedDomains),
                'dead_count'  => $deadCount,
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
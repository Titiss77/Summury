<?php declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\ExternalUrlGuard;
use App\Models\AuditLogModel;
use App\Models\CronLogModel;
use App\Models\ItemModel;
use Config\Services;

class CronController extends BaseController
{
    /**
     * Exécute la vérification des liens morts en arrière-plan.
     */
    public function run()
    {
        // Cette tâche peut parcourir de nombreux liens distincts.
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

        // Vérifie si le délai de 7 jours s'est écoulé ou si l'exécution est forcée
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

        (new CronLogModel())->where('task_name', 'check_dead_links')->delete();

        $itemModel = new ItemModel();
        $items = $itemModel->where('lien !=', '')->where('lien IS NOT NULL')->findAll();

        // Configuration du client HTTP pour le scraping
        $client = Services::curlrequest([
            'timeout' => 5,
            'connect_timeout' => 2,
            'http_errors' => false,
            'allow_redirects' => false,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'headers' => [
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ],
        ]);

        // Remplacement du bloc "Parcours de toutes les cartes..." dans CronController.php
        $deadCount = 0;
        $totalChecked = 0;
        $currentTimestamp = date('Y-m-d H:i:s');
        $checkedUrls = [];
        $urlsToProcess = [];

        // 1. Préparation des URLs uniques
        foreach ($items as $item) {
            $ep = $item->episode ?: '1';
            $ep2 = str_pad((string) $ep, 2, '0', STR_PAD_LEFT);
            $s = $item->saison ?: '1';
            $s2 = str_pad((string) $s, 2, '0', STR_PAD_LEFT);
            $urlToTest = str_replace(['{ep}', '{ep2}', '{s}', '{s2}'], [$ep, $ep2, $s, $s2], $item->lien);

            if (!preg_match('#^https?://#i', $urlToTest)) {
                $urlToTest = 'https://' . $urlToTest;
            }

            if (\App\Libraries\ExternalUrlGuard::isPublicHttpUrl($urlToTest)) {
                $urlsToProcess[$urlToTest][] = $item;  // Regrouper les items par URL identique
                $totalChecked++;
            }
        }

        // 2. Traitement asynchrone par lots (Batch processing)
        $batchSize = 25;  // Nombre de requêtes simultanées
        $urlChunks = array_chunk(array_keys($urlsToProcess), $batchSize);

        foreach ($urlChunks as $chunk) {
            $mh = curl_multi_init();
            $curlHandles = [];

            // Initialisation des requêtes du lot
            foreach ($chunk as $url) {
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_NOBODY => true,  // Équivalent d'une requête HEAD (ne télécharge pas le body)
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 5,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
                ]);
                curl_multi_add_handle($mh, $ch);
                $curlHandles[$url] = $ch;
            }

            // Exécution simultanée
            $active = null;
            do {
                $mrc = curl_multi_exec($mh, $active);
                if ($active) {
                    curl_multi_select($mh);
                }
            } while ($active && $mrc == CURLM_OK);

            // Récupération des résultats
            foreach ($curlHandles as $url => $ch) {
                $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                // Fallback GET si HEAD est bloqué (403, 405, 501)
                if (in_array($statusCode, [403, 405, 501])) {
                    curl_setopt($ch, CURLOPT_NOBODY, false);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Range: bytes=0-0']);
                    $statusCode = curl_exec($ch) !== false ? curl_getinfo($ch, CURLINFO_HTTP_CODE) : 0;
                }

                $checkedUrls[$url] = $statusCode;

                // Mise à jour de la base de données pour chaque item lié à cette URL
                $isDead = in_array($statusCode, [0, 404, 410], true);
                foreach ($urlsToProcess[$url] as $item) {
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

        // Enregistrement d'un log global si aucun lien mort n'a été trouvé
        if (0 === $deadCount) {
            $cronModel->insert([
                'task_name' => 'check_dead_links',
                'last_run' => $currentTimestamp,
                'code_erreur' => 200,
            ]);
        }

        if ($deadCount > 0) {
            $audit = new AuditLogModel();
            $uniqueUrlsCount = count($checkedUrls);
            $message = $isForced ? 'Scan FORCÉ de liens' : 'Scan de liens en arrière-plan';
            $audit->logAction('Maintenance Système', "{$message} : {$uniqueUrlsCount} URL(s) uniques testées. {$deadCount} carte(s) impactée(s).");
        }

        $result = $this->response->setJSON([
            'status' => 'executed',
            'forced' => $isForced,
            'total_cards' => $totalChecked,
            'unique_urls' => count($checkedUrls),
            'dead_count' => $deadCount,
        ]);

        flock($lockHandle, LOCK_UN);
        fclose($lockHandle);

        return $result;
    }
}
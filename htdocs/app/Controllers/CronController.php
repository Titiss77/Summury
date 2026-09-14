<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\CronLogModel;
use App\Models\ItemModel;
use App\Models\YoutubeChannelModel;
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
            'connect_timeout' => 5,
            'http_errors' => false,
            'allow_redirects' => true,
            'verify' => false,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'headers' => [
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ],
        ]);

        $deadCount = 0;
        $totalChecked = 0;
        $currentTimestamp = date('Y-m-d H:i:s');
        $deadLinksDetails = [];
        $checkedDomains = [];

        foreach ($items as $item) {
            $ep = $item->episode ?: '1';
            $ep2 = str_pad((string) $ep, 2, '0', STR_PAD_LEFT);
            $s = $item->saison ?: '1';
            $s2 = str_pad((string) $s, 2, '0', STR_PAD_LEFT);
            $urlToTest = str_replace(['{ep}', '{ep2}', '{s}', '{s2}'], [$ep, $ep2, $s, $s2], $item->lien);

            $parsedUrl = parse_url($item->lien);
            if (!isset($parsedUrl['host'])) {
                continue;
            }

            $scheme = $parsedUrl['scheme'] ?? 'https';
            $domainToTest = $scheme.'://'.$parsedUrl['host'];
            ++$totalChecked;
            $statusCode = null;

            if (array_key_exists($domainToTest, $checkedDomains)) {
                $statusCode = $checkedDomains[$domainToTest];
            } else {
                try {
                    $response = $client->get($domainToTest);
                    $statusCode = $response->getStatusCode();
                } catch (\Throwable $e) {
                    $statusCode = 0;
                }
                $checkedDomains[$domainToTest] = $statusCode;
            }

            if (0 === $statusCode || 404 === $statusCode) {
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
                'code_erreur' => 200,
            ]);
        }

        if ($deadCount > 0) {
            $audit = new AuditLogModel();
            $uniqueDomainsCount = count($checkedDomains);
            $message = $isForced ? 'Scan FORCÉ de liens' : 'Scan de liens en arrière-plan';
            $audit->logAction('Maintenance Système', "{$message} : {$uniqueDomainsCount} domaines uniques testés. {$deadCount} carte(s) impactée(s).");
        }

        return $this->response->setJSON([
            'status' => 'executed',
            'forced' => $isForced,
            'total_cards' => $totalChecked,
            'dead_count' => $deadCount
        ]);
    }

    public function youtube()
    {
        ini_set('max_execution_time', '0');
        $channelModel = new \App\Models\YoutubeChannelModel();
        $itemModel = new \App\Models\ItemModel();
        $channels = $channelModel->findAll();
        
        $client = \Config\Services::curlrequest([
            'timeout' => 10,
            'verify' => false,
        ]);

        $addedCount = 0;

        foreach ($channels as $channel) {
            $url = 'https://www.youtube.com/feeds/videos.xml?channel_id=' . $channel['channel_id'];
            
            try {
                $response = $client->get($url, ['http_errors' => false]);
                if ($response->getStatusCode() === 200) {
                    $xml = simplexml_load_string($response->getBody());
                    
                    if ($xml && isset($xml->entry[0])) {
                        $latestVideo = $xml->entry[0];
                        $videoId = str_replace('yt:video:', '', (string)$latestVideo->id);
                        
                        // Si une nouvelle vidéo est détectée dans le flux
                        if ($channel['last_video_id'] !== $videoId) {
                            
                            // --- ASTUCE ANTI-SHORTS ---
                            // On fait une requête HEAD (très légère) sans suivre les redirections
                            $shortCheck = $client->request('HEAD', 'https://www.youtube.com/shorts/' . $videoId, [
                                'http_errors' => false,
                                'allow_redirects' => false
                            ]);

                            // Si YouTube redirige (Code différent de 200), c'est une vidéo classique !
                            if ($shortCheck->getStatusCode() !== 200) {
                                $videoLink = 'https://www.youtube.com/watch?v=' . $videoId;
                                $existing = $itemModel->where('lien', $videoLink)->first();
                                
                                // Si la carte n'existe pas déjà sur le site
                                if (!$existing) {
                                    $itemModel->insert([
                                        'id_user' => $channel['user_id'],
                                        'id_division' => 5, // 5 = Correspond à la division Vidéos
                                        'sous_categorie' => $channel['channel_name'], 
                                        'titre' => (string)$latestVideo->title,
                                        'status' => 'À voir',
                                        'is_public' => 0, 
                                        'description' => 'Sortie récente de ' . $channel['channel_name'],
                                        'image' => 'https://img.youtube.com/vi/' . $videoId . '/mqdefault.jpg',
                                        'lien' => $videoLink,
                                        'link_status' => 'ok',
                                        'position' => 0
                                    ]);
                                    $addedCount++;
                                }
                            }
                            
                            // On met à jour la mémoire du cron pour ne pas reboucler indéfiniment
                            // (On le fait même si c'était un Short, pour dire "j'ai vu cette nouveauté, on passe")
                            $channelModel->update($channel['id'], ['last_video_id' => $videoId]);
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Ignore les échecs silencieusement pour passer à la chaîne suivante
                continue; 
            }
        }

        if ($addedCount > 0) {
            (new \App\Models\AuditLogModel())->logAction('CRON YouTube', "{$addedCount} nouvelle(s) vidéo(s) classique(s) ajoutée(s) depuis le flux RSS.");
        }

        return $this->response->setJSON(['status' => 'executed', 'videos_added' => $addedCount]);
    }
}
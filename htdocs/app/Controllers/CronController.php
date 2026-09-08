<?php declare(strict_types=1);

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
            'connect_timeout' => 5,
            'http_errors' => false,
            'allow_redirects' => true,
            'verify' => false,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko)',
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
            $ep = $item->episode ?: '1';
            $ep2 = str_pad((string) $ep, 2, '0', STR_PAD_LEFT);
            $s = $item->saison ?: '1';
            $s2 = str_pad((string) $s, 2, '0', STR_PAD_LEFT);
            $urlToTest = str_replace(
                ['{ep}', '{ep2}', '{s}', '{s2}'],
                [$ep, $ep2, $s, $s2],
                $item->lien
            );
            
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
                'item_id' => null,
                'titre' => 'Aucun lien mort détecté',
                'url_testee' => null,
                'code_erreur' => 200,
            ]);
        }

        // ============================================
        // AJOUT DES AUTOMATISATIONS 
        // ============================================
        $automationModel = new \App\Models\AutomationModel();
        $automations = $automationModel->findAll();
        $newCardsCount = 0;

        foreach ($automations as $auto) {
            
            // --- AUTOMATISATION YOUTUBE & RSS ---
            if ($auto['type'] === 'youtube' || $auto['type'] === 'rss') {
                try {
                    $rss = @simplexml_load_file($auto['source_url']);
                    if ($rss) {
                        $latestItem = null;
                        $link = '';
                        $title = '';

                        if (isset($rss->channel->item[0])) {
                            $latestItem = $rss->channel->item[0];
                            $link = (string)$latestItem->link;
                            $title = (string)$latestItem->title;
                        } elseif (isset($rss->entry[0])) {
                            $latestItem = $rss->entry[0];
                            $link = (string)$latestItem->link['href'];
                            $title = (string)$latestItem->title;
                        }

                        if ($link && $link !== $auto['last_item_id']) {
                            $itemData = [
                                'id_user' => $auto['id_user'],
                                'id_division' => $auto['id_division'],
                                'sous_categorie' => $auto['sous_categorie'],
                                'titre' => $title,
                                'lien' => $link,
                                'status' => 'À voir',
                                'is_public' => 0,
                                'description' => 'Ajout automatique (' . strtoupper($auto['type']) . ')',
                                'position' => 0
                            ];
                            $itemModel->insert($itemData);
                            $newCardsCount++;
                            $automationModel->update($auto['id'], ['last_item_id' => $link]);
                        }
                    }
                } catch (\Exception $e) {
                    // Ignore silentieusement
                }
            } 
            
            // --- AUTOMATISATION SURVEILLANCE DE SAISON ---
            elseif ($auto['type'] === 'next_season' && !empty($auto['last_item_id'])) {
                $targetUrl = $auto['last_item_id'];
                
                try {
                    $response = $client->get($targetUrl);
                    $statusCode = $response->getStatusCode();
                    $html = (string) $response->getBody();

                    // Vérification anti faux-positifs (pages qui retournent 200 OK mais affichent "Erreur 404")
                    $is404 = false;
                    $badWords = ['page not found', '404', 'introuvable', 'n\'existe pas', 'aucun résultat'];
                    foreach ($badWords as $word) {
                        if (stripos($html, $word) !== false) {
                            $is404 = true; 
                            break;
                        }
                    }

                    if ($statusCode === 200 && !$is404) {
                        // On essaie d'extraire le vrai titre de la page pour la carte
                        $titre = "Nouvelle saison disponible !";
                        if (preg_match('/<title>(.*?)<\/title>/i', $html, $matchesTitle)) {
                            // On nettoie un peu le titre des trucs parasites
                            $titre = trim(str_replace(['Voir', 'Streaming', 'VF', 'VOSTFR', 'Gratuit'], '', $matchesTitle[1]));
                        }

                        $itemData = [
                            'id_user' => $auto['id_user'],
                            'id_division' => $auto['id_division'],
                            'sous_categorie' => $auto['sous_categorie'],
                            'titre' => $titre,
                            'lien' => $targetUrl,
                            'status' => 'À voir',
                            'is_public' => 0,
                            'description' => 'Détecté automatiquement ! La nouvelle saison est sortie sur votre site.',
                            'position' => 0
                        ];
                        $itemModel->insert($itemData);
                        $newCardsCount++;

                        // Le travail est fait, on supprime cette règle !
                        $automationModel->delete($auto['id']);
                    }
                } catch (\Exception $e) {
                    // Timeout ou 404 pure, on ne fait rien, la saison n'est juste pas encore sortie !
                }
            }
        }
        // ============================================

        if ($deadCount > 0 || $newCardsCount > 0) {
            $audit = new AuditLogModel();
            $message = $isForced ? 'Scan FORCÉ' : 'Scan en arrière-plan';
            $audit->logAction('Maintenance Système', "{$message} : {$deadCount} lien(s) mort(s). {$newCardsCount} carte(s) générée(s) via automatisation.");
        }
        
        return $this->response->setJSON([
            'status' => 'executed',
            'forced' => $isForced,
            'total_cards' => $totalChecked,
            'new_automations' => $newCardsCount,
            'dead_count' => $deadCount,
            'dead_links' => $deadLinksDetails,
        ]);
    }
}
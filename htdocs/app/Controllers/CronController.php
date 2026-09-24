<?php declare(strict_types=1);

namespace App\Controllers;

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
        ini_set('max_execution_time', '0');

        $cronModel = new CronLogModel();
        $lastRunRow = $cronModel->orderBy('last_run', 'DESC')->first();
        $now = time();
        $shouldRun = false;
        
        $isForced = '1' === $this->request->getGet('force');

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
            return $this->response->setJSON(['status' => 'skipped', 'message' => 'Délai de 7 jours non écoulé.']);
        }

        $cronModel->truncate();

        $itemModel = new ItemModel();
        $items = $itemModel->where('lien !=', '')->where('lien IS NOT NULL')->findAll();

        // Configuration du client HTTP pour le scraping
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

        // Parcours de toutes les cartes contenant un lien
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

            // Système de cache par domaine pour éviter de requêter le même domaine en boucle
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

            // Marquage de la carte si le lien est identifié comme mort
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
                // Rétablissement du statut si le lien refonctionne
                if ('dead' === $item->link_status) {
                    $itemModel->update($item->id, ['link_status' => 'ok']);
                }
            }
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
            $uniqueDomainsCount = count($checkedDomains);
            $message = $isForced ? 'Scan FORCÉ de liens' : 'Scan de liens en arrière-plan';
            $audit->logAction('Maintenance Système', "{$message} : {$uniqueDomainsCount} domaines uniques testés. {$deadCount} carte(s) impactée(s).");
        }

        return $this->response->setJSON([
            'status' => 'executed',
            'forced' => $isForced,
            'total_cards' => $totalChecked,
            'dead_count' => $deadCount,
        ]);
    }
}
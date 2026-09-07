<?php

declare(strict_types=1);

namespace App\Commands;

use App\Models\AuditLogModel;
use App\Models\CronLogModel;
use App\Models\ItemModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

class CheckDeadLinks extends BaseCommand
{
    protected $group = 'Maintenance';
    protected $name = 'app:check-links';
    protected $description = 'Vérifie les liens morts (404) ou blacklistés de toutes les cartes. Exécution limitée à 1 fois par semaine.';

    public function run(array $params): void
    {
        $cronModel = new CronLogModel();
        $lastRun = $cronModel->where('task_name', 'check_dead_links')->first();

        if ($lastRun) {
            $lastRunDate = strtotime($lastRun['last_run']);
            $now = time();

            $force = in_array('-f', $params);

            if (($now - $lastRunDate) < 604800 && !$force) {
                CLI::write('La vérification a déjà eu lieu cette semaine ('.date('d/m/Y', $lastRunDate).').', 'yellow');
                CLI::write("Utilisez 'php spark app:check-links -f' pour forcer l'exécution.", 'yellow');

                return;
            }
        }

        CLI::write('Démarrage de la vérification des liens externes...', 'cyan');

        $itemModel = new ItemModel();
        $items = $itemModel->where('lien !=', '')->where('lien IS NOT NULL')->findAll();

        $client = Services::curlrequest([
            'timeout' => 7,
            'connect_timeout' => 5,
            'verify' => false,
            'http_errors' => false,
            'allow_redirects' => false,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'headers' => [
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7',
            ],
        ]);

        $deadCount = 0;
        $totalChecked = 0;

        $blacklist = [
            'flemmix.zip',
            'dlink9.com',
            'domain is for sale',
            'page not found',
            'expired',
        ];

        foreach ($items as $item) {
            $ep = $item->episode ?: '1';
            $ep2 = str_pad((string) $ep, 2, '0', STR_PAD_LEFT);

            $urlToTest = str_replace(['{ep}', '{ep2}'], [$ep, $ep2], $item->lien);
            ++$totalChecked;

            $isDead = false;
            $statusLog = '';

            // 1. Vérification immédiate de l'URL brute renseignée en base
            foreach ($blacklist as $badWord) {
                if (false !== stripos($urlToTest, $badWord)) {
                    $isDead = true;
                    $statusLog = 'Blacklist (URL source)';

                    break;
                }
            }

            // 2. Requête HTTP si l'URL semble saine
            if (!$isDead) {
                try {
                    $response = $client->get($urlToTest);
                    $statusCode = $response->getStatusCode();

                    // Si erreur réseau (DNS_PROBE_FINISHED_NXDOMAIN renvoie souvent 0)
                    if (0 === $statusCode) {
                        $isDead = true;
                        $statusLog = 'Erreur Réseau/DNS (Hôte introuvable)';
                    }
                    // Si c'est une redirection (301, 302, 307, 308)
                    elseif ($statusCode >= 300 && $statusCode < 400) {
                        $redirectUrl = $response->getHeaderLine('Location');

                        foreach ($blacklist as $badWord) {
                            if (false !== stripos($redirectUrl, $badWord)) {
                                $isDead = true;
                                $statusLog = "Blacklist (Redirigé vers {$badWord})";

                                break;
                            }
                        }
                    }
                    // Si la page charge directement en 200 OK
                    elseif (200 == $statusCode) {
                        $html = $response->getBody();
                        foreach ($blacklist as $badWord) {
                            if (false !== stripos((string) $html, $badWord)) {
                                $isDead = true;
                                $statusLog = "Blacklist (Mot clé '{$badWord}' dans le code)";

                                break;
                            }
                        }
                    }
                    // Si la page est clairement introuvable (On ignore les 403 et 5xx qui sont souvent des blocages Cloudflare)
                    elseif (404 === $statusCode) {
                        $isDead = true;
                        $statusLog = 'Erreur HTTP 404 (Introuvable)';
                    }
                } catch (\Throwable $e) {
                    // CATCH : On utilise \Throwable pour s'assurer d'attraper absolument toutes les erreurs
                    $isDead = true;
                    $statusLog = 'Timeout ou Erreur DNS (Domaine expiré ?)';
                }
            }

            // 3. Application du statut en base de données
            if ($isDead) {
                CLI::write("[MORT] ({$statusLog}) : {$item->titre}", 'red');
                $itemModel->update($item->id, ['link_status' => 'dead']);
                ++$deadCount;
            } else {
                if ('dead' === $item->link_status) {
                    $itemModel->update($item->id, ['link_status' => 'ok']);
                    CLI::write("[RÉTABLI] : {$item->titre}", 'green');
                }
            }
        }

        if ($lastRun) {
            $cronModel->update($lastRun['id'], ['last_run' => date('Y-m-d H:i:s')]);
        } else {
            $cronModel->insert(['task_name' => 'check_dead_links', 'last_run' => date('Y-m-d H:i:s')]);
        }

        if ($deadCount > 0) {
            $audit = new AuditLogModel();
            $audit->logAction('Maintenance Système', "Scan de liens : {$totalChecked} URLs testées, {$deadCount} lien(s) mort(s) détecté(s).");
        }

        CLI::newLine();
        CLI::write("✓ Vérification terminée. {$totalChecked} liens testés. {$deadCount} liens morts détectés.", 'black', 'green');
    }
}

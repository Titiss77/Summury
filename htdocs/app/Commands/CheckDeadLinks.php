<?php

declare(strict_types=1);

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * The previous command downloaded every row into PHP and treated network
 * failures as dead links. Link checks now run through the bounded HTTP worker.
 */
class CheckDeadLinks extends BaseCommand
{
    protected $group = 'Maintenance';
    protected $name = 'app:check-links';
    protected $description = 'Affiche la configuration nécessaire au cron HTTP de vérification des liens.';

    public function run(array $params): void
    {
        CLI::write('Cette commande ne lance plus de scan en mémoire.', 'yellow');
        CLI::write('Configurez un cron HTTP externe sur ' . rtrim((string) env('app.baseURL'), '/') . '/j?token=<CRON_SECRET>');
        CLI::write('Le worker traite un petit lot par appel et reprend automatiquement au prochain passage.');
    }
}

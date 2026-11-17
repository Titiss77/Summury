<?php

declare(strict_types=1);

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/** Link availability is checked from the admin page using an external ping API. */
class CheckDeadLinks extends BaseCommand
{
    protected $group = 'Maintenance';
    protected $name = 'app:check-links';
    protected $description = 'Indique comment lancer le scan des liens depuis l’administration.';

    public function run(array $params): void
    {
        CLI::write('Ouvrez Administration > Liens morts, puis cliquez sur « Lancer le scan Ping ».', 'yellow');
        CLI::write('Le navigateur interroge Check-Host et enregistre les résultats dans l’application.');
    }
}

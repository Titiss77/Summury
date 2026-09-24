<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

/**
 * Historise les exécutions des tâches planifiées (ex: vérification des liens morts).
 */
class CronLogModel extends Model
{
    protected $table = 'cron_logs';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'task_name',
        'last_run',
        'item_id',
        'titre',
        'url_testee',
        'code_erreur',
    ];
    protected $useTimestamps = false;
}

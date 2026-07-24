<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

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

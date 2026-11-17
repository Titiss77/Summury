<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

/**
 * Historise les signalements et les anciennes vérifications de liens.
 */
class CronLogModel extends Model
{
    public const TASK_PENDING_USER_REPORT = 'user_link_report_pending';
    public const TASK_REJECTED_USER_REPORT = 'user_link_report_rejected';
    public const TASK_CONFIRMED_DEAD = 'check_dead_links';

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

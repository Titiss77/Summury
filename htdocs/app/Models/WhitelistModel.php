<?php declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

/**
 * Gère les domaines de confiance (whitelist) pour ignorer les faux positifs du Cron.
 */
class WhitelistModel extends Model
{
    protected $table = 'whitelist';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['domain', 'created_at'];
    protected $useTimestamps = false;
}
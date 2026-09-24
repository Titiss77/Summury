<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

/**
 * Gère les signalements (ex: liens morts ou erreurs signalés par la communauté).
 */
class ReportModel extends Model
{
    protected $table = 'reports';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['item_id', 'user_id', 'type', 'description', 'status'];

    // Gère automatiquement les colonnes created_at et updated_at
    protected $useTimestamps = true;
}

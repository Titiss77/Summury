<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ReportModel extends Model
{
    protected $table = 'reports';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['item_id', 'user_id', 'type', 'description', 'status'];
    protected $useTimestamps = true; // Gère automatiquement created_at et updated_at
}

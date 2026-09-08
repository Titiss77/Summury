<?php declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class AutomationModel extends Model
{
    protected $table = 'automations';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    
    protected $allowedFields = [
        'id_user', 
        'type', 
        'source_url', 
        'id_division', 
        'sous_categorie', 
        'last_item_id'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}

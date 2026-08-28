<?php declare(strict_types=1);

namespace App\Models;
use CodeIgniter\Model;

class StatutModel extends Model
{
    protected $table = 'statuts';
    protected $primaryKey = 'nom';
    protected $returnType = 'array';
    protected $allowedFields = ['nom'];
}
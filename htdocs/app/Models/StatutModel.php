<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

/**
 * Gère les différents statuts possibles pour les cartes (À voir, En cours, etc.).
 */
class StatutModel extends Model
{
    protected $table = 'statuts';
    protected $primaryKey = 'nom';
    protected $returnType = 'array';
    protected $allowedFields = ['nom'];
}

<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modèle regroupant les configurations de scraping/disponibilité pour les domaines supportés.
 */
class SiteConfigModel extends Model
{
    protected $table = 'sites_config';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'domain',
        'regex_episode',
        'indicateurs_page_invalide',
        'indicateurs_lecteur',
        'is_active',
    ];
    protected $useTimestamps = false;
}

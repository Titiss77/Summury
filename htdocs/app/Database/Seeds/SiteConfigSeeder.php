<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SiteConfigSeeder extends Seeder
{
    public function run(): void
    {
        $siteConfigData = [
            [
                'id' => 1,
                'domain' => 'voir-anime.to',
                'regex_episode' => '/-(\d+)-vostfr/i',
                'indicateurs_page_invalide' => '[\"Premier EP\", \"Dernier EP\"]',
                'indicateurs_lecteur' => '[\"class=\\\"lecteur\\\"\", \"<iframe\", \"Lecteur\"]',
                'is_active' => 1,
            ],
            [
                'id' => 2,
                'domain' => 'scan-vf.net',
                'regex_episode' => '/chapitre-(\d+)/i',
                'indicateurs_page_invalide' => '[\"Liste des chapitres\", \"Manga en cours\"]',
                'indicateurs_lecteur' => '[\"img-responsive\", \"img-fluid\", \"pages_container\"]',
                'is_active' => 1,
            ],
            [
                'id' => 3,
                'domain' => 'franime.fr',
                'regex_episode' => '/[?&]ep=(\d+)/i',
                'indicateurs_page_invalide' => '[]',
                'indicateurs_lecteur' => '[\"margin-player\", \"Regarder l&#x27;épisode\"]',
                'is_active' => 1,
            ],
        ];

        $this->db->table('sites_config')->insertBatch($siteConfigData);
    }
}

<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class HeaderSeeder extends Seeder
{
    public function run(): void
    {
        $headerData = [
            [
                'id'   => 1,
                'nom' => 'Animés & Mangas',
            ],
            [
                'id'   => 2,
                'nom' => 'Films & Séries',
            ],
            [
                'id'   => 3,
                'nom' => 'Vidéos',
            ],
            [
                'id'   => 4,
                'nom' => 'Streaming',
            ],
            [
                'id'   => 5,
                'nom' => 'Utilitaires',
            ],
        ];
        
        $this->db->table('header')->insertBatch($headerData);
    }
}
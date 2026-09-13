<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DivisionSeeder extends Seeder
{
    public function run(): void
    {
        $divisionData = [
            [
                'id'   => 1,
                'id_header' => 1,
                'nom' => 'Animés',
            ],
            [
                'id'   => 2,
                'id_header' => 1,
                'nom' => 'Mangas',
            ],
            [
                'id'   => 3,
                'id_header' => 2,
                'nom' => 'Films',
            ],
            [
                'id'   => 4,
                'id_header' => 2,
                'nom' => 'Séries',
            ],
            [
                'id'   => 5,
                'id_header' => 3,
                'nom' => 'Vidéos',
            ],
            [
                'id'   => 6,
                'id_header' => 4,
                'nom' => 'Hors Catégories',
            ],
            [
                'id'   => 7,
                'id_header' => 4,
                'nom' => "Lecteurs d'Animés",
            ],
            [
                'id'   => 8,
                'id_header' => 4,
                'nom' => 'Lecteurs de Mangas',
            ],
            [
                'id'   => 9,
                'id_header' => 4,
                'nom' => 'Lecteurs de Films',
            ],
            [
                'id'   => 10,
                'id_header' => 4,
                'nom' => 'Lecteurs de Séries',
            ],
            [
                'id'   => 11,
                'id_header' => 5,
                'nom' => 'Utilitaires Web',
            ],
            [
                'id'   => 12,
                'id_header' => 5,
                'nom' => 'Autres',
            ]
        ];
        
        $this->db->table('division')->insertBatch($divisionData);
    }
}
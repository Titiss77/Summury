<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class StatutSeeder extends Seeder
{
    public function run(): void
    {
        $statutsData = [
            [
                'nom' => 'À voir',
                'ordre' => 2,
            ],
            [
                'nom' => 'Aucun',
                'ordre' => 1,
            ],
            [
                'nom' => 'En cours',
                'ordre' => 3,
            ],
            [
                'nom' => 'En pause',
                'ordre' => 4,
            ],
            [
                'nom' => 'Terminé',
                'ordre' => 5,
            ],
        ];

        $this->db->table('statuts')->insertBatch($statutsData);
    }
}

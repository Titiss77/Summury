<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $itemData = [
            [
                'id' => 1,
                'id_user' => 3,
                'id_division' => 1,
                'sous_categorie' => null,
                'titre' => 'One Piece',
                'titre_original' => 'ワンピース',
                'status' => 'En cours',
                'is_public' => 1,  // 0: Privé, 1: Public, 2: En attente
                'description' => `Il fut un temps où Gold Roger était le plus grand de tous les pirates, le "Roi des Pirates" était son surnom. A sa mort, son trésor d'une valeur inestimable connu sous le nom de "One Piece" fut caché quelque part sur "Grand Line". De nombreux pira...`,
                'date_sortie' => null,
                'image' => 'https://image.tmdb.org/t/p/w500/8f33Q5AuM2G5ZqFeusbIqDjr3cI.jpg',
                'lien' => null,
                'link_status' => null,
                'saison' => null,
                'total_saisons' => null,
                'episode' => null,
                'total_episodes' => null,
                'position' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('item')->insertBatch($itemData);
    }
}
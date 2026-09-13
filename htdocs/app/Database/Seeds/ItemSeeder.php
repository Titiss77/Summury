<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $itemData = [
            // 1. Un Animé "En cours" (Teste les badges d'épisodes et les liens dynamiques)
            [
                'id_user'        => 1,
                'id_division'    => 1,
                'sous_categorie' => 'Shonen',
                'titre'          => 'One Piece',
                'titre_original' => 'ワンピース',
                'status'         => 'En cours',
                'is_public'      => 1,
                'description'    => "L'histoire de Monkey D. Luffy, un garçon qui rêve de devenir le Roi des Pirates en trouvant le légendaire trésor One Piece.",
                'date_sortie'    => null,
                'image'          => 'https://image.tmdb.org/t/p/w500/8f33Q5AuM2G5ZqFeusbIqDjr3cI.jpg',
                'lien'           => null,
                'link_status'    => 'ok',
                'saison'         => null,
                'total_saisons'  => null,
                'episode'        => null,
                'total_episodes' => null,
                'position'       => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]
        ];

        $this->db->table('item')->insertBatch($itemData);
    }
}
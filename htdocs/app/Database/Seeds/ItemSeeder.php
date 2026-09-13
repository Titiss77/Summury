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
                'id_user'        => 3,
                'id_division'    => 1, // Animés
                'sous_categorie' => 'Shonen',
                'titre'          => 'One Piece',
                'titre_original' => 'ワンピース',
                'status'         => 'En cours',
                'is_public'      => 1,
                'description'    => "L'histoire de Monkey D. Luffy, un garçon qui rêve de devenir le Roi des Pirates en trouvant le légendaire trésor One Piece.",
                'date_sortie'    => null,
                'image'          => 'https://image.tmdb.org/t/p/w500/8f33Q5AuM2G5ZqFeusbIqDjr3cI.jpg',
                'lien'           => 'https://franime.fr/anime/one-piece?ep={ep}',
                'link_status'    => 'ok',
                'saison'         => 1,
                'total_saisons'  => 1,
                'episode'        => 1080,
                'total_episodes' => null, // Inconnu
                'position'       => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            // 2. Une Série "Terminée" (Teste le design spécifique des statuts terminés)
            [
                'id_user'        => 3,
                'id_division'    => 4, // Séries
                'sous_categorie' => 'Drame',
                'titre'          => 'Breaking Bad',
                'titre_original' => null,
                'status'         => 'Terminé',
                'is_public'      => 1,
                'description'    => "Un professeur de chimie atteint d'un cancer terminal s'associe à un ancien élève pour fabriquer et vendre de la méthamphétamine.",
                'date_sortie'    => null,
                'image'          => 'https://image.tmdb.org/t/p/w500/ggFHVNu6YYI5L9pCfOacjizwpB.jpg',
                'lien'           => null,
                'link_status'    => null,
                'saison'         => 5,
                'total_saisons'  => 5,
                'episode'        => 16,
                'total_episodes' => 16,
                'position'       => 2,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            // 3. Un Film "À voir" avec une date dans le futur (Teste l'alerte de date de sortie rouge)
            [
                'id_user'        => 3,
                'id_division'    => 3, // Films
                'sous_categorie' => 'Science-Fiction',
                'titre'          => 'Spider-Man: Beyond the Spider-Verse',
                'titre_original' => null,
                'status'         => 'À voir',
                'is_public'      => 1,
                'description'    => "La conclusion très attendue de la trilogie animée Spider-Verse.",
                'date_sortie'    => '2027-03-15 00:00:00', // Date dans le futur
                'image'          => 'https://image.tmdb.org/t/p/w500/8c4a8kE7PizaGQQnditMmI1xbRp.jpg',
                'lien'           => null,
                'link_status'    => null,
                'saison'         => null,
                'total_saisons'  => null,
                'episode'        => null,
                'total_episodes' => null,
                'position'       => 3,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            // 4. Un Manga "En pause" et "Privé" sans image (Teste le placeholder d'image et le mode privé)
            [
                'id_user'        => 3,
                'id_division'    => 2, // Mangas
                'sous_categorie' => 'Seinen',
                'titre'          => 'Berserk',
                'titre_original' => 'ベルセルク',
                'status'         => 'En pause',
                'is_public'      => 0, // Carte privée
                'description'    => "Guts, le guerrier noir, erre dans un monde cauchemardesque en quête de vengeance.",
                'date_sortie'    => null,
                'image'          => null, // Pas d'image pour forcer le design "Aperçu"
                'lien'           => 'https://scan-vf.net/berserk/chapitre-{ep}',
                'link_status'    => 'ok',
                'saison'         => null,
                'total_saisons'  => null,
                'episode'        => 373,
                'total_episodes' => null,
                'position'       => 4,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]
        ];

        $this->db->table('item')->insertBatch($itemData);
    }
}
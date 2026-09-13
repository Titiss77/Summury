<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $itemData = [
            // 1. Un Animé "En cours" (Teste les badges d'épisodes et les liens)
            [
                'id_user'        => 3,
                'id_division'    => 1, // Animés
                'sous_categorie' => 'Shonen',
                'titre'          => 'One Piece',
                'titre_original' => 'ワンピース',
                'status'         => 'En cours',
                'is_public'      => 0,
                'description'    => "L'histoire de Monkey D. Luffy, un garçon qui rêve de devenir le Roi des Pirates en trouvant le légendaire trésor One Piece.",
                'date_sortie'    => null,
                'image'          => 'https://image.tmdb.org/t/p/w500/8f33Q5AuM2G5ZqFeusbIqDjr3cI.jpg',
                'lien'           => 'https://voir-anime.to/anime/one-piece/one-piece-{ep4}-vostfr/',
                'link_status'    => 'ok',
                'saison'         => null,
                'total_saisons'  => null,
                'episode'        => 1041,
                'total_episodes' => null, // En cours, pas de fin connue
                'position'       => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],

            // 2. Un Manga "Terminé" 
            [
                'id_user'        => 3,
                'id_division'    => 2, // Mangas
                'sous_categorie' => 'Seinen',
                'titre'          => 'L\'Attaque des Titans',
                'titre_original' => '進撃の巨人',
                'status'         => 'Terminé',
                'is_public'      => 0,
                'description'    => "Dans un monde ravagé par des titans mangeurs d'hommes, Eren Yeager cherche à exterminer ces créatures.",
                'date_sortie'    => null,
                'image'          => 'https://image.tmdb.org/t/p/w500/aiy35Evcofzl7hASZZvsFgltleg.jpg',
                'lien'           => null,
                'link_status'    => 'ok',
                'saison'         => null,
                'total_saisons'  => null,
                'episode'        => 139,
                'total_episodes' => 139,
                'position'       => 2,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],

            // 3. Un Film "À voir" avec une date dans le futur (Teste l'alerte de date de sortie)
            [
                'id_user'        => 3,
                'id_division'    => 3, // Films
                'sous_categorie' => 'Science-Fiction',
                'titre'          => 'Spider-Man: Beyond the Spider-Verse',
                'titre_original' => null,
                'status'         => 'À voir',
                'is_public'      => 1,
                'description'    => "La conclusion très attendue de la trilogie animée Spider-Verse de Miles Morales.",
                'date_sortie'    => '2026-11-15 00:00:00', // Date future (en rouge sur ton site)
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

            // 4. Une Série "En pause" (Carte Privée)
            [
                'id_user'        => 3,
                'id_division'    => 4, // Séries
                'sous_categorie' => 'Drame',
                'titre'          => 'The Last of Us',
                'titre_original' => null,
                'status'         => 'En pause',
                'is_public'      => 0, // 0 = Privée
                'description'    => "Vingt ans après la destruction de la civilisation moderne, Joel est chargé de faire sortir Ellie, 14 ans, d'une zone de quarantaine.",
                'date_sortie'    => null,
                'image'          => 'https://image.tmdb.org/t/p/w500/u3bZgnGQ9T01sWNhyveQz0wH0Hl.jpg',
                'lien'           => 'https://www.primevideo.com/',
                'link_status'    => 'ok',
                'saison'         => 1,
                'total_saisons'  => 2,
                'episode'        => 5,
                'total_episodes' => 9,
                'position'       => 4,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],

            // 5. Un Manga sans image (Teste le placeholder d'image par défaut)
            [
                'id_user'        => 3,
                'id_division'    => 2, // Mangas
                'sous_categorie' => 'Dark Fantasy',
                'titre'          => 'Berserk',
                'titre_original' => 'ベルセルク',
                'status'         => 'En cours',
                'is_public'      => 1,
                'description'    => "Guts, le guerrier noir, erre dans un monde cauchemardesque en quête de vengeance.",
                'date_sortie'    => null,
                'image'          => null, // Va forcer l'affichage de la div "Aperçu"
                'lien'           => 'https://scan-vf.net/berserk/chapitre-{ep}',
                'link_status'    => 'ok',
                'saison'         => null,
                'total_saisons'  => null,
                'episode'        => 373,
                'total_episodes' => null,
                'position'       => 5,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],

            // 6. Un utilitaire web / lien (Sans saison ni épisode)
            [
                'id_user'        => 3,
                'id_division'    => 11, // Utilitaires Web
                'sous_categorie' => 'Base de données',
                'titre'          => 'MyAnimeList',
                'titre_original' => null,
                'status'         => 'Aucun',
                'is_public'      => 1,
                'description'    => "La plus grande base de données et communauté en ligne sur les animes et mangas.",
                'date_sortie'    => null,
                'image'          => 'https://upload.wikimedia.org/wikipedia/commons/7/7a/MyAnimeList_Logo.png',
                'lien'           => 'https://myanimelist.net/',
                'link_status'    => 'ok',
                'saison'         => null,
                'total_saisons'  => null,
                'episode'        => null,
                'total_episodes' => null,
                'position'       => 6,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]
        ];

        $this->db->table('item')->insertBatch($itemData);
    }
}
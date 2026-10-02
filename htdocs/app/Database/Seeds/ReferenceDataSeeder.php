<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Inserts the application's reference data without overwriting existing rows.
 */
class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        $headers = [
            1 => 'Animés & Mangas',
            2 => 'Films & Séries',
            3 => 'Vidéos',
            4 => 'Streaming',
            5 => 'Utilitaires',
        ];

        foreach ($headers as $id => $nom) {
            $this->insertIfMissing('header', ['id' => $id], ['id' => $id, 'nom' => $nom]);
        }

        $divisions = [
            1 => [1, 'Animés'],
            2 => [1, 'Mangas'],
            3 => [2, 'Films'],
            4 => [2, 'Séries'],
            5 => [3, 'Vidéos'],
            6 => [4, 'Hors Catégories'],
            7 => [4, 'Lecteurs d’Animés'],
            8 => [4, 'Lecteurs de Mangas'],
            9 => [4, 'Lecteurs de Films'],
            10 => [4, 'Lecteurs de Séries'],
            11 => [5, 'Utilitaires Web'],
            12 => [5, 'Autres'],
        ];

        foreach ($divisions as $id => [$idHeader, $nom]) {
            $this->insertIfMissing('division', ['id' => $id], [
                'id' => $id,
                'id_header' => $idHeader,
                'nom' => $nom,
            ]);
        }

        foreach ([
            ['nom' => 'Aucun', 'ordre' => 1],
            ['nom' => 'À voir', 'ordre' => 2],
            ['nom' => 'En cours', 'ordre' => 3],
            ['nom' => 'En pause', 'ordre' => 4],
            ['nom' => 'Terminé', 'ordre' => 5],
        ] as $statut) {
            $this->insertIfMissing('statuts', ['nom' => $statut['nom']], $statut);
        }

        // Starter rules used by the dead-link / episode checker.
        $sites = [
            [
                'id' => 1,
                'domain' => 'voir-anime.to',
                'regex_episode' => '/-(\\d+)-vostfr/i',
                'indicateurs_page_invalide' => '["Premier EP", "Dernier EP"]',
                'indicateurs_lecteur' => '["class=\\"lecteur\\"", "<iframe", "Lecteur"]',
                'is_active' => 1,
            ],
            [
                'id' => 2,
                'domain' => 'scan-vf.net',
                'regex_episode' => '/chapitre-(\\d+)/i',
                'indicateurs_page_invalide' => '["Liste des chapitres", "Manga en cours"]',
                'indicateurs_lecteur' => '["img-responsive", "img-fluid", "pages_container"]',
                'is_active' => 1,
            ],
            [
                'id' => 4,
                'domain' => 'franime.fr',
                'regex_episode' => '/[?&]ep=(\\d+)/i',
                'indicateurs_page_invalide' => '[]',
                'indicateurs_lecteur' => '["margin-player", "Regarder l’épisode"]',
                'is_active' => 1,
            ],
        ];

        foreach ($sites as $site) {
            $this->insertIfMissing('sites_config', ['id' => $site['id']], $site);
        }
    }

    /** @param array<string, int|string> $identity @param array<string, int|string> $data */
    private function insertIfMissing(string $table, array $identity, array $data): void
    {
        if ($this->db->table($table)->where($identity)->countAllResults() === 0) {
            $this->db->table($table)->insert($data);
        }
    }
}

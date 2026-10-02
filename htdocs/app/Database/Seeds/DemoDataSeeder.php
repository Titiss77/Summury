<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/** Adds a deterministic, varied dataset for exploring the application locally. */
class DemoDataSeeder extends Seeder
{
    private const USER_ID = 1;
    private const CREATED_AT = '2026-09-01 12:00:00';

    public function run(): void
    {
        $items = [
            [2001, 1, null, 'DEMO — Frieren : au-delà du voyage', 'Sous-titré', 'En cours', 1, 'Un voyage contemplatif après la fin de la grande aventure.', 2023, 9, 17, 28, 17, 28, 'https://example.com/frieren', 1, null],
            [2002, 1, null, 'DEMO — Spy x Family', 'Sous-titré', 'À voir', 1, 'Une famille improbable pour une mission secrète.', 2022, null, null, 1, 25, 25, 'https://example.com/spy-family', 2, null],
            [2003, 1, null, 'DEMO — Fullmetal Alchemist: Brotherhood', 'Terminé', 'Terminé', 1, 'Deux frères cherchent à réparer les conséquences d’une transmutation interdite.', 2009, 5, 64, 64, 64, 64, 'https://example.com/fullmetal', 3, null],
            [2004, 2, null, 'DEMO — One Piece (manga)', 'Lecture', 'En cours', 1, 'Une longue aventure de piraterie, suivie au fil des chapitres.', 1997, null, 1120, 1100, null, null, 'https://example.com/one-piece-manga', 1, null],
            [2005, 2, null, 'DEMO — Blue Period', 'Lecture', 'En pause', 0, 'Un lycéen découvre sa passion pour la peinture.', 2017, null, 55, 40, null, null, 'https://example.com/blue-period', 2, null],
            [2006, 3, null, 'DEMO — Le Voyage de Chihiro', 'Film', 'Terminé', 1, 'Un film d’animation fantastique.', 2001, null, null, null, null, null, 'https://example.com/chihiro', 1, null],
            [2007, 3, null, 'DEMO — Dune : Deuxième partie', 'Film', 'À voir', 1, 'La suite du récit de science-fiction sur Arrakis.', 2024, null, null, null, null, null, 'https://example.com/dune-2', 2, null],
            [2008, 4, null, 'DEMO — The Bear', 'Série', 'En cours', 0, 'Un chef revient à Chicago pour reprendre le restaurant familial.', 2022, 3, 3, 5, 2, 28, 'https://example.com/the-bear', 1, null],
            [2009, 4, null, 'DEMO — Severance', 'Série', 'En pause', 1, 'Des employés séparent leurs souvenirs professionnels et personnels.', 2022, 1, 9, 19, 1, 19, 'https://example.com/severance', 2, null],
            [2010, 5, null, 'DEMO — Conférence : le web ouvert', 'Vidéo', 'Aucun', 0, 'Une conférence à regarder plus tard.', 2025, null, null, null, null, null, 'https://example.com/web-ouvert', 1, null],
            [2011, 6, 'Gratuit', 'DEMO — Arte.tv', 'Streaming', 'Aucun', 1, 'Plateforme de documentaires et de programmes culturels.', 2025, null, null, null, null, null, 'https://www.arte.tv/', 1, null],
            [2012, 6, 'Payant', 'DEMO — Plateforme vidéo', 'Streaming', 'Aucun', 0, 'Exemple de service avec abonnement.', 2025, null, null, null, null, null, 'https://example.com/streaming', 2, null],
            [2013, 7, null, 'DEMO — Lecteur anime', 'Anime', 'Aucun', 1, 'Exemple de lecteur dans la catégorie dédiée.', 2025, null, null, null, null, null, 'https://example.com/anime-player', 1, null],
            [2014, 8, null, 'DEMO — Lecteur manga', 'Manga', 'Aucun', 1, 'Exemple de lecteur manga dans la catégorie dédiée.', 2025, null, null, null, null, null, 'https://example.com/manga-player', 1, null],
            [2015, 9, null, 'DEMO — Lecteur de films', 'Film', 'Aucun', 1, 'Exemple de lecteur de films.', 2025, null, null, null, null, null, 'https://example.com/movie-player', 1, null],
            [2016, 10, null, 'DEMO — Lecteur de séries', 'Série', 'Aucun', 1, 'Exemple de lecteur de séries.', 2025, null, null, null, null, null, 'https://example.com/series-player', 1, null],
            [2017, 11, 'Productivité', 'DEMO — Gestionnaire de tâches', 'Outil', 'En cours', 0, 'Comparer quelques outils de gestion de tâches.', 2025, null, null, null, null, null, 'https://example.com/tasks', 1, null],
            [2018, 11, 'IA', 'DEMO — Assistant de rédaction', 'Outil', 'À voir', 0, 'Outil à évaluer pour la rédaction et la synthèse.', 2025, null, null, null, null, null, 'https://example.com/writing-assistant', 2, null],
            [2019, 12, null, 'DEMO — Idée à classer', 'Note', 'Aucun', 0, 'Exemple dans la catégorie Autres.', 2025, null, null, null, null, null, null, 1, null],
            [2020, 11, 'Archives', 'DEMO — Ancien outil (corbeille)', 'Outil', 'Terminé', 0, 'Exemple de carte supprimée à restaurer.', 2021, null, null, null, null, null, 'https://example.com/archived-tool', 3, '2026-09-15 10:00:00'],
            [2021, 4, null, 'DEMO — Série avec lien à vérifier', 'Série', 'À voir', 1, 'Exemple de lien signalé comme indisponible.', 2025, null, null, null, null, null, 'https://example.com/unavailable', 4, null],
            [2022, 1, null, 'DEMO — Série sans image', 'Sous-titré', 'En pause', 0, 'Cas de carte sans illustration.', 2024, 1, 12, 6, 3, 12, null, 5, null],
            [2023, 3, null, 'DEMO — Film sans date de sortie', 'Film', 'Aucun', 0, null, null, null, null, null, null, null, null, 3, null],
            [2024, 2, null, 'DEMO — Manga avec progression', 'Lecture', 'En cours', 0, 'Une progression de lecture avec compteurs de chapitres.', 2020, null, 120, 82, null, null, 'https://example.com/manga-progress', 3, null],
        ];

        foreach ($items as [$id, $division, $subcategory, $title, $originalTitle, $status, $public, $description, $releaseYear, $season, $totalSeasons, $episode, $totalEpisodes, $episodeGlobal, $url, $position, $deletedAt]) {
            $this->insertIfMissing('item', ['id' => $id], [
                'id' => $id,
                'id_user' => self::USER_ID,
                'id_division' => $division,
                'sous_categorie' => $subcategory,
                'titre' => $title,
                'titre_original' => $originalTitle,
                'status' => $status,
                'is_public' => $public,
                'description' => $description,
                'date_sortie' => $releaseYear === null ? null : $releaseYear.'-01-01 00:00:00',
                'image' => null,
                'lien' => $url,
                'link_status' => $id === 2021 ? 'dead' : 'ok',
                'saison' => $season,
                'total_saisons' => $totalSeasons,
                'episode' => $episode,
                'total_episodes' => $totalEpisodes,
                'episode_global' => $episodeGlobal,
                'total_episodes_global' => $totalEpisodes,
                'position' => $position,
                'created_at' => self::CREATED_AT,
                'updated_at' => self::CREATED_AT,
                'deleted_at' => $deletedAt,
            ]);
        }

        $this->insertIfMissing('item_revisions', ['id' => 2001], [
            'id' => 2001, 'original_item_id' => 2001, 'id_user' => self::USER_ID,
            'titre' => 'DEMO — Frieren : au-delà du voyage (titre proposé)', 'sous_categorie' => 'Sous-titré',
            'status' => 'En cours', 'description' => 'Exemple de proposition en attente de modération.',
            'episode' => 18, 'total_episodes' => 28, 'saison' => 1, 'total_saisons' => 1,
            'position' => 1, 'date_sortie' => '2023-09-29 00:00:00', 'revision_status' => 'pending',
        ]);
        $this->insertIfMissing('item_revisions', ['id' => 2002], [
            'id' => 2002, 'original_item_id' => 2003, 'id_user' => self::USER_ID,
            'titre' => 'DEMO — Fullmetal Alchemist: Brotherhood', 'sous_categorie' => 'Sous-titré',
            'status' => 'Terminé', 'description' => 'Exemple de proposition déjà approuvée.',
            'episode' => 64, 'total_episodes' => 64, 'saison' => 1, 'total_saisons' => 1,
            'position' => 3, 'date_sortie' => '2009-04-05 00:00:00', 'revision_status' => 'approved',
        ]);

        $this->insertIfMissing('reports', ['id' => 2001], [
            'id' => 2001, 'item_id' => 2021, 'user_id' => self::USER_ID, 'type' => 'lien_mort',
            'description' => 'Le lien renvoie une erreur 404.', 'status' => 'pending',
            'created_at' => self::CREATED_AT, 'updated_at' => self::CREATED_AT,
        ]);
        $this->insertIfMissing('reports', ['id' => 2002], [
            'id' => 2002, 'item_id' => 2011, 'user_id' => self::USER_ID, 'type' => 'information',
            'description' => 'Exemple de signalement déjà traité.', 'status' => 'resolved',
            'created_at' => self::CREATED_AT, 'updated_at' => self::CREATED_AT,
        ]);

        $this->insertIfMissing('audit_logs', ['id' => 2001], [
            'id' => 2001, 'user_id' => self::USER_ID, 'action' => 'DEMO — Création de carte',
            'details' => 'Entrée de journal destinée à la démonstration.', 'ip_address' => '127.0.0.1',
            'created_at' => self::CREATED_AT,
        ]);
        $this->insertIfMissing('audit_logs', ['id' => 2002], [
            'id' => 2002, 'user_id' => null, 'action' => 'DEMO — Tâche planifiée',
            'details' => 'Exemple d’action système sans utilisateur connecté.', 'ip_address' => null,
            'created_at' => '2026-09-02 12:00:00',
        ]);

        $this->insertIfMissing('cron_logs', ['id' => 2001], [
            'id' => 2001, 'task_name' => 'DEMO — Vérification des liens', 'last_run' => self::CREATED_AT,
            'item_id' => 2021, 'titre' => 'DEMO — Série avec lien à vérifier',
            'url_testee' => 'https://example.com/unavailable', 'code_erreur' => 404,
        ]);
        $this->insertIfMissing('cron_logs', ['id' => 2002], [
            'id' => 2002, 'task_name' => 'DEMO — Vérification des liens', 'last_run' => '2026-09-02 12:00:00',
            'item_id' => 2011, 'titre' => 'DEMO — Arte.tv', 'url_testee' => 'https://www.arte.tv/', 'code_erreur' => 200,
        ]);
    }

    /** @param array<string, int|string|null> $identity @param array<string, int|string|null> $data */
    private function insertIfMissing(string $table, array $identity, array $data): void
    {
        if ($this->db->table($table)->where($identity)->countAllResults() === 0) {
            $this->db->table($table)->insert($data);
        }
    }
}

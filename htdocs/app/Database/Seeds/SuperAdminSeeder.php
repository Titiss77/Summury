<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/** Restores the fixed Super Admin account and its public cards. */
class SuperAdminSeeder extends Seeder
{
    private const USER_ID = 1;

    public function run(): void
    {
        $this->call('ReferenceDataSeeder');

        $this->insertIfMissing('users', ['id' => self::USER_ID], [
            'id' => self::USER_ID,
            'username' => 'Super Admin',
            'active' => 1,
            'created_at' => '2026-04-11 17:01:15',
            'updated_at' => '2026-05-17 12:39:43',
        ]);

        // Reuses the password hash from the project's database export.
        $this->insertIfMissing('auth_identities', ['user_id' => self::USER_ID, 'type' => 'email_password'], [
            'user_id' => self::USER_ID,
            'type' => 'email_password',
            'name' => null,
            'secret' => 'titisland@gmail.com',
            'secret2' => '$2y$12$fQQGOXUFz0cpRjQv6KEQKunD.NyN.foC2QF30zzcvm47qdRIHtW26',
            'force_reset' => 0,
            'created_at' => '2026-04-11 17:01:16',
            'updated_at' => '2026-09-05 11:55:27',
        ]);

        $this->insertIfMissing('auth_groups_users', ['user_id' => self::USER_ID, 'group' => 'superadmin'], [
            'user_id' => self::USER_ID,
            'group' => 'superadmin',
            'created_at' => '2026-04-11 17:01:16',
        ]);

        // id, division, subcategory, title, image, URL, link status, description, position, deleted_at
        $cards = [
            [10, 7, null, 'VoirAnime', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQdYwTRt_o2nzbUEQuhIf36xoD7DC5rpxP6vg&s', 'https://voir-anime.to/', 'ok', '', 0, null],
            [12, 10, null, 'PapaduStream', '', 'https://papadustreami.buzz/', 'ok', '', 0, null],
            [13, 10, null, 'PLR', null, 'https://sites.google.com/view/prl-series/accueil?authuser=0', 'ok', null, 0, null],
            [14, 7, null, 'Franime', 'https://linktr.ee/og/image/franime.jpg', 'https://franime.fr/', 'ok', '', 0, null],
            [16, 8, null, 'Lelmanga', 'https://img.themesinfo.com/i/1/387/wordpress-theme-mangareader-q6z9a-m.jpg', 'https://www.lelmanga.com/', 'ok', '', 0, null],
            [17, 8, null, 'ScanVf', null, 'https://www.scan-vf.net/', 'ok', null, 0, null],
            [19, 8, null, 'Sushiscan', '', 'https://sushiscan.net', 'ok', '', 0, null],
            [20, 9, null, 'PLR', null, 'https://sites.google.com/view/teamprl/', 'ok', null, 0, null],
            [21, 6, 'Gratuit', 'Wiflix', '', 'https://go-fle.site', 'ok', '', 4, null],
            [22, 6, 'Payant', 'Netflix', 'https://images.ctfassets.net/4cd45et68cgf/Rx83JoRDMkYNlMC9MKzcB/2b14d5a59fc3937afd3f03191e19502d/Netflix-Symbol.png?w=700&h=456', 'https://www.netflix.com/browse', 'ok', '', 0, null],
            [25, 11, null, 'Audio To Text', null, 'https://editor.flixier.com/transcribe?fx_source=search&lang=en&fx_campaign=convert-audio-to-text&fx_medium=tools', 'ok', 'Convertir les fichiers audio en textes', 4, null],
            [26, 11, null, 'Bootstrap Icons', null, 'https://icons.getbootstrap.com', 'ok', "Biblioth\u{00E8}que d'ic\u{00F4}nes", 5, null],
            [27, 6, 'Payant', 'Prime Video', 'https://cdn.prod.website-files.com/63f46dc8ada663b2260ad042/651e7514b3a51ee790163981_Amazon%20-%20Prime%20Video%20(2).jpg', 'https://www.primevideo.com/', 'dead', '', 1, null],
            [28, 11, null, 'ClipDrop', '', 'https://clipdrop.co/', 'ok', 'Administrer des images', 6, null],
            [30, 11, null, 'Durable', '', 'https://app.durable.co/dashboard', 'dead', "G\u{00E9}n\u{00E9}rer des sites web", 7, '2026-09-07 22:29:15'],
            [31, 11, null, 'Fotor', null, 'https://www.fotor.com/', 'ok', "conceptions et \u{00E9}ditions d'images", 8, null],
            [32, 11, 'IA', 'Krea.ai', '', 'https://www.krea.ai/apps/image/realtime', 'ok', 'G\u{00E9}n\u{00E9}rer des Images', 3, null],
            [33, 11, null, 'obfuscator', null, 'https://obfuscator.io/', 'ok', 'crypter les scripts javascripts', 9, null],
            [38, 11, 'IA', 'Gemini', '', 'https://gemini.google.com/app?hl=fr', 'ok', '', 1, null],
            [53, 6, 'Gratuit', 'Nakastream', '', 'https://nakastream.wiki/', 'ok', '', 3, null],
            [62, 6, 'Payant', 'Canal +', '', 'https://www.canalplus.com/?from=pass', 'ok', '', 2, '2026-07-07 19:47:56'],
            [72, 11, 'IA', 'Claude Code', '', 'https://claude.ai/new', 'ok', '', 0, null],
            [103, 11, 'IA', 'Copilot', '', '', 'ok', '', 2, null],
            [133, 6, 'Gratuit', 'Youtube', '', 'https://www.youtube.com/feed/subscriptions', 'ok', '', 3, null],
        ];

        foreach ($cards as [$id, $division, $subcategory, $title, $image, $url, $linkStatus, $description, $position, $deletedAt]) {
            $this->insertIfMissing('item', ['id' => $id], [
                'id' => $id,
                'id_user' => self::USER_ID,
                'is_public' => 1,
                'id_division' => $division,
                'sous_categorie' => $subcategory,
                'titre' => $title,
                'status' => 'Aucun',
                'image' => $image,
                'lien' => $url,
                'link_status' => $linkStatus,
                'description' => $description,
                'episode' => null,
                'total_episodes' => null,
                'saison' => null,
                'total_saisons' => match ($id) {
                    16 => 2,
                    19 => 1,
                    default => null,
                },
                'episode_global' => null,
                'total_episodes_global' => null,
                'position' => $position,
                'date_sortie' => null,
                'deleted_at' => $deletedAt,
            ]);
        }
    }

    /** @param array<string, int|string|null> $identity @param array<string, int|string|null> $data */
    private function insertIfMissing(string $table, array $identity, array $data): void
    {
        if ($this->db->table($table)->where($identity)->countAllResults() === 0) {
            $this->db->table($table)->insert($data);
        }
    }
}

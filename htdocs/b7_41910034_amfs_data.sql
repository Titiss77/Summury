SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

TRUNCATE TABLE `audit_logs`;
TRUNCATE TABLE `auth_groups_users`;
INSERT INTO `auth_groups_users` (`id`, `user_id`, `group`, `created_at`) VALUES
(1, 1, 'superadmin', '2026-04-11 17:01:16'),
(3, 3, 'user', '2026-04-30 14:13:10'),
(6, 2, 'admin', '2026-05-17 22:56:01'),
(7, 4, 'user', '2026-05-29 22:30:45'),
(8, 5, 'user', '2026-06-17 20:17:27');

TRUNCATE TABLE `auth_identities`;
INSERT INTO `auth_identities` (`id`, `user_id`, `type`, `name`, `secret`, `secret2`, `expires`, `extra`, `force_reset`, `last_used_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'email_password', NULL, 'titisland@gmail.com', '$2y$12$fQQGOXUFz0cpRjQv6KEQKunD.NyN.foC2QF30zzcvm47qdRIHtW26', NULL, NULL, 0, '2026-07-08 11:18:31', '2026-04-11 17:01:16', '2026-07-08 11:18:31'),
(2, 2, 'email_password', NULL, 'mathisfrances11@gmail.com', '$2y$12$MCy7X0OR/J0IAycNYTkrwOGAi2UygpbYVtakTyTZEOGJvE6b60BSa', NULL, NULL, 0, '2026-07-30 23:00:33', '2026-04-11 17:02:38', '2026-07-30 23:00:33'),
(3, 2, 'magic-link', NULL, 'e3fc52dba64bd3b1958f', NULL, '2026-04-30 15:11:11', NULL, 0, NULL, '2026-04-30 14:11:11', '2026-04-30 14:11:11'),
(4, 3, 'email_password', NULL, 'hugophilippe26@gmail.com', '$2y$12$9rKoqgP6n7E1vosN4EUWpu5L/lPLkyb9GrDKmIqJxQX9pzG/7drPG', NULL, NULL, 0, NULL, '2026-04-30 14:13:09', '2026-04-30 14:13:10'),
(5, 4, 'email_password', NULL, 'mathisfrances111@gmail.com', '$2y$12$q4NTrCKkkMj3kINlncokHuDcbgPaDT2SDDooXI0R5asUjUwjK1pem', NULL, NULL, 0, '2026-07-07 14:51:37', '2026-05-29 22:30:44', '2026-07-07 14:51:37'),
(6, 5, 'email_password', NULL, 'ambrefrances1@gmail.com', '$2y$12$AyjlWNvzet1MU5XhMJBDdeMjd9oGgFhKSGjIbtn3R25TWPeFUTfTG', NULL, NULL, 0, '2026-07-15 11:07:32', '2026-06-17 20:17:27', '2026-07-15 11:07:32');

TRUNCATE TABLE `auth_logins`;
TRUNCATE TABLE `auth_permissions_users`;
TRUNCATE TABLE `auth_remember_tokens`;
TRUNCATE TABLE `auth_token_logins`;
TRUNCATE TABLE `cron_logs`;
INSERT INTO `cron_logs` (`id`, `item_id`, `titre`, `url_testee`, `code_erreur`, `task_name`, `last_run`) VALUES
(1, NULL, 'Aucun lien mort détecté', NULL, 200, 'check_dead_links', '2026-08-01 18:07:56');

TRUNCATE TABLE `division`;
INSERT INTO `division` (`id`, `id_header`, `nom`) VALUES
(1, 1, 'Animés'),
(2, 1, 'Mangas'),
(3, 2, 'Films'),
(4, 2, 'Séries'),
(5, 3, 'Payant/Tout-en-un'),
(6, 3, 'Streaming Animés'),
(7, 3, 'Lecture Mangas'),
(8, 3, 'Streaming Films'),
(9, 3, 'Streaming Séries'),
(10, 5, 'Utilitaires Web'),
(11, 5, 'Autres');

TRUNCATE TABLE `header`;
INSERT INTO `header` (`id`, `nom`) VALUES
(1, 'Animés & Mangas'),
(2, 'Films & Séries'),
(3, 'Liens'),
(5, 'Outils');

TRUNCATE TABLE `item`;
INSERT INTO `item` (`id`, `id_user`, `is_public`, `id_division`, `titre`, `status`, `image`, `lien`, `link_status`, `description`, `episode`, `saison`, `position`, `date_sortie`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 2, 0, 1, 'One Piece', 'En cours', 'https://image.tmdb.org/t/p/w500/l5menwH7JjOBbXjoftYdwMmsqmT.jpg', 'https://voir-anime.to/anime/one-piece/one-piece-{ep4}-vostfr/', 'ok', 'Une aventure en haute mer légendaire et unique en son genre. Monkey D. Luffy est un jeune aventurier...', '1141', 1, 9, NULL, NULL, NULL, '2026-07-27 14:01:50'),
(2, 2, 0, 2, 'One Piece', 'Aucun', 'https://www.myutaku.com/media/mangas/12.jpg', 'https://www.scan-vf.net/one_piece/chapitre-{ep}', 'ok', 'Une aventure en haute mer légendaire et unique en son genre. Monkey D. Luffy est un jeune aventurier...', '11890', 0, 0, '2026-07-24 18:00:00', NULL, NULL, '2026-08-01 18:14:02'),
(5, 2, 0, 1, 'Frieren', 'Aucun', 'https://image.tmdb.org/t/p/w500/j8K7vgF3Kp5T6EwJvez9B4it6CB.jpg', 'https://voir-anime.to/anime/sousou-no-frieren-{s}/sousou-no-frieren-{s}-{ep2}-vostfr/', 'ok', 'L’elfe Frieren a vaincu le roi des démons aux côtés du groupe mené par le jeune héros Himmel. Après ...', '3', 2, 6, NULL, NULL, NULL, '2026-07-27 14:01:50'),
(6, 2, 0, 1, 'Wind Breaker', 'Aucun', 'https://cdn.myanimelist.net/images/anime/1526/148873l.jpg', 'https://voir-anime.to/anime/wind-breaker-{s}/wind-breaker-{s}-{ep2}-vostfr/', 'ok', 'Ever since Haruka Sakura joined Furin High School, where its students call themselves Bofurin and protect the town of Makochi, he has gained new friends despite his initial skepticism. Now starting to learn how to fight alongside his classmates an...', '12', 2, 4, NULL, '2026-07-16 19:28:34', NULL, '2026-07-16 19:28:34'),
(7, 2, 0, 1, 'To Your Eternity', 'Aucun', 'https://image.tmdb.org/t/p/w500/bohMYRVSIG68md0zQobyWbV4S8e.jpg', 'https://voir-anime.to/anime/fumetsu-no-anata-e-{s}/fumetsu-no-anata-e-{s}-{ep2}-vostfr/', 'ok', 'Un garçon solitaire errant dans les régions arctiques de l\'Amérique du Nord rencontre un loup. Tous ...', '9', 3, 10, NULL, NULL, NULL, '2026-07-27 14:01:50'),
(8, 2, 0, 1, 'Bleach', 'En pause', 'https://www.myutaku.com/media/anime/poster/74796.jpg', 'https://voir-anime.to/anime/bleach/bleach-{ep3}-vostfr/', 'ok', 'Adolescent de quinze ans, Ichigo Kurosaki possède un don particulier : celui de voir les esprits. Un...', '154', 8, 13, NULL, NULL, NULL, '2026-07-27 14:01:50'),
(10, 1, 1, 6, 'VoirAnime', 'Aucun', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQdYwTRt_o2nzbUEQuhIf36xoD7DC5rpxP6vg&s', 'https://voir-anime.to/', 'ok', '', '', 0, 0, NULL, NULL, NULL, NULL),
(12, 1, 1, 9, 'PapaduStream', 'Aucun', '', 'https://papadustream.sarl/', 'ok', '', '', 0, 0, NULL, NULL, NULL, '2026-08-01 18:07:42'),
(13, 1, 1, 9, 'PLR', 'Aucun', NULL, 'https://sites.google.com/view/prl-series/accueil?authuser=0', 'ok', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(14, 1, 1, 6, 'Franime', 'Aucun', 'https://linktr.ee/og/image/franime.jpg', 'https://franime.fr/', 'ok', '', '', 0, 0, NULL, NULL, NULL, NULL),
(16, 1, 1, 7, 'Lelmanga', 'Aucun', 'https://img.themesinfo.com/i/1/387/wordpress-theme-mangareader-q6z9a-m.jpg', 'https://www.lelmanga.com/', 'ok', '', '', 0, 2, NULL, NULL, NULL, NULL),
(17, 1, 1, 7, 'ScanVf', 'Aucun', NULL, 'https://www.scan-vf.net/', 'ok', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(19, 1, 1, 7, 'Sushiscan', 'Aucun', '', 'https://sushiscan.net', 'ok', '', '', 0, 1, NULL, NULL, NULL, NULL),
(20, 1, 1, 8, 'PLR', 'Aucun', NULL, 'https://sites.google.com/view/teamprl/', 'ok', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(21, 1, 1, 5, 'Wiflix', 'Aucun', '', 'https://go-fle.site', 'ok', '', NULL, 0, 3, NULL, NULL, NULL, '2026-08-01 18:06:17'),
(22, 1, 1, 5, 'Netflix', 'Aucun', 'https://images.ctfassets.net/4cd45et68cgf/Rx83JoRDMkYNlMC9MKzcB/2b14d5a59fc3937afd3f03191e19502d/Netflix-Symbol.png?w=700&h=456', 'https://www.netflix.com/browse', 'ok', '', '', 0, 0, NULL, NULL, NULL, '2026-07-07 19:48:20'),
(24, 2, 0, 1, 'Noble Reincarnation', 'En pause', 'https://image.tmdb.org/t/p/w500/ggxUYlw7a3eVegnXDv8aCDiLccJ.jpg', 'https://voir-anime.to/anime/noble-reincarnation-born-blessed-so-ill-obtain-ultimate-power/noble-reincarnation-born-blessed-so-ill-obtain-ultimate-power-{ep2}-vostfr/', 'ok', 'En tant que treizième prince de la famille royale, Noah a toujours mené une vie paisible, loin des i...', '2', 1, 5, NULL, NULL, NULL, '2026-07-27 14:01:50'),
(25, 1, 1, 10, 'Audio To Text', 'Aucun', NULL, 'https://editor.flixier.com/transcribe?fx_source=search&lang=en&fx_campaign=convert-audio-to-text&fx_medium=tools', 'ok', 'Convertir les fichiers audio en textes', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(26, 1, 1, 10, 'Bootstrap Icons', 'Aucun', NULL, 'https://icons.getbootstrap.com', 'ok', 'Bibliothèque d\'icônes', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(27, 1, 1, 5, 'Prime Video', 'Aucun', 'https://cdn.prod.website-files.com/63f46dc8ada663b2260ad042/651e7514b3a51ee790163981_Amazon%20-%20Prime%20Video%20(2).jpg', 'https://www.primevideo.com/', 'ok', '', '', 0, 1, NULL, NULL, NULL, '2026-07-07 19:48:20'),
(28, 1, 1, 10, 'ClipDrop', 'Aucun', '', 'https://clipdrop.co/', 'ok', 'Administrer des images', '', 0, 0, NULL, NULL, NULL, NULL),
(30, 1, 1, 10, 'Durable', 'Aucun', '', 'https://app.durable.co/dashboard', 'ok', 'Générer des sites web', '', 0, 0, NULL, NULL, NULL, NULL),
(31, 1, 1, 10, 'Fotor', 'Aucun', NULL, 'https://www.fotor.com/', 'ok', 'conceptions et éditions d\'images', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(32, 1, 1, 10, 'Krea.ai', 'Aucun', NULL, 'https://www.krea.ai/apps/image/realtime', 'ok', 'Générer des Images', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(33, 1, 1, 10, 'obfuscator', 'Aucun', NULL, 'https://obfuscator.io/', 'ok', 'crypter les scripts javascripts', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(34, 2, 0, 1, 'Tsugai - Daemons of the Shadow Realm', 'En cours', 'https://image.tmdb.org/t/p/w500/mNqW2jnAogZa0nJ94q1LUum8Hos.jpg', 'https://voir-anime.to/anime/yomi-no-tsugai/daemons-of-the-shadow-realm-{ep2}-vostfr/', 'ok', 'Yuru, le chasseur, vit séparé de sa sœur jumelle Asa, enfermée dans une prison pour satisfaire un ri...', '17', 1, 3, '2026-08-01 15:00:00', NULL, NULL, '2026-07-27 14:01:50'),
(35, 2, 0, 1, 'Classroom of the Elite ', 'En cours', 'https://cdn.myanimelist.net/images/anime/1176/153626l.jpg', 'https://voir-anime.to/anime/classroom-of-the-elite-{s}/classroom-of-the-elite-{s}-{ep2}-vostfr/', 'ok', 'As soon as Kiyotaka Ayanokouji and the rest of Class D officially enter their second year at Tokyo Metropolitan Advanced Nurturing High School, they face their next special test: each second-year student will have to team up with a first-year stud...', '8', 4, 0, NULL, NULL, NULL, '2026-07-27 22:32:03'),
(36, 2, 0, 1, 'Re:ZERO', 'Aucun', 'https://image.tmdb.org/t/p/w500/ccG0ZfXOQ0834bIus4SwZrXtkyM.jpg', 'https://voir-anime.to/anime/rezero-kara-hajimeru-isekai-seikatsu-s{s}/re-zero-kara-hajimeru-isekai-seikatsu-saison-{s}-{ep2}-vostfr/', 'ok', 'Subaru Natsuki a basculé dans un monde fantastique où il fait la connaissance d’Émilia, une jeune fi...', '1', 3, 15, NULL, NULL, NULL, '2026-07-27 14:01:50'),
(37, 2, 0, 1, 'Dr. STONE', 'À voir', 'https://image.tmdb.org/t/p/w500/dLlnzbDCblBXcJqFLXyvN43NIwp.jpg', 'https://voir-anime.to/anime/dr-stone-{s}-science-future/dr-stone-{s}-{ep2}-vostfr/', 'ok', 'Plusieurs milliers d\'années après un mystérieux phénomène qui a transformé toute l\'humanité en pierr...', '1', 4, 14, NULL, NULL, NULL, '2026-07-27 14:01:50'),
(38, 1, 1, 10, 'Gemini', 'Aucun', '', 'https://gemini.google.com/app?hl=fr', 'ok', '', '', 0, 0, NULL, NULL, NULL, NULL),
(39, 2, 0, 11, 'Suivi des comptes', 'Aucun', '', 'https://summury.22web.org/suivi-comptes/index.php', 'ok', '', '', 0, 0, NULL, NULL, NULL, '2026-07-25 12:51:36'),
(40, 2, 0, 2, 'Jujutsu Kaisen Modulo', 'À voir', 'https://www.myutaku.com/media/mangas/88950.jpg?1757883349', 'https://www.scan-vf.net/jujutsu-kaisen-modulo/chapitre-{ep}', 'ok', 'Souffrance, regrets, humiliations... les sentiments négatifs que ressentent les humains se transform...', '5', 0, 2, NULL, NULL, NULL, NULL),
(41, 2, 1, 11, 'LivesPalmes', 'Aucun', '', 'https://livepalmes.web.app/', 'ok', 'LivePalmes (FFESSM) : suivez la nage avec palmes en direct, consultez les records et les archives.', '', NULL, 1, NULL, NULL, NULL, '2026-07-25 12:51:36'),
(43, 2, 0, 1, 'Les Carnets de l\'apothicaire', 'Aucun', 'https://image.tmdb.org/t/p/w500/47pSay5Ao7SFeyQBZVkW5ifyhAZ.jpg', 'https://voir-anime.to/anime/the-apothecary-diaries/the-apothecary-diaries-{ep2}-vostfr/', 'ok', 'Formée dès son plus jeune âge par son père apothicaire, Mao Mao est un jour vendue comme servante au...', '1', 1, 16, NULL, NULL, NULL, '2026-07-27 14:01:50'),
(47, 2, 0, 11, 'Liens très privés', 'Aucun', '', 'https://prive.titiss.space', 'ok', '', '', 0, 2, NULL, NULL, NULL, '2026-07-25 12:51:36'),
(52, 2, 0, 2, 'Black Clover', 'À voir', 'https://image.tmdb.org/t/p/w500/p3rUhlE81nWxPqpPR8F2u7a01Tl.jpg', 'https://www.scan-vf.net/black-clover/chapitre-{ep}', 'ok', 'Dans un monde régi par la magie, Yuno et Asta ont grandi ensemble avec un seul but en tête : devenir...', '356', 0, 1, NULL, NULL, NULL, NULL),
(53, 1, 1, 5, 'Nakastream', 'Aucun', '', 'https://nakastream.wiki/', 'ok', '', '', 0, 4, NULL, NULL, NULL, '2026-07-07 19:48:20'),
(54, 2, 0, 11, 'Site de troll', 'Aucun', '', 'https://mathis.likesyou.org/troll/amfs/Trouve-tu_le_site_interessant', 'ok', '', '', 0, 3, NULL, NULL, NULL, '2026-07-25 12:51:36'),
(55, 2, 0, 1, 'BLACK TORCH', 'En cours', 'https://image.tmdb.org/t/p/w500/qxPsSYAiNhFLETmFJZ0s5HWyYhr.jpg', 'https://voir-anime.to/anime/black-torch/black-torch-{ep2}-vostfr/', 'ok', 'Adolescent au grand cœur capable de communiquer avec le monde animal, Jiro est issu d\'une longue lig...', '5', 1, 2, '2026-08-01 15:00:00', NULL, NULL, '2026-07-27 14:01:50'),
(57, 2, 0, 1, 'Mushoku Tensei: Jobless Reincarnation', 'En cours', 'https://image.tmdb.org/t/p/w500/sviEqFIPJW5gFtuYy8XyE0Uscid.jpg', 'https://voir-anime.to/anime/mushoku-tensei-{s}/mushoku-tensei-{s}-{ep2}-vostfr/', 'ok', '« Ici, je vais me transcender ! » Un anonyme de 34 ans, célibataire endurci, reclus et au chômage se...', '6', 3, 1, '2026-08-02 17:00:00', NULL, NULL, '2026-07-27 14:01:50'),
(58, 2, 0, 4, 'Game of Thrones', 'À voir', 'https://image.tmdb.org/t/p/w500/eRMfekBOnwyE9G0ffyEJIBOjX2n.jpg', 'https://nakastream.tv/player?title=Game%20of%20Thrones&id=339&poster=/eRMfekBOnwyE9G0ffyEJIBOjX2n.jpg&type=tv&season={s}&episode={ep}', 'ok', 'Il y a très longtemps, à une époque oubliée, une force a détruit l\'équilibre des saisons. Dans un pa...', '1', 1, 0, NULL, '2026-07-07 19:32:21', '2026-07-07 19:31:19', '2026-07-07 19:32:21'),
(59, 2, 0, 4, 'Vampire Diaries', 'À voir', 'https://image.tmdb.org/t/p/w500/4RHhqEdI2VV5wHp0rLmKAg9t9h6.jpg', 'https://nakastream.tv/player?title=Vampire%20Diaries&id=1434&poster=/4RHhqEdI2VV5wHp0rLmKAg9t9h6.jpg&type=tv&season={s}&episode={ep}', 'ok', 'Quatre mois après le tragique accident de voiture qui a tué leurs parents, Elena Gilbert, 17 ans, et son frère Jeremy, 15 ans, essaient encore de s\'adapter à cette nouvelle réalité. Belle et populaire, l\'adolescente poursuit ses études au Mystic F...', '16', 5, 2, NULL, NULL, '2026-07-07 19:33:46', '2026-07-31 12:50:47'),
(60, 2, 0, 4, 'The Originals', 'En cours', 'https://image.tmdb.org/t/p/w500/keJOhJXGiLL54EW6QocbyvQGquA.jpg', 'https://nakastream.tv/player?title=The%20Originals&id=1438&poster=/keJOhJXGiLL54EW6QocbyvQGquA.jpg&type=tv&season={s}&episode={ep}', 'ok', 'Le vampire originel Klaus fait son retour au Vieux Carré, un quartier français de la Nouvelle Orléans. Dans cette ville qu’il a aidé à construire quelques siècles plus tôt, il y retrouve son ancien protégé, le diabolique et charismatique Marcel. D...', '16', 1, 1, NULL, NULL, '2026-07-07 19:35:06', '2026-08-01 17:33:19'),
(61, 2, 0, 4, 'Ordre de diffusion TVD & The originals', 'Aucun', '', 'https://drive.google.com/drive/folders/1fd1YxKtcuBG0xH5TnCoL2po_BS7aOYT_?usp=sharing', 'ok', 'Capture n°3', NULL, NULL, 0, NULL, NULL, '2026-07-07 19:44:52', '2026-08-01 17:36:50'),
(62, 1, 1, 5, 'Canal +', 'Aucun', '', 'https://www.canalplus.com/?from=pass', 'ok', '', '', 0, 2, NULL, NULL, '2026-07-07 19:47:56', '2026-07-07 19:48:24'),
(63, 2, 0, 4, 'The Protector', 'En cours', 'https://image.tmdb.org/t/p/w500/v3cYsLksGX1baCYtn2AQa2R5HDR.jpg', '', 'ok', '', '', 0, 0, NULL, '2026-07-09 21:22:53', '2026-07-09 21:18:46', '2026-07-09 21:22:53'),
(64, 2, 0, 3, 'Enola Holmes 3', 'Aucun', 'https://image.tmdb.org/t/p/w500/ncHImt9szlNQaNM2iY3vcgSdDp.jpg', 'https://nakastream.tv/player?title=Enola%20Holmes%203&id=8950&poster=/7kRYHH9H9PjBFwz1FprbHB2AAjI.jpg&type=movie', 'ok', 'La détective Enola Holmes poursuit ses aventures à Malte, où son projet de mariage se complique quand elle doit résoudre une périlleuse affaire liée à la disparition de Sherlock.', '', 0, 0, NULL, '2026-07-09 22:18:56', '2026-07-09 21:23:44', '2026-07-09 22:18:56'),
(65, 2, 0, 3, 'Enola Holmes 3', 'Aucun', 'https://image.tmdb.org/t/p/w500/ncHImt9szlNQaNM2iY3vcgSdDp.jpg', 'https://nakastream.tv/player?title=Enola%20Holmes%203&id=8950&poster=/7kRYHH9H9PjBFwz1FprbHB2AAjI.jpg&type=movie', 'ok', 'La détective Enola Holmes poursuit ses aventures à Malte, où son projet de mariage se complique quand elle doit résoudre une périlleuse affaire liée à la disparition de Sherlock.', NULL, NULL, 0, NULL, '2026-07-11 22:29:21', '2026-07-09 22:19:14', '2026-07-11 22:29:21'),
(66, 2, 0, 4, 'Le Protecteur d\'Istanbul', 'À voir', 'https://image.tmdb.org/t/p/w500/mj6z8wMzcYPt9pwJqHxy0Avlnum.jpg', 'https://papadustream.rentals/cat-series/drame-s/1287-le-protecteur-distanbul-c8a/{s}-saison/{ep}-episode.html', 'ok', 'Après avoir découvert ce qui le lie à un ancien ordre secret, un jeune homme de l\'Istanbul moderne entreprend de sauver la ville des griffes d\'un ennemi immortel.', '1', 4, 3, NULL, '2026-07-20 22:38:08', '2026-07-09 22:29:20', '2026-07-20 22:38:08'),
(67, 2, 0, 4, 'The Witcher', 'En cours', 'https://image.tmdb.org/t/p/w500/rhErSlk0M236rNFertVAZa9lz9S.jpg', 'https://papadustream.sarl/cat-series/aventure-s/4328-the-witcher/{s}-saison/{ep}-episode.html', 'ok', 'Le sorcier Geralt, un chasseur de monstres mutant, se bat pour trouver sa place dans un monde où les humains se révèlent souvent plus vicieux que les bêtes.', '1', 4, 4, NULL, NULL, '2026-07-11 14:18:32', '2026-08-01 18:07:42'),
(68, 2, 0, 1, 'Arifureta', 'En pause', 'https://image.tmdb.org/t/p/w500/3vwcB2MtQA1VZMCljCRSrDzNzdj.jpg', 'https://voir-anime.to/anime/arifureta-shokugyou-de-sekai-saikyou-{s}/arifureta-shokugyou-de-sekai-saikyou-{s}-{ep2}-vostfr/', 'ok', 'Hajime Nagumo, véritable souffre-douleur, se retrouve transporté avec toute sa classe dans un autre monde. Alors que ses camarades acquièrent des techniques de combat ultra puissantes, Hajime se retrouve doté d’une modeste compétence. Suite à la m...', '1', 3, 7, NULL, NULL, '2026-07-16 19:31:41', '2026-07-27 14:01:50'),
(69, 2, 0, 1, 'A Playthrough of a Certain Dude\'s VRMMO Life', 'En pause', 'https://image.tmdb.org/t/p/w500/sCLrdFdsweruRFLdN1DytcwHBZw.jpg', 'https://voir-anime.to/anime/a-playthrough-of-a-certain-dudes-vrmmo-life/a-playthrough-of-a-certain-dudes-vrmmo-life-{ep2}-vostfr/', 'ok', 'Taichi Tanaka est un Japonais ordinaire qui vient de se créer un personnage, \"Earth\", dans un tout nouveau jeu VRMMO appelé \"One More Free Life Online\" et promettant un champ d’action quasi-illimité. Dans un monde où les joueurs sont libres de déf...', '6', 1, 8, NULL, '2026-07-27 14:42:53', '2026-07-16 19:33:59', '2026-07-27 14:42:53'),
(70, 2, 0, 1, 'Villainess Level 99', 'En pause', 'https://image.tmdb.org/t/p/w500/vsTjL8hO4iSUcEx7eNxAgMxDspa.jpg', 'https://voir-anime.to/anime/villainess-level-99/villainess-level-99-{ep2}-vostfr/', 'ok', 'Cette étudiante japonaise discrète est réincarnée dans le corps d’Eumiella Dolkness, la méchante de son otome game préféré. Aspirant toujours à une vie tranquille, elle n’est pas vraiment ravie et décide d’abandonner ses fonctions maléfiques. Jusq...', '10', 1, 11, NULL, NULL, '2026-07-16 19:35:41', '2026-07-27 14:01:50'),
(71, 2, 0, 1, 'Goblin Slayer', 'En pause', 'https://image.tmdb.org/t/p/w500/nUiT0whRDuUJKkk74L1pn8xUE2z.jpg', 'https://voir-anime.to/anime/goblin-slayer-ii/goblin-slayer-{s}-{ep2}-vostfr/', 'ok', 'Au sein de la Guilde des Aventuriers, les gobelins sont perçus comme de simples nuisibles dont l’élimination est confiée aux novices inexpérimentés. Cependant, un aventurier de rang Argent, surnommé le « Goblin Slayer », discerne la véritable natu...', '1', 2, 12, NULL, NULL, '2026-07-16 19:37:51', '2026-07-27 14:01:50'),
(72, 1, 1, 10, 'Claude Code', 'Aucun', '', 'https://claude.ai/new', 'ok', '', NULL, NULL, 0, NULL, NULL, '2026-07-19 12:23:28', '2026-07-19 12:23:40'),
(73, 2, 0, 11, 'Intranap du pec', 'Aucun', '', 'https://pec-intranap.is-best.net/', 'ok', '', NULL, NULL, 4, NULL, NULL, '2026-07-19 16:26:59', '2026-07-25 12:51:36'),
(74, 2, 0, 11, 'Fit Analitics', 'Aucun', '', 'https://fitanalitics.likesyou.org/', 'ok', '', NULL, NULL, 5, NULL, NULL, '2026-07-20 22:33:03', '2026-07-25 12:51:36'),
(75, 2, 0, 11, 'Calculateur MG&M', 'Aucun', '', 'https://cmg-navy.22web.org/', 'ok', '', NULL, NULL, 6, NULL, NULL, '2026-07-24 15:25:22', '2026-07-25 12:51:36'),
(76, 2, 0, 1, 'Ingoku Danchi', 'Aucun', 'https://cdn.myanimelist.net/images/anime/1373/155741.jpg', 'https://voir-anime.to/anime/ingoku-danchi/ingoku-danchi-deviants-apartment-complex-{ep2}-vostfr/', 'ok', 'Des rumeurs circulent depuis peu concernant un complexe d\'appartements hanté par de nombreuses femmes lascives. Ignorant tout de ces rumeurs, un jeune homme nommé Yoshida devient le nouveau gérant de ce complexe. Lors de ses rondes nocturnes...', '1', 1, 16, NULL, '2026-07-27 10:44:39', '2026-07-26 23:37:25', '2026-07-27 10:44:39'),
(77, 2, 0, 1, 'Ingoku Danchi', 'Aucun', 'https://cdn.myanimelist.net/images/anime/1373/155741.jpg', 'https://voir-anime.to/anime/ingoku-danchi/ingoku-danchi-deviants-apartment-complex-{ep2}-vostfr/', 'ok', 'Yoshida jeune diplômé, petit et frêle, se retrouve à la tête d\'un immeuble d\'appartements contre son gré lorsque son père, l\'ancien gérant, se blesse. À son insu, cet immeuble abrite pas mal de femmes aux préférences sexuelles pour le moins… inhab...', '1', 1, 4, NULL, '2026-07-27 14:51:07', '2026-07-27 10:44:23', '2026-07-27 14:51:07');

TRUNCATE TABLE `item_revisions`;
TRUNCATE TABLE `migrations`;
TRUNCATE TABLE `reports`;
TRUNCATE TABLE `settings`;
TRUNCATE TABLE `sites_config`;
INSERT INTO `sites_config` (`id`, `domain`, `regex_episode`, `indicateurs_page_invalide`, `indicateurs_lecteur`, `is_active`) VALUES
(1, 'voir-anime.to', '/-(\\d+)-vostfr/i', '[\"Premier EP\", \"Dernier EP\"]', '[\"class=\\\"lecteur\\\"\", \"<iframe\", \"Lecteur\"]', 1),
(2, 'scan-vf.net', '/chapitre-(\\d+)/i', '[\"Liste des chapitres\", \"Manga en cours\"]', '[\"img-responsive\", \"img-fluid\", \"pages_container\"]', 1);

TRUNCATE TABLE `users`;
INSERT INTO `users` (`id`, `username`, `status`, `status_message`, `active`, `last_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Super Admin', NULL, NULL, 1, '2026-07-07 14:51:06', '2026-04-11 17:01:15', '2026-05-17 12:39:43', NULL),
(2, 'Titiss', NULL, NULL, 1, '2026-08-01 18:19:58', '2026-04-11 17:02:37', '2026-04-11 17:02:38', NULL),
(3, 'Seiko', NULL, NULL, 1, NULL, '2026-04-30 14:13:09', '2026-05-29 22:18:11', NULL),
(4, 'User de test', 'banned', 'Accès révoqué par l\'administration.', 1, NULL, '2026-05-29 22:30:44', '2026-07-07 15:33:52', NULL),
(5, 'Ambre', NULL, NULL, 1, '2026-08-01 15:10:26', '2026-06-17 20:17:27', '2026-06-25 12:09:08', NULL);
COMMIT;

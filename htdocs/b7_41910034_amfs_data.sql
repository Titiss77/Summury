SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


TRUNCATE TABLE `audit_logs`;
INSERT INTO `audit_logs` VALUES
(1, 2, 'Mise à jour Carte', 'Modification de la carte ID 2 (\'One Piece\'). Visibilité : Privée.', '5.49.246.18', '2026-08-01 18:24:39'),
(2, 2, 'Mise à jour Carte', 'Modification de la carte ID 2 (\'One Piece\'). Visibilité : Privée.', '5.49.246.18', '2026-08-01 18:36:45'),
(3, 2, 'Mise à jour Carte', 'Modification de la carte ID 55 (\'BLACK TORCH\'). Visibilité : Privée.', '5.49.246.18', '2026-08-02 21:17:48'),
(4, 2, 'Mise à jour Carte', 'Modification de la carte ID 34 (\'Tsugai - Daemons of the Shadow Realm\'). Visibilité : Privée.', '5.49.246.18', '2026-08-02 21:40:10'),
(5, 2, 'Mise à jour Carte', 'Modification de la carte ID 57 (\'Mushoku Tensei: Jobless Reincarnation\'). Visibilité : Privée.', '5.49.246.18', '2026-08-03 17:11:06'),
(6, 2, 'Incrémentation Rapide', 'Mise à jour de la carte ID 35 (\'Classroom of the Elite \') : Épisode passé à 9.', '5.49.246.18', '2026-08-03 18:32:45'),
(7, 2, 'Mise à jour Carte', 'Modification de la carte ID 35 (\'Classroom of the Elite \'). Visibilité : Privée.', '5.49.246.18', '2026-08-03 21:18:34'),
(8, 2, 'Mise à jour Carte', 'Modification de la carte ID 35 (\'Classroom of the Elite \'). Visibilité : Privée.', '5.49.246.18', '2026-08-03 21:21:58'),
(9, 2, 'Mise à jour Carte', 'Modification de la carte ID 35 (\'Classroom of the Elite \'). Visibilité : Privée.', '5.49.246.18', '2026-08-03 21:53:10'),
(10, 2, 'Mise à jour Carte', 'Modification de la carte ID 35 (\'Classroom of the Elite \'). Visibilité : Privée.', '5.49.246.18', '2026-08-03 22:04:26'),
(11, 2, 'Mise à jour Carte', 'Modification de la carte ID 35 (\'Classroom of the Elite \'). Visibilité : Privée.', '5.49.246.18', '2026-08-03 22:34:01'),
(12, 2, 'Mise à jour Carte', 'Modification de la carte ID 35 (\'Classroom of the Elite \'). Visibilité : Privée.', '5.49.246.18', '2026-08-03 22:34:09'),
(13, 2, 'Mise à jour Carte', 'Modification de la carte ID 35 (\'Classroom of the Elite\'). Visibilité : Privée.', '5.49.246.18', '2026-08-03 22:34:16'),
(14, 2, 'Mise à jour Carte', 'Modification de la carte ID 36 (\'Re:ZERO\'). Visibilité : Privée.', '5.49.246.18', '2026-08-03 22:34:44'),
(15, 2, 'Mise à jour Carte', 'Modification de la carte ID 37 (\'Dr. STONE\'). Visibilité : Privée.', '5.49.246.18', '2026-08-03 22:35:01'),
(16, 2, 'Reorganisation', 'L\'utilisateur a modifié l\'ordre d\'affichage de 15 carte(s).', '5.49.246.18', '2026-08-03 22:35:52'),
(17, 2, 'Reorganisation', 'L\'utilisateur a modifié l\'ordre d\'affichage de 15 carte(s).', '5.49.246.18', '2026-08-03 22:35:54'),
(18, 2, 'Mise à jour Carte', 'Modification de la carte ID 8 (\'Bleach\'). Visibilité : Privée.', '5.49.246.18', '2026-08-03 22:38:01'),
(19, 2, 'Mise à jour Carte', 'Modification de la carte ID 8 (\'Bleach\'). Visibilité : Privée.', '5.49.246.18', '2026-08-03 22:38:40'),
(20, 2, 'Incrémentation Rapide', 'Mise à jour de la carte ID 8 (\'Bleach\') : Épisode passé à 155.', '5.49.246.18', '2026-08-03 23:03:34'),
(21, 2, 'Incrémentation Rapide', 'Mise à jour de la carte ID 60 (\'The Originals\') : Épisode passé à 17.', '5.49.246.18', '2026-08-04 13:06:25'),
(22, 2, 'Incrémentation Rapide', 'Mise à jour de la carte ID 60 (\'The Originals\') : Épisode passé à 18.', '104.28.42.23', '2026-08-04 13:43:18'),
(23, 2, 'Mise à jour Carte', 'Modification de la carte ID 57 (\'Mushoku Tensei\'). Visibilité : Privée.', '5.49.246.18', '2026-08-04 22:11:01'),
(24, 2, 'Mise à jour Carte', 'Modification de la carte ID 34 (\'Yuru no Tsugai\'). Visibilité : Privée.', '5.49.246.18', '2026-08-04 22:11:16'),
(25, 2, 'Mise à jour Carte', 'Modification de la carte ID 70 (\'Villainess Level 99\'). Visibilité : Privée.', '5.49.246.18', '2026-08-04 22:12:22'),
(26, 2, 'Mise à jour Carte', 'Modification de la carte ID 70 (\'Villainess Level 99\'). Visibilité : Privée.', '5.49.246.18', '2026-08-04 22:38:36'),
(27, 2, 'Mise à jour Carte', 'Modification de la carte ID 70 (\'Villainess Level 99\'). Visibilité : Privée.', '5.49.246.18', '2026-08-04 22:38:39'),
(28, 2, 'Incrémentation Rapide', 'Mise à jour de la carte ID 59 (\'Vampire Diaries\') : Épisode passé à 17.', '104.28.42.14', '2026-08-05 13:01:04'),
(29, 2, 'Incrémentation Rapide', 'Mise à jour de la carte ID 59 (\'Vampire Diaries\') : Épisode passé à 18.', '5.49.246.18', '2026-08-06 23:10:41'),
(30, 2, 'Incrémentation Rapide', 'Mise à jour de la carte ID 60 (\'The Originals\') : Épisode passé à 19.', '5.49.246.18', '2026-08-07 13:24:39'),
(31, NULL, 'Maintenance Système', 'Scan de liens en arrière-plan : 30 domaines uniques testés pour 51 cartes. 1 carte(s) impactée(s).', '5.49.246.18', '2026-08-09 16:02:27'),
(32, 2, 'Mise à jour Carte', 'Modification de la carte ID 55 (\'BLACK TORCH\'). Visibilité : Privée.', '5.49.246.18', '2026-08-09 17:33:34'),
(33, 2, 'Mise à jour Carte', 'Modification de la carte ID 34 (\'Yuru no Tsugai\'). Visibilité : Privée.', '5.49.246.18', '2026-08-09 18:11:15'),
(34, 2, 'Mise à jour Carte', 'Modification de la carte ID 2 (\'One Piece\'). Visibilité : Privée.', '5.49.246.18', '2026-08-09 18:37:39'),
(35, 2, 'Mise à jour Carte', 'Modification de la carte ID 2 (\'One Piece\'). Visibilité : Privée.', '5.49.246.18', '2026-08-09 18:37:52'),
(36, 2, 'Incrémentation Rapide', 'Mise à jour de la carte ID 59 (\'Vampire Diaries\') : Épisode passé à 19.', '5.49.246.18', '2026-08-09 23:20:44'),
(37, 2, 'Incrémentation Rapide', 'Mise à jour de la carte ID 60 (\'The Originals\') : Épisode passé à 20.', '5.49.246.18', '2026-08-10 16:49:35'),
(38, 5, 'Création Carte', 'Création de la carte ID 78 (\'Soy Luna\'). Visibilité initiale: Privée.', '5.49.246.18', '2026-08-10 20:37:29'),
(39, 5, 'Mise à jour Carte', 'Modification de la carte ID 78 (\'Soy Luna\'). Visibilité : Privée.', '5.49.246.18', '2026-08-10 20:38:06'),
(40, 5, 'Mise à jour Carte', 'Modification de la carte ID 78 (\'Soy Luna\'). Visibilité : Privée.', '5.49.246.18', '2026-08-10 20:38:36'),
(41, 2, 'Mise à jour Carte', 'Modification de la carte ID 57 (\'Mushoku Tensei\'). Visibilité : Privée.', '5.49.246.18', '2026-08-10 21:04:32'),
(42, 2, 'Soumission Draft', 'L\'utilisateur a proposé une modification pour la carte publique ID 28 (\'ClipDrop\').', '5.49.246.18', '2026-08-10 21:09:13'),
(43, 2, 'Maintenance : Remplacement en masse', 'Migration du domaine \'clipdrop.co\' vers \'	clipdrop.co\' appliquée sur 1 carte(s).', '5.49.246.18', '2026-08-10 21:09:32'),
(44, 2, 'Maintenance Système', 'Scan de liens en arrière-plan : 30 domaines uniques testés pour 52 cartes. 1 carte(s) impactée(s).', '5.49.246.18', '2026-08-10 21:10:01'),
(45, 2, 'Modération : Approbation Draft', 'Validation du Draft ID 1. Les données de la carte publique ID 28 (\'ClipDrop\') ont été écrasées avec succès.', '5.49.246.18', '2026-08-10 21:10:19'),
(46, 2, 'Création Carte', 'Création de la carte ID 79 (\'Oppenheimer\'). Visibilité initiale: Privée.', '5.49.246.18', '2026-08-10 21:11:30'),
(47, 5, 'Incrémentation Rapide', 'Mise à jour de la carte ID 78 (\'Soy Luna\') : Épisode passé à 3.', '5.49.246.18', '2026-08-10 21:22:43'),
(48, 5, 'Incrémentation Rapide', 'Mise à jour de la carte ID 78 (\'Soy Luna\') : Épisode passé à 4.', '5.49.246.18', '2026-08-10 21:22:46'),
(49, 5, 'Mise à jour Carte', 'Modification de la carte ID 78 (\'Soy Luna\'). Visibilité : Privée.', '5.49.246.18', '2026-08-10 21:25:02'),
(50, 2, 'Mise à jour Carte', 'Modification de la carte ID 79 (\'Oppenheimer\'). Visibilité : Privée.', '5.49.246.18', '2026-08-10 21:32:07'),
(51, 2, 'Incrémentation Rapide', 'Mise à jour de la carte ID 59 (\'Vampire Diaries\') : Épisode passé à 20.', '5.49.246.18', '2026-08-10 21:38:38'),
(52, 2, 'Incrémentation Rapide', 'Mise à jour de la carte ID 60 (\'The Originals\') : Épisode passé à 21.', '5.49.246.18', '2026-08-10 22:22:45'),
(53, 5, 'Mise à jour Carte', 'Modification de la carte ID 78 (\'Soy Luna\'). Visibilité : Privée.', '5.49.246.18', '2026-08-11 00:02:14'),
(54, 5, 'Création Carte', 'Création de la carte ID 80 (\'The Rookie : Le Flic de Los Angeles\'). Visibilité initiale: Privée.', '5.49.246.18', '2026-08-11 00:05:24'),
(55, 5, 'Création Carte', 'Création de la carte ID 81 (\'Violetta\'). Visibilité initiale: Privée.', '5.49.246.18', '2026-08-11 00:09:56'),
(56, 5, 'Mise à jour Carte', 'Modification de la carte ID 78 (\'Soy Luna\'). Visibilité : Privée.', '5.49.246.18', '2026-08-11 08:36:07'),
(57, 2, 'Incrémentation Rapide', 'Mise à jour de la carte ID 59 (\'Vampire Diaries\') : Épisode passé à 21.', '5.49.246.18', '2026-08-11 21:07:01'),
(58, 2, 'Incrémentation Rapide', 'Mise à jour de la carte ID 60 (\'The Originals\') : Épisode passé à 22.', '5.49.246.18', '2026-08-11 21:53:20'),
(59, 2, 'Mise à jour Carte', 'Modification de la carte ID 60 (\'The Originals\'). Visibilité : Privée.', '5.49.246.18', '2026-08-11 22:36:07'),
(60, 2, 'Mise à jour Carte', 'Modification de la carte ID 60 (\'The Originals\'). Visibilité : Privée.', '5.49.246.18', '2026-08-11 22:38:23'),
(61, 2, 'Mise à jour Carte', 'Modification de la carte ID 60 (\'The Originals\'). Visibilité : Privée.', '5.49.246.18', '2026-08-11 22:38:36'),
(62, 2, 'Mise à jour Carte', 'Modification de la carte ID 60 (\'The Originals\'). Visibilité : Privée.', '5.49.246.18', '2026-08-11 23:36:37'),
(63, 2, 'Création Carte', 'Création de la carte ID 82 (\'Percy Jackson et les Olympiens\'). Visibilité initiale: Privée.', '5.49.246.18', '2026-08-11 23:49:07'),
(64, 2, 'Création Carte', 'Création de la carte ID 83 (\'The Walking Dead : Dead City\'). Visibilité initiale: Privée.', '5.49.246.18', '2026-08-11 23:50:57'),
(65, 2, 'Création Carte', 'Création de la carte ID 84 (\'The Walking Dead : Daryl Dixon\'). Visibilité initiale: Privée.', '5.49.246.18', '2026-08-11 23:53:28'),
(66, 2, 'Création Carte', 'Création de la carte ID 85 (\'Under the Dome\'). Visibilité initiale: Privée.', '5.49.246.18', '2026-08-11 23:54:39'),
(67, 2, 'Mise à jour Carte', 'Modification de la carte ID 83 (\'The Walking Dead : Dead City\'). Visibilité : Privée.', '5.49.246.18', '2026-08-11 23:59:27'),
(68, 2, 'Mise à jour Carte', 'Modification de la carte ID 83 (\'The Walking Dead : Dead City\'). Visibilité : Privée.', '5.49.246.18', '2026-08-12 00:00:15'),
(69, 2, 'Mise à jour Carte', 'Modification de la carte ID 84 (\'The Walking Dead : Daryl Dixon\'). Visibilité : Privée.', '5.49.246.18', '2026-08-12 00:00:24'),
(70, 2, 'Mise à jour Carte', 'Modification de la carte ID 83 (\'The Walking Dead : Dead City\'). Visibilité : Privée.', '5.49.246.18', '2026-08-12 00:01:00'),
(71, 2, 'Mise à jour Carte', 'Modification de la carte ID 85 (\'Under the Dome\'). Visibilité : Privée.', '5.49.246.18', '2026-08-12 00:01:51'),
(72, 2, 'Création Carte', 'Création de la carte ID 86 (\'Saw II\'). Visibilité initiale: Privée.', '5.49.246.18', '2026-08-12 00:03:10'),
(73, 2, 'Mise à jour Carte', 'Modification de la carte ID 79 (\'Oppenheimer\'). Visibilité : Privée.', '5.49.246.18', '2026-08-12 00:04:16'),
(74, 2, 'Création Carte', 'Création de la carte ID 87 (\'Saw III\'). Visibilité initiale: Privée.', '5.49.246.18', '2026-08-12 00:04:55'),
(75, 2, 'Création Carte', 'Création de la carte ID 88 (\'Saw IV\'). Visibilité initiale: Privée.', '5.49.246.18', '2026-08-12 00:05:29'),
(76, 2, 'Création Carte', 'Création de la carte ID 89 (\'Saw V\'). Visibilité initiale: Privée.', '5.49.246.18', '2026-08-12 00:06:03'),
(77, 2, 'Création Carte', 'Création de la carte ID 90 (\'Saw VI\'). Visibilité initiale: Privée.', '5.49.246.18', '2026-08-12 00:06:56'),
(78, 5, 'Mise à jour Carte', 'Modification de la carte ID 78 (\'Soy Luna\'). Visibilité : Privée.', '5.49.246.18', '2026-08-12 00:07:21'),
(79, 2, 'Création Carte', 'Création de la carte ID 91 (\'Saw 3D : Chapitre final\'). Visibilité initiale: Privée.', '5.49.246.18', '2026-08-12 00:07:51'),
(80, 2, 'Création Carte', 'Création de la carte ID 92 (\'Jigsaw\'). Visibilité initiale: Privée.', '5.49.246.18', '2026-08-12 00:08:23'),
(81, 2, 'Création Carte', 'Création de la carte ID 93 (\'Spirale : L\'Héritage de Saw\'). Visibilité initiale: Privée.', '5.49.246.18', '2026-08-12 00:09:18'),
(82, 2, 'Création Carte', 'Création de la carte ID 94 (\'Saw X\'). Visibilité initiale: Privée.', '5.49.246.18', '2026-08-12 00:10:12'),
(83, 2, 'Création Carte', 'Création de la carte ID 95 (\'La Planète des singes : L\'Affrontement\'). Visibilité initiale: Privée.', '5.49.246.18', '2026-08-12 00:12:27'),
(84, 2, 'Reorganisation', 'L\'utilisateur a modifié l\'ordre d\'affichage de 11 carte(s).', '5.49.246.18', '2026-08-12 00:12:29'),
(85, 2, 'Reorganisation', 'L\'utilisateur a modifié l\'ordre d\'affichage de 11 carte(s).', '5.49.246.18', '2026-08-12 00:12:32'),
(86, 2, 'Création Carte', 'Création de la carte ID 96 (\'La Planète des singes : Suprématie\'). Visibilité initiale: Privée.', '5.49.246.18', '2026-08-12 00:13:07'),
(87, 2, 'Reorganisation', 'L\'utilisateur a modifié l\'ordre d\'affichage de 12 carte(s).', '5.49.246.18', '2026-08-12 00:13:11'),
(88, 2, 'Reorganisation', 'L\'utilisateur a modifié l\'ordre d\'affichage de 12 carte(s).', '5.49.246.18', '2026-08-12 00:13:13'),
(89, 2, 'Création Carte', 'Création de la carte ID 97 (\'La Planète des singes : Le Nouveau Royaume\'). Visibilité initiale: Privée.', '5.49.246.18', '2026-08-12 00:13:46'),
(90, 2, 'Reorganisation', 'L\'utilisateur a modifié l\'ordre d\'affichage de 13 carte(s).', '5.49.246.18', '2026-08-12 00:13:51'),
(91, 2, 'Reorganisation', 'L\'utilisateur a modifié l\'ordre d\'affichage de 13 carte(s).', '5.49.246.18', '2026-08-12 00:13:53'),
(92, 2, 'Reorganisation', 'L\'utilisateur a modifié l\'ordre d\'affichage de 13 carte(s).', '5.49.246.18', '2026-08-12 00:13:55'),
(93, 2, 'Création Carte', 'Création de la carte ID 98 (\'His Dark Materials : À la croisée des mondes\'). Visibilité initiale: Privée.', '5.49.246.18', '2026-08-12 00:21:46'),
(94, 2, 'Reorganisation', 'L\'utilisateur a modifié l\'ordre d\'affichage de 9 carte(s).', '5.49.246.18', '2026-08-12 00:21:54'),
(95, 2, 'Reorganisation', 'L\'utilisateur a modifié l\'ordre d\'affichage de 9 carte(s).', '5.49.246.18', '2026-08-12 00:21:59'),
(96, 5, 'Mise à jour Carte', 'Modification de la carte ID 78 (\'Soy Luna\'). Visibilité : Privée.', '5.49.246.18', '2026-08-12 14:01:27'),
(97, 5, 'Mise à jour Carte', 'Modification de la carte ID 78 (\'Soy Luna\'). Visibilité : Privée.', '5.49.246.18', '2026-08-12 14:24:47'),
(98, 5, 'Mise à jour Carte', 'Modification de la carte ID 78 (\'Soy Luna\'). Visibilité : Privée.', '5.49.246.18', '2026-08-12 14:25:20'),
(99, 5, 'Mise à jour Carte', 'Modification de la carte ID 78 (\'Soy Luna\'). Visibilité : Privée.', '5.49.246.18', '2026-08-12 15:07:18'),
(100, 5, 'Mise à jour Carte', 'Modification de la carte ID 78 (\'Soy Luna\'). Visibilité : Privée.', '5.49.246.18', '2026-08-12 17:04:46'),
(101, 1, 'Modification Profil', 'Mise à jour du compte ID 5. Nouveau groupe de sécurité assigné: [user].', '104.28.42.22', '2026-08-13 19:12:42'),
(102, 2, 'Incrémentation Rapide', 'Mise à jour de la carte ID 59 (\'Vampire Diaries\') : Épisode passé à 22.', '5.49.246.18', '2026-08-13 21:32:17'),
(103, 5, 'Mise à jour Carte', 'Modification de la carte ID 78 (\'Soy Luna\'). Visibilité : Privée.', '79.83.33.206', '2026-08-13 22:49:16'),
(104, 5, 'Incrémentation Rapide', 'Mise à jour de la carte ID 78 (\'Soy Luna\') : Épisode passé à 20.', '79.83.33.206', '2026-08-13 22:53:51'),
(105, 2, 'Incrémentation Rapide', 'Mise à jour de la carte ID 60 (\'The Originals\') : Épisode passé à 23.', '79.95.127.52', '2026-08-13 23:16:01'),
(106, 2, 'Mise à jour Carte', 'Modification de la carte ID 60 (\'The Originals\'). Visibilité : Privée.', '79.95.127.52', '2026-08-13 23:16:52'),
(107, 5, 'Incrémentation Rapide', 'Mise à jour de la carte ID 78 (\'Soy Luna\') : Épisode passé à 21.', '5.49.246.18', '2026-08-14 19:02:28'),
(108, 2, 'Mise à jour Carte', 'Modification de la carte ID 59 (\'Vampire Diaries\'). Visibilité : Privée.', '5.49.246.18', '2026-08-14 23:01:15'),
(109, 2, 'Incrémentation Rapide', 'Mise à jour de la carte ID 59 (\'Vampire Diaries\') : Épisode passé à 2.', '5.49.246.18', '2026-08-15 01:06:37'),
(110, 5, 'Mise à jour Carte', 'Modification de la carte ID 78 (\'Soy Luna\'). Visibilité : Privée.', '5.49.246.18', '2026-08-15 12:17:27'),
(111, 5, 'Création Carte', 'Création de la carte ID 99 (\'Kally\'s Mashup, la voix de la pop\'). Visibilité initiale: Privée.', '5.49.246.18', '2026-08-15 12:49:04'),
(112, 5, 'Mise à jour Carte', 'Modification de la carte ID 99 (\'Kally\'s Mashup, la voix de la pop\'). Visibilité : Privée.', '5.49.246.18', '2026-08-15 12:49:42'),
(113, 5, 'Mise à jour Carte', 'Modification de la carte ID 99 (\'Kally\'s Mashup, la voix de la pop\'). Visibilité : Privée.', '5.49.246.18', '2026-08-15 13:01:44'),
(114, 5, 'Incrémentation Rapide', 'Mise à jour de la carte ID 78 (\'Soy Luna\') : Épisode passé à 21.', '5.49.246.18', '2026-08-15 13:03:17'),
(115, 5, 'Incrémentation Rapide', 'Mise à jour de la carte ID 78 (\'Soy Luna\') : Épisode passé à 22.', '5.49.246.18', '2026-08-15 15:13:58'),
(116, 2, 'Incrémentation Rapide', 'Mise à jour de la carte ID 60 (\'The Originals\') : Épisode passé à 2.', '5.49.246.18', '2026-08-15 16:03:08'),
(117, 5, 'Création Carte', 'Création de la carte ID 100 (\'Le Prince et moi\'). Visibilité initiale: Privée.', '5.49.246.18', '2026-08-16 00:05:55'),
(118, 5, 'Mise à jour Carte', 'Modification de la carte ID 100 (\'Le Prince et moi\'). Visibilité : Privée.', '5.49.246.18', '2026-08-16 00:06:26'),
(119, 5, 'Mise à jour Carte', 'Modification de la carte ID 100 (\'Le Prince et moi\'). Visibilité : Privée.', '5.49.246.18', '2026-08-16 00:07:21'),
(120, 2, 'Mise à jour Carte', 'Modification de la carte ID 55 (\'BLACK TORCH\'). Visibilité : Privée.', '5.49.246.18', '2026-08-16 17:46:22'),
(121, 2, 'Mise à jour Carte', 'Modification de la carte ID 34 (\'Yuru no Tsugai\'). Visibilité : Privée.', '5.49.246.18', '2026-08-16 18:11:12'),
(122, 5, 'Mise à jour Carte', 'Modification de la carte ID 100 (\'Le Prince et moi\'). Visibilité : Privée.', '5.49.246.18', '2026-08-17 12:44:25'),
(123, 5, 'Création Carte', 'Création de la carte ID 101 (\'Cendrillon\'). Visibilité initiale: Privée.', '5.49.246.18', '2026-08-17 13:02:19'),
(124, 5, 'Suppression Carte', 'Suppression de la carte ID 99 (\'Kally\'s Mashup, la voix de la pop\').', '5.49.246.18', '2026-08-17 13:11:12'),
(125, 2, 'Maintenance Système', 'Scan de liens en arrière-plan : 30 domaines uniques testés pour 72 cartes. 1 carte(s) impactée(s).', '5.49.246.18', '2026-08-17 21:44:08'),
(126, 2, 'Mise à jour Carte', 'Modification de la carte ID 57 (\'Mushoku Tensei\'). Visibilité : Privée.', '5.49.246.18', '2026-08-17 22:08:12'),
(127, 2, 'Incrémentation Rapide', 'Mise à jour de la carte ID 59 (\'Vampire Diaries\') : Épisode passé à 3.', '5.49.246.18', '2026-08-17 22:09:41'),
(128, 2, 'Mise à jour Carte', 'Modification de la carte ID 61 (\'Ordre de diffusion TVD & The originals\'). Visibilité : Privée.', '5.49.246.18', '2026-08-17 22:09:46'),
(129, 5, 'Mise à jour Carte', 'Modification de la carte ID 101 (\'Cendrillon\'). Visibilité : Privée.', '5.49.246.18', '2026-08-18 21:41:49'),
(130, 5, 'Mise à jour Carte', 'Modification de la carte ID 101 (\'Cendrillon\'). Visibilité : Privée.', '5.49.246.18', '2026-08-18 21:46:35'),
(131, 5, 'Mise à jour Carte', 'Modification de la carte ID 101 (\'Cendrillon\'). Visibilité : Privée.', '5.49.246.18', '2026-08-18 22:36:34'),
(132, 5, 'Mise à jour Carte', 'Modification de la carte ID 101 (\'Cendrillon\'). Visibilité : Privée.', '5.49.246.18', '2026-08-18 22:36:47'),
(133, 5, 'Mise à jour Carte', 'Modification de la carte ID 100 (\'Le Prince et moi\'). Visibilité : Privée.', '5.49.246.18', '2026-08-18 22:37:05'),
(134, 5, 'Mise à jour Carte', 'Modification de la carte ID 100 (\'Le Prince et moi\'). Visibilité : Privée.', '5.49.246.18', '2026-08-18 23:09:11'),
(135, 2, 'Incrémentation Rapide', 'Mise à jour de la carte ID 60 (\'The Originals\') : Épisode passé à 3.', '5.49.246.18', '2026-08-19 15:24:25');

TRUNCATE TABLE `auth_groups_users`;
INSERT INTO `auth_groups_users` VALUES
(1, 1, 'superadmin', '2026-04-11 17:01:16'),
(3, 3, 'user', '2026-04-30 14:13:10'),
(6, 2, 'admin', '2026-05-17 22:56:01'),
(7, 4, 'user', '2026-05-29 22:30:45'),
(8, 5, 'user', '2026-06-17 20:17:27');

TRUNCATE TABLE `auth_identities`;
INSERT INTO `auth_identities` VALUES
(1, 1, 'email_password', NULL, 'titisland@gmail.com', '$2y$12$fQQGOXUFz0cpRjQv6KEQKunD.NyN.foC2QF30zzcvm47qdRIHtW26', NULL, NULL, 0, '2026-08-17 01:31:37', '2026-04-11 17:01:16', '2026-08-17 01:31:37'),
(2, 2, 'email_password', NULL, 'mathisfrances11@gmail.com', '$2y$12$MCy7X0OR/J0IAycNYTkrwOGAi2UygpbYVtakTyTZEOGJvE6b60BSa', NULL, NULL, 0, '2026-08-17 16:26:47', '2026-04-11 17:02:38', '2026-08-17 16:26:47'),
(3, 2, 'magic-link', NULL, 'e3fc52dba64bd3b1958f', NULL, '2026-04-30 15:11:11', NULL, 0, NULL, '2026-04-30 14:11:11', '2026-04-30 14:11:11'),
(4, 3, 'email_password', NULL, 'hugophilippe26@gmail.com', '$2y$12$9rKoqgP6n7E1vosN4EUWpu5L/lPLkyb9GrDKmIqJxQX9pzG/7drPG', NULL, NULL, 0, NULL, '2026-04-30 14:13:09', '2026-04-30 14:13:10'),
(5, 4, 'email_password', NULL, 'mathisfrances111@gmail.com', '$2y$12$q4NTrCKkkMj3kINlncokHuDcbgPaDT2SDDooXI0R5asUjUwjK1pem', NULL, NULL, 0, '2026-07-07 14:51:37', '2026-05-29 22:30:44', '2026-07-07 14:51:37'),
(6, 5, 'email_password', NULL, 'ambrefrances1@gmail.com', '$2y$12$AyjlWNvzet1MU5XhMJBDdeMjd9oGgFhKSGjIbtn3R25TWPeFUTfTG', NULL, NULL, 0, '2026-08-13 20:31:03', '2026-06-17 20:17:27', '2026-08-13 20:31:03');

TRUNCATE TABLE `auth_logins`;
INSERT INTO `auth_logins` VALUES
(1, '5.49.246.18', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'email_password', 'mathisfrances11@gmail.com', 2, '2026-08-01 18:24:19', 1),
(2, '5.49.246.18', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'email_password', 'mathisfrances11@gmail.com', 2, '2026-08-03 17:10:45', 1),
(3, '5.49.246.18', 'Mozilla/5.0 (SMART-TV; Linux; Tizen 8.0) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/7.0 Chrome/120.0.6099.5 TV Safari/537.36', 'email_password', 'mathisfrances11@gmail.com', 2, '2026-08-04 12:07:06', 1),
(4, '172.226.148.7', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/27.0 Safari/605.1.15', 'email_password', 'mathisfrances11@gmail.com', 2, '2026-08-04 12:08:18', 1),
(5, '104.28.42.14', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/27.0 Safari/605.1.15', 'email_password', 'mathisfrances11@gmail.com', 2, '2026-08-05 13:00:55', 1),
(6, '5.49.246.18', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'email_password', 'mathisfrances11@gmail.com', 2, '2026-08-06 21:57:49', 1),
(7, '5.49.246.18', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', 'email_password', 'ambrefrances1@gmail.com', 5, '2026-08-09 20:02:24', 1),
(8, '5.49.246.18', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'email_password', 'mathisfrances11@gmail.com', 2, '2026-08-11 13:31:14', 1),
(9, '5.49.246.18', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Mobile Safari/537.36', 'email_password', 'mathisfrances11@gmail.com', 2, '2026-08-11 20:22:03', 1),
(10, '104.28.42.22', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/27.0 Safari/605.1.15', 'email_password', 'titisland@gmail.com', 1, '2026-08-13 19:11:42', 1),
(11, '5.49.246.18', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', 'email_password', 'ambrefrances1@gmail.com', 5, '2026-08-13 19:18:53', 1),
(12, '5.49.246.18', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', 'email_password', 'ambrefrances1@gmail.com', NULL, '2026-08-13 20:28:18', 0),
(13, '5.49.246.18', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', 'email_password', 'ambrefrances1@gmail.com', NULL, '2026-08-13 20:29:15', 0),
(14, '5.49.246.18', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', 'email_password', 'ambrefrances1@gmail.com', NULL, '2026-08-13 20:29:38', 0),
(15, '5.49.246.18', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', 'email_password', 'ambrefrances1@gmail.com', 5, '2026-08-13 20:31:03', 1),
(16, '5.49.246.18', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'email_password', 'mathisfrances11@gmail.com', 2, '2026-08-16 16:06:06', 1),
(17, '5.49.246.18', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'email_password', 'mathisfrances11@gmail.com', 2, '2026-08-16 16:06:14', 1),
(18, '5.49.246.18', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'email_password', 'titisland@gmail.com', 1, '2026-08-16 16:06:36', 1),
(19, '5.49.246.18', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'email_password', 'mathisfrances11@gmail.com', 2, '2026-08-16 17:04:34', 1),
(20, '140.248.41.25', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/27.0 Safari/605.1.15', 'email_password', 'mathisfrances11@gmail.com', 2, '2026-08-17 01:30:15', 1),
(21, '172.226.148.5', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/27.0 Safari/605.1.15', 'email_password', 'mathisfrances11@gmail.com', 2, '2026-08-17 01:31:04', 1),
(22, '146.75.166.48', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/27.0 Safari/605.1.15', 'email_password', 'titisland@gmail.com', 1, '2026-08-17 01:31:37', 1),
(23, '146.75.166.48', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/27.0 Safari/605.1.15', 'email_password', 'mathisfrances11@gmail.com', 2, '2026-08-17 01:32:03', 1),
(24, '5.49.246.18', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'email_password', 'mathisfrances11@gmail.com', 2, '2026-08-17 16:26:47', 1);

TRUNCATE TABLE `auth_permissions_users`;
TRUNCATE TABLE `auth_remember_tokens`;
INSERT INTO `auth_remember_tokens` VALUES
(11, 'd796e389f82d77e1d2afffbf', '25b5a64aff3b6691a18b26d5adc2ac621e9ae287eb54a24c2a793627716dad33', 5, '2026-09-17 21:31:47', '2026-08-13 19:18:53', '2026-08-18 21:31:47'),
(12, 'c61b0018c5001c2bdbe8bc7c', '37c71e0625052e5482e97b03526f7eee035ac346fde9af0372ca164b2db0a400', 5, '2026-09-17 21:41:24', '2026-08-13 20:31:03', '2026-08-18 21:41:24'),
(20, '463d8a45440392c66529730a', 'ea62e2cec36a4dfa3047adaafc607536558e085116b00653b011cc9c2e620253', 2, '2026-09-16 01:32:03', '2026-08-17 01:32:03', '2026-08-17 01:32:03'),
(21, '890d7c027201be17a190c51b', 'c9f47bfb177143dca43cc958dbeba604123f83f083867a4cea7850b7e3763351', 2, '2026-09-18 15:23:08', '2026-08-17 16:26:47', '2026-08-19 15:23:08');

TRUNCATE TABLE `auth_token_logins`;
TRUNCATE TABLE `cron_logs`;
INSERT INTO `cron_logs` VALUES
(1, 25, 'Audio To Text', 'https://editor.flixier.com/transcribe?fx_source=search&lang=en&fx_campaign=convert-audio-to-text&fx_medium=tools', 0, 'check_dead_links', '2026-08-17 21:43:48');

TRUNCATE TABLE `division`;
INSERT INTO `division` VALUES
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
INSERT INTO `header` VALUES
(1, 'Animés & Mangas'),
(2, 'Films & Séries'),
(3, 'Liens'),
(5, 'Outils');

TRUNCATE TABLE `item`;
INSERT INTO `item` VALUES
(1, 2, 0, 1, 'One Piece', 'En cours', 'https://image.tmdb.org/t/p/w500/l5menwH7JjOBbXjoftYdwMmsqmT.jpg', 'https://voir-anime.to/anime/one-piece/one-piece-{ep4}-vostfr/', 'ok', 'Une aventure en haute mer légendaire et unique en son genre. Monkey D. Luffy est un jeune aventurier...', '1141', 1, 8, NULL, NULL, NULL, '2026-08-03 22:35:54'),
(2, 2, 0, 2, 'One Piece', 'Aucun', 'https://www.myutaku.com/media/mangas/12.jpg', 'https://www.scan-vf.net/one_piece/chapitre-{ep}', 'ok', 'Une aventure en haute mer légendaire et unique en son genre. Monkey D. Luffy est un jeune aventurier...', '1191', 0, 0, '2026-08-14 18:00:00', NULL, NULL, '2026-08-09 18:37:52'),
(5, 2, 0, 1, 'Frieren', 'Aucun', 'https://image.tmdb.org/t/p/w500/j8K7vgF3Kp5T6EwJvez9B4it6CB.jpg', 'https://voir-anime.to/anime/sousou-no-frieren-{s}/sousou-no-frieren-{s}-{ep2}-vostfr/', 'ok', 'L’elfe Frieren a vaincu le roi des démons aux côtés du groupe mené par le jeune héros Himmel. Après ...', '3', 2, 6, NULL, NULL, NULL, '2026-08-03 22:35:54'),
(6, 2, 0, 1, 'Wind Breaker', 'Aucun', 'https://cdn.myanimelist.net/images/anime/1526/148873l.jpg', 'https://voir-anime.to/anime/wind-breaker-{s}/wind-breaker-{s}-{ep2}-vostfr/', 'ok', 'Ever since Haruka Sakura joined Furin High School, where its students call themselves Bofurin and protect the town of Makochi, he has gained new friends despite his initial skepticism. Now starting to learn how to fight alongside his classmates an...', '12', 2, 4, NULL, '2026-07-16 19:28:34', NULL, '2026-07-16 19:28:34'),
(7, 2, 0, 1, 'To Your Eternity', 'Aucun', 'https://image.tmdb.org/t/p/w500/bohMYRVSIG68md0zQobyWbV4S8e.jpg', 'https://voir-anime.to/anime/fumetsu-no-anata-e-{s}/fumetsu-no-anata-e-{s}-{ep2}-vostfr/', 'ok', 'Un garçon solitaire errant dans les régions arctiques de l\'Amérique du Nord rencontre un loup. Tous ...', '9', 3, 9, NULL, NULL, NULL, '2026-08-03 22:35:54'),
(8, 2, 0, 1, 'Bleach', 'En pause', 'https://www.myutaku.com/media/anime/poster/74796.jpg', 'https://franime.fr/anime/bleach?s={s}&ep={ep}&lang=vo&anime_id=244', 'ok', 'Adolescent de quinze ans, Ichigo Kurosaki possède un don particulier : celui de voir les esprits. Un...', '155', 1, 4, NULL, NULL, NULL, '2026-08-03 23:03:34'),
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
(24, 2, 0, 1, 'Noble Reincarnation', 'En pause', 'https://image.tmdb.org/t/p/w500/ggxUYlw7a3eVegnXDv8aCDiLccJ.jpg', 'https://voir-anime.to/anime/noble-reincarnation-born-blessed-so-ill-obtain-ultimate-power/noble-reincarnation-born-blessed-so-ill-obtain-ultimate-power-{ep2}-vostfr/', 'ok', 'En tant que treizième prince de la famille royale, Noah a toujours mené une vie paisible, loin des i...', '2', 1, 5, NULL, NULL, NULL, '2026-08-03 22:35:54'),
(25, 1, 1, 10, 'Audio To Text', 'Aucun', NULL, 'https://editor.flixier.com/transcribe?fx_source=search&lang=en&fx_campaign=convert-audio-to-text&fx_medium=tools', 'dead', 'Convertir les fichiers audio en textes', NULL, NULL, 0, NULL, NULL, NULL, '2026-08-17 21:44:00'),
(26, 1, 1, 10, 'Bootstrap Icons', 'Aucun', NULL, 'https://icons.getbootstrap.com', 'ok', 'Bibliothèque d\'icônes', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(27, 1, 1, 5, 'Prime Video', 'Aucun', 'https://cdn.prod.website-files.com/63f46dc8ada663b2260ad042/651e7514b3a51ee790163981_Amazon%20-%20Prime%20Video%20(2).jpg', 'https://www.primevideo.com/', 'ok', '', '', 0, 1, NULL, NULL, NULL, '2026-07-07 19:48:20'),
(28, 1, 1, 10, 'ClipDrop', 'Aucun', '', 'https://clipdrop.co/', 'ok', 'Administrer des images', NULL, 0, 0, NULL, NULL, NULL, '2026-08-17 21:44:01'),
(30, 1, 1, 10, 'Durable', 'Aucun', '', 'https://app.durable.co/dashboard', 'ok', 'Générer des sites web', '', 0, 0, NULL, NULL, NULL, NULL),
(31, 1, 1, 10, 'Fotor', 'Aucun', NULL, 'https://www.fotor.com/', 'ok', 'conceptions et éditions d\'images', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(32, 1, 1, 10, 'Krea.ai', 'Aucun', NULL, 'https://www.krea.ai/apps/image/realtime', 'ok', 'Générer des Images', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(33, 1, 1, 10, 'obfuscator', 'Aucun', NULL, 'https://obfuscator.io/', 'ok', 'crypter les scripts javascripts', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(34, 2, 0, 1, 'Yuru no Tsugai', 'En cours', 'https://image.tmdb.org/t/p/w500/mNqW2jnAogZa0nJ94q1LUum8Hos.jpg', 'https://voir-anime.to/anime/yomi-no-tsugai/daemons-of-the-shadow-realm-{ep2}-vostfr/', 'ok', 'Yuru, le chasseur, vit séparé de sa sœur jumelle Asa, enfermée dans une prison pour satisfaire un ri...', '20', 1, 3, '2026-08-22 15:00:00', NULL, NULL, '2026-08-16 18:11:12'),
(35, 2, 0, 1, 'Classroom of the Elite', 'En cours', 'https://image.tmdb.org/t/p/w500/mmhx3dImdsfYpcFm3J1tlQt5IRN.jpg', 'https://franime.fr/anime/classroom-of-the-elite?s={s}&ep={ep}&lang=vo&anime_id=13503', 'ok', 'Kiyotaka Ayanokôji intègre le prestigieux lycée de haut niveau de Tokyo où, une fois le diplôme en poche, quasiment 100 % des élèves trouvent un travail ou sont reçus à l’université. Pas de chance, il se retrouve dans la 2de D où finissent tous le...', '9', 4, 0, NULL, NULL, NULL, '2026-08-03 22:35:54'),
(36, 2, 0, 1, 'Re:ZERO', 'Aucun', 'https://image.tmdb.org/t/p/w500/ccG0ZfXOQ0834bIus4SwZrXtkyM.jpg', 'https://voir-anime.to/anime/rezero-kara-hajimeru-isekai-seikatsu-s{s}/re-zero-kara-hajimeru-isekai-seikatsu-saison-{s}-{ep2}-vostfr/', 'ok', 'Subaru Natsuki a basculé dans un monde fantastique où il fait la connaissance d’Émilia, une jeune fille aux longs cheveux d’argent qu’il jure de protéger. Malheureusement, le jeune homme ne résiste pas longtemps en se faisant tuer rapidement. Pour...', '1', 3, 13, NULL, NULL, NULL, '2026-08-03 22:35:54'),
(37, 2, 0, 1, 'Dr. STONE', 'À voir', 'https://image.tmdb.org/t/p/w500/dLlnzbDCblBXcJqFLXyvN43NIwp.jpg', 'https://voir-anime.to/anime/dr-stone-{s}-science-future/dr-stone-{s}-{ep2}-vostfr/', 'ok', 'Plusieurs milliers d\'années après un mystérieux phénomène qui a transformé toute l\'humanité en pierre, Senku, un lycéen extrêmement intelligent et animé par un esprit scientifique, se réveille. Face à ce monde figé, où toutes les civilisations se ...', '1', 4, 12, NULL, NULL, NULL, '2026-08-03 22:35:54'),
(38, 1, 1, 10, 'Gemini', 'Aucun', '', 'https://gemini.google.com/app?hl=fr', 'ok', '', '', 0, 0, NULL, NULL, NULL, NULL),
(39, 2, 0, 11, 'Suivi des comptes', 'Aucun', '', 'https://summury.22web.org/suivi-comptes/index.php', 'ok', '', '', 0, 0, NULL, NULL, NULL, '2026-07-25 12:51:36'),
(40, 2, 0, 2, 'Jujutsu Kaisen Modulo', 'À voir', 'https://www.myutaku.com/media/mangas/88950.jpg?1757883349', 'https://www.scan-vf.net/jujutsu-kaisen-modulo/chapitre-{ep}', 'ok', 'Souffrance, regrets, humiliations... les sentiments négatifs que ressentent les humains se transform...', '5', 0, 2, NULL, NULL, NULL, NULL),
(41, 2, 1, 11, 'LivesPalmes', 'Aucun', '', 'https://livepalmes.web.app/', 'ok', 'LivePalmes (FFESSM) : suivez la nage avec palmes en direct, consultez les records et les archives.', '', NULL, 1, NULL, NULL, NULL, '2026-07-25 12:51:36'),
(43, 2, 0, 1, 'Les Carnets de l\'apothicaire', 'Aucun', 'https://image.tmdb.org/t/p/w500/47pSay5Ao7SFeyQBZVkW5ifyhAZ.jpg', 'https://voir-anime.to/anime/the-apothecary-diaries/the-apothecary-diaries-{ep2}-vostfr/', 'ok', 'Formée dès son plus jeune âge par son père apothicaire, Mao Mao est un jour vendue comme servante au...', '1', 1, 14, NULL, NULL, NULL, '2026-08-03 22:35:54'),
(47, 2, 0, 11, 'Liens très privés', 'Aucun', '', 'https://prive.titiss.space', 'ok', '', '', 0, 2, NULL, NULL, NULL, '2026-07-25 12:51:36'),
(52, 2, 0, 2, 'Black Clover', 'À voir', 'https://image.tmdb.org/t/p/w500/p3rUhlE81nWxPqpPR8F2u7a01Tl.jpg', 'https://www.scan-vf.net/black-clover/chapitre-{ep}', 'ok', 'Dans un monde régi par la magie, Yuno et Asta ont grandi ensemble avec un seul but en tête : devenir...', '356', 0, 1, NULL, NULL, NULL, NULL),
(53, 1, 1, 5, 'Nakastream', 'Aucun', '', 'https://nakastream.wiki/', 'ok', '', '', 0, 4, NULL, NULL, NULL, '2026-07-07 19:48:20'),
(54, 2, 0, 11, 'Site de troll', 'Aucun', '', 'https://mathis.likesyou.org/troll/amfs/Trouve-tu_le_site_interessant', 'ok', '', '', 0, 3, NULL, NULL, NULL, '2026-07-25 12:51:36'),
(55, 2, 0, 1, 'BLACK TORCH', 'En cours', 'https://image.tmdb.org/t/p/w500/qxPsSYAiNhFLETmFJZ0s5HWyYhr.jpg', 'https://voir-anime.to/anime/black-torch/black-torch-{ep2}-vostfr/', 'ok', 'Adolescent au grand cœur capable de communiquer avec le monde animal, Jiro est issu d\'une longue lig...', '8', 1, 2, '2026-08-22 15:00:00', NULL, NULL, '2026-08-16 17:46:22'),
(57, 2, 0, 1, 'Mushoku Tensei', 'En cours', 'https://image.tmdb.org/t/p/w500/sviEqFIPJW5gFtuYy8XyE0Uscid.jpg', 'https://voir-anime.to/anime/mushoku-tensei-{s}/mushoku-tensei-{s}-{ep2}-vostfr/', 'ok', '« Ici, je vais me transcender ! » Un anonyme de 34 ans, célibataire endurci, reclus et au chômage se...', '9', 3, 1, '2026-08-23 17:00:00', NULL, NULL, '2026-08-17 22:08:12'),
(58, 2, 0, 4, 'Game of Thrones', 'À voir', 'https://image.tmdb.org/t/p/w500/eRMfekBOnwyE9G0ffyEJIBOjX2n.jpg', 'https://nakastream.tv/player?title=Game%20of%20Thrones&id=339&poster=/eRMfekBOnwyE9G0ffyEJIBOjX2n.jpg&type=tv&season={s}&episode={ep}', 'ok', 'Il y a très longtemps, à une époque oubliée, une force a détruit l\'équilibre des saisons. Dans un pa...', '1', 1, 0, NULL, '2026-07-07 19:32:21', '2026-07-07 19:31:19', '2026-07-07 19:32:21'),
(59, 2, 0, 4, 'Vampire Diaries', 'À voir', 'https://image.tmdb.org/t/p/w500/4RHhqEdI2VV5wHp0rLmKAg9t9h6.jpg', 'https://nakastream.tv/player?title=Vampire%20Diaries&id=1434&poster=/4RHhqEdI2VV5wHp0rLmKAg9t9h6.jpg&type=tv&season={s}&episode={ep}', 'ok', 'Quatre mois après le tragique accident de voiture qui a tué leurs parents, Elena Gilbert, 17 ans, et son frère Jeremy, 15 ans, essaient encore de s\'adapter à cette nouvelle réalité. Belle et populaire, l\'adolescente poursuit ses études au Mystic F...', '3', 6, 2, NULL, NULL, '2026-07-07 19:33:46', '2026-08-17 22:09:41'),
(60, 2, 0, 4, 'The Originals', 'En cours', 'https://image.tmdb.org/t/p/w500/keJOhJXGiLL54EW6QocbyvQGquA.jpg', 'https://nakastream.tv/player?title=The%20Originals&id=1438&poster=/keJOhJXGiLL54EW6QocbyvQGquA.jpg&type=tv&season={s}&episode={ep}', 'ok', 'Le vampire originel Klaus fait son retour au Vieux Carré, un quartier français de la Nouvelle Orléans. Dans cette ville qu’il a aidé à construire quelques siècles plus tôt, il y retrouve son ancien protégé, le diabolique et charismatique Marcel. D...', '3', 2, 1, NULL, NULL, '2026-07-07 19:35:06', '2026-08-19 15:24:25'),
(61, 2, 0, 4, 'Ordre de diffusion TVD & The originals', 'Aucun', '', 'https://drive.google.com/drive/folders/1fd1YxKtcuBG0xH5TnCoL2po_BS7aOYT_?usp=sharing', 'ok', 'Capture n°4', NULL, NULL, 0, NULL, NULL, '2026-07-07 19:44:52', '2026-08-17 22:09:46'),
(62, 1, 1, 5, 'Canal +', 'Aucun', '', 'https://www.canalplus.com/?from=pass', 'ok', '', '', 0, 2, NULL, NULL, '2026-07-07 19:47:56', '2026-07-07 19:48:24'),
(63, 2, 0, 4, 'The Protector', 'En cours', 'https://image.tmdb.org/t/p/w500/v3cYsLksGX1baCYtn2AQa2R5HDR.jpg', '', 'ok', '', '', 0, 0, NULL, '2026-07-09 21:22:53', '2026-07-09 21:18:46', '2026-07-09 21:22:53'),
(64, 2, 0, 3, 'Enola Holmes 3', 'Aucun', 'https://image.tmdb.org/t/p/w500/ncHImt9szlNQaNM2iY3vcgSdDp.jpg', 'https://nakastream.tv/player?title=Enola%20Holmes%203&id=8950&poster=/7kRYHH9H9PjBFwz1FprbHB2AAjI.jpg&type=movie', 'ok', 'La détective Enola Holmes poursuit ses aventures à Malte, où son projet de mariage se complique quand elle doit résoudre une périlleuse affaire liée à la disparition de Sherlock.', '', 0, 0, NULL, '2026-07-09 22:18:56', '2026-07-09 21:23:44', '2026-07-09 22:18:56'),
(65, 2, 0, 3, 'Enola Holmes 3', 'Aucun', 'https://image.tmdb.org/t/p/w500/ncHImt9szlNQaNM2iY3vcgSdDp.jpg', 'https://nakastream.tv/player?title=Enola%20Holmes%203&id=8950&poster=/7kRYHH9H9PjBFwz1FprbHB2AAjI.jpg&type=movie', 'ok', 'La détective Enola Holmes poursuit ses aventures à Malte, où son projet de mariage se complique quand elle doit résoudre une périlleuse affaire liée à la disparition de Sherlock.', NULL, NULL, 0, NULL, '2026-07-11 22:29:21', '2026-07-09 22:19:14', '2026-07-11 22:29:21'),
(66, 2, 0, 4, 'Le Protecteur d\'Istanbul', 'À voir', 'https://image.tmdb.org/t/p/w500/mj6z8wMzcYPt9pwJqHxy0Avlnum.jpg', 'https://papadustream.rentals/cat-series/drame-s/1287-le-protecteur-distanbul-c8a/{s}-saison/{ep}-episode.html', 'ok', 'Après avoir découvert ce qui le lie à un ancien ordre secret, un jeune homme de l\'Istanbul moderne entreprend de sauver la ville des griffes d\'un ennemi immortel.', '1', 4, 3, NULL, '2026-07-20 22:38:08', '2026-07-09 22:29:20', '2026-07-20 22:38:08'),
(67, 2, 0, 4, 'The Witcher', 'En cours', 'https://image.tmdb.org/t/p/w500/rhErSlk0M236rNFertVAZa9lz9S.jpg', 'https://papadustream.sarl/cat-series/aventure-s/4328-the-witcher/{s}-saison/{ep}-episode.html', 'ok', 'Le sorcier Geralt, un chasseur de monstres mutant, se bat pour trouver sa place dans un monde où les humains se révèlent souvent plus vicieux que les bêtes.', '1', 4, 4, NULL, NULL, '2026-07-11 14:18:32', '2026-08-12 00:21:59'),
(68, 2, 0, 1, 'Arifureta', 'En pause', 'https://image.tmdb.org/t/p/w500/3vwcB2MtQA1VZMCljCRSrDzNzdj.jpg', 'https://voir-anime.to/anime/arifureta-shokugyou-de-sekai-saikyou-{s}/arifureta-shokugyou-de-sekai-saikyou-{s}-{ep2}-vostfr/', 'ok', 'Hajime Nagumo, véritable souffre-douleur, se retrouve transporté avec toute sa classe dans un autre monde. Alors que ses camarades acquièrent des techniques de combat ultra puissantes, Hajime se retrouve doté d’une modeste compétence. Suite à la m...', '1', 3, 7, NULL, NULL, '2026-07-16 19:31:41', '2026-08-03 22:35:54'),
(69, 2, 0, 1, 'A Playthrough of a Certain Dude\'s VRMMO Life', 'En pause', 'https://image.tmdb.org/t/p/w500/sCLrdFdsweruRFLdN1DytcwHBZw.jpg', 'https://voir-anime.to/anime/a-playthrough-of-a-certain-dudes-vrmmo-life/a-playthrough-of-a-certain-dudes-vrmmo-life-{ep2}-vostfr/', 'ok', 'Taichi Tanaka est un Japonais ordinaire qui vient de se créer un personnage, \"Earth\", dans un tout nouveau jeu VRMMO appelé \"One More Free Life Online\" et promettant un champ d’action quasi-illimité. Dans un monde où les joueurs sont libres de déf...', '6', 1, 8, NULL, '2026-07-27 14:42:53', '2026-07-16 19:33:59', '2026-07-27 14:42:53'),
(70, 2, 0, 1, 'Villainess Level 99', 'En pause', 'https://image.tmdb.org/t/p/w500/vsTjL8hO4iSUcEx7eNxAgMxDspa.jpg', 'https://franime.fr/anime/villainess-level-99-i-may-be-the-hidden-boss-but-im-not-the-demon-lord?s={s}&ep={ep}&lang=vo&anime_id=47237', 'ok', 'Cette étudiante japonaise discrète est réincarnée dans le corps d’Eumiella Dolkness, la méchante de son otome game préféré. Aspirant toujours à une vie tranquille, elle n’est pas vraiment ravie et décide d’abandonner ses fonctions maléfiques. Jusq...', '11', 1, 10, NULL, NULL, '2026-07-16 19:35:41', '2026-08-04 22:38:39'),
(71, 2, 0, 1, 'Goblin Slayer', 'En pause', 'https://image.tmdb.org/t/p/w500/nUiT0whRDuUJKkk74L1pn8xUE2z.jpg', 'https://voir-anime.to/anime/goblin-slayer-ii/goblin-slayer-{s}-{ep2}-vostfr/', 'ok', 'Au sein de la Guilde des Aventuriers, les gobelins sont perçus comme de simples nuisibles dont l’élimination est confiée aux novices inexpérimentés. Cependant, un aventurier de rang Argent, surnommé le « Goblin Slayer », discerne la véritable natu...', '1', 2, 11, NULL, NULL, '2026-07-16 19:37:51', '2026-08-03 22:35:54'),
(72, 1, 1, 10, 'Claude Code', 'Aucun', '', 'https://claude.ai/new', 'ok', '', NULL, NULL, 0, NULL, NULL, '2026-07-19 12:23:28', '2026-07-19 12:23:40'),
(73, 2, 0, 11, 'Intranap du pec', 'Aucun', '', 'https://pec-intranap.is-best.net/', 'ok', '', NULL, NULL, 4, NULL, NULL, '2026-07-19 16:26:59', '2026-07-25 12:51:36'),
(74, 2, 0, 11, 'Fit Analitics', 'Aucun', '', 'https://fitanalitics.likesyou.org/', 'ok', '', NULL, NULL, 5, NULL, NULL, '2026-07-20 22:33:03', '2026-07-25 12:51:36'),
(75, 2, 0, 11, 'Calculateur MG&M', 'Aucun', '', 'https://cmg-navy.22web.org/', 'ok', '', NULL, NULL, 6, NULL, NULL, '2026-07-24 15:25:22', '2026-07-25 12:51:36'),
(76, 2, 0, 1, 'Ingoku Danchi', 'Aucun', 'https://cdn.myanimelist.net/images/anime/1373/155741.jpg', 'https://voir-anime.to/anime/ingoku-danchi/ingoku-danchi-deviants-apartment-complex-{ep2}-vostfr/', 'ok', 'Des rumeurs circulent depuis peu concernant un complexe d\'appartements hanté par de nombreuses femmes lascives. Ignorant tout de ces rumeurs, un jeune homme nommé Yoshida devient le nouveau gérant de ce complexe. Lors de ses rondes nocturnes...', '1', 1, 16, NULL, '2026-07-27 10:44:39', '2026-07-26 23:37:25', '2026-07-27 10:44:39'),
(77, 2, 0, 1, 'Ingoku Danchi', 'Aucun', 'https://cdn.myanimelist.net/images/anime/1373/155741.jpg', 'https://voir-anime.to/anime/ingoku-danchi/ingoku-danchi-deviants-apartment-complex-{ep2}-vostfr/', 'ok', 'Yoshida jeune diplômé, petit et frêle, se retrouve à la tête d\'un immeuble d\'appartements contre son gré lorsque son père, l\'ancien gérant, se blesse. À son insu, cet immeuble abrite pas mal de femmes aux préférences sexuelles pour le moins… inhab...', '1', 1, 4, NULL, '2026-07-27 14:51:07', '2026-07-27 10:44:23', '2026-07-27 14:51:07'),
(78, 5, 0, 4, 'Soy Luna', 'En cours', 'https://image.tmdb.org/t/p/w500/4JDmIzhNF7aMsDGlxVwkQ9kv9E6.jpg', 'https://nakastream.tv/player?title=Soy%20Luna&id=8332&poster=/4JDmIzhNF7aMsDGlxVwkQ9kv9E6.jpg&type=tv&season={s}&episode={ep}', 'ok', 'Luna glisse avec bonheur sur la vie comme sur ses rollers ! Comme toutes les jeunes filles de son âge, elle vit avec sa famille entourée d’amis et découvre la vie entre ses cours et son job de serveuse. Mais ce qu’elle aime par-dessus tout, c’est ...', '22', 2, 0, NULL, NULL, '2026-08-10 20:37:29', '2026-08-15 15:13:58'),
(79, 2, 0, 3, 'Oppenheimer', 'En pause', 'https://image.tmdb.org/t/p/w500/boAUuJBeID7VNp4L7LNMQs8mfQS.jpg', 'https://nakastream.tv/player?title=Oppenheimer&id=1065&poster=/boAUuJBeID7VNp4L7LNMQs8mfQS.jpg&type=movie', 'ok', 'Temps : 2:28:00', NULL, NULL, 0, NULL, NULL, '2026-08-10 21:11:30', '2026-08-12 00:13:55'),
(80, 5, 0, 4, 'The Rookie : Le Flic de Los Angeles', 'En cours', 'https://image.tmdb.org/t/p/w500/aOXBLBRg7M5D2Zrs5luKD5cDB8O.jpg', 'https://nakastream.tv/player?id=334&title=The+Rookie+%3A+Le+Flic+de+Los+Angeles&type=tv&poster=%2FaOXBLBRg7M5D2Zrs5luKD5cDB8O.jpg&season={s}&episode={ep}&backdrop=%2F6iNWfGVCEfASDdlNb05TP5nG0ll.jpg', 'ok', 'John Nolan, le rookie le plus âgé du LAPD, utilise son expérience de vie, sa détermination et son sens de l’humour pour suivre des recrues âgées de 20 ans de moins que lui.', '2', 2, 1, NULL, NULL, '2026-08-11 00:05:24', '2026-08-11 00:05:24');
INSERT INTO `item` VALUES
(81, 5, 0, 4, 'Violetta', 'À voir', 'https://image.tmdb.org/t/p/w500/b3MUGJeKakAZwQa7lNJxTP1pJmD.jpg', '', 'ok', 'Violetta, c\'est l\'histoire d\'une adolescente très talentueuse qui, après avoir vécu de nombreuses années à Madrid, est retournée avec son père à Buenos Aires, sa ville natale. Elle entrera dans un studio de musique, mais suscitera la jalousie de L...', '1', 1, 2, NULL, NULL, '2026-08-11 00:09:56', '2026-08-11 00:09:56'),
(82, 2, 0, 4, 'Percy Jackson et les Olympiens', 'En cours', 'https://image.tmdb.org/t/p/w500/mAX4KE8ewwuDAIzwv3WTpqE3yh7.jpg', 'https://nakastream.tv/player?title=Percy%20Jackson%20et%20les%20Olympiens&id=583&poster=/mAX4KE8ewwuDAIzwv3WTpqE3yh7.jpg&type=tv&season={s}&episode={ep}', 'ok', 'Percy Jackson a une mission dangereuse. Tout en déjouant des monstres et des dieux, il doit parcourir l’Amérique pour retourner l’Éclair primitif à Zeus et mettre fin à une guerre sans merci. Après avoir perdu sa mère, Percy trouve refuge à la Col...', '6', 1, 5, NULL, NULL, '2026-08-11 23:49:07', '2026-08-12 00:21:59'),
(83, 2, 0, 4, 'The Walking Dead : Dead City', 'En pause', 'https://image.tmdb.org/t/p/w500/wq3vuQzQgbS83zX3malAFWMsSwX.jpg', 'https://nakastream.tv/player?title=The%20Walking%20Dead%20%3A%20Dead%20City&id=673&poster=/wq3vuQzQgbS83zX3malAFWMsSwX.jpg&type=tv&season={s}&episode={ep}', 'ok', 'Quelques années après les évènements survenus au Commonwealth, Maggie et Negan se rendent dans un Manhattan post-apocalyptique coupé depuis longtemps du continent. La ville en ruine est peuplée de morts et d\'habitants qui ont fait de New York, un ...', '1', 2, 6, NULL, NULL, '2026-08-11 23:50:57', '2026-08-12 00:21:59'),
(84, 2, 0, 4, 'The Walking Dead : Daryl Dixon', 'Aucun', 'https://image.tmdb.org/t/p/w500/sP5QdW9FN18XWcA4ROz3MPAQBTx.jpg', 'https://nakastream.tv/player?title=The%20Walking%20Dead%20%3A%20Daryl%20Dixon&id=672&poster=/sP5QdW9FN18XWcA4ROz3MPAQBTx.jpg&type=tv&season={s}&episode={ep}', 'ok', 'Daryl Dixon se réveille quelque part sur le continent européen et essaie de reconstituer ce qui s\'est passé. Comment est-il arrivé ici ? Comment va-t-il rentrer chez lui ?', '4', 2, 7, NULL, NULL, '2026-08-11 23:53:28', '2026-08-12 00:21:59'),
(85, 2, 0, 4, 'Under the Dome', 'En pause', 'https://image.tmdb.org/t/p/w500/fwH0ePhd7m3swtCuFeubtR49ZTd.jpg', 'https://nakastream.tv/player?title=Under%20the%20Dome&id=4228&poster=/fwH0ePhd7m3swtCuFeubtR49ZTd.jpg&type=tv&season={s}&episode={ep}', 'ok', 'D\'après le roman du même nom de Stephen King, cette émission suit les habitants de Chester\'s Mill, une petite ville des États-Unis où les événements exceptionnels sont rares. Mais un jour, un dôme invisible apparaît et englobe toute la ville. Les ...', '8', 2, 8, NULL, NULL, '2026-08-11 23:54:39', '2026-08-12 00:21:59'),
(86, 2, 0, 3, 'Saw II', 'Aucun', 'https://image.tmdb.org/t/p/w500/tz8Dfep4Vz142fZ9GdRGdhjVrI2.jpg', 'https://nakastream.tv/player?title=Saw%20II&id=3307&poster=/tz8Dfep4Vz142fZ9GdRGdhjVrI2.jpg&type=movie', 'ok', 'Chargé de l’enquête autour d’une mort sanglante, l’Inspecteur Eric Mason est persuadé que le crime est l’œuvre du redoutable Jigsaw, un criminel machiavélique qui impose à ses victimes des choix auxquels personne ne souhaite jamais être confronté....', NULL, NULL, 4, NULL, NULL, '2026-08-12 00:03:10', '2026-08-12 00:13:55'),
(87, 2, 0, 3, 'Saw III', 'Aucun', 'https://image.tmdb.org/t/p/w500/oT0CHpyQgryaz4z3TAq9Gvtcn2A.jpg', 'https://nakastream.tv/player?title=Saw%20III&id=4151&poster=/aOKaThXYt1cKnIx6lFKl1tVROz2.jpg&type=movie', 'ok', 'Le tueur au puzzle a mystérieusement échappé à ceux qui pensaient le tenir. Pendant que la police se démène pour tenter de remettre la main dessus, le génie criminel a décidé de reprendre son jeu terrifiant avec l’aide de sa protégée, Amanda. Le d...', NULL, NULL, 5, NULL, NULL, '2026-08-12 00:04:55', '2026-08-12 00:13:55'),
(88, 2, 0, 3, 'Saw IV', 'Aucun', 'https://image.tmdb.org/t/p/w500/4igbMego1x6SpBxGuER3bqTTLkW.jpg', 'https://nakastream.tv/player?title=Saw%20IV&id=6321&poster=/bOmwqSCNnz1isYCc9fc5NMchspf.jpg&type=movie', 'ok', 'Le tueur au puzzle et sa protégée, Amanda, ont disparu, mais la partie continue. Après le meurtre de l’inspectrice Kerry, deux profileurs chevronnés du FBI, les agents Strahm et Perez, viennent aider le détective Hoffman à réunir les pièces du der...', NULL, NULL, 6, NULL, NULL, '2026-08-12 00:05:29', '2026-08-12 00:13:55'),
(89, 2, 0, 3, 'Saw V', 'Aucun', 'https://image.tmdb.org/t/p/w500/2J1huUvravSdwuCVydu4RXEeHYh.jpg', 'https://nakastream.tv/player?title=Saw%20V&id=3577&poster=/rA8w4g4eg0GcD0P8mZZR11r7r4X.jpg&type=movie', 'ok', 'Il semble qu\'Hoffman soit le seul héritier du pouvoir du tueur au puzzle. Mais lorsque son secret risque d’être découvert, il n’a pas le droit à l’erreur et doit éliminer chaque menace. Les pièges vont se multiplier pour se refermer, inexorablemen...', NULL, NULL, 7, NULL, NULL, '2026-08-12 00:06:03', '2026-08-12 00:13:55'),
(90, 2, 0, 3, 'Saw VI', 'Aucun', 'https://image.tmdb.org/t/p/w500/4g097vnfnpWjKHTT0DuRKIihArj.jpg', 'https://nakastream.tv/player?title=Saw%20VI&id=3429&poster=/4g097vnfnpWjKHTT0DuRKIihArj.jpg&type=movie', 'ok', 'L’agent spécial Strahm est mort, et le détective Hoffman s’impose alors comme le légataire incontesté de l’héritage de Jigsaw. Cependant, tandis que le FBI se rapproche de plus en plus dangereusement de lui, Hoffman est obligé de commencer un nouv...', NULL, NULL, 8, NULL, NULL, '2026-08-12 00:06:56', '2026-08-12 00:13:55'),
(91, 2, 0, 3, 'Saw 3D : Chapitre final', 'Aucun', 'https://image.tmdb.org/t/p/w500/lVeg4c1XQMYeXXrXl2259qCDXUw.jpg', 'https://nakastream.tv/player?title=Saw%203D%20%3A%20Chapitre%20final&id=6484&poster=/lVeg4c1XQMYeXXrXl2259qCDXUw.jpg&type=movie', 'ok', 'Alors que la bataille fait rage autour de l’héritage terrifiant du Tueur au puzzle, un groupe de survivants s’associe et fait appel à un autre rescapé, Bobby Dagen, une sorte de gourou. En croyant trouver de l’aide, ils vont vivre le pire. Bobby c...', NULL, NULL, 9, NULL, NULL, '2026-08-12 00:07:51', '2026-08-12 00:13:55'),
(92, 2, 0, 3, 'Jigsaw', 'Aucun', 'https://image.tmdb.org/t/p/w500/8nJqDxcJSbb72foMUahP9QVrYWm.jpg', 'https://nakastream.tv/player?title=Jigsaw&id=7247&poster=/8nJqDxcJSbb72foMUahP9QVrYWm.jpg&type=movie', 'ok', 'Après une série de meurtres qui ressemblent étrangement à ceux de Jigsaw, le tueur au puzzle, la police se lance à la poursuite d’un homme mort depuis plus de dix ans. Un nouveau jeu vient de commencer… John Kramer est‐il revenu d’entre les morts ...', NULL, NULL, 10, NULL, NULL, '2026-08-12 00:08:23', '2026-08-12 00:13:55'),
(93, 2, 0, 3, 'Spirale : L\'Héritage de Saw', 'Aucun', 'https://image.tmdb.org/t/p/w500/2QVoPPQ3GrBx4eHUx1X29EGe2B4.jpg', 'https://nakastream.tv/player?title=Spirale%C2%A0%3A%20L%27H%C3%A9ritage%20de%20Saw&id=7899&poster=/2QVoPPQ3GrBx4eHUx1X29EGe2B4.jpg&type=movie', 'ok', 'Travaillant dans l\'ombre d’une légende locale de la police, le lieutenant Ezekiel « Zeke » Banks et son nouveau partenaire enquêtent sur une série de meurtres macabres dont le mode opératoire rappelle étrangement celui d’un tueur en série qui sévi...', NULL, NULL, 11, NULL, NULL, '2026-08-12 00:09:18', '2026-08-12 00:13:55'),
(94, 2, 0, 3, 'Saw X', 'Aucun', 'https://image.tmdb.org/t/p/w500/yMUhmrRYlNvE2ALpWvC8lNAM3Sa.jpg', 'https://nakastream.tv/player?title=Saw%20X&id=1939&poster=/yMUhmrRYlNvE2ALpWvC8lNAM3Sa.jpg&type=movie', 'ok', 'Dans l\'espoir d\'une guérison miraculeuse, John Kramer se rend au Mexique pour une procédure médicale risquée et expérimentale, pour découvrir que toute l\'opération est une arnaque visant à escroquer les plus vulnérables. Armé d\'un nouvel objectif,...', NULL, NULL, 12, NULL, NULL, '2026-08-12 00:10:12', '2026-08-12 00:13:55'),
(95, 2, 0, 3, 'La Planète des singes : L\'Affrontement', 'Aucun', 'https://image.tmdb.org/t/p/w500/ioCKDIwA4iXs8h4pEMTXN7E5LKG.jpg', 'https://nakastream.tv/player?title=La%20Plan%C3%A8te%20des%20singes%20%3A%20L%27Affrontement&id=1054&poster=/ioCKDIwA4iXs8h4pEMTXN7E5LKG.jpg&type=movie', 'ok', 'Une nation de plus en plus nombreuse de singes évolués, dirigée par César, est menacée par un groupe d’humains qui a survécu au virus dévastateur qui s\'est répandu 10 ans plus tôt. Ils parviennent à une trêve fragile, mais de courte durée : les de...', NULL, NULL, 1, NULL, NULL, '2026-08-12 00:12:27', '2026-08-12 00:13:55'),
(96, 2, 0, 3, 'La Planète des singes : Suprématie', 'Aucun', 'https://image.tmdb.org/t/p/w500/4lex7Z6pgnkHvc5KYAgKfcdYuPJ.jpg', 'https://nakastream.tv/player?title=La%20Plan%C3%A8te%20des%20singes%20%3A%20Supr%C3%A9matie&id=1056&poster=/4Ar01t6sW1ZZBcbz2R1wqjzIBdr.jpg&type=movie', 'ok', 'César et les Singes sont contraints de mener un combat dont ils ne veulent pas contre une armée d\'Humains dirigée par un Colonel impitoyable. Les Singes connaissent des pertes considérables et César, dans sa quête de vengeance, va devoir lutter co...', NULL, NULL, 2, NULL, NULL, '2026-08-12 00:13:07', '2026-08-12 00:13:55'),
(97, 2, 0, 3, 'La Planète des singes : Le Nouveau Royaume', 'Aucun', 'https://image.tmdb.org/t/p/w500/4925wPllJdQmHd1RxbZ62ZekaW3.jpg', 'https://nakastream.tv/player?title=La%20Plan%C3%A8te%20des%20singes%20%3A%20Le%20Nouveau%20Royaume&id=1053&poster=/4925wPllJdQmHd1RxbZ62ZekaW3.jpg&type=movie', 'ok', 'Plusieurs générations après le règne de César, les singes ont définitivement pris le pouvoir. Les humains, quant à eux, ont régressé à l\'état sauvage et vivent en retrait. Alors qu\'un nouveau chef tyrannique construit peu à peu son empire, un jeun...', NULL, NULL, 3, NULL, NULL, '2026-08-12 00:13:46', '2026-08-12 00:13:55'),
(98, 2, 0, 4, 'His Dark Materials : À la croisée des mondes', 'Aucun', 'https://image.tmdb.org/t/p/w500/yxkepbA5TFZkQ7ThRjbV08QXRCq.jpg', 'https://nakastream.tv/player?title=His%20Dark%20Materials%20%3A%20%C3%80%20la%20crois%C3%A9e%20des%20mondes&id=7348&poster=/yxkepbA5TFZkQ7ThRjbV08QXRCq.jpg&type=tv&season={s}&episode={ep}', 'ok', 'Courageuse et futée, Lyra se retrouve embarquée dans une folle aventure dans les contrées du Nord, à la recherche de son meilleur ami disparu. Pourquoi cette jeune fille orpheline, élevée dans l\'atmosphère austère et confinée du prestigieux Jordan...', '1', 1, 3, NULL, NULL, '2026-08-12 00:21:46', '2026-08-12 00:21:59'),
(99, 5, 0, 4, 'Kally\'s Mashup, la voix de la pop', 'À voir', 'https://image.tmdb.org/t/p/w500/axclJ10eoxxpVyZZC7FPq8jA6Gf.jpg', 'https://papadustreami.yachts/cat-series/comedie-s/13375-kallys-mashup-53d/{s}-saison/{ep}-episode.html', 'ok', 'Kally est une jeune prodige de la musique de 13 ans qui cherche l\'équilibre entre sa vie de pianiste et ses occupations d\'adolescente.', '1', 1, 3, NULL, '2026-08-17 13:11:12', '2026-08-15 12:49:04', '2026-08-17 13:11:12'),
(100, 5, 0, 3, 'Le Prince et moi', 'Terminé', 'https://image.tmdb.org/t/p/w500/m0SSHCU6o9lp4HrxIuvIonfth0p.jpg', 'https://nakastream.tv/player?id=8046&title=Le+Prince+et+moi&type=movie&poster=%2Fm0SSHCU6o9lp4HrxIuvIonfth0p.jpg&backdrop=%2Fu70yZonRQy8WOURASeTmxhKWDIT.jpg', 'ok', 'À l\'université du Wisconsin, le prince héritier du Danemark tombe amoureux d\'une fille de fermiers qui étudie en médecine. Désirant l\'épouser pour en faire la future reine de son pays, le jeune prince doit d\'abord la présenter à ses sévères parent...', NULL, NULL, 0, NULL, NULL, '2026-08-16 00:05:55', '2026-08-18 23:09:11'),
(101, 5, 0, 3, 'Cendrillon', 'Terminé', 'https://image.tmdb.org/t/p/w500/xQB77ZHBchFdZkiPCBJGQDd9qzB.jpg', '', 'ok', 'Après la mort de la mère de la jeune Ella, son père, un marchand, se remarie. Ella accueille chaleureusement sa belle‐mère, Lady Tremaine, et ses deux filles Anastasia et Drisella. Mais lorsque le père d’Ella disparaît à son tour, les trois femmes...', NULL, NULL, 1, NULL, NULL, '2026-08-17 13:02:19', '2026-08-18 22:36:47');

TRUNCATE TABLE `item_revisions`;
INSERT INTO `item_revisions` VALUES
(1, 28, 2, 'ClipDrop', 'Aucun', '', 'https://clipdrop.co/', 'Administrer des images', NULL, 0, 0, NULL, 'approved', '2026-08-10 12:09:14');

TRUNCATE TABLE `migrations`;
TRUNCATE TABLE `reports`;
TRUNCATE TABLE `settings`;
TRUNCATE TABLE `sites_config`;
INSERT INTO `sites_config` VALUES
(1, 'voir-anime.to', '/-(\\d+)-vostfr/i', '[\"Premier EP\", \"Dernier EP\"]', '[\"class=\\\"lecteur\\\"\", \"<iframe\", \"Lecteur\"]', 1),
(2, 'scan-vf.net', '/chapitre-(\\d+)/i', '[\"Liste des chapitres\", \"Manga en cours\"]', '[\"img-responsive\", \"img-fluid\", \"pages_container\"]', 1),
(4, 'franime.fr', '/[?&]ep=(\\d+)/i', '[]', '[\"margin-player\", \"Regarder l&#x27;épisode\"]', 1);

TRUNCATE TABLE `users`;
INSERT INTO `users` VALUES
(1, 'Super Admin', NULL, NULL, 1, '2026-08-16 17:04:16', '2026-04-11 17:01:15', '2026-05-17 12:39:43', NULL),
(2, 'Titiss', NULL, NULL, 1, '2026-08-19 15:24:25', '2026-04-11 17:02:37', '2026-04-11 17:02:38', NULL),
(3, 'Seiko', NULL, NULL, 1, NULL, '2026-04-30 14:13:09', '2026-05-29 22:18:11', NULL),
(4, 'User de test', 'banned', 'Accès révoqué par l\'administration.', 1, NULL, '2026-05-29 22:30:44', '2026-07-07 15:33:52', NULL),
(5, 'Ambre', NULL, NULL, 1, '2026-08-18 23:09:11', '2026-06-17 20:17:27', '2026-06-25 12:09:08', NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

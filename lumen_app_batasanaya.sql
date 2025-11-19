-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 13, 2025 at 04:02 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lumen_app_batasanaya`
--

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` int NOT NULL,
  `created_by_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`id`, `file_name`, `type`, `file`, `size`, `created_by_id`, `created_at`, `updated_at`) VALUES
('06c7af57-1c35-4f31-9b96-6aee593db2b5', '1762921190_ha-pink.png', 'image', 'uploads/assets/1762921190_ha-pink.png', 12526, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:19:50', '2025-11-12 04:19:50'),
('08fb16b0-4b6a-485f-910c-60bee55b2cc6', '1762921440_mim.png', 'image', 'uploads/assets/1762921440_mim.png', 14466, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:24:00', '2025-11-12 04:24:00'),
('0b8c740a-de91-4f22-9116-729eea18b568', '1762921098_fa-pink.png', 'image', 'uploads/assets/1762921098_fa-pink.png', 14085, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:18:18', '2025-11-12 04:18:18'),
('0d934512-4bc5-4abd-993a-84b715e7bdd4', '1762921638_sa.png', 'image', 'uploads/assets/1762921638_sa.png', 15800, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:27:18', '2025-11-12 04:27:18'),
('10b08e00-a034-47d1-9f05-e46029fdecc3', '1762921541_qo.png', 'image', 'uploads/assets/1762921541_qo.png', 17136, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:25:41', '2025-11-12 04:25:41'),
('12db193a-7978-403f-92bd-e3737832b564', '1762921142_gin-pink.png', 'image', 'uploads/assets/1762921142_gin-pink.png', 13509, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:19:02', '2025-11-12 04:19:02'),
('197ee85a-b77b-419b-803c-1c2b5b1db4de', '1762921488_na.png', 'image', 'uploads/assets/1762921488_na.png', 14939, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:24:48', '2025-11-12 04:24:48'),
('230fdbd3-f9f0-40a5-ad37-fb9051e164dd', '1762922066_za.png', 'image', 'uploads/assets/1762922066_za.png', 14185, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:34:26', '2025-11-12 04:34:26'),
('2d6514d6-87d6-4676-8756-78f60edf4a09', '1762920939_dhod.png', 'image', 'uploads/assets/1762920939_dhod.png', 16880, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:15:39', '2025-11-12 04:15:39'),
('2da71ab8-65e9-4d87-b5b4-4abe26028eb1', '1762921686_sod.png', 'image', 'uploads/assets/1762921686_sod.png', 15991, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:28:07', '2025-11-12 04:28:07'),
('31dfc44d-f4b9-421a-abea-c968fd13b81e', '1762920664_alif.png', 'image', 'uploads/assets/1762920664_alif.png', 13861, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:11:04', '2025-11-12 04:11:04'),
('3450a11e-c3f8-4a0c-87b1-f35e4a83576c', '1762921661_sa-pink.png', 'image', 'uploads/assets/1762921661_sa-pink.png', 12985, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:27:41', '2025-11-12 04:27:41'),
('3f0cd244-b08e-4b6a-bde7-8d683df01f1c', '1762922018_ya.png', 'image', 'uploads/assets/1762922018_ya.png', 17122, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:33:39', '2025-11-12 04:33:39'),
('3fe3a6ec-5d81-420b-86d5-28f9a27b32b0', '1762921399_la-pink.png', 'image', 'uploads/assets/1762921399_la-pink.png', 12310, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:23:19', '2025-11-12 04:23:19'),
('4241ddae-44cd-4886-8c7e-cd2d1c2f66f0', '1762921797_ta.png', 'image', 'uploads/assets/1762921797_ta.png', 15430, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:29:57', '2025-11-12 04:29:57'),
('43d87a1d-cf58-4ecc-aa3d-ff46289f3946', '1762920612_ain-pink.png', 'image', 'uploads/assets/1762920612_ain-pink.png', 12716, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:10:12', '2025-11-12 04:10:12'),
('4a2e5c22-541d-46cf-874d-c472674e8e2f', '1762921935_tsa-pink.png', 'image', 'uploads/assets/1762921935_tsa-pink.png', 13449, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:32:15', '2025-11-12 04:32:15'),
('4aa8b013-17f2-4e16-aa17-5a463bf68c89', '1762921591_ro.png', 'image', 'uploads/assets/1762921591_ro.png', 13456, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:26:31', '2025-11-12 04:26:31'),
('4e776932-4dff-4d5d-b157-8df56fe7d7f0', '1762921008_dzo.png', 'image', 'uploads/assets/1762921008_dzo.png', 17227, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:16:48', '2025-11-12 04:16:48'),
('52437130-b355-495f-bfac-4946f78f6801', '1762921120_gin.png', 'image', 'uploads/assets/1762921120_gin.png', 15955, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:18:40', '2025-11-12 04:18:40'),
('54ca4649-c1b4-4533-890a-6ee34c680d7e', '1762921243_ja-pink.png', 'image', 'uploads/assets/1762921243_ja-pink.png', 13043, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:20:43', '2025-11-12 04:20:43'),
('54e87f3a-2367-420c-93ea-23b34113897e', '1762920312_ja.png', 'image', 'uploads/assets/1762920312_ja.png', 15496, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:05:12', '2025-11-12 04:05:12'),
('5883e67c-5686-474b-a982-f1c3542d0245', '1762921341_kho-pink.png', 'image', 'uploads/assets/1762921341_kho-pink.png', 13464, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:22:21', '2025-11-12 04:22:21'),
('58ddb6b2-da8f-474e-a6b9-ad1ac8459958', '1762920216_ain.png', 'image', 'uploads/assets/1762920216_ain.png', 15520, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:03:37', '2025-11-12 04:03:37'),
('5f8ea9dc-ff38-41ce-aba6-ba9319a4209e', '1762920836_da.png', 'image', 'uploads/assets/1762920836_da.png', 13493, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:13:56', '2025-11-12 04:13:56'),
('62eca3b5-e12f-46d3-8e87-036a25a42d87', '1762921026_dzo-pink.png', 'image', 'uploads/assets/1762921026_dzo-pink.png', 14150, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:17:06', '2025-11-12 04:17:06'),
('70921804-21da-4666-a2de-95b5f76734e7', '1762921217_ja.png', 'image', 'uploads/assets/1762921217_ja.png', 15496, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:20:17', '2025-11-12 04:20:17'),
('74df6dea-1cbf-4c24-a9fd-8af5692f0316', '1762922041_ya-pink.png', 'image', 'uploads/assets/1762922041_ya-pink.png', 14614, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:34:01', '2025-11-12 04:34:01'),
('75dca83e-08ad-4dc0-901a-d6db7331b39c', '1762921288_ka-pink.png', 'image', 'uploads/assets/1762921288_ka-pink.png', 13101, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:21:28', '2025-11-12 04:21:28'),
('934c7499-d902-4d97-8277-2d9ed5b54772', '1762921614_ro-pink.png', 'image', 'uploads/assets/1762921614_ro-pink.png', 10977, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:26:54', '2025-11-12 04:26:54'),
('94383853-dd5d-4d78-9a5f-8a0a051de718', '1762920985_dza-pink.png', 'image', 'uploads/assets/1762920985_dza-pink.png', 11925, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:16:25', '2025-11-12 04:16:25'),
('97926da8-8f1d-463c-8f21-a95dbda0db00', '1762921465_mim-pink.png', 'image', 'uploads/assets/1762921465_mim-pink.png', 11593, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:24:25', '2025-11-12 04:24:25'),
('9a6203cc-17e9-4547-8ff6-cceb2e3f4251', '1762921312_kho.png', 'image', 'uploads/assets/1762921312_kho.png', 15836, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:21:53', '2025-11-12 04:21:53'),
('9ddbb117-0253-49a3-bfa6-b4276172188e', '1762921512_na-pink.png', 'image', 'uploads/assets/1762921512_na-pink.png', 12231, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:25:13', '2025-11-12 04:25:13'),
('9eb96164-705a-4423-8e94-ad85b560ba9c', '1762921911_tsa.png', 'image', 'uploads/assets/1762921911_tsa.png', 16045, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:31:51', '2025-11-12 04:31:51'),
('b8a310e0-5723-4d51-9e31-234084880e1a', '1762921074_fa.png', 'image', 'uploads/assets/1762921074_fa.png', 16441, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:17:55', '2025-11-12 04:17:55'),
('bc6d2227-ed1a-48ac-89dc-1efd4047b7a1', '1762921964_wa.png', 'image', 'uploads/assets/1762921964_wa.png', 14397, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:32:44', '2025-11-12 04:32:44'),
('bdbf1fd6-4eac-4890-ac52-2c27085c8596', '1762921750_syin.png', 'image', 'uploads/assets/1762921750_syin.png', 17232, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:29:10', '2025-11-12 04:29:10'),
('c0bdb09c-fd6c-432e-8fef-0ea0010303dd', '1762921851_tho.png', 'image', 'uploads/assets/1762921851_tho.png', 16261, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:30:51', '2025-11-12 04:30:51'),
('cb5632b1-f05e-4182-b547-3e6fa6a65e73', '1762921167_ha.png', 'image', 'uploads/assets/1762921167_ha.png', 14949, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:19:27', '2025-11-12 04:19:27'),
('cc87cae3-4738-4762-86f7-f28d963abfaf', '1762922088_za-pink.png', 'image', 'uploads/assets/1762922088_za-pink.png', 11925, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:34:48', '2025-11-12 04:34:48'),
('d0551f23-3a66-4396-a0a4-65d2df65afc9', '1762921775_syin-pink.png', 'image', 'uploads/assets/1762921775_syin-pink.png', 14864, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:29:35', '2025-11-12 04:29:35'),
('de8ddf0b-05c2-4698-99db-4e3840bb9195', '1762921823_ta-pink.png', 'image', 'uploads/assets/1762921823_ta-pink.png', 12680, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:30:23', '2025-11-12 04:30:23'),
('ded58ed4-ec63-4c24-9489-cca02e94eb04', '1762921991_wa-pink.png', 'image', 'uploads/assets/1762921991_wa-pink.png', 11950, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:33:11', '2025-11-12 04:33:11'),
('def9bdd5-07a3-462a-bd4b-f31c775490e5', '1762920460_ba-pink.png', 'image', 'uploads/assets/1762920460_ba-pink.png', 12597, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:07:41', '2025-11-12 04:07:41'),
('e1976f91-21c5-4bd2-b8df-d66b8da19584', '1762920702_alif-pink.png', 'image', 'uploads/assets/1762920702_alif-pink.png', 11838, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:11:42', '2025-11-12 04:11:42'),
('e287f2ff-078c-430e-a208-e9f7bf435ada', '1762921367_la.png', 'image', 'uploads/assets/1762921367_la.png', 15153, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:22:47', '2025-11-12 04:22:47'),
('e2d9e28d-37d3-404b-b60c-42302e597af1', '1762920815_ba.png', 'image', 'uploads/assets/1762920815_ba.png', 15476, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:13:35', '2025-11-12 04:13:35'),
('e30414ad-550d-40d3-b3cf-2eda397ec659', '1762921565_qo-pink.png', 'image', 'uploads/assets/1762921565_qo-pink.png', 14322, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:26:05', '2025-11-12 04:26:05'),
('e41fa933-d5a4-4309-a8af-8a1aba6b60dc', '1762921877_tho-pink.png', 'image', 'uploads/assets/1762921877_tho-pink.png', 13196, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:31:17', '2025-11-12 04:31:17'),
('ed2bc701-9477-427c-92a1-6bbf5a5bb09e', '1762921722_sod-pink.png', 'image', 'uploads/assets/1762921722_sod-pink.png', 13732, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:28:42', '2025-11-12 04:28:42'),
('ef432ef3-e8fa-4110-902d-cc9d2811bc63', '1762920966_dza.png', 'image', 'uploads/assets/1762920966_dza.png', 14395, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:16:06', '2025-11-12 04:16:06'),
('f1476004-9bd6-4e64-9d83-2588ed980b79', '1762920858_da-pink.png', 'image', 'uploads/assets/1762920858_da-pink.png', 10821, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:14:18', '2025-11-12 04:14:18'),
('f45b9b44-27b3-4aa4-80f4-bc011211b1ac', '1762920909_dhod-pink.png', 'image', 'uploads/assets/1762920909_dhod-pink.png', 14580, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:15:09', '2025-11-12 04:15:09'),
('f577afa3-ef24-45f8-8304-ada8e9a96105', '1762921267_ka.png', 'image', 'uploads/assets/1762921267_ka.png', 15569, '75964126-fb57-4c50-ba57-13fb53aa8a3b', '2025-11-12 04:21:07', '2025-11-12 04:21:07');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2025_10_22_114953_create_roles_table', 1),
(2, '2025_10_22_115005_create_users_table', 1),
(3, '2025_10_22_115837_create_user_subscriptions_table', 1),
(4, '2025_10_22_115846_create_user_preferences_table', 1),
(5, '2025_10_23_055446_create_otps_table', 2),
(6, '2025_10_24_122145_create_assets_table', 3),
(7, '2025_10_28_015248_create_songs_table', 4),
(8, '2025_11_11_060609_create_videos_table', 5);

-- --------------------------------------------------------

--
-- Table structure for table `otps`
--

CREATE TABLE `otps` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_used` tinyint(1) NOT NULL DEFAULT '0',
  `is_revoked` tinyint(1) NOT NULL DEFAULT '0',
  `expired_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `otps`
--

INSERT INTO `otps` (`id`, `phone_number`, `code`, `is_used`, `is_revoked`, `expired_at`, `created_at`, `updated_at`) VALUES
('51ecfa97-a466-4e2f-971e-6b78d970fe75', '08123456789', '154908', 1, 0, '2025-10-23 06:59:18', '2025-10-23 06:54:18', '2025-10-23 06:55:01'),
('65e31e19-4c76-4b49-b3e2-96b9f7d8cdfc', '08123456789', '798909', 0, 0, '2025-10-23 06:54:05', '2025-10-23 06:49:05', '2025-10-23 06:49:05');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`) VALUES
('602da934-8fef-4c1c-a3e1-1f7eeb821736', 'user', '2025-10-22 12:36:17', '2025-10-22 12:36:17'),
('90dbd1f9-1431-42b6-9ef8-499c43c91cd1', 'admin', '2025-10-22 12:36:17', '2025-10-22 12:36:17');

-- --------------------------------------------------------

--
-- Table structure for table `songs`
--

CREATE TABLE `songs` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `thumbnail` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pin_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_picture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `phone_number`, `email`, `password`, `pin_code`, `profile_picture`, `role_id`, `created_at`, `updated_at`) VALUES
('278be847-8556-4199-ad52-eabb92cfb9f1', 'Reza Ardiansyah', NULL, 'reza@example.com', '$2y$12$OTCexx5pafu/qdsQTKm51OvVzhvjqmG4IRiHtyP0a5ewhBSnBxVMW', NULL, NULL, '602da934-8fef-4c1c-a3e1-1f7eeb821736', '2025-10-27 11:58:04', '2025-10-27 11:58:04'),
('34dd9320-1fa5-4bd9-9d7c-4a7334009502', 'Azer Reza', '0895398567404', 'cakcakgelo@example.com', '$2y$12$tNduYPWDY/hEdoVKYseQlu6RIOZZEcsLwT2KBzKkoZEdi5QEOKPXe', NULL, NULL, '602da934-8fef-4c1c-a3e1-1f7eeb821736', '2025-10-28 11:36:41', '2025-10-28 11:36:41'),
('54d33e9e-38b3-43aa-9224-d829d2cb83a0', 'testing', NULL, 'testing@gmail.com', '$2y$12$4hJszjlbN7Zg1tOp9Ls5E.Fhs0vLY/X6Ffpm6/oVj7CV7f76rQBfO', NULL, NULL, '602da934-8fef-4c1c-a3e1-1f7eeb821736', '2025-10-28 01:34:34', '2025-10-28 01:34:34'),
('6a528609-c32c-431f-9ec0-45a115d4d626', 'Rizaldi Ananda Kurnia', NULL, 'rizaldi@example.com', '$2y$12$z97XQd1GTPl6OrP2yqz4Xe0jC2qbTohYX5Lc7IpXtUkV6pjlhDvpa', NULL, 'uploads/profiles/profile_6a528609-c32c-431f-9ec0-45a115d4d626_1761540661.jpg', '602da934-8fef-4c1c-a3e1-1f7eeb821736', '2025-10-24 12:09:47', '2025-10-27 04:51:01'),
('75964126-fb57-4c50-ba57-13fb53aa8a3b', 'Aldo Firmansyah', '08123456789', 'aldo.test@example.com', '$2y$12$geo2v0/hwOOwWLux1xzAh.rI/LTD4s/59raaYt5Ve4.YCj7pEbNGe', NULL, NULL, '90dbd1f9-1431-42b6-9ef8-499c43c91cd1', '2025-10-22 14:58:51', '2025-10-23 05:48:20'),
('a0006bec-d06c-48b4-9dda-07caeb0684c3', 'Ardiansyah Reza', NULL, 'cakcakgelo10@example.com', '$2y$12$AL9hEQ6Hx5wtDabWGDp/5OJYl/C6LFUr1V1G7lbmmQq0wQpMopao.', NULL, NULL, '602da934-8fef-4c1c-a3e1-1f7eeb821736', '2025-10-28 06:36:46', '2025-10-28 06:36:46'),
('ab3ad883-0f48-4bed-ab19-6ae95cae993c', 'coba1', NULL, 'testing1@gmail.com', '$2y$12$tyHuMzwnScNOYL4PLzH.Lupzv.F6vJee2riUTCxpYNQ/SDCeljmr6', NULL, NULL, '602da934-8fef-4c1c-a3e1-1f7eeb821736', '2025-10-30 11:45:20', '2025-10-30 11:45:20'),
('c7d5493e-f6cc-4d21-905e-1f6cf9637b86', 'coba7', '08765432199', 'coba7@gmail.com', '$2y$12$qmJP8hNAnp02tTEVYwQd9uA02QmpV9FdVSBbW/nvbLDXk1LMCGG1e', NULL, NULL, '602da934-8fef-4c1c-a3e1-1f7eeb821736', '2025-10-30 13:08:48', '2025-10-30 13:08:48'),
('d8af2c19-177a-4fd1-87d0-3700938eacc8', 'coba3', '0853982743122', 'testing3@gmail.com', '$2y$12$pcXw5NvYnBpYEnOc.LmLR.RwX0NNU5PCxZeRp/p/ospWeN7Db6uHy', NULL, NULL, '602da934-8fef-4c1c-a3e1-1f7eeb821736', '2025-10-30 12:55:00', '2025-10-30 12:55:00'),
('dff53791-7fa2-4b37-b2eb-4a1073fecc01', 'coba123', '0810987654321', 'coba@gmail.com', '$2y$12$ntnAc04V1bhyjQUXlJXmjuKFxTuMxP3n/ibyCkQWvk9AELTad/o.O', NULL, NULL, '602da934-8fef-4c1c-a3e1-1f7eeb821736', '2025-10-30 12:35:48', '2025-10-30 12:35:48'),
('e0cc5039-a48d-4dda-a10c-1ca17649954a', 'Test1', NULL, 'tes1@gmail.com', '$2y$12$NQ4du71RV6LUkxOJdXxlde2yTknZHJVlDk03cfWT/G7sCUXdlicnW', NULL, NULL, '602da934-8fef-4c1c-a3e1-1f7eeb821736', '2025-10-27 12:23:54', '2025-10-27 12:23:54'),
('e232e553-f642-4d5e-89e2-8fae363f286d', 'coba2', NULL, 'testing2@gmail.com', '$2y$12$Nq7q8xq/F87EdDXnIebzPegcOtPhhyy.DDwx16RUA0CrmCKbfCIBC', NULL, NULL, '602da934-8fef-4c1c-a3e1-1f7eeb821736', '2025-10-30 12:34:23', '2025-10-30 12:34:23');

-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

CREATE TABLE `user_preferences` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `audio_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `music_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `max_screen_time` int NOT NULL DEFAULT '7200',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_preferences`
--

INSERT INTO `user_preferences` (`id`, `audio_enabled`, `music_enabled`, `max_screen_time`, `created_at`, `updated_at`) VALUES
('6a528609-c32c-431f-9ec0-45a115d4d626', 0, 1, 7200, '2025-10-28 02:35:35', '2025-10-28 02:35:35'),
('75964126-fb57-4c50-ba57-13fb53aa8a3b', 1, 1, 7200, '2025-10-28 02:28:47', '2025-10-28 02:28:47');

-- --------------------------------------------------------

--
-- Table structure for table `user_subscriptions`
--

CREATE TABLE `user_subscriptions` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `videos`
--

CREATE TABLE `videos` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `video_url` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `thumbnail_url` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `assets_file_name_unique` (`file_name`),
  ADD KEY `assets_created_by_id_foreign` (`created_by_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `otps`
--
ALTER TABLE `otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `otps_phone_number_index` (`phone_number`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`);

--
-- Indexes for table `songs`
--
ALTER TABLE `songs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `songs_title_unique` (`title`),
  ADD KEY `songs_created_by_id_foreign` (`created_by_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_phone_number_unique` (`phone_number`),
  ADD KEY `users_role_id_foreign` (`role_id`);

--
-- Indexes for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_subscriptions_user_id_unique` (`user_id`);

--
-- Indexes for table `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `videos_created_by_id_foreign` (`created_by_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assets`
--
ALTER TABLE `assets`
  ADD CONSTRAINT `assets_created_by_id_foreign` FOREIGN KEY (`created_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `otps`
--
ALTER TABLE `otps`
  ADD CONSTRAINT `otps_phone_number_foreign` FOREIGN KEY (`phone_number`) REFERENCES `users` (`phone_number`) ON DELETE CASCADE;

--
-- Constraints for table `songs`
--
ALTER TABLE `songs`
  ADD CONSTRAINT `songs_created_by_id_foreign` FOREIGN KEY (`created_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD CONSTRAINT `user_preferences_id_foreign` FOREIGN KEY (`id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  ADD CONSTRAINT `user_subscriptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `videos`
--
ALTER TABLE `videos`
  ADD CONSTRAINT `videos_created_by_id_foreign` FOREIGN KEY (`created_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

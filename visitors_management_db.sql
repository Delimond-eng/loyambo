-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 15, 2025 at 03:33 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `visitors_management_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `libelle` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'actif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `libelle`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Ressources Humaines', 'actif', '2025-04-15 13:22:49', '2025-04-15 13:22:49'),
(2, 'Informatique', 'actif', '2025-04-15 13:22:49', '2025-04-15 13:22:49'),
(3, 'Administration', 'actif', '2025-04-15 13:22:49', '2025-04-15 13:22:49'),
(4, 'Ventes', 'actif', '2025-04-15 13:22:49', '2025-04-15 13:22:49'),
(5, 'Support Technique', 'actif', '2025-04-15 13:22:49', '2025-04-15 13:22:49'),
(6, 'Juridique', 'actif', '2025-04-15 13:22:49', '2025-04-15 13:22:49'),
(7, 'Marketing', 'actif', '2025-04-15 13:22:49', '2025-04-15 13:22:49'),
(8, 'Maintenance', 'actif', '2025-04-15 13:22:49', '2025-04-15 13:22:49'),
(9, 'Opérations', 'actif', '2025-04-15 13:22:49', '2025-04-15 13:22:49');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_100000_create_password_resets_table', 1),
(2, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(3, '2024_02_13_065528_create_users_table', 1),
(4, '2025_04_10_113412_create_visits_table', 1),
(5, '2025_04_10_113550_create_visit_histories_table', 1),
(6, '2025_04_10_113901_create_user_permissions_table', 1),
(7, '2025_04_13_210217_create_departments_table', 1),
(8, '2025_04_13_210305_create_prof_types_table', 1),
(9, '2025_04_13_210340_create_visitor_types_table', 1),
(10, '2025_04_13_210852_create_visit_purposes_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prof_types`
--

CREATE TABLE `prof_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `libelle` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'actif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `prof_types`
--

INSERT INTO `prof_types` (`id`, `libelle`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Carte d\'Electeur', 'actif', '2025-04-15 13:22:49', '2025-04-15 13:22:49'),
(2, 'Permis de conduire', 'actif', '2025-04-15 13:22:49', '2025-04-15 13:22:49'),
(3, 'Carte de Service', 'actif', '2025-04-15 13:22:49', '2025-04-15 13:22:49'),
(4, 'Passeport', 'actif', '2025-04-15 13:22:49', '2025-04-15 13:22:49');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('ADMIN','USER') NOT NULL DEFAULT 'USER',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Mr. Didier ', 'admin@gmail.com', NULL, '$2y$10$yBKxdUJVfxN7ba1uaOU5aecDbexc6UmtuganJgR5N5Kx8avzOcLvG', 'ADMIN', NULL, '2025-04-15 13:22:49', '2025-04-15 13:22:49');

-- --------------------------------------------------------

--
-- Table structure for table `user_permissions`
--

CREATE TABLE `user_permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `permission_type` enum('Update','Delete','Export') NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_permissions`
--

INSERT INTO `user_permissions` (`id`, `user_id`, `permission_type`, `enabled`, `created_at`, `updated_at`) VALUES
(1, 1, 'Update', 1, '2025-04-15 13:22:49', '2025-04-15 13:22:49'),
(2, 1, 'Delete', 1, '2025-04-15 13:22:49', '2025-04-15 13:22:49'),
(3, 1, 'Export', 1, '2025-04-15 13:22:49', '2025-04-15 13:22:49');

-- --------------------------------------------------------

--
-- Table structure for table `visitor_types`
--

CREATE TABLE `visitor_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `libelle` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'actif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `visitor_types`
--

INSERT INTO `visitor_types` (`id`, `libelle`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Fournisseur', 'actif', '2025-04-15 13:22:49', '2025-04-15 13:22:49'),
(2, 'Candidat', 'actif', '2025-04-15 13:22:49', '2025-04-15 13:22:49'),
(3, 'Coursier', 'actif', '2025-04-15 13:22:49', '2025-04-15 13:22:49'),
(4, 'Client', 'actif', '2025-04-15 13:22:49', '2025-04-15 13:22:49'),
(5, 'Invité', 'actif', '2025-04-15 13:22:49', '2025-04-15 13:22:49'),
(6, 'Technicien', 'actif', '2025-04-15 13:22:49', '2025-04-15 13:22:49'),
(7, 'Auditeur', 'actif', '2025-04-15 13:22:49', '2025-04-15 13:22:49'),
(8, 'Employé', 'actif', '2025-04-15 13:22:49', '2025-04-15 13:22:49');

-- --------------------------------------------------------

--
-- Table structure for table `visits`
--

CREATE TABLE `visits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `company_or_address` varchar(255) DEFAULT NULL,
  `contact_number` varchar(255) DEFAULT NULL,
  `email_address` varchar(255) DEFAULT NULL,
  `id_proof_type` varchar(255) DEFAULT NULL,
  `id_proof_number` varchar(255) DEFAULT NULL,
  `visitor_type` varchar(255) DEFAULT NULL,
  `vehicle_number` varchar(255) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `purpose` varchar(255) NOT NULL,
  `visit_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `time_in` time NOT NULL,
  `time_out` time DEFAULT NULL,
  `stay_time` varchar(255) DEFAULT NULL,
  `entry_authorized_by` varchar(255) DEFAULT NULL,
  `pass_number` varchar(255) DEFAULT NULL,
  `hostname` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Approved','Completed') NOT NULL DEFAULT 'Pending',
  `remarks` text DEFAULT NULL,
  `picture_url` varchar(255) DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED NOT NULL,
  `update_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `visit_histories`
--

CREATE TABLE `visit_histories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `visit_id` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED NOT NULL,
  `update_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `changes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`changes`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `visit_purposes`
--

CREATE TABLE `visit_purposes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `libelle` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'actif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `visit_purposes`
--

INSERT INTO `visit_purposes` (`id`, `libelle`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Réunion', 'actif', '2025-04-15 13:22:49', '2025-04-15 13:22:49'),
(2, 'Entretien', 'actif', '2025-04-15 13:22:49', '2025-04-15 13:22:49'),
(3, 'Livraison', 'actif', '2025-04-15 13:22:49', '2025-04-15 13:22:49'),
(4, 'Support', 'actif', '2025-04-15 13:22:49', '2025-04-15 13:22:49'),
(5, 'Séminaire', 'actif', '2025-04-15 13:22:49', '2025-04-15 13:22:49'),
(6, 'Proposition commerciale', 'actif', '2025-04-15 13:22:49', '2025-04-15 13:22:49'),
(7, 'Réparation', 'actif', '2025-04-15 13:22:49', '2025-04-15 13:22:49'),
(8, 'Inspection', 'actif', '2025-04-15 13:22:49', '2025-04-15 13:22:49'),
(9, 'Consultation', 'actif', '2025-04-15 13:22:49', '2025-04-15 13:22:49'),
(10, 'Formation', 'actif', '2025-04-15 13:22:49', '2025-04-15 13:22:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `prof_types`
--
ALTER TABLE `prof_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `visitor_types`
--
ALTER TABLE `visitor_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `visits`
--
ALTER TABLE `visits`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `visit_histories`
--
ALTER TABLE `visit_histories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `visit_purposes`
--
ALTER TABLE `visit_purposes`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prof_types`
--
ALTER TABLE `prof_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_permissions`
--
ALTER TABLE `user_permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `visitor_types`
--
ALTER TABLE `visitor_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `visits`
--
ALTER TABLE `visits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `visit_histories`
--
ALTER TABLE `visit_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `visit_purposes`
--
ALTER TABLE `visit_purposes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

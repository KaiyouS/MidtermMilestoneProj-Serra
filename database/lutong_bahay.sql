-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: Oct 28, 2026 at 04:36 AM
-- Server version: 10.11.19-MariaDB-ubu2204
-- PHP Version: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lutong_bahay`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(40) NOT NULL,
  `description` varchar(150) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Ulam', 'Main dishes to pair with rice'),
(2, 'Sabaw', 'Soups and stews for rainy days'),
(3, 'Gulay', 'Vegetable dishes'),
(4, 'Almusal', 'Breakfast favorites'),
(5, 'Merienda', 'Afternoon snacks'),
(6, 'Kakanin', 'Rice cakes and native delicacies'),
(7, 'Panghimagas', 'Desserts and sweets');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(10) UNSIGNED NOT NULL,
  `recipe_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `body` varchar(1000) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `recipe_id`, `user_id`, `body`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'Ginawa ko ito kagabi. Super sarap! I added a little brown sugar at the end.', '2026-10-28 11:05:03', NULL),
(2, 1, 1, 'Salamat, Mang Tonyo! A bit of sugar is a good idea if your vinegar is very sharp.', '2026-10-28 11:15:03', NULL),
(3, 2, 3, 'Real sampalok makes such a difference. Can I use pork belly instead of ribs?', '2026-10-28 11:25:03', '2026-10-28 12:30:03'),
(4, 2, 2, 'Pwede! Just cut the simmering time to about 35 minutes.', '2026-10-28 11:35:03', NULL),
(5, 5, 1, 'The tip about draining on a rack is gold.', '2026-10-28 11:45:03', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `recipe_id` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`user_id`, `recipe_id`, `created_at`) VALUES
(1, 2, '2026-10-28 12:35:03'),
(1, 6, '2026-10-28 12:35:03'),
(2, 1, '2026-10-28 12:35:03'),
(2, 7, '2026-10-28 12:35:03'),
(3, 1, '2026-10-28 12:35:03'),
(3, 5, '2026-10-28 12:35:03');

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

CREATE TABLE `ingredients` (
  `id` int(10) UNSIGNED NOT NULL,
  `recipe_id` int(10) UNSIGNED NOT NULL,
  `position` smallint(5) UNSIGNED NOT NULL,
  `quantity` decimal(8,3) DEFAULT NULL,
  `unit` varchar(30) NOT NULL DEFAULT '',
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ingredients`
--

INSERT INTO `ingredients` (`id`, `recipe_id`, `position`, `quantity`, `unit`, `name`) VALUES
(1, 1, 1, 1.000, 'kg', 'chicken thighs and drumsticks'),
(2, 1, 2, 0.500, 'cup', 'soy sauce'),
(3, 1, 3, 0.667, 'cup', 'cane vinegar'),
(4, 1, 4, 1.000, 'head', 'garlic, crushed'),
(5, 1, 5, 3.000, 'pcs', 'dried bay leaves'),
(6, 1, 6, 1.000, 'tsp', 'whole black peppercorns'),
(7, 1, 7, 0.500, 'cup', 'water'),
(8, 1, 8, 2.000, 'tbsp', 'cooking oil'),
(9, 2, 1, 1.000, 'kg', 'pork ribs, cut into pieces'),
(10, 2, 2, 10.000, 'cups', 'water'),
(11, 2, 3, 250.000, 'g', 'fresh sampalok (tamarind)'),
(12, 2, 4, 1.000, 'pc', 'onion, quartered'),
(13, 2, 5, 2.000, 'pcs', 'tomatoes, quartered'),
(14, 2, 6, 4.000, 'pcs', 'gabi (taro), halved'),
(15, 2, 7, 1.000, 'pc', 'labanos (radish), sliced'),
(16, 2, 8, 1.000, 'bunch', 'sitaw (string beans), cut'),
(17, 2, 9, 1.000, 'pc', 'talong (eggplant), sliced'),
(18, 2, 10, 1.000, 'bunch', 'kangkong'),
(19, 2, 11, 2.000, 'pcs', 'siling haba'),
(20, 2, 12, NULL, '', 'patis (fish sauce), to taste'),
(21, 3, 1, 2.000, 'cups', 'kalabasa (squash), cubed'),
(22, 3, 2, 1.000, 'bunch', 'sitaw, cut into 2-inch pieces'),
(23, 3, 3, 1.500, 'cups', 'coconut milk'),
(24, 3, 4, 1.000, 'tbsp', 'bagoong alamang'),
(25, 3, 5, 3.000, 'cloves', 'garlic, minced'),
(26, 3, 6, 1.000, 'pc', 'onion, chopped'),
(27, 3, 7, 1.000, 'thumb', 'ginger, sliced'),
(28, 3, 8, NULL, '', 'salt and pepper, to taste'),
(29, 4, 1, 1.000, 'cup', 'malagkit (glutinous rice)'),
(30, 4, 2, 5.000, 'cups', 'water'),
(31, 4, 3, 4.000, 'pcs', 'tablea (cacao tablets)'),
(32, 4, 4, 0.333, 'cup', 'sugar'),
(33, 4, 5, 0.250, 'cup', 'evaporated milk'),
(34, 4, 6, 6.000, 'pcs', 'tuyo, fried'),
(35, 5, 1, 6.000, 'pcs', 'saging na saba, halved lengthwise'),
(36, 5, 2, 0.500, 'cup', 'langka (jackfruit), in strips'),
(37, 5, 3, 12.000, 'pcs', 'lumpia wrappers'),
(38, 5, 4, 0.750, 'cup', 'brown sugar'),
(39, 5, 5, 2.000, 'cups', 'cooking oil'),
(40, 6, 1, 3.000, 'cups', 'malagkit (glutinous rice)'),
(41, 6, 2, 2.000, 'cups', 'water'),
(42, 6, 3, 4.000, 'cups', 'coconut milk'),
(43, 6, 4, 2.000, 'cups', 'muscovado sugar'),
(44, 6, 5, 0.500, 'tsp', 'salt'),
(45, 6, 6, NULL, '', 'banana leaves, for lining'),
(46, 7, 1, 10.000, 'pcs', 'egg yolks'),
(47, 7, 2, 1.000, 'can', 'condensed milk'),
(48, 7, 3, 1.000, 'can', 'evaporated milk'),
(49, 7, 4, 1.000, 'tsp', 'calamansi zest'),
(50, 7, 5, 0.500, 'cup', 'white sugar, for caramel');

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE `recipes` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `category_id` tinyint(3) UNSIGNED NOT NULL,
  `title` varchar(120) NOT NULL,
  `description` varchar(500) NOT NULL,
  `servings` tinyint(3) UNSIGNED NOT NULL DEFAULT 4,
  `steps` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `recipes`
--

INSERT INTO `recipes` (`id`, `user_id`, `category_id`, `title`, `description`, `servings`, `steps`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Lola Ising\'s Chicken Adobo', 'The adobo I grew up with: more vinegar than soy, lots of garlic, and simmered until the sauce turns glossy. Even better the next day.', 4, 'Combine the chicken, soy sauce, vinegar, half the garlic, bay leaves and peppercorns. Marinate for at least 30 minutes.\nHeat the oil in a pan and fry the remaining garlic until golden. Set aside for topping.\nBrown the chicken pieces in the same pan, skin side down first.\nPour in the marinade and the water. Bring to a boil without stirring so the vinegar cooks off.\nCover and simmer for 30 minutes, turning the chicken halfway.\nUncover and reduce the sauce until it coats the chicken. Top with the fried garlic and serve with rice.', '2026-10-23 16:35:03', NULL),
(2, 2, 2, 'Sinigang na Baboy sa Sampalok', 'Sour pork soup soured with real tamarind, not the packet. Perfect when it rains.', 6, 'Boil the tamarind in 2 cups of water until soft. Mash, strain, and set the juice aside.\nIn a large pot, bring the pork ribs and the rest of the water to a boil. Skim off the scum.\nAdd the onion and tomatoes. Simmer for 45 minutes until the pork is tender.\nAdd the gabi and radish and cook for 10 minutes.\nPour in the tamarind juice, then add the sitaw and eggplant. Cook for 5 minutes.\nSeason with fish sauce, add the kangkong and siling haba, and turn off the heat.', '2026-10-24 11:35:03', '2026-10-28 11:35:03'),
(3, 3, 3, 'Ginataang Kalabasa at Sitaw', 'Squash and string beans in coconut milk. Budget-friendly, creamy, and good even without meat.', 4, 'Sauté the garlic, onion and ginger in oil until fragrant.\nAdd the shrimp paste and cook for 1 minute.\nPour in the coconut milk and bring to a gentle simmer.\nAdd the squash and cook for 8 minutes until almost tender.\nAdd the sitaw and cook for 3 more minutes. Season with salt and pepper.', '2026-10-25 06:35:03', NULL),
(4, 1, 4, 'Champorado with Tuyo', 'Chocolate rice porridge for cold mornings. The salty tuyo on the side is not optional.', 3, 'Rinse the malagkit rice and bring it to a boil with the water.\nSimmer, stirring often, for 20 minutes until the rice is soft.\nAdd the tablea and stir until fully melted.\nSweeten with sugar to taste.\nServe hot with a swirl of evaporated milk and fried tuyo on the side.', '2026-10-26 01:35:03', NULL),
(5, 2, 5, 'Turon na Saging', 'Banana and langka spring rolls with a crackly caramel coat, the classic afternoon merienda.', 6, 'Roll each banana half in brown sugar.\nPlace on a lumpia wrapper with a strip of langka, then roll up tightly and seal the edge with water.\nHeat the oil and fry the rolls over medium heat until golden.\nSprinkle the rest of the sugar into the oil and let it caramelize, then turn the rolls to coat.\nDrain on a rack (not paper) so the caramel stays crisp.', '2026-10-26 20:35:03', NULL),
(6, 3, 6, 'Biko with Latik', 'Sticky rice cake cooked in coconut milk and muscovado, topped with crunchy latik. Made for every fiesta.', 12, 'Cook the malagkit with 2 cups of water until just done.\nSimmer 2 cups of coconut milk until it separates into oil and brown curds. Strain: those curds are your latik.\nIn a wide pan, heat the remaining coconut milk with the muscovado and salt until thick.\nAdd the cooked rice and stir constantly for 20 minutes until very thick and glossy.\nSpread in a banana leaf-lined pan and top with the latik.', '2026-10-27 15:35:03', NULL),
(7, 1, 7, 'Leche Flan sa Llanera', 'Silky egg-yolk custard with a dark caramel top. Steamed, not baked, the way my tita taught me.', 8, 'Melt the sugar for the caramel directly in the llanera over low heat until amber. Let it harden.\nGently mix the egg yolks, condensed milk, evaporated milk and calamansi zest without whipping in air.\nStrain the mixture twice into the llanera.\nCover with foil and steam over low heat for 40 minutes.\nCool, then chill overnight before turning out onto a plate.', '2026-10-28 10:35:03', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `display_name` varchar(60) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `display_name`, `email`, `password_hash`, `created_at`) VALUES
(1, 'Aling Nena', 'nena@lutongbahay.test', '$2y$10$A0TJtIYRkg/LWTdNOTL3Uu86yUbKBvPC2i6LoshBbYt13FngvvzwK', '2026-10-28 12:35:03'),
(2, 'Mang Tonyo', 'tonyo@lutongbahay.test', '$2y$10$sK/N.lEP3057zw6HpYC2EeBofNweyT/.tgxDVkgzZ1t6ld/JJ9I36', '2026-10-28 12:35:03'),
(3, 'Bea Santos', 'bea@lutongbahay.test', '$2y$10$VwkJ./IkG1PqDzrauG1SR.i7Tk5cPh3xwDJgutVbmaK1XMZxgpAzu', '2026-10-28 12:35:03');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_categories_name` (`name`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_comments_recipe_created` (`recipe_id`,`created_at`),
  ADD KEY `idx_comments_user` (`user_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`user_id`,`recipe_id`),
  ADD KEY `idx_favorites_recipe` (`recipe_id`);

--
-- Indexes for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ingredients_recipe_position` (`recipe_id`,`position`);

--
-- Indexes for table `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_recipes_created` (`created_at`),
  ADD KEY `idx_recipes_user` (`user_id`),
  ADD KEY `idx_recipes_category` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `recipes`
--
ALTER TABLE `recipes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `fk_comments_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `fk_favorites_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_favorites_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD CONSTRAINT `fk_ingredients_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recipes`
--
ALTER TABLE `recipes`
  ADD CONSTRAINT `fk_recipes_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `fk_recipes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

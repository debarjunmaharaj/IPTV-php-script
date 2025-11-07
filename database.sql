-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 17, 2025 at 10:06 AM
-- Server version: 11.4.5-MariaDB
-- PHP Version: 8.3.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bookshel_a1`
--

-- --------------------------------------------------------

--
-- Table structure for table `ads`
--

CREATE TABLE `ads` (
  `id` int(11) NOT NULL,
  `ad_name` varchar(255) NOT NULL,
  `ad_code` text NOT NULL,
  `ad_location` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ads`
--

INSERT INTO `ads` (`id`, `ad_name`, `ad_code`, `ad_location`, `is_active`) VALUES
(1, 'Header Ad', '<a href=\"#\"><img src=\"https://www.adspeed.com/placeholder-728x90-Test.gif\" class=\"img-fluid\"></a>', 'header_ad', 1),
(2, 'Sidebar Ad', '<a href=\"#\"><img src=\"https://placehold.co/600x400\" class=\"img-fluid\"></a>\r\n\r\n<a href=\"#\"><img src=\"https://placehold.co/600x400\" class=\"img-fluid\"></a>', 'sidebar_ad', 1);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `show_in_menu` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `show_in_menu`) VALUES
(1, 'মন্দিরের খবর', 'temple-news', 1),
(2, 'পূজা ও উৎসব', 'puja-and-festivals', 1),
(3, 'আধ্যাত্মিক', 'spiritual', 1),
(4, 'ভিডিও', 'videos', 1),
(5, 'আন্তর্জাতিক', 'আন-তর-জ-ত-ক', 1);

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `featured_image` varchar(255) NOT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `view_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `slug` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `category_id`, `title`, `content`, `featured_image`, `tags`, `view_count`, `created_at`, `slug`) VALUES
(1, 1, 'demo', 'sdfgh', '685168370cca3-channelom2.png', '', 3, '2025-06-17 13:05:59', 'demo'),
(2, 1, 'asdfg', 'sdfg', '685168c5bc4fc-bhotic.png', 'xcvbn, dfgh,', 12, '2025-06-17 13:08:21', 'sdf'),
(3, 1, 'sdfgh', '<iframe src=\"https://www.facebook.com/plugins/video.php?height=314&href=https%3A%2F%2Fwww.facebook.com%2Fchannelomnews%2Fvideos%2F726794843630691%2F&show_text=false&width=560&t=0\" width=\"560\" height=\"314\" style=\"border:none;overflow:hidden\" scrolling=\"no\" frameborder=\"0\" allowfullscreen=\"true\" allow=\"autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share\" allowFullScreen=\"true\"></iframe>', '', '', 1, '2025-06-17 13:10:39', 'sdfg');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_name` varchar(100) NOT NULL,
  `setting_value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_name`, `setting_value`) VALUES
('breaking_news_ticker', 'ঢাকেশ্বরী মন্দিরে বিশেষ পূজা অনুষ্ঠিত। সারাদেশে জন্মাষ্টমী উদযাপিত।'),
('facebook_url', 'https://facebook.com/'),
('footer_copyright', 'All rights reserved © 2025 Channel OM'),
('homepage_meta_description', 'আপনার মন্দিরের সব খবর, পূজা ও উৎসবের সময়সূচী, এবং আধ্যাত্মিক আলোচনার জন্য নির্ভরযোগ্য অনলাইন পোর্টাল।'),
('homepage_meta_keywords', 'temple, mandir, puja, festival, hindu, sanatan, spiritual, মন্দির, পূজা, উৎসব, হিন্দু, সনাতন'),
('homepage_meta_title', 'TV Channel - আপনার মন্দিরের নির্ভরযোগ্য সংবাদ মাধ্যম'),
('homepage_video_iframe', '<iframe src=\"https://www.facebook.com/plugins/video.php?height=314&href=https%3A%2F%2Fwww.facebook.com%2Fchannelomnews%2Fvideos%2F726794843630691%2F&show_text=false&width=560&t=0\" width=\"560\" height=\"314\" style=\"border:none;overflow:hidden\" scrolling=\"no\" frameborder=\"0\" allowfullscreen=\"true\" allow=\"autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share\" allowFullScreen=\"true\"></iframe>'),
('site_favicon', '68516824457fb-channelom2.png'),
('site_logo', '6851682444764-channelom2.png'),
('site_name', 'Channel OM'),
('twitter_url', 'https://twitter.com/'),
('youtube_url', 'https://youtube.com/');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$0NiDxSqiiaaTtekIBjltAedCUikp/Bc3xkuK4URsDW1QEv91jEwXS');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ads`
--
ALTER TABLE `ads`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `posts_ibfk_1` (`category_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ads`
--
ALTER TABLE `ads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

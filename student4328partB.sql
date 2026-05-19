-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Εξυπηρετητής: localhost:3306
-- Χρόνος δημιουργίας: 03 Φεβ 2026 στις 02:23:46
-- Έκδοση διακομιστή: 11.4.9-MariaDB-ubu2404
-- Έκδοση PHP: 8.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Βάση δεδομένων: `student4328partB`
--

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `announcements`
--

CREATE TABLE `announcements` (
  `id` int(10) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `topic` varchar(255) NOT NULL,
  `body` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Άδειασμα δεδομένων του πίνακα `announcements`
--

INSERT INTO `announcements` (`id`, `date`, `topic`, `body`) VALUES
(1, '2026-01-05', 'Έναρξη μαθημάτων', 'Τα μαθήματα αρχίζουν την Δευτέρα 08/01/2026.'),
(2, '2026-01-10', 'Ανάρτηση εργασίας', 'Η 1η εργασία έχει ανακοινωθεί στην ιστοσελίδα Εργασίες.'),
(3, '2026-01-12', 'Ανάρτηση εργασίας', 'Η 2η εργασία έχει ανακοινωθεί στην ιστοσελίδα Εργασίες.'),
(4, '2026-01-15', 'Ανακοίνωση εξεταστικής', 'Η εξεταστική ξεκινάει την Δευτέρα 20/01/2026 και το μάθημα εξετάζεται στις 02/02/2026.'),
(5, '2026-01-17', 'Αναβολή μαθήματος', 'Το αυριανό μάθημα στις 18/01/2026, αναβάλλεται για τις 19/01/2026, κανονικά στην αίθουσα Η6'),
(6, '2026-01-30', 'Υποβλήθηκε η εργασία 4', 'Η ημερομηνία παράδοσης της εργασίας είναι 2026-02-12');

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `sender_email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Άδειασμα δεδομένων του πίνακα `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `sender_email`, `subject`, `body`, `created_at`) VALUES
(1, 'mastartzi@csd.auth.gr', 'test', 'test1', '2026-01-30 17:41:20'),
(2, 'mastartzi@csd.auth.gr', 'test', 'test', '2026-01-31 17:05:53'),
(3, 'mastartzi@csd.auth.gr', 'test', 'test2', '2026-01-31 17:14:30'),
(4, 'mariadim1@csd.auth.gr', 'test', 'test3', '2026-01-31 17:15:21'),
(5, 'mariadim1@csd.auth.gr', 'test', 'test3', '2026-01-31 17:24:49'),
(6, 'mariadim1@csd.auth.gr', 'DOKIMI APOSTOLIS', 'TEST', '2026-01-31 17:25:32'),
(7, 'mastartzi@csd.auth.gr', '1', '1', '2026-01-31 17:26:42'),
(8, 'mastartzi@csd.auth.gr', 'Ερωτηση για εξεταστικη', '1', '2026-01-31 17:32:33'),
(9, 'mastartzi@csd.auth.gr', 'test', 'τεστ', '2026-01-31 17:43:44'),
(10, 'mastartzi@csd.auth.gr', 'test', 'τεστ', '2026-01-31 17:47:58'),
(11, 'astartzimartha500@gmail.com', 'test', '333', '2026-01-31 17:48:40'),
(12, 'astartzimartha500@gmail.com', 'test', '333', '2026-01-31 18:07:18');

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `contact_message_recipients`
--

CREATE TABLE `contact_message_recipients` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `tutor_user_id` int(11) NOT NULL,
  `tutor_email` varchar(255) NOT NULL,
  `delivered_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Άδειασμα δεδομένων του πίνακα `contact_message_recipients`
--

INSERT INTO `contact_message_recipients` (`id`, `message_id`, `tutor_user_id`, `tutor_email`, `delivered_at`) VALUES
(1, 9, 1, 'mastartzi@csd.auth.gr', '2026-01-31 17:43:44'),
(2, 9, 2, 'nikosios1@auth.gr', '2026-01-31 17:43:44'),
(3, 9, 5, 'tutor@test.gr', '2026-01-31 17:43:44'),
(4, 9, 7, 'tutor@auth.gr', '2026-01-31 17:43:44'),
(5, 9, 10, 'anastasiapan1@auth.gr', '2026-01-31 17:43:44'),
(6, 10, 1, 'mastartzi@csd.auth.gr', '2026-01-31 17:47:58'),
(7, 10, 2, 'nikosios1@auth.gr', '2026-01-31 17:47:58'),
(8, 10, 5, 'tutor@test.gr', '2026-01-31 17:47:58'),
(9, 10, 7, 'tutor@auth.gr', '2026-01-31 17:47:58'),
(10, 10, 10, 'anastasiapan1@auth.gr', '2026-01-31 17:47:58'),
(11, 11, 1, 'mastartzi@csd.auth.gr', '2026-01-31 17:48:40'),
(12, 11, 2, 'nikosios1@auth.gr', '2026-01-31 17:48:40'),
(13, 11, 5, 'tutor@test.gr', '2026-01-31 17:48:40'),
(14, 11, 7, 'tutor@auth.gr', '2026-01-31 17:48:40'),
(15, 11, 10, 'anastasiapan1@auth.gr', '2026-01-31 17:48:40'),
(16, 12, 1, 'mastartzi@csd.auth.gr', '2026-01-31 18:07:18'),
(17, 12, 2, 'nikosios1@auth.gr', '2026-01-31 18:07:18'),
(18, 12, 5, 'tutor@test.gr', '2026-01-31 18:07:18'),
(19, 12, 7, 'tutor@auth.gr', '2026-01-31 18:07:18'),
(20, 12, 10, 'anastasiapan1@auth.gr', '2026-01-31 18:07:18');

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `documents`
--

CREATE TABLE `documents` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `file_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Άδειασμα δεδομένων του πίνακα `documents`
--

INSERT INTO `documents` (`id`, `title`, `description`, `file_path`) VALUES
(1, 'Εισαγωγή', 'Εισαγωγικό υλικό για το μάθημα.', 'files/file1.docx'),
(2, 'Συμπληρωματικές Ασκήσεις', 'Συμπληρωματικές σημειώσεις και παραδείγματα.', 'files/file2.docx'),
(3, '1ο κεφάλαιο', 'Υλικό μαθήματος 1ου κεφαλαίου.', 'files/file3.docx'),
(4, '2ο κεφάλαιο', 'Υλικό μαθήματος 2ου κεφαλαίου.', 'files/file4.docx'),
(5, '3ο κεφάλαιο', 'Υλικό μαθήματος 3ου κεφαλαίου.', 'files/file5.docx'),
(6, '4ο κεφάλαιο', 'Υλικό μαθήματος 4ου κεφαλαίου', 'files/file6.docx');

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `homework`
--

CREATE TABLE `homework` (
  `id` int(11) NOT NULL,
  `goals` text NOT NULL,
  `deliverables` text NOT NULL,
  `due_date` date NOT NULL,
  `file_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Άδειασμα δεδομένων του πίνακα `homework`
--

INSERT INTO `homework` (`id`, `goals`, `deliverables`, `due_date`, `file_path`) VALUES
(1, 'Εξοικείωση με δημιουργία στατικών ιστοσελίδων.\r\nΚατανόηση δομής HTML και χρήσης CSS.\r\nΧρήση υπερσυνδέσμων και αρχείων.', 'Γραπτή αναφορά σε Word\r\nΠαρουσίαση σε PowerPoint', '2026-02-05', 'files/ergasia1.docx'),
(2, 'Εμβάθυνση στη χρήση φορμών HTML.\r\nΟργάνωση περιεχομένου με div.\r\nΔιασύνδεση πολλαπλών σελίδων.', 'Γραπτή αναφορά σε Word\r\nΠαρουσίαση σε PowerPoint', '2026-02-05', 'files/ergasia2.docx'),
(4, 'Να υλοποιήσετε μια δυναμική ιστοσελίδα.', 'Τα αρχεία html και css.\r\nΈνα report για το τι έχετε υλοποιήσει.', '2026-02-12', 'files/ergasia3.docx');

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `login` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('Tutor','Student') NOT NULL,
  `password` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Άδειασμα δεδομένων του πίνακα `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `login`, `password_hash`, `role`, `password`) VALUES
(1, 'Martha', 'Astartzi', 'mastartzi@csd.auth.gr', '$2y$10$zfcQKAxOosqJFZiqM0kb4eMrvrhS87D9td2r.lWbZFEel.hLTHjBW', 'Tutor', 'martha'),
(2, 'Nikos', 'Iosifidis', 'nikosios1@auth.gr', '$2y$10$IEGp7E.0N18FFyFqL6QJCuRmv095YH7LRVgtw0YueAbjWZ4MnUL1W', 'Tutor', 'nikos'),
(3, 'Georgios', 'Papadopoulos', 'geopapa1@csd.auth.gr', '$2y$10$f08lVlr4YtGlX0k9WNU1g.5rtObJ8.Fn2.wuoUAyw0IP6JiNiK.fG', 'Student', 'georgios'),
(4, 'Maria', 'Dimitriou', 'mariadim1@csd.auth.gr', '$2y$10$cJujkcYxXROoMmqcaT9Sd.ENYLpSCK7BTj8ivBlwPA.Ry7WBw/vUa', 'Student', 'maria'),
(5, 'Test', 'Tutor', 'tutor@test.gr', '$2y$10$6yDN2a9Nn7Pur61jrQN.POTccARSo0eV6dSGzCOCW3fgGRHpmu8x.', 'Tutor', '1234'),
(6, 'Test', 'Student', 'student@test.gr', '$2y$10$GbDgH0nAapstYHiCprenaO92egACl2sa0MeNPldewwuVSk1wKGy9y', 'Student', '1234'),
(7, 'Demo', 'Tutor', 'tutor@csd.auth.gr', '$2y$10$5jGwgY.NfZvW./vmg./xXusIIrE6tRtNqkyLjm78TV1zAtr93V68C', 'Tutor', '1234'),
(8, 'Demo', 'Student', 'student@csd.auth.gr', '$2y$10$ko1MrdZi0fzNI3mw34u8OulbCbxcYFnzadUUz148yryiRbvB145ua', 'Student', '1234'),
(9, 'John', 'Antonopoulos', 'johnanto1@csd.auth.gr', '$2y$10$6c7PkRChjw8vO2PXi9.EmO.Lp9Cy7.bb2nKrWeCa30Pojyn7wJ.TG', 'Student', 'john'),
(10, 'Anastasia', 'Panagiotou', 'anastasiapan1@auth.gr', '$2y$10$WCA2QDQ9ynqUyu66V60xpex.qaWQYwNu5putwK7pGLnE5XGb5e7pm', 'Tutor', 'anastasia');

--
-- Ευρετήρια για άχρηστους πίνακες
--

--
-- Ευρετήρια για πίνακα `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Ευρετήρια για πίνακα `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Ευρετήρια για πίνακα `contact_message_recipients`
--
ALTER TABLE `contact_message_recipients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_message_id` (`message_id`),
  ADD KEY `idx_tutor_user_id` (`tutor_user_id`);

--
-- Ευρετήρια για πίνακα `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`);

--
-- Ευρετήρια για πίνακα `homework`
--
ALTER TABLE `homework`
  ADD PRIMARY KEY (`id`);

--
-- Ευρετήρια για πίνακα `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`);

--
-- AUTO_INCREMENT για άχρηστους πίνακες
--

--
-- AUTO_INCREMENT για πίνακα `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT για πίνακα `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT για πίνακα `contact_message_recipients`
--
ALTER TABLE `contact_message_recipients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT για πίνακα `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT για πίνακα `homework`
--
ALTER TABLE `homework`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT για πίνακα `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Περιορισμοί για άχρηστους πίνακες
--

--
-- Περιορισμοί για πίνακα `contact_message_recipients`
--
ALTER TABLE `contact_message_recipients`
  ADD CONSTRAINT `fk_cmr_message` FOREIGN KEY (`message_id`) REFERENCES `contact_messages` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

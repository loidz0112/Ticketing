-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th1 22, 2026 lúc 03:20 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `ticketing_db`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phan_loai`
--

CREATE TABLE `phan_loai` (
  `id` int(11) NOT NULL,
  `ten` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `phan_loai`
--

INSERT INTO `phan_loai` (`id`, `ten`) VALUES
(3, 'Mạng'),
(1, 'Phần cứng'),
(2, 'Phần mềm');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `tieu_de` varchar(200) NOT NULL,
  `mo_ta` text NOT NULL,
  `phan_loai_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `trang_thai` enum('Mới','Đang xử lý','Đã hoàn thành','Từ chối') NOT NULL DEFAULT 'Mới',
  `ly_do_tu_choi` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `tickets`
--

INSERT INTO `tickets` (`id`, `tieu_de`, `mo_ta`, `phan_loai_id`, `user_id`, `assigned_to`, `trang_thai`, `ly_do_tu_choi`, `created_at`, `updated_at`) VALUES
(1, 'Không thể kết nối Wifi', 'k thể kết nối wifi nhưng LAN vẫn hoạt động', 3, 3, 2, 'Đã hoàn thành', NULL, '2026-01-17 05:40:29', '2026-01-17 05:45:33'),
(2, 'Máy tính gặp lỗi', 'Lỗi không xác định', 2, 3, 4, 'Đang xử lý', NULL, '2026-01-22 19:16:43', '2026-01-22 19:18:52');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ticket_attachments`
--

CREATE TABLE `ticket_attachments` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_size` int(11) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ticket_notes`
--

CREATE TABLE `ticket_notes` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `technician_id` int(11) NOT NULL,
  `note` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `ticket_notes`
--

INSERT INTO `ticket_notes` (`id`, `ticket_id`, `technician_id`, `note`, `created_at`) VALUES
(1, 1, 2, 'hỏng ăng ten phát tín hiệu từ morden', '2026-01-17 05:45:33'),
(2, 2, 4, 'Đang tìm hiểu nguyên nhân và khắc phục', '2026-01-22 19:18:52');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','technician','user') NOT NULL DEFAULT 'user',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password_hash`, `role`, `created_at`) VALUES
(1, 'Quản trị viên', 'admin@gmail.com', '$2y$10$sFvhhY4wXB3OQ/jO2duMIuu0atO6gJedcctChGyODPyEjrld48hhi', 'admin', '2026-01-17 05:09:51'),
(2, 'Thợ kỹ thuật 01', 'tech@gmail.com', '$2y$10$qqGaOn5Pzc.ixgfLFOSsdeRQcyyLVQHbxWqRjGujAcBNtQxjIcVDa', 'technician', '2026-01-17 05:09:51'),
(3, 'Người dùng 01', 'user@gmail.com', '$2y$10$AqWQ/KPrDRuFRrvRrlpvCexEtvocYCLt327R9VnpROTePnbia8Jr.', 'user', '2026-01-17 05:09:51'),
(4, 'Thợ kỹ thuật 02', 'tech02@gmail.com', '$2y$10$hoPPcA56R6s4d6BPzaSLJuwsY0DR4yHr7dS0SRXQkfmZONRfo2Jc2', 'technician', '2026-01-17 05:43:44');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `phan_loai`
--
ALTER TABLE `phan_loai`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ten` (`ten`);

--
-- Chỉ mục cho bảng `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `phan_loai_id` (`phan_loai_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Chỉ mục cho bảng `ticket_attachments`
--
ALTER TABLE `ticket_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Chỉ mục cho bảng `ticket_notes`
--
ALTER TABLE `ticket_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `technician_id` (`technician_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `phan_loai`
--
ALTER TABLE `phan_loai`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `ticket_attachments`
--
ALTER TABLE `ticket_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `ticket_notes`
--
ALTER TABLE `ticket_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`phan_loai_id`) REFERENCES `phan_loai` (`id`),
  ADD CONSTRAINT `tickets_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tickets_ibfk_3` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `ticket_attachments`
--
ALTER TABLE `ticket_attachments`
  ADD CONSTRAINT `ticket_attachments_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_attachments_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `ticket_notes`
--
ALTER TABLE `ticket_notes`
  ADD CONSTRAINT `ticket_notes_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_notes_ibfk_2` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

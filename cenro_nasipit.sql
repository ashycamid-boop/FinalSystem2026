-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 04, 2026 at 05:32 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cenro_nasipit`
--

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `id` int(11) NOT NULL,
  `property_number` varchar(100) DEFAULT NULL,
  `office_division` varchar(255) DEFAULT NULL,
  `equipment_type` varchar(100) DEFAULT NULL,
  `year_acquired` year(4) DEFAULT NULL,
  `shelf_life` varchar(50) DEFAULT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `processor` varchar(255) DEFAULT NULL,
  `ram_size` varchar(50) DEFAULT NULL,
  `gpu` varchar(255) DEFAULT NULL,
  `range_category` varchar(100) DEFAULT NULL,
  `computer_name` varchar(255) DEFAULT NULL,
  `os_version` varchar(255) DEFAULT NULL,
  `office_productivity` varchar(255) DEFAULT NULL,
  `endpoint_protection` varchar(255) DEFAULT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `accountable_person` varchar(255) DEFAULT NULL,
  `accountable_person_id` int(11) DEFAULT NULL,
  `accountable_sex` varchar(20) DEFAULT NULL,
  `accountable_employment` varchar(100) DEFAULT NULL,
  `actual_user` varchar(255) DEFAULT NULL,
  `actual_user_id` int(11) DEFAULT NULL,
  `actual_user_sex` varchar(20) DEFAULT NULL,
  `actual_user_employment` varchar(100) DEFAULT NULL,
  `nature_of_work` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `qr_code_path` varchar(255) DEFAULT NULL,
  `status` enum('Available','In Use','Under Maintenance','Disposed') DEFAULT 'In Use',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment`
--

INSERT INTO `equipment` (`id`, `property_number`, `office_division`, `equipment_type`, `year_acquired`, `shelf_life`, `brand`, `model`, `processor`, `ram_size`, `gpu`, `range_category`, `computer_name`, `os_version`, `office_productivity`, `endpoint_protection`, `serial_number`, `accountable_person`, `accountable_person_id`, `accountable_sex`, `accountable_employment`, `actual_user`, `actual_user_id`, `actual_user_sex`, `actual_user_employment`, `nature_of_work`, `remarks`, `qr_code_path`, `status`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
(1, 'UPS-2021-04-01', 'Office sample', 'UPS', '2025', 'Beyond 5 Years', 'Brand Sample', 'sample model', 'sample Processor', 'RAM Sample', 'GPU Sample', NULL, 'Computer Name Sample', 'Windows 10', 'Google Workspace (Docs, Sheets, Slides)', 'Avast', 'Serial Sample', '10', NULL, 'Male', 'Part-Time', '10', NULL, 'Female', 'Project-Based', 'Research / Planning', 'Sample ', 'uploads/qr/eq-1.png', 'In Use', '2025-12-23 00:29:29', '2026-01-15 04:19:28', NULL, NULL),
(2, 'DC-2023-04-01', 'IT Department', 'Desktop Computer', '2024', 'Within 5 Years', 'Dell', 'OptiPlex 7090', 'Intel Core i7-10700T', '16GB DDR4', 'Intel UHD Graphics 630', NULL, 'IT-DESKTOP-001', 'Windows 11', 'Microsoft 365 (Office 365)', 'Windows Defender / Windows Firewall', 'SN-DEL-123456789', '10', NULL, 'Female', 'Part-Time', '7', NULL, 'Female', 'Part-Time', 'Procurement / Supply', '', 'uploads/qr/eq-2.png', 'In Use', '2025-12-23 01:40:18', '2025-12-25 07:55:10', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `service_requests`
--

CREATE TABLE `service_requests` (
  `id` int(10) UNSIGNED NOT NULL,
  `ticket_no` varchar(64) NOT NULL,
  `ticket_date` date NOT NULL,
  `requester_name` varchar(255) DEFAULT NULL,
  `requester_position` varchar(150) DEFAULT NULL,
  `requester_office` varchar(150) DEFAULT NULL,
  `requester_division` varchar(150) DEFAULT NULL,
  `requester_phone` varchar(50) DEFAULT NULL,
  `requester_email` varchar(150) DEFAULT NULL,
  `request_type` varchar(150) DEFAULT NULL,
  `request_description` text DEFAULT NULL,
  `feedback_rating` varchar(50) DEFAULT NULL,
  `requester_signature_path` varchar(255) DEFAULT NULL,
  `auth1_name` varchar(255) DEFAULT NULL,
  `auth1_position` varchar(150) DEFAULT NULL,
  `auth1_date` date DEFAULT NULL,
  `auth1_signature_path` varchar(255) DEFAULT NULL,
  `auth2_name` varchar(255) DEFAULT NULL,
  `auth2_position` varchar(150) DEFAULT NULL,
  `auth2_date` date DEFAULT NULL,
  `auth2_signature_path` varchar(255) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'open',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `ack_signature_path` text DEFAULT NULL,
  `acknowledged_at` datetime DEFAULT NULL,
  `acknowledged_by_name` varchar(191) DEFAULT NULL,
  `acknowledged_by` varchar(150) DEFAULT NULL,
  `rating` varchar(32) DEFAULT NULL,
  `rating_comment` text DEFAULT NULL,
  `rated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_requests`
--

INSERT INTO `service_requests` (`id`, `ticket_no`, `ticket_date`, `requester_name`, `requester_position`, `requester_office`, `requester_division`, `requester_phone`, `requester_email`, `request_type`, `request_description`, `feedback_rating`, `requester_signature_path`, `auth1_name`, `auth1_position`, `auth1_date`, `auth1_signature_path`, `auth2_name`, `auth2_position`, `auth2_date`, `auth2_signature_path`, `status`, `created_by`, `created_at`, `updated_at`, `ack_signature_path`, `acknowledged_at`, `acknowledged_by_name`, `acknowledged_by`, `rating`, `rating_comment`, `rated_at`) VALUES
(11, '2026-01-0001', '2026-01-16', 'Jay Ivan Tadena', 'Project support staff', 'Support Unit', 'Conservation Development Section', '09123276243', 'jayivan@gmail.com', 'Assist in the orientation of watershed', 'FIRST TRY', NULL, 'public/uploads/signatures/sig_CN-2026-01-0001_requester_1768548714.png', 'Ashy Sultan', 'Sample Title', '2026-01-16', 'public/uploads/signatures/auth1_6969e9dddee72.png', 'Yashy Sultan', 'Sample Title', '2026-01-16', 'public/uploads/signatures/auth2_6969e9dddf225.png', 'Pending', 10, '2026-01-16 07:31:54', '2026-02-17 07:55:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, '2026-01-0002', '2026-01-16', 'Jay Ivan Tadena', 'Project support staff', 'Support Unit', 'Conservation Development Section', '09123276243', 'jayivan@gmail.com', 'Paano ba magmahal?', 'HAHslhdkjsdgKJZDFH', 'excellent', 'public/uploads/signatures/sig_CN-2026-01-0002_requester_1768550383.png', 'Ashy Sultan', 'Sample Title', '2026-01-16', 'public/uploads/signatures/auth1_6969f09192ec2.png', 'Yashy Sultan', 'Sample Title', '2026-01-16', 'public/uploads/signatures/auth2_6969f09193334.png', 'Completed', 10, '2026-01-16 07:59:43', '2026-02-02 03:19:54', 'public/uploads/ack_signatures/ack_12_1768797494.png', '2026-01-19 12:38:14', NULL, 'Jay Ivan Tadena', NULL, NULL, NULL),
(13, '2026-01-0003', '2026-01-19', 'Jay Ivan Tadena', 'Project support staff', 'Support Unit', 'Sample devision', '09123276243', 'jayivan@gmail.com', 'San ako nagkulang?', 'HAHAHHAHA', NULL, 'public/uploads/signatures/sig_CN-2026-01-0003_requester_1768799596.png', 'Ashy Sultan', 'Sample Title', '2026-02-17', 'public/uploads/signatures/auth1_6993c84035a5d.png', 'Yashy Sultan', 'Sample Title', '2026-02-17', 'public/uploads/signatures/auth2_6993c84035d7c.png', 'Ongoing', 10, '2026-01-19 05:13:16', '2026-02-17 01:45:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, '2026-01-0004', '2026-01-19', 'Jay Ivan Tadena', 'Project support staff', 'Support Unit', 'Sample devision', '09123276243', 'jayivan@gmail.com', 'HAHAHAHA mwaa', 'HAHAHHAHAHA', NULL, 'public/uploads/signatures/sig_CN-2026-01-0004_requester_1768804374.png', 'Ashy Sultan', 'Sample Title', '2026-02-17', 'public/uploads/signatures/auth1_6993c7c80a138.png', 'Yashy Sultan', 'Sample Title', '2026-02-17', 'public/uploads/signatures/auth2_6993c7c80a3f3.png', 'Pending', 10, '2026-01-19 06:32:54', '2026-02-17 07:54:55', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, '2026-01-0005', '2026-01-25', 'Rich Balaido', 'Project support staff', 'Support Unit', 'Conservation Development Section', '09123276243', 'rich@gmail.com', 'Assist in the orientation of watershed', 'HELLLOOOO', NULL, 'public/uploads/signatures/sig_CN-2026-01-0005_requester_1769316251.png', 'Ashy Sultan', 'Sample Title', '2026-02-17', 'public/uploads/signatures/auth1_6993c1bbed9d6.png', 'Yashy Sultan', 'Sample Title', '2026-02-17', 'public/uploads/signatures/auth2_6993c1bbee1b3.png', 'Ongoing', 8, '2026-01-25 04:44:11', '2026-02-17 01:58:12', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, '2026-02-0001', '2026-02-03', 'Jay Ivan Tadena', 'Project support staff', 'Support Unit', 'Conservation Development Section', '09123276243', 'jayivan@gmail.com', 'HELP ME TO FIX PROJECTOR', 'HJAHAHHAjkshja', 'excellent', 'public/uploads/signatures/sig_CN-2026-02-0001_requester_1770101383.png', 'Ashy Sultan', 'Sample Title', '2026-02-03', 'public/uploads/signatures/auth1_69819b22cce62.png', 'Yashy Sultan', 'Sample Title', '2026-02-03', 'public/uploads/signatures/auth2_69819b22cd41a.png', 'Ongoing', 10, '2026-02-03 06:49:43', '2026-02-20 22:31:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(17, '2026-02-0002', '2026-02-06', 'Joel Caluya', 'Project support staff', 'Support Unit', 'Sample devision', '091234567891', 'joel@gmail.com', 'HELP ME TO FIX PROJECTOR', 'JHLBSDJBJ,N', 'excellent', 'public/uploads/signatures/sig_CN-2026-02-0002_requester_1770361305.png', 'Ashy Sultan', 'Sample Title', '2026-02-06', 'public/uploads/signatures/auth1_69859d3990f1f.png', 'Yashy Sultan', 'Sample Title', '2026-02-06', 'public/uploads/signatures/auth2_69859d39919b7.png', 'Completed', 7, '2026-02-06 07:01:45', '2026-02-09 02:27:54', 'public/uploads/ack_signatures/ack_17_1770604074.png', '2026-02-09 10:27:54', NULL, 'Joel Caluya', NULL, NULL, NULL),
(18, '2026-02-0003', '2026-02-21', 'Roslain Sultan Camid', 'HAHAHHAHA', 'HAHAHAHHAHA', 'HAHAHHAHA', '09837633547', 'roslain@gmail.com', 'HAHHAHAHHA', 'HAHAHHAHHA', NULL, 'public/uploads/signatures/sig_CN-2026-02-0003_requester_1771635009.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 13, '2026-02-21 00:50:09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `service_request_actions`
--

CREATE TABLE `service_request_actions` (
  `id` int(10) UNSIGNED NOT NULL,
  `service_request_id` int(10) UNSIGNED NOT NULL,
  `action_date` date DEFAULT NULL,
  `action_time` time DEFAULT NULL,
  `action_details` text DEFAULT NULL,
  `action_staff_id` int(11) DEFAULT NULL,
  `action_signature_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_request_actions`
--

INSERT INTO `service_request_actions` (`id`, `service_request_id`, `action_date`, `action_time`, `action_details`, `action_staff_id`, `action_signature_path`, `created_at`, `updated_at`) VALUES
(41, 12, '2026-01-16', '16:02:00', 'HAHAAAAAA', 11, 'public/uploads/signatures/action_12_6969f091a44be.png', '2026-02-02 03:06:05', NULL),
(60, 17, '2026-02-06', '15:50:00', 'Paki ayos ang sarili', 11, 'public/uploads/signatures/action_17_6989441e4936c.png', '2026-02-09 02:19:10', NULL),
(65, 14, '2026-02-17', '09:43:00', 'I MISS YOU', 11, NULL, '2026-02-17 01:43:36', NULL),
(66, 13, '2026-02-17', '09:45:00', 'HAHAHAHHA', 11, NULL, '2026-02-17 01:45:36', NULL),
(67, 15, '2026-02-17', '09:17:00', 'i miss you so much', 11, 'public/uploads/signatures/action_15_6993c1bc13953.png', '2026-02-17 01:58:12', NULL),
(68, 15, '2026-02-17', '09:58:00', 'HAHHAHA', 11, NULL, '2026-02-17 01:58:12', NULL),
(73, 11, '2026-01-16', '15:33:00', 'HELLO PAKI AYOS FO', 11, 'public/uploads/signatures/action_11_69941dd63aa44.png', '2026-02-17 07:50:46', NULL),
(74, 11, '2026-01-16', '15:34:00', 'PAKI AYOS ANG SARILI', 11, 'public/uploads/signatures/action_11_69941dd63ca85.png', '2026-02-17 07:50:46', NULL),
(75, 11, '2026-01-16', '15:34:00', 'Done Set up', 11, 'public/uploads/signatures/action_11_69941dd63d606.png', '2026-02-17 07:50:46', NULL),
(76, 11, '2026-02-17', '09:58:00', 'HAHAHHAHA', 11, 'public/uploads/signatures/action_11_69941dd63f9db.png', '2026-02-17 07:50:46', NULL),
(77, 16, '2026-02-03', '14:51:00', 'Joel please fix the Projector', 11, NULL, '2026-02-20 22:31:47', NULL),
(78, 16, '2026-02-03', '14:52:00', 'All done', 11, 'public/uploads/signatures/action_16_69819b45656ff.png', '2026-02-20 22:31:47', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `spot_reports`
--

CREATE TABLE `spot_reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference_no` varchar(100) NOT NULL,
  `incident_datetime` datetime DEFAULT NULL,
  `memo_date` datetime DEFAULT NULL,
  `location` text DEFAULT NULL,
  `summary` text DEFAULT NULL,
  `team_leader` varchar(255) DEFAULT NULL,
  `custodian` varchar(255) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Draft',
  `status_comment` text DEFAULT NULL,
  `case_status` varchar(64) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `submitted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `spot_reports`
--

INSERT INTO `spot_reports` (`id`, `reference_no`, `incident_datetime`, `memo_date`, `location`, `summary`, `team_leader`, `custodian`, `status`, `status_comment`, `case_status`, `created_at`, `updated_at`, `submitted_by`) VALUES
(7, '2026-02-02-0002', '2026-02-02 12:14:00', '2026-02-02 12:14:00', 'Brgy Rizal', 'HAHAHHAHA', 'HAHHAHHA', 'Sample name', 'Approved', NULL, 'Under Investigation', '2026-02-02 12:15:50', '2026-02-21 08:59:55', 8),
(8, '2026-02-02-0003', '2026-02-02 12:24:00', '2026-02-02 12:24:00', 'Brgy Rizal', 'glugujhj,hl', 'HAHHAHHA', 'Sample name', 'Approved', NULL, 'Under Investigation', '2026-02-02 12:26:22', '2026-02-06 14:40:49', 8),
(9, '2026-02-02-0004', '2026-02-02 16:29:00', '2026-02-02 16:29:00', 'Brgy Rizal', 'HAHHAHAH', 'HAHHAHHA', 'Sample name', 'Approved', NULL, 'Under Investigation', '2026-02-02 16:29:52', '2026-02-21 16:05:41', 8),
(10, '2026-02-02-0005', '2026-02-02 17:39:00', '2026-02-02 17:39:00', 'Brgy Rizal, Buenavsita Agusan del Norte', 'Nadkapan kahoy tapos nakita nako siya na naay lain.', 'Hashmine Camid', 'Jasmine Camid', 'Rejected', 'HAHAHHA', 'For Filing', '2026-02-02 17:43:22', '2026-02-21 07:48:55', 8),
(11, '2026-02-21-0001', '2026-02-21 08:52:00', '2026-02-21 08:52:00', 'HAHAHAHAH', 'HAHAHAHAH', 'AHHAHAH', 'HSHAHSHA', 'Approved', '', 'Filed in Court', '2026-02-21 08:54:06', '2026-02-21 14:03:00', 13),
(12, '2026-03-02-0001', '2026-03-02 13:32:00', '2026-03-02 13:30:00', 'HAHHAAHAH', 'HAHAHAHHAH', 'AHHAHHA', 'HAHHAHA', 'Pending', NULL, NULL, '2026-03-02 13:32:49', NULL, 8);

-- --------------------------------------------------------

--
-- Table structure for table `spot_report_files`
--

CREATE TABLE `spot_report_files` (
  `id` int(10) UNSIGNED NOT NULL,
  `report_id` int(10) UNSIGNED NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_path` varchar(1024) NOT NULL,
  `orig_name` varchar(512) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `spot_report_files`
--

INSERT INTO `spot_report_files` (`id`, `report_id`, `file_type`, `file_path`, `orig_name`, `created_at`) VALUES
(31, 7, 'person_evidence', '/uploads/spot_reports/2026-02-02-0002/person_698024f63a843.jpg', 'person#0:c5d44d78-9db2-48a0-ab74-b064c4d95f43.jpg', '2026-02-02 12:15:50'),
(32, 7, 'vehicle_evidence', '/uploads/spot_reports/2026-02-02-0002/vehicle_698024f63b045.jpg', 'vehicle#0:11d0c99c-766a-4a5d-b043-dcf8c3fbd6d7.jpg', '2026-02-02 12:15:50'),
(33, 7, 'item_evidence', '/uploads/spot_reports/2026-02-02-0002/item_698024f63bdd3.jpg', 'item#0:bd710a6a-9973-49c7-8d7e-f432cf113d08.jpg', '2026-02-02 12:15:50'),
(34, 7, 'evidence', '/uploads/spot_reports/2026-02-02-0002/evi_698024f63c98c.jpg', '11d0c99c-766a-4a5d-b043-dcf8c3fbd6d7.jpg', '2026-02-02 12:15:50'),
(35, 7, 'pdf', '/uploads/spot_reports/2026-02-02-0002/doc_698024f63d352.pdf', 'Task_2_VeriAttend_Report.pdf', '2026-02-02 12:15:50'),
(36, 8, 'item_evidence', '/uploads/spot_reports/2026-02-02-0003/item_6980276e31148.jpg', 'item#0:11d0c99c-766a-4a5d-b043-dcf8c3fbd6d7.jpg', '2026-02-02 12:26:22'),
(37, 8, 'item_evidence', '/uploads/spot_reports/2026-02-02-0003/item_6980276e32d9c.jpg', 'item#1:11d0c99c-766a-4a5d-b043-dcf8c3fbd6d7.jpg', '2026-02-02 12:26:22'),
(38, 9, 'vehicle_evidence', '/uploads/spot_reports/2026-02-02-0004/vehicle_698060804dc87.jpg', 'vehicle#0:c039b782-bcc0-4eda-83ef-d681ef34299f.jpg', '2026-02-02 16:29:52'),
(39, 10, 'person_evidence', '/uploads/spot_reports/2026-02-02-0005/person_698071ba25558.jpg', 'person#0:11ad2222-bc21-4c66-b61a-5a1553eb7fa6.jpg', '2026-02-02 17:43:22'),
(40, 10, 'vehicle_evidence', '/uploads/spot_reports/2026-02-02-0005/vehicle_698071ba269f0.jpg', 'vehicle#0:c039b782-bcc0-4eda-83ef-d681ef34299f.jpg', '2026-02-02 17:43:22'),
(41, 10, 'item_evidence', '/uploads/spot_reports/2026-02-02-0005/item_698071ba293f2.jpg', 'item#0:c5d44d78-9db2-48a0-ab74-b064c4d95f43.jpg', '2026-02-02 17:43:22'),
(42, 10, 'evidence', '/uploads/spot_reports/2026-02-02-0005/evi_698071ba2a512.jpg', '11ad2222-bc21-4c66-b61a-5a1553eb7fa6.jpg', '2026-02-02 17:43:22'),
(43, 10, 'pdf', '/uploads/spot_reports/2026-02-02-0005/doc_698071ba2a90c.pdf', 'camid.pdf', '2026-02-02 17:43:22'),
(44, 11, 'person_evidence', '/uploads/spot_reports/2026-02-21-0001/person_6999022e74e68.jpg', 'person#0:photo_2026-02-09_21-09-36.jpg', '2026-02-21 08:54:06'),
(45, 11, 'vehicle_evidence', '/uploads/spot_reports/2026-02-21-0001/vehicle_6999022e765c7.jpg', 'vehicle#0:e8440da5-489a-452e-9c65-380e0cbb7993.jpg', '2026-02-21 08:54:06'),
(46, 11, 'item_evidence', '/uploads/spot_reports/2026-02-21-0001/item_6999022e77028.jpg', 'item#0:001d6cda-cdbb-45cd-bdf9-178e4c3daa3c.jpg', '2026-02-21 08:54:06'),
(47, 11, 'evidence', '/uploads/spot_reports/2026-02-21-0001/evi_6999022e78097.jpg', '001d6cda-cdbb-45cd-bdf9-178e4c3daa3c.jpg', '2026-02-21 08:54:06'),
(48, 11, 'pdf', '/uploads/spot_reports/2026-02-21-0001/doc_6999022e7a567.pdf', 'M02 20, Doc 1(1).pdf', '2026-02-21 08:54:06'),
(49, 12, 'person_evidence', '/uploads/spot_reports/2026-03-02-0001/person_69a5210169838.png', 'person#0:36.png', '2026-03-02 13:32:49'),
(50, 12, 'vehicle_evidence', '/uploads/spot_reports/2026-03-02-0001/vehicle_69a521016b5ec.png', 'vehicle#0:37.png', '2026-03-02 13:32:49');

-- --------------------------------------------------------

--
-- Table structure for table `spot_report_items`
--

CREATE TABLE `spot_report_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `report_id` int(10) UNSIGNED NOT NULL,
  `item_no` varchar(50) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `quantity` varchar(100) DEFAULT NULL,
  `volume` varchar(100) DEFAULT NULL,
  `value` decimal(15,2) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `status` varchar(64) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `spot_report_items`
--

INSERT INTO `spot_report_items` (`id`, `report_id`, `item_no`, `type`, `description`, `quantity`, `volume`, `value`, `remarks`, `status`, `created_at`, `updated_at`) VALUES
(7, 7, '19', 'Forest Product', 'HAHHA', '19', '19', 8.00, 'HAHHAH', 'Seized', '2026-02-06 14:39:08', '2026-02-06 14:39:08'),
(8, 8, '1', 'Forest Product', 'HAHHA', 'HAHAH', 'HSHLJ,FJL', 99.00, 'HJSD', 'Donated to LGU', '2026-02-06 14:39:08', '2026-02-06 14:39:08'),
(9, 8, '2', 'Forest Product', 'HAHHA', 'HAHAH', 'HSHLJ,FJL', 101.00, 'HAHHAH', 'Burned/Destroyed', '2026-02-06 14:39:08', '2026-02-06 14:39:08'),
(10, 10, '', 'Forest Product', 'HAHHA', '19', '99', 1099.00, 'HAHAH', 'Confiscated', '2026-02-06 14:39:08', '2026-02-06 14:39:32'),
(11, 11, '1', 'Equipment', 'HAHAHAH', '99', '99', 9.90, 'HAHAHHA', 'Disposed', '2026-02-21 08:54:06', '2026-02-21 08:54:06');

-- --------------------------------------------------------

--
-- Table structure for table `spot_report_persons`
--

CREATE TABLE `spot_report_persons` (
  `id` int(10) UNSIGNED NOT NULL,
  `report_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `age` varchar(50) DEFAULT NULL,
  `gender` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact` varchar(100) DEFAULT NULL,
  `role` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `spot_report_persons`
--

INSERT INTO `spot_report_persons` (`id`, `report_id`, `name`, `age`, `gender`, `address`, `contact`, `role`, `status`, `created_at`, `updated_at`) VALUES
(7, 7, 'Ashy', '19', 'Female', 'Brgy Riizal', '0973287458', 'Driver', NULL, '2026-02-06 14:39:08', '2026-02-06 14:39:08'),
(8, 10, 'Ashy Camid', '19', 'Male', 'Brgy 3 BTA ADN', '09090481881', 'Driver', NULL, '2026-02-06 14:39:08', '2026-02-06 14:39:08'),
(9, 11, 'HAHAHAH', '27', 'Female', 'HAHAHAHA', '0974389574', 'Helper', 'On Bail', '2026-02-21 08:54:06', '2026-02-21 17:00:53'),
(10, 12, 'ASHY', '19', 'Male', 'HAHAHHAHAHAH', '09876567898', 'Lookout', 'Convicted', '2026-03-02 13:32:49', '2026-03-02 13:32:49');

-- --------------------------------------------------------

--
-- Table structure for table `spot_report_vehicles`
--

CREATE TABLE `spot_report_vehicles` (
  `id` int(10) UNSIGNED NOT NULL,
  `report_id` int(10) UNSIGNED NOT NULL,
  `plate` varchar(100) DEFAULT NULL,
  `make` varchar(255) DEFAULT NULL,
  `color` varchar(100) DEFAULT NULL,
  `owner` varchar(255) DEFAULT NULL,
  `contact` varchar(100) DEFAULT NULL,
  `engine` varchar(255) DEFAULT NULL,
  `status` varchar(64) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `spot_report_vehicles`
--

INSERT INTO `spot_report_vehicles` (`id`, `report_id`, `plate`, `make`, `color`, `owner`, `contact`, `engine`, `status`, `remarks`, `created_at`, `updated_at`) VALUES
(7, 7, '19', 'Hyundai', '100', 'ahhaha', '098438953', 'HAHAHAH', 'Donated to LGU', 'HAHAH', '2026-02-06 14:39:07', '2026-02-21 17:05:23'),
(8, 9, '098290', 'Bugatti', 'hahah', 'ahhaha', 'hahah', 'HAHAHAH', 'Donated', 'HAHAH', '2026-02-06 14:39:07', '2026-02-21 17:05:39'),
(9, 10, '0111', 'Ford', 'White', 'Yashyy Sultan', '09873647891', 'Sample Engine', 'Released to Owner', 'Nadakpan kay gwap', '2026-02-06 14:39:07', '2026-02-06 14:40:05'),
(10, 11, '897589475', 'Toyota', 'WHITE', 'HAHAHH', '09675849212', 'HAHAHA', 'Donated to LGU', 'HAHHAHAH', '2026-02-21 08:54:06', '2026-02-21 17:05:52'),
(11, 12, '93892835', 'SAKAY US', 'WHITE', 'HAHAHH', '09675849212', 'HAHAHA', 'Publicly Auctioned', 'HAHHAHAH', '2026-03-02 13:32:49', '2026-03-02 13:32:49');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `office_unit` varchar(255) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `role` enum('Admin','Enforcement Officer','Enforcer','Property Custodian','Office Staff') NOT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `full_name`, `contact_number`, `office_unit`, `profile_picture`, `role`, `status`, `created_at`, `updated_at`, `last_login`, `position`) VALUES
(7, 'joel@gmail.com', '$2y$10$AxqyNW5QupVYrv2dpyzfd.0d.zGSM0bpNYNonUXZD3LTlAsnrr8PC', 'Joel Caluya', '091234567891', 'Monitoring and Evaluation Unit', 'public/uploads/user_7_1766452988.png', 'Enforcement Officer', 1, '2025-12-23 01:17:06', '2026-03-03 10:28:48', '2026-03-03 10:28:48', ''),
(8, 'rich@gmail.com', '$2y$10$ukQb.d8Gieig/qLNbG46eOWoYRcJRwfkefOUyc0V4UPn.RlXBBp8G', 'Rich Balaido', '09123276243', 'Licensing and Permitting Unit', 'public/uploads/user_8_1766453004.png', 'Enforcer', 1, '2025-12-23 01:18:08', '2026-03-02 05:38:25', '2026-03-02 05:38:25', ''),
(9, 'joryn@gmail.com', '$2y$10$hanNFULBKj.sXdWn28DBbuFI1mhQLz0UYV2c9eeoV/Ey9S1vLS/tK', 'Joryn Cagulangan', '09783624786', 'Support Unit', 'public/uploads/user_9_1770599948.png', 'Property Custodian', 1, '2025-12-23 01:20:14', '2026-03-03 11:36:10', '2026-03-03 11:36:10', NULL),
(10, 'jayivan@gmail.com', '$2y$10$MgKZZNkqqP4i6ZrVcAB0k.wMEomR68MBueALwiI146ppK5c2TmJEW', 'Jay Ivan Tadena', '09123276243', 'Support Unit', 'public/uploads/user_10_1766453186.png', 'Office Staff', 1, '2025-12-23 01:26:17', '2026-03-02 02:54:50', '2026-03-02 02:54:50', NULL),
(11, 'admin@gmail.com', '$2y$10$6A2ksMr/cShcTlRs/S8Fz.kXooTJ8.MITtsaMftjfDhrbeA5j9BNa', 'Ashmen Camid', NULL, NULL, 'public/uploads/user_11_1768541643.jpg', 'Admin', 1, '2025-12-23 09:02:02', '2026-03-03 11:43:01', '2026-03-03 11:43:01', NULL),
(12, 'gabriel@gmail.com', '$2y$10$XuTqTXLeO1Z5X9gSuKjjPOdwQML90RxXETUM/8tqecBC/LUHMi9xO', 'Gabriel Billiones Pahaganas', '09090481837', 'Monitoring and Evaluation Unit', 'public/uploads/user_12_1766805722.jpg', 'Enforcement Officer', 1, '2025-12-27 03:21:26', '2025-12-27 03:22:02', NULL, NULL),
(13, 'roslain@gmail.com', '$2y$10$bdweRQHvB8K3LAz11SlipuMkLKb18X80wQCyU7435nKE6WpZSY4Cm', 'Roslain Sultan Camid', '09837633547', 'NGP', 'public/uploads/user_13_1766805841.png', 'Enforcer', 1, '2025-12-27 03:23:19', '2026-03-02 05:34:55', '2026-03-02 05:34:55', ''),
(14, 'wahida@gmail.com', '$2y$10$RtFeXEgsHmZJdgKsUI0JZeqUhspvmFhLIhv8Qm82MK9/VGTxwELRS', 'Wahida Sultan Camid', '09764536217', 'Support Unit', 'public/uploads/user_14_1766805944.jpg', 'Property Custodian', 1, '2025-12-27 03:24:57', '2025-12-27 03:25:44', NULL, NULL),
(15, 'novaisah@gmail.com', '$2y$10$1l/P/dkC1AjlXk6Va291qeQoxMhbwVZQGmH9g1Z9vtkjBV5OO3bma', 'Novaisah Sultan Camid', '09873627354', 'NGP', 'public/uploads/user_15_1766806276.png', 'Office Staff', 1, '2025-12-27 03:31:03', '2026-02-21 00:43:17', '2026-02-21 00:43:17', ''),
(16, 'raga@gmail.com', '$2y$10$XU0pTd0DVSCQ1eQyVnIlS.RoJI2f88zVZqAbG1hZObwUXFkqvL0Ya', 'Raga Batao Sultan', '09654738296', 'Antongalon ENR Monitoring Information and Assistance Center', 'public/uploads/1766806820_3c0bee15942c.jpg', 'Enforcement Officer', 1, '2025-12-27 03:40:20', '2026-02-18 04:40:15', NULL, ''),
(17, 'gabriella@gmail.com', '$2y$10$HC3WlK5H7UuzD3XLDwDnCOEbZ5Li3bumQnTAj7dcqtQo2LNhNfFfa', 'Gabriella Billiones Pahaganas', '09848374389', 'Monitoring and Evaluation Unit', 'public/uploads/user_17_1771210849.jpg', 'Enforcement Officer', 1, '2026-02-16 02:56:46', '2026-02-21 08:22:31', '2026-02-21 08:22:31', 'Head office staff'),
(18, 'admin1@gmail.com', '$2y$10$XU0pTd0DVSCQ1eQyVnIlS.RoJI2f88zVZqAbG1hZObwUXFkqvL0Ya', 'PJ Mordeno', '09090481888', 'Support Unit', NULL, 'Admin', 1, '2026-02-17 01:35:35', '2026-02-17 01:35:35', NULL, 'ICT FOCAL'),
(19, 'biabenini@gmail.com', '$2y$10$jMml1SXbaYkrNKK.RRYAruvHmAkcB/6KuAO4X057Wp3YbfIUoP0SC', 'Bia Azenith Taglucop', '09765748931', 'Planning Unit', 'public/uploads/1771911740_2e7c7ef98e61.jpg', 'Property Custodian', 1, '2026-02-24 05:42:20', '2026-02-24 05:44:56', '2026-02-24 05:42:54', 'Dogstyle');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `property_number` (`property_number`),
  ADD KEY `accountable_person_id` (`accountable_person_id`),
  ADD KEY `actual_user_id` (`actual_user_id`),
  ADD KEY `fk_equipment_created_by` (`created_by`),
  ADD KEY `fk_equipment_updated_by` (`updated_by`);

--
-- Indexes for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ticket_no` (`ticket_no`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `service_request_actions`
--
ALTER TABLE `service_request_actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_service_request_id` (`service_request_id`);

--
-- Indexes for table `spot_reports`
--
ALTER TABLE `spot_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference_no` (`reference_no`),
  ADD KEY `fk_spot_reports_submitted_by` (`submitted_by`);

--
-- Indexes for table `spot_report_files`
--
ALTER TABLE `spot_report_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_id` (`report_id`);

--
-- Indexes for table `spot_report_items`
--
ALTER TABLE `spot_report_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_id` (`report_id`);

--
-- Indexes for table `spot_report_persons`
--
ALTER TABLE `spot_report_persons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_id` (`report_id`);

--
-- Indexes for table `spot_report_vehicles`
--
ALTER TABLE `spot_report_vehicles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_id` (`report_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `service_requests`
--
ALTER TABLE `service_requests`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `service_request_actions`
--
ALTER TABLE `service_request_actions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `spot_reports`
--
ALTER TABLE `spot_reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `spot_report_files`
--
ALTER TABLE `spot_report_files`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `spot_report_items`
--
ALTER TABLE `spot_report_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `spot_report_persons`
--
ALTER TABLE `spot_report_persons`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `spot_report_vehicles`
--
ALTER TABLE `spot_report_vehicles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `equipment`
--
ALTER TABLE `equipment`
  ADD CONSTRAINT `equipment_ibfk_1` FOREIGN KEY (`accountable_person_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `equipment_ibfk_2` FOREIGN KEY (`actual_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_equipment_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_equipment_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `service_request_actions`
--
ALTER TABLE `service_request_actions`
  ADD CONSTRAINT `fk_sra_request` FOREIGN KEY (`service_request_id`) REFERENCES `service_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `spot_reports`
--
ALTER TABLE `spot_reports`
  ADD CONSTRAINT `fk_spot_reports_submitted_by` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `spot_report_files`
--
ALTER TABLE `spot_report_files`
  ADD CONSTRAINT `fk_srf_report` FOREIGN KEY (`report_id`) REFERENCES `spot_reports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `spot_report_items`
--
ALTER TABLE `spot_report_items`
  ADD CONSTRAINT `fk_sri_report` FOREIGN KEY (`report_id`) REFERENCES `spot_reports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `spot_report_persons`
--
ALTER TABLE `spot_report_persons`
  ADD CONSTRAINT `fk_srp_report` FOREIGN KEY (`report_id`) REFERENCES `spot_reports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `spot_report_vehicles`
--
ALTER TABLE `spot_report_vehicles`
  ADD CONSTRAINT `fk_srv_report` FOREIGN KEY (`report_id`) REFERENCES `spot_reports` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

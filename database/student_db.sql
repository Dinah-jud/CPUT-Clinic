-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 19, 2025 at 06:06 PM
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
-- Database: `student_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointment`
--

CREATE TABLE `appointment` (
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('Scheduled','Completed','Cancelled') DEFAULT 'Scheduled',
  `appointment_type` enum('General','Follow-up','Emergency') NOT NULL,
  `schedule_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointment`
--

INSERT INTO `appointment` (`appointment_id`, `patient_id`, `doctor_id`, `appointment_date`, `appointment_time`, `status`, `appointment_type`, `schedule_id`) VALUES
(2, 102, 1, '2025-08-31', '11:00:00', 'Scheduled', 'Follow-up', 1),
(3, 103, 2, '2025-09-01', '10:30:00', 'Scheduled', 'Emergency', 3),
(5, 104, 3, '2025-09-03', '11:00:00', 'Scheduled', 'General', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `availabilityschedule`
--

CREATE TABLE `availabilityschedule` (
  `schedule_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `available_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `availabilityschedule`
--

INSERT INTO `availabilityschedule` (`schedule_id`, `doctor_id`, `available_date`, `start_time`, `end_time`) VALUES
(1, 1, '2025-08-31', '09:00:00', '12:00:00'),
(2, 1, '2025-08-31', '13:00:00', '16:00:00'),
(3, 2, '2025-09-01', '10:00:00', '14:00:00'),
(4, 3, '2025-09-03', '09:00:00', '13:00:00'),
(5, 4, '2025-09-04', '09:00:00', '16:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `idNumber` varchar(50) NOT NULL,
  `gender` varchar(20) NOT NULL,
  `address` varchar(255) NOT NULL,
  `user_type` varchar(20) NOT NULL DEFAULT 'patient'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `full_name`, `email`, `password`, `phone`, `idNumber`, `gender`, `address`, `user_type`) VALUES
(1, 'Yvvonne Mthiyane', '222530723@mycput.ac.za', '$2y$10$iOy2aGnvO11dm91onvgUyezxudKw3YuuhFSqSW83/nEUsKlOAzBC2', '0640771802', '0', '0', '0', 'patient'),
(2, 'thina mthi', 'thi@gmai.com', '$2y$10$5lGF88gqWPFNu3gKg5cpHO0UqSgtZSRTFMNlSDYSbZ5tC/21yZBDu', '0630782341', '0698721108085', 'Male', '23 plein', 'patient'),
(3, 'Yoni spel', 'yoni@gmai.com', '$2y$10$WMlCrIVD3FGwsbp58MHu4uPdIUt5JPOgJvqJRanjryY81UlJMg4ru', '0789364108', '02051225508085', 'Male', 'Rise foshore ', 'patient'),
(4, 'Pelisa', 'pali@gmail.com', '$2y$10$pAR8GmlYzqqems.PhBT1Y.JLaJiTPh9rPTVtUtKtmWPqVESRdxHCe', '09838865326', '9848238955', 'Female', '50 sirl lowry', 'patient'),
(5, 'Sine Mhlawuli', '222578910@mycput.ac.za', '$2y$10$Z59tQlc7q5Ry6qFsifWJju4O8dEFBmtkaS84.cGV0iJioe3MNBfdW', '0789865105', '0809451109095', 'Male', '12 Plein street ', 'patient'),
(6, 'Onisto Spel', '222590713@mycput.ac.za', '$2y$10$wHSX0L5i5mS00hdwyA6e6uljuxoLmyAa5iGlHM4daa5GGfgvWjR7O', '0750991602', '0956091108085', 'Male', '12 Loyd', 'patient'),
(7, 'Esihle', '225780943@mycput.ac.za', '$2y$10$OZrLZy1m4PfR1a4.9wgPge3NVHMwOWuS4qEB8tVSXCDQeAFskidta', '0973009764', '0987340098085', 'Female', 'Cape Town', 'patient'),
(8, 'Ahluma', '222512571@mycput.ac.za', '$2y$10$FFD7ZjNrGJe3diu5gmv8.uDAwuy8RXBppSb8nSO9nVWu8hvU1P5Ca', '0737155639', '0401030620082', 'Female', '40 Sir Lowry Zonnebloem Cape Town', 'patient'),
(9, 'Siya', '22118790@mycput.ac.za', '$2y$10$aElfSdSwokHljZYZkFhbaeJOgnByo3i3SToyLo0VDMQvVgLKRFfLe', '0650441457', '0508091108085', 'Female', '18434 Better life', 'patient'),
(10, 'Sisipho Ncume', '233650823@mycput.ac.za', '$2y$10$cG.Bo.RgoOvvFP43AvqMluE0OENDujqIeKOBfzflXkhSFQ03dIoCq', '0789843104', '0709081108089', 'Female', '39 Lower', 'patient'),
(11, 'Angel jade', '442850723@mycput.ac.za', '$2y$10$kQ/XN0YN94Jh9C6HQo8TL..wYxs6k9kzYCpdn5QvyfzaC4FZOfVum', '0879843109', '0102031109094', 'Male', 'Site C 23', 'patient'),
(12, 'Achumile Nade', '333230912@mycput.ac.za', '$2y$10$2mHt7npbJzA87WkxQ9j.pOI8hIyrKBx5gEtOCHV2bdDdOOi7K8v/a', '0640881502', '9409121109095', 'Male', '45 site B', 'patient'),
(13, 'Aphele Dame', '267863498@mycput.ac.za', '$2y$10$7C.vNRRjIpbUQtwccaqHjO.uznMql.ERdOcNIb8qzLPp0Z1aXFbsO', '0659823109', '0102049907652', 'Female', '56 Loyd', 'patient'),
(16, 'Sino', '221169723@mycput.ac.za', '$2y$10$DQ0RkYOnf9X/eos7ewoBKe4rr3u25o5fLDYAQ7B49HbvG4CEnPZgi', '0840721802', '0402071108035', 'Male', '78 Adderley Str ', 'patient'),
(17, 'Dr Dlamini', '233953074@mycput.ac.za', '$2y$10$9Yn27pKUaPoYMej19LUNy.NecLATI3O9TnHrSQ5NVdujQ.84KzXfy', '0849849802', '0603943208035', 'Male', '34 Site Str', 'doctor');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointment`
--
ALTER TABLE `appointment`
  ADD PRIMARY KEY (`appointment_id`),
  ADD UNIQUE KEY `uc_no_double_booking` (`doctor_id`,`appointment_date`,`appointment_time`),
  ADD KEY `fk_schedule` (`schedule_id`);

--
-- Indexes for table `availabilityschedule`
--
ALTER TABLE `availabilityschedule`
  ADD PRIMARY KEY (`schedule_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointment`
--
ALTER TABLE `appointment`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `availabilityschedule`
--
ALTER TABLE `availabilityschedule`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointment`
--
ALTER TABLE `appointment`
  ADD CONSTRAINT `fk_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `availabilityschedule` (`schedule_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

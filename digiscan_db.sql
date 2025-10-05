-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 05, 2025 at 04:57 PM
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
-- Database: `digiscan_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `candidate_name` varchar(100) NOT NULL,
  `position` varchar(100) NOT NULL,
  `submitted_at` datetime NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `candidate_name`, `position`, `submitted_at`, `status`) VALUES
(1, 'David Brown', 'Senior Developer', '2023-06-10 14:30:00', 'Rejected'),
(2, 'Sarah Miller', 'Marketing Manager', '2023-06-08 10:15:00', 'Pending'),
(3, 'Robert Wilson', 'HR Assistant', '2023-06-05 16:45:00', 'Approved'),
(4, 'Jennifer Davis', 'Sales Representative', '2023-05-28 11:20:00', 'Rejected');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` enum('Present','Absent','Late','On Leave') DEFAULT 'Present',
  `employee_id` int(11) NOT NULL,
  `hours_worked` decimal(4,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`attendance_id`, `date`, `status`, `employee_id`, `hours_worked`) VALUES
(1, '2025-06-03', 'Present', 5, 0.00),
(10, '2023-06-01', 'Present', 5, 8.00),
(11, '2023-06-01', 'Present', 6, 7.50),
(12, '2023-06-02', 'Late', 5, 6.00),
(13, '2023-06-02', 'Present', 6, 8.00),
(14, '2023-06-03', 'Present', 5, 8.00),
(15, '2023-06-03', 'On Leave', 6, 0.00),
(16, '2023-06-04', 'Present', 5, 8.00),
(17, '2023-06-04', 'Absent', 6, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `audit_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `recorded_at` datetime DEFAULT current_timestamp(),
  `file_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`audit_id`, `description`, `recorded_at`, `file_path`) VALUES
(1, 'Quarterly audit completed for Q1 2025.', '2025-06-08 08:02:52', '/audits/q2_2023_financial.pdf'),
(2, 'Manual adjustment found in payroll system.', '2025-06-08 08:02:52', '/audits/annual_compliance_2023.pdf'),
(3, 'Expense irregularity resolved with supplier.', '2025-06-08 08:02:52', '/audits/expense_policy_2023.pdf'),
(4, 'All bank transactions matched for April 2025.', '2025-06-08 08:02:52', ''),
(5, 'Unauthorized login attempt recorded.', '2025-06-08 08:02:52', ''),
(6, 'Employee case', '2025-06-08 23:27:39', '/audits/employee.pdf'),
(7, 'Employee case', '2025-06-08 23:28:16', '/audits/employee.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `backup_log`
--

CREATE TABLE `backup_log` (
  `backup_id` int(11) NOT NULL,
  `backup_file` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `backup_log`
--

INSERT INTO `backup_log` (`backup_id`, `backup_file`, `created_at`) VALUES
(1, 'Clients are sending many orders nowadays. So work hard', '2025-06-04 16:38:59'),
(2, 'backup_20250604_130939.sql', '2025-06-04 16:39:39'),
(3, 'restore_Clients are sending many orders nowadays. So work hard', '2025-06-04 16:40:06'),
(4, 'restore_backup_20250604_130939.sql', '2025-06-04 16:40:27'),
(5, 'restore_backup_20250604_130939.sql', '2025-06-04 16:44:00');

-- --------------------------------------------------------

--
-- Table structure for table `budgets`
--

CREATE TABLE `budgets` (
  `budget_id` int(11) NOT NULL,
  `department` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `period` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `budgets`
--

INSERT INTO `budgets` (`budget_id`, `department`, `amount`, `period`, `created_at`) VALUES
(1, 'Marketing', 50000.00, 'Q3 2023', '2023-06-01 09:00:00'),
(2, 'R&D', 75000.00, 'Q3 2023', '2023-06-05 14:30:00'),
(3, 'Operations', 100000.00, 'Q3 2023', '2023-06-10 11:15:00'),
(4, 'IT', 85000.00, 'Q1 2025', '2025-06-08 23:09:49');

-- --------------------------------------------------------

--
-- Table structure for table `catalogue`
--

CREATE TABLE `catalogue` (
  `item_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `catalogue`
--

INSERT INTO `catalogue` (`item_id`, `name`, `description`, `price`, `category`) VALUES
(1, 'Business Cards', 'Premium 350gsm silk laminated business cards', 49.99, 'Stationery'),
(2, 'Brochures', 'A4 trifold brochures, full color both sides', 199.99, 'Marketing'),
(3, 'Posters', 'A2 size posters, high-gloss finish', 29.99, 'Signage'),
(4, 'Banners', 'Vinyl retractable banners 850x2000mm', 149.99, 'Signage');

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `client_id` int(11) NOT NULL,
  `client_username` varchar(50) NOT NULL,
  `client_password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`client_id`, `client_username`, `client_password`) VALUES
(1, 'Stepon Shoes', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(2, 'acme_corp', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(3, 'globex', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(4, 'wayne_ent', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- --------------------------------------------------------

--
-- Table structure for table `client_company`
--

CREATE TABLE `client_company` (
  `cl_id` int(11) NOT NULL,
  `comp_name` varchar(100) NOT NULL,
  `address` varchar(100) NOT NULL,
  `contact` varchar(15) NOT NULL,
  `industry` varchar(100) NOT NULL,
  `comp_email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client_company`
--

INSERT INTO `client_company` (`cl_id`, `comp_name`, `address`, `contact`, `industry`, `comp_email`) VALUES
(1, 'MAS', 'Biyagama', '079565223', 'Clothing', 'mas@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `client_order`
--

CREATE TABLE `client_order` (
  `order_id` int(11) NOT NULL,
  `order_name` varchar(255) DEFAULT NULL,
  `client_id` int(11) NOT NULL,
  `order_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','completed','cancelled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client_order`
--

INSERT INTO `client_order` (`order_id`, `order_name`, `client_id`, `order_date`, `due_date`, `amount`, `status`) VALUES
(1, 'Banners', 1, '2025-06-05', '2025-06-18', 15000.00, 'pending'),
(2, 'Posters', 1, '2025-06-05', '2025-06-18', 25000.00, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `company_goals`
--

CREATE TABLE `company_goals` (
  `goal_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `target_date` date NOT NULL,
  `status` enum('Active','Completed','Cancelled') DEFAULT 'Active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_goals`
--

INSERT INTO `company_goals` (`goal_id`, `title`, `description`, `target_date`, `status`, `created_at`) VALUES
(1, 'Reach 1 million profits ', 'for 6 months we have a target now', '2025-06-26', 'Active', '2025-06-05 07:50:00');

-- --------------------------------------------------------

--
-- Table structure for table `company_policies`
--

CREATE TABLE `company_policies` (
  `policy_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_policies`
--

INSERT INTO `company_policies` (`policy_id`, `title`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Clear Expectations', 'Company policies define how employees should behave and what is expected of them, reducing ambiguity and promoting consistency.', '2025-06-05 07:07:07', '2025-06-08 12:01:34'),
(2, 'Compliance', 'They ensure the company adheres to legal and regulatory requirements, minimizing potential legal risks.', '2025-06-05 07:07:26', '2025-06-08 12:01:34'),
(3, 'Clear Expectations', 'Company policies define how employees should behave and what is expected of them, reducing ambiguity and promoting consistency.', '2025-06-05 07:09:12', '2025-06-08 12:01:34'),
(4, 'Clear Expectations', 'dsds', '2025-06-08 12:03:35', '2025-06-08 12:03:35');

-- --------------------------------------------------------

--
-- Table structure for table `customer_feedback`
--

CREATE TABLE `customer_feedback` (
  `feedback_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `feedback` text NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` between 1 and 5),
  `submitted_at` datetime DEFAULT current_timestamp(),
  `design_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_feedback`
--

INSERT INTO `customer_feedback` (`feedback_id`, `customer_name`, `feedback`, `rating`, `submitted_at`, `design_id`) VALUES
(1, 'Nimal Perera', 'Great service and fast delivery!', 5, '2025-06-07 18:26:05', 1),
(2, 'Samantha De Silva', 'Product quality was good but packaging was poor.', 3, '2025-06-07 18:26:05', 2),
(3, 'Ruwan Fernando', 'Very helpful customer support.', 4, '2025-06-07 18:26:05', 3);

-- --------------------------------------------------------

--
-- Table structure for table `customer_invoices`
--

CREATE TABLE `customer_invoices` (
  `invoice_id` int(11) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `issue_date` date NOT NULL,
  `status` enum('Pending','Paid') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_invoices`
--

INSERT INTO `customer_invoices` (`invoice_id`, `client_name`, `amount`, `issue_date`, `status`) VALUES
(1, 'John Perera', 1200.00, '2025-06-01', 'Pending'),
(2, 'Ayesha Fernando', 450.75, '2025-05-29', 'Pending'),
(3, 'Malik Silva', 980.50, '2025-05-25', 'Paid'),
(4, 'Tharindu Jayasena', 300.00, '2025-06-03', 'Paid'),
(5, 'Tharindu', 300.00, '2025-06-08', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `custom_products`
--

CREATE TABLE `custom_products` (
  `custom_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `specifications` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `custom_products`
--

INSERT INTO `custom_products` (`custom_id`, `client_id`, `item_id`, `specifications`, `created_at`) VALUES
(1, 1, 1, 'Double-sided, spot UV on logo, rounded corners', '2025-06-10 14:29:12'),
(2, 2, 2, 'Company brochure with custom die-cut shape', '2025-06-10 14:29:12'),
(3, 1, 3, 'Event posters with metallic gold ink accents', '2025-06-10 14:29:12'),
(4, 3, 1, 'Double-sided, spot UV on logo, rounded corners', '2025-06-10 14:30:03'),
(5, 3, 2, 'Company brochure with custom die-cut shape', '2025-06-10 14:30:03'),
(6, 3, 3, 'Event posters with metallic gold ink accents', '2025-06-10 14:30:03'),
(7, 3, 3, 'Social media posts png format', '2025-06-10 14:30:42'),
(8, 3, 3, 'Social media posts png format', '2025-06-10 14:30:46');

-- --------------------------------------------------------

--
-- Table structure for table `data_privacy_audits`
--

CREATE TABLE `data_privacy_audits` (
  `audit_id` int(11) NOT NULL,
  `audit_type` varchar(100) NOT NULL,
  `status` enum('Compliant','Non-compliant') NOT NULL,
  `logged_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `data_privacy_audits`
--

INSERT INTO `data_privacy_audits` (`audit_id`, `audit_type`, `status`, `logged_at`) VALUES
(1, 'GDPR Compliance', 'Compliant', '2023-06-10 14:00:00'),
(2, 'HIPAA Compliance', 'Non-compliant', '2023-06-05 11:30:00'),
(3, 'PCI DSS Compliance', 'Compliant', '2023-05-28 16:45:00');

-- --------------------------------------------------------

--
-- Table structure for table `decisions`
--

CREATE TABLE `decisions` (
  `decision_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `submitted_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `decisions`
--

INSERT INTO `decisions` (`decision_id`, `title`, `description`, `status`, `submitted_at`, `updated_at`) VALUES
(1, 'Open New Branch in Kandy', 'Proposal to expand DigiScan operations by opening a new branch in Kandy. Expected ROI in 18 months.', 'Approved', '2025-06-05 07:44:21', '2025-06-05 07:44:33'),
(2, 'Upgrade Server Infrastructure', 'Decision to upgrade existing on-premise servers to cloud-based AWS infrastructure for scalability and redundancy.', 'Rejected', '2025-06-05 07:44:21', '2025-06-05 07:44:35'),
(3, 'Employee Health Insurance Plan', 'Initiate a new corporate health insurance policy covering employees and their immediate families.', 'Approved', '2025-06-05 07:44:21', '2025-06-05 07:44:38'),
(4, 'Change Official Working Hours', 'Adjust company working hours to 8:00 AM – 4:00 PM based on productivity feedback.', 'Approved', '2025-06-05 07:44:21', '2025-06-05 07:44:39'),
(5, 'Launch New Product Line', 'Approval required for launching a new biometric scanning product targeted at the education sector.', 'Pending', '2025-06-05 07:44:21', NULL),
(6, 'Discontinue Legacy Software', 'Request to phase out support for legacy HRMS software by end of Q4.', 'Rejected', '2025-06-05 07:44:21', '2025-06-05 07:44:40'),
(7, 'Hire New Data Analyst Team', 'Approval to hire 3 data analysts for the Business Intelligence division.', 'Pending', '2025-06-05 07:44:21', NULL),
(8, 'Increase Marketing Budget Q3', 'Proposal to increase the Q3 marketing budget by 25% to boost outreach and lead generation.', 'Pending', '2025-06-05 07:44:21', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `departmental_budgets`
--

CREATE TABLE `departmental_budgets` (
  `budget_id` int(11) NOT NULL,
  `department` varchar(100) NOT NULL,
  `allocated_amount` decimal(12,2) NOT NULL,
  `spent_amount` decimal(12,2) NOT NULL,
  `fiscal_year` year(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departmental_budgets`
--

INSERT INTO `departmental_budgets` (`budget_id`, `department`, `allocated_amount`, `spent_amount`, `fiscal_year`) VALUES
(1, 'Human Resources', 50000.00, 42000.00, '2024'),
(2, 'Information Technology', 120000.00, 90000.00, '2024'),
(3, 'Finance', 80000.00, 60000.00, '2023'),
(4, 'Marketing', 70000.00, 68000.00, '2023'),
(5, 'Operations', 100000.00, 95000.00, '2025');

-- --------------------------------------------------------

--
-- Table structure for table `designed_files`
--

CREATE TABLE `designed_files` (
  `file_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `custom_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `designed_files`
--

INSERT INTO `designed_files` (`file_id`, `client_id`, `custom_id`, `file_path`, `created_at`) VALUES
(1, 3, 4, 'uploads/1749546148_aaron-burden-3z8kVEYCYxY-unsplash.jpg', '2025-06-10 14:32:28');

-- --------------------------------------------------------

--
-- Table structure for table `design_reports`
--

CREATE TABLE `design_reports` (
  `report_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `generated_at` datetime NOT NULL,
  `file_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `design_reports`
--

INSERT INTO `design_reports` (`report_id`, `title`, `generated_at`, `file_path`) VALUES
(1, 'Q2 Design Portfolio Review', '2023-06-01 09:00:00', '/reports/q2_design_review.pdf'),
(2, 'Customer Feedback Analysis May 2023', '2023-06-05 14:30:00', '/reports/may_feedback_analysis.pdf'),
(3, 'Design Approval Metrics', '2023-06-10 11:15:00', '/reports/approval_metrics_june.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `discounts`
--

CREATE TABLE `discounts` (
  `discount_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `discount_percentage` decimal(5,2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `discounts`
--

INSERT INTO `discounts` (`discount_id`, `product_id`, `discount_percentage`, `start_date`, `end_date`) VALUES
(1, 1, 15.00, '2025-06-19', '2025-06-30'),
(2, 2, 10.00, '2025-07-10', '2025-07-25'),
(3, 3, 20.00, '2025-07-02', '2025-07-31'),
(4, 4, 55.00, '2025-06-10', '2025-06-13');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `EMP_ID` int(11) NOT NULL,
  `FName` varchar(50) NOT NULL,
  `LName` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `EmpNIC` varchar(20) NOT NULL,
  `years_of_experience` int(11) DEFAULT NULL,
  `designation` varchar(100) NOT NULL,
  `EmpContact_No` varchar(15) DEFAULT NULL,
  `DOB` date DEFAULT NULL,
  `Emp_Email` varchar(100) DEFAULT NULL,
  `Dependent_Name` varchar(100) DEFAULT NULL,
  `Dependent_NIC` varchar(20) DEFAULT NULL,
  `Relationship` varchar(50) DEFAULT NULL,
  `DeContact_No` varchar(15) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `salary` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`EMP_ID`, `FName`, `LName`, `username`, `password`, `EmpNIC`, `years_of_experience`, `designation`, `EmpContact_No`, `DOB`, `Emp_Email`, `Dependent_Name`, `Dependent_NIC`, `Relationship`, `DeContact_No`, `created_at`, `salary`) VALUES
(5, 'thenuka', 'ubayasena', 'thenuka', '$2y$10$UQe.nC2A8rYHWoAqSAxc2ui4aljmKYRtBqNagRPkbBf9tRKCS.wZ.', '200108203035', 2, 'CEO', '0719022937', '2001-03-22', 'ubayasenat@gmail.com', 'Priyani', '98510314', 'Parent', '0718032303', '2025-06-03 15:54:08', 150000.00),
(6, 'Nethmi', 'Vindula', 'nethmi', '$2y$10$Zg002NzbqgEAg.m9dfZ8D.keavJ6ZL3OnNfGnDNYmyx4lkbuw5Cdi', '19975153123', 3, 'CEO', '0715126512', '1997-09-10', 'nethmi@gmail.com', 'Alaka', '98510314', 'Parent', '0718032303', '2025-06-04 09:57:27', 200000.00);

-- --------------------------------------------------------

--
-- Table structure for table `employee_db`
--

CREATE TABLE `employee_db` (
  `record_id` int(11) NOT NULL,
  `EMP_ID` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `last_updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_db`
--

INSERT INTO `employee_db` (`record_id`, `EMP_ID`, `username`, `last_updated`) VALUES
(5, 5, 'thenuka11', '2025-06-08 15:06:20'),
(6, 6, 'nethmi', '2023-06-01 09:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `employee_feedback`
--

CREATE TABLE `employee_feedback` (
  `feed_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_feedback`
--

INSERT INTO `employee_feedback` (`feed_id`, `title`, `description`, `employee_id`) VALUES
(1, 'All pending tasks', 'Done by now', 5);

-- --------------------------------------------------------

--
-- Table structure for table `encryption_logs`
--

CREATE TABLE `encryption_logs` (
  `log_id` int(11) NOT NULL,
  `data_type` varchar(100) NOT NULL,
  `encryption_method` varchar(100) NOT NULL,
  `logged_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `encryption_logs`
--

INSERT INTO `encryption_logs` (`log_id`, `data_type`, `encryption_method`, `logged_at`) VALUES
(1, 'Employee Records', 'AES-256', '2023-06-15 10:00:00'),
(2, 'Financial Data', 'RSA-2048', '2023-06-14 15:30:00'),
(3, 'Customer Information', 'AES-256', '2023-06-10 09:15:00'),
(4, 'Employee Records', 'AES-256', '2023-06-15 10:00:00'),
(5, 'Financial Data', 'RSA-2048', '2023-06-14 15:30:00'),
(6, 'Customer Information', 'AES-256', '2023-06-10 09:15:00'),
(7, 'Client', 'AA-26f', '2025-06-08 15:05:52');

-- --------------------------------------------------------

--
-- Table structure for table `equipment_allocation`
--

CREATE TABLE `equipment_allocation` (
  `allocation_id` int(11) NOT NULL,
  `equipment_id` varchar(50) NOT NULL,
  `equipment_name` varchar(100) NOT NULL,
  `assigned_to` int(11) NOT NULL,
  `allocation_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment_allocation`
--

INSERT INTO `equipment_allocation` (`allocation_id`, `equipment_id`, `equipment_name`, `assigned_to`, `allocation_date`) VALUES
(5, 'MACH-001', 'CNC Router', 5, '2023-06-01'),
(6, 'MACH-002', 'Laser Cutter', 5, '2023-06-05'),
(7, 'MACH-003', '3D Printer', 6, '2023-06-10'),
(8, 'TOOL-101', 'Precision Calipers', 6, '2023-06-08');

-- --------------------------------------------------------

--
-- Table structure for table `equipment_logs`
--

CREATE TABLE `equipment_logs` (
  `log_id` int(11) NOT NULL,
  `equipment_name` varchar(100) NOT NULL,
  `status` enum('Operational','Maintenance','Out of Service') NOT NULL,
  `checked_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment_logs`
--

INSERT INTO `equipment_logs` (`log_id`, `equipment_name`, `status`, `checked_at`) VALUES
(1, 'Assembly Line 1', 'Operational', '2023-06-15 08:00:00'),
(2, 'CNC Machine 3', 'Maintenance', '2023-06-15 08:15:00'),
(3, 'Packaging Station 2', 'Operational', '2023-06-14 16:30:00'),
(4, 'Welding Robot 1', 'Out of Service', '2023-06-14 10:45:00'),
(5, 'Engine 3', 'Maintenance', '2025-06-08 15:30:06');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL,
  `comments` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`feedback_id`, `client_id`, `order_id`, `rating`, `comments`, `created_at`) VALUES
(1, 3, 1, 5, 'Excellent quality and fast delivery!', '2025-06-10 16:14:25'),
(2, 3, 2, 4, 'Good service but delivery was 2 days late', '2025-06-10 16:14:25');

-- --------------------------------------------------------

--
-- Table structure for table `financial_reports`
--

CREATE TABLE `financial_reports` (
  `report_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `generated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `financial_reports`
--

INSERT INTO `financial_reports` (`report_id`, `title`, `file_path`, `generated_at`) VALUES
(1, 'Daily Report - 2025-06-07', '/reports/daily_2025_06_07.pdf', '2025-06-08 08:22:45'),
(2, 'Weekly Summary - Week 23', '/reports/weekly_2025_week23.pdf', '2025-06-08 08:22:45'),
(3, 'Monthly Report - May 2025', '/reports/monthly_may_2025.pdf', '2025-06-08 08:22:45'),
(4, 'Yearly Summary - 2024', '/reports/yearly_2024.pdf', '2025-06-08 08:22:45');

-- --------------------------------------------------------

--
-- Table structure for table `financial_transactions`
--

CREATE TABLE `financial_transactions` (
  `transaction_id` int(11) NOT NULL,
  `type` enum('Income','Expense') NOT NULL,
  `category` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL CHECK (`amount` > 0),
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `financial_transactions`
--

INSERT INTO `financial_transactions` (`transaction_id`, `type`, `category`, `amount`, `description`, `created_at`) VALUES
(1, 'Income', 'Chairs', 150000.00, 'Buy chairs', '2025-06-07 13:46:15'),
(2, 'Income', 'Client', 15000.00, 'New clients', '2025-06-08 17:38:30'),
(3, 'Income', 'dffdf', 11111.00, 'gdfg', '2025-06-08 17:38:43');

-- --------------------------------------------------------

--
-- Table structure for table `hr_reports`
--

CREATE TABLE `hr_reports` (
  `report_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `generated_at` datetime NOT NULL,
  `file_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hr_reports`
--

INSERT INTO `hr_reports` (`report_id`, `title`, `generated_at`, `file_path`) VALUES
(1, 'Q1 2023 Employee Performance', '2023-04-15 10:30:00', '/reports/performance_q1_2023.pdf'),
(2, 'Annual HR Summary 2022', '2023-01-10 09:15:00', '/reports/annual_hr_2022.pdf'),
(3, 'June 2023 Attendance Report', '2023-07-01 14:45:00', '/reports/attendance_june_2023.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `inquiries`
--

CREATE TABLE `inquiries` (
  `inquiry_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Pending',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inquiries`
--

INSERT INTO `inquiries` (`inquiry_id`, `client_id`, `item_id`, `message`, `status`, `created_at`) VALUES
(1, 3, 4, 'Do you offer outdoor waterproof banners?', 'Answered', '2025-06-10 14:33:52'),
(2, 3, 1, 'Can we get samples of your business cards?', 'Pending', '2025-06-10 14:33:52');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `item_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `unit_price` decimal(10,2) NOT NULL,
  `location` varchar(50) NOT NULL,
  `last_updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`item_id`, `item_name`, `quantity`, `unit_price`, `location`, `last_updated`) VALUES
(1, 'Stainless Steel Bolts (5mm)', 4, 125.00, 'Kegalle', '2025-06-08 20:49:47'),
(2, 'Aluminum Sheets (1m x 2m)', 18, 45.99, 'Aisle 1, Bin 2', '2023-06-14 14:15:00'),
(3, 'Rubber Gaskets (Small)', 500, 0.75, 'Aisle 2, Bin 5', '2023-06-10 11:00:00'),
(4, 'Copper Wiring (10m spool)', 32, 12.50, 'Aisle 4, Bin 1', '2023-06-12 16:45:00'),
(5, 'Plastic Housing (Black)', 8, 8.99, 'Aisle 3, Bin 3', '2023-06-13 10:20:00'),
(6, 'Chairs', 7, 150.00, 'Kegalle', '2025-06-08 20:49:08');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_reports`
--

CREATE TABLE `inventory_reports` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `generated_at` datetime NOT NULL,
  `file_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_reports`
--

INSERT INTO `inventory_reports` (`id`, `title`, `generated_at`, `file_path`) VALUES
(1, 'Q2 2023 Inventory Summary', '2023-06-01 09:00:00', '/reports/q2_inventory_summary.pdf'),
(2, 'Low Stock Alert Report', '2023-06-05 14:30:00', '/reports/low_stock_june.pdf'),
(3, 'Supplier Performance Analysis', '2023-06-10 11:15:00', '/reports/supplier_performance.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_updates`
--

CREATE TABLE `inventory_updates` (
  `update_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `invoice_id` int(11) NOT NULL,
  `client_name` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `created_at` datetime NOT NULL,
  `order_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`invoice_id`, `client_name`, `amount`, `due_date`, `status`, `created_at`, `order_id`) VALUES
(6, 'Thenuka', 224.95, '2025-06-10', '', '2025-06-10 15:57:23', 1),
(7, 'Thenuka', 1699.91, '2025-06-10', '', '2025-06-10 15:57:23', 2),
(8, 'Thenuka', 1349.55, '2025-06-10', 'Pending', '2025-06-10 15:57:23', 3);

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `lr_id` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `req_date` date NOT NULL,
  `st_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('Pending','Accepted','Rejected') DEFAULT 'Pending',
  `employee_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_requests`
--

INSERT INTO `leave_requests` (`lr_id`, `reason`, `req_date`, `st_date`, `end_date`, `status`, `employee_id`) VALUES
(1, 'wedding', '2025-06-05', '2025-06-10', '2025-06-11', '', 5),
(2, 'Funeral', '2025-06-05', '2025-06-11', '2025-06-12', 'Rejected', 5),
(3, 'wedding', '2025-06-05', '2025-06-10', '2025-06-11', 'Pending', 5);

-- --------------------------------------------------------

--
-- Table structure for table `machine_performance`
--

CREATE TABLE `machine_performance` (
  `performance_id` int(11) NOT NULL,
  `machine_id` varchar(50) NOT NULL,
  `machine_name` varchar(100) NOT NULL,
  `uptime` decimal(5,2) NOT NULL,
  `downtime` decimal(5,2) NOT NULL,
  `last_maintenance` date DEFAULT NULL,
  `recorded_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `machine_performance`
--

INSERT INTO `machine_performance` (`performance_id`, `machine_id`, `machine_name`, `uptime`, `downtime`, `last_maintenance`, `recorded_at`) VALUES
(1, 'MACH-001', 'CNC Router', 95.50, 4.50, '2023-06-05', '2023-06-15 08:00:00'),
(2, 'MACH-002', 'Laser Cutter', 98.25, 1.75, '2023-05-28', '2023-06-15 08:15:00'),
(3, 'MACH-003', '3D Printer', 92.75, 7.25, '2023-06-10', '2023-06-15 08:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `marketing_budgets`
--

CREATE TABLE `marketing_budgets` (
  `budget_id` int(11) NOT NULL,
  `department` varchar(100) NOT NULL,
  `allocated_amount` decimal(10,2) NOT NULL,
  `spent_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fiscal_year` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marketing_budgets`
--

INSERT INTO `marketing_budgets` (`budget_id`, `department`, `allocated_amount`, `spent_amount`, `fiscal_year`) VALUES
(1, 'Marketing', 50000.00, 42350.75, '2023'),
(2, 'Marketing', 45000.00, 45000.00, '2022'),
(3, 'Marketing', 75000.00, 12500.00, '2025');

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `material_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `availability` enum('In Stock','Low Stock','Out of Stock') NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `materials`
--

INSERT INTO `materials` (`material_id`, `supplier_id`, `category_id`, `item_id`, `availability`, `price`) VALUES
(1, 1, 1, 1, 'Out of Stock', 10.00);

-- --------------------------------------------------------

--
-- Table structure for table `material_categories`
--

CREATE TABLE `material_categories` (
  `category_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `material_categories`
--

INSERT INTO `material_categories` (`category_id`, `supplier_id`, `name`, `description`) VALUES
(1, 1, 'Banners', 'good one');

-- --------------------------------------------------------

--
-- Table structure for table `material_returns`
--

CREATE TABLE `material_returns` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('Pending','Approved','Rejected','Completed') NOT NULL DEFAULT 'Pending',
  `request_timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `material_returns`
--

INSERT INTO `material_returns` (`id`, `item_id`, `quantity`, `reason`, `status`, `request_timestamp`) VALUES
(1, 5, 2, 'Defective molding', 'Pending', '2023-06-14 10:45:00'),
(2, 3, 15, 'Incorrect size delivered', 'Approved', '2023-06-10 13:30:00'),
(3, 1, 50, 'Excess inventory', 'Completed', '2023-06-05 15:15:00'),
(4, 2, 1, 'Damaged during shipping', 'Rejected', '2023-06-08 11:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `network_performance`
--

CREATE TABLE `network_performance` (
  `metric_id` int(11) NOT NULL,
  `metric_type` varchar(50) NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `logged_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `network_performance`
--

INSERT INTO `network_performance` (`metric_id`, `metric_type`, `value`, `logged_at`) VALUES
(1, 'Bandwidth', 85.75, '2023-06-15 10:00:00'),
(2, 'Latency', 42.30, '2023-06-15 10:00:00'),
(3, 'Packet Loss', 1.25, '2023-06-15 10:00:00'),
(4, 'Bandwidth', 92.50, '2023-06-14 18:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `sent_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `recipient_id`, `message`, `sent_at`) VALUES
(1, 5, 'do the pending tasks by 9th June', '2025-06-04 16:36:10');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `custom_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `discount_id` int(11) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Pending',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `client_id`, `custom_id`, `quantity`, `total_amount`, `discount_id`, `status`, `created_at`) VALUES
(1, 3, 1, 500, 224.95, 1, 'Completed', '2025-06-10 14:36:49'),
(2, 3, 2, 1000, 1699.91, 1, 'Delivered', '2025-06-10 14:36:49'),
(3, 3, 3, 50, 1349.55, 3, 'Pending', '2025-06-10 14:36:49'),
(4, 3, 5, 150, 23998.80, 3, 'Pending', '2025-06-10 15:18:02'),
(5, 3, 4, 100, 4249.15, 1, 'Cancelled', '2025-06-10 15:18:16');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `method` varchar(20) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `invoice_id`, `method`, `amount`, `created_at`) VALUES
(3, 7, 'Cash', 5000.00, '2025-06-10 16:00:23');

-- --------------------------------------------------------

--
-- Table structure for table `payroll`
--

CREATE TABLE `payroll` (
  `payroll_id` int(11) NOT NULL,
  `EMP_ID` int(11) NOT NULL,
  `salary` decimal(10,2) NOT NULL CHECK (`salary` >= 0),
  `payment_date` date NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payroll`
--

INSERT INTO `payroll` (`payroll_id`, `EMP_ID`, `salary`, `payment_date`, `status`, `created_at`) VALUES
(7, 5, 4500.00, '2025-06-01', 'Pending', '2025-06-08 22:29:11'),
(8, 6, 4800.00, '2025-06-01', 'Approved', '2025-06-08 22:29:11'),
(9, 5, 5000.00, '2025-06-01', 'Pending', '2025-06-08 22:29:11'),
(10, 5, 4700.00, '2025-06-01', 'Pending', '2025-06-08 22:29:11'),
(11, 6, 4500.00, '2025-05-01', 'Pending', '2025-06-08 22:29:11'),
(12, 5, 4800.00, '2025-05-01', 'Pending', '2025-06-08 22:29:11');

-- --------------------------------------------------------

--
-- Table structure for table `performance_metrics`
--

CREATE TABLE `performance_metrics` (
  `metric_id` int(11) NOT NULL,
  `metric_name` varchar(255) NOT NULL,
  `value` varchar(100) NOT NULL,
  `recorded_at` datetime DEFAULT current_timestamp(),
  `target_value` varchar(255) NOT NULL,
  `updated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `performance_metrics`
--

INSERT INTO `performance_metrics` (`metric_id`, `metric_name`, `value`, `recorded_at`, `target_value`, `updated_at`) VALUES
(1, 'Monthly Revenue Growth', '12%', '2025-06-05 07:46:42', '0.00', '2025-06-08 12:07:46'),
(2, 'Customer Satisfaction Score', '89%', '2025-06-05 07:46:42', '0.00', '2025-06-08 12:07:46'),
(3, 'Average Ticket Resolution Time', '4.5 hours', '2025-06-05 07:46:42', '0.00', '2025-06-08 12:07:46'),
(4, 'Employee Retention Rate', '93%', '2025-06-05 07:46:42', '0.00', '2025-06-08 12:07:46'),
(5, 'Net Profit Margin', '22.4%', '2025-06-05 07:46:42', '0.00', '2025-06-08 12:07:46'),
(6, 'Monthly Active Users', '3,200', '2025-06-05 07:46:42', '0.00', '2025-06-08 12:07:46'),
(7, 'System Uptime', '99.98%', '2025-06-05 07:46:42', '0.00', '2025-06-08 12:07:46');

-- --------------------------------------------------------

--
-- Table structure for table `performance_reports`
--

CREATE TABLE `performance_reports` (
  `report_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `submission_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `performance_reports`
--

INSERT INTO `performance_reports` (`report_id`, `title`, `file_path`, `submission_date`) VALUES
(1, 'June Production Efficiency Report', '/reports/june_production.pdf', '2023-06-10 14:00:00'),
(2, 'Quality Control Analysis Q2', '/reports/q2_quality.pdf', '2023-06-05 11:30:00'),
(3, 'Machine Uptime Statistics May', '/reports/may_uptime.pdf', '2023-06-01 09:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `performance_reviews`
--

CREATE TABLE `performance_reviews` (
  `perf_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comments` text DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `performance_reviews`
--

INSERT INTO `performance_reviews` (`perf_id`, `rating`, `comments`, `employee_id`) VALUES
(1, 3, 'Average', 5);

-- --------------------------------------------------------

--
-- Table structure for table `production_costs`
--

CREATE TABLE `production_costs` (
  `cost_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `cost_type` enum('Material','Labor','Overhead') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `logged_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_costs`
--

INSERT INTO `production_costs` (`cost_id`, `order_id`, `cost_type`, `amount`, `logged_at`) VALUES
(1, 1, 'Material', 2500.00, '2023-06-01 10:00:00'),
(2, 1, 'Labor', 1800.50, '2023-06-05 14:30:00'),
(3, 2, 'Material', 1200.75, '2023-06-03 09:15:00'),
(4, 3, 'Overhead', 950.25, '2023-06-10 11:45:00'),
(5, 1, 'Labor', 1500.00, '2025-06-08 15:31:19');

-- --------------------------------------------------------

--
-- Table structure for table `production_orders`
--

CREATE TABLE `production_orders` (
  `order_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('Pending','In Progress','Completed') NOT NULL DEFAULT 'Pending',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_orders`
--

INSERT INTO `production_orders` (`order_id`, `product_name`, `quantity`, `due_date`, `status`, `created_at`) VALUES
(1, 'Widget A', 500, '2023-06-20', 'In Progress', '2023-05-28 10:00:00'),
(2, 'Gadget B', 200, '2023-06-25', 'In Progress', '2023-06-01 14:30:00'),
(3, 'Component C', 1000, '2023-06-15', 'Completed', '2023-05-25 09:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `production_output`
--

CREATE TABLE `production_output` (
  `output_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `production_date` date NOT NULL,
  `recorded_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_output`
--

INSERT INTO `production_output` (`output_id`, `product_name`, `quantity`, `production_date`, `recorded_at`) VALUES
(1, 'Model X', 120, '2023-06-14', '2023-06-14 17:30:00'),
(2, 'Model Y', 85, '2023-06-13', '2023-06-13 16:45:00'),
(3, 'Model Z', 200, '2023-06-10', '2023-06-10 18:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `production_reports`
--

CREATE TABLE `production_reports` (
  `report_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `generated_at` datetime NOT NULL,
  `file_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_reports`
--

INSERT INTO `production_reports` (`report_id`, `title`, `generated_at`, `file_path`) VALUES
(1, 'May 2023 Production Summary', '2023-06-01 09:00:00', '/reports/may_2023_production.pdf'),
(2, 'Q2 Quality Control Report', '2023-06-10 14:30:00', '/reports/q2_quality_control.pdf'),
(3, 'Equipment Maintenance Log', '2023-06-05 11:15:00', '/reports/equipment_maintenance_june.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `production_schedules`
--

CREATE TABLE `production_schedules` (
  `schedule_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_schedules`
--

INSERT INTO `production_schedules` (`schedule_id`, `product_name`, `quantity`, `start_date`, `end_date`, `created_at`) VALUES
(1, 'Widget A', 1000, '2023-06-01', '2023-06-15', '2023-05-25 09:00:00'),
(2, 'Gadget B', 500, '2023-06-10', '2023-06-25', '2023-05-28 14:30:00'),
(3, 'Component C', 2000, '2023-06-05', '2023-06-20', '2023-05-30 11:15:00'),
(4, 'Gadget D', 1500, '2025-06-11', '2025-07-09', '2025-06-08 15:29:45');

-- --------------------------------------------------------

--
-- Table structure for table `production_tasks`
--

CREATE TABLE `production_tasks` (
  `task_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `assigned_to` int(11) NOT NULL,
  `status` enum('Pending','In Progress','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  `deadline` date NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_tasks`
--

INSERT INTO `production_tasks` (`task_id`, `description`, `assigned_to`, `status`, `deadline`, `created_at`) VALUES
(1, 'Assemble 50 units of Model X', 5, 'Pending', '2023-06-20', '2023-06-12 09:30:00'),
(2, 'Quality check batch #1234', 5, 'In Progress', '2023-06-15', '2023-06-10 14:15:00'),
(3, 'Perform maintenance on CNC Router', 6, 'Completed', '2023-06-05', '2023-06-01 11:00:00'),
(4, 'Package finished products for shipment', 6, 'Pending', '2023-06-18', '2023-06-14 16:45:00');

-- --------------------------------------------------------

--
-- Table structure for table `production_workflow`
--

CREATE TABLE `production_workflow` (
  `workflow_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `step_name` varchar(100) NOT NULL,
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `submitted_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_workflow`
--

INSERT INTO `production_workflow` (`workflow_id`, `order_id`, `step_name`, `status`, `submitted_at`) VALUES
(1, 1, 'Initial Assembly', 'Approved', '2023-06-05 09:30:00'),
(2, 1, 'Quality Check', 'Rejected', '2023-06-10 14:15:00'),
(3, 2, 'Material Preparation', 'Pending', '2023-06-08 11:00:00'),
(4, 3, 'Final Packaging', 'Approved', '2023-06-12 16:45:00');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `price`, `updated_at`) VALUES
(1, 'Premium Widget', 99.99, '2023-06-01 11:20:00'),
(2, 'Basic Widget', 49.99, '2023-05-15 09:30:00'),
(3, 'Deluxe Package', 199.99, '2023-06-05 16:45:00'),
(4, 'Gold Package', 145.00, '2025-06-08 10:01:55');

-- --------------------------------------------------------

--
-- Table structure for table `product_designs`
--

CREATE TABLE `product_designs` (
  `design_id` int(11) NOT NULL,
  `design_name` varchar(100) NOT NULL,
  `designer_id` int(11) NOT NULL,
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `designer` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_designs`
--

INSERT INTO `product_designs` (`design_id`, `design_name`, `designer_id`, `status`, `created_at`, `updated_at`, `designer`) VALUES
(1, 'EcoWater Bottle', 1, 'Approved', '2023-05-15 09:30:00', NULL, 'Kasun'),
(2, 'SmartHome Hub', 2, 'Approved', '2023-06-01 14:15:00', NULL, 'Nimali'),
(3, 'Foldable Tablet', 3, 'Approved', '2023-06-05 11:00:00', NULL, 'Sahan'),
(4, 'Wireless Earbuds Pro', 1, 'Rejected', '2023-05-20 16:45:00', NULL, 'Prabhath'),
(5, 'Wireless Earbuds Pro', 1, 'Pending', '2023-05-20 16:45:00', NULL, 'Prabhath');

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `promotion_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promotions`
--

INSERT INTO `promotions` (`promotion_id`, `title`, `description`, `start_date`, `end_date`) VALUES
(1, 'Summer Sale', 'Biggest sale of the season with discounts up to 50%', '2023-06-15', '2023-06-30'),
(2, 'New Product Launch', 'Special introductory prices for our new product line', '2023-05-01', '2023-05-15'),
(3, 'Holiday Special', 'Celebrate the holidays with our exclusive offers', '2023-12-01', '2023-12-31'),
(4, 'Poya Special', 'Poson Poya', '2025-06-10', '2025-06-24');

-- --------------------------------------------------------

--
-- Table structure for table `quality_checks`
--

CREATE TABLE `quality_checks` (
  `check_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `check_date` date NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quality_checks`
--

INSERT INTO `quality_checks` (`check_id`, `product_name`, `status`, `check_date`, `created_at`) VALUES
(1, 'Model X', 'Pending', '2023-06-15', '2023-06-15 09:00:00'),
(2, 'Model Y', 'Approved', '2023-06-14', '2023-06-14 11:30:00'),
(3, 'Model Z', 'Rejected', '2023-06-11', '2023-06-11 10:15:00'),
(4, 'Model X', 'Approved', '2023-06-16', '2023-06-16 08:45:00');

-- --------------------------------------------------------

--
-- Table structure for table `quality_inspections`
--

CREATE TABLE `quality_inspections` (
  `inspection_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `inspected_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quality_inspections`
--

INSERT INTO `quality_inspections` (`inspection_id`, `order_id`, `product_name`, `status`, `inspected_at`) VALUES
(1, 1, 'Widget A', 'Approved', '2023-06-10 14:00:00'),
(2, 2, 'Gadget B', 'Pending', '2023-06-08 11:30:00'),
(3, 3, 'Component C', 'Approved', '2023-06-12 10:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `quality_reports`
--

CREATE TABLE `quality_reports` (
  `report_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `report_file` varchar(255) NOT NULL,
  `comments` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `refunds`
--

CREATE TABLE `refunds` (
  `refund_id` int(11) NOT NULL,
  `return_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Pending',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `refunds`
--

INSERT INTO `refunds` (`refund_id`, `return_id`, `amount`, `status`, `created_at`) VALUES
(1, 1, 5000.00, 'Pending', '2025-06-10 16:13:50');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `report_type` varchar(100) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `generated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`report_id`, `report_type`, `file_path`, `generated_at`) VALUES
(1, 'monthly salaries', 'D:\\CVs\\Thenuka Ubayasena (Intern - CV).pdf', '2025-06-04 19:53:05');

-- --------------------------------------------------------

--
-- Table structure for table `returns`
--

CREATE TABLE `returns` (
  `return_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Pending',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `returns`
--

INSERT INTO `returns` (`return_id`, `order_id`, `reason`, `status`, `created_at`) VALUES
(1, 2, 'It didn\'t worthy for this money', 'Approved', '2025-06-10 16:04:17');

-- --------------------------------------------------------

--
-- Table structure for table `sales_proposals`
--

CREATE TABLE `sales_proposals` (
  `proposal_id` int(11) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `submitted_at` datetime NOT NULL,
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_proposals`
--

INSERT INTO `sales_proposals` (`proposal_id`, `client_name`, `amount`, `submitted_at`, `status`) VALUES
(1, 'Acme Corporation', 15000.00, '2023-06-10 14:30:00', 'Approved'),
(2, 'Globex Inc', 25000.00, '2023-06-08 10:15:00', 'Pending'),
(3, 'Initech LLC', 18000.00, '2023-06-05 16:45:00', 'Approved'),
(4, 'Umbrella Corp', 30000.00, '2023-05-28 11:20:00', 'Rejected');

-- --------------------------------------------------------

--
-- Table structure for table `sales_reports`
--

CREATE TABLE `sales_reports` (
  `report_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `generated_at` datetime NOT NULL,
  `file_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_reports`
--

INSERT INTO `sales_reports` (`report_id`, `title`, `generated_at`, `file_path`) VALUES
(1, 'Q1 2023 Sales Report', '2023-04-15 10:30:00', '/reports/sales_q1_2023.pdf'),
(2, 'Marketing Campaign ROI', '2023-05-20 14:45:00', '/reports/marketing_roi_2023.pdf'),
(3, 'Annual Sales Summary 2022', '2023-01-10 09:15:00', '/reports/annual_2022.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL,
  `supplier_username` varchar(50) NOT NULL,
  `supplier_password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `supplier_username`, `supplier_password`) VALUES
(1, 'italy', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_feedback`
--

CREATE TABLE `supplier_feedback` (
  `feedback_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `comments` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier_info`
--

CREATE TABLE `supplier_info` (
  `info_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `company_name` varchar(100) NOT NULL,
  `contact_email` varchar(100) NOT NULL,
  `contact_phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier_info`
--

INSERT INTO `supplier_info` (`info_id`, `supplier_id`, `company_name`, `contact_email`, `contact_phone`, `address`, `updated_at`) VALUES
(1, 1, 'MAS', 'mas@gmail.com', '0719022937', 'Biyagama', '2025-06-10 17:19:45');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_invoices`
--

CREATE TABLE `supplier_invoices` (
  `invoice_id` int(11) NOT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `issue_date` date NOT NULL,
  `status` enum('Pending','Paid') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier_invoices`
--

INSERT INTO `supplier_invoices` (`invoice_id`, `supplier_name`, `amount`, `issue_date`, `status`) VALUES
(1, 'ABC Supplies', 820.00, '2025-05-15', 'Pending'),
(2, 'EcoPrint Solutions', 430.25, '2025-05-20', 'Paid'),
(3, 'Lanka Paper Co.', 980.00, '2025-06-01', 'Pending'),
(4, 'Global Traders', 1500.00, '2025-05-10', 'Paid');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_orders`
--

CREATE TABLE `supplier_orders` (
  `order_id` int(11) NOT NULL,
  `supplier_name` varchar(100) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `order_date` date NOT NULL,
  `status` enum('Pending','Shipped','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
  `created_at` datetime NOT NULL,
  `supplier_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier_orders`
--

INSERT INTO `supplier_orders` (`order_id`, `supplier_name`, `item_id`, `quantity`, `order_date`, `status`, `created_at`, `supplier_id`) VALUES
(7, 'Acme Paper Co.', 1, 20, '2025-05-15', 'Delivered', '2025-05-10 09:30:00', 1),
(8, 'Ink Solutions Ltd.', 2, 15, '2025-05-18', 'Shipped', '2025-05-12 14:15:00', 1),
(9, 'Printing Supplies Inc.', 3, 10, '2025-05-20', 'Pending', '2025-05-15 11:45:00', 1),
(10, 'Acme Paper Co.', 4, 30, '2025-06-01', 'Pending', '2025-05-28 16:20:00', 1),
(11, 'Ink Solutions Ltd.', 5, 25, '2025-06-05', 'Shipped', '2025-05-30 10:00:00', 1),
(12, 'Printing Supplies Inc.', 6, 8, '2025-06-08', 'Delivered', '2025-06-01 13:45:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `supplier_payments`
--

CREATE TABLE `supplier_payments` (
  `payment_id` int(11) NOT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier_payments`
--

INSERT INTO `supplier_payments` (`payment_id`, `supplier_name`, `amount`, `payment_date`) VALUES
(1, 'ABC Supplies', 750.00, '2025-06-01'),
(2, 'Global Traders', 1200.50, '2025-05-28'),
(3, 'EcoPrint Solutions', 430.25, '2025-06-03'),
(4, 'Lanka Paper Co.', 980.00, '2025-06-05'),
(5, 'MAS', 50000.00, '2025-06-04');

-- --------------------------------------------------------

--
-- Table structure for table `system_config`
--

CREATE TABLE `system_config` (
  `config_key` varchar(100) NOT NULL,
  `config_value` text NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_config`
--

INSERT INTO `system_config` (`config_key`, `config_value`, `updated_at`) VALUES
('admin_code', '14850', '2025-06-04 13:56:07'),
('site_name', 'DigiScan', '2025-06-04 13:51:06');

-- --------------------------------------------------------

--
-- Table structure for table `system_performance`
--

CREATE TABLE `system_performance` (
  `metric_id` int(11) NOT NULL,
  `metric_type` varchar(50) NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `logged_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_performance`
--

INSERT INTO `system_performance` (`metric_id`, `metric_type`, `value`, `logged_at`) VALUES
(1, 'CPU', 75.25, '2023-06-15 10:00:00'),
(2, 'Memory', 62.50, '2023-06-15 10:00:00'),
(3, 'Disk', 45.75, '2023-06-15 10:00:00'),
(4, 'CPU', 82.30, '2023-06-14 15:00:00'),
(5, 'Memory', 55.22, '2025-06-08 15:06:29');

-- --------------------------------------------------------

--
-- Table structure for table `task`
--

CREATE TABLE `task` (
  `task_id` int(11) NOT NULL,
  `task_title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `assigned_date` date NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('Pending','In Progress','Completed') DEFAULT 'Pending',
  `employee_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task`
--

INSERT INTO `task` (`task_id`, `task_title`, `description`, `assigned_date`, `due_date`, `status`, `employee_id`) VALUES
(1, 'Add new clients', 'New clients are waiting to the adding them to systen', '2025-06-05', '2025-06-07', 'Completed', 5);

-- --------------------------------------------------------

--
-- Table structure for table `tax_documents`
--

CREATE TABLE `tax_documents` (
  `document_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `year` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tax_documents`
--

INSERT INTO `tax_documents` (`document_id`, `type`, `year`, `file_path`, `created_at`) VALUES
(1, 'Income Tax', 2022, '/tax-docs/income_2022.pdf', '2023-04-15 10:00:00'),
(2, 'VAT Return', 2023, '/tax-docs/vat_q1_2023.pdf', '2023-06-10 14:30:00'),
(3, 'Corporate Tax', 2022, '/tax-docs/corp_2022.pdf', '2023-03-20 09:15:00'),
(4, 'Expenses Tax', 2025, '.pdf', '2025-06-08 22:31:15');

-- --------------------------------------------------------

--
-- Table structure for table `technical_issues`
--

CREATE TABLE `technical_issues` (
  `issue_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `status` enum('Open','Resolved') NOT NULL DEFAULT 'Open',
  `reported_at` datetime NOT NULL,
  `resolved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `technical_issues`
--

INSERT INTO `technical_issues` (`issue_id`, `description`, `status`, `reported_at`, `resolved_at`) VALUES
(1, 'Server downtime - unable to connect to database', 'Resolved', '2023-06-14 08:45:00', '2023-06-14 10:30:00'),
(2, 'Email service not working for some users', 'Open', '2023-06-15 14:20:00', NULL),
(3, 'Printer connection issues in accounting department', 'Resolved', '2023-06-10 11:00:00', '2023-06-10 12:45:00'),
(4, 'Server downtime - unable to connect to database', 'Resolved', '2023-06-14 08:45:00', '2023-06-14 10:30:00'),
(5, 'Email service not working for some users', 'Open', '2023-06-15 14:20:00', NULL),
(6, 'Printer connection issues in accounting department', 'Resolved', '2023-06-10 11:00:00', '2023-06-10 12:45:00'),
(7, 'AC is not working in IT dep', 'Open', '2025-06-08 15:06:08', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `terms_agreements`
--

CREATE TABLE `terms_agreements` (
  `term_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `terms_agreements`
--

INSERT INTO `terms_agreements` (`term_id`, `client_id`, `title`, `content`, `created_at`) VALUES
(1, 1, 'Printing Service Agreement', 'This agreement covers all printing services provided by DigiScan...', '2025-06-10 14:07:20'),
(2, 2, 'Confidentiality Agreement', 'All designs and materials provided to DigiScan must remain confidential...', '2025-06-10 14:07:20');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `status` enum('Unverified','Verified','Rejected') DEFAULT 'Unverified',
  `submitted_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `description`, `amount`, `status`, `submitted_at`, `updated_at`) VALUES
(1, 'Buy tables and chairs for employees', 29000.00, 'Unverified', '2025-06-05 07:15:14', '2025-06-08 23:19:46'),
(2, 'Rent a car monthly for office use', 40000.00, 'Verified', '2025-06-05 07:21:14', '2025-06-08 23:17:47'),
(3, '3 AC machines', 750000.00, 'Rejected', '2025-06-05 07:22:02', '2025-06-08 23:21:03');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) DEFAULT 'employee'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin', '1234', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `user_access`
--

CREATE TABLE `user_access` (
  `access_id` int(11) NOT NULL,
  `EMP_ID` int(11) NOT NULL,
  `role` enum('Admin','User') NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_access`
--

INSERT INTO `user_access` (`access_id`, `EMP_ID`, `role`, `updated_at`) VALUES
(5, 5, 'Admin', '2023-06-15 09:30:00'),
(6, 6, 'User', '2023-06-14 14:15:00'),
(7, 5, 'Admin', '2023-06-15 09:30:00'),
(8, 6, 'User', '2023-06-14 14:15:00'),
(9, 5, 'User', '2025-06-08 15:05:34');

-- --------------------------------------------------------

--
-- Table structure for table `work_schedule`
--

CREATE TABLE `work_schedule` (
  `schedule_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `shift_start` time NOT NULL,
  `shift_end` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `work_schedule`
--

INSERT INTO `work_schedule` (`schedule_id`, `employee_id`, `date`, `shift_start`, `shift_end`) VALUES
(1, 5, '2025-06-05', '13:59:41', '16:59:41');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`audit_id`);

--
-- Indexes for table `backup_log`
--
ALTER TABLE `backup_log`
  ADD PRIMARY KEY (`backup_id`);

--
-- Indexes for table `budgets`
--
ALTER TABLE `budgets`
  ADD PRIMARY KEY (`budget_id`);

--
-- Indexes for table `catalogue`
--
ALTER TABLE `catalogue`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`client_id`),
  ADD UNIQUE KEY `client_username` (`client_username`);

--
-- Indexes for table `client_company`
--
ALTER TABLE `client_company`
  ADD PRIMARY KEY (`cl_id`);

--
-- Indexes for table `client_order`
--
ALTER TABLE `client_order`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `company_goals`
--
ALTER TABLE `company_goals`
  ADD PRIMARY KEY (`goal_id`);

--
-- Indexes for table `company_policies`
--
ALTER TABLE `company_policies`
  ADD PRIMARY KEY (`policy_id`);

--
-- Indexes for table `customer_feedback`
--
ALTER TABLE `customer_feedback`
  ADD PRIMARY KEY (`feedback_id`);

--
-- Indexes for table `customer_invoices`
--
ALTER TABLE `customer_invoices`
  ADD PRIMARY KEY (`invoice_id`);

--
-- Indexes for table `custom_products`
--
ALTER TABLE `custom_products`
  ADD PRIMARY KEY (`custom_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `data_privacy_audits`
--
ALTER TABLE `data_privacy_audits`
  ADD PRIMARY KEY (`audit_id`);

--
-- Indexes for table `decisions`
--
ALTER TABLE `decisions`
  ADD PRIMARY KEY (`decision_id`);

--
-- Indexes for table `departmental_budgets`
--
ALTER TABLE `departmental_budgets`
  ADD PRIMARY KEY (`budget_id`);

--
-- Indexes for table `designed_files`
--
ALTER TABLE `designed_files`
  ADD PRIMARY KEY (`file_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `custom_id` (`custom_id`);

--
-- Indexes for table `design_reports`
--
ALTER TABLE `design_reports`
  ADD PRIMARY KEY (`report_id`);

--
-- Indexes for table `discounts`
--
ALTER TABLE `discounts`
  ADD PRIMARY KEY (`discount_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`EMP_ID`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `employee_db`
--
ALTER TABLE `employee_db`
  ADD PRIMARY KEY (`record_id`),
  ADD UNIQUE KEY `EMP_ID` (`EMP_ID`);

--
-- Indexes for table `employee_feedback`
--
ALTER TABLE `employee_feedback`
  ADD PRIMARY KEY (`feed_id`),
  ADD KEY `fk_employee_feedback` (`employee_id`);

--
-- Indexes for table `encryption_logs`
--
ALTER TABLE `encryption_logs`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `equipment_allocation`
--
ALTER TABLE `equipment_allocation`
  ADD PRIMARY KEY (`allocation_id`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Indexes for table `equipment_logs`
--
ALTER TABLE `equipment_logs`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `financial_reports`
--
ALTER TABLE `financial_reports`
  ADD PRIMARY KEY (`report_id`);

--
-- Indexes for table `financial_transactions`
--
ALTER TABLE `financial_transactions`
  ADD PRIMARY KEY (`transaction_id`);

--
-- Indexes for table `hr_reports`
--
ALTER TABLE `hr_reports`
  ADD PRIMARY KEY (`report_id`);

--
-- Indexes for table `inquiries`
--
ALTER TABLE `inquiries`
  ADD PRIMARY KEY (`inquiry_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `inventory_reports`
--
ALTER TABLE `inventory_reports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_updates`
--
ALTER TABLE `inventory_updates`
  ADD PRIMARY KEY (`update_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `material_id` (`material_id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`invoice_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`lr_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `machine_performance`
--
ALTER TABLE `machine_performance`
  ADD PRIMARY KEY (`performance_id`);

--
-- Indexes for table `marketing_budgets`
--
ALTER TABLE `marketing_budgets`
  ADD PRIMARY KEY (`budget_id`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`material_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `material_categories`
--
ALTER TABLE `material_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `material_returns`
--
ALTER TABLE `material_returns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `network_performance`
--
ALTER TABLE `network_performance`
  ADD PRIMARY KEY (`metric_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `recipient_id` (`recipient_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `custom_id` (`custom_id`),
  ADD KEY `discount_id` (`discount_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indexes for table `payroll`
--
ALTER TABLE `payroll`
  ADD PRIMARY KEY (`payroll_id`),
  ADD KEY `EMP_ID` (`EMP_ID`);

--
-- Indexes for table `performance_metrics`
--
ALTER TABLE `performance_metrics`
  ADD PRIMARY KEY (`metric_id`);

--
-- Indexes for table `performance_reports`
--
ALTER TABLE `performance_reports`
  ADD PRIMARY KEY (`report_id`);

--
-- Indexes for table `performance_reviews`
--
ALTER TABLE `performance_reviews`
  ADD PRIMARY KEY (`perf_id`),
  ADD KEY `fk_employee_review` (`employee_id`);

--
-- Indexes for table `production_costs`
--
ALTER TABLE `production_costs`
  ADD PRIMARY KEY (`cost_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `production_orders`
--
ALTER TABLE `production_orders`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `production_output`
--
ALTER TABLE `production_output`
  ADD PRIMARY KEY (`output_id`);

--
-- Indexes for table `production_reports`
--
ALTER TABLE `production_reports`
  ADD PRIMARY KEY (`report_id`);

--
-- Indexes for table `production_schedules`
--
ALTER TABLE `production_schedules`
  ADD PRIMARY KEY (`schedule_id`);

--
-- Indexes for table `production_tasks`
--
ALTER TABLE `production_tasks`
  ADD PRIMARY KEY (`task_id`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Indexes for table `production_workflow`
--
ALTER TABLE `production_workflow`
  ADD PRIMARY KEY (`workflow_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `product_name` (`product_name`);

--
-- Indexes for table `product_designs`
--
ALTER TABLE `product_designs`
  ADD PRIMARY KEY (`design_id`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`promotion_id`);

--
-- Indexes for table `quality_checks`
--
ALTER TABLE `quality_checks`
  ADD PRIMARY KEY (`check_id`);

--
-- Indexes for table `quality_inspections`
--
ALTER TABLE `quality_inspections`
  ADD PRIMARY KEY (`inspection_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `quality_reports`
--
ALTER TABLE `quality_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `material_id` (`material_id`);

--
-- Indexes for table `refunds`
--
ALTER TABLE `refunds`
  ADD PRIMARY KEY (`refund_id`),
  ADD KEY `return_id` (`return_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`);

--
-- Indexes for table `returns`
--
ALTER TABLE `returns`
  ADD PRIMARY KEY (`return_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `sales_proposals`
--
ALTER TABLE `sales_proposals`
  ADD PRIMARY KEY (`proposal_id`);

--
-- Indexes for table `sales_reports`
--
ALTER TABLE `sales_reports`
  ADD PRIMARY KEY (`report_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`),
  ADD UNIQUE KEY `supplier_username` (`supplier_username`);

--
-- Indexes for table `supplier_feedback`
--
ALTER TABLE `supplier_feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `supplier_info`
--
ALTER TABLE `supplier_info`
  ADD PRIMARY KEY (`info_id`),
  ADD UNIQUE KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `supplier_invoices`
--
ALTER TABLE `supplier_invoices`
  ADD PRIMARY KEY (`invoice_id`);

--
-- Indexes for table `supplier_orders`
--
ALTER TABLE `supplier_orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `supplier_payments`
--
ALTER TABLE `supplier_payments`
  ADD PRIMARY KEY (`payment_id`);

--
-- Indexes for table `system_config`
--
ALTER TABLE `system_config`
  ADD PRIMARY KEY (`config_key`);

--
-- Indexes for table `system_performance`
--
ALTER TABLE `system_performance`
  ADD PRIMARY KEY (`metric_id`);

--
-- Indexes for table `task`
--
ALTER TABLE `task`
  ADD PRIMARY KEY (`task_id`),
  ADD KEY `fk_employee_task` (`employee_id`);

--
-- Indexes for table `tax_documents`
--
ALTER TABLE `tax_documents`
  ADD PRIMARY KEY (`document_id`);

--
-- Indexes for table `technical_issues`
--
ALTER TABLE `technical_issues`
  ADD PRIMARY KEY (`issue_id`);

--
-- Indexes for table `terms_agreements`
--
ALTER TABLE `terms_agreements`
  ADD PRIMARY KEY (`term_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_access`
--
ALTER TABLE `user_access`
  ADD PRIMARY KEY (`access_id`),
  ADD KEY `EMP_ID` (`EMP_ID`);

--
-- Indexes for table `work_schedule`
--
ALTER TABLE `work_schedule`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `audit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `backup_log`
--
ALTER TABLE `backup_log`
  MODIFY `backup_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `budgets`
--
ALTER TABLE `budgets`
  MODIFY `budget_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `catalogue`
--
ALTER TABLE `catalogue`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `client_company`
--
ALTER TABLE `client_company`
  MODIFY `cl_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `client_order`
--
ALTER TABLE `client_order`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `company_goals`
--
ALTER TABLE `company_goals`
  MODIFY `goal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `company_policies`
--
ALTER TABLE `company_policies`
  MODIFY `policy_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `customer_feedback`
--
ALTER TABLE `customer_feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `customer_invoices`
--
ALTER TABLE `customer_invoices`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `custom_products`
--
ALTER TABLE `custom_products`
  MODIFY `custom_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `data_privacy_audits`
--
ALTER TABLE `data_privacy_audits`
  MODIFY `audit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `decisions`
--
ALTER TABLE `decisions`
  MODIFY `decision_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `departmental_budgets`
--
ALTER TABLE `departmental_budgets`
  MODIFY `budget_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `designed_files`
--
ALTER TABLE `designed_files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `design_reports`
--
ALTER TABLE `design_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `discounts`
--
ALTER TABLE `discounts`
  MODIFY `discount_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `EMP_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `employee_db`
--
ALTER TABLE `employee_db`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `employee_feedback`
--
ALTER TABLE `employee_feedback`
  MODIFY `feed_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `encryption_logs`
--
ALTER TABLE `encryption_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `equipment_allocation`
--
ALTER TABLE `equipment_allocation`
  MODIFY `allocation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `equipment_logs`
--
ALTER TABLE `equipment_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `financial_reports`
--
ALTER TABLE `financial_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `financial_transactions`
--
ALTER TABLE `financial_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `hr_reports`
--
ALTER TABLE `hr_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `inquiries`
--
ALTER TABLE `inquiries`
  MODIFY `inquiry_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `inventory_reports`
--
ALTER TABLE `inventory_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `inventory_updates`
--
ALTER TABLE `inventory_updates`
  MODIFY `update_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `lr_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `machine_performance`
--
ALTER TABLE `machine_performance`
  MODIFY `performance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `marketing_budgets`
--
ALTER TABLE `marketing_budgets`
  MODIFY `budget_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `material_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `material_categories`
--
ALTER TABLE `material_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `material_returns`
--
ALTER TABLE `material_returns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `network_performance`
--
ALTER TABLE `network_performance`
  MODIFY `metric_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `payroll_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `performance_metrics`
--
ALTER TABLE `performance_metrics`
  MODIFY `metric_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `performance_reports`
--
ALTER TABLE `performance_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `performance_reviews`
--
ALTER TABLE `performance_reviews`
  MODIFY `perf_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `production_costs`
--
ALTER TABLE `production_costs`
  MODIFY `cost_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `production_orders`
--
ALTER TABLE `production_orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `production_output`
--
ALTER TABLE `production_output`
  MODIFY `output_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `production_reports`
--
ALTER TABLE `production_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `production_schedules`
--
ALTER TABLE `production_schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `production_tasks`
--
ALTER TABLE `production_tasks`
  MODIFY `task_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `production_workflow`
--
ALTER TABLE `production_workflow`
  MODIFY `workflow_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `product_designs`
--
ALTER TABLE `product_designs`
  MODIFY `design_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `promotion_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `quality_checks`
--
ALTER TABLE `quality_checks`
  MODIFY `check_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `quality_inspections`
--
ALTER TABLE `quality_inspections`
  MODIFY `inspection_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `quality_reports`
--
ALTER TABLE `quality_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `refunds`
--
ALTER TABLE `refunds`
  MODIFY `refund_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sales_proposals`
--
ALTER TABLE `sales_proposals`
  MODIFY `proposal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sales_reports`
--
ALTER TABLE `sales_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `supplier_feedback`
--
ALTER TABLE `supplier_feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier_info`
--
ALTER TABLE `supplier_info`
  MODIFY `info_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `supplier_invoices`
--
ALTER TABLE `supplier_invoices`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `supplier_orders`
--
ALTER TABLE `supplier_orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `supplier_payments`
--
ALTER TABLE `supplier_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `system_performance`
--
ALTER TABLE `system_performance`
  MODIFY `metric_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `task`
--
ALTER TABLE `task`
  MODIFY `task_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tax_documents`
--
ALTER TABLE `tax_documents`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `technical_issues`
--
ALTER TABLE `technical_issues`
  MODIFY `issue_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `terms_agreements`
--
ALTER TABLE `terms_agreements`
  MODIFY `term_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_access`
--
ALTER TABLE `user_access`
  MODIFY `access_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `work_schedule`
--
ALTER TABLE `work_schedule`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`EMP_ID`) ON DELETE CASCADE;

--
-- Constraints for table `client_order`
--
ALTER TABLE `client_order`
  ADD CONSTRAINT `client_order_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `client_company` (`cl_id`) ON DELETE CASCADE;

--
-- Constraints for table `custom_products`
--
ALTER TABLE `custom_products`
  ADD CONSTRAINT `custom_products_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`),
  ADD CONSTRAINT `custom_products_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `catalogue` (`item_id`);

--
-- Constraints for table `designed_files`
--
ALTER TABLE `designed_files`
  ADD CONSTRAINT `designed_files_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`),
  ADD CONSTRAINT `designed_files_ibfk_2` FOREIGN KEY (`custom_id`) REFERENCES `custom_products` (`custom_id`);

--
-- Constraints for table `discounts`
--
ALTER TABLE `discounts`
  ADD CONSTRAINT `discounts_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `employee_db`
--
ALTER TABLE `employee_db`
  ADD CONSTRAINT `employee_db_ibfk_1` FOREIGN KEY (`EMP_ID`) REFERENCES `employees` (`EMP_ID`);

--
-- Constraints for table `employee_feedback`
--
ALTER TABLE `employee_feedback`
  ADD CONSTRAINT `fk_employee_feedback` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`EMP_ID`) ON DELETE SET NULL;

--
-- Constraints for table `equipment_allocation`
--
ALTER TABLE `equipment_allocation`
  ADD CONSTRAINT `equipment_allocation_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `employees` (`EMP_ID`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`),
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `inquiries`
--
ALTER TABLE `inquiries`
  ADD CONSTRAINT `inquiries_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`),
  ADD CONSTRAINT `inquiries_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `catalogue` (`item_id`);

--
-- Constraints for table `inventory_updates`
--
ALTER TABLE `inventory_updates`
  ADD CONSTRAINT `inventory_updates_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`),
  ADD CONSTRAINT `inventory_updates_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `materials` (`material_id`);

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`EMP_ID`) ON DELETE CASCADE;

--
-- Constraints for table `materials`
--
ALTER TABLE `materials`
  ADD CONSTRAINT `materials_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`),
  ADD CONSTRAINT `materials_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `material_categories` (`category_id`),
  ADD CONSTRAINT `materials_ibfk_3` FOREIGN KEY (`item_id`) REFERENCES `catalogue` (`item_id`);

--
-- Constraints for table `material_categories`
--
ALTER TABLE `material_categories`
  ADD CONSTRAINT `material_categories_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`);

--
-- Constraints for table `material_returns`
--
ALTER TABLE `material_returns`
  ADD CONSTRAINT `material_returns_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`item_id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`recipient_id`) REFERENCES `employees` (`EMP_ID`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`custom_id`) REFERENCES `custom_products` (`custom_id`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`discount_id`) REFERENCES `discounts` (`discount_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`invoice_id`);

--
-- Constraints for table `payroll`
--
ALTER TABLE `payroll`
  ADD CONSTRAINT `payroll_ibfk_1` FOREIGN KEY (`EMP_ID`) REFERENCES `employees` (`EMP_ID`) ON DELETE CASCADE;

--
-- Constraints for table `performance_reviews`
--
ALTER TABLE `performance_reviews`
  ADD CONSTRAINT `fk_employee_review` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`EMP_ID`) ON DELETE CASCADE;

--
-- Constraints for table `production_costs`
--
ALTER TABLE `production_costs`
  ADD CONSTRAINT `production_costs_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `production_orders` (`order_id`);

--
-- Constraints for table `production_tasks`
--
ALTER TABLE `production_tasks`
  ADD CONSTRAINT `production_tasks_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `employees` (`EMP_ID`);

--
-- Constraints for table `production_workflow`
--
ALTER TABLE `production_workflow`
  ADD CONSTRAINT `production_workflow_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `production_orders` (`order_id`);

--
-- Constraints for table `quality_inspections`
--
ALTER TABLE `quality_inspections`
  ADD CONSTRAINT `quality_inspections_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `production_orders` (`order_id`);

--
-- Constraints for table `quality_reports`
--
ALTER TABLE `quality_reports`
  ADD CONSTRAINT `quality_reports_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`),
  ADD CONSTRAINT `quality_reports_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `materials` (`material_id`);

--
-- Constraints for table `refunds`
--
ALTER TABLE `refunds`
  ADD CONSTRAINT `refunds_ibfk_1` FOREIGN KEY (`return_id`) REFERENCES `returns` (`return_id`);

--
-- Constraints for table `returns`
--
ALTER TABLE `returns`
  ADD CONSTRAINT `returns_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `supplier_feedback`
--
ALTER TABLE `supplier_feedback`
  ADD CONSTRAINT `supplier_feedback_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`);

--
-- Constraints for table `supplier_info`
--
ALTER TABLE `supplier_info`
  ADD CONSTRAINT `supplier_info_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`);

--
-- Constraints for table `supplier_orders`
--
ALTER TABLE `supplier_orders`
  ADD CONSTRAINT `supplier_orders_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`item_id`),
  ADD CONSTRAINT `supplier_orders_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`);

--
-- Constraints for table `task`
--
ALTER TABLE `task`
  ADD CONSTRAINT `fk_employee_task` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`EMP_ID`) ON DELETE CASCADE;

--
-- Constraints for table `terms_agreements`
--
ALTER TABLE `terms_agreements`
  ADD CONSTRAINT `terms_agreements_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`);

--
-- Constraints for table `user_access`
--
ALTER TABLE `user_access`
  ADD CONSTRAINT `user_access_ibfk_1` FOREIGN KEY (`EMP_ID`) REFERENCES `employees` (`EMP_ID`);

--
-- Constraints for table `work_schedule`
--
ALTER TABLE `work_schedule`
  ADD CONSTRAINT `work_schedule_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`EMP_ID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

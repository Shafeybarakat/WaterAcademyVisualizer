-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 23, 2025 at 02:35 PM
-- Server version: 10.11.10-MariaDB
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u652025084_new_wa_db`
--
CREATE DATABASE IF NOT EXISTS `u652025084_new_wa_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `u652025084_new_wa_db`;

DELIMITER $$
--
-- Procedures
--
DROP PROCEDURE IF EXISTS `AddTraineesWithEnrollments`$$
CREATE DEFINER=`u652025084_new_wa_user`@`127.0.0.1` PROCEDURE `AddTraineesWithEnrollments` (IN `groupName` VARCHAR(50), IN `firstName1` VARCHAR(50), IN `lastName1` VARCHAR(50), IN `email1` VARCHAR(100), IN `firstName2` VARCHAR(50), IN `lastName2` VARCHAR(50), IN `email2` VARCHAR(100), IN `firstName3` VARCHAR(50), IN `lastName3` VARCHAR(50), IN `email3` VARCHAR(100), IN `firstName4` VARCHAR(50), IN `lastName4` VARCHAR(50), IN `email4` VARCHAR(100), IN `firstName5` VARCHAR(50), IN `lastName5` VARCHAR(50), IN `email5` VARCHAR(100))   BEGIN
    DECLARE groupId INT;
    DECLARE traineeId1, traineeId2, traineeId3, traineeId4, traineeId5 INT;
    
    -- Get the GroupID
    SELECT GroupID INTO groupId FROM `Groups` WHERE GroupName = groupName;
    
    -- Insert trainees
    INSERT INTO `Trainees` (`FirstName`, `LastName`, `Email`, `GroupID`, `Status`) 
    VALUES (firstName1, lastName1, email1, groupId, 'Active');
    SET traineeId1 = LAST_INSERT_ID();
    
    INSERT INTO `Trainees` (`FirstName`, `LastName`, `Email`, `GroupID`, `Status`) 
    VALUES (firstName2, lastName2, email2, groupId, 'Active');
    SET traineeId2 = LAST_INSERT_ID();
    
    INSERT INTO `Trainees` (`FirstName`, `LastName`, `Email`, `GroupID`, `Status`) 
    VALUES (firstName3, lastName3, email3, groupId, 'Active');
    SET traineeId3 = LAST_INSERT_ID();
    
    INSERT INTO `Trainees` (`FirstName`, `LastName`, `Email`, `GroupID`, `Status`) 
    VALUES (firstName4, lastName4, email4, groupId, 'Active');
    SET traineeId4 = LAST_INSERT_ID();
    
    INSERT INTO `Trainees` (`FirstName`, `LastName`, `Email`, `GroupID`, `Status`) 
    VALUES (firstName5, lastName5, email5, groupId, 'Active');
    SET traineeId5 = LAST_INSERT_ID();
    
    -- Enroll each trainee in all courses for this group
    INSERT INTO `Enrollments` (`TID`, `GroupCourseID`, `EnrollmentDate`, `Status`)
    SELECT traineeId1, ID, CURDATE(), 'Enrolled'
    FROM `GroupCourses` WHERE GroupID = groupId;
    
    INSERT INTO `Enrollments` (`TID`, `GroupCourseID`, `EnrollmentDate`, `Status`)
    SELECT traineeId2, ID, CURDATE(), 'Enrolled'
    FROM `GroupCourses` WHERE GroupID = groupId;
    
    INSERT INTO `Enrollments` (`TID`, `GroupCourseID`, `EnrollmentDate`, `Status`)
    SELECT traineeId3, ID, CURDATE(), 'Enrolled'
    FROM `GroupCourses` WHERE GroupID = groupId;
    
    INSERT INTO `Enrollments` (`TID`, `GroupCourseID`, `EnrollmentDate`, `Status`)
    SELECT traineeId4, ID, CURDATE(), 'Enrolled'
    FROM `GroupCourses` WHERE GroupID = groupId;
    
    INSERT INTO `Enrollments` (`TID`, `GroupCourseID`, `EnrollmentDate`, `Status`)
    SELECT traineeId5, ID, CURDATE(), 'Enrolled'
    FROM `GroupCourses` WHERE GroupID = groupId;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `Attendance`
--
-- Creation: May 21, 2025 at 12:26 AM
-- Last update: May 21, 2025 at 12:26 AM
--

DROP TABLE IF EXISTS `Attendance`;
CREATE TABLE `Attendance` (
  `AttendanceID` int(11) NOT NULL,
  `TID` int(11) NOT NULL,
  `GroupCourseID` int(11) NOT NULL,
  `PresentHours` decimal(5,1) DEFAULT NULL,
  `ExcusedHours` decimal(5,1) DEFAULT NULL,
  `LateHours` decimal(5,1) DEFAULT NULL,
  `AbsentHours` decimal(5,1) DEFAULT NULL,
  `TakenSessions` int(11) DEFAULT NULL,
  `MoodlePoints` decimal(5,2) DEFAULT NULL,
  `AttendancePercentage` decimal(5,2) NOT NULL,
  `Notes` text DEFAULT NULL,
  `RecordedBy` int(11) DEFAULT NULL,
  `CreatedAt` timestamp NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `SessionDate` date NOT NULL,
  `Status` enum('Present','Absent','Late','Excused') NOT NULL DEFAULT 'Absent'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Attendance`
--

INSERT INTO `Attendance` (`AttendanceID`, `TID`, `GroupCourseID`, `PresentHours`, `ExcusedHours`, `LateHours`, `AbsentHours`, `TakenSessions`, `MoodlePoints`, `AttendancePercentage`, `Notes`, `RecordedBy`, `CreatedAt`, `UpdatedAt`, `SessionDate`, `Status`) VALUES
(1, 1, 1, 36.0, 0.0, 1.0, 3.0, 12, NULL, 90.00, 'Generally good attendance, one absence.', 3, '2025-05-16 01:27:06', '2025-05-16 01:27:06', '0000-00-00', 'Absent'),
(2, 2, 1, 40.0, 0.0, 0.0, 0.0, 12, NULL, 100.00, 'Perfect attendance.', 3, '2025-05-16 01:27:06', '2025-05-16 01:27:06', '0000-00-00', 'Absent'),
(3, 4, 3, 75.0, 5.0, 0.0, 0.0, 20, NULL, 93.75, 'Excellent attendance, few excused hours.', 3, '2025-05-16 01:27:06', '2025-05-16 01:27:06', '0000-00-00', 'Absent'),
(4, 9, 4, 80.0, 0.1, 3.6, 4.9, 20, NULL, 86.06, NULL, 4, '2025-05-17 15:58:08', '2025-05-17 15:58:08', '0000-00-00', 'Absent'),
(5, 10, 4, 81.2, 4.7, 1.0, 2.1, 20, NULL, 89.06, NULL, 20, '2025-05-17 15:58:08', '2025-05-17 15:58:08', '0000-00-00', 'Absent'),
(6, 11, 4, 84.5, 3.3, 3.3, 2.8, 20, NULL, 88.25, NULL, 17, '2025-05-17 15:58:08', '2025-05-17 15:58:08', '0000-00-00', 'Absent'),
(7, 12, 4, 77.4, 2.4, 1.0, 5.3, 20, NULL, 80.97, NULL, 5, '2025-05-17 15:58:08', '2025-05-17 15:58:08', '0000-00-00', 'Absent'),
(8, 13, 4, 65.3, 1.8, 1.3, 2.6, 20, NULL, 89.98, NULL, 5, '2025-05-17 15:58:08', '2025-05-17 15:58:08', '0000-00-00', 'Absent'),
(11, 9, 5, 87.3, 4.7, 4.1, 3.2, 20, NULL, 82.96, NULL, 18, '2025-05-17 15:58:08', '2025-05-17 15:58:08', '0000-00-00', 'Absent'),
(12, 10, 5, 47.9, 2.5, 0.2, 7.3, 20, NULL, 89.87, NULL, 20, '2025-05-17 15:58:08', '2025-05-17 15:58:08', '0000-00-00', 'Absent'),
(13, 11, 5, 68.8, 3.2, 2.2, 2.7, 20, NULL, 81.09, NULL, 18, '2025-05-17 15:58:08', '2025-05-17 15:58:08', '0000-00-00', 'Absent'),
(14, 12, 5, 42.5, 0.8, 3.2, 7.1, 20, NULL, 92.96, NULL, 19, '2025-05-17 15:58:08', '2025-05-17 15:58:08', '0000-00-00', 'Absent'),
(15, 13, 5, 70.0, 3.1, 1.4, 5.6, 20, NULL, 99.02, NULL, 4, '2025-05-17 15:58:08', '2025-05-17 15:58:08', '0000-00-00', 'Absent'),
(18, 9, 6, 22.1, 0.7, 3.0, 1.1, 10, NULL, 82.99, NULL, 4, '2025-05-17 15:58:08', '2025-05-17 15:58:08', '0000-00-00', 'Absent'),
(19, 10, 6, 21.9, 2.0, 0.4, 3.3, 10, NULL, 97.53, NULL, 18, '2025-05-17 15:58:08', '2025-05-17 15:58:08', '0000-00-00', 'Absent'),
(20, 11, 6, 28.9, 1.8, 2.7, 3.4, 10, NULL, 94.64, NULL, 20, '2025-05-17 15:58:08', '2025-05-17 15:58:08', '0000-00-00', 'Absent'),
(21, 12, 6, 22.7, 1.3, 2.7, 0.8, 10, NULL, 82.37, NULL, 4, '2025-05-17 15:58:08', '2025-05-17 15:58:08', '0000-00-00', 'Absent'),
(22, 13, 6, 25.4, 2.3, 0.4, 1.8, 10, NULL, 88.74, NULL, 4, '2025-05-17 15:58:08', '2025-05-17 15:58:08', '0000-00-00', 'Absent'),
(25, 14, 7, 65.8, 4.3, 3.1, 5.0, 15, NULL, 92.95, NULL, 20, '2025-05-17 15:58:08', '2025-05-17 15:58:08', '0000-00-00', 'Absent'),
(26, 15, 7, 30.4, 4.5, 2.2, 4.8, 15, NULL, 81.70, NULL, 18, '2025-05-17 15:58:08', '2025-05-17 15:58:08', '0000-00-00', 'Absent'),
(27, 16, 7, 68.6, 4.0, 0.7, 2.9, 15, NULL, 80.70, NULL, 17, '2025-05-17 15:58:08', '2025-05-17 15:58:08', '0000-00-00', 'Absent'),
(28, 17, 7, 49.9, 1.9, 2.0, 8.7, 15, NULL, 82.64, NULL, 20, '2025-05-17 15:58:08', '2025-05-17 15:58:08', '0000-00-00', 'Absent'),
(29, 18, 7, 64.7, 4.9, 1.4, 4.5, 15, NULL, 88.20, NULL, 18, '2025-05-17 15:58:08', '2025-05-17 15:58:08', '0000-00-00', 'Absent'),
(32, 14, 8, 49.3, 0.1, 0.7, 0.1, 10, NULL, 88.46, NULL, 5, '2025-05-17 15:58:08', '2025-05-17 15:58:08', '0000-00-00', 'Absent'),
(33, 15, 8, 22.7, 1.3, 2.8, 1.9, 10, NULL, 81.20, NULL, 4, '2025-05-17 15:58:08', '2025-05-17 15:58:08', '0000-00-00', 'Absent'),
(34, 16, 8, 34.6, 2.3, 1.3, 4.3, 10, NULL, 98.90, NULL, 5, '2025-05-17 15:58:08', '2025-05-17 15:58:08', '0000-00-00', 'Absent'),
(35, 17, 8, 42.7, 1.5, 0.7, 3.3, 10, NULL, 92.18, NULL, 4, '2025-05-17 15:58:08', '2025-05-17 15:58:08', '0000-00-00', 'Absent'),
(36, 18, 8, 34.0, 0.4, 0.7, 3.7, 10, NULL, 80.99, NULL, 4, '2025-05-17 15:58:08', '2025-05-17 15:58:08', '0000-00-00', 'Absent');

-- --------------------------------------------------------

--
-- Table structure for table `Courses`
--
-- Creation: May 15, 2025 at 11:58 PM
-- Last update: May 19, 2025 at 05:16 AM
--

DROP TABLE IF EXISTS `Courses`;
CREATE TABLE `Courses` (
  `CourseID` int(11) NOT NULL,
  `CourseName` varchar(100) NOT NULL,
  `CourseCode` varchar(20) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `DurationWeeks` int(11) DEFAULT NULL,
  `TotalHours` int(11) DEFAULT NULL,
  `CreatedAt` timestamp NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Status` enum('Active','Complete','Archived') NOT NULL DEFAULT 'Active' COMMENT 'Status of the course template: Active (can be used for new instances), Complete (template might be phased out but instances exist), Archived (no longer used, historical). Admin can manually set to Archived.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Courses`
--

INSERT INTO `Courses` (`CourseID`, `CourseName`, `CourseCode`, `Description`, `DurationWeeks`, `TotalHours`, `CreatedAt`, `UpdatedAt`, `Status`) VALUES
(1, 'Introduction to Water Chemistry', 'CHEM101', 'Fundamentals of water chemistry and quality parameters.', 4, 40, '2025-05-16 01:00:18', '2025-05-16 01:00:18', 'Active'),
(2, 'Advanced Hydrology', 'HYDRO201', 'In-depth study of hydrological cycles and water movement.', 2, 60, '2025-05-16 01:00:18', '2025-05-19 05:15:24', 'Active'),
(3, 'Wastewater Treatment Processes', 'WWTP301', 'Covers various physical, chemical, and biological wastewater treatment methods.', 8, 80, '2025-05-16 01:00:18', '2025-05-16 01:00:18', 'Active'),
(4, 'Water Distribution Systems', 'WDS102', 'Design and operation of water distribution networks.', 5, 50, '2025-05-16 01:00:18', '2025-05-16 01:00:18', 'Complete'),
(5, 'Old Desalination Techniques', 'DESAL001', 'Historical overview of desalination (to be archived).', 4, 30, '2025-05-16 01:00:18', '2025-05-16 01:00:18', 'Archived'),
(6, 'Basic Water Chemistry', 'BWC-101', 'Fundamentals of water chemistry including pH, alkalinity, hardness, and common contaminants', 4, 120, '2025-05-17 15:45:18', '2025-05-19 05:16:26', 'Active'),
(7, 'Water Quality Testing', 'WQT-201', 'Methods and procedures for testing water quality in various contexts', 3, 45, '2025-05-17 15:45:18', '2025-05-17 15:45:18', 'Active'),
(8, 'Pump Operations', 'PO-301', 'Operation and maintenance of various pump types used in water systems', 4, 60, '2025-05-17 15:45:18', '2025-05-17 15:45:18', 'Active'),
(9, 'Safety Procedures', 'SP-101', 'Essential safety protocols for water treatment and distribution facilities', 2, 30, '2025-05-17 15:45:18', '2025-05-17 15:45:18', 'Active'),
(10, 'Electrical Safety', 'ES-201', 'Electrical safety principles for water utility operations', 3, 45, '2025-05-17 15:45:18', '2025-05-17 15:45:18', 'Active'),
(11, 'Mechanical Maintenance', 'MM-301', 'Maintenance procedures for mechanical equipment in water systems', 5, 75, '2025-05-17 15:45:18', '2025-05-17 15:45:18', 'Active'),
(12, 'Pipeline Management', 'PM-401', 'Management and maintenance of water distribution pipelines', 6, 90, '2025-05-17 15:45:18', '2025-05-17 15:45:18', 'Active'),
(13, 'Customer Service Skills', 'CSS-101', 'Customer service best practices for water utility staff', 2, 30, '2025-05-17 15:45:18', '2025-05-17 15:45:18', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `EducationMetrics`
--
-- Creation: May 15, 2025 at 09:03 PM
-- Last update: May 15, 2025 at 09:03 PM
--

DROP TABLE IF EXISTS `EducationMetrics`;
CREATE TABLE `EducationMetrics` (
  `MetricID` int(11) NOT NULL,
  `MetricName` varchar(100) NOT NULL,
  `Acronym` varchar(20) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `Formula` text DEFAULT NULL,
  `CalculationMethod` text DEFAULT NULL,
  `InterpretationGuidelines` text DEFAULT NULL,
  `RelevantComponents` varchar(255) DEFAULT NULL,
  `DisplayOrder` int(11) DEFAULT 999,
  `IsActive` tinyint(1) DEFAULT 1,
  `Category` varchar(50) DEFAULT NULL,
  `ReferenceRange` varchar(100) DEFAULT NULL,
  `CreatedAt` timestamp NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `EducationMetrics`
--

INSERT INTO `EducationMetrics` (`MetricID`, `MetricName`, `Acronym`, `Description`, `Formula`, `CalculationMethod`, `InterpretationGuidelines`, `RelevantComponents`, `DisplayOrder`, `IsActive`, `Category`, `ReferenceRange`, `CreatedAt`, `UpdatedAt`) VALUES
(1, 'Learning Gap Indicator', 'LGI', 'Measures how well a student closed their initial knowledge gap based on pre-test and final performance.', '((FinalScore - NormalizedPretest) / (100 - NormalizedPretest)) * 100', 'Retrieve PreTest score and Final course score. Normalize PreTest to a 0-100 scale if needed (PreTest/50*100). Calculate LGI using the formula: ((FinalScore - NormalizedPretest) / (100 - NormalizedPretest)) * 100. If PreTest is 0 or null, LGI is null.', 'Higher values (closer to 100%) indicate better learning progress from initial knowledge state. Values above 80% suggest excellent knowledge acquisition. Negative values indicate regression.', 'PreTest,Final Exam', 1, 1, 'Progress', '0-100%', '2025-05-15 21:03:54', '2025-05-15 21:03:54'),
(2, 'Attendance Impact Factor', 'AIF', 'Correlation between attendance and final performance, showing how attendance influences learning outcomes.', 'Statistical correlation coefficient between attendance percentage and final score', 'Calculate the correlation coefficient between attendance percentages and final scores across all trainees in a group or course. Values range from -1 to 1.', 'Values closer to 1 indicate strong positive correlation between attendance and performance. Values near 0 suggest little impact. Negative values may indicate issues with course delivery.', 'Attendance,Total Score', 2, 1, 'Engagement', '-1 to 1', '2025-05-15 21:03:54', '2025-05-15 21:03:54'),
(3, 'Quiz Consistency Index', 'QCI', 'Measures consistency in performance across multiple assessments during the course.', '(Standard Deviation of Quiz Scores / Average Quiz Score) * 100', 'Calculate the standard deviation of all available quiz scores for a trainee. Divide by the average quiz score and multiply by 100 to get a percentage. Lower values indicate more consistent performance.', 'Lower values indicate more consistent performance. Values under 10% show excellent consistency. Values above 30% suggest inconsistent learning or engagement.', 'Quiz 1,Quiz 2,Quiz 3', 3, 1, 'Progress', '0-50%', '2025-05-15 21:03:54', '2025-05-15 21:03:54'),
(4, 'Performance Growth Rate', 'PGR', 'Measures the rate of improvement across sequential assessments over time.', '(Final Quiz Score - First Quiz Score) / Duration in Weeks', 'Subtract the first quiz score from the final quiz score, then divide by the course duration in weeks. This shows average weekly improvement in points.', 'Higher values indicate faster learning progression. Negative values suggest regression. Compare across similar courses for context.', 'Quiz 1,Final Exam,DurationWeeks', 4, 1, 'Progress', '-5 to 5 points/week', '2025-05-15 21:03:54', '2025-05-15 21:03:54'),
(5, 'Final vs Continuous Assessment Gap', 'FCAG', 'Difference between performance on final exam and continuous assessment, indicating alignment between day-to-day learning and exam preparation.', 'Final Exam Score - Average of Continuous Assessment Scores', 'Calculate the average of all quiz and participation scores during the course. Subtract this from the final exam score. Values can be positive or negative.', 'Values near zero indicate consistent performance throughout the course. Positive values suggest better performance on finals than daily work. Negative values may indicate test anxiety or inadequate exam preparation.', 'Quiz 1,Quiz 2,Quiz 3,Participation,Final Exam', 5, 1, 'Assessment', '-30 to 30 points', '2025-05-15 21:03:54', '2025-05-15 21:03:54'),
(6, 'Knowledge Retention Rate', 'KRR', 'Measures information retention over time when follow-up assessments are conducted.', '(Post-Delay Test Score / Immediate Post-Test Score) * 100', 'If a follow-up assessment is conducted weeks/months after course completion, divide that score by the original final exam score and multiply by 100 to get a percentage.', 'Higher percentages indicate better long-term retention. Values above 90% show excellent retention. Values below 70% suggest poor retention or need for refresher training.', 'Final Exam,Post-Course Assessment', 6, 0, 'Retention', '50-100%', '2025-05-15 21:03:54', '2025-05-15 21:03:54'),
(7, 'Engagement Index', 'EI', 'Quantifies trainee engagement with course materials and activities.', 'Weighted score based on attendance, participation, and activity completion', 'Calculate a weighted combination of attendance percentage (50%), participation score (30%), and completion of optional activities (20%) to create a 0-100 scale.', 'Higher scores indicate stronger engagement with learning materials. Values above 85% show strong engagement. Values below 60% suggest disengagement or potential issues.', 'Attendance,Participation', 7, 1, 'Engagement', '0-100%', '2025-05-15 21:03:54', '2025-05-15 21:03:54'),
(8, 'Peer Comparison Factor', 'PCF', 'Indicates relative performance compared to peer group in the same course.', '(Individual Score - Group Average) / Group Standard Deviation', 'Calculate the z-score by subtracting the group average total score from the individual total score, then divide by the standard deviation of the group scores.', 'Positive values indicate above-average performance. Values above 1 show performance in top 16% of class. Values below -1 indicate bottom 16% of class. Values between -1 and 1 are within typical range.', 'Total Score', 8, 1, 'Comparison', '-3 to 3', '2025-05-15 21:03:54', '2025-05-15 21:03:54'),
(9, 'Concept Mastery Rate', 'CMR', 'Percentage of key concepts or learning objectives successfully demonstrated.', '(Number of Mastered Concepts / Total Key Concepts) * 100', 'For courses with defined learning objectives or key concepts, count the number successfully demonstrated in assessments, divide by total number of concepts, and multiply by 100.', 'Higher percentages indicate broader subject mastery. Values above 80% suggest strong mastery of the subject matter. Values below 60% may indicate need for targeted remediation.', 'Quiz 1,Quiz 2,Quiz 3,Final Exam', 9, 0, 'Progress', '0-100%', '2025-05-15 21:03:54', '2025-05-15 21:03:54'),
(10, 'Learning Efficiency Ratio', 'LER', 'Learning outcomes relative to time invested, measuring efficiency of learning process.', 'Performance Improvement / Total Hours of Study', 'Divide the difference between final and initial assessment scores by the total course hours. Higher values indicate more efficient learning per hour invested.', 'Higher values suggest more efficient learning processes. Useful for comparing efficiency across different training methodologies or instructor approaches.', 'PreTest,Final Exam,TotalHours', 10, 0, 'Efficiency', '0.1-1.0 points/hour', '2025-05-15 21:03:54', '2025-05-15 21:03:54');

-- --------------------------------------------------------

--
-- Table structure for table `EmailTemplates`
--
-- Creation: May 17, 2025 at 09:41 PM
-- Last update: May 17, 2025 at 09:41 PM
--

DROP TABLE IF EXISTS `EmailTemplates`;
CREATE TABLE `EmailTemplates` (
  `TemplateID` int(11) NOT NULL,
  `TemplateName` varchar(100) NOT NULL,
  `TemplateCode` varchar(50) NOT NULL,
  `Subject` varchar(200) NOT NULL,
  `HtmlContent` text NOT NULL,
  `TextContent` text DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `CreatedAt` timestamp NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `EmailTemplates`
--

INSERT INTO `EmailTemplates` (`TemplateID`, `TemplateName`, `TemplateCode`, `Subject`, `HtmlContent`, `TextContent`, `Description`, `CreatedAt`, `UpdatedAt`) VALUES
(1, 'Welcome Email', 'welcome', 'Welcome to Water Academy', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 5px;\">\r\n  <div style=\"text-align: center; margin-bottom: 20px;\">\r\n    <img src=\"{{logo_url}}\" alt=\"Water Academy Logo\" style=\"max-width: 150px;\">\r\n  </div>\r\n  <h1 style=\"color: #0056b3; margin-bottom: 20px;\">Welcome to Water Academy!</h1>\r\n  <p>Hello {{user_name}},</p>\r\n  <p>Welcome to Water Academy! Your account has been created with the following details:</p>\r\n  <p><strong>Username:</strong> {{username}}<br>\r\n  <strong>Role:</strong> {{user_role}}</p>\r\n  <p>Please use the following temporary password to log in:</p>\r\n  <p style=\"background-color: #f5f5f5; padding: 10px; text-align: center; font-family: monospace; font-size: 18px;\">{{temp_password}}</p>\r\n  <p>For security reasons, you will be required to change your password upon first login.</p>\r\n  <p><a href=\"{{login_url}}\" style=\"display: inline-block; padding: 10px 20px; background-color: #0056b3; color: white; text-decoration: none; border-radius: 5px;\">Click here to login</a></p>\r\n  <p>If you have any questions, please contact your system administrator.</p>\r\n  <p>Thank you,<br>Water Academy Team</p>\r\n</div>', 'Welcome to Water Academy!\r\n\r\nHello {{user_name}},\r\n\r\nWelcome to Water Academy! Your account has been created with the following details:\r\n\r\nUsername: {{username}}\r\nRole: {{user_role}}\r\n\r\nPlease use the following temporary password to log in:\r\n{{temp_password}}\r\n\r\nFor security reasons, you will be required to change your password upon first login.\r\n\r\nLogin URL: {{login_url}}\r\n\r\nIf you have any questions, please contact your system administrator.\r\n\r\nThank you,\r\nWater Academy Team', 'Sent to new users when their account is created', '2025-05-17 21:41:54', '2025-05-17 21:41:54'),
(2, 'Password Reset', 'password_reset', 'Water Academy - Password Reset', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 5px;\">\r\n  <div style=\"text-align: center; margin-bottom: 20px;\">\r\n    <img src=\"{{logo_url}}\" alt=\"Water Academy Logo\" style=\"max-width: 150px;\">\r\n  </div>\r\n  <h1 style=\"color: #0056b3; margin-bottom: 20px;\">Password Reset</h1>\r\n  <p>Hello {{user_name}},</p>\r\n  <p>We received a request to reset your password. If you didn\'t make this request, you can safely ignore this email.</p>\r\n  <p>To reset your password, click the button below. This link will expire in 1 hour.</p>\r\n  <p style=\"text-align: center;\">\r\n    <a href=\"{{reset_url}}\" style=\"display: inline-block; padding: 10px 20px; background-color: #0056b3; color: white; text-decoration: none; border-radius: 5px;\">Reset Password</a>\r\n  </p>\r\n  <p>If the button doesn\'t work, you can copy and paste this link into your browser:</p>\r\n  <p style=\"background-color: #f5f5f5; padding: 10px; word-break: break-all;\">{{reset_url}}</p>\r\n  <p>This link will expire on {{expiry_time}}.</p>\r\n  <p>Thank you,<br>Water Academy Team</p>\r\n</div>', 'Password Reset\r\n\r\nHello {{user_name}},\r\n\r\nWe received a request to reset your password. If you didn\'t make this request, you can safely ignore this email.\r\n\r\nTo reset your password, visit this link (expires in 1 hour):\r\n{{reset_url}}\r\n\r\nThis link will expire on {{expiry_time}}.\r\n\r\nThank you,\r\nWater Academy Team', 'Sent when a user requests a password reset', '2025-05-17 21:41:54', '2025-05-17 21:41:54'),
(3, 'Course Assignment', 'course_assignment', 'Course Assignment Notification', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 5px;\">\r\n  <div style=\"text-align: center; margin-bottom: 20px;\">\r\n    <img src=\"{{logo_url}}\" alt=\"Water Academy Logo\" style=\"max-width: 150px;\">\r\n  </div>\r\n  <h1 style=\"color: #0056b3; margin-bottom: 20px;\">Course Assignment</h1>\r\n  <p>Hello {{instructor_name}},</p>\r\n  <p>You have been assigned as the instructor for the following course:</p>\r\n  <div style=\"background-color: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0;\">\r\n    <p><strong>Course:</strong> {{course_name}}</p>\r\n    <p><strong>Group:</strong> {{group_name}}</p>\r\n    <p><strong>Start Date:</strong> {{start_date}}</p>\r\n    <p><strong>End Date:</strong> {{end_date}}</p>\r\n    <p><strong>Number of Trainees:</strong> {{trainee_count}}</p>\r\n  </div>\r\n  <p>Please log in to the Water Academy system to view your course details and prepare your materials.</p>\r\n  <p style=\"text-align: center;\">\r\n    <a href=\"{{course_url}}\" style=\"display: inline-block; padding: 10px 20px; background-color: #0056b3; color: white; text-decoration: none; border-radius: 5px;\">View Course Details</a>\r\n  </p>\r\n  <p>If you have any questions, please contact the group coordinator.</p>\r\n  <p>Thank you,<br>Water Academy Team</p>\r\n</div>', 'Course Assignment\r\n\r\nHello {{instructor_name}},\r\n\r\nYou have been assigned as the instructor for the following course:\r\n\r\nCourse: {{course_name}}\r\nGroup: {{group_name}}\r\nStart Date: {{start_date}}\r\nEnd Date: {{end_date}}\r\nNumber of Trainees: {{trainee_count}}\r\n\r\nPlease log in to the Water Academy system to view your course details and prepare your materials:\r\n{{course_url}}\r\n\r\nIf you have any questions, please contact the group coordinator.\r\n\r\nThank you,\r\nWater Academy Team', 'Sent to instructors when they are assigned to a course', '2025-05-17 21:41:54', '2025-05-17 21:41:54'),
(4, 'Grade Submission Reminder', 'grade_reminder', 'Grade Submission Reminder', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 5px;\">\r\n  <div style=\"text-align: center; margin-bottom: 20px;\">\r\n    <img src=\"{{logo_url}}\" alt=\"Water Academy Logo\" style=\"max-width: 150px;\">\r\n  </div>\r\n  <h1 style=\"color: #0056b3; margin-bottom: 20px;\">Grade Submission Reminder</h1>\r\n  <p>Hello {{instructor_name}},</p>\r\n  <p>This is a friendly reminder that grade submissions for the following course(s) are due soon:</p>\r\n  <ul style=\"background-color: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0;\">\r\n    {{course_list}}\r\n  </ul>\r\n  <p>Please log in to the Water Academy system to submit your grades before the deadline.</p>\r\n  <p style=\"text-align: center;\">\r\n    <a href=\"{{grades_url}}\" style=\"display: inline-block; padding: 10px 20px; background-color: #0056b3; color: white; text-decoration: none; border-radius: 5px;\">Submit Grades</a>\r\n  </p>\r\n  <p>If you have already submitted your grades, please disregard this message.</p>\r\n  <p>Thank you,<br>Water Academy Team</p>\r\n</div>', 'Grade Submission Reminder\r\n\r\nHello {{instructor_name}},\r\n\r\nThis is a friendly reminder that grade submissions for the following course(s) are due soon:\r\n\r\n{{course_list}}\r\n\r\nPlease log in to the Water Academy system to submit your grades before the deadline:\r\n{{grades_url}}\r\n\r\nIf you have already submitted your grades, please disregard this message.\r\n\r\nThank you,\r\nWater Academy Team', 'Sent to instructors as a reminder to submit grades', '2025-05-17 21:41:54', '2025-05-17 21:41:54'),
(5, 'Report Email', 'report_email', 'Water Academy Report', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 5px;\">\r\n  <div style=\"text-align: center; margin-bottom: 20px;\">\r\n    <img src=\"{{logo_url}}\" alt=\"Water Academy Logo\" style=\"max-width: 150px;\">\r\n  </div>\r\n  <h1 style=\"color: #0056b3; margin-bottom: 20px;\">Water Academy Report</h1>\r\n  <p>Hello,</p>\r\n  <p>{{message}}</p>\r\n  <p>Please find the attached report from Water Academy.</p>\r\n  <p>If you have any questions about this report, please contact the sender.</p>\r\n  <p>Thank you,<br>Water Academy Team</p>\r\n</div>', 'Water Academy Report\r\n\r\nHello,\r\n\r\n{{message}}\r\n\r\nPlease find the attached report from Water Academy.\r\n\r\nIf you have any questions about this report, please contact the sender.\r\n\r\nThank you,\r\nWater Academy Team', 'Used when sending reports via email', '2025-05-17 21:41:54', '2025-05-17 21:41:54');

-- --------------------------------------------------------

--
-- Table structure for table `Enrollments`
--
-- Creation: May 15, 2025 at 09:03 PM
-- Last update: May 17, 2025 at 04:03 PM
--

DROP TABLE IF EXISTS `Enrollments`;
CREATE TABLE `Enrollments` (
  `EnrollmentID` int(11) NOT NULL,
  `TID` int(11) NOT NULL,
  `GroupCourseID` int(11) NOT NULL,
  `EnrollmentDate` date DEFAULT NULL,
  `Status` enum('Enrolled','Completed','Dropped','InProgress') DEFAULT 'Enrolled',
  `CompletionDate` date DEFAULT NULL,
  `FinalScore` decimal(5,2) DEFAULT NULL,
  `CertificatePath` varchar(255) DEFAULT NULL,
  `CreatedAt` timestamp NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Enrollments`
--

INSERT INTO `Enrollments` (`EnrollmentID`, `TID`, `GroupCourseID`, `EnrollmentDate`, `Status`, `CompletionDate`, `FinalScore`, `CertificatePath`, `CreatedAt`, `UpdatedAt`) VALUES
(1, 1, 1, '2024-02-20', 'Enrolled', NULL, NULL, NULL, '2025-05-16 01:17:25', '2025-05-16 01:17:25'),
(2, 2, 1, '2024-02-20', 'Enrolled', NULL, NULL, NULL, '2025-05-16 01:17:25', '2025-05-16 01:17:25'),
(3, 3, 2, '2024-03-15', 'Enrolled', NULL, NULL, NULL, '2025-05-16 01:17:25', '2025-05-16 01:17:25'),
(4, 4, 3, '2023-08-25', 'Completed', '2023-10-31', 265.00, NULL, '2025-05-16 01:17:25', '2025-05-17 16:03:47'),
(5, 9, 4, '2025-05-17', 'Completed', '2024-02-11', 88.58, NULL, '2025-05-17 15:58:08', '2025-05-17 16:03:47'),
(6, 10, 4, '2025-05-17', 'Completed', '2024-02-11', 84.80, NULL, '2025-05-17 15:58:08', '2025-05-17 16:03:47'),
(7, 11, 4, '2025-05-17', 'Completed', '2024-02-11', 91.46, NULL, '2025-05-17 15:58:08', '2025-05-17 16:03:47'),
(8, 12, 4, '2025-05-17', 'Completed', '2024-02-11', 88.55, NULL, '2025-05-17 15:58:08', '2025-05-17 16:03:47'),
(9, 13, 4, '2025-05-17', 'Completed', '2024-02-11', 79.74, NULL, '2025-05-17 15:58:08', '2025-05-17 16:03:47'),
(10, 9, 5, '2025-05-17', 'Completed', '2024-03-11', 89.27, NULL, '2025-05-17 15:58:08', '2025-05-17 16:03:47'),
(11, 10, 5, '2025-05-17', 'Completed', '2024-03-11', 80.47, NULL, '2025-05-17 15:58:08', '2025-05-17 16:03:47'),
(12, 11, 5, '2025-05-17', 'Completed', '2024-03-11', 79.23, NULL, '2025-05-17 15:58:08', '2025-05-17 16:03:47'),
(13, 12, 5, '2025-05-17', 'Completed', '2024-03-11', 78.01, NULL, '2025-05-17 15:58:08', '2025-05-17 16:03:47'),
(14, 13, 5, '2025-05-17', 'Completed', '2024-03-11', 87.43, NULL, '2025-05-17 15:58:08', '2025-05-17 16:03:47'),
(15, 9, 6, '2025-05-17', 'Completed', '2024-03-25', 94.04, NULL, '2025-05-17 15:58:08', '2025-05-17 16:03:47'),
(16, 10, 6, '2025-05-17', 'Completed', '2024-03-25', 81.33, NULL, '2025-05-17 15:58:08', '2025-05-17 16:03:47'),
(17, 11, 6, '2025-05-17', 'Completed', '2024-03-25', 89.14, NULL, '2025-05-17 15:58:08', '2025-05-17 16:03:47'),
(18, 12, 6, '2025-05-17', 'Completed', '2024-03-25', 84.61, NULL, '2025-05-17 15:58:08', '2025-05-17 16:03:47'),
(19, 13, 6, '2025-05-17', 'Completed', '2024-03-25', 87.54, NULL, '2025-05-17 15:58:08', '2025-05-17 16:03:47'),
(20, 14, 7, '2025-05-17', 'Completed', '2025-02-21', 79.01, NULL, '2025-05-17 15:58:08', '2025-05-17 16:03:47'),
(21, 15, 7, '2025-05-17', 'Completed', '2025-02-21', 87.61, NULL, '2025-05-17 15:58:08', '2025-05-17 16:03:47'),
(22, 16, 7, '2025-05-17', 'Completed', '2025-02-21', 85.52, NULL, '2025-05-17 15:58:08', '2025-05-17 16:03:47'),
(23, 17, 7, '2025-05-17', 'Completed', '2025-02-21', 82.57, NULL, '2025-05-17 15:58:08', '2025-05-17 16:03:47'),
(24, 18, 7, '2025-05-17', 'Completed', '2025-02-21', 89.51, NULL, '2025-05-17 15:58:08', '2025-05-17 16:03:47'),
(25, 14, 8, '2025-05-17', 'Enrolled', NULL, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(26, 15, 8, '2025-05-17', 'Enrolled', NULL, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(27, 16, 8, '2025-05-17', 'Enrolled', NULL, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(28, 17, 8, '2025-05-17', 'Enrolled', NULL, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(29, 18, 8, '2025-05-17', 'Enrolled', NULL, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(35, 19, 9, '2025-05-17', 'Enrolled', NULL, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(36, 20, 9, '2025-05-17', 'Enrolled', NULL, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(37, 21, 9, '2025-05-17', 'Enrolled', NULL, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(38, 22, 9, '2025-05-17', 'Enrolled', NULL, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(39, 23, 9, '2025-05-17', 'Enrolled', NULL, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(40, 19, 10, '2025-05-17', 'Enrolled', NULL, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(41, 20, 10, '2025-05-17', 'Enrolled', NULL, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(42, 21, 10, '2025-05-17', 'Enrolled', NULL, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(43, 22, 10, '2025-05-17', 'Enrolled', NULL, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(44, 23, 10, '2025-05-17', 'Enrolled', NULL, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(50, 24, 11, '2025-05-17', 'Enrolled', NULL, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(51, 25, 11, '2025-05-17', 'Enrolled', NULL, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(52, 26, 11, '2025-05-17', 'Enrolled', NULL, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(53, 27, 11, '2025-05-17', 'Enrolled', NULL, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(54, 28, 11, '2025-05-17', 'Enrolled', NULL, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(55, 24, 12, '2025-05-17', 'Enrolled', NULL, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(56, 25, 12, '2025-05-17', 'Enrolled', NULL, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(57, 26, 12, '2025-05-17', 'Enrolled', NULL, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(58, 27, 12, '2025-05-17', 'Enrolled', NULL, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(59, 28, 12, '2025-05-17', 'Enrolled', NULL, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(65, 29, 13, '2025-05-17', 'Enrolled', NULL, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(66, 30, 13, '2025-05-17', 'Enrolled', NULL, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(67, 31, 13, '2025-05-17', 'Enrolled', NULL, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(68, 32, 13, '2025-05-17', 'Enrolled', NULL, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(69, 33, 13, '2025-05-17', 'Enrolled', NULL, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08');

-- --------------------------------------------------------

--
-- Table structure for table `GradeComponents`
--
-- Creation: May 15, 2025 at 09:03 PM
-- Last update: May 16, 2025 at 01:21 AM
--

DROP TABLE IF EXISTS `GradeComponents`;
CREATE TABLE `GradeComponents` (
  `ComponentID` int(11) NOT NULL,
  `ComponentName` varchar(100) NOT NULL,
  `MaxPoints` decimal(5,2) NOT NULL,
  `Description` text DEFAULT NULL,
  `IsDefault` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `GradeComponents`
--

INSERT INTO `GradeComponents` (`ComponentID`, `ComponentName`, `MaxPoints`, `Description`, `IsDefault`) VALUES
(1, 'PreTest', 50.00, 'Initial knowledge assessment. Does not add to course total.', 1),
(2, 'Attendance', 10.00, 'Attendance score, derived from percentage.', 1),
(3, 'Participation', 10.00, 'Class participation and engagement.', 1),
(4, 'Quiz 1', 30.00, 'First intra-course quiz.', 0),
(5, 'Quiz 2', 30.00, 'Second intra-course quiz (optional).', 0),
(6, 'Quiz 3', 30.00, 'Third intra-course quiz (optional).', 0),
(7, 'Final Exam', 50.00, 'Final comprehensive examination.', 1);

-- --------------------------------------------------------

--
-- Table structure for table `GroupCourses`
--
-- Creation: May 15, 2025 at 09:03 PM
-- Last update: May 17, 2025 at 03:45 PM
--

DROP TABLE IF EXISTS `GroupCourses`;
CREATE TABLE `GroupCourses` (
  `ID` int(11) NOT NULL,
  `GroupID` int(11) NOT NULL,
  `CourseID` int(11) NOT NULL,
  `InstructorID` int(11) DEFAULT NULL,
  `StartDate` date DEFAULT NULL,
  `EndDate` date DEFAULT NULL,
  `Location` varchar(255) DEFAULT NULL,
  `ScheduleDetails` text DEFAULT NULL,
  `Status` varchar(50) DEFAULT 'Scheduled',
  `CreatedAt` timestamp NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `GroupCourses`
--

INSERT INTO `GroupCourses` (`ID`, `GroupID`, `CourseID`, `InstructorID`, `StartDate`, `EndDate`, `Location`, `ScheduleDetails`, `Status`, `CreatedAt`, `UpdatedAt`) VALUES
(1, 1, 1, 3, '2024-03-01', '2024-03-31', 'Room 101', 'Mon-Wed-Fri 9AM-12PM', 'In Progress', '2025-05-16 01:10:50', '2025-05-16 01:10:50'),
(2, 2, 2, 4, '2024-04-01', '2024-05-15', 'Room 102', 'Tue-Thu 1PM-4PM', 'Scheduled', '2025-05-16 01:10:50', '2025-05-16 01:10:50'),
(3, 3, 3, 3, '2023-09-01', '2023-10-31', 'Room 103', 'Mon-Fri 10AM-1PM', 'Completed', '2025-05-16 01:10:50', '2025-05-16 01:10:50'),
(4, 4, 6, 17, '2024-01-15', '2024-02-11', 'Main Campus - Lab 1', 'Mon, Wed, Thu 9AM-12PM', 'Completed', '2025-05-17 15:45:18', '2025-05-17 15:45:18'),
(5, 4, 8, 18, '2024-02-12', '2024-03-11', 'Main Campus - Workshop 2', 'Mon, Wed, Thu 9AM-12PM', 'Completed', '2025-05-17 15:45:18', '2025-05-17 15:45:18'),
(6, 4, 9, 20, '2024-03-12', '2024-03-25', 'Main Campus - Room 103', 'Mon, Wed, Thu 9AM-12PM', 'Completed', '2025-05-17 15:45:18', '2025-05-17 15:45:18'),
(7, 5, 10, 19, '2025-02-01', '2025-02-21', 'SEC Training Center - Room A', 'Sun, Tue, Wed 1PM-4PM', 'Completed', '2025-05-17 15:45:18', '2025-05-17 15:45:18'),
(8, 5, 11, 18, '2025-02-24', '2025-03-28', 'SEC Training Center - Workshop B', 'Sun, Tue, Wed 1PM-4PM', 'In Progress', '2025-05-17 15:45:18', '2025-05-17 15:45:18'),
(9, 6, 12, 18, '2025-03-10', '2025-04-18', 'SWCC Facility - Training Room 1', 'Mon, Tue, Thu 8AM-11AM', 'Scheduled', '2025-05-17 15:45:18', '2025-05-17 15:45:18'),
(10, 6, 7, 17, '2025-04-21', '2025-05-12', 'SWCC Facility - Lab 2', 'Mon, Tue, Thu 8AM-11AM', 'Scheduled', '2025-05-17 15:45:18', '2025-05-17 15:45:18'),
(11, 7, 13, 21, '2025-04-20', '2025-05-03', 'MRFQ HQ - Conference Room', 'Sun, Mon, Wed 10AM-1PM', 'Scheduled', '2025-05-17 15:45:18', '2025-05-17 15:45:18'),
(12, 7, 7, 17, '2025-05-04', '2025-05-24', 'MRFQ HQ - Lab Room', 'Sun, Mon, Wed 10AM-1PM', 'Scheduled', '2025-05-17 15:45:18', '2025-05-17 15:45:18'),
(13, 8, 8, 18, '2025-05-15', '2025-06-11', 'NWC Facility - Workshop A', 'Sun, Tue, Thu 9AM-12PM', 'Scheduled', '2025-05-17 15:45:18', '2025-05-17 15:45:18'),
(14, 9, 6, 17, '2025-06-01', '2025-06-28', 'SEC Training Center - Lab C', 'Mon, Wed, Thu 1PM-4PM', 'Scheduled', '2025-05-17 15:45:18', '2025-05-17 15:45:18'),
(15, 10, 7, 17, '2025-01-05', '2025-01-25', 'SWCC Advanced Facility - Lab 3', 'Sun, Tue, Wed 8AM-11AM', 'In Progress', '2025-05-17 15:45:18', '2025-05-17 15:45:18');

-- --------------------------------------------------------

--
-- Table structure for table `Groups`
--
-- Creation: May 15, 2025 at 09:01 PM
-- Last update: May 17, 2025 at 03:45 PM
--

DROP TABLE IF EXISTS `Groups`;
CREATE TABLE `Groups` (
  `GroupID` int(11) NOT NULL,
  `GroupName` varchar(50) NOT NULL,
  `Program` varchar(100) DEFAULT NULL,
  `Duration` int(11) DEFAULT NULL,
  `Semesters` int(11) DEFAULT NULL,
  `StartDate` date DEFAULT NULL,
  `EndDate` date DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `Status` varchar(50) DEFAULT 'Active',
  `Room` varchar(20) DEFAULT NULL,
  `CoordinatorID` int(11) DEFAULT NULL,
  `CreatedAt` timestamp NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Groups`
--

INSERT INTO `Groups` (`GroupID`, `GroupName`, `Program`, `Duration`, `Semesters`, `StartDate`, `EndDate`, `Description`, `Status`, `Room`, `CoordinatorID`, `CreatedAt`, `UpdatedAt`) VALUES
(1, 'Water Chemistry Batch A 2024', NULL, NULL, NULL, '2024-03-01', '2024-03-31', 'First batch for Water Chemistry in 2024.', 'Active', NULL, 5, '2025-05-16 01:00:34', '2025-05-16 01:00:34'),
(2, 'Advanced Hydrology Batch A 2024', NULL, NULL, NULL, '2024-04-01', '2024-05-15', 'First batch for Advanced Hydrology in 2024.', 'Planned', NULL, 5, '2025-05-16 01:00:34', '2025-05-16 01:00:34'),
(3, 'Wastewater Treatment Batch B 2023', NULL, NULL, NULL, '2023-09-01', '2023-10-31', 'Second batch for WWTP in 2023 (completed).', 'Completed', NULL, 6, '2025-05-16 01:00:34', '2025-05-16 01:00:34'),
(4, 'NWC-WQT-01', 'Water Quality Training', 12, NULL, '2024-01-15', '2024-04-15', 'First cohort of water quality trainees', 'Completed', 'A101', 14, '2025-05-17 15:45:18', '2025-05-17 15:45:18'),
(5, 'SEC-MNT-01', 'Maintenance Fundamentals', 12, NULL, '2025-02-01', '2025-05-01', 'Maintenance training for SEC staff', 'Active', 'B202', 15, '2025-05-17 15:45:18', '2025-05-17 15:45:18'),
(6, 'SWCC-DST-01', 'Distribution Systems', 12, NULL, '2025-03-10', '2025-06-10', 'Distribution systems training for SWCC', 'Scheduled', 'C303', 16, '2025-05-17 15:45:18', '2025-05-17 15:45:18'),
(7, 'MRFQ-UTL-01', 'Utility Management', 12, NULL, '2025-04-20', '2025-07-20', 'Utility management training for MRFQ', 'Scheduled', 'D404', 14, '2025-05-17 15:45:18', '2025-05-17 15:45:18'),
(8, 'NWC-OPS-02', 'Operations Training', 8, NULL, '2025-05-15', '2025-07-10', 'Operations training for NWC staff', 'Scheduled', 'A102', 15, '2025-05-17 15:45:18', '2025-05-17 15:45:18'),
(9, 'SEC-ENG-03', 'Engineering Basics', 10, NULL, '2025-06-01', '2025-08-10', 'Engineering fundamentals for SEC staff', 'Scheduled', 'B203', 16, '2025-05-17 15:45:18', '2025-05-17 15:45:18'),
(10, 'SWCC-ADV-02', 'Advanced Water Treatment', 14, NULL, '2025-01-05', '2025-04-12', 'Advanced treatment techniques for SWCC', 'Active', 'C304', 14, '2025-05-17 15:45:18', '2025-05-17 15:45:18');

-- --------------------------------------------------------

--
-- Table structure for table `Permissions`
--
-- Creation: May 23, 2025 at 02:33 PM
--

DROP TABLE IF EXISTS `Permissions`;
CREATE TABLE `Permissions` (
  `PermissionID` int(11) NOT NULL,
  `PermissionName` varchar(100) NOT NULL,
  `Description` text DEFAULT NULL,
  `Category` varchar(50) DEFAULT NULL,
  `CreatedAt` timestamp NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `RolePermissions`
--
-- Creation: May 23, 2025 at 02:34 PM
--

DROP TABLE IF EXISTS `RolePermissions`;
CREATE TABLE `RolePermissions` (
  `RoleID` int(11) NOT NULL,
  `PermissionID` int(11) NOT NULL,
  `CreatedAt` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Roles`
--
-- Creation: May 23, 2025 at 02:33 PM
--

DROP TABLE IF EXISTS `Roles`;
CREATE TABLE `Roles` (
  `RoleID` int(11) NOT NULL,
  `RoleName` varchar(50) NOT NULL,
  `Description` text DEFAULT NULL,
  `CreatedAt` timestamp NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `TraineeGrades`
--
-- Creation: May 21, 2025 at 12:26 AM
-- Last update: May 21, 2025 at 12:26 AM
--

DROP TABLE IF EXISTS `TraineeGrades`;
CREATE TABLE `TraineeGrades` (
  `GradeID` int(11) NOT NULL,
  `TID` int(11) NOT NULL,
  `GroupCourseID` int(11) NOT NULL,
  `ComponentID` int(11) NOT NULL,
  `Score` decimal(5,2) DEFAULT NULL,
  `GradeDate` date DEFAULT NULL,
  `Comments` text DEFAULT NULL,
  `RecordedBy` int(11) DEFAULT NULL,
  `PositiveFeedback` text DEFAULT NULL,
  `AreasToImprove` text DEFAULT NULL,
  `CreatedAt` timestamp NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `PreTest` decimal(5,2) DEFAULT 0.00,
  `AttGrade` decimal(5,2) DEFAULT 0.00,
  `Participation` decimal(5,2) DEFAULT 0.00,
  `Quiz1` decimal(5,2) DEFAULT 0.00,
  `Quiz2` decimal(5,2) DEFAULT 0.00,
  `Quiz3` decimal(5,2) DEFAULT 0.00,
  `QuizAv` decimal(5,2) DEFAULT 0.00,
  `Final` decimal(5,2) DEFAULT 0.00,
  `Total` decimal(5,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `TraineeGrades`
--

INSERT INTO `TraineeGrades` (`GradeID`, `TID`, `GroupCourseID`, `ComponentID`, `Score`, `GradeDate`, `Comments`, `RecordedBy`, `PositiveFeedback`, `AreasToImprove`, `CreatedAt`, `UpdatedAt`, `PreTest`, `AttGrade`, `Participation`, `Quiz1`, `Quiz2`, `Quiz3`, `QuizAv`, `Final`, `Total`) VALUES
(1, 1, 1, 4, 85.00, '2024-03-10', 'Good understanding on Quiz 1.', 3, NULL, NULL, '2025-05-16 01:25:10', '2025-05-16 01:25:10', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(2, 1, 1, 7, 78.00, '2024-03-20', 'Needs more detail in Final Exam answers.', 3, NULL, NULL, '2025-05-16 01:25:10', '2025-05-16 01:25:10', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(3, 2, 1, 4, 92.00, '2024-03-10', 'Excellent work on Quiz 1!', 3, NULL, NULL, '2025-05-16 01:25:10', '2025-05-16 01:25:10', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(4, 4, 3, 1, 30.00, '2023-09-02', 'Pre-test score.', 3, NULL, NULL, '2025-05-16 01:25:10', '2025-05-16 01:25:10', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(5, 4, 3, 3, 90.00, '2023-10-30', 'Very active (Participation).', 3, NULL, NULL, '2025-05-16 01:25:10', '2025-05-16 01:25:10', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(6, 4, 3, 4, 85.00, '2023-10-30', 'Good score on Quiz 1.', 3, NULL, NULL, '2025-05-16 01:25:10', '2025-05-16 01:25:10', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(7, 4, 3, 7, 90.00, '2023-10-30', 'Strong final exam.', 3, NULL, NULL, '2025-05-16 01:25:10', '2025-05-16 01:25:10', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(8, 9, 4, 1, 35.44, '2024-01-15', NULL, 17, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(9, 10, 4, 1, 16.23, '2024-01-15', NULL, 17, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(10, 11, 4, 1, 24.81, '2024-01-15', NULL, 17, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(11, 12, 4, 1, 35.37, '2024-01-15', NULL, 17, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(12, 13, 4, 1, 32.41, '2024-01-15', NULL, 17, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(15, 9, 4, 2, 8.61, '2025-05-17', NULL, 4, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(16, 10, 4, 2, 8.91, '2025-05-17', NULL, 20, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(17, 11, 4, 2, 8.83, '2025-05-17', NULL, 17, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(18, 12, 4, 2, 8.10, '2025-05-17', NULL, 5, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(19, 13, 4, 2, 9.00, '2025-05-17', NULL, 5, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(22, 9, 4, 3, 7.59, '2024-02-11', NULL, 17, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(23, 10, 4, 3, 9.24, '2024-02-11', NULL, 17, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(24, 11, 4, 3, 7.44, '2024-02-11', NULL, 17, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(25, 12, 4, 3, 8.47, '2024-02-11', NULL, 17, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(26, 13, 4, 3, 7.04, '2024-02-11', NULL, 17, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(29, 9, 4, 4, 25.89, '2024-01-25', NULL, 17, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(30, 10, 4, 4, 29.07, '2024-01-25', NULL, 17, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(31, 11, 4, 4, 27.68, '2024-01-25', NULL, 17, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(32, 12, 4, 4, 21.19, '2024-01-25', NULL, 17, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(33, 13, 4, 4, 22.90, '2024-01-25', NULL, 17, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(36, 9, 4, 5, 20.94, '2024-02-04', NULL, 17, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(37, 10, 4, 5, 25.98, '2024-02-04', NULL, 17, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(38, 11, 4, 5, 27.09, '2024-02-04', NULL, 17, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(39, 12, 4, 5, 27.53, '2024-02-04', NULL, 17, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(40, 13, 4, 5, 26.38, '2024-02-04', NULL, 17, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(43, 9, 4, 7, 48.96, '2024-02-11', NULL, 17, 'Excellent understanding of core concepts. Good practical application.', 'Could improve on practical application of concepts.', '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(44, 10, 4, 7, 39.12, '2024-02-11', NULL, 17, 'Excellent understanding of core concepts. Good practical application.', 'Could improve on practical application of concepts.', '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(45, 11, 4, 7, 47.80, '2024-02-11', NULL, 17, 'Excellent understanding of core concepts. Good practical application.', 'Needs more practice with complex problem scenarios.', '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(46, 12, 4, 7, 47.62, '2024-02-11', NULL, 17, 'Excellent understanding of core concepts. Good practical application.', 'Needs more practice with complex problem scenarios.', '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(47, 13, 4, 7, 39.06, '2024-02-11', NULL, 17, 'Excellent understanding of core concepts. Good practical application.', 'Could improve on practical application of concepts.', '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(50, 9, 5, 1, 14.81, '2024-02-12', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(51, 10, 5, 1, 15.01, '2024-02-12', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(52, 11, 5, 1, 20.64, '2024-02-12', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(53, 12, 5, 1, 18.16, '2024-02-12', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(54, 13, 5, 1, 18.88, '2024-02-12', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(57, 9, 5, 2, 8.30, '2025-05-17', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(58, 10, 5, 2, 8.99, '2025-05-17', NULL, 20, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(59, 11, 5, 2, 8.11, '2025-05-17', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(60, 12, 5, 2, 9.30, '2025-05-17', NULL, 19, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(61, 13, 5, 2, 9.90, '2025-05-17', NULL, 4, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(64, 9, 5, 3, 8.99, '2024-03-11', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(65, 10, 5, 3, 8.30, '2024-03-11', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(66, 11, 5, 3, 7.51, '2024-03-11', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(67, 12, 5, 3, 8.67, '2024-03-11', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(68, 13, 5, 3, 7.80, '2024-03-11', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(71, 9, 5, 4, 26.64, '2024-02-19', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(72, 10, 5, 4, 25.21, '2024-02-19', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(73, 11, 5, 4, 26.13, '2024-02-19', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(74, 12, 5, 4, 25.01, '2024-02-19', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(75, 13, 5, 4, 26.65, '2024-02-19', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(78, 9, 5, 5, 28.23, '2024-02-26', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(79, 10, 5, 5, 21.20, '2024-02-26', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(80, 11, 5, 5, 21.32, '2024-02-26', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(81, 12, 5, 5, 23.01, '2024-02-26', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(82, 13, 5, 5, 21.08, '2024-02-26', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(85, 9, 5, 7, 44.54, '2024-03-11', NULL, 18, 'Excellent practical skills with pump systems. Good troubleshooting abilities.', 'Needs more practice with advanced troubleshooting scenarios.', '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(86, 10, 5, 7, 39.97, '2024-03-11', NULL, 18, 'Excellent practical skills with pump systems. Good troubleshooting abilities.', 'Could improve on efficiency optimization techniques.', '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(87, 11, 5, 7, 39.88, '2024-03-11', NULL, 18, 'Excellent practical skills with pump systems. Good troubleshooting abilities.', 'Could improve on efficiency optimization techniques.', '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(88, 12, 5, 7, 36.03, '2024-03-11', NULL, 18, 'Excellent practical skills with pump systems. Good troubleshooting abilities.', 'Needs more practice with advanced troubleshooting scenarios.', '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(89, 13, 5, 7, 45.86, '2024-03-11', NULL, 18, 'Strong understanding of pump mechanics and operational principles.', 'Could improve on efficiency optimization techniques.', '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(92, 9, 6, 1, 29.97, '2024-03-12', NULL, 20, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(93, 10, 6, 1, 38.98, '2024-03-12', NULL, 20, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(94, 11, 6, 1, 34.95, '2024-03-12', NULL, 20, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(95, 12, 6, 1, 17.84, '2024-03-12', NULL, 20, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(96, 13, 6, 1, 34.35, '2024-03-12', NULL, 20, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(99, 9, 6, 2, 8.30, '2025-05-17', NULL, 4, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(100, 10, 6, 2, 9.75, '2025-05-17', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(101, 11, 6, 2, 9.46, '2025-05-17', NULL, 20, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(102, 12, 6, 2, 8.24, '2025-05-17', NULL, 4, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(103, 13, 6, 2, 8.87, '2025-05-17', NULL, 4, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(106, 9, 6, 3, 7.82, '2024-03-25', NULL, 20, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(107, 10, 6, 3, 9.81, '2024-03-25', NULL, 20, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(108, 11, 6, 3, 9.58, '2024-03-25', NULL, 20, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(109, 12, 6, 3, 8.48, '2024-03-25', NULL, 20, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(110, 13, 6, 3, 9.67, '2024-03-25', NULL, 20, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(113, 9, 6, 4, 29.62, '2024-03-17', NULL, 20, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(114, 10, 6, 4, 21.45, '2024-03-17', NULL, 20, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(115, 11, 6, 4, 28.36, '2024-03-17', NULL, 20, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(116, 12, 6, 4, 27.47, '2024-03-17', NULL, 20, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(117, 13, 6, 4, 22.25, '2024-03-17', NULL, 20, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(120, 9, 6, 7, 48.30, '2024-03-25', NULL, 20, 'Excellent understanding of safety protocols. Good hazard identification skills.', 'Needs more practice with emergency response simulations.', '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(121, 10, 6, 7, 40.32, '2024-03-25', NULL, 20, 'Strong knowledge of emergency procedures and preventive measures.', 'Could improve on risk assessment documentation.', '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(122, 11, 6, 7, 41.74, '2024-03-25', NULL, 20, 'Strong knowledge of emergency procedures and preventive measures.', 'Could improve on risk assessment documentation.', '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(123, 12, 6, 7, 40.42, '2024-03-25', NULL, 20, 'Excellent understanding of safety protocols. Good hazard identification skills.', 'Could improve on risk assessment documentation.', '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(124, 13, 6, 7, 46.75, '2024-03-25', NULL, 20, 'Excellent understanding of safety protocols. Good hazard identification skills.', 'Needs more practice with emergency response simulations.', '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(127, 14, 7, 1, 34.17, '2025-02-01', NULL, 19, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(128, 15, 7, 1, 22.50, '2025-02-01', NULL, 19, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(129, 16, 7, 1, 30.01, '2025-02-01', NULL, 19, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(130, 17, 7, 1, 12.53, '2025-02-01', NULL, 19, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(131, 18, 7, 1, 22.64, '2025-02-01', NULL, 19, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(134, 14, 7, 2, 9.30, '2025-05-17', NULL, 20, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(135, 15, 7, 2, 8.17, '2025-05-17', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(136, 16, 7, 2, 8.07, '2025-05-17', NULL, 17, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(137, 17, 7, 2, 8.26, '2025-05-17', NULL, 20, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(138, 18, 7, 2, 8.82, '2025-05-17', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(141, 14, 7, 3, 9.56, '2025-02-21', NULL, 19, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(142, 15, 7, 3, 7.01, '2025-02-21', NULL, 19, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(143, 16, 7, 3, 8.37, '2025-02-21', NULL, 19, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(144, 17, 7, 3, 7.82, '2025-02-21', NULL, 19, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(145, 18, 7, 3, 7.01, '2025-02-21', NULL, 19, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(148, 14, 7, 4, 21.90, '2025-02-08', NULL, 19, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(149, 15, 7, 4, 29.42, '2025-02-08', NULL, 19, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(150, 16, 7, 4, 21.41, '2025-02-08', NULL, 19, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(151, 17, 7, 4, 28.79, '2025-02-08', NULL, 19, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(152, 18, 7, 4, 29.71, '2025-02-08', NULL, 19, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(155, 14, 7, 7, 38.25, '2025-02-21', NULL, 19, 'Strong knowledge of safety regulations and hazard prevention.', 'Needs more practice with safety documentation procedures.', '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(156, 15, 7, 7, 43.01, '2025-02-21', NULL, 19, 'Strong knowledge of safety regulations and hazard prevention.', 'Could improve on troubleshooting complex electrical issues.', '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(157, 16, 7, 7, 47.67, '2025-02-21', NULL, 19, 'Strong knowledge of safety regulations and hazard prevention.', 'Could improve on troubleshooting complex electrical issues.', '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(158, 17, 7, 7, 37.70, '2025-02-21', NULL, 19, 'Excellent understanding of electrical safety principles. Good practical skills.', 'Needs more practice with safety documentation procedures.', '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(159, 18, 7, 7, 43.97, '2025-02-21', NULL, 19, 'Excellent understanding of electrical safety principles. Good practical skills.', 'Needs more practice with safety documentation procedures.', '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(162, 14, 8, 1, 29.98, '2025-02-24', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(163, 15, 8, 1, 29.67, '2025-02-24', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(164, 16, 8, 1, 18.40, '2025-02-24', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(165, 17, 8, 1, 22.99, '2025-02-24', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(166, 18, 8, 1, 19.77, '2025-02-24', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(169, 14, 8, 4, 23.29, '2025-03-06', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(170, 15, 8, 4, 26.70, '2025-03-06', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(171, 16, 8, 4, 23.61, '2025-03-06', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(172, 17, 8, 4, 27.97, '2025-03-06', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(173, 18, 8, 4, 29.00, '2025-03-06', NULL, 18, NULL, NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(176, 19, 9, 1, 13.29, '2025-03-08', NULL, 18, NULL, NULL, '2025-05-17 15:58:09', '2025-05-17 15:58:09', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(177, 20, 9, 1, 35.43, '2025-03-08', NULL, 18, NULL, NULL, '2025-05-17 15:58:09', '2025-05-17 15:58:09', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(178, 21, 9, 1, 37.29, '2025-03-08', NULL, 18, NULL, NULL, '2025-05-17 15:58:09', '2025-05-17 15:58:09', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(179, 22, 9, 1, 10.17, '2025-03-08', NULL, 18, NULL, NULL, '2025-05-17 15:58:09', '2025-05-17 15:58:09', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
(180, 23, 9, 1, 18.95, '2025-03-08', NULL, 18, NULL, NULL, '2025-05-17 15:58:09', '2025-05-17 15:58:09', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `Trainees`
--
-- Creation: May 21, 2025 at 12:04 AM
-- Last update: May 22, 2025 at 07:50 AM
--

DROP TABLE IF EXISTS `Trainees`;
CREATE TABLE `Trainees` (
  `TID` int(11) NOT NULL,
  `GovID` varchar(50) DEFAULT NULL,
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Phone` varchar(20) DEFAULT NULL COMMENT 'Contact phone number for the trainee',
  `PhoneNumber` varchar(20) DEFAULT NULL,
  `Address` text DEFAULT NULL,
  `City` varchar(100) DEFAULT NULL COMMENT 'City of residence',
  `Country` varchar(100) DEFAULT NULL COMMENT 'Country of residence',
  `DateOfBirth` date DEFAULT NULL,
  `EmergencyContactName` varchar(100) DEFAULT NULL,
  `EmergencyContactPhone` varchar(20) DEFAULT NULL,
  `GroupID` int(11) DEFAULT NULL,
  `UserID` int(11) DEFAULT NULL,
  `Status` enum('Active','Inactive','Graduated','Dropped') DEFAULT 'Active',
  `Notes` text DEFAULT NULL COMMENT 'Additional notes about the trainee',
  `CreatedAt` timestamp NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Trainees`
--

INSERT INTO `Trainees` (`TID`, `GovID`, `FirstName`, `LastName`, `Email`, `Phone`, `PhoneNumber`, `Address`, `City`, `Country`, `DateOfBirth`, `EmergencyContactName`, `EmergencyContactPhone`, `GroupID`, `UserID`, `Status`, `Notes`, `CreatedAt`, `UpdatedAt`) VALUES
(1, NULL, 'Charlie', 'Davis', 'trainee1@wateracademy.com', '1234567896', NULL, '123 Main St', 'Riyadh', 'Saudi Arabia', '1995-05-10', 'Eva Davis', '0501112222', 1, 7, '', 'Eager to learn.', '2025-05-16 01:05:40', '2025-05-16 01:05:40'),
(2, NULL, 'Diana', 'Evans', 'trainee2@wateracademy.com', '1234567897', NULL, '456 Oak Ave', 'Jeddah', 'Saudi Arabia', '1998-08-20', 'Frank Evans', '0503334444', 1, 8, '', NULL, '2025-05-16 01:05:40', '2025-05-16 01:05:40'),
(3, NULL, 'Edward', 'Harris', 'trainee3@wateracademy.com', '1234567898', NULL, '789 Pine Rd', 'Dammam', 'Saudi Arabia', '1992-01-30', 'Grace Harris', '0505556666', 2, 9, '', 'Previous experience in related field.', '2025-05-16 01:05:40', '2025-05-16 01:05:40'),
(4, NULL, 'Fiona', 'Wilson', 'trainee4@wateracademy.com', '1234567899', NULL, '101 Maple Dr', 'Riyadh', 'Saudi Arabia', '2000-11-05', 'George Wilson', '0507778888', 3, 10, '', 'Excellent participation.', '2025-05-16 01:05:40', '2025-05-16 01:05:40'),
(9, NULL, 'Ali', 'Hassan', 'ali.hassan@example.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, NULL, 'Active', NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(10, NULL, 'Fatima', 'Ahmed', 'fatima.ahmed@example.com', NULL, '0501151151', NULL, NULL, NULL, NULL, NULL, NULL, 4, NULL, 'Active', NULL, '2025-05-17 15:58:08', '2025-05-22 07:50:47'),
(11, NULL, 'Mohammed', 'Saleh', 'mohammed.saleh@example.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, NULL, 'Active', NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(12, NULL, 'Nora', 'Khalid', 'nora.khalid@example.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, NULL, 'Active', NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(13, NULL, 'Omar', 'Abdullah', 'omar.abdullah@example.com', NULL, '0559017469', NULL, NULL, NULL, NULL, NULL, NULL, 4, NULL, 'Active', NULL, '2025-05-17 15:58:08', '2025-05-18 07:47:59'),
(14, NULL, 'Saad', 'Al-Qahtani', 'saad.qahtani@example.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, 'Active', NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(15, NULL, 'Huda', 'Al-Otaibi', 'huda.otaibi@example.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, 'Active', NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(16, NULL, 'Fahad', 'Al-Harbi', 'fahad.harbi@example.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, 'Active', NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(17, NULL, 'Aisha', 'Al-Zahrani', 'aisha.zahrani@example.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, 'Active', NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(18, NULL, 'Khalid', 'Al-Ghamdi', 'khalid.ghamdi@example.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, 'Active', NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(19, NULL, 'Majid', 'Al-Dosari', 'majid.dosari@example.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 6, NULL, 'Active', NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(20, NULL, 'Layla', 'Al-Shammari', 'layla.shammari@example.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 6, NULL, 'Active', NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(21, NULL, 'Turki', 'Al-Mutairi', 'turki.mutairi@example.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 6, NULL, 'Active', NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(22, NULL, 'Reem', 'Al-Sulaiman', 'reem.sulaiman@example.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 6, NULL, 'Active', NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(23, NULL, 'Yousef', 'Al-Malki', 'yousef.malki@example.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 6, NULL, 'Active', NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(24, NULL, 'Ibrahim', 'Al-Rashid', 'ibrahim.rashid@example.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 7, NULL, 'Active', NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(25, NULL, 'Maha', 'Al-Juhani', 'maha.juhani@example.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 7, NULL, 'Active', NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(26, NULL, 'Saud', 'Al-Anazi', 'saud.anazi@example.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 7, NULL, 'Active', NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(27, NULL, 'Nouf', 'Al-Balawi', 'nouf.balawi@example.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 7, NULL, 'Active', NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(28, NULL, 'Faisal', 'Al-Shamrani', 'faisal.shamrani@example.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 7, NULL, 'Active', NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(29, NULL, 'Abdulaziz', 'Al-Qurashi', 'abdulaziz.qurashi@example.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 8, NULL, 'Active', NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(30, NULL, 'Hanan', 'Al-Ruwaili', 'hanan.ruwaili@example.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 8, NULL, 'Active', NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(31, NULL, 'Nawaf', 'Al-Dossary', 'nawaf.dossary@example.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 8, NULL, 'Active', NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(32, NULL, 'Lina', 'Al-Asmari', 'lina.asmari@example.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 8, NULL, 'Active', NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08'),
(33, NULL, 'Talal', 'Al-Sharif', 'talal.sharif@example.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 8, NULL, 'Active', NULL, '2025-05-17 15:58:08', '2025-05-17 15:58:08');

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--
-- Creation: May 23, 2025 at 02:34 PM
--

DROP TABLE IF EXISTS `Users`;
CREATE TABLE `Users` (
  `UserID` int(11) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `FirstName` varchar(50) DEFAULT NULL,
  `LastName` varchar(50) DEFAULT NULL,
  `Phone` varchar(20) DEFAULT NULL COMMENT 'Contact phone number for the user',
  `Specialty` varchar(255) DEFAULT NULL COMMENT 'User specialty, e.g., for instructors',
  `Qualifications` text DEFAULT NULL COMMENT 'User qualifications, e.g., degrees, certifications',
  `Biography` text DEFAULT NULL COMMENT 'Short biography of the user',
  `PreferredLanguage` varchar(50) DEFAULT 'English' COMMENT 'User preferred language for the system',
  `Department` varchar(100) DEFAULT NULL COMMENT 'Department the user belongs to, if applicable',
  `AvatarPath` varchar(255) DEFAULT NULL,
  `Role` enum('Super Admin','Admin','Instructor','Coordinator','Trainee') NOT NULL,
  `Status` enum('Active','Inactive','Pending') DEFAULT 'Active',
  `IsActive` tinyint(1) DEFAULT 1,
  `CreatedAt` timestamp NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `LastLogin` datetime DEFAULT NULL COMMENT 'Tracks when user last logged in',
  `RoleID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Users`
--

INSERT INTO `Users` (`UserID`, `Username`, `Password`, `Email`, `FirstName`, `LastName`, `Phone`, `Specialty`, `Qualifications`, `Biography`, `PreferredLanguage`, `Department`, `AvatarPath`, `Role`, `Status`, `IsActive`, `CreatedAt`, `UpdatedAt`, `LastLogin`, `RoleID`) VALUES
(1, 'shafey', '$2y$10$roKxDw3kmL3Af5FDlwBMt.IwZUzsSl9sBogGiYqbEr1dAQiAKBH7O', 'shafey@outlook.com', 'Shafey', 'Barakat', '0564187764', 'AI Consultant', '', '', 'English', 'AI', '../assets/userphotos/1_1747373984.jpg', 'Super Admin', 'Active', 1, '2025-05-15 20:58:48', '2025-05-23 14:26:31', '2025-05-23 14:26:31', NULL),
(2, 'superadmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin@wateracademy.com', 'Super', 'Admin', '1234567890', 'System Management', 'PhD in Everything', 'The main administrator.', 'English', 'IT', 'assets/img/avatars/1.png', 'Super Admin', 'Active', 1, '2025-05-16 01:00:01', '2025-05-17 12:56:47', '2025-05-17 12:56:47', NULL),
(3, 'adminuser', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@wateracademy.com', 'Regular', 'Admin', '1234567891', 'Operations', 'MSc in Management', 'Handles daily operations.', 'English', 'Administration', 'assets/img/avatars/2.png', 'Admin', 'Active', 1, '2025-05-16 01:00:01', '2025-05-17 12:56:47', '2025-05-17 12:56:47', NULL),
(4, 'instructor1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'instructor1@wateracademy.com', 'John', 'Doe', '1234567892', 'Water Treatment', 'BSc in Chemistry', 'Experienced instructor in water treatment processes.', 'English', 'Training', 'assets/img/avatars/3.png', 'Instructor', 'Active', 1, '2025-05-16 01:00:01', '2025-05-17 12:56:47', '2025-05-17 12:56:47', NULL),
(5, 'instructor2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'instructor2@wateracademy.com', 'Jane', 'Smith', '1234567893', 'Hydrology', 'MSc in Environmental Science', 'Specializes in hydrology and water resources.', 'English', 'Training', 'assets/img/avatars/4.png', 'Instructor', 'Active', 1, '2025-05-16 01:00:01', '2025-05-17 12:56:47', '2025-05-17 12:56:47', NULL),
(6, 'coordinator1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coordinator1@wateracademy.com', 'Alice', 'Brown', '1234567894', 'Program Coordination', 'BA in Education', 'Coordinates training programs.', 'English', 'Coordination', 'assets/img/avatars/1.png', 'Coordinator', 'Active', 1, '2025-05-16 01:00:01', '2025-05-17 12:56:47', '2025-05-17 12:56:47', NULL),
(7, 'coordinator2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coordinator2@wateracademy.com', 'Bob', 'Green', '1234567895', 'Logistics', 'Diploma in Admin', 'Manages logistics for groups.', 'English', 'Coordination', 'assets/img/avatars/2.png', 'Coordinator', 'Active', 1, '2025-05-16 01:00:01', '2025-05-17 12:56:47', '2025-05-17 12:56:47', NULL),
(8, 'traineeuser1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainee1@wateracademy.com', 'Charlie', 'Davis', '1234567896', NULL, NULL, NULL, 'English', NULL, NULL, 'Trainee', 'Active', 1, '2025-05-16 01:00:01', '2025-05-17 12:56:47', '2025-05-17 12:56:47', NULL),
(9, 'traineeuser2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainee2@wateracademy.com', 'Diana', 'Evans', '1234567897', NULL, NULL, NULL, 'English', NULL, NULL, 'Trainee', 'Active', 1, '2025-05-16 01:00:01', '2025-05-17 12:56:47', '2025-05-17 12:56:47', NULL),
(10, 'traineeuser3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainee3@wateracademy.com', 'Edward', 'Harris', '1234567898', NULL, NULL, NULL, 'English', NULL, NULL, 'Trainee', 'Active', 1, '2025-05-16 01:00:01', '2025-05-17 12:56:47', '2025-05-17 12:56:47', NULL),
(11, 'traineeuser4', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainee4@wateracademy.com', 'Fiona', 'Wilson', '1234567899', NULL, NULL, NULL, 'English', NULL, NULL, 'Trainee', 'Active', 1, '2025-05-16 01:00:01', '2025-05-17 12:56:47', '2025-05-17 12:56:47', NULL),
(12, 'admin1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin1@wateracademy.org', 'Ahmed', 'Mohammed', '+966501234567', NULL, NULL, NULL, 'English', NULL, NULL, 'Admin', 'Active', 1, '2025-05-17 15:32:03', '2025-05-20 21:19:17', '2025-05-20 21:19:17', NULL),
(13, 'admin2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin2@wateracademy.org', 'Fatima', 'Al-Saud', '+966501234568', NULL, NULL, NULL, 'English', NULL, NULL, 'Admin', 'Active', 1, '2025-05-17 15:32:03', '2025-05-17 15:32:03', NULL, NULL),
(14, 'coord1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coord1@wateracademy.org', 'Khalid', 'Al-Harbi', '+966501234569', NULL, NULL, NULL, 'English', NULL, NULL, 'Coordinator', 'Active', 1, '2025-05-17 15:32:03', '2025-05-17 15:32:03', NULL, NULL),
(15, 'coord2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coord2@wateracademy.org', 'Nora', 'Al-Qahtani', '+966501234570', NULL, NULL, NULL, 'English', NULL, NULL, 'Coordinator', 'Active', 1, '2025-05-17 15:32:03', '2025-05-21 04:20:13', '2025-05-21 04:20:13', NULL),
(16, 'coord3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coord3@wateracademy.org', 'Omar', 'Al-Ghamdi', '+966501234571', NULL, NULL, NULL, 'English', NULL, NULL, 'Coordinator', 'Active', 1, '2025-05-17 15:32:03', '2025-05-17 15:32:03', NULL, NULL),
(17, 'instr1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'instr1@wateracademy.org', 'Mohammed', 'Al-Otaibi', '+966501234572', 'Water Chemistry', 'PhD in Chemistry, 10+ years experience', NULL, 'English', NULL, NULL, 'Instructor', 'Active', 1, '2025-05-17 15:32:03', '2025-05-21 04:16:45', '2025-05-21 04:16:45', NULL),
(18, 'instr2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'instr2@wateracademy.org', 'Layla', 'Al-Dosari', '+966501234573', 'Mechanical Engineering', 'MSc in Mechanical Engineering', NULL, 'English', NULL, NULL, 'Instructor', 'Active', 1, '2025-05-17 15:32:03', '2025-05-17 15:32:03', NULL, NULL),
(19, 'instr3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'instr3@wateracademy.org', 'Abdullah', 'Al-Shammari', '+966501234574', 'Electrical Systems', 'BSc in Electrical Engineering, 15+ years industry experience', NULL, 'English', NULL, NULL, 'Instructor', 'Active', 1, '2025-05-17 15:32:03', '2025-05-21 04:17:27', '2025-05-21 04:17:27', NULL),
(20, 'instr4', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'instr4@wateracademy.org', 'Sara', 'Al-Mutairi', '+966501234575', 'Safety Procedures', 'Certified Safety Professional, 8+ years experience', NULL, 'English', NULL, NULL, 'Instructor', 'Active', 1, '2025-05-17 15:32:03', '2025-05-21 04:17:53', '2025-05-21 04:17:53', NULL),
(21, 'instr5', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'instr5@wateracademy.org', 'Yousef', 'Al-Zahrani', '+966501234576', 'Customer Service', 'MBA, 12+ years in utility customer relations', NULL, 'English', NULL, NULL, 'Instructor', 'Active', 1, '2025-05-17 15:32:03', '2025-05-17 15:32:03', NULL, NULL),
(22, 'admin1@wateracademy.org', '$2y$10$tMEx8mUke6raSthy2QMJi.dpnReYq36pFPSIFf7VaKE8STLhvIbvq', 'ib@gmail.com', 'Ibraheem', 'Ibraheem', '0501151151', 'English ', '', NULL, 'English', NULL, NULL, 'Instructor', 'Active', 1, '2025-05-20 21:33:04', '2025-05-20 21:33:04', NULL, NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `View_GroupPerformanceMetrics`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `View_GroupPerformanceMetrics`;
CREATE TABLE `View_GroupPerformanceMetrics` (
`GroupID` int(11)
,`GroupName` varchar(50)
,`GroupCourseID` int(11)
,`CourseID` int(11)
,`CourseName` varchar(100)
,`EnrolledTrainees` bigint(21)
,`AvgAttendance` decimal(5,1)
,`AvgFinalExamScore` decimal(5,1)
,`AvgPreTestScore` decimal(5,1)
,`AvgLGI` decimal(19,1)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `View_TraineeComponentGrades`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `View_TraineeComponentGrades`;
CREATE TABLE `View_TraineeComponentGrades` (
`GradeID` int(11)
,`TID` int(11)
,`TraineeFullName` varchar(101)
,`TraineeEmail` varchar(100)
,`GroupCourseID` int(11)
,`StandardCourseName` varchar(100)
,`StandardCourseCode` varchar(20)
,`ComponentID` int(11)
,`ComponentName` varchar(100)
,`ComponentMaxPoints` decimal(5,2)
,`ComponentScore` decimal(5,2)
,`GradeDate` date
,`GradeComments` text
,`PositiveFeedback` text
,`AreasToImprove` text
,`GradedByInstructorID` int(11)
,`GradedByInstructorName` varchar(101)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `View_TraineeEnrollmentDetails`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `View_TraineeEnrollmentDetails`;
CREATE TABLE `View_TraineeEnrollmentDetails` (
`TID` int(11)
,`TraineeFullName` varchar(101)
,`TraineeEmail` varchar(100)
,`TraineeGovID` varchar(50)
,`TraineePrimaryGroupID` int(11)
,`TraineePrimaryGroupName` varchar(50)
,`GroupCourseID` int(11)
,`CourseInstanceGroupID` int(11)
,`CourseInstanceGroupName` varchar(50)
,`StandardCourseID` int(11)
,`StandardCourseName` varchar(100)
,`StandardCourseCode` varchar(20)
,`InstanceStartDate` date
,`InstanceEndDate` date
,`InstructorID` int(11)
,`InstructorFullName` varchar(101)
,`InstructorEmail` varchar(100)
,`EnrollmentID` int(11)
,`EnrollmentDate` date
,`EnrollmentStatus` enum('Enrolled','Completed','Dropped','InProgress')
,`CompletionDate` date
,`StoredFinalScore` decimal(5,2)
,`CertificatePath` varchar(255)
,`CoordinatorID` int(11)
,`CoordinatorFullName` varchar(101)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `View_TraineePerformanceDetails`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `View_TraineePerformanceDetails`;
CREATE TABLE `View_TraineePerformanceDetails` (
`TID` int(11)
,`TraineeFullName` varchar(101)
,`GroupID` int(11)
,`GroupName` varchar(50)
,`GroupCourseID` int(11)
,`CourseID` int(11)
,`CourseName` varchar(100)
,`AttendancePercentage` decimal(5,2)
,`PreTestScore` decimal(5,2)
,`ParticipationScore` decimal(5,2)
,`AvgQuizScore` decimal(9,6)
,`FinalExamScore` decimal(5,2)
,`CompositeScore` decimal(8,1)
,`LGI` decimal(19,1)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_AttendanceSummary`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `vw_AttendanceSummary`;
CREATE TABLE `vw_AttendanceSummary` (
`TID` int(11)
,`FullName` varchar(101)
,`CourseID` int(11)
,`CourseName` varchar(100)
,`GroupID` int(11)
,`GroupName` varchar(50)
,`TotalSessions` bigint(21)
,`PresentCount` decimal(22,0)
,`LateCount` decimal(22,0)
,`AbsentCount` decimal(22,0)
,`ExcusedCount` decimal(22,0)
,`AttendancePercentage` decimal(27,1)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_Instructors`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `vw_Instructors`;
CREATE TABLE `vw_Instructors` (
`InstructorID` int(11)
,`Username` varchar(50)
,`FirstName` varchar(50)
,`LastName` varchar(50)
,`FullName` varchar(101)
,`Email` varchar(100)
,`Phone` varchar(20)
,`Specialty` varchar(255)
,`Qualifications` text
,`IsActive` enum('Active','Inactive','Pending')
,`CreatedAt` timestamp
,`UpdatedAt` timestamp
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_TraineeGrades`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `vw_TraineeGrades`;
CREATE TABLE `vw_TraineeGrades` (
`TID` int(11)
,`FullName` varchar(101)
,`CourseID` int(11)
,`CourseName` varchar(100)
,`GroupID` int(11)
,`GroupName` varchar(50)
,`PreTest` decimal(5,2)
,`AttGrade` decimal(5,2)
,`Participation` decimal(5,2)
,`Quiz1` decimal(5,2)
,`Quiz2` decimal(5,2)
,`Quiz3` decimal(5,2)
,`QuizAv` decimal(5,2)
,`Final` decimal(5,2)
,`Total` decimal(5,2)
,`LGI` decimal(11,1)
,`PositiveFeedback` text
,`AreasToImprove` text
);

-- --------------------------------------------------------

--
-- Structure for view `View_GroupPerformanceMetrics`
--
DROP TABLE IF EXISTS `View_GroupPerformanceMetrics`;

DROP VIEW IF EXISTS `View_GroupPerformanceMetrics`;
CREATE VIEW `View_GroupPerformanceMetrics`  AS SELECT `g`.`GroupID` AS `GroupID`, `g`.`GroupName` AS `GroupName`, `gc`.`ID` AS `GroupCourseID`, `c`.`CourseID` AS `CourseID`, `c`.`CourseName` AS `CourseName`, count(distinct `e`.`TID`) AS `EnrolledTrainees`, round(avg(`a`.`AttendancePercentage`),1) AS `AvgAttendance`, round(avg(`tg_final`.`Score`),1) AS `AvgFinalExamScore`, round(avg(`tg_pretest`.`Score`),1) AS `AvgPreTestScore`, round(avg(case when `tg_pretest`.`Score` is null then NULL when 100 - `tg_pretest`.`Score` / 50 * 100 = 0 then 0 else (`tg_composite`.`CompositeScore` - `tg_pretest`.`Score` / 50 * 100) / (100 - `tg_pretest`.`Score` / 50 * 100) * 100 end),1) AS `AvgLGI` FROM (((((((`Groups` `g` join `GroupCourses` `gc` on(`g`.`GroupID` = `gc`.`GroupID`)) join `Courses` `c` on(`gc`.`CourseID` = `c`.`CourseID`)) join `Enrollments` `e` on(`gc`.`ID` = `e`.`GroupCourseID`)) left join `Attendance` `a` on(`e`.`TID` = `a`.`TID` and `gc`.`ID` = `a`.`GroupCourseID`)) left join `TraineeGrades` `tg_final` on(`e`.`TID` = `tg_final`.`TID` and `gc`.`ID` = `tg_final`.`GroupCourseID` and `tg_final`.`ComponentID` = (select `GradeComponents`.`ComponentID` from `GradeComponents` where `GradeComponents`.`ComponentName` = 'Final Exam'))) left join `TraineeGrades` `tg_pretest` on(`e`.`TID` = `tg_pretest`.`TID` and `gc`.`ID` = `tg_pretest`.`GroupCourseID` and `tg_pretest`.`ComponentID` = (select `GradeComponents`.`ComponentID` from `GradeComponents` where `GradeComponents`.`ComponentName` = 'PRETEST'))) left join (select `tg`.`TID` AS `TID`,`tg`.`GroupCourseID` AS `GroupCourseID`,round(ifnull((select `Attendance`.`AttendancePercentage` / 10 from `Attendance` where `Attendance`.`TID` = `tg`.`TID` and `Attendance`.`GroupCourseID` = `tg`.`GroupCourseID`),0) + ifnull(max(case when `gc`.`ComponentName` = 'Participation' then `tg`.`Score` end),0) + ifnull(avg(case when `gc`.`ComponentName` like 'Quiz%' then `tg`.`Score` end),0) + ifnull(max(case when `gc`.`ComponentName` = 'Final Exam' then `tg`.`Score` end),0),1) AS `CompositeScore` from (`TraineeGrades` `tg` join `GradeComponents` `gc` on(`tg`.`ComponentID` = `gc`.`ComponentID`)) group by `tg`.`TID`,`tg`.`GroupCourseID`) `tg_composite` on(`e`.`TID` = `tg_composite`.`TID` and `gc`.`ID` = `tg_composite`.`GroupCourseID`)) GROUP BY `g`.`GroupID`, `g`.`GroupName`, `gc`.`ID`, `c`.`CourseID`, `c`.`CourseName` ;

-- --------------------------------------------------------

--
-- Structure for view `View_TraineeComponentGrades`
--
DROP TABLE IF EXISTS `View_TraineeComponentGrades`;

DROP VIEW IF EXISTS `View_TraineeComponentGrades`;
CREATE VIEW `View_TraineeComponentGrades`  AS SELECT `tg`.`GradeID` AS `GradeID`, `t`.`TID` AS `TID`, concat(`t`.`FirstName`,' ',`t`.`LastName`) AS `TraineeFullName`, `t`.`Email` AS `TraineeEmail`, `gc`.`ID` AS `GroupCourseID`, `c`.`CourseName` AS `StandardCourseName`, `c`.`CourseCode` AS `StandardCourseCode`, `comp`.`ComponentID` AS `ComponentID`, `comp`.`ComponentName` AS `ComponentName`, `comp`.`MaxPoints` AS `ComponentMaxPoints`, `tg`.`Score` AS `ComponentScore`, `tg`.`GradeDate` AS `GradeDate`, `tg`.`Comments` AS `GradeComments`, `tg`.`PositiveFeedback` AS `PositiveFeedback`, `tg`.`AreasToImprove` AS `AreasToImprove`, `tg`.`RecordedBy` AS `GradedByInstructorID`, concat(`u_instr`.`FirstName`,' ',`u_instr`.`LastName`) AS `GradedByInstructorName` FROM (((((`TraineeGrades` `tg` join `Trainees` `t` on(`tg`.`TID` = `t`.`TID`)) join `GroupCourses` `gc` on(`tg`.`GroupCourseID` = `gc`.`ID`)) join `Courses` `c` on(`gc`.`CourseID` = `c`.`CourseID`)) join `GradeComponents` `comp` on(`tg`.`ComponentID` = `comp`.`ComponentID`)) left join `Users` `u_instr` on(`tg`.`RecordedBy` = `u_instr`.`UserID`)) ;

-- --------------------------------------------------------

--
-- Structure for view `View_TraineeEnrollmentDetails`
--
DROP TABLE IF EXISTS `View_TraineeEnrollmentDetails`;

DROP VIEW IF EXISTS `View_TraineeEnrollmentDetails`;
CREATE VIEW `View_TraineeEnrollmentDetails`  AS SELECT `t`.`TID` AS `TID`, concat(`t`.`FirstName`,' ',`t`.`LastName`) AS `TraineeFullName`, `t`.`Email` AS `TraineeEmail`, `t`.`GovID` AS `TraineeGovID`, `g`.`GroupID` AS `TraineePrimaryGroupID`, `g`.`GroupName` AS `TraineePrimaryGroupName`, `gc`.`ID` AS `GroupCourseID`, `gc_g`.`GroupID` AS `CourseInstanceGroupID`, `gc_g`.`GroupName` AS `CourseInstanceGroupName`, `c`.`CourseID` AS `StandardCourseID`, `c`.`CourseName` AS `StandardCourseName`, `c`.`CourseCode` AS `StandardCourseCode`, `gc`.`StartDate` AS `InstanceStartDate`, `gc`.`EndDate` AS `InstanceEndDate`, `gc`.`InstructorID` AS `InstructorID`, concat(`u_instr`.`FirstName`,' ',`u_instr`.`LastName`) AS `InstructorFullName`, `u_instr`.`Email` AS `InstructorEmail`, `e`.`EnrollmentID` AS `EnrollmentID`, `e`.`EnrollmentDate` AS `EnrollmentDate`, `e`.`Status` AS `EnrollmentStatus`, `e`.`CompletionDate` AS `CompletionDate`, `e`.`FinalScore` AS `StoredFinalScore`, `e`.`CertificatePath` AS `CertificatePath`, `u_coord`.`UserID` AS `CoordinatorID`, concat(`u_coord`.`FirstName`,' ',`u_coord`.`LastName`) AS `CoordinatorFullName` FROM (((((((`Trainees` `t` join `Enrollments` `e` on(`t`.`TID` = `e`.`TID`)) join `GroupCourses` `gc` on(`e`.`GroupCourseID` = `gc`.`ID`)) join `Courses` `c` on(`gc`.`CourseID` = `c`.`CourseID`)) join `Groups` `gc_g` on(`gc`.`GroupID` = `gc_g`.`GroupID`)) left join `Groups` `g` on(`t`.`GroupID` = `g`.`GroupID`)) left join `Users` `u_instr` on(`gc`.`InstructorID` = `u_instr`.`UserID`)) left join `Users` `u_coord` on(`gc_g`.`CoordinatorID` = `u_coord`.`UserID`)) ;

-- --------------------------------------------------------

--
-- Structure for view `View_TraineePerformanceDetails`
--
DROP TABLE IF EXISTS `View_TraineePerformanceDetails`;

DROP VIEW IF EXISTS `View_TraineePerformanceDetails`;
CREATE VIEW `View_TraineePerformanceDetails`  AS SELECT `t`.`TID` AS `TID`, concat(`t`.`FirstName`,' ',`t`.`LastName`) AS `TraineeFullName`, `g`.`GroupID` AS `GroupID`, `g`.`GroupName` AS `GroupName`, `gc`.`ID` AS `GroupCourseID`, `c`.`CourseID` AS `CourseID`, `c`.`CourseName` AS `CourseName`, (select `Attendance`.`AttendancePercentage` from `Attendance` where `Attendance`.`TID` = `t`.`TID` and `Attendance`.`GroupCourseID` = `gc`.`ID`) AS `AttendancePercentage`, (select `TraineeGrades`.`Score` from `TraineeGrades` where `TraineeGrades`.`TID` = `t`.`TID` and `TraineeGrades`.`GroupCourseID` = `gc`.`ID` and `TraineeGrades`.`ComponentID` = (select `GradeComponents`.`ComponentID` from `GradeComponents` where `GradeComponents`.`ComponentName` = 'PRETEST')) AS `PreTestScore`, (select `TraineeGrades`.`Score` from `TraineeGrades` where `TraineeGrades`.`TID` = `t`.`TID` and `TraineeGrades`.`GroupCourseID` = `gc`.`ID` and `TraineeGrades`.`ComponentID` = (select `GradeComponents`.`ComponentID` from `GradeComponents` where `GradeComponents`.`ComponentName` = 'Participation')) AS `ParticipationScore`, (select avg(`TraineeGrades`.`Score`) from `TraineeGrades` where `TraineeGrades`.`TID` = `t`.`TID` and `TraineeGrades`.`GroupCourseID` = `gc`.`ID` and `TraineeGrades`.`ComponentID` in (select `GradeComponents`.`ComponentID` from `GradeComponents` where `GradeComponents`.`ComponentName` like 'Quiz%')) AS `AvgQuizScore`, (select `TraineeGrades`.`Score` from `TraineeGrades` where `TraineeGrades`.`TID` = `t`.`TID` and `TraineeGrades`.`GroupCourseID` = `gc`.`ID` and `TraineeGrades`.`ComponentID` = (select `GradeComponents`.`ComponentID` from `GradeComponents` where `GradeComponents`.`ComponentName` = 'Final Exam')) AS `FinalExamScore`, round(ifnull((select `Attendance`.`AttendancePercentage` / 10 from `Attendance` where `Attendance`.`TID` = `t`.`TID` and `Attendance`.`GroupCourseID` = `gc`.`ID`),0) + ifnull((select `TraineeGrades`.`Score` from `TraineeGrades` where `TraineeGrades`.`TID` = `t`.`TID` and `TraineeGrades`.`GroupCourseID` = `gc`.`ID` and `TraineeGrades`.`ComponentID` = (select `GradeComponents`.`ComponentID` from `GradeComponents` where `GradeComponents`.`ComponentName` = 'Participation')),0) + ifnull((select avg(`TraineeGrades`.`Score`) from `TraineeGrades` where `TraineeGrades`.`TID` = `t`.`TID` and `TraineeGrades`.`GroupCourseID` = `gc`.`ID` and `TraineeGrades`.`ComponentID` in (select `GradeComponents`.`ComponentID` from `GradeComponents` where `GradeComponents`.`ComponentName` like 'Quiz%')),0) + ifnull((select `TraineeGrades`.`Score` from `TraineeGrades` where `TraineeGrades`.`TID` = `t`.`TID` and `TraineeGrades`.`GroupCourseID` = `gc`.`ID` and `TraineeGrades`.`ComponentID` = (select `GradeComponents`.`ComponentID` from `GradeComponents` where `GradeComponents`.`ComponentName` = 'Final Exam')),0),1) AS `CompositeScore`, CASE WHEN (select `TraineeGrades`.`Score` from `TraineeGrades` where `TraineeGrades`.`TID` = `t`.`TID` AND `TraineeGrades`.`GroupCourseID` = `gc`.`ID` AND `TraineeGrades`.`ComponentID` = (select `GradeComponents`.`ComponentID` from `GradeComponents` where `GradeComponents`.`ComponentName` = 'PRETEST')) is null THEN NULL WHEN 100 - (select `TraineeGrades`.`Score` from `TraineeGrades` where `TraineeGrades`.`TID` = `t`.`TID` AND `TraineeGrades`.`GroupCourseID` = `gc`.`ID` AND `TraineeGrades`.`ComponentID` = (select `GradeComponents`.`ComponentID` from `GradeComponents` where `GradeComponents`.`ComponentName` = 'PRETEST')) / 50 * 100 = 0 THEN 0 ELSE round((round(ifnull((select `Attendance`.`AttendancePercentage` / 10 from `Attendance` where `Attendance`.`TID` = `t`.`TID` and `Attendance`.`GroupCourseID` = `gc`.`ID`),0) + ifnull((select `TraineeGrades`.`Score` from `TraineeGrades` where `TraineeGrades`.`TID` = `t`.`TID` and `TraineeGrades`.`GroupCourseID` = `gc`.`ID` and `TraineeGrades`.`ComponentID` = (select `GradeComponents`.`ComponentID` from `GradeComponents` where `GradeComponents`.`ComponentName` = 'Participation')),0) + ifnull((select avg(`TraineeGrades`.`Score`) from `TraineeGrades` where `TraineeGrades`.`TID` = `t`.`TID` and `TraineeGrades`.`GroupCourseID` = `gc`.`ID` and `TraineeGrades`.`ComponentID` in (select `GradeComponents`.`ComponentID` from `GradeComponents` where `GradeComponents`.`ComponentName` like 'Quiz%')),0) + ifnull((select `TraineeGrades`.`Score` from `TraineeGrades` where `TraineeGrades`.`TID` = `t`.`TID` and `TraineeGrades`.`GroupCourseID` = `gc`.`ID` and `TraineeGrades`.`ComponentID` = (select `GradeComponents`.`ComponentID` from `GradeComponents` where `GradeComponents`.`ComponentName` = 'Final Exam')),0),1) - (select `TraineeGrades`.`Score` from `TraineeGrades` where `TraineeGrades`.`TID` = `t`.`TID` and `TraineeGrades`.`GroupCourseID` = `gc`.`ID` and `TraineeGrades`.`ComponentID` = (select `GradeComponents`.`ComponentID` from `GradeComponents` where `GradeComponents`.`ComponentName` = 'PRETEST')) / 50 * 100) / (100 - (select `TraineeGrades`.`Score` from `TraineeGrades` where `TraineeGrades`.`TID` = `t`.`TID` and `TraineeGrades`.`GroupCourseID` = `gc`.`ID` and `TraineeGrades`.`ComponentID` = (select `GradeComponents`.`ComponentID` from `GradeComponents` where `GradeComponents`.`ComponentName` = 'PRETEST')) / 50 * 100) * 100,1) END AS `LGI` FROM ((((`Trainees` `t` join `Groups` `g` on(`t`.`GroupID` = `g`.`GroupID`)) join `GroupCourses` `gc` on(`g`.`GroupID` = `gc`.`GroupID`)) join `Courses` `c` on(`gc`.`CourseID` = `c`.`CourseID`)) join `Enrollments` `e` on(`t`.`TID` = `e`.`TID` and `gc`.`ID` = `e`.`GroupCourseID`)) ;

-- --------------------------------------------------------

--
-- Structure for view `vw_AttendanceSummary`
--
DROP TABLE IF EXISTS `vw_AttendanceSummary`;

DROP VIEW IF EXISTS `vw_AttendanceSummary`;
CREATE VIEW `vw_AttendanceSummary`  AS SELECT `t`.`TID` AS `TID`, concat(`t`.`FirstName`,' ',`t`.`LastName`) AS `FullName`, `c`.`CourseID` AS `CourseID`, `c`.`CourseName` AS `CourseName`, `g`.`GroupID` AS `GroupID`, `g`.`GroupName` AS `GroupName`, count(distinct `a`.`SessionDate`) AS `TotalSessions`, sum(case when `a`.`Status` = 'Present' then 1 else 0 end) AS `PresentCount`, sum(case when `a`.`Status` = 'Late' then 1 else 0 end) AS `LateCount`, sum(case when `a`.`Status` = 'Absent' then 1 else 0 end) AS `AbsentCount`, sum(case when `a`.`Status` = 'Excused' then 1 else 0 end) AS `ExcusedCount`, round(sum(case when `a`.`Status` in ('Present','Late') then 1 else 0 end) * 100.0 / count(distinct `a`.`SessionDate`),1) AS `AttendancePercentage` FROM ((((`Trainees` `t` join `Attendance` `a` on(`t`.`TID` = `a`.`TID`)) join `GroupCourses` `gc` on(`a`.`GroupCourseID` = `gc`.`ID`)) join `Courses` `c` on(`gc`.`CourseID` = `c`.`CourseID`)) join `Groups` `g` on(`gc`.`GroupID` = `g`.`GroupID`)) GROUP BY `t`.`TID`, `t`.`FirstName`, `t`.`LastName`, `c`.`CourseID`, `c`.`CourseName`, `g`.`GroupID`, `g`.`GroupName` ;

-- --------------------------------------------------------

--
-- Structure for view `vw_Instructors`
--
DROP TABLE IF EXISTS `vw_Instructors`;

DROP VIEW IF EXISTS `vw_Instructors`;
CREATE VIEW `vw_Instructors`  AS SELECT `Users`.`UserID` AS `InstructorID`, `Users`.`Username` AS `Username`, `Users`.`FirstName` AS `FirstName`, `Users`.`LastName` AS `LastName`, concat(`Users`.`FirstName`,' ',`Users`.`LastName`) AS `FullName`, `Users`.`Email` AS `Email`, `Users`.`Phone` AS `Phone`, `Users`.`Specialty` AS `Specialty`, `Users`.`Qualifications` AS `Qualifications`, `Users`.`Status` AS `IsActive`, `Users`.`CreatedAt` AS `CreatedAt`, `Users`.`UpdatedAt` AS `UpdatedAt` FROM `Users` WHERE `Users`.`Role` = 'Instructor' ;

-- --------------------------------------------------------

--
-- Structure for view `vw_TraineeGrades`
--
DROP TABLE IF EXISTS `vw_TraineeGrades`;

DROP VIEW IF EXISTS `vw_TraineeGrades`;
CREATE VIEW `vw_TraineeGrades`  AS SELECT `t`.`TID` AS `TID`, concat(`t`.`FirstName`,' ',`t`.`LastName`) AS `FullName`, `c`.`CourseID` AS `CourseID`, `c`.`CourseName` AS `CourseName`, `g`.`GroupID` AS `GroupID`, `g`.`GroupName` AS `GroupName`, `tg`.`PreTest` AS `PreTest`, `tg`.`AttGrade` AS `AttGrade`, `tg`.`Participation` AS `Participation`, `tg`.`Quiz1` AS `Quiz1`, `tg`.`Quiz2` AS `Quiz2`, `tg`.`Quiz3` AS `Quiz3`, `tg`.`QuizAv` AS `QuizAv`, `tg`.`Final` AS `Final`, `tg`.`Total` AS `Total`, CASE WHEN `tg`.`PreTest` > 0 THEN round((`tg`.`Final` - `tg`.`PreTest`) / `tg`.`PreTest` * 100,1) ELSE 0 END AS `LGI`, `tg`.`PositiveFeedback` AS `PositiveFeedback`, `tg`.`AreasToImprove` AS `AreasToImprove` FROM ((((`Trainees` `t` join `TraineeGrades` `tg` on(`t`.`TID` = `tg`.`TID`)) join `GroupCourses` `gc` on(`tg`.`GroupCourseID` = `gc`.`ID`)) join `Courses` `c` on(`gc`.`CourseID` = `c`.`CourseID`)) join `Groups` `g` on(`gc`.`GroupID` = `g`.`GroupID`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Attendance`
--
ALTER TABLE `Attendance`
  ADD PRIMARY KEY (`AttendanceID`),
  ADD UNIQUE KEY `UQ_Trainee_GroupCourse_Attendance` (`TID`,`GroupCourseID`),
  ADD KEY `fk_attendance_trainee` (`TID`),
  ADD KEY `fk_attendance_groupcourse` (`GroupCourseID`),
  ADD KEY `fk_attendance_recordedby` (`RecordedBy`);

--
-- Indexes for table `Courses`
--
ALTER TABLE `Courses`
  ADD PRIMARY KEY (`CourseID`),
  ADD UNIQUE KEY `CourseCode` (`CourseCode`);

--
-- Indexes for table `EducationMetrics`
--
ALTER TABLE `EducationMetrics`
  ADD PRIMARY KEY (`MetricID`),
  ADD UNIQUE KEY `MetricName` (`MetricName`);

--
-- Indexes for table `EmailTemplates`
--
ALTER TABLE `EmailTemplates`
  ADD PRIMARY KEY (`TemplateID`),
  ADD UNIQUE KEY `TemplateCode` (`TemplateCode`);

--
-- Indexes for table `Enrollments`
--
ALTER TABLE `Enrollments`
  ADD PRIMARY KEY (`EnrollmentID`),
  ADD UNIQUE KEY `UQ_Trainee_GroupCourse` (`TID`,`GroupCourseID`),
  ADD KEY `fk_enrollments_trainee` (`TID`),
  ADD KEY `fk_enrollments_groupcourse` (`GroupCourseID`);

--
-- Indexes for table `GradeComponents`
--
ALTER TABLE `GradeComponents`
  ADD PRIMARY KEY (`ComponentID`),
  ADD UNIQUE KEY `ComponentName` (`ComponentName`);

--
-- Indexes for table `GroupCourses`
--
ALTER TABLE `GroupCourses`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `fk_groupcourses_group` (`GroupID`),
  ADD KEY `fk_groupcourses_course` (`CourseID`),
  ADD KEY `fk_groupcourses_instructor` (`InstructorID`);

--
-- Indexes for table `Groups`
--
ALTER TABLE `Groups`
  ADD PRIMARY KEY (`GroupID`),
  ADD KEY `fk_group_coordinator` (`CoordinatorID`);

--
-- Indexes for table `Permissions`
--
ALTER TABLE `Permissions`
  ADD PRIMARY KEY (`PermissionID`),
  ADD UNIQUE KEY `PermissionName` (`PermissionName`);

--
-- Indexes for table `RolePermissions`
--
ALTER TABLE `RolePermissions`
  ADD PRIMARY KEY (`RoleID`,`PermissionID`),
  ADD KEY `PermissionID` (`PermissionID`);

--
-- Indexes for table `Roles`
--
ALTER TABLE `Roles`
  ADD PRIMARY KEY (`RoleID`),
  ADD UNIQUE KEY `RoleName` (`RoleName`);

--
-- Indexes for table `TraineeGrades`
--
ALTER TABLE `TraineeGrades`
  ADD PRIMARY KEY (`GradeID`),
  ADD UNIQUE KEY `UQ_Trainee_GroupCourse_Component` (`TID`,`GroupCourseID`,`ComponentID`),
  ADD KEY `fk_traineegrades_trainee` (`TID`),
  ADD KEY `fk_traineegrades_groupcourse` (`GroupCourseID`),
  ADD KEY `fk_traineegrades_component` (`ComponentID`),
  ADD KEY `fk_traineegrades_recordedby` (`RecordedBy`);

--
-- Indexes for table `Trainees`
--
ALTER TABLE `Trainees`
  ADD PRIMARY KEY (`TID`),
  ADD UNIQUE KEY `UQ_OldTID` (`GovID`),
  ADD UNIQUE KEY `UQ_TraineeEmail` (`Email`),
  ADD UNIQUE KEY `UQ_GovID` (`GovID`),
  ADD KEY `fk_trainee_group` (`GroupID`),
  ADD KEY `fk_trainee_user` (`UserID`);

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Username` (`Username`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `RoleID` (`RoleID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Attendance`
--
ALTER TABLE `Attendance`
  MODIFY `AttendanceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `Courses`
--
ALTER TABLE `Courses`
  MODIFY `CourseID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `EducationMetrics`
--
ALTER TABLE `EducationMetrics`
  MODIFY `MetricID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `EmailTemplates`
--
ALTER TABLE `EmailTemplates`
  MODIFY `TemplateID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `Enrollments`
--
ALTER TABLE `Enrollments`
  MODIFY `EnrollmentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `GradeComponents`
--
ALTER TABLE `GradeComponents`
  MODIFY `ComponentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `GroupCourses`
--
ALTER TABLE `GroupCourses`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `Groups`
--
ALTER TABLE `Groups`
  MODIFY `GroupID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `Permissions`
--
ALTER TABLE `Permissions`
  MODIFY `PermissionID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Roles`
--
ALTER TABLE `Roles`
  MODIFY `RoleID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `TraineeGrades`
--
ALTER TABLE `TraineeGrades`
  MODIFY `GradeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=183;

--
-- AUTO_INCREMENT for table `Trainees`
--
ALTER TABLE `Trainees`
  MODIFY `TID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `Users`
--
ALTER TABLE `Users`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Attendance`
--
ALTER TABLE `Attendance`
  ADD CONSTRAINT `fk_attendance_groupcourse` FOREIGN KEY (`GroupCourseID`) REFERENCES `GroupCourses` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_attendance_recordedby` FOREIGN KEY (`RecordedBy`) REFERENCES `Users` (`UserID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_attendance_trainee` FOREIGN KEY (`TID`) REFERENCES `Trainees` (`TID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Enrollments`
--
ALTER TABLE `Enrollments`
  ADD CONSTRAINT `fk_enrollments_groupcourse` FOREIGN KEY (`GroupCourseID`) REFERENCES `GroupCourses` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_enrollments_trainee` FOREIGN KEY (`TID`) REFERENCES `Trainees` (`TID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `GroupCourses`
--
ALTER TABLE `GroupCourses`
  ADD CONSTRAINT `fk_groupcourses_course` FOREIGN KEY (`CourseID`) REFERENCES `Courses` (`CourseID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_groupcourses_group` FOREIGN KEY (`GroupID`) REFERENCES `Groups` (`GroupID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_groupcourses_instructor` FOREIGN KEY (`InstructorID`) REFERENCES `Users` (`UserID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `Groups`
--
ALTER TABLE `Groups`
  ADD CONSTRAINT `fk_group_coordinator` FOREIGN KEY (`CoordinatorID`) REFERENCES `Users` (`UserID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `RolePermissions`
--
ALTER TABLE `RolePermissions`
  ADD CONSTRAINT `RolePermissions_ibfk_1` FOREIGN KEY (`RoleID`) REFERENCES `Roles` (`RoleID`) ON DELETE CASCADE,
  ADD CONSTRAINT `RolePermissions_ibfk_2` FOREIGN KEY (`PermissionID`) REFERENCES `Permissions` (`PermissionID`) ON DELETE CASCADE;

--
-- Constraints for table `TraineeGrades`
--
ALTER TABLE `TraineeGrades`
  ADD CONSTRAINT `fk_traineegrades_component` FOREIGN KEY (`ComponentID`) REFERENCES `GradeComponents` (`ComponentID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_traineegrades_groupcourse` FOREIGN KEY (`GroupCourseID`) REFERENCES `GroupCourses` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_traineegrades_recordedby` FOREIGN KEY (`RecordedBy`) REFERENCES `Users` (`UserID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_traineegrades_trainee` FOREIGN KEY (`TID`) REFERENCES `Trainees` (`TID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Trainees`
--
ALTER TABLE `Trainees`
  ADD CONSTRAINT `fk_trainee_group` FOREIGN KEY (`GroupID`) REFERENCES `Groups` (`GroupID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_trainee_user` FOREIGN KEY (`UserID`) REFERENCES `Users` (`UserID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `Users`
--
ALTER TABLE `Users`
  ADD CONSTRAINT `Users_ibfk_1` FOREIGN KEY (`RoleID`) REFERENCES `Roles` (`RoleID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- Add users table for authentication
USE `bte_result_system`;

CREATE TABLE IF NOT EXISTS `users` (
  `User_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Email` varchar(100) NOT NULL UNIQUE,
  `Password` varchar(255) NOT NULL,
  `Role` enum('student','faculty','admin') NOT NULL,
  `Reference_ID` int(11) DEFAULT NULL,
  `Created_At` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Last_Login` timestamp NULL DEFAULT NULL,
  `Status` enum('active','inactive','pending') DEFAULT 'pending',
  PRIMARY KEY (`User_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add attendance table
CREATE TABLE IF NOT EXISTS `attendance` (
  `Attendance_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Student_ID` int(11) NOT NULL,
  `Subj_ID` int(11) NOT NULL,
  `Faculty_ID` int(11) NOT NULL,
  `Date` date NOT NULL,
  `Status` enum('Present','Absent','Late') NOT NULL,
  `Remarks` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`Attendance_ID`),
  KEY `Student_ID` (`Student_ID`),
  KEY `Subj_ID` (`Subj_ID`),
  KEY `Faculty_ID` (`Faculty_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add Email column to student table if not exists
ALTER TABLE `student` ADD COLUMN IF NOT EXISTS `Email` varchar(100) DEFAULT NULL;

-- Update subject table structure to include year and semester
ALTER TABLE `subject` ADD COLUMN IF NOT EXISTS `Year` int(1) DEFAULT 1;
ALTER TABLE `subject` ADD COLUMN IF NOT EXISTS `Semester` int(1) DEFAULT 1;

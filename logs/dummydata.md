## Dummy Data INSERT Statements

**Important Note on Passwords:**
For all user accounts created below, the password is set to `'password'`. The hashed value provided is for this password: `$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi`

---

### 1. `Users` Table

```sql
-- Users Table Data
INSERT INTO `Users` (`Username`, `Email`, `Password`, `Role`, `FirstName`, `LastName`, `Phone`, `AvatarPath`, `Specialty`, `Qualifications`, `Biography`, `PreferredLanguage`, `Department`, `Status`, `CreatedAt`, `UpdatedAt`) VALUES
('superadmin', 'superadmin@wateracademy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Admin', 'Super', 'Admin', '1234567890', 'assets/img/avatars/1.png', 'System Management', 'PhD in Everything', 'The main administrator.', 'English', 'IT', 'Active', NOW(), NOW()),
('adminuser', 'admin@wateracademy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'Regular', 'Admin', '1234567891', 'assets/img/avatars/2.png', 'Operations', 'MSc in Management', 'Handles daily operations.', 'English', 'Administration', 'Active', NOW(), NOW()),
('instructor1', 'instructor1@wateracademy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Instructor', 'John', 'Doe', '1234567892', 'assets/img/avatars/3.png', 'Water Treatment', 'BSc in Chemistry', 'Experienced instructor in water treatment processes.', 'English', 'Training', 'Active', NOW(), NOW()),
('instructor2', 'instructor2@wateracademy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Instructor', 'Jane', 'Smith', '1234567893', 'assets/img/avatars/4.png', 'Hydrology', 'MSc in Environmental Science', 'Specializes in hydrology and water resources.', 'English', 'Training', 'Active', NOW(), NOW()),
('coordinator1', 'coordinator1@wateracademy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Coordinator', 'Alice', 'Brown', '1234567894', 'assets/img/avatars/1.png', 'Program Coordination', 'BA in Education', 'Coordinates training programs.', 'English', 'Coordination', 'Active', NOW(), NOW()),
('coordinator2', 'coordinator2@wateracademy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Coordinator', 'Bob', 'Green', '1234567895', 'assets/img/avatars/2.png', 'Logistics', 'Diploma in Admin', 'Manages logistics for groups.', 'English', 'Coordination', 'Active', NOW(), NOW()),
('traineeuser1', 'trainee1@wateracademy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trainee', 'Charlie', 'Davis', '1234567896', NULL, NULL, NULL, NULL, 'English', NULL, 'Active', NOW(), NOW()),
('traineeuser2', 'trainee2@wateracademy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trainee', 'Diana', 'Evans', '1234567897', NULL, NULL, NULL, NULL, 'English', NULL, 'Active', NOW(), NOW()),
('traineeuser3', 'trainee3@wateracademy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trainee', 'Edward', 'Harris', '1234567898', NULL, NULL, NULL, NULL, 'English', NULL, 'Active', NOW(), NOW()),
('traineeuser4', 'trainee4@wateracademy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trainee', 'Fiona', 'Wilson', '1234567899', NULL, NULL, NULL, NULL, 'English', NULL, 'Active', NOW(), NOW());
```

---

### 2. `Courses` Table

```sql
-- Courses Table Data
INSERT INTO `Courses` (`CourseName`, `CourseCode`, `Description`, `DurationWeeks`, `TotalHours`, `Status`, `CreatedAt`, `UpdatedAt`) VALUES
('Introduction to Water Chemistry', 'CHEM101', 'Fundamentals of water chemistry and quality parameters.', 4, 40, 'Active', NOW(), NOW()),
('Advanced Hydrology', 'HYDRO201', 'In-depth study of hydrological cycles and water movement.', 6, 60, 'Active', NOW(), NOW()),
('Wastewater Treatment Processes', 'WWTP301', 'Covers various physical, chemical, and biological wastewater treatment methods.', 8, 80, 'Active', NOW(), NOW()),
('Water Distribution Systems', 'WDS102', 'Design and operation of water distribution networks.', 5, 50, 'Complete', NOW(), NOW()),
('Old Desalination Techniques', 'DESAL001', 'Historical overview of desalination (to be archived).', 4, 30, 'Archived', NOW(), NOW());
```

---

### 3. `Groups` Table

```sql
-- Groups Table Data
-- Assuming UserID 5 (coordinator1) and 6 (coordinator2) exist from Users table.
INSERT INTO `Groups` (`GroupID`, `GroupName`, `Description`, `StartDate`, `EndDate`, `CoordinatorID`, `Status`, `CreatedAt`, `UpdatedAt`) VALUES
('GRP-2024-CHEM-A', 'Water Chemistry Batch A 2024', 'First batch for Water Chemistry in 2024.', '2024-03-01', '2024-03-31', 5, 'Active', NOW(), NOW()),
('GRP-2024-HYDRO-A', 'Advanced Hydrology Batch A 2024', 'First batch for Advanced Hydrology in 2024.', '2024-04-01', '2024-05-15', 5, 'Planned', NOW(), NOW()),
('GRP-2023-WWTP-B', 'Wastewater Treatment Batch B 2023', 'Second batch for WWTP in 2023 (completed).', '2023-09-01', '2023-10-31', 6, 'Completed', NOW(), NOW());
```

---

### 4. `Trainees` Table

```sql
-- Trainees Table Data
-- Assuming UserIDs 7, 8, 9, 10 exist from Users table and are designated as trainees.
-- Assuming GroupIDs 'GRP-2024-CHEM-A', 'GRP-2024-HYDRO-A', 'GRP-2023-WWTP-B' exist from Groups table.
INSERT INTO `Trainees` (`UserID`, `FirstName`, `LastName`, `Email`, `Phone`, `DateOfBirth`, `Address`, `City`, `Country`, `EmergencyContactName`, `EmergencyContactPhone`, `GroupID`, `Status`, `Notes`, `CreatedAt`, `UpdatedAt`) VALUES
(7, 'Charlie', 'Davis', 'trainee1@wateracademy.com', '1234567896', '1995-05-10', '123 Main St', 'Riyadh', 'Saudi Arabia', 'Eva Davis', '0501112222', 1, 'Enrolled', 'Eager to learn.', NOW(), NOW()),
(8, 'Diana', 'Evans', 'trainee2@wateracademy.com', '1234567897', '1998-08-20', '456 Oak Ave', 'Jeddah', 'Saudi Arabia', 'Frank Evans', '0503334444', 1, 'Enrolled', NULL, NOW(), NOW()),
(9, 'Edward', 'Harris', 'trainee3@wateracademy.com', '1234567898', '1992-01-30', '789 Pine Rd', 'Dammam', 'Saudi Arabia', 'Grace Harris', '0505556666', 2, 'Enrolled', 'Previous experience in related field.', NOW(), NOW()),
(10, 'Fiona', 'Wilson', 'trainee4@wateracademy.com', '1234567899', '2000-11-05', '101 Maple Dr', 'Riyadh', 'Saudi Arabia', 'George Wilson', '0507778888', 3, 'Graduated', 'Excellent participation.', NOW(), NOW());
```

---

### 5. `GroupCourses` Table

```sql
-- GroupCourses Table Data
-- Assuming GroupIDs 'GRP-2024-CHEM-A', 'GRP-2024-HYDRO-A', 'GRP-2023-WWTP-B' exist from Groups table.
-- Assuming CourseIDs 1, 2, 3 exist from Courses table.
-- Assuming Instructor UserIDs 3 (John Doe) and 4 (Jane Smith) exist from Users table.
INSERT INTO `GroupCourses` (`GroupID`, `CourseID`, `InstructorID`, `StartDate`, `EndDate`, `Location`, `ScheduleDetails`, `Status`, `CreatedAt`, `UpdatedAt`) VALUES
(1, 1, 3, '2024-03-01', '2024-03-31', 'Room 101', 'Mon-Wed-Fri 9AM-12PM', 'In Progress', NOW(), NOW()),
(2, 2, 4, '2024-04-01', '2024-05-15', 'Room 102', 'Tue-Thu 1PM-4PM', 'Scheduled', NOW(), NOW()),
(3, 3, 3, '2023-09-01', '2023-10-31', 'Room 103', 'Mon-Fri 10AM-1PM', 'Completed', NOW(), NOW());
```

---

### 6. `Enrollments` Table

```sql
-- Enrollments Table Data
-- Assuming TraineeIDs 1, 2, 3, 4 exist from Trainees table.
-- Assuming GroupCourseIDs 1, 2, 3 exist from GroupCourses table (auto-incremented primary key).

INSERT INTO `Enrollments` (`TID`, `GroupCourseID`, `EnrollmentDate`, `Status`, `FinalScore`, `CreatedAt`, `UpdatedAt`) VALUES
(1, 1, '2024-02-20', 'Enrolled', NULL, NOW(), NOW()),
(2, 1, '2024-02-20', 'Enrolled', NULL, NOW(), NOW()),
(3, 2, '2024-03-15', 'Enrolled', NULL, NOW(), NOW()),
(4, 3, '2023-08-25', 'Completed', 88.5, NOW(), NOW());

```

---

### 7. `GradeComponents` Table

```sql
-- GradeComponents Table Data
-- Assuming CourseIDs 1, 2, 3 exist from Courses table.
--- a/f:\Water Academy Web - Local\wa\logs\dummydata.md
+++ b/f:\Water Academy Web - Local\wa\logs\dummydata.md
@@ -69,17 +69,14 @@
 
 ```sql
 -- GradeComponents Table Data
--- Assuming CourseIDs 1, 2, 3 exist from Courses table.
-INSERT INTO `GradeComponents` (`CourseID`, `ComponentName`, `Weight`, `MaxPoints`, `Description`, `CreatedAt`, `UpdatedAt`) VALUES
-(1, 'Quiz 1', 20, 100, 'First quiz on basic concepts.', NOW(), NOW()),
-(1, 'Midterm Exam', 30, 100, 'Comprehensive midterm.', NOW(), NOW()),
-(1, 'Final Exam', 50, 100, 'Final comprehensive exam.', NOW(), NOW()),
-(2, 'Assignment 1', 25, 100, 'Hydrological cycle assignment.', NOW(), NOW()),
-(2, 'Project Presentation', 40, 100, 'Group project presentation.', NOW(), NOW()),
-(2, 'Final Paper', 35, 100, 'Research paper on selected topic.', NOW(), NOW()),
-(3, 'PRETEST', 0, 50, 'Initial knowledge assessment.', NOW(), NOW()),
-(3, 'Participation', 10, 100, 'Class participation.', NOW(), NOW()),
-(3, 'Quiz Average', 30, 100, 'Average of all quizzes.', NOW(), NOW()),
-(3, 'Final Exam', 60, 100, 'Final comprehensive exam.', NOW(), NOW());
+-- These are general components. Their association with specific courses happens via TraineeGrades.
+-- MaxPoints here reflect the typical maximum for that component type.
+INSERT INTO `GradeComponents` (`ComponentName`, `MaxPoints`, `Description`, `IsDefault`) VALUES
+('PreTest', 50.00, 'Initial knowledge assessment. Does not add to course total.', 1),
+('Attendance', 10.00, 'Attendance score, derived from percentage.', 1),
+('Participation', 10.00, 'Class participation and engagement.', 1),
+('Quiz 1', 30.00, 'First intra-course quiz.', 0),
+('Quiz 2', 30.00, 'Second intra-course quiz (optional).', 0),
+('Quiz 3', 30.00, 'Third intra-course quiz (optional).', 0),
+('Final Exam', 50.00, 'Final comprehensive examination.', 1);

```

---

### 8. `TraineeGrades` Table

```sql
-- TraineeGrades Table Data
-- Assuming TraineeIDs 1, 2, 4 exist from Trainees table.
-- Assuming CourseIDs 1, 3 exist from Courses table.
-- Assuming ComponentIDs 1, 2 (for Course 1) and 7, 8, 9, 10 (for Course 3) exist from GradeComponents table.
-- Assuming RecordedBy UserID 3 (Instructor John Doe) exists from Users table.
INSERT INTO `TraineeGrades` (`TraineeID`, `CourseID`, `ComponentID`, `Score`, `GradeDate`, `RecordedBy`, `Comments`, `CreatedAt`, `UpdatedAt`) VALUES
(1, 1, 1, 85, '2024-03-10', 3, 'Good understanding.', NOW(), NOW()),
(1, 1, 2, 78, '2024-03-20', 3, 'Needs more detail in answers.', NOW(), NOW()),
(2, 1, 1, 92, '2024-03-10', 3, 'Excellent work!', NOW(), NOW()),
(4, 3, 7, 30, '2023-09-02', 3, 'Pre-test score.', NOW(), NOW()),
(4, 3, 8, 90, '2023-10-30', 3, 'Very active.', NOW(), NOW()),
(4, 3, 9, 85, '2023-10-30', 3, 'Good quiz average.', NOW(), NOW()),
(4, 3, 10, 90, '2023-10-30', 3, 'Strong final exam.', NOW(), NOW());
```

---

### 9. `Attendance` Table

```sql
-- Attendance Table Data
-- Assuming TraineeIDs 1, 2, 4 exist from Trainees table.
-- Assuming CourseIDs 1, 3 exist from Courses table.
INSERT INTO `Attendance` (`TraineeID`, `CourseID`, `SessionDate`, `Status`, `Notes`, `CreatedAt`, `UpdatedAt`) VALUES
(1, 1, '2024-03-01', 'Present', NULL, NOW(), NOW()),
(1, 1, '2024-03-04', 'Present', NULL, NOW(), NOW()),
(1, 1, '2024-03-06', 'Late', 'Arrived 10 minutes late.', NOW(), NOW()),
(1, 1, '2024-03-08', 'Absent', 'Called in sick.', NOW(), NOW()),
(2, 1, '2024-03-01', 'Present', NULL, NOW(), NOW()),
(2, 1, '2024-03-04', 'Present', NULL, NOW(), NOW()),
(4, 3, '2023-09-01', 'Present', NULL, NOW(), NOW()),
(4, 3, '2023-09-02', 'Present', NULL, NOW(), NOW());
```

in the `GradeComponents` table, it's Pre-Test 

in the `TraineeGrades` table, it's PreTest 

in the `View_TraineeComponentGrades` view, it's Pre-Test in the ComponentName column. 

in the `View_TraineePerformanceDetails` view, it's used as PreTestScore as a column name.

in the `vw_TraineeGrades` view, it's used as PreTest as a column name.



# Water Academy Management System - Consolidated Documentation

## Contents

1. System Overview
2. File Structure
3. Database Schema
4. Core Entities and Creation Sequence
5. User Roles and Permissions
6. Enrollment Process
7. Attendance Management
8. Grade Management
   - Grade Components
   - Grade Calculation Logic
   - Learning Gap Index (LGI)
9. Report System
   - Report Types
   - PDF Generation
   - Email Integration
10. PDF Report Implementation
    - Library Integration
    - Report Utility System
    - Email API Endpoint
    - Helper Functions
    - Email Templates System
11. File Relationships
12. Current Status and Next Steps
13. Known Issues and Troubleshooting
14. System Implementation Notes
15. Recent Fixes and Updates
16. Modal System Consolidation - May 2025
17. Modal System Updates and Fixes - June 2025
18. Mobile Sidebar and Module Loading Fixes - June 2025
19. UI Fixes and Module Loading Improvements - May 30, 2025
20. Comprehensive UI Fixes - May 30, 2025

## 1. System Overview

The Water Academy Management System is a comprehensive platform for managing educational operations at the Water Academy. It handles course management, trainee enrollment, attendance tracking, grade recording, and performance reporting. The system supports multiple user roles with different permissions and provides detailed analytics and reports on trainee and group performance.

The system is built with PHP for server-side processing, MySQL for data storage, and JavaScript/HTML/CSS for the frontend. It includes features for PDF report generation, email notifications, and data visualization.

## 2. File Structure

```
.
├── analysis_report.md                  # Markdown file likely containing analysis reports or summaries.
├── index.html                          # Main entry point for the web application, possibly a landing page or a redirect to login.php.
├── login.php                           # Handles user authentication, processing login credentials. Relates to includes/auth.php for authentication logic and assets/css/pages/login.css for styling.
├── logout.php                          # Handles user session termination.
├── test-page.html                      # A simple HTML file likely used for testing purposes.
├── api/
│   ├── group_wizard_api.php            # API for group wizard operations.
│   ├── REMOTE SQL.txt                  # Documentation for remote SQL server.
│   ├── SSH ACCESS.txt                  # SSH access information.
│   └── trainee_management_api.php      # API for trainee management operations.
├── assets/
│   ├── css/
│   │   ├── base.css                    # Fundamental CSS styles, likely defining basic typography, colors, and resets.
│   │   ├── core.css                    # Core layout styles.
│   │   ├── layout.css                  # Defines the overall layout structure of the application, including grid systems, header, sidebar, and footer positioning.
│   │   ├── temp.css                    # Temporary CSS file, possibly for testing new styles or features before integrating them into core stylesheets.
│   │   ├── theme-switcher.css          # Styles for theme switching functionality.
│   │   ├── _old/                       # Unused or deprecated stylesheets.
│   │   ├── components/                 # Directory containing modular CSS components
│   │   │   ├── buttons.css             # Styles specific to buttons used throughout the application.
│   │   │   ├── cards.css               # Styles for card-like UI elements including dashboard stats cards, action cards, and generic cards with theme compatibility.
│   │   │   ├── dropdowns.css           # Styles for dropdown menus with theme compatibility and Select2 integration.
│   │   │   └── modals.css              # Styles for modal dialogs with theme compatibility and BS-Stepper integration.
│   │   └── pages/
│   │       └── login.css               # Specific styles for the login page, overriding or extending general styles. Relates to login.php.
│   ├── dashjs/
│   │   ├── group_wizard.js             # JavaScript for group wizard functionality. Likely interacts with api/group_wizard_api.php and dashboards/group_wizard.php.
│   │   └── upcoming-events.js          # JavaScript for displaying upcoming events.
│   ├── fonts/
│   │   ├── michroma/                   # Michroma font files.
│   │   ├── Michroma webfont kit/       # Michroma webfont kit.
│   │   ├── ubuntu/                     # Ubuntu font files.
│   │   └── Ubuntu webfont kit/
│   ├── graphs/
│   │   ├── chart-design.pdf            # PDF document for chart design.
│   │   ├── Chart.js Configuration Template.js  # Template for Chart.js configurations.
│   │   ├── Dashboard Desktop.pdf       # PDF document for desktop dashboard design.
│   │   ├── Graphs for dahsboards.pdf   # PDF document for dashboard graphs.
│   │   └── KPI Review .pdf             # PDF document for KPI review.
│   ├── img/
│   │   ├── visualizerlogo.png          # Main visualizer logo.
│   │   ├── avatars/                    # User profile images.
│   │   ├── bg/                         # Background images.
│   │   ├── favicon/                    # Favicon files.
│   │   ├── icons/                      # UI icons.
│   │   ├── layouts/                    # Layout images.
│   │   └── logos/                      # Application logos.
│   ├── js/
│   │   ├── app.js                      # Main application JavaScript file, responsible for initializing core UI components and modules. It acts as an entry point for other JS modules like wa-modal.js, wa-table.js, theme-switcher.js, and sidebar-toggle.js.
│   │   ├── cache-management.js         # JavaScript for managing browser cache, possibly to ensure fresh content.
│   │   ├── config.js                   # Global JavaScript configuration settings for the frontend.
│   │   ├── sidebar-toggle.js           # JavaScript for handling the sidebar toggle functionality. Relates to includes/sidebar.php and assets/css/layout.css.
│   │   ├── switch-role.js              # JavaScript for handling user role switching. Likely interacts with dashboards/switch_role.php.
│   │   ├── theme-switcher.js           # JavaScript for switching between different themes (e.g., light/dark mode). Relates to assets/css/theme-switcher.css.
│   │   ├── wa-modal.js                 # Unified JavaScript for handling all modal dialogs across the application. This file consolidates functionality previously found in ui-modals.js and modal_handlers.js (now in _old or _UNUSED directories). It provides consistent modal showing, hiding, content updating and form submission handling with jQuery compatibility.
│   │   ├── wa-table.js                 # Unified JavaScript for handling table functionalities like sorting, filtering, and pagination. This centralizes table logic for consistency and provides smart type detection for sorting different data types.
│   │   ├── _old/                       # Directory for old or deprecated JavaScript files.
│   │   ├── modules/                    # Directory for JavaScript modules.
│   │   └── utils/                      # Directory for JavaScript utility functions.
│   └── userphotos/
│       ├── 1_1747373961.jpg            # Example user photo.
│       └── 1_1747373984.jpg            # Example user photo.
├── dashboards/
│   ├── ajax_get_courses_by_group.php       # AJAX endpoint to fetch courses based on a selected group. Used by frontend scripts for dynamic dropdowns.
│   ├── ajax_search_trainees.php            # AJAX endpoint to search for trainees. Used by frontend search functionalities.
│   ├── attendance_grades.php               # PHP page combining attendance and grades management. Interacts with submit_attendance.php, submit_grades.php, get_trainees_for_attendance.php, get_trainees_for_grades.php, and assets/js/wa-modal.js.
│   ├── attendance.php                      # Page for managing attendance records.
│   ├── check_tables.php                    # Script to verify database table structures.
│   ├── coordinator_dashboard.php           # Dashboard specific to the coordinator role.
│   ├── coordinators.php                    # Page for managing coordinators. Interacts with get_coordinator.php and assets/js/wa-modal.js.
│   ├── course_add.php                      # Page for adding new courses.
│   ├── course_delete.php                   # Script to delete a course.
│   ├── course_edit.php                     # Page for editing existing courses.
│   ├── courses.php                         # Page for listing and managing courses.
│   ├── fix_trainee_report.php              # Script to fix issues in trainee reports.
│   ├── get_available_courses.php           # Returns a list of available courses.
│   ├── get_coordinator.php                 # Fetches details of a specific coordinator, likely for modal content. Relates to dashboards/coordinators.php and assets/js/wa-modal.js.
│   ├── get_course_defaults.php             # Returns default settings for courses.
│   ├── get_courses.php                     # Returns courses, possibly filtered by group or other criteria.
│   ├── get_event_details.php               # Fetches details for a specific event.
│   ├── get_group_course.php                # Retrieves information about a group-course association.
│   ├── get_group_report_data.php           # Provides data for group performance reports. Used by dashboards/report_group_performance.php.
│   ├── get_group.php                       # Fetches details of a specific group.
│   ├── get_instructor_courses_admin.php    # Returns instructor courses for admin view.
│   ├── get_instructor_courses.php          # Returns courses assigned to an instructor.
│   ├── get_instructor.php                  # Fetches details of a specific instructor.
│   ├── get_instructors.php                 # Returns a list of instructors.
│   ├── get_trainee_data.php                # Fetches data for a specific trainee.
│   ├── get_trainees_for_attendance.php     # Fetches trainee data specifically for the attendance modal. Relates to dashboards/attendance_grades.php.
│   ├── get_trainees_for_grades.php         # Fetches trainee data specifically for the grades modal. Relates to dashboards/attendance_grades.php.
│   ├── get_users_by_role.php               # Returns users filtered by their role.
│   ├── grades.php                          # Page for managing grades.
│   ├── group_dashboard.php                 # Dashboard specific to a group.
│   ├── group_wizard.php                    # Interface for creating and managing groups using a wizard-like flow. Interacts with api/group_wizard_api.php and assets/dashjs/group_wizard.js.
│   ├── group-analytics-source.php          # Data source for group analytics.
│   ├── group-analytics.php                 # Page displaying group analytics.
│   ├── groups.php                          # Page for listing and managing groups.
│   ├── index.php                           # Main dashboard index page, likely the first page users see after login.
│   ├── instructor_actions.php              # Handles actions related to instructors (add, edit, delete).
│   ├── instructor_courses.php              # Page for managing courses assigned to instructors.
│   ├── instructor_dashboard.php            # Dashboard specific to the instructor role.
│   ├── instructors.php                     # Page for listing and managing instructors.
│   ├── manage_trainees_modal_content.php   # Provides HTML content for a modal used to manage trainees. Used by assets/js/wa-modal.js.
│   ├── profile.php                         # User profile page.
│   ├── remove_course_from_group.php        # Script to remove a course from a group.
│   ├── report_attendance_summary.php       # Generates a summary report of attendance. Relates to dashboards/report_functions.php and assets/js/report-print.js.
│   ├── report_functions.php                # Collection of helper functions used for generating various reports.
│   ├── report_group_performance.php        # Generates a performance report for a specific group. Relates to dashboards/get_group_report_data.php and assets/js/group-performance-report.js.
│   ├── report_trainee_performance.php      # Generates a performance report for a specific trainee. Relates to dashboards/report_functions.php and assets/js/report-print.js.
│   ├── reports.php                         # Page for managing and viewing various reports.
│   ├── send_report_email.php               # Handles sending reports via email. Used by assets/js/report-print.js.
│   ├── settings.php                        # Dashboard settings page.
│   ├── submit_attendance.php               # Processes attendance form submissions and updates the database. Relates to dashboards/attendance_grades.php.
│   ├── submit_grades.php                   # Processes grade form submissions and updates the database. Relates to dashboards/attendance_grades.php.
│   ├── switch_back.php                     # Script to switch back to a previous user role (e.g., from admin impersonation).
│   ├── switch_role.php                     # Script to switch user roles. Interacts with assets/js/switch-role.js.
│   ├── test_trainee_search.php             # Test page for trainee search functionality.
│   ├── trainee_dashboard.php               # Dashboard specific to the trainee role.
│   ├── trainee_report_all.php              # Generates a report showing all courses for a trainee.
│   ├── trainee_report.php                  # Generates an individual trainee course report.
│   ├── trainees.php                        # Page for listing and managing trainees.
│   ├── update_event_date.php               # Script to update an event's date.
│   ├── update_group.php                    # Script to update group information.
│   └── users.php                           # Page for managing users.
├── includes/
│   ├── auth.php                # Authentication functions.
│   ├── config - Copy.php       # Backup of the configuration file.
│   ├── config.php              # Database connection and general configuration settings.
│   ├── footer.php              # Common footer section included in most PHP pages. Includes modal implementations for role switching and group wizard.
│   ├── header.php              # Common header section included in most PHP pages, often includes CSS and JavaScript links.
│   ├── sidebar.php             # Navigation sidebar content, included in dashboard pages. Relates to assets/js/sidebar-toggle.js.
│   ├── u652025084_new_wa_db.sql # Database schema SQL dump.
│   ├── UI issues.md            # Markdown file documenting UI issues.
│   └── _bk/                    # Backup of include files.
│       ├── footer - Copy.php
│       ├── header - Copy.php
│       └── sidebar - Copy.php
├── logs/
│   ├── dummydata.md                  # Markdown file for dummy data notes.
│   ├── metrics_recommendations.txt   # Text file for metrics recommendations.
│   ├── modal_system_update.md        # Documentation for modal system updates.
│   ├── Project Logic 05.md           # Documentation related to project logic, possibly RBAC system.
│   ├── Projectinfo.md                # This very file, containing consolidated project documentation.
│   ├── _UNUSED/                      # Unused documentation files.
│   │   ├── UI_Fixes_May_30_2025.md
│   │   └── UI_FIXES_README.md
├── settings/
│   └── email_templates.php   # Email template management.
└── Srcs/
    ├── batch_load.php        # Batch data loading.
    ├── batch_process.php     # Batch data processing.
    ├── data_entry.php        # Data entry form.
    ├── index.php             # Source utilities index.
    └── show_tables.php       # Database table viewer.
```

## 3. Database Schema

### Tables

*   **Attendance**
    *   `AttendanceID` int(11)
    *   `TID` int(11)
    *   `GroupCourseID` int(11)
    *   `PresentHours` decimal(5,1)
    *   `ExcusedHours` decimal(5,1)
    *   `LateHours` decimal(5,1)
    *   `AbsentHours` decimal(5,1)
    *   `TakenSessions` int(11)
    *   `MoodlePoints` decimal(5,2)
    *   `AttendancePercentage` decimal(5,2)
    *   `Notes` text
    *   `RecordedBy` int(11)
    *   `CreatedAt` timestamp
    *   `UpdatedAt` timestamp
    *   `SessionDate` date
    *   `Status` enum('Present','Absent','Late','Excused')

*   **Courses**
    *   `CourseID` int(11)
    *   `CourseName` varchar(100)
    *   `CourseCode` varchar(20)
    *   `Description` text
    *   `DurationWeeks` int(11)
    *   `TotalHours` int(11)
    *   `CreatedAt` timestamp
    *   `UpdatedAt` timestamp
    *   `Status` enum('Active','Complete','Archived')

*   **EducationMetrics**
    *   `MetricID` int(11)
    *   `MetricName` varchar(100)
    *   `Acronym` varchar(20)
    *   `Description` text
    *   `Formula` text
    *   `CalculationMethod` text
    *   `InterpretationGuidelines` text
    *   `RelevantComponents` varchar(255)
    *   `DisplayOrder` int(11)
    *   `IsActive` tinyint(1)
    *   `Category` varchar(50)
    *   `ReferenceRange` varchar(100)
    *   `CreatedAt` timestamp
    *   `UpdatedAt` timestamp

*   **EmailTemplates**
    *   `TemplateID` int(11)
    *   `TemplateName` varchar(100)
    *   `TemplateCode` varchar(50)
    *   `Subject` varchar(200)
    *   `HtmlContent` text
    *   `TextContent` text
    *   `Description` text
    *   `CreatedAt` timestamp
    *   `UpdatedAt` timestamp

*   **Enrollments**
    *   `EnrollmentID` int(11)
    *   `TID` int(11)
    *   `GroupCourseID` int(11)
    *   `EnrollmentDate` date
    *   `Status` enum('Enrolled','Completed','Dropped','InProgress')
    *   `CompletionDate` date
    *   `FinalScore` decimal(5,2)
    *   `CertificatePath` varchar(255)
    *   `CreatedAt` timestamp
    *   `UpdatedAt` timestamp

*   **GradeComponents**
    *   `ComponentID` int(11)
    *   `ComponentName` varchar(100)
    *   `MaxPoints` decimal(5,2)
    *   `Description` text
    *   `IsDefault` tinyint(1)

*   **GroupCourses**
    *   `ID` int(11)
    *   `GroupID` int(11)
    *   `CourseID` int(11)
    *   `InstructorID` int(11)
    *   `StartDate` date
    *   `EndDate` date
    *   `Location` varchar(255)
    *   `ScheduleDetails` text
    *   `Status` varchar(50)
    *   `CreatedAt` timestamp
    *   `UpdatedAt` timestamp

*   **Groups**
    *   `GroupID` int(11)
    *   `GroupName` varchar(50)
    *   `Program` varchar(100)
    *   `Duration` int(11)
    *   `Semesters` int(11)
    *   `StartDate` date
    *   `EndDate` date
    *   `Description` text
    *   `Status` varchar(50)
    *   `Room` varchar(20)
    *   `CoordinatorID` int(11)
    *   `CreatedAt` timestamp
    *   `UpdatedAt` timestamp

*   **Permissions**
    *   `PermissionID` int(11)
    *   `PermissionName` varchar(100)
    *   `Description` text
    *   `Category` varchar(50)
    *   `CreatedAt` timestamp
    *   `UpdatedAt` timestamp

*   **RolePermissions**
    *   `RoleID` int(11)
    *   `PermissionID` int(11)
    *   `CreatedAt` timestamp

*   **Roles**
    *   `RoleID` int(11)
    *   `RoleName` varchar(50)
    *   `Description` text
    *   `CreatedAt` timestamp
    *   `UpdatedAt` timestamp

*   **TraineeGrades**
    *   `GradeID` int(11)
    *   `TID` int(11)
    *   `GroupCourseID` int(11)
    *   `ComponentID` int(11)
    *   `Score` decimal(5,2)
    *   `GradeDate` date
    *   `Comments` text
    *   `RecordedBy` int(11)
    *   `PositiveFeedback` text
    *   `AreasToImprove` text
    *   `CreatedAt` timestamp
    *   `UpdatedAt` timestamp
    *   `PreTest` decimal(5,2)
    *   `AttGrade` decimal(5,2)
    *   `Participation` decimal(5,2)
    *   `Quiz1` decimal(5,2)
    *   `Quiz2` decimal(5,2)
    *   `Quiz3` decimal(5,2)
    *   `QuizAv` decimal(5,2)
    *   `Final` decimal(5,2)
    *   `Total` decimal(5,2)

*   **Trainees**
    *   `TID` int(11)
    *   `GovID` varchar(50)
    *   `FirstName` varchar(50)
    *   `LastName` varchar(50)
    *   `Email` varchar(100)
    *   `Phone` varchar(20)
    *   `PhoneNumber` varchar(20)
    *   `Address` text
    *   `City` varchar(100)
    *   `Country` varchar(100)
    *   `DateOfBirth` date
    *   `EmergencyContactName` varchar(100)
    *   `EmergencyContactPhone` varchar(20)
    *   `GroupID` int(11)
    *   `UserID` int(11)
    *   `Status` enum('Active','Inactive','Graduated','Dropped')
    *   `Notes` text
    *   `CreatedAt` timestamp
    *   `UpdatedAt` timestamp

*   **Users**
    *   `UserID` int(11)
    *   `Username` varchar(50)
    *   `Password` varchar(255)
    *   `Email` varchar(100)
    *   `FirstName` varchar(50)
    *   `LastName` varchar(50)
    *   `Phone` varchar(20)
    *   `Specialty` varchar(255)
    *   `Qualifications` text
    *   `Biography` text
    *   `PreferredLanguage` varchar(50)
    *   `Department` varchar(100)
    *   `AvatarPath` varchar(255)
    *   `Role` enum('Super Admin','Admin','Instructor','Coordinator','Trainee')
    *   `Status` enum('Active','Inactive','Pending')
    *   `IsActive` tinyint(1)
    *   `CreatedAt` timestamp
    *   `UpdatedAt` timestamp
    *   `LastLogin` datetime
    *   `RoleID` int(11)

### Views

*   **View_GroupPerformanceMetrics**
    *   `GroupID` int(11)
    *   `GroupName` varchar(50)
    *   `GroupCourseID` int(11)
    *   `CourseID` int(11)
    *   `CourseName` varchar(100)
    *   `EnrolledTrainees` bigint(21)
    *   `AvgAttendance` decimal(5,1)
    *   `AvgFinalExamScore` decimal(5,1)
    *   `AvgPreTestScore` decimal(5,1)
    *   `AvgLGI` decimal(19,1)

*   **View_TraineeComponentGrades**
    *   `GradeID` int(11)
    *   `TID` int(11)
    *   `TraineeFullName` varchar(101)
    *   `TraineeEmail` varchar(100)
    *   `GroupCourseID` int(11)
    *   `StandardCourseName` varchar(100)
    *   `StandardCourseCode` varchar(20)
    *   `ComponentID` int(11)
    *   `ComponentName` varchar(100)
    *   `ComponentMaxPoints` decimal(5,2)
    *   `ComponentScore` decimal(5,2)
    *   `GradeDate` date
    *   `GradeComments` text
    *   `PositiveFeedback` text
    *   `AreasToImprove` text
    *   `GradedByInstructorID` int(11)
    *   `GradedByInstructorName` varchar(101)

*   **View_TraineeEnrollmentDetails**
    *   `TID` int(11)
    *   `TraineeFullName` varchar(101)
    *   `TraineeEmail` varchar(100)
    *   `TraineeGovID` varchar(50)
    *   `TraineePrimaryGroupID` int(11)
    *   `TraineePrimaryGroupName` varchar(50)
    *   `GroupCourseID` int(11)
    *   `CourseInstanceGroupID` int(11)
    *   `CourseInstanceGroupName` varchar(50)
    *   `StandardCourseID` int(11)
    *   `StandardCourseName` varchar(100)
    *   `StandardCourseCode` varchar(20)
    *   `InstanceStartDate` date
    *   `InstanceEndDate` date
    *   `InstructorID` int(11)
    *   `InstructorFullName` varchar(101)
    *   `InstructorEmail` varchar(100)
    *   `EnrollmentID` int(11)
    *   `EnrollmentDate` date
    *   `EnrollmentStatus` enum('Enrolled','Completed','Dropped','InProgress')
    *   `CompletionDate` date
    *   `StoredFinalScore` decimal(5,2)
    *   `CertificatePath` varchar(255)
    *   `CoordinatorID` int(11)
    *   `CoordinatorFullName` varchar(101)

*   **View_TraineePerformanceDetails**
    *   `TID` int(11)
    *   `TraineeFullName` varchar(101)
    *   `GroupID` int(11)
    *   `GroupName` varchar(50)
    *   `GroupCourseID` int(11)
    *   `CourseID` int(11)
    *   `CourseName` varchar(100)
    *   `AttendancePercentage` decimal(5,2)
    *   `PreTestScore` decimal(5,2)
    *   `ParticipationScore` decimal(5,2)
    *   `AvgQuizScore` decimal(9,6)
    *   `FinalExamScore` decimal(5,2)
    *   `CompositeScore` decimal(8,1)
    *   `LGI` decimal(19,1)

*   **vw_AttendanceSummary**
    *   `TID` int(11)
    *   `FullName` varchar(101)
    *   `CourseID` int(11)
    *   `CourseName` varchar(100)
    *   `GroupID` int(11)
    *   `GroupName` varchar(50)
    *   `TotalSessions` bigint(21)
    *   `PresentCount` decimal(22,0)
    *   `LateCount` decimal(22,0)
    *   `AbsentCount` decimal(22,0)
    *   `ExcusedCount` decimal(22,0)
    *   `AttendancePercentage` decimal(27,1)

*   **vw_Instructors**
    *   `InstructorID` int(11)
    *   `Username` varchar(50)
    *   `FirstName` varchar(50)
    *   `LastName` varchar(50)
    *   `FullName` varchar(101)
    *   `Email` varchar(100)
    *   `Phone` varchar(20)
    *   `Specialty` varchar(255)
    *   `Qualifications` text
    *   `IsActive` enum('Active','Inactive','Pending')
    *   `CreatedAt` timestamp
    *   `UpdatedAt` timestamp

*   **vw_TraineeGrades**
    *   `TID` int(11)
    *   `FullName` varchar(101)
    *   `CourseID` int(11)
    *   `CourseName` varchar(100)
    *   `GroupID` int(11)
    *   `GroupName` varchar(50)
    *   `PreTest` decimal(5,2)
    *   `AttGrade` decimal(5,2)
    *   `Participation` decimal(5,2)
    *   `Quiz1` decimal(5,2)
    *   `Quiz2` decimal(5,2)
    *   `Quiz3` decimal(5,2)
    *   `QuizAv` decimal(5,2)
    *   `Final` decimal(5,2)
    *   `Total` decimal(5,2)
    *   `LGI` decimal(11,1)
    *   `PositiveFeedback` text
    *   `AreasToImprove` text

### Key Attendance and Grades Files

**Note:** As of May 2025, modal_handlers.js has been moved to _UNUSED folder, with its functionality consolidated into wa-modal.js. The references below are maintained for historical documentation purposes.

1. **dashboards/attendance_grades.php**
   - Main interface for managing attendance and grades
   - Contains modals for entering attendance and grade data
   - Uses modal_handlers.js for calculations and validation

2. **dashboards/get_trainees_for_grades.php**
   - AJAX endpoint that loads trainee data for the grades modal
   - Returns trainee information and existing grade data

3. **dashboards/get_trainees_for_attendance.php**
   - AJAX endpoint that loads trainee data for the attendance modal
   - Returns trainee information and existing attendance data

4. **dashboards/submit_grades.php**
   - Processes grade form submissions
   - Validates input values against maximum limits
   - Stores grade data in the database

5. **dashboards/submit_attendance.php**
   - Processes attendance form submissions
   - Calculates attendance percentages and points
   - Stores attendance data in the database

6. **assets/js/wa-modal.js**
   - Handles client-side functionality for all modals across the application
   - Provides consistent modal triggering and event handling
   - Manages modal initialization, showing, hiding, and content updates

7. **assets/js/wa-table.js**
   - Provides unified table functionality across the application
   - Handles sorting, filtering, and row management
   - Supports responsive designs and theme compatibility

### Key Report-Related Files

1. **dashboards/report_trainee_performance.php**
   - Displays performance data for trainees across courses
   - Uses `dashboards/report_functions.php` and `assets/js/report-print.js`
   - Calls `ajax_search_trainees.php` and `ajax_get_courses_by_group.php`

2. **dashboards/report_group_performance.php**
   - Shows performance metrics for an entire group
   - Uses `assets/js/group-performance-report.js` and `assets/js/report-fixes.js`
   - Calls `dashboards/get_group_report_data.php` for data

3. **dashboards/report_attendance_summary.php**
   - Summarizes attendance data across groups and courses
   - Uses `dashboards/report_functions.php` and `assets/js/report-print.js`

4. **assets/js/report-print.js**
   - Handles PDF generation and email functionality for reports
   - Converts HTML reports to PDF using html2pdf library
   - Makes AJAX calls to `dashboards/send_report_email.php`

## 15. Recent Fixes and Updates

The system has recently undergone several important fixes and enhancements to improve functionality and user experience, particularly in the attendance and grades management modules and UI improvements.

### UI Improvements (May 2025)

1. **Theme System Enhancements**
   - Set dark theme as the system default
   - Improved dark mode text contrast for better readability
   - Enhanced all text colors in dark mode to ensure visibility
   - Fixed theme switcher functionality in header

2. **Table Improvements**
   - Added sorting functionality to tables (click column headers to sort)
   - Implemented live search filtering on tables
   - Standardized table styling across all pages
   - Fixed table header visibility issues in dark mode

3. **Admin Action Buttons Removal**
   - Removed "Add Group" button from groups.php (previously linked to add_group.php)
   - Removed "Add Trainee" button from trainees.php (previously linked to add_trainee.php)
   - Removed "Add Course" button from courses.php (previously linked to course_add.php)
   - Removed "Add Instructor" button from instructors.php (previously linked to instructor_actions.php)
   - Note: These functions will be reintroduced in future updates with improved security and validation

### Attendance and Grades Improvements (May 2025)

1. **Attendance Points Calculation Fix**
   - Removed the artificial 10-point maximum cap from attendance points calculation
   - Points now display the true calculated value using the formula: Points = (2 * Present Hours) + (1 * Excused Hours)
   - Updated to consistently display points with one decimal place for better precision
   - Modified calculateAttendance() function in modal_handlers.js to remove capping logic

2. **Data Validation for Grade Inputs**
   - Added client-side validation to prevent grades from exceeding maximum component values:
     - Pre-Test: max 50 points
     - Participation: max 10 points
     - Quizzes: max 30 points each
     - Final Exam: max 50 points
   - Implemented server-side validation in submit_grades.php to ensure data integrity
   - Added visual feedback (highlighting invalid fields) when validation fails
   - Displays warning toast notifications when users attempt to enter values exceeding limits

3. **Form Submission Improvements**
   - Fixed issue where submitting grades/attendance redirected to instructor_dashboard.php with "UNAUTHORIZED" message
   - Removed form action attributes to prevent standard form submission and page redirection
   - Updated AJAX submission handlers to properly handle success and error responses
   - Added proper error handling to display meaningful error messages to users
   - Implemented auto-saving functionality after 30 seconds of inactivity

4. **UI Enhancements**
   - Added column headers showing maximum expected values for each grade component
   - Made the Attendance Grade column read-only as it's a calculated item (10% of attendance percentage)
   - Implemented consistent decimal place formatting (one decimal place) for all numeric values
   - Added status indicator showing save status (Ready, Saving, Saved, Error)
   - Improved layout and styling for better usability on different screen sizes

5. **Permission Handling Updates**
   - Enhanced permission checking for grade and attendance submission
   - Improved error messaging for unauthorized access attempts
   - Ensured Super Admin role has full access to all grade/attendance functions

These improvements have resolved several critical issues that were affecting the grade and attendance management functionality, resulting in a more robust and user-friendly system. The changes maintain full compatibility with existing data structures while enhancing data validation and user experience.

## 12. Current Status and Next Steps

The Water Academy Management System has reached a stable production state with all core functionality implemented and operational. The system currently provides:

1. **Comprehensive User Management**
   - Role-based access control (RBAC) with fine-grained permissions
   - User profiles for instructors, coordinators, and administrators

2. **Course and Group Management**
   - Course creation and management with detailed metadata
   - Group management with coordinator assignment
   - Course-to-group assignment with instructor assignment

3. **Trainee Management**
   - Individual and batch trainee enrollment
   - Automatic course enrollment for trainees in groups
   - Trainee status tracking

4. **Attendance and Grade Recording**
   - Spreadsheet-like interface for data entry
   - Automatic calculations for attendance percentages and points
   - Grade component tracking with validation
   - Learning Gap Index (LGI) calculation

5. **Reporting System**
   - Trainee performance reports
   - Group performance reports
   - Attendance summary reports
   - PDF export functionality
   - Email report sharing

### Next Steps and Future Enhancements

The following enhancements are planned for future releases:

1. **Mobile Responsiveness Improvements**
   - Optimize all interfaces for mobile devices
   - Create responsive data entry forms for grades and attendance

2. **Advanced Analytics Dashboard**
   - Implement additional performance metrics
   - Create visualizations for trend analysis
   - Add comparative analytics across groups and courses

3. 

4. **Integration Capabilities**
   - Develop API endpoints for integration with other systems
   - Add import/export functionality for external LMS systems
   - Implement webhook notifications for system events

5. **Enhanced Reporting**
   - Add customizable report templates
   - Implement scheduled report generation and distribution
   - Create data export options in multiple formats (CSV, Excel, etc.)

6. **System Optimization**
   - Performance tuning for large datasets
   - Code refactoring for improved maintainability
   - Enhanced caching for frequently accessed data

The development team will prioritize these enhancements based on user feedback and operational requirements.

## 16. Modal System Consolidation - May 2025

### Overview

The modal and table functionality in the Water Academy Visualizer project has been consolidated to improve consistency, reduce code duplication, and fix interaction issues with action buttons on several pages. Two new main files have been created:

- `wa-modal.js`: Unified modal handling across the application
- `wa-table.js`: Unified table functionality across the application

### Key Changes

1. **Consolidated JavaScript Files**
   - Created `wa-modal.js` to replace `ui-modals.js` and components from `modal_handlers.js`
   - Created `wa-table.js` to consolidate table functionality previously scattered across multiple files
   - Moved deprecated files to `assets/js/_UNUSED` directory

2. **Enhanced Modal Handling**
   - Implemented a more reliable event-based system for modal triggers
   - Fixed issues with modal activation through action buttons
   - Added support for both Bootstrap's data attributes and custom attributes
   - Improved error handling and debugging output
   - Implemented automatic modal initialization

3. **Enhanced Table Functionality**
   - Improved sorting with smart type detection (text, numbers, dates)
   - Enhanced theme compatibility
   - Added row management functions (add, update, delete)
   - Implemented visible row tracking

4. **Page-Specific Updates**
   - Fixed modal functionality on `instructors.php`
   - Fixed modal functionality on `coordinators.php`
   - Created `get_coordinator.php` to support AJAX loading of coordinator details
   - Updated event handling to use the new WA_Modal event system

### Migration Changes

1. **Button Attributes**
   - Changed from inline `onclick` handlers to data attributes:
     - `data-modal-target="modalId"`
     - `data-modal-action="show"`
     - Additional data attributes for user-specific data (e.g., `data-instructor-id`)

2. **Event Handlers**
   - Changed from jQuery-style event handlers to native JavaScript event listeners
   - Updated to use the new WA_Modal events (`wa.modal.beforeShow`, `wa.modal.shown`, etc.)

3. **Modal Content Loading**
   - Improved AJAX loading of modal content
   - Added loading indicators and error handling

### Usage Examples

#### Modal Triggers

```html
<!-- Old style (removed) -->
<button onclick="loadInstructorDetails('123'); WA_Modal.show('editInstructorModal');"
        data-instructor-id="123">Edit</button>

<!-- New style -->
<button data-modal-target="editInstructorModal" 
        data-modal-action="show"
        data-instructor-id="123">Edit</button>
```

#### Modal Event Handlers

```javascript
// Event handler for modal opening
document.getElementById('editInstructorModal').addEventListener('wa.modal.beforeShow', function(event) {
    // Get data from the button that triggered the modal
    const instructorId = event.detail.userData.instructorId;
    
    // Load data via AJAX
    loadInstructorDetails(instructorId);
});
```

#### Table Initialization

```javascript
// Initialize table with sorting and filtering
WA_Table.initTable('instructorsTable');

// Search input for filtering
<input type="text" data-search-input data-search-target="instructorsTable">

// Sortable column headers
<th data-sort="name">Name <i class="bx bx-sort-alt-2 text-muted"></i></th>
```

## 17. Modal System Updates and Fixes - June 2025

### Overview

Following the May 2025 Modal System Consolidation, we encountered some compatibility issues with the new modal implementation in certain environments. These issues have been resolved by switching from the native Bootstrap Modal implementation to a jQuery-based approach for better compatibility across different environments.

### Key Issues Identified

1. **Script Loading Conflicts:**
   - Console errors indicated problems with ES6 module imports in the application
   - Bootstrap Modal object was not properly defined when trying to use it directly
   - Several script loading errors were occurring in sequence

2. **Solution Approach:**
   - Moved from Bootstrap Modal API (`new bootstrap.Modal()`) to jQuery Modal API (`$('#modalId').modal('show')`)
   - Eliminated dependencies on the Bootstrap object which was causing conflicts
   - Improved modal loading flow, especially for AJAX-loaded content

### Implementation Details

1. **Modal Triggering Pattern:**
   ```javascript
   // Instead of this (which was causing errors):
   const modal = new bootstrap.Modal(document.getElementById('modalId'));
   modal.show();
   
   // We now use this jQuery-based approach:
   $('#modalId').modal('show');
   ```

2. **Improved Flow for AJAX Content:**
   - Show the modal first to provide immediate user feedback
   - Then load content via AJAX with a loading spinner
   - Update modal content after data is loaded

3. **Retained Data Attribute Pattern:**
   - Still using `data-modal-target="modalId"` and other data attributes for consistency
   - Event handlers attach to these attributes for modal triggering

### Files Updated

1. `dashboards/instructors.php` - Modified to use jQuery for modals
2. `dashboards/coordinators.php` - Modified to use jQuery for modals 
3. `dashboards/get_coordinator.php` - New file added to support AJAX loading of coordinator details

### Known Issues

This implementation pattern should be applied to other pages in the system that use modals. Pages that might need similar updates include:

- `dashboards/groups.php`
- `dashboards/trainees.php`
- `dashboards/courses.php`
- `dashboards/users.php`

### Implementation Note for Developers

**IMPORTANT:** When working with modals in this system, DO NOT use the native Bootstrap Modal API directly. Instead, use the jQuery Modal API as shown above. The system has several script loading conflicts that prevent the native Bootstrap Modal from working correctly. The jQuery approach is more compatible with the current environment.

## 18. Mobile Sidebar and Module Loading Fixes - June 2025

### Overview

In June 2025, several critical fixes were implemented to address issues with the mobile sidebar and JavaScript module loading. These changes improve the mobile experience by ensuring the sidebar is fully functional and clickable on mobile devices, and by resolving module loading errors in the JavaScript console.

### Key Issues Identified

1. **Mobile Sidebar Issues:**
   - Sidebar appeared offset from the left side of the screen on mobile
   - Menu items were unclickable when the sidebar was toggled
   - The sidebar overlay didn't work properly
   - Visual display issues with blurred and unresponsive sidebar

2. **JavaScript Module Loading Errors:**
   - Console errors about import statements outside of modules
   - Unexpected 'export' token errors
   - Issues with Bootstrap and jQuery dependencies

### Solution Approach

1. **CSS Fixes:**
   - Enhanced mobile-specific CSS in main.css with higher specificity and !important flags
   - Fixed z-index conflicts between sidebar, overlay, and content
   - Corrected pointer-events to ensure menu items are clickable
   - Improved transition and transform properties for smoother animations

2. **JavaScript Improvements:**
   - Created module-fix.js to handle JavaScript module loading errors
   - Implemented menu-clickability-fix.js for ensuring menu items are clickable
   - Updated sidebar-toggle.js for better mobile interactions
   - Modified layout-fixes.js to better handle mobile viewport calculations

### Implementation Details

1. **Mobile Sidebar CSS Fixes:**
   ```css
   @media (max-width: 1199.98px) {
     .menu.menu-vertical {
       transform: translateX(-100%) !important;
       width: var(--sidebar-width) !important;
       z-index: 2000 !important;
       pointer-events: auto !important;
       /* Additional styling for mobile sidebar */
     }
     
     .menu.menu-vertical.show,
     .layout-menu-expanded .menu.menu-vertical {
       transform: translateX(0) !important;
       box-shadow: 0 0 20px rgba(0, 0, 0, 0.2) !important;
     }
     
     /* Other mobile-specific styling */
   }
   ```

2. **Module Loading Fixes:**
   - Added a polyfill in module-fix.js for import/export statements
   - Created global namespace objects for commonly used libraries
   - Added error handling to prevent module-related errors from stopping execution

3. **Menu Clickability Improvements:**
   - Applied explicit styles to ensure menu items are clickable
   - Added proper z-index values to maintain correct layering
   - Implemented event handlers to ensure click events are properly captured

### Files Updated

1. **CSS Files:**
   - `assets/css/main.css` - Added mobile sidebar fixes at the end of the file

2. **JavaScript Files:**
   - `assets/js/module-fix.js` (new) - Handles JavaScript module loading errors
   - `assets/js/menu-clickability-fix.js` (new) - Ensures menu items are clickable
   - `assets/js/sidebar-toggle.js` - Updated for better mobile interaction
   - `assets/js/layout-fixes.js` - Modified to handle mobile viewport better

3. **PHP Files:**
   - `includes/header.php` - Updated to include the new JavaScript files

### Developer Notes

1. **Module Loading:**
   - The system now includes shims for problematic libraries to prevent module loading errors
   - This approach is backwards compatible and doesn't break existing functionality

2. **Mobile Testing:**
   - Always test the sidebar on mobile devices or using browser device emulation
   - Pay special attention to clickability of menu items and proper overlay display

3. **Future Enhancements:**
   - Consider implementing touch gesture support (swipe to open/close sidebar)
   - Further optimize loading of JavaScript and CSS for improved performance

These changes have significantly improved the mobile experience by ensuring the sidebar is fully functional and visually correct on mobile devices, while also resolving JavaScript console errors related to module loading.

## 19. UI Fixes and Module Loading Improvements - May 30, 2025

### Overview of Fixes

On May 30, 2025, comprehensive fixes were implemented to resolve UI distortions and JavaScript errors in the Water Academy Visualizer project. These fixes focused on four key areas: JavaScript module loading, modal functionality, theme switching, and stats cards display.

### Issues Fixed

1. **JavaScript Module Loading Errors**
   - `Uncaught ReferenceError: require is not defined in bootstrap.min.js`
   - `Uncaught TypeError: Cannot read properties of undefined (reading 'getCssVar')`
   - Message port closing errors

2. **UI Distortions**
   - Missing stats cards on the home page (index.php)
   - Theme switching (dark/light) not working properly
   - Non-functional modals

### Implementation Approach

#### JavaScript Module Loading

The core issue was that Bootstrap was trying to use CommonJS-style `require()` calls in a browser environment where that's not available. Our fixes:

1. Created a shim for `require()` that returns appropriate global objects
2. Defined global objects like `jQuery`, `$`, `Popper`, and `bootstrap` before they're needed
3. Dynamically loaded libraries in the correct order with proper dependencies
4. Implemented error catching to prevent script execution from stopping

#### Modals

Modal functionality was broken due to Bootstrap initialization issues. Our fixes:

1. Ensured Bootstrap is properly loaded before initializing modals
2. Created a shim for `bootstrap.Modal` that works even before Bootstrap loads
3. Re-wired all modal triggers and dismiss buttons with proper event handlers
4. Provided fallback mechanisms if Bootstrap modal functions fail

#### Theme Switching

Theme switching wasn't applying correctly to all elements. Our fixes:

1. Updated both HTML and body classes for theme consistency
2. Set explicit data-theme attributes
3. Forced style recalculation through small DOM operations
4. Specifically targeted elements like cards, tables, and navbars to ensure proper theming
5. Implemented theme-specific styles in CSS for elements like stats cards

#### Stats Cards

Stats cards were missing due to styling issues. Our fixes:

1. Created dedicated CSS for stats cards with proper theming (integrated into main.css)
2. Implemented responsive design for different viewport sizes
3. Added hover effects and transitions for better UX
4. Ensured proper color variables and fallbacks

### Files Modified/Created

#### JavaScript Fixes

1. **`module-fix.js`** (Updated)
   - Provided shims for required JavaScript modules
   - Created required global objects (jQuery, Popper, Bootstrap)
   - Handled dynamic loading of libraries in the correct order
   - Added error catching for module-related errors
   - Implemented `window.Helpers` with required methods like `getCssVar`

2. **`modal-fix.js`** (New)
   - Specifically targeted modal functionality issues
   - Provided fallback mechanisms when Bootstrap modal isn't loaded yet
   - Fixed modal triggers and dismiss buttons
   - Ensured proper z-index and event handling

3. **`theme-switcher.js`** (Updated)
   - Improved theme switching mechanism
   - Updated both HTML and body classes for theme consistency
   - Added forced style application through small reflows
   - Explicitly updated card backgrounds and other themed elements
   - Dispatched custom events to notify other scripts of theme changes

4. **`config.js`** (Updated)
   - Removed dependency on `window.Helpers.getCssVar`
   - Added safe fallback values for all CSS variables
   - Implemented a local `getCssVar` function

#### CSS Fixes

1. **`main.css`** (Updated)
   - Added specific styling for the stats cards on the home page
   - Implemented theme-specific styles for both light and dark modes
   - Included responsive design adjustments
   - Added styling for action cards and events cards

#### PHP Files

1. **`header.php`** (Updated)
   - Added script for modal fixes
   - Added explicit theme switcher script
   - Removed duplicate loading of module-fix.js
   - Added cache-busting version parameters to all custom scripts

### Future Recommendations

1. **Library Bundling**: Consider using a bundler like Webpack or Rollup to properly handle dependencies
2. **Module System**: Implement a proper ES modules system instead of relying on global objects
3. **CSS Variables**: Expand the use of CSS variables for better theme consistency
4. **Performance Optimization**: Reduce redundant style calculations and DOM manipulations

## 20. Comprehensive UI Fixes - May 30, 2025

### Overview

This section provides a comprehensive overview of the fixes implemented to resolve UI issues in the Water Academy Visualizer project on May 30, 2025, particularly focusing on:

1. Mobile sidebar and layout issues
2. Theme switching functionality
3. Stats cards display problems
4. Modal functionality
5. Dropdown menu functionality
6. JavaScript loading and dependency issues

### Key Files Modified

#### JavaScript Files

1. **`module-fix.js`** - Comprehensive fix for JavaScript module loading issues
   - Added proper shims for CommonJS-style require/exports
   - Created global objects for libraries (jQuery, Bootstrap, Popper)
   - Added error handling for module-related errors
   - Implemented dynamic library loading with proper order

2. **`theme-switcher.js`** - Enhanced theme switching functionality
   - Fixed theme application to all UI elements
   - Improved cookie and localStorage handling
   - Added event dispatching for theme changes
   - Fixed dark/light icon toggling

3. **`modal-fix.js`** - Fixed modal dialog functionality
   - Added jQuery-based approach as primary method
   - Implemented fallback for native DOM when jQuery isn't available
   - Fixed z-index issues with modals
   - Added proper backdrop handling

4. **`menu-clickability-fix.js`** - Fixed menu item clickability
   - Applied proper z-index to menu items
   - Ensured pointer-events are set correctly
   - Added explicit click handlers for menu items
   - Fixed mobile toggle button behavior

5. **`sidebar-toggle.js`** - Fixed sidebar toggle functionality
   - Improved mobile sidebar behavior
   - Fixed overlay handling
   - Added proper state persistence
   - Fixed resize handling for sidebar

6. **`dashboard-init.js`** - Dashboard initialization and component handling
   - Applied theme to dashboard components
   - Fixed card spacing and layout
   - Initialized Bootstrap components
   - Added fallbacks for dropdown functionality

7. **`layout-fixes.js`** - Fixed layout and structure issues
   - Resolved mobile layout issues
   - Fixed page width when sidebar appears
   - Applied proper spacing between components
   - Enhanced responsiveness

#### CSS Files

1. **`main.css`** - Comprehensive styling fixes
   - Fixed mobile sidebar issues
   - Enhanced theme variables and application
   - Fixed stats cards and dashboard components
   - Improved responsiveness
   - Fixed card spacing and layout

#### PHP Files

1. **`header.php`** - Fixed script loading and theme initialization
   - Added explicit loading of jQuery, Popper, and Bootstrap
   - Ensured scripts load in the correct order
   - Fixed theme attribute initialization

### Detailed Changes

#### Mobile Sidebar Fixes

- Fixed sidebar positioning and z-index on mobile devices
- Added proper overlay for mobile sidebar
- Ensured menu items are clickable
- Fixed sidebar toggle button functionality
- Improved transitions and animations

#### Theme Switching Fixes

- Fixed theme variables for light and dark modes
- Ensured theme is applied to all UI components
- Added proper persistence through cookies and localStorage
- Fixed theme toggle button functionality
- Applied theme to modals, dropdowns, and forms

#### Stats Cards Fixes

- Fixed card spacing and layout
- Applied proper theming to cards
- Enhanced responsiveness for different viewport sizes
- Fixed icon colors and sizing
- Improved hover effects

#### Modal Functionality Fixes

- Fixed modal showing and hiding
- Added proper backdrop handling
- Fixed z-index issues
- Ensured modals work without Bootstrap
- Added proper theming to modals

#### Dropdown Menu Fixes

- Fixed dropdown positioning
- Ensured dropdowns are clickable
- Fixed z-index issues
- Added proper theming to dropdowns
- Fixed user dropdown menu in header

#### JavaScript Loading Fixes

- Implemented proper script loading order
- Added shims for missing functionality
- Fixed module-related errors
- Enhanced error handling
- Added fallbacks for library dependencies

### Testing

These fixes have been tested across different viewport sizes and browsers. The key areas verified include:

1. Mobile sidebar functionality
2. Theme switching between dark and light modes
3. Stats cards display on the dashboard
4. Modal functionality throughout the application
5. Dropdown menus, especially the user dropdown in the header
6. Overall layout and spacing

### Browser Compatibility

The fixes have been designed to work across modern browsers:

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

### Future Recommendations

1. **Bundling**: Consider using a modern bundler like Webpack or Rollup to handle JavaScript dependencies properly
2. **Module System**: Implement a proper ES modules system instead of relying on global objects
3. **CSS Variables**: Expand the use of CSS variables for better theme consistency
4. **Performance Optimization**: Reduce redundant style calculations and DOM manipulations
5. **Code Splitting**: Split JavaScript files based on functionality for better performance

## 21. Comprehensive UI Fixes - June 2025 Implementation

### Overview

This section documents the implementation of a comprehensive UI fix plan in June 2025, addressing JavaScript module loading conflicts, standardizing modal interactions, fixing mobile sidebar issues, and ensuring theme consistency across UI components.

### Files Created

*   `assets/js/utils/logger.js`: Provides a structured logging utility for debugging and tracking application flow.
*   `assets/js/utils/config-manager.js`: Centralized configuration management system for feature flags, library paths, and debug settings.
*   `assets/js/utils/dependency-manager.js`: Manages the loading order and dependencies of JavaScript libraries, including shims for CommonJS `require()` calls.
*   `assets/js/module-fix.js`: Updated to integrate with `dependency-manager.js` and provide necessary global object shims (jQuery, Popper, Bootstrap).
*   `assets/js/utils/modal-upgrade.js`: Automatically converts old `onclick` modal triggers to new `data-modal-target` and `data-modal-action` attributes for standardized event handling.
*   `assets/css/components/mobile-sidebar.css`: Contains specific CSS rules to fix mobile sidebar positioning, visibility, and pointer events, ensuring proper responsiveness.
*   `logs/Comprehensive UI Fix Implementation Plan.md`: Detailed plan outlining the step-by-step implementation of these UI fixes.

### Files Modified

*   `includes/header.php`: Updated to include the new utility scripts (`logger.js`, `config-manager.js`, `dependency-manager.js`, `module-fix.js`, `modal-upgrade.js`) in the correct loading order. Also updated the overall script loading sequence to ensure core libraries are loaded after the new utilities.
*   `assets/js/wa-modal.js`: Modified to leverage `WA_Config` for feature flags and to implement a more robust modal show/hide logic, prioritizing jQuery-based interactions with fallbacks to native Bootstrap or manual DOM manipulation.
*   `assets/css/layout.css`: Updated to import `assets/css/components/mobile-sidebar.css` to apply the mobile sidebar fixes.
*   `assets/js/sidebar-toggle.js`: Enhanced to correctly toggle sidebar classes and manage the overlay, improving mobile interaction and responsiveness.
*   `assets/js/theme-switcher.js`: Refined to utilize `WA_Config` for theme persistence and default settings, ensuring consistent theme application across HTML and body elements.

### Key Changes and Improvements

1.  **Centralized Utilities**: Introduction of `logger.js`, `config-manager.js`, and `dependency-manager.js` provides a robust foundation for managing application behavior, configurations, and external library loading.
2.  **Robust JavaScript Loading**: The new dependency management system resolves previous `require()` errors and ensures that jQuery, Popper.js, and Bootstrap are loaded in the correct sequence, preventing conflicts and `undefined` errors.
3.  **Standardized Modals**: `wa-modal.js` now offers a more reliable and consistent modal experience, with automatic upgrade of old triggers and intelligent fallbacks for showing/hiding modals.
4.  **Improved Mobile Sidebar**: Dedicated CSS and updated JavaScript ensure the mobile sidebar functions correctly, with proper positioning, clickable menu items, and a functional overlay.
5.  **Consistent Theming**: `theme-switcher.js` now integrates with the new configuration system, ensuring that theme changes are applied consistently across all UI components and persist across sessions.

These changes aim to significantly improve the overall stability, responsiveness, and user experience of the Water Academy Management System's UI.

# Projectinfo\_Part1.md

## 1. Overview

This document describes the current Water Academy reporting system and serves as a basis for migrating the UI to Tailwind CSS + Alpine JS + Chart.js. It includes:

* Complete file/folder structure with purposes
* Database tables and views used by front-end

**Intended audience:** Developers or AI agents (e.g., Gemini) performing the UI migration.

## 2. File Structure & Purpose

Below is the full project hierarchy. Each item includes a short description of its role in the system.

```
/ (project root)
├── index.php                  # Landing page (redirects to login if not authenticated)
├── login.php                  # User login form and authentication logic
├── logout.php                 # Logout script, destroys session
├── README.md                  # High-level project overview and setup instructions
├── Projectinfo.md             # This documentation file (detailed current system info)
├── docs/
│   ├── Original_Projectinfo.md  # Backup of this original doc before migration
│   └── WaterAcademy_UI_TransferGuide.md  # New migration guide
├── includes/                  # Shared PHP includes and components
│   ├── auth.php               # Contains isLoggedIn(), getUserRoleID(), hasPermission(), require_permission()
│   ├── config.php             # Database connection and global config (defines $baseAssetPath = '/assets/')
│   ├── header.php             # Current Bootstrap-based header (will be replaced by Tailwind header)
│   ├── sidebar.php            # Current Bootstrap-based sidebar (will be replaced)
│   ├── footer.php             # Current footer include
│   ├── report_functions.php   # PHP helpers for computing LGI, composite scores, attendance % (keep unchanged)
│   ├── email_functions.php    # Helpers to send email, HTML templates (used by report-print)
│   └── components/            # Reusable UI fragments (to be updated)
│       └── kpi-card.php       # Tailwind+Alpine KPI card partial (new component)
├── dashboards/                # All report, dashboard, and data-entry pages
│   ├── ajax_get_courses_by_group.php      # Returns JSON for courses by group (used in dropdowns)
│   ├── ajax_search_trainees.php           # Returns JSON results for trainee search/filter
│   ├── attendance.php                    # Attendance entry page (shows the spreadsheet UI)
│   ├── coordinator_dashboard.php         # Dashboard for coordinators (charts + KPIs)
│   ├── group-analytics.php               # Alias for report_group_performance.php (old Bootstrap UI)
│   ├── get_group_report_data.php         # Loads PHP data ($groups array) for group report views
│   ├── instructor_dashboard.php          # Instructor dashboard (attendance & grade links)
│   ├── manage_groups.php                 # Page to create/edit/delete groups (wizard flow)
│   ├── manage_trainees.php               # Page to add/edit/delete trainees (calls stored procedure)
│   ├── report_attendance_summary.php     # Displays attendance summary report (table + chart)
│   ├── report_group_performance.php      # Displays group performance report (doughnut charts + table)
│   ├── report_trainee_performance.php    # Displays trainee performance report (KPIs + table)
│   ├── report_print_modal.php            # Partial used to show "Export PDF / Email" modal
│   ├── report_print.php                  # AJAX endpoint to generate PDF base64 and send via email
│   ├── attendance_grades.php             # Grade entry page for instructors (per‐component input)
│   ├── trainees.php                      # List of all trainees (Admin view) with filters
│   └── user_management.php               # Create/edit/delete user accounts (SuperAdmin & Admin)
├── assets/
│   ├── css/
│   │   ├── (DELETED) old Sneat/Bootstrap CSS files
│   │   ├── tailwind.css     # Generated Tailwind CSS (build artifact or CDN)
│   │   └── custom.css       # Minimal overrides / component styles (explicit `@apply` usage)
│   ├── js/
│   │   ├── (DELETED) old jQuery-driven JS modules
│   │   ├── alpine.min.js    # Alpine.js (via CDN)
│   │   ├── chart.min.js     # Chart.js (via CDN)
│   │   └── app.js           # Tailwind+Alpine state management and chart initialization
│   ├── vendor/
│   │   └── html2pdf/
│   │       └── html2pdf.bundle.min.js  # Used by report-print.js to generate PDFs
│   ├── images/
│   │   ├── waLogoBlue.png   # Water Academy logo used in header/sidebar
│   │   └── (other icons, backgrounds, avatars)
│   └── fonts/               # (If any custom fonts; otherwise omitted)
├── settings/                 # Email templates & settings UI (Admin only)
│   └── email_templates.php   # Manage saved email templates (used by report_print)
├── Srcs/                     # Legacy scripts (database batch import, data entry) – untouched
│   ├── batch_load.php
│   ├── batch_process.php
│   ├── data_entry.php
│   └── show_tables.php
├── logs/
│   └── Migration_Log.txt      # (Optional: record of migration steps and notes)
└── u652025084_new_wa_db.sql  # Full database schema (for local setup)
```

### 2.1 Deleted Files Overview

List of old Sneat/Bootstrap and jQuery-dependent assets removed:

* **CSS** (moved or deleted):

  * badges.css, buttons.css, cards.css, layout.css, navbar.css, progress.css, sidebar.css, tables.css, typography.css, style.css
* **JS** (moved or deleted):

  * dashboards-analytics.js, group-performance-report.js, report-fixes.js, any jQuery plugins (e.g., Bootstrap bundle).

**Note:** Instead of tracking each filename individually, the migration process removes entire directories of old assets. Always verify no references remain in PHP includes or HTML templates.

## 3. Database Schema & Front-End Usage

### 3.1 Core Tables

Below is a summary of tables, primary columns, and how they are used by the UI.

| Table Name           | Key Columns                                                                                                               | UI Usage / Data Contract                                                                                                  |
| -------------------- | ------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------- |
| **Users**            | UserID, Username, PasswordHash, RoleID, FirstName, LastName, Email, Status                                                | Used by `auth.php`. Front-end displays `FirstName LastName` in header. Permissions mapped via RoleID → `hasPermission()`. |
| **Roles**            | RoleID, RoleName                                                                                                          | Mapped to user actions: SuperAdmin, Admin, Instructor, Coordinator, Trainee                                               |
| **Courses**          | CourseID, CourseName, CourseCode, Description, DurationWeeks, TotalHours, Status                                          | Shown in dropdowns when assigning courses to groups.                                                                      |
| **Groups**           | GroupID, GroupName, Program, StartDate, EndDate, CoordinatorID, Status                                                    | Used in `manage_groups.php` and as data source in `report_group_performance.php`.                                         |
| **GroupCourses**     | ID, GroupID, CourseID, InstructorID, StartDate, EndDate, Status                                                           | Populated in “Assign Courses” wizard. Used in attendance & grade entry.                                                   |
| **Trainees**         | TID, GovID, FirstName, LastName, Email, GroupID, Status, DateOfBirth, Gender                                              | Used in `/trainees.php`, `attendance.php`, `report_trainee_performance.php`.                                              |
| **Enrollments**      | EnrollmentID, TID, GroupCourseID, EnrollmentDate, Status, FinalScore                                                      | Auto-created by stored procedure `AddTraineesWithEnrollments`. Front-end shows FinalScore in trainee reports.             |
| **GradeComponents**  | ComponentID, ComponentName, MaxPoints, IsDefault                                                                          | Used to build dynamic form fields in `attendance_grades.php`.                                                             |
| **TraineeGrades**    | GradeID, TID, GroupCourseID, ComponentID, Score, GradeDate, Comments                                                      | Instructor enters grades here. Aggregated into `LGI` in `report_trainee_performance.php`.                                 |
| **Attendance**       | AttendanceID, TID, GroupCourseID, PresentHours, ExcusedHours, LateHours, AbsentHours, TakenSessions, AttendancePercentage | Populated via `attendance.php`. Used in `report_attendance_summary.php`.                                                  |
| **EmailTemplates**   | TemplateID, TemplateName, TemplateCode, Subject, HtmlContent, TextContent                                                 | Managed via `settings/email_templates.php`. Used by `report_print.php`.                                                   |
| **EducationMetrics** | MetricID, MetricName, Acronym, Formula, Description                                                                       | Defines available metrics (e.g., LGI formula). Used in `report_functions.php`.                                            |

### 3.2 Views

Views are read-only aggregates that front-end queries to simplify JOIN logic.

| View Name                           | Fields                                                                                     | Usage in UI                                                                                                      |
| ----------------------------------- | ------------------------------------------------------------------------------------------ | ---------------------------------------------------------------------------------------------------------------- |
| **View\_GroupPerformanceMetrics**   | GroupID, GroupName, TraineeCount, AvgFinalExamScore, AvgAttendance, AvgLGI                 | Data source for `report_group_performance.php`: `$groups` & aggregated `$avgScore`, `$avgAttendance`, `$avgLGI`. |
| **View\_TraineePerformanceDetails** | TID, Name, GroupName, PreTestScore, QuizAverage, FinalExamScore, AttendancePercentage, LGI | Data source for `report_trainee_performance.php`.                                                                |
| **vw\_AttendanceSummary**           | GroupName, CourseName, PresentCount, AbsentCount, AttendancePercentage                     | Data source for `report_attendance_summary.php`.                                                                 |
| **View\_TraineeComponentGrades**    | TID, ComponentName, Score, GradeDate                                                       | Used to show per-component grades in `attendance_grades.php`.                                                    |
| **vw\_TraineeEnrollmentDetails**    | TID, GroupCourseID, EnrollmentDate, Status                                                 | Helps display enrollment info in trainee management.                                                             |

---

*End of Projectinfo\_Part1.md*

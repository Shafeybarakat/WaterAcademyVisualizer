# 🤖 AI Assistant Integration Plan for Water Academy Dashboard

### 📅 Date Started: 2025-06-05
### 👨‍💼 Developer: Gemini via VS Code (under supervision of Shifo)

---

## 🧠 Prompt to Gemini (Read This First)

You are helping build a modular, role-aware AI assistant for a PHP-based dashboard used in a training environment. The assistant should:

### 🔐 Understand Roles (from `$_SESSION['role']`):
- `super_admin`: full access
- `admin`: full access (except system settings)
- `coordinator`: view group and trainee reports
- `instructor`: view + submit grades and attendance for their assigned groups

---

### 📁 Existing Functionality (in `/dashboards/`):
Includes but not limited to:
- `view_group_report.php`
- `view_trainee_report.php`
- `submit_grades.php`
- `submit_attendance.php`
- `view_instructors.php`
- `view_courses.php`
- `view_groups.php`
(Add more by scanning the folder)

---

## ✅ Task Objectives

1.  **Scan `/dashboards/` folder and other relevant PHP files**
    *   Extract all action files (both UI pages and backend functions)
    *   Map each to its function and permitted roles
    *   Save the table under “📋 Action Role Mapping” section below ✅

2.  **Create `/modules/ai_agent/` folder** with these files:
    *   `ai_agent.php`: the assistant UI shown after login
    *   `ai_chat.js`: chat logic, button handling, input box, AJAX handler
    *   `ai_router.php`: receives requests, checks permissions, includes matching `/dashboards/` file
    *   `role_actions_map.php`: config of available actions per role (used by both chat and router)
    *   (Optional future override layer: `modules/ai_agent/actions/`)
    *   `components/`: reusable themed UI blocks:
        *   `table.php`: styled tables for both light/dark mode
        *   `modal.php`: generic modal popup for questions

3.  **Frontend Behavior**
    *   Show floating assistant on `dashboards/index.php` (landing page after login)
    *   **Dashboard Layout:** The AI assistant will be integrated into a modified `index.php` template. This template will use the existing header and footer, but will *not* include the sidebar. It will initially display the status cards at the top.
    *   **Dynamic Content Area:** When the user requests something via the AI assistant, the page will reload the same template (without the status cards), displaying only a large container card where the requested UI element (e.g., a report, a form) will be injected.
    *   **Multistep Interactions:** Any multistep interactions (e.g., selecting a group and course for a report) will be handled via sequenced modals. The AI will trigger a modal with the necessary input fields (e.g., dropdowns), and upon user selection, the modal will disappear, and the AI agent will pull the respective report page, passing the selected IDs.
    *   Greet user based on session name and role
    *   Display buttons with common actions (per role from `role_actions_map.php`)
    *   Allow free input like: "Submit attendance for Group A"
    *   Send input to `ai_router.php` and inject output into `#main-content` of a large card (assuming `#main-content` is the ID of the large container card).
    *   Support theme switching (light/dark) via a toggle in the header
    *   Header should also show logged-in user's name and role with avatar image, which links to user dropdown menu.

4.  **Backend Behavior**
    *   `ai_router.php` checks action + role
    *   Includes file from `/dashboards/` if access allowed
    *   Else shows error: “You don’t have permission for that.”
    *   **Keyword Matcher:** For now, the keyword matcher will provide users with choices based on their role. For example, coordinators should only be offered viewing reports for groups and trainees registered to their names. This implies a mapping of keywords/phrases to available actions, and then filtering these actions based on the user's role and specific data access (e.g., groups/courses assigned to them).
    *   Fallback: Redisplay buttons on unknown input
    *   If additional input is needed (e.g., which group?), trigger `modal.php`

5.  **Table + Modal Output**
    *   All output tables must use the system's unified table styling
    *   Must auto-switch between light/dark modes
    *   Modal prompts (for follow-up input) must be clean, mobile-friendly, and match the current theme

6.  **Logging**
    *   Log every action triggered via AI:
        *   Timestamp
        *   User ID
        *   Role
        *   Triggered action
        *   Raw input
    *   **Proposed Logging Mechanism:** Logs will be stored in a dedicated log file within the `logs/` directory, e.g., `logs/ai_actions.log`. The format will be JSON lines for easy parsing, including the specified fields.

---

## 🚀 Implementation Strategy

**Crucial Note:** To avoid disturbing the existing UI, all PHP files identified for integration with the AI assistant (both UI pages and backend functions) will be *copied* into the new `/modules/ai_agent/` directory. All modifications required for AI integration (e.g., removing the sidebar, adapting for dynamic content injection) will be applied only to these copied files. The original files in `/dashboards/` and other directories will remain untouched.

---

## 📋 Action Role Mapping

| Function | PHP File | Allowed Roles |
|---|---|---|
| View Group Report | `dashboards/reports.php` (for filters), `dashboards/report_group_performance.php`, `dashboards/group-analytics.php` | super_admin, admin, coordinator, instructor |
| View Trainee Report | `dashboards/reports.php` (for filters), `dashboards/report_trainee_performance.php`, `dashboards/report_attendance_summary.php` | super_admin, admin, coordinator, instructor |
| Submit Grades | `dashboards/attendance_grades.php`, `dashboards/submit_grades.php` | super_admin, admin, instructor |
| Submit Attendance | `dashboards/attendance_grades.php`, `dashboards/submit_attendance.php` | super_admin, admin, instructor |
| View Courses | `dashboards/courses.php` | super_admin, admin |
| Add Course | `dashboards/course_add.php` | super_admin, admin |
| Edit Course | `dashboards/course_edit.php` | super_admin, admin |
| Delete Course | `dashboards/course_delete.php` | super_admin, admin |
| View Groups | `dashboards/groups.php` | super_admin, admin, coordinator |
| Manage Groups (Wizard) | `dashboards/group_wizard.php` | super_admin, admin |
| View Instructors | `dashboards/instructors.php` | super_admin, admin |
| Manage Instructors | `dashboards/instructor_actions.php` | super_admin, admin |
| View Trainees | `dashboards/trainees.php` | super_admin, admin, coordinator |
| Manage Trainees | `dashboards/manage_trainees_modal_content.php` | super_admin, admin, coordinator |
| View Users | `dashboards/users.php` | super_admin, admin |
| Manage Users | `dashboards/users.php` | super_admin, admin |
| View Profile | `dashboards/profile.php` | super_admin, admin, coordinator, instructor |
| System Settings | `dashboards/settings.php` | super_admin, admin |
| Switch Role | `dashboards/switch_role.php`, `dashboards/switch_back.php` | super_admin, admin |
| API: Get Courses by Group | `dashboards/ajax_get_courses_by_group.php` | All (with access to filters) |
| API: Search Trainees | `dashboards/ajax_search_trainees.php` | All (with access to search) |
| API: Get Trainee Data | `dashboards/get_trainee_data.php` | All (with access to reports) |
| API: Get Instructor Courses (Admin) | `dashboards/get_instructor_courses_admin.php` | super_admin, admin |
| API: Get Instructor Courses | `dashboards/get_instructor_courses.php` | instructor, super_admin, admin |
| API: Get Trainees for Attendance | `dashboards/get_trainees_for_attendance.php` | super_admin, admin, instructor |
| API: Get Trainees for Grades | `dashboards/get_trainees_for_grades.php` | super_admin, admin, instructor |
| API: Get Group Data | `dashboards/get_group.php` | super_admin, admin, coordinator |
| API: Get Group Report Data | `dashboards/get_group_report_data.php` | super_admin, admin, coordinator, instructor |
| API: Get Event Details | `dashboards/get_event_details.php` | All (with access to events) |
| API: Send Report Email | `dashboards/send_report_email.php` | super_admin, admin, coordinator |
| API: Update Event Date | `dashboards/update_event_date.php` | super_admin, admin |
| API: Update Group | `dashboards/update_group.php` | super_admin, admin |
| API: Remove Course from Group | `dashboards/remove_course_from_group.php` | super_admin, admin |
| API: Get Users by Role | `dashboards/get_users_by_role.php` | super_admin, admin |
| API: Get Coordinator | `dashboards/get_coordinator.php` | super_admin, admin |
| API: Get Course Defaults | `dashboards/get_course_defaults.php` | super_admin, admin |
| API: Get Courses (General) | `dashboards/get_courses.php` | super_admin, admin |
| API: Get Group Course | `dashboards/get_group_course.php` | super_admin, admin, coordinator, instructor |
| API: Get Available Courses | `dashboards/get_available_courses.php` | super_admin, admin |
| API: Group Wizard API | `api/group_wizard_api.php` | super_admin, admin |
| API: Trainee Management API | `api/trainee_management_api.php` | super_admin, admin |
| Utility: Check Tables | `dashboards/check_tables.php` | super_admin |
| Utility: Fix Trainee Report | `dashboards/fix_trainee_report.php` | super_admin, admin |
| Utility: Test Trainee Search | `dashboards/test_trainee_search.php` | super_admin, admin |
| Settings: Email Templates | `settings/email_templates.php` | super_admin, admin |

✅ **Step 1 Complete?** — [ ] *(Fill this when done)*

---

## 🏗️ AI Assistant Module Structure

```
/modules/ai_agent/
├── ai_agent.php
├── ai_chat.js
├── ai_router.php
├── role_actions_map.php
├── components/
│   ├── table.php
│   └── modal.php
└── actions/ (optional custom overrides)
```

✅ **Step 2 Complete?** — [ ]  

---

## 🧪 Self-Check Mechanism for Gemini

For each step, Gemini must:
1. Print a short confirmation in the console/output
2. Mark the corresponding checkbox in this file with ✅
3. Stop and request human confirmation before proceeding to next phase (optional toggle)

---

## 🚀 Future Expansions
- Voice input/output using Web Speech API
- Language switch for multilingual UI
- Smart recommendations (e.g., suggest report when grades are low)
- Chat memory for previous requests within session

✅ **All Phases Complete?** — [ ]

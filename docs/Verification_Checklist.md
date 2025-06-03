# Verification Checklist for Water Academy UI Migration

This document outlines detailed test cases for each migrated page to ensure functionality, responsiveness, and correct data display after the UI migration to Tailwind CSS + Alpine.js + Chart.js.

## I. General Checks (Apply to all migrated pages)

- [ ] **No Console Errors:** Open browser DevTools (F12) and check the Console tab. There should be no JavaScript errors (e.g., `Uncaught ReferenceError`, `jQuery is not defined`).
- [ ] **No Network Errors:** In DevTools, go to the Network tab. Ensure all CSS, JS, and image files load with a 200 OK status. No 404 errors for old Bootstrap/jQuery assets.
- [ ] **Responsive Layout:**
    - Resize the browser window to various widths (e.g., 320px, 768px, 1024px, 1440px).
    - Confirm elements stack correctly on small screens and expand appropriately on larger screens.
    - Ensure no horizontal scrollbars appear unexpectedly.
- [ ] **RBAC (Role-Based Access Control):**
    - Log in as different user roles (Super Admin, Admin, Instructor, Coordinator).
    - Verify that only permitted sidebar links and page content are visible/accessible for each role.
    - Attempt to access a page without permission (e.g., Instructor trying to access `settings/email_templates.php`) and confirm redirection or access denied message.
- [ ] **Alpine.js Functionality:**
    - For pages using Alpine.js (e.g., header, sidebar, attendance entry), interact with toggles, dropdowns, and inputs.
    - Confirm `x-show`, `x-data`, `x-transition`, `@click`, `x-model` directives work as expected.
    - In DevTools Console, check `Alpine.rawData('layout')` (or other Alpine components) to ensure data is reactive.

## II. Page-Specific Checklists

### 1. `dashboards/report_group_performance.php`

- [ ] **KPI Cards:**
    - [ ] Verify the three KPI cards (Avg Score, Avg Attendance, Avg LGI) are displayed.
    - [ ] Check that the values on the cards match the expected aggregated data (if sample data is used, verify against that).
    - [ ] Confirm icons (`bx bx-chart-pie`, `bx bx-calendar-check`, `bx bx-line-chart`) are visible.
- [ ] **Doughnut Charts:**
    - [ ] Confirm three doughnut charts are rendered within the KPI cards.
    - [ ] Verify chart slices visually represent the percentage values (e.g., 85% score shows a large slice for 85 and small for 15).
    - [ ] Check chart responsiveness on resize.
- [ ] **Groups Table:**
    - [ ] Verify the table displays group names, trainee counts, and average metrics.
    - [ ] Check that table headers are correctly aligned and styled.
    - [ ] Confirm horizontal scrolling is enabled for the table on small screens if content overflows.
    - [ ] Verify data in table rows matches expected sample data.

### 2. `dashboards/report_trainee_performance.php`

- [ ] **KPI Cards:**
    - [ ] Verify the four KPI cards (Pre-Test Avg, Quiz Avg, Final Exam Avg, LGI) are displayed.
    - [ ] Check values on cards match expected aggregated data.
    - [ ] Confirm icons (`bx bx-pen-alt`, `bx bx-question-circle`, `bx bx-file-alt`, `bx bx-line-chart`) are visible.
- [ ] **Doughnut Charts:**
    - [ ] Confirm four doughnut charts are rendered within the KPI cards.
    - [ ] Verify chart slices visually represent the percentage values.
    - [ ] Check chart responsiveness on resize.
- [ ] **Trainees Table:**
    - [ ] Verify the table displays trainee names, group names, and all performance metrics (Pre-Test, Quizzes, Final, Attendance %, LGI).
    - [ ] Check that table headers are correctly aligned and styled.
    - [ ] Confirm horizontal scrolling is enabled for the table on small screens.
    - [ ] Verify data in table rows matches expected sample data.

### 3. `dashboards/report_attendance_summary.php`

- [ ] **Bar Chart:**
    - [ ] Confirm the bar chart for attendance summary is rendered.
    - [ ] Verify bar heights correspond to attendance percentages for each group/course.
    - [ ] Check chart responsiveness on resize.
- [ ] **Attendance Summary Table:**
    - [ ] Verify the table displays group name, course name, present/absent counts, and attendance percentage.
    - [ ] Check that table headers are correctly aligned and styled.
    - [ ] Confirm horizontal scrolling is enabled for the table on small screens.
    - [ ] Verify data in table rows matches expected sample data.

### 4. `dashboards/attendance.php`

- [ ] **Course Selection Form (Initial State):**
    - [ ] Verify "Select Course" form is visible if `group_course_id` is not in URL.
    - [ ] Confirm Group dropdown populates correctly with assigned groups.
    - [ ] Verify Course dropdown is initially disabled.
    - [ ] Select a group → confirm Course dropdown enables and populates with courses for that group.
    - [ ] Select a course and click "Continue" → confirm page reloads with attendance entry table.
- [ ] **Attendance Entry Table (After Course Selection):**
    - [ ] Verify table displays trainee names, input fields for Present/Late/Excused/Absent hours, Total Sessions, and calculated Attendance %.
    - [ ] Change values in input fields (e.g., Present Hours, Absent Hours) → confirm Attendance % updates dynamically.
    - [ ] Click "Save" button for an individual row → confirm `save-attendance` event is dispatched and (if backend is functional) data is saved and page reloads.
    - [ ] Click "Save All Attendance" button → confirm form submission.
- [ ] **Error/No Trainees Messages:**
    - [ ] If no courses assigned, verify "No courses assigned" alert is shown.
    - [ ] If no trainees in selected course, verify "No trainees found" alert is shown.

### 5. `dashboards/instructor_dashboard.php`

- [ ] **KPI Cards:**
    - [ ] Verify the three KPI cards (My Courses, Pending Grades, Pending Attendance) are displayed.
    - [ ] Check values on cards match expected dummy data.
    - [ ] Confirm icons (`bx bx-book`, `bx bx-edit`, `bx bx-calendar-day`) are visible.
- [ ] **Assigned Courses Overview:**
    - [ ] Verify the "Assigned Courses Overview" section is present.
    - [ ] Check that the dummy list of courses is displayed.

### 6. `dashboards/coordinator_dashboard.php`

- [ ] **Statistics Cards:**
    - [ ] Verify the four statistics cards (Groups, Trainees, Attendance, Final Scores) are displayed.
    - [ ] Check values on cards match expected dummy data.
    - [ ] Confirm icons (`bx bx-group`, `bx bx-user`, `bx bx-calendar-check`, `bx bx-bar-chart-alt-2`) are visible.
- [ ] **My Groups Overview:**
    - [ ] Verify the "My Groups Overview" section is present.
    - [ ] Check that the dummy list of groups is displayed.

---

*End of Verification_Checklist.md*

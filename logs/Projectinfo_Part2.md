### Documentation Verification

- [ ] Section 4: Entity Creation Workflow & Dependencies complete
- [ ] Section 5: User Roles & Permissions Mapping complete
- [ ] Section 6: Known Issues & Troubleshooting complete
- [ ] Section 7: References & Links complete

# Projectinfo\_Part2.md

## 4. Entity Creation Workflow & Dependencies

To ensure data integrity, entities must be created in the following order. Each step’s completion is a precondition for the next.

1. **Create Users (Admin & Instructor & Coordinator)**

   * In `user_management.php`, create user accounts.
   * Must assign RoleID to each (1: SuperAdmin, 2: Admin, 3: Instructor, 4: Coordinator).
   * **Dependency:** No other pages function until a logged-in user exists.

2. **Create Courses**

   * In `manage_courses.php` (or `courses.php`), add course templates.
   * **Dependency:** Groups cannot be created until courses exist.

3. **Create Groups**

   * In `manage_groups.php`, create a new group, selecting an existing Coordinator (CoordinatorID).
   * **Post-action:** After group creation, UI should prompt “Assign Course to Group” (creates `GroupCourses`).

4. **Assign Courses to Groups (Populate GroupCourses)**

   * In `assign_courses.php`, select a Group and add one or more Courses with Instructor assignment.
   * **Dependency:** Trainees cannot be added until at least one `GroupCourses` record exists for the group.

5. **Add Trainees**

   * In `manage_trainees.php`, select a group, enter trainee details (Name, GovID, Email, etc.).
   * Upon submission, back-end calls `AddTraineesWithEnrollments` stored procedure, which populates `Enrollments` automatically.
   * **Dependency:** Attendance and Grades pages now have valid `EnrollmentID` to reference.

6. **Record Grades & Attendance**

   * **Attendance Page (`attendance.php`):** Mark Present/Absent/Late hours per trainee per GroupCourse.
   * **Grades Page (`attendance_grades.php`):** Enter component scores (PreTest, Quizzes, FinalExam).
   * Back-end calculates `AttendancePercentage` and `LGI` (Learning Gain Index) via helper functions in `report_functions.php`.

7. **Run Reports**

   * **Group Performance (`report_group_performance.php`):** Reads `View_GroupPerformanceMetrics`.
   * **Trainee Performance (`report_trainee_performance.php`):** Reads `View_TraineePerformanceDetails`.
   * **Attendance Summary (`report_attendance_summary.php`):** Reads `vw_AttendanceSummary`.
   * Users can “Export PDF” or “Email Report” (calls `report_print.php`) for each.

> **ADD:** In the transfer guide, implement UI controls that are disabled or hidden until these preconditions are met (e.g., “Add Trainee” button only active if `GroupCourses.count > 0`).

## 5. User Roles & Permissions Mapping

| Role            | Can Access Pages & Actions                                                                                                                                                                                                                                                                                                                       |
| --------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **SuperAdmin**  | Full access to everything: `user_management.php`, `manage_courses.php`, `manage_groups.php`, `assign_courses.php`, `manage_trainees.php`, `attendance.php`, `attendance_grades.php`,<br> `report_group_performance.php`, `report_trainee_performance.php`, `report_attendance_summary.php`, `settings/email_templates.php`, and all other pages. |
| **Admin**       | All pages except the Settings panel: can manage users, courses, groups, trainees, attendance, grades, and run all reports (but no access to `settings/email_templates.php`).                                                                                                                                                                     |
| **Instructor**  | Limited to: `instructor_dashboard.php`, `attendance.php` (only for groups where `InstructorID = currentUserID`), `attendance_grades.php` (same group‐filter),<br> `report_trainee_performance.php` (only for trainees in their assigned groups).                                                                                                 |
| **Coordinator** | Limited to: `coordinator_dashboard.php`, `report_group_performance.php` (only for groups where `CoordinatorID = currentUserID`),<br> `report_attendance_summary.php` (same group‐filter). No data‐entry permissions.                                                                                                                             |
| **Trainee**     | No direct access to the system UI. Their data is visible only through Instructor or Coordinator reports.                                                                                                                                                                                                                                         |

> **ADD:** Each sidebar link or button in the new Tailwind UI must be wrapped in `<?php if (hasPermission('…')): ?> … <?php endif; ?>` with the correct permission names:
>
> * `access_user_management` → user\_management.php
> * `access_course_management` → manage\_courses.php
> * `manage_groups` → manage\_groups.php
> * `assign_group_courses` → assign\_courses.php
> * `add_trainees` → manage\_trainees.php
> * `record_attendance` → attendance.php
> * `record_grades` → attendance\_grades.php
> * `access_group_reports` → report\_group\_performance.php
> * `access_trainee_reports` → report\_trainee\_performance.php
> * `access_attendance_summary` → report\_attendance\_summary.php
> * `access_settings` → settings/email\_templates.php

Make sure `auth.php` defines these permissions and maps them to RoleID.

## 6. Known Issues & Troubleshooting

1. **jQuery Conflicts**: Multiple pages load jQuery from different versions. This caused `$ is not defined` errors. Removing all jQuery-dependent scripts (e.g., `dashboards-analytics.js`) resolves these.
2. **Bootstrap Overrides**: Sneat theme’s global selectors (e.g., `.card`, `.btn`) conflicted with custom styles. Hence migrating to utility-first Tailwind CSS eliminates these conflicts.
3. **Modal Z-index Bugs**: Existing Bootstrap modals interfered with other components. Rewriting modals in Alpine (`x-show` + `x-transition`) fixes layering.
4. **Chart Resizing Glitches**: Old Chart.js initialization in `group-performance-report.js` needed `window.resize` hacks. In Tailwind + Alpine, use `x-effect` or `Chart.resize()` within `DOMContentLoaded` for responsive charts.
5. **File Path Inconsistencies**: Some pages referenced `/assets/js/app.js` while others used `<?= $baseAssetPath ?>js/app.js`. Standardize all to use `<?= $baseAssetPath ?>js/app.js` with `$baseAssetPath = '/assets/';` in `config.php`.

## 7. References & Links

* Migration Guide: [docs/WaterAcademy\_UI\_TransferGuide.md](docs/WaterAcademy_UI_TransferGuide.md)
* Backup of Original Doc: [docs/Original\_Projectinfo.md](docs/Original_Projectinfo.md)

---

*End of Projectinfo\_Part2.md*

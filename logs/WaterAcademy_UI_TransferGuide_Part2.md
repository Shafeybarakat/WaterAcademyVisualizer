### Migration Status

- [ ] Phase 4: Page-by-Page Migration & Testing
  - [ ] Step 4.1: Migrate report_group_performance.php
  - [ ] Step 4.2: Migrate report_trainee_performance.php
  - [ ] Step 4.3: Migrate report_attendance_summary.php
  - [ ] Step 4.4: Migrate attendance.php
  - [ ] Step 4.5: Migrate instructor_dashboard.php & coordinator_dashboard.php
- [ ] Phase 5: Finalization, Testing, & Documentation
  - [ ] Step 5.1: Write Verification Checklists
  - [ ] Step 5.2: Remove Deprecated Files Permanently
  - [ ] Step 5.3: Create Pull Request & Code Review
  - [ ] Step 5.4: Post-Migration Smoke Testing & Performance Check

## III. Phase-by-Page Migration Plan (continued)

### Phase 4: Page-by-Page Migration & Testing (Steps 4.1–4.5)

#### Step 4.1: Migrate `report_group_performance.php`

1. **Header & Sidebar Includes**

   ```php
   <?php
   require_once __DIR__ . '/../includes/auth.php';
   require_permission('access_group_reports');
   require_once __DIR__ . '/../includes/config.php';
   $pageTitle = 'Group Performance Report';
   include __DIR__ . '/../includes/header.php';
   ?>
   ```
2. **Main Content:**

   ```php
   <main class="p-6 bg-gray-50 flex-1">
     <!-- KPI Cards Row -->
     <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
       <?php
       // $avgScore, $avgAttendance, $avgLGI are defined in get_group_report_data.php
       $cards = [
         ['Avg Score', 'fas fa-chart-pie text-blue-500', $avgScore, 'chartAvgScore'],
         ['Avg Attendance', 'fas fa-calendar-check text-green-500', $avgAttendance, 'chartAvgAttendance'],
         ['Avg LGI', 'fas fa-chart-line text-yellow-500', $avgLGI, 'chartAvgLGI'],
       ];
       foreach ($cards as $c) {
         [$title, $iconClass, $value, $chartId] = $c;
         include __DIR__ . '/../includes/components/kpi-card.php';
       }
       ?>
     </div>

     <!-- Groups Table -->
     <div class="bg-white rounded-lg shadow overflow-x-auto">
       <table class="min-w-full divide-y divide-gray-200">
         <thead class="bg-gray-100">
           <tr>
             <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Group Name</th>
             <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"># Trainees</th>
             <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Score</th>
             <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Attendance</th>
             <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg LGI</th>
           </tr>
         </thead>
         <tbody class="bg-white divide-y divide-gray-200">
           <?php foreach ($groups as $g): ?>
             <tr>
               <td class="px-4 py-4 whitespace-nowrap"><?= htmlspecialchars($g['GroupName']) ?></td>
               <td class="px-4 py-4 whitespace-nowrap"><?= $g['TraineeCount'] ?></td>
               <td class="px-4 py-4 whitespace-nowrap"><?= $g['AvgFinalExamScore'] ?>%</td>
               <td class="px-4 py-4 whitespace-nowrap"><?= $g['AvgAttendance'] ?>%</td>
               <td class="px-4 py-4 whitespace-nowrap"><?= $g['AvgLGI'] ?>%</td>
             </tr>
           <?php endforeach; ?>
         </tbody>
       </table>
     </div>
   </main>
   ```
3. **Footer Include:**

   ```php
   <?php include __DIR__ . '/../includes/footer.php'; ?>
   ```
4. **Chart.js Initialization (`assets/js/app.js`):**

   ```js
   document.addEventListener('DOMContentLoaded', () => {
     function initDoughnutChart(chartId, dataValue, colorHex) {
       const ctx = document.getElementById(chartId);
       if (!ctx) return;
       new Chart(ctx, {
         type: 'doughnut',
         data: {
           labels: [chartId, 'Remaining'],
           datasets: [{
             data: [dataValue, 100 - dataValue],
             backgroundColor: [colorHex, '#E5E7EB'],
           }]
         },
         options: {
           cutout: '70%',
           plugins: { legend: { display: false } }
         }
       });
     }

     initDoughnutChart('chartAvgScore', <?= $avgScore ?>, '#3B82F6');
     initDoughnutChart('chartAvgAttendance', <?= $avgAttendance ?>, '#10B981');
     initDoughnutChart('chartAvgLGI', <?= $avgLGI ?>, '#F59E0B');
   });
   ```
5. **Verification:**

   * **Sample Data Test:**

     ```sql
     INSERT INTO Groups (GroupName, Program, StartDate, EndDate, CoordinatorID, Status)
       VALUES ('Test Group A', 'Program X', '2025-07-01', '2025-12-31', 3, 'Active');
     INSERT INTO Enrollments (TID, GroupCourseID, EnrollmentDate, Status)
       VALUES (1, 1, '2025-07-10', 'Enrolled');
     ```

     * Load `report_group_performance.php` as a Coordinator of that group.
     * Expect three doughnut charts showing 0% (since no grades/attendance data), and a table row for "Test Group A".
   * **Console Check:** No errors. All scripts and CSS load successfully.
   * **Responsive Check:** At `<768px`, cards stack vertically; table scrolls horizontally.
6. **Git Commit:**

   ```
   feat: migrate report_group_performance.php to Tailwind + Alpine + Chart.js (sample tests passing)
   ```

#### Step 4.2: Migrate `report_trainee_performance.php`

1. **Header & Sidebar Includes** (same pattern as Step 4.1 but permission: `access_trainee_reports`).
2. **KPI Cards Row:** Show Pre-Test Avg, Quiz Avg, Final Exam Avg, LGI.

   ```php
   <main class="p-6 bg-gray-50 flex-1">
     <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
       <?php
       $cards = [
         ['Pre-Test Avg', 'fas fa-pen-alt text-indigo-500', $avgPreTest, 'chartPreTest'],
         ['Quiz Avg', 'fas fa-question-circle text-purple-500', $avgQuiz, 'chartQuizAvg'],
         ['Final Exam Avg', 'fas fa-file-alt text-red-500', $avgFinal, 'chartFinalExam'],
         ['LGI', 'fas fa-chart-line text-yellow-500', $avgLGI, 'chartLGI'],
       ];
       foreach ($cards as $c) {
         [$title, $iconClass, $value, $chartId] = $c;
         // For items without charts, set $chartId = '' and modify kpi-card to hide canvas when ID is empty
         include __DIR__ . '/../includes/components/kpi-card.php';
       }
       ?>
     </div>
   ```
3. **Trainees Table:**

   ```php
     <div class="bg-white rounded-lg shadow overflow-x-auto">
       <table class="min-w-full divide-y divide-gray-200">
         <thead class="bg-gray-100">
           <tr>
             <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trainee Name</th>
             <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Group Name</th>
             <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pre-Test Score</th>
             <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quiz Avg</th>
             <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Final Exam</th>
             <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance %</th>
             <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">LGI</th>
           </tr>
         </thead>
         <tbody class="bg-white divide-y divide-gray-200">
           <?php foreach ($trainees as $t): ?>
             <tr>
               <td class="px-4 py-4 whitespace-nowrap"><?= htmlspecialchars($t['Name']) ?></td>
               <td class="px-4 py-4 whitespace-nowrap"><?= htmlspecialchars($t['GroupName']) ?></td>
               <td class="px-4 py-4 whitespace-nowrap"><?= $t['PreTestScore'] ?>%</td>
               <td class="px-4 py-4 whitespace-nowrap"><?= $t['QuizAverage'] ?>%</td>
               <td class="px-4 py-4 whitespace-nowrap"><?= $t['FinalExamScore'] ?>%</td>
               <td class="px-4 py-4 whitespace-nowrap"><?= $t['AttendancePercentage'] ?>%</td>
               <td class="px-4 py-4 whitespace-nowrap"><?= $t['LGI'] ?>%</td>
             </tr>
           <?php endforeach; ?>
         </tbody>
       </table>
     </div>
   </main>
   ```
4. **Chart Initialization (`app.js`):**

   ```js
   document.addEventListener('DOMContentLoaded', () => {
     initDoughnutChart('chartPreTest', <?= $avgPreTest ?>, '#6366F1');
     initDoughnutChart('chartQuizAvg', <?= $avgQuiz ?>, '#8B5CF6');
     initDoughnutChart('chartFinalExam', <?= $avgFinal ?>, '#EF4444');
     initDoughnutChart('chartLGI', <?= $avgLGI ?>, '#F59E0B');
   });
   ```
5. **Verification:**

   * **Sample Data Test:** Insert one trainee:

     ```sql
     INSERT INTO Trainees (GovID, FirstName, LastName, Email, GroupID, Status)
       VALUES ('GOV12345', 'Test', 'Trainee', 'test@example.com', 1, 'Active');
     INSERT INTO TraineeGrades (TID, GroupCourseID, ComponentID, Score, GradeDate)
       VALUES (LAST_INSERT_ID(), 1, 1, 80, NOW());
     INSERT INTO Attendance (TID, GroupCourseID, PresentHours, AbsentHours, TakenSessions, AttendancePercentage)
       VALUES (LAST_INSERT_ID(), 1, 20, 0, 20, 100);
     ```

     * Load `report_trainee_performance.php` → verify the four chart cards and the row for "Test Trainee".
   * **Responsive Check:** Cards stack 1×4 on mobile, 2×2 on tablets, 4 columns on desktop.
   * **Console Check:** No errors.
6. **Git Commit:**

   ```
   feat: migrate report_trainee_performance.php to Tailwind + Alpine + Chart.js (sample tests passing)
   ```

#### Step 4.3: Migrate `report_attendance_summary.php`

1. **Includes:**

   ```php
   <?php
   require_once __DIR__ . '/../includes/auth.php';
   require_permission('access_attendance_summary');
   require_once __DIR__ . '/../includes/config.php';
   $pageTitle = 'Attendance Summary';
   include __DIR__ . '/../includes/header.php';
   ?>
   ```
2. **Content:**

   ```php
   <main class="p-6 bg-gray-50 flex-1">
     <!-- Bar Chart for Attendance % per Group -->
     <div class="bg-white rounded-lg shadow p-4 mb-8 w-full">
       <canvas id="chartAttendanceSummary" data-labels='<?= json_encode(array_column($attendanceSummary, 'GroupName')) ?>' data-values='<?= json_encode(array_column($attendanceSummary, 'AttendancePercentage')) ?>' class="w-full h-64"></canvas>
     </div>

     <!-- Attendance Summary Table -->
     <div class="bg-white rounded-lg shadow overflow-x-auto">
       <table class="min-w-full divide-y divide-gray-200">
         <thead class="bg-gray-100">
           <tr>
             <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Group Name</th>
             <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course Name</th>
             <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Present Count</th>
             <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Absent Count</th>
             <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance %</th>
           </tr>
         </thead>
         <tbody class="bg-white divide-y divide-gray-200">
           <?php foreach ($attendanceSummary as $row): ?>
             <tr>
               <td class="px-4 py-4 whitespace-nowrap"><?= htmlspecialchars($row['GroupName']) ?></td>
               <td class="px-4 py-4 whitespace-nowrap"><?= htmlspecialchars($row['CourseName']) ?></td>
               <td class="px-4 py-4 whitespace-nowrap"><?= $row['PresentCount'] ?></td>
               <td class="px-4 py-4 whitespace-nowrap"><?= $row['AbsentCount'] ?></td>
               <td class="px-4 py-4 whitespace-nowrap"><?= $row['AttendancePercentage'] ?>%</td>
             </tr>
           <?php endforeach; ?>
         </tbody>
       </table>
     </div>
   </main>
   ```
3. **Chart Initialization (`app.js`):**

   ```js
   document.addEventListener('DOMContentLoaded', () => {
     const ctx = document.getElementById('chartAttendanceSummary');
     if (ctx) {
       const labels = JSON.parse(ctx.dataset.labels);
       const data = JSON.parse(ctx.dataset.values);
       new Chart(ctx, {
         type: 'bar',
         data: {
           labels: labels,
           datasets: [{
             label: 'Attendance %',
             data: data,
             backgroundColor: '#3B82F6',
           }]
         },
         options: {
           responsive: true,
           scales: { y: { beginAtZero: true, max: 100 } }
         }
       });
     }
   });
   ```
4. **Verification:**

   * **Sample Data Test:**

     ```sql
     INSERT INTO Attendance (TID, GroupCourseID, PresentHours, AbsentHours, TakenSessions, AttendancePercentage)
       SELECT TID, GroupCourseID, 15, 5, 20, 75 FROM Enrollments WHERE GroupCourseID=1;
     ```

     * Load `report_attendance_summary.php` → verify bar for Group 1 shows 75%.
   * **Console Check:** No JS errors.
   * **Responsive Check:** At narrow widths, bars remain legible (use `options: { maintainAspectRatio: false }` if needed).
5. **Git Commit:**

   ```
   feat: migrate report_attendance_summary.php to Tailwind + Alpine + Chart.js (sample tests passing)
   ```

#### Step 4.4: Migrate `attendance.php`

1. **Includes:**

   ```php
   <?php
   require_once __DIR__ . '/../includes/auth.php';
   require_permission('record_attendance');
   require_once __DIR__ . '/../includes/config.php';
   $pageTitle = 'Attendance Entry';
   include __DIR__ . '/../includes/header.php';
   ?>
   ```

2. **Content (read-only table or Alpine inline editing):**

   ```php
   <main class="p-6 bg-gray-50 flex-1">
     <div class="bg-white rounded-lg shadow overflow-x-auto">
       <table class="min-w-full divide-y divide-gray-200">
         <thead class="bg-gray-100">
           <tr>
             <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trainee Name</th>
             <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Group</th>
             <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Present Hours</th>
             <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Absent Hours</th>
             <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Taken Sessions</th>
             <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance %</th>
             <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
           </tr>
         </thead>
         <tbody class="bg-white divide-y divide-gray-200">
           <?php foreach ($attendanceRows as $row): ?>
             <tr x-data="{ present: <?= $row['PresentHours'] ?>, absent: <?= $row['AbsentHours'] ?> }">
               <td class="px-4 py-4 whitespace-nowrap"><?= htmlspecialchars($row['Name']) ?></td>
               <td class="px-4 py-4 whitespace-nowrap"><?= htmlspecialchars($row['GroupName']) ?></td>
               <td class="px-4 py-4 whitespace-nowrap">
                 <input type="number" min="0" x-model.number="present" class="w-16 border rounded px-2 py-1 text-sm" />
               </td>
               <td class="px-4 py-4 whitespace-nowrap">
                 <input type="number" min="0" x-model.number="absent" class="w-16 border rounded px-2 py-1 text-sm" />
               </td>
               <td class="px-4 py-4 whitespace-nowrap"><?= $row['TakenSessions'] ?></td>
               <td class="px-4 py-4 whitespace-nowrap"><?= $row['AttendancePercentage'] ?>%</td>
               <td class="px-4 py-4 whitespace-nowrap">
                 <button @click="$dispatch('save-attendance', { id: <?= $row['AttendanceID'] ?>, present, absent })" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-sm">Save</button>
               </td>
             </tr>
           <?php endforeach; ?>
         </tbody>
       </table>
     </div>
   </main>
   ```

   **Note:** JavaScript event listener in `app.js` listens for `save-attendance` and performs `fetch()` to `save_attendance.php` with `{ id, present, absent }`.

3. **JavaScript (`app.js`) Addition:**

   ```js
   document.addEventListener('save-attendance', event => {
     const { id, present, absent } = event.detail;
     fetch('/dashboards/save_attendance.php', {
       method: 'POST',
       headers: { 'Content-Type': 'application/json' },
       body: JSON.stringify({ AttendanceID: id, PresentHours: present, AbsentHours: absent }),
     })
     .then(res => res.json())
     .then(data => {
       if (data.success) {
         alert('Attendance updated.');
         location.reload();
       } else {
         alert('Error: ' + data.message);
       }
     });
   });
   ```

4. **Verification:**

   *   **Sample Data Test:** Update one attendance record:

     ```sql
     UPDATE Attendance SET PresentHours = 10, AbsentHours = 10 WHERE AttendanceID = 1;
     ```

     *   Load `attendance.php`, modify values via inputs, click "Save" → confirm DB updates.
   *   **Console Check:** No errors, network tab shows a 200 response from `save_attendance.php` with `{"success":true}`.

5. **Git Commit:**

   ```
   feat: migrate attendance.php to Tailwind + Alpine inline editing
   ```

#### Step 4.5: Migrate `instructor_dashboard.php` & `coordinator_dashboard.php`

1. **Includes:**

   ```php
   <?php
   require_once __DIR__ . '/../includes/auth.php';
   require_permission('access_dashboard');
   require_once __DIR__ . '/../includes/config.php';
   $pageTitle = 'Instructor Dashboard'; // or 'Coordinator Dashboard'
   include __DIR__ . '/../includes/header.php';
   ?>
   ```
2. **Content Structure:**

   ```php
   <main class="p-6 bg-gray-50 flex-1">
     <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
       <?php
       // Example KPI array for instructor:
       $instructorKPIs = [
         ['My Courses', 'fas fa-book text-indigo-500', $courseCount, ''],
         ['Pending Grades', 'fas fa-edit text-red-500', $pendingGradesCount, ''],
         ['Pending Attendance', 'fas fa-calendar-day text-green-500', $pendingAttendanceCount, ''],
       ];
       foreach ($instructorKPIs as $k) {
         [$title, $iconClass, $value, $chartId] = $k;
         // For items without charts, set $chartId = '' and modify kpi-card to hide canvas when ID is empty
         include __DIR__ . '/../includes/components/kpi-card.php';
       }
       ?>
     </div>
   </main>
   ```
3. **If charts needed:** Use `initDoughnutChart` or `initBarChart` from `app.js`.

   ```js
   document.addEventListener('DOMContentLoaded', () => {
     initDoughnutChart('chartMyCourses', <?= $courseCompletionPct ?>, '#4F46E5');
   });
   ```
4. **Verification:**

   *   As an Instructor, ensure only KPIs relevant to their assigned courses show.
   *   No “Add Trainee” or “Manage Groups” links appear in sidebar.
   *   Charts (if any) reflect correct percentages.
5. **Git Commit:**

   ```
   feat: migrate instructor_dashboard.php to Tailwind + Alpine
   feat: migrate coordinator_dashboard.php to Tailwind + Alpine
   ```

### Phase 5: Finalization, Testing, & Documentation

#### Step 5.1: Write Verification Checklists

*   **Action:** In the `docs/` folder, create `Verification_Checklist.md` with detailed tests for each page:

    1.  **Group Performance Report:**

        *   Check KPI card values match DB data.
        *   Check chart slices correspond to percentages.
        *   Check table rows and columns align properly and scroll horizontally at small widths.
    2.  **Trainee Performance Report:** … (similar pattern)
    3.  **Attendance Summary:** …
    4.  **Attendance Entry / Grade Entry:** validate CRUD operations.
    5.  **Dashboards:** Confirm KPIs render correctly.

*   **Verification:** Ensure `Verification_Checklist.md` is present in `docs/` and lists at least one test case per migrated page.

*   **Git Commit:**

    ```
    docs: add Verification_Checklist.md with tests for all migrated pages
    ```

#### Step 5.2: Remove Deprecated Files Permanently

*   **Action:**

    ```bash
    git rm -r assets/css/base.css assets/css/components/ assets/js/dashboards-analytics.js assets/js/group-performance-report.js report-fixes.js
    git commit -m "chore: remove deprecated Bootstrap and jQuery files"
    ```
*   **Verification:**

    *   Run a global search (`grep -R "badge.css" -n .`) → no hits.
    *   Open each page → no broken references to old assets.
*   **Testing:**

    *   Clear browser cache → Reload multiple pages to ensure no missing-file errors.
*   **Git Commit:** (as above)

#### Step 5.3: Create Pull Request & Code Review

*   **Action:** Push `feature/tailwind-rebuild`, then open a PR against `main`. Use this PR template:

    ```markdown
    ## Summary of Changes
    - Migrated all frontend pages from Bootstrap/jQuery to Tailwind CSS + Alpine.js + Chart.js.
    - Removed old assets.
    - Added new components: Header, Sidebar, KPI Card, Verification Checklist.

    ## Checklist Before Merge
    - [ ] All sample data tests for each page passed.
    - [ ] No console errors on any migrated page.
    - [ ] Responsive layout verified at 320px, 768px, 1024px, 1440px.
    - [ ] RBAC checks confirmed: roles see only allowed links.
    - [ ] PDF/email export tested for group, trainee, attendance reports.

    ## After Merge
    - Tag release: `git tag v2.0-ui-tailwind`
    - Ensure CI/CD pipeline excludes `assets/css/tailwind.css` from lint-only rules if it is generated.
    ```
*   **Verification:** Peer review session; address any requested changes.
*   **Testing:** Reviewer manually checks a random sample of migrated pages.

#### Step 5.4: Post-Migration Smoke Testing & Performance Check

*   **Action:** After merging and deploying to staging:

    1.  **Role-based navigation check:** Log in as Super Admin, Admin, Instructor, Coordinator. Verify sidebar links are correct per role.
    2.  **Sample Data Scenarios:**

        *   Create a new group, assign courses, add trainees, record attendance/grades, then run each report.
        *   Verify UI matches expected output for each combination.
    3.  **PDF/Email Export Test:**

        *   Click “Export PDF” on group report → ensure PDF downloads or previews.
        *   Click “Email Report” → send to a test email; confirm reception.
    4.  **Responsive QA:**

        *   Resize to mobile/tablet/desktop → ensure no overlap or overflowing content.
    5.  **Performance:**

        *   Run Lighthouse audit (Mobile & Desktop) → Ensure no major warnings (e.g., render-blocking CSS, unused JS).
        *   Verify page load times are within acceptable thresholds (<2s for dashboard pages).

*   **Verification:** Document results in `docs/PostMigration_QA.md`. Include screenshots if possible.

*   **Testing:** Use Chrome DevTools Lighthouse.

*   **Git Commit:**

    ```
    test: add PostMigration_QA.md with smoke test results and performance metrics
    ```

---

## IV. Git Workflow & Commit Guidelines

1.  **Branch Naming:** Always prefix feature branches with `feature/` (e.g., `feature/tailwind-rebuild`).
2.  **Commit Scopes & Types:** Use conventional commit messages:

    *   `feat:` for new features or migrated pages
    *   `fix:` for bug fixes in migration
    *   `chore:` for non-code changes (removal of old assets)
    *   `docs:` for documentation changes
    *   `test:` for adding/updating tests or QA docs
3.  **Pull Requests:**

    *   Include PR template from Step 5.3.
    *   Assign at least one reviewer.
    *   Link to relevant QA docs.
4.  **Merging:** Use “Squash and Merge” or true merge commits per team policy. After merge, tag version `v2.0-ui-tailwind`.

## V. Tailwind Build & Deployment Instructions

### V.1 Installing Tools

1.  Install Node.js (v14+).
2.  In project root:

    ```bash
    npm init -y
    npm install tailwindcss postcss autoprefixer --save-dev
    npx tailwindcss init
    ```
3.  Create `postcss.config.js`:

    ```js
    module.exports = {
      plugins: [
        require('tailwindcss'),
        require('autoprefixer'),
      ]
    };
    ```
4.  Edit `tailwind.config.js`:

    ```js
    module.exports = {
      mode: 'jit',
      purge: [
        './dashboards/**/*.php',
        './includes/**/*.php',
        './assets/js/**/*.js',
      ],
      safelist: [
        'bg-blue-500', 'bg-green-500', 'bg-yellow-500', // e.g., for dynamic icon colors
      ],
      theme: {
        extend: {
          colors: {
            'primary-wa': '#0D3B66',
            'secondary-wa': '#F4D35E',
            'accent-wa': '#EE964B',
          },
          fontFamily: {
            sans: ['Lato', 'sans-serif'],
            heading: ['Poppins', 'sans-serif'],
          },
        },
      },
      variants: {},
      plugins: [],
    };
    ```
5.  Create `assets/css/input.css` with:

    ```css
    @tailwind base;
    @tailwind components;
    @tailwind utilities;
    ```
6.  Update `package.json` scripts:

    ```json
    "scripts": {
      "build:css": "postcss assets/css/input.css -o assets/css/tailwind.css --minify"
    }
    ```

### V.2 Building Tailwind on Local

*   Run:

    ```bash
    npm run build:css
    ```
*   Check that `assets/css/tailwind.css` is generated and contains used classes.

### V.3 CI/CD Considerations

*   **.gitignore:**

    *   Add `assets/css/tailwind.css` if you want to generate on deploy instead of storing in Git.
*   **Deployment Script:**

    *   Ensure `npm ci && npm run build:css` runs before sending files to production.

## VI. Appendices

### Appendix A: Full `includes/header.php` Code

```php
<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php'; // defines $baseAssetPath, DB config
if (!isLoggedIn()) {
  header('Location: /login.php');
  exit;
}
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'Water Academy Dashboard') ?></title>
  <!-- Tailwind CSS -->
  <link href="<?= BASE_ASSET_PATH ?>css/tailwind.css" rel="stylesheet">
  <link href="<?= BASE_ASSET_PATH ?>css/custom.css" rel="stylesheet">
</head>
<body x-data="layout()" x-init="initLayout()" class="h-full bg-gray-100">
  <div class="flex h-full">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <div :class="sidebarOpen || window.innerWidth >= 768 ? 'ml-64' : 'ml-0'" class="flex-1 flex flex-col transition-all duration-200">
      <header class="flex items-center justify-between bg-white border-b shadow-sm px-4 py-3">
        <button @click="sidebarOpen = !sidebarOpen" class="md:hidden text-gray-600 hover:text-gray-900">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
        <div class="flex items-center space-x-4">
          <h1 class="text-xl font-semibold text-gray-800"><?= htmlspecialchars($pageTitle ?? '') ?></h1>
        </div>
        <div class="relative" x-data="{ open: false }">
          <button @click="open = !open" class="flex items-center text-gray-600 hover:text-gray-900 focus:outline-none">
            <span class="mr-2"><?= htmlspecialchars($currentUser['firstName'] . ' ' . $currentUser['lastName']) ?></span>
            <img src="<?= BASE_ASSET_PATH ?>images/avatar.png" alt="Avatar" class="h-8 w-8 rounded-full">
          </button>
          <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white border rounded-lg shadow-lg overflow-hidden z-20">
            <a href="/profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profile</a>
            <a href="/logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Logout</a>
          </div>
        </div>
      </header>
```

### Appendix B: Full `includes/sidebar.php` Code

```php
<?php
$roleID = getUserRoleID();
?>
<aside
  x-show="sidebarOpen || window.innerWidth >= 768"
  @click.outside="sidebarOpen = false"
  class="fixed inset-y-0 left-0 w-64 bg-white border-r shadow-lg transform transition-transform duration-200 md:translate-x-0 z-30"
  :class="{ '-translate-x-full': !(sidebarOpen || window.innerWidth >= 768) }"
>
  <div class="flex items-center justify-between p-4 border-b">
    <a href="/coordinator_dashboard.php"><img src="<?= BASE_ASSET_PATH ?>images/waLogoBlue.png" alt="Water Academy" class="h-8"></a>
    <button @click="sidebarOpen = false" class="text-gray-600 hover:text-gray-900 md:hidden">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>
  </div>
  <nav class="mt-4">
    <ul>
      <?php if (hasPermission('access_dashboard')): ?>
        <li class="px-4 py-2 hover:bg-gray-100"><a href="/coordinator_dashboard.php" class="block text-gray-700">Dashboard</a></li>
      <?php endif; ?>

      <?php if (hasPermission('manage_groups')): ?>
        <li class="px-4 py-2 hover:bg-gray-100"><a href="/manage_groups.php" class="block text-gray-700">Groups</a></li>
      <?php endif; ?>

      <?php if (hasPermission('manage_courses')): ?>
        <li class="px-4 py-2 hover:bg-gray-100"><a href="/manage_courses.php" class="block text-gray-700">Courses</a></li>
      <?php endif; ?>

      <?php if (hasPermission('manage_trainees')): ?>
        <li class="px-4 py-2 hover:bg-gray-100"><a href="/manage_trainees.php" class="block text-gray-700">Trainees</a></li>
      <?php endif; ?>

      <?php if (hasPermission('record_attendance')): ?>
        <li class="px-4 py-2 hover:bg-gray-100"><a href="/attendance.php" class="block text-gray-700">Attendance</a></li>
      <?php endif; ?>

      <?php if (hasPermission('record_grades')): ?>
        <li class="px-4 py-2 hover:bg-gray-100"><a href="/attendance_grades.php" class="block text-gray-700">Grade Entry</a></li>
      <?php endif; ?>

      <?php if (hasPermission('access_group_reports') || hasPermission('access_trainee_reports') || hasPermission('access_attendance_summary')): ?>
        <li class="px-4 py-2 font-medium text-gray-700 uppercase text-xs mt-4">Reports</li>
        <?php if (hasPermission('access_group_reports')): ?>
          <li class="px-6 py-2 hover:bg-gray-100"><a href="/report_group_performance.php" class="block text-gray-600">Group Performance</a></li>
        <?php endif; ?>
        <?php if (hasPermission('access_trainee_reports')): ?>
          <li class="px-6 py-2 hover:bg-gray-100"><a href="/report_trainee_performance.php" class="block text-gray-600">Trainee Performance</a></li>
        <?php endif; ?>
        <?php if (hasPermission('access_attendance_summary')): ?>
          <li class="px-6 py-2 hover:bg-gray-100"><a href="/report_attendance_summary.php" class="block text-gray-600">Attendance Summary</a></li>
        <?php endif; ?>
      <?php endif; ?>

      <?php if (hasPermission('access_user_management')): ?>
        <li class="px-4 py-2 hover:bg-gray-100"><a href="/user_management.php" class="block text-gray-700">User Management</a></li>
      <?php endif; ?>

      <?php if (hasPermission('access_settings')): ?>
        <li class="px-4 py-2 hover:bg-gray-100"><a href="/settings/email_templates.php" class="block text-gray-700">Settings</a></li>
      <?php endif; ?>
    </ul>
  </nav>
</aside>
```

### Appendix C: Full `assets/js/app.js` Code

```js
// app.js

function layout() {
  return {
    sidebarOpen: window.innerWidth >= 768,
    initLayout() {
      if (window.innerWidth < 768) {
        this.sidebarOpen = false;
      }
      window.addEventListener('resize', () => {
        if (window.innerWidth >= 768) {
          this.sidebarOpen = true;
        }
      });
    }
  };
}

// Utility to initialize a Doughnut chart
function initDoughnutChart(chartId, dataValue, hexColor) {
  const ctx = document.getElementById(chartId);
  if (!ctx) return;
  new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: [chartId, 'Remaining'],
      datasets: [{
        data: [dataValue, 100 - dataValue],
        backgroundColor: [hexColor, '#E5E7EB'],
      }]
    },
    options: {
      cutout: '70%',
      plugins: { legend: { display: false } }
    }
  });
}

// Utility for Bar chart (attendance summary)
function initBarChart(chartId, labelsArray, dataArray) {
  const ctx = document.getElementById(chartId);
  if (!ctx) return;
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labelsArray,
      datasets: [{
        label: 'Percentage',
        data: dataArray,
        backgroundColor: '#3B82F6',
      }]
    },
    options: {
      responsive: true,
      scales: { y: { beginAtZero: true, max: 100 } }
    }
  });
}

// Initialize charts once DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
  // Example: group report charts
  if (document.getElementById('chartAvgScore')) {
    initDoughnutChart('chartAvgScore', parseInt(document.getElementById('chartAvgScore').dataset.value), '#3B82F6');
  }
  // Similarly for other charts...

  // Attendance summary bar chart
  if (document.getElementById('chartAttendanceSummary')) {
    const labels = JSON.parse(document.getElementById('chartAttendanceSummary').dataset.labels);
    const data = JSON.parse(document.getElementById('chartAttendanceSummary').dataset.values);
    initBarChart('chartAttendanceSummary', labels, data);
  }

  // Listen for saving attendance via Alpine event
  document.addEventListener('save-attendance', event => {
    const { id, present, absent } = event.detail;
    fetch('/dashboards/save_attendance.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ AttendanceID: id, PresentHours: present, AbsentHours: absent }),
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        alert('Attendance updated.');
        location.reload();
      } else {
        alert('Error: ' + data.message);
      }
    });
  });
});
```

---

*End of WaterAcademy\_UI\_TransferGuide\_Part2.md*

</final_file_content>

IMPORTANT: For any future changes to this file, use the final_file_content shown above as your reference. This content reflects the current state of the file, including any auto-formatting (e.g., if you used single quotes but the formatter converted them to double quotes). Always base your SEARCH/REPLACE operations on this final version to ensure accuracy.<environment_details>
# VSCode Visible Files
logs/Projectinfo_Part2.md

# VSCode Open Tabs
logs/WaterAcademy_UI_TransferGuide_Part1.md
logs/Projectinfo_Part1.md
logs/Projectinfo_Part2.md

# Actively Running Terminals
## Original command: `git push -u origin main`

# Current Time
6/3/2025, 6:09:04 PM (Asia/Riyadh, UTC+3:00)

# Context Window Usage
977,174 / 1,048.576K tokens used (93%)

# Current Mode
ACT MODE
</environment_details>

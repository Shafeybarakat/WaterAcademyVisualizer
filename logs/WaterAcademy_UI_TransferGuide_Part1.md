# WaterAcademy\_UI\_TransferGuide\_Part1.md

## I. Overview & Objectives

**Who Should Use This Guide:**

* Any developer or AI agent (e.g., Gemini, Claude) tasked with migrating the Water Academy system’s front-end from Bootstrap/jQuery to Tailwind CSS + Alpine JS + Chart.js.
* Assumes familiarity with PHP, Git, npm/Tailwind CLI, and basic front-end tooling.

**Baseline Version:**

* Starting code is tagged `v1.0-bootstrap` on commit `abc123` (June 1, 2025).
* All pages currently use Bootstrap 5/Sneat theme and jQuery, with custom CSS/JS (stored under `/assets/css/` and `/assets/js/`).

**Objectives:**

1. Remove all Bootstrap/Sneat and jQuery dependencies.
2. Implement a clean, utility-first UI using Tailwind CSS, Alpine JS for interactivity, and Chart.js for data visualization.
3. Preserve all existing PHP business logic, data contracts, and RBAC (role-based access control).
4. Provide mobile-first, responsive layouts and a modular component architecture.
5. Establish a phased migration plan with verification steps and Git commit guidelines.

## II. New Folder & File Structure

After migration, the project should be organized as follows. Deleted files are marked “(DELETED)”. Any new file needed for Tailwind/Alpine/Chart.js is annotated.

```
/ (project root)
├── index.php                  # Landing page (unchanged)
├── login.php                  # User login (unchanged)
├── logout.php                 # Logout script (unchanged)
├── README.md                  # Project overview
├── Projectinfo.md             # Current system documentation (upgraded)
├── docs/
│   ├── Original_Projectinfo.md  # Backup of Projectinfo.md pre-migration
│   └── WaterAcademy_UI_TransferGuide_Part1.md  # This migration guide, Part 1
├── includes/                  # Shared PHP includes
│   ├── auth.php               # Authentication & permission helpers (unchanged)
│   ├── config.php             # DB connection + define $baseAssetPath = '/assets/'
│   ├── header.php             # Tailwind+Alpine header (new)
│   ├── sidebar.php            # Tailwind+Alpine sidebar (new)
│   ├── footer.php             # Footer includes (Tailwind, scripts)
│   ├── report_functions.php   # Business logic for LGI, attendance % (unchanged)
│   ├── email_functions.php    # Helpers for email (unchanged)
│   └── components/
│       └── kpi-card.php       # Tailwind+Alpine KPI card partial (new)
├── dashboards/                # PHP pages for dashboards & reports
│   ├── ajax_get_courses_by_group.php      # Returns JSON list of courses by GroupID (unchanged)
│   ├── ajax_search_trainees.php           # Returns JSON search results for trainees (unchanged)
│   ├── attendance.php                    # Attendance entry (Tailwind+Alpine after migration)
│   ├── attendance_grades.php             # Grade entry (Tailwind+Alpine)
│   ├── coordinator_dashboard.php         # Coordinator dashboard (migrated)
│   ├── get_group_report_data.php         # Loads PHP $groups for reports (unchanged)
│   ├── instructor_dashboard.php          # Instructor dashboard (migrated)
│   ├── manage_courses.php                # Manage courses CRUD (Tailwind forms)
│   ├── manage_groups.php                 # Manage groups CRUD (Tailwind wizard)
│   ├── manage_trainees.php               # Manage trainees CRUD (Tailwind forms)
│   ├── report_attendance_summary.php     # Attendance summary report (Tailwind+Chart.js)
│   ├── report_group_performance.php      # Group performance report (Tailwind+Chart.js)
│   ├── report_trainee_performance.php    # Trainee performance report (Tailwind+Chart.js)
│   ├── report_print_modal.php            # Modal partial for Export PDF / Email (Tailwind+Alpine)
│   ├── report_print.php                  # AJAX endpoint to generate/send PDF (unchanged)
│   └── trainees.php                      # List all trainees (Tailwind table with filters)
├── assets/
│   ├── css/
│   │   ├── (DELETED) badges.css           # old Sneat/Bootstrap CSS
│   │   ├── (DELETED) buttons.css          # old
│   │   ├── (DELETED) cards.css            # old
│   │   ├── (DELETED) layout.css           # old
│   │   ├── (DELETED) navbar.css           # old
│   │   ├── (DELETED) progress.css         # old
│   │   ├── (DELETED) sidebar.css          # old
│   │   ├── (DELETED) tables.css           # old
│   │   ├── (DELETED) typography.css       # old
│   │   ├── tailwind.css                   # Generated Tailwind CSS (build artifact)  
│   │   └── custom.css                     # Minimal overrides and @apply utilities  
│   ├── js/
│   │   ├── (DELETED) dashboards-analytics.js  # old jQuery-driven chart logic
│   │   ├── (DELETED) group-performance-report.js # old
│   │   ├── (DELETED) report-fixes.js       # old jQuery hotfixes
│   │   ├── alpine.min.js                   # Alpine.js (via CDN or local copy)  
│   │   ├── chart.min.js                    # Chart.js (via CDN or local copy)  
│   │   └── app.js                          # Main JS: Alpine data, Chart.js init  
│   ├── vendor/
│   │   └── html2pdf/
│   │       └── html2pdf.bundle.min.js      # PDF generation library (unchanged)  
│   ├── images/
│   │   ├── waLogoBlue.png                  # Logo used in header/sidebar  
│   │   └── (other icons/avatars)           # Static assets  
│   └── fonts/                              # (custom fonts, if any)  
├── settings/                               # Email templates & system settings UI  
│   └── email_templates.php                 # Manage email templates (UI)  
├── Srcs/                                   # Legacy scripts (unchanged)  
│   ├── batch_load.php
│   ├── batch_process.php
│   ├── data_entry.php
│   └── show_tables.php
├── logs/
│   └── Migration_Log.txt                   # (Optional) record migration notes  
└── u652025084_new_wa_db.sql                # Database schema
```

### 2.1 Deleted Files Overview

List of old Sneat/Bootstrap and jQuery-dependent assets removed:

* **CSS** (moved or deleted):

  * badges.css, buttons.css, cards.css, layout.css, navbar.css, progress.css, sidebar.css, tables.css, typography.css, style.css
* **JS** (moved or deleted):

  * dashboards-analytics.js, group-performance-report.js, report-fixes.js, any jQuery plugins (e.g., Bootstrap bundle).

**Note:** Instead of tracking each filename individually, the migration process removes entire directories of old assets. Always verify no references remain in PHP includes or HTML templates.

## III. Phase-by-Phase Migration Plan

The migration is divided into **5 phases**, each with discrete steps, clear verification criteria, testing mechanisms, and Git commit guidelines.

### Phase 1: Preparation & Backup

#### Step 1.1: Create Migration Branch

* **Action:**

  ```bash
  git checkout -b feature/tailwind-rebuild
  git push -u origin feature/tailwind-rebuild
  ```
* **Verification:**

  * Run `git branch` → Confirm you are on `feature/tailwind-rebuild`.
  * `git status` should report no uncommitted changes.
* **Testing:** None (branch creation only).
* **Git Commit:**

  ```
  chore: create feature/tailwind-rebuild branch from main
  ```

#### Step 1.2: Backup Original Documentation

* **Action:**

  ```bash
  mkdir -p docs
  cp Projectinfo.md docs/Original_Projectinfo.md
  ```
* **Verification:**

  * Ensure `docs/Original_Projectinfo.md` exists and matches `Projectinfo.md` exactly.
* **Testing:** Open both files in editor to compare.
* **Git Commit:**

  ```
  docs: backup original Projectinfo.md as Original_Projectinfo.md
  ```

### Phase 2: Cleanup Old Assets & Skeleton Setup

#### Step 2.1: Remove Old CSS/JS References

* **Action:** Edit `/includes/header.php` and `/includes/footer.php` to remove all `<link>`/`<script>` tags that reference old CSS/JS. For example:

  ```diff
  --- a/includes/header.php
  +++ b/includes/header.php
  @@ -20,7 +20,7 @@
   <!-- Old Bootstrap CSS references (remove these) -->
  -<link rel="stylesheet" href="<?= $baseAssetPath ?>css/bootstrap.min.css">
  -<link rel="stylesheet" href="<?= $baseAssetPath ?>css/layout.css">
  +<!-- [REMOVED] Bootstrap/Sneat CSS -->
   
   <!-- New Tailwind CSS -->
   <link href="<?= $baseAssetPath ?>css/tailwind.css" rel="stylesheet">
  @@ -50,7 +50,7 @@
   <!-- Old JS references (remove these) -->
  -<script src="<?= $baseAssetPath ?>js/jquery.min.js"></script>
  -<script src="<?= $baseAssetPath ?>js/dashboards-analytics.js"></script>
  +<!-- [REMOVED] jQuery & custom JS -->
   
   <!-- New Alpine.js -->
   <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
   <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
   <script src="<?= $baseAssetPath ?>js/app.js"></script>
  ```
* **Verification:**

  * Reload any page (e.g., `login.php` or `attendance.php`).
  * In DevTools Network tab, confirm no 404 errors for old CSS/JS paths.
  * In Console, confirm no errors like `Uncaught ReferenceError: $ is not defined` or `Bootstrap is not defined`.
* **Testing:**

  * Even if the page looks unstyled, there should be no missing-file errors and no JavaScript console errors.
* **Git Commit:**

  ```
  chore: remove old Bootstrap/Sneat CSS and jQuery-based JS references
  ```

#### Step 2.2: Add Tailwind & Alpine Skeleton References

* **Action:** In `/includes/header.php`, after removing old CSS, insert:

  ```html
  <!-- Tailwind CSS (via CDN or local build) -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.0/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= $baseAssetPath ?>css/custom.css">

  <!-- Alpine.js (deferred) -->
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  ```

  In `/includes/footer.php`, replace old JS references with the same Alpine/Chart.js tags and:

  ```html
  <!-- App logic (Alpine state & Chart.js initialization) -->
  <script src="<?= $baseAssetPath ?>js/app.js"></script>
  ```
* **Verification:**

  * In DevTools, confirm `tailwind.min.css`, `alpine.min.js`, and `chart.min.js` load successfully.
  * Confirm there are no requests for old CSS or JS files.
* **Testing:**

  * Temporarily add a test class to a page (e.g., `<div class="bg-blue-500 p-4">Test</div>`) to verify Tailwind utilities are working.
* **Git Commit:**

  ```
  feat: add Tailwind CSS and Alpine.js, Chart.js references in header/footer
  ```

### Phase 3: Core Includes & Componentization

> **Note on `$baseAssetPath`:** In `includes/config.php`, ensure you define:
>
> ```php
> <?php
>   define('BASE_ASSET_PATH', '/assets/');
>   // or use $baseAssetPath = '/assets/'; if using variables.
> ?>
> ```
>
> Then use `<?= BASE_ASSET_PATH ?>css/tailwind.css` instead of hardcoding `/assets/css/...`.

#### Step 3.1: Create Tailwind Header (`includes/header.php`)

* **Action:** Replace existing header HTML (navbar, brand, dropdowns) with this Tailwind/Alpine version:

  ```php
  <?php
  // includes/header.php
  if (!isLoggedIn()) {
    header('Location: /login.php');
    exit;
  }
  $currentUser = getCurrentUser(); // returns array with firstName, lastName, role, etc.
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
    <!-- Page wrapper (sidebar + main) -->
    <div class="flex h-full">
      <?php include __DIR__ . '/sidebar.php'; ?>
      <!-- Main content container -->
      <div :class="sidebarOpen || window.innerWidth >= 768 ? 'ml-64' : 'ml-0'" class="flex-1 flex flex-col transition-all duration-200">
        <!-- Top navigation bar -->
        <header class="flex items-center justify-between bg-white border-b shadow-sm px-4 py-3">
          <!-- Mobile menu button -->
          <button @click="sidebarOpen = !sidebarOpen" class="md:hidden text-gray-600 hover:text-gray-900">
            <!-- Heroicon: menu -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
          <div class="flex items-center space-x-4">
            <h1 class="text-xl font-semibold text-gray-800"><?= htmlspecialchars($pageTitle ?? '') ?></h1>
            <!-- Theme switcher or notifications could go here -->
          </div>
          <!-- User dropdown -->
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
        <!-- Main content slot starts here -->
  ```

  * **Verification:**

    * Open any protected page (e.g., `coordinator_dashboard.php`) → Session redirect to login if not authenticated.
    * Check header appears with brand area blank (pageTitle), hamburger icon on mobile, and user’s full name.
  * **Testing:**

    * In DevTools, run `Alpine.rawData('layout')` to ensure Alpine data is initialized.
    * Resize browser to confirm header responsiveness (hamburger visible at `md:hidden`).
  * **Git Commit:**

    ```
    feat: add Tailwind + Alpine header include with user dropdown
    ```

#### Step 3.2: Create Tailwind Sidebar (`includes/sidebar.php`)

* **Action:** Add this file:

  ```php
  <?php
  // includes/sidebar.php
  $roleID = getUserRoleID();
  ?>
  <!-- Sidebar (hidden by default on mobile) -->
  <aside
    x-show="sidebarOpen || window.innerWidth >= 768"
    @click.outside="sidebarOpen = false"
    class="fixed inset-y-0 left-0 w-64 bg-white border-r shadow-lg transform transition-transform duration-200
           md:translate-x-0 z-30"
    :class="{ '-translate-x-full': !(sidebarOpen || window.innerWidth >= 768) }"
  >
    <div class="flex items-center justify-between p-4 border-b">
      <a href="/coordinator_dashboard.php"><img src="<?= BASE_ASSET_PATH ?>images/waLogoBlue.png" alt="Water Academy" class="h-8"></a>
      <button @click="sidebarOpen = false" class="text-gray-600 hover:text-gray-900 md:hidden">
        <!-- Heroicon: x -->
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
* **Main Content Wrapper:** Immediately after including `sidebar.php` in `header.php`, wrap the page content in:

  ```html
  <div :class="sidebarOpen || window.innerWidth >= 768 ? 'ml-64' : 'ml-0'" class="flex-1 flex flex-col transition-all duration-200">
    <!-- Page-specific content begins here -->
  ```
* **Verification:**

  * On desktop (`≥768px`), sidebar is visible by default and main content is offset by 16rem (`ml-64`).
  * On mobile (`<768px`), sidebar is hidden (`-translate-x-full`) by default; clicking hamburger toggles it.
  * Confirm sidebar links appear only for permitted roles (e.g., Admin sees “Settings”, Instructor does not).
* **Testing:**

  * As an Admin user, open DevTools → verify `<aside>` has `translate-x-0` at widths ≥768px; at <768px it has `-translate-x-full`.
  * Click hamburger → sidebar animates in with `translate-x-0`.
* **Git Commit:**

  ```
  feat: add Tailwind + Alpine sidebar include with RBAC-based links
  ```

#### Step 3.3: Create KPI Card Component (`includes/components/kpi-card.php`)

* **Action:** Add the following partial:

  ```php
  <?php
  // includes/components/kpi-card.php
  // Expects:
  //   $title (string), e.g. 'Avg Score'
  //   $iconClass (string), e.g. 'fas fa-chart-pie text-blue-500'
  //   $value (int or float), e.g. 85
  //   $chartId (string), e.g. 'chartAvgScore'
  ?>
  <div class="bg-white rounded-lg shadow p-4 flex flex-col items-center">
    <div class="flex items-center justify-between w-full mb-2">
      <h2 class="text-lg font-semibold text-gray-700"><?= htmlspecialchars($title) ?></h2>
      <i class="<?= htmlspecialchars($iconClass) ?> text-xl"></i>
    </div>
    <canvas id="<?= htmlspecialchars($chartId) ?>" class="w-full h-40"></canvas>
    <p class="mt-2 text-center text-2xl font-semibold text-gray-800"><?= htmlspecialchars($value) ?>%</p>
  </div>
  ```
* **Verification:**

  * Do a quick include in `report_group_performance.php`:

    ```php
    <?php
      $title = 'Test KPI';
      $iconClass = 'fas fa-chart-pie text-blue-500';
      $value = 50;
      $chartId = 'chartTestKPI';
      include __DIR__ . '/../includes/components/kpi-card.php';
    ?>
    ```
  * Load the page → ensure a white card with a chart canvas and "50%" appears.
  * Confirm Tailwind classes (`bg-white`, `rounded-lg`, `shadow`, etc.) render correctly.
* **Testing:**

  * In DevTools, inspect the `<canvas>` → verify it is 40 units tall (`h-40` → 10rem) and adapts width.
* **Git Commit:**

  ```
  feat: add kpi-card component partial using Tailwind and PHP variables
  ```

### Phase 4: Page-by-Page Migration & Testing (continued in Part 2)

> The remainder of the migration (Steps 4.1–4.5), Phase 5, Git Workflow, Tailwind Build & Deployment, and Appendices are provided in **Part 2** of this guide.

---

*End of WaterAcademy\_UI\_TransferGuide\_Part1.md*

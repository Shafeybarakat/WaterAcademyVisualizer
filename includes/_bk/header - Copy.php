<?php
// Ensure session is started (config.php should do this, but good to double-check)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine base path for includes and assets
$includeBasePath = ""; // For files in root
$current_script_path = dirname($_SERVER["PHP_SELF"]);
if (basename($current_script_path) !== "wa" && $current_script_path !== "/wa") { // If in a subdirectory like /dashboards/ or /reports/
    // Count how many levels deep we are from the 'wa' root
    $path_parts = explode('/', trim($current_script_path, '/'));
    $wa_index = array_search('wa', $path_parts);
    if ($wa_index !== false) {
        $depth = count($path_parts) - ($wa_index + 1);
        $includeBasePath = str_repeat("../", $depth);
    } else {
         // Fallback if 'wa' is not in the path (e.g. running from a different structure)
        // This might need adjustment based on your exact server setup if 'wa' is not the webroot segment
        $doc_root_parts = explode('/', trim($_SERVER['DOCUMENT_ROOT'], '/'));
        $script_parts = explode('/', trim(dirname($_SERVER['SCRIPT_FILENAME']), '/'));
        $common_path = [];
        foreach($script_parts as $i => $part){
            if(isset($doc_root_parts[$i]) && $doc_root_parts[$i] == $part){
                $common_path[] = $part;
            } else {
                break;
            }
        }
        $levels_down = count($script_parts) - count($common_path);
        $includeBasePath = str_repeat("../", $levels_down);
         if ($levels_down === 0 && basename(dirname($_SERVER["PHP_SELF"])) === "wa") { // If script is in the root 'wa' directory
            // This case might be redundant if the above logic is robust
        } else if ($levels_down === 0) {
             $includeBasePath = "./"; // Current directory if it's not clear
        }
    }
}


require_once $includeBasePath . "includes/config.php"; // For $conn
require_once $includeBasePath . 'includes/auth.php';   // For authentication functions

// Protect authenticated areas globally
// This will redirect to login if a user tries to access /dashboards/* or /reports/* without being logged in.
protect_authenticated_area($includeBasePath . 'login.php?message=login_required');


// --- Variables for Header ---
$pageTitle = $pageTitle ?? "Home"; // Default title, can be overridden by including page

// Get user details from session or database if needed
$userName = $_SESSION["FullName"] ?? "User";
$userRole = $_SESSION["user_role"] ?? "Guest";
$avatarPathFromSession = $_SESSION["AvatarPath"] ?? null;

// If critical session info is missing for a logged-in user, try to refresh it (optional, good for robustness)
if (isLoggedIn() && (!isset($_SESSION["FullName"]) || !isset($_SESSION["user_role"]))) {
    if (isset($_SESSION["user_id"]) && $conn) {
        $user_id_refresh = $_SESSION["user_id"];
        $stmt_refresh = $conn->prepare("SELECT CONCAT(FirstName, ' ', LastName) AS FullName, Role, AvatarPath FROM Users WHERE UserID = ?");
        if ($stmt_refresh) {
            $stmt_refresh->bind_param("i", $user_id_refresh);
            $stmt_refresh->execute();
            $result_refresh = $stmt_refresh->get_result();
            if ($row_refresh = $result_refresh->fetch_assoc()) {
                $_SESSION["FullName"] = $row_refresh["FullName"];
                $_SESSION["user_role"] = $row_refresh["Role"];
                $_SESSION["AvatarPath"] = $row_refresh["AvatarPath"];
                // Update local vars
                $userName = $_SESSION["FullName"];
                $userRole = $_SESSION["user_role"];
                $avatarPathFromSession = $_SESSION["AvatarPath"];
            }
            $stmt_refresh->close();
        }
    }
}

$baseAssetPath = $includeBasePath . "assets/";
$defaultAvatar = $baseAssetPath . "img/avatars/1.png";
$avatarPath = $avatarPathFromSession ? $includeBasePath . ltrim($avatarPathFromSession, '/') : $defaultAvatar;

// Check if avatar file exists on server, otherwise use default
// Note: file_exists needs a server path. Construct it carefully.
$avatarServerPath = rtrim($_SERVER["DOCUMENT_ROOT"], '/') . '/' . ltrim(parse_url($avatarPath, PHP_URL_PATH), '/');
if (!file_exists($avatarServerPath) || empty($avatarPathFromSession) ) {
    $avatarPath = $defaultAvatar;
}

$baseLinkPath = $includeBasePath; // For links within the application structure

?>
<?php
// Get theme from cookie or use default
$current_theme = isset($_COOKIE['wa_theme']) ? $_COOKIE['wa_theme'] : 'default';
$theme_class = 'theme-' . $current_theme;
?>
<!DOCTYPE html>
<html
  lang="en"
  class="light-style layout-menu-fixed layout-compact <?php echo $theme_class; ?>"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="<?php echo htmlspecialchars($baseAssetPath); ?>"
  data-template="vertical-menu-template-free">
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title><?php echo htmlspecialchars($pageTitle); ?> | Water Academy</title>

    <meta name="description" content="Saudi Water Academy Training Management System" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo htmlspecialchars($baseAssetPath); ?>img/favicon/favicon.ico" />

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="<?php echo htmlspecialchars($baseAssetPath); ?>vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sneat-bootstrap-html-admin-template-free@1.0.0/assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="<?php echo htmlspecialchars($baseAssetPath); ?>vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <!-- ApexCharts CSS (Added for dashboard charts) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.css">
    
    <!-- Chart.js for doughnut charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Helpers -->
    <script src="<?php echo htmlspecialchars($baseAssetPath); ?>vendor/js/helpers.js"></script>
    <script src="<?php echo htmlspecialchars($baseAssetPath); ?>js/config.js"></script>
    <script src="<?php echo htmlspecialchars($baseAssetPath); ?>js/prevent-cache.js"></script>

    <?php
    // Conditional loading for report pages
    $current_page = basename($_SERVER["PHP_SELF"]);
    $is_report_page = (strpos($current_page, 'report_') !== false || 
                      strpos($current_page, 'trainee_report') !== false || 
                      strpos($current_page, 'group-analytics') !== false);
    
    if ($is_report_page) {
        // Include html2pdf library
        echo '<script src="' . htmlspecialchars($baseAssetPath) . 'vendor/html2pdf/html2pdf.bundle.min.js"></script>';
        
        // Include report functions
        require_once $includeBasePath . "dashboards/report_functions.php";
        
        // Set up report page if the function exists
        if (function_exists('setupReportPage')) {
            setupReportPage($pageTitle, true);
        }
    }
    ?>

    <!-- Custom CSS - consolidated files -->
    <link rel="stylesheet" href="<?php echo htmlspecialchars($baseAssetPath); ?>css/main.css?v=<?php echo time(); ?>" />
    
    <!-- Custom Font for Welcome Message -->
    <style>
      @font-face {
        font-family: 'Michroma';
        src: url('<?php echo htmlspecialchars($baseAssetPath); ?>fonts/michroma/Michroma.ttf') format('truetype');
        font-weight: normal;
        font-style: normal;
      }
    </style>

    <!-- Theme Switcher Script -->
    <script src="<?php echo htmlspecialchars($baseAssetPath); ?>js/theme-switcher.js"></script>
</head>

<body>
  <!-- Layout wrapper -->
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
      <style>
        /* Make header sticky to the top */
        .layout-navbar {
          position: sticky;
          top: 0;
          z-index: 1050;
        }
        
        /* Set vertical alignment of all elements to top */
        .container-xxl, .container-fluid, .row, .col, .card, .card-body {
          vertical-align: top;
        }
        
        /* Ensure content fills available space and pushes footer to bottom */
        .content-wrapper {
          min-height: calc(100vh - 56px - 56px); /* 100vh - header height - footer height */
          display: flex;
          flex-direction: column;
        }
        
        /* All content containers should align to the top by default */
        .container-xxl, .container-p-y {
          padding-top: 0 !important;
        }
      </style>

      <?php
        // Only include sidebar if user is logged in and not on login page itself
        // (login.php should not include this header, or header needs to know not to include sidebar on login)
        // The protect_authenticated_area() in auth.php (called above) handles redirecting if not logged in.
        // So, if we reach here and are on a dashboard page, user must be logged in.
        if (isLoggedIn() && strpos($_SERVER["PHP_SELF"], 'login.php') === false) {
            include $includeBasePath . "includes/sidebar.php";
        }
      ?>

      <!-- Layout container -->
      <div class="layout-page">
        <!-- Navbar -->
        <?php if (isLoggedIn() && strpos($_SERVER["PHP_SELF"], 'login.php') === false): ?>
        <nav
          class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
          id="layout-navbar">
          <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
            <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
              <i class="bx bx-menu bx-sm"></i>
            </a>
          </div>

          <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">

            <!-- Page Title -->
            <div class="page-title"><?php echo htmlspecialchars($pageTitle); ?></div>
            <!-- / Page Title -->

            <ul class="navbar-nav flex-row align-items-center ms-auto">
              <!-- Theme toggle button -->
              <li class="nav-item me-3">
                <button id="theme-toggle-btn" class="btn btn-icon btn-outline-secondary rounded-circle">
                  <i class="bx bx-sun theme-icon-light"></i>
                  <i class="bx bx-moon theme-icon-dark d-none"></i>
                </button>
              </li>

              <li class="nav-item me-3">
                <span class="navbar-text">
                  <span class="user-welcome">Welcome, <?php echo htmlspecialchars($userName); ?>!</span> 
                  <span class="user-role"><?php echo htmlspecialchars($userRole); ?></span>
                </span>
                <?php if (isset($_SESSION['switched_user_id'])): ?>
                <div class="mt-1 text-warning">
                  <i class="bx bx-user-pin"></i> 
                  <small>You switched to <?php echo htmlspecialchars($_SESSION['switched_user_name']); ?>'s view</small>
                  <a href="<?php echo htmlspecialchars($baseLinkPath); ?>dashboards/switch_back.php" class="btn btn-link btn-sm p-0 ms-2 text-primary" style="font-size: 0.8rem;">Switch Back</a>
                </div>
                <?php endif; ?>
              </li>

              <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                  <div class="avatar avatar-online">
                    <img src="<?php echo htmlspecialchars($avatarPath); ?>?v=<?php echo time(); ?>" alt class="w-px-40 h-auto rounded-circle" style="object-fit: cover;"/>
                  </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item" href="<?php echo htmlspecialchars($baseLinkPath); ?>dashboards/profile.php">
                      <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                          <div class="avatar avatar-online">
                            <img src="<?php echo htmlspecialchars($avatarPath); ?>?v=<?php echo time(); ?>" alt class="w-px-40 h-auto rounded-circle" style="object-fit: cover;"/>
                          </div>
                        </div>
                        <div class="flex-grow-1">
                          <span class="fw-medium d-block"><?php echo htmlspecialchars($userName); ?></span>
                          <small class="text-muted"><?php echo htmlspecialchars($userRole); ?></small>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li>
                    <div class="dropdown-divider"></div>
                  </li>
                  <li>
                    <a class="dropdown-item" href="<?php echo htmlspecialchars($baseLinkPath); ?>dashboards/profile.php">
                      <i class="bx bx-user me-2"></i>
                      <span class="align-middle">My Profile</span>
                    </a>
                  </li>
                  <?php if (hasPermission('manage_system_settings')): ?>
                  <li>
                    <a class="dropdown-item" href="<?php echo htmlspecialchars($baseLinkPath); ?>dashboards/settings.php">
                      <i class="bx bx-cog me-2"></i>
                      <span class="align-middle">Settings</span>
                    </a>
                  </li>
                  <?php endif; ?>
                  <?php if (hasPermission('manage_roles')): ?>
                  <li>
                    <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#switchRoleModal">
                      <i class="bx bx-user-pin me-2"></i>
                      <span class="align-middle">Switch Role</span>
                    </a>
                  </li>
                  <?php endif; ?>
                  <li>
                    <div class="dropdown-divider"></div>
                  </li>
                  <li>
                    <a class="dropdown-item" href="<?php echo htmlspecialchars($baseLinkPath); ?>logout.php">
                      <i class="bx bx-power-off me-2"></i>
                      <span class="align-middle">Log Out</span>
                    </a>
                  </li>
                </ul>
              </li>
            </ul>
          </div>
        </nav>
        <?php endif; ?>
        <!-- / Navbar -->

        <!-- Content wrapper -->
        <div class="content-wrapper">
          <!-- Content -->
          <!-- The main page content will go here -->
          <!-- Make sure pages include this header, then their content, then the footer -->

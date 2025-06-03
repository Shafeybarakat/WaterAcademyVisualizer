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
$current_theme = isset($_COOKIE['wa_theme']) ? $_COOKIE['wa_theme'] : 'dark';
$theme_class = 'theme-' . $current_theme;
?>
<!DOCTYPE html>
<html
  lang="en"
  class="layout-menu-fixed layout-compact <?php echo $theme_class; ?>"
  dir="ltr"
  data-theme="<?php echo $current_theme; ?>"
  data-bs-theme="<?php echo $current_theme; ?>"
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

    <print class="1baseLinkPath"></print>


    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo htmlspecialchars($baseAssetPath); ?>img/favicon/favicon.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet" />

    <!-- Icons CSS -->
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

    <!-- Core CSS (Essential Bootstrap Styles) -->
    <link rel="stylesheet" href="<?php echo htmlspecialchars($baseAssetPath); ?>css/core.css" class="template-customizer-core-css" />

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo htmlspecialchars($baseAssetPath); ?>css/base.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="<?php echo htmlspecialchars($baseAssetPath); ?>css/layout.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="<?php echo htmlspecialchars($baseAssetPath); ?>css/components/cards.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="<?php echo htmlspecialchars($baseAssetPath); ?>css/components/buttons.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="<?php echo htmlspecialchars($baseAssetPath); ?>css/components/dropdowns.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="<?php echo htmlspecialchars($baseAssetPath); ?>css/components/modals.css?v=<?php echo time(); ?>" /> <!-- New Modals CSS -->
    <link rel="stylesheet" href="<?php echo htmlspecialchars($baseAssetPath); ?>css/pages/login.css?v=<?php echo time(); ?>" />

    <!-- Vendors CSS (only essential ones, perfect-scrollbar.css is problematic) -->
    <link rel="stylesheet" href="<?php echo htmlspecialchars($baseAssetPath); ?>vendor/libs/perfect-scrollbar/perfect-scrollbar.css" /> 
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bs-stepper/dist/css/bs-stepper.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.css"> 
    
    <!-- Custom Font for Welcome Message (Michroma is already in base.css) -->
    <!-- <style>
      @font-face {
        font-family: 'Michroma';
        src: url('<?php echo htmlspecialchars($baseAssetPath); ?>fonts/michroma/Michroma.ttf') format('truetype');
        font-weight: normal;
        font-style: normal;
      }
    </style> -->

    <!-- Base dependencies -->
    <script src="<?php echo htmlspecialchars($baseAssetPath); ?>js/utils/logger.js?v=<?php echo time(); ?>"></script>
    <script src="<?php echo htmlspecialchars($baseAssetPath); ?>js/utils/config-manager.js?v=<?php echo time(); ?>"></script>
    <script src="<?php echo htmlspecialchars($baseAssetPath); ?>js/utils/dependency-manager.js?v=<?php echo time(); ?>"></script>
    <script src="<?php echo htmlspecialchars($baseAssetPath); ?>js/module-fix.js?v=<?php echo time(); ?>"></script>

    <!-- Chart.js for doughnut charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- ApexCharts JS -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>

    <!-- BS-Stepper JS -->
    <script src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js"></script>

    <!-- Our Refactored Helpers -->
    <script src="<?php echo htmlspecialchars($baseAssetPath); ?>js/utils/helpers.js?v=<?php echo time(); ?>"></script>
    
    <!-- Theme and UI components (after core libraries) -->
    <script src="<?php echo htmlspecialchars($baseAssetPath); ?>js/theme-switcher.js?v=<?php echo time(); ?>"></script>
    <script src="<?php echo htmlspecialchars($baseAssetPath); ?>js/sidebar-toggle.js?v=<?php echo time(); ?>"></script>
    
    <!-- Essential custom JS files (now initialized by app.js) -->
    <script src="<?php echo htmlspecialchars($baseAssetPath); ?>js/wa-modal.js?v=<?php echo time(); ?>"></script>
    <script src="<?php echo htmlspecialchars($baseAssetPath); ?>js/utils/modal-upgrade.js?v=<?php echo time(); ?>"></script>
    <script src="<?php echo htmlspecialchars($baseAssetPath); ?>js/wa-table.js?v=<?php echo time(); ?>"></script>
    
    <!-- Our Modular JS Files (must be loaded before app.js) -->
    <script src="<?php echo htmlspecialchars($baseAssetPath); ?>js/modules/ui-components.js?v=<?php echo time(); ?>"></script>
    <script src="<?php echo htmlspecialchars($baseAssetPath); ?>js/modules/layout.js?v=<?php echo time(); ?>"></script>

    <!-- Our Main Application JS -->
    <script src="<?php echo htmlspecialchars($baseAssetPath); ?>js/app.js?v=<?php echo time(); ?>"></script>
    
    <!-- Cache Management JS -->
    <script src="<?php echo htmlspecialchars($baseAssetPath); ?>js/cache-management.js?v=<?php echo time(); ?>"></script>

    <!-- Removed old/redundant JS files -->
    <!-- <script src="<?php echo htmlspecialchars($baseAssetPath); ?>js/main.js"></script> -->
    <!-- <script src="<?php echo htmlspecialchars($baseAssetPath); ?>js/page-transitions.js"></script> -->
    <!-- <script src="<?php echo htmlspecialchars($baseAssetPath); ?>js/consolidated-dropdown-fix.js"></script> -->
    <!-- <script src="<?php echo htmlspecialchars($baseAssetPath); ?>js/modal-handler.js"></script> -->
    <!-- <script src="<?php echo htmlspecialchars($baseAssetPath); ?>vendor/js/helpers.js"></script> -->
    <!-- <script src="<?php echo htmlspecialchars($baseAssetPath); ?>vendor/js/menu.js"></script> -->

</head>

<body>
  <!-- Layout wrapper -->
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
     
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
        <?php
        // Diagnostic: Check isLoggedIn() and current page
        echo "<!-- DIAGNOSTIC: isLoggedIn() = " . (isLoggedIn() ? 'true' : 'false') . " -->\n";
        echo "<!-- DIAGNOSTIC: Current Page = " . htmlspecialchars($_SERVER["PHP_SELF"]) . " -->\n";
        echo "<!-- DIAGNOSTIC: Is Login Page = " . (strpos($_SERVER["PHP_SELF"], 'login.php') !== false ? 'true' : 'false') . " -->\n";

        if (isLoggedIn() && strpos($_SERVER["PHP_SELF"], 'login.php') === false): ?>
        <nav
          class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
          id="layout-navbar">
          <!-- Mobile menu toggle button - only visible on mobile -->
          <div class="d-xl-none me-3"> <!-- Added me-3 for spacing -->
            <a class="nav-link px-0 me-xl-4 js-layout-menu-toggle" href="javascript:void(0)">
              <i class="bx bx-menu bx-sm"></i>
            </a>
          </div>
          
          <!-- Desktop sidebar toggle - REMOVED -->

          <div class="navbar-nav-right d-flex align-items-center justify-content-between flex-grow-1" id="navbar-collapse"> <!-- Removed w-100, added flex-grow-1 -->
            <!-- Page Title - Left aligned -->
            <div class="navbar-nav align-items-center">
              <h4 class="page-title m-0"><?php echo htmlspecialchars($pageTitle); ?></h4>
            </div>
            
            <ul class="navbar-nav flex-row align-items-center">
              <!-- Theme toggle button -->
              <li class="nav-item me-3" style="list-style: none; margin-right: 20px;">
                <button id="theme-toggle-btn" class="btn btn-icon rounded-circle">
                  <i class="bx bx-sun theme-icon-light"></i>
                  <i class="bx bx-moon theme-icon-dark d-none"></i>
                </button>
              </li>

              <!-- User welcome and role with better alignment -->
              <li class="nav-item me-3">
                <div class="navbar-text text-end">
                  <div class="user-welcome">Welcome, <?php echo htmlspecialchars($userName); ?>!</div> 
                  <div class="user-role"><?php echo htmlspecialchars($userRole); ?></div>
                  <?php if (isset($_SESSION['switched_user_id'])): ?>
                  <div class="mt-1 text-warning">
                    <i class="bx bx-user-pin"></i> 
                    <small>You switched to <?php echo htmlspecialchars($_SESSION['switched_user_name']); ?>'s view</small>
                    <a href="<?php echo htmlspecialchars($baseLinkPath); ?>dashboards/switch_back.php" class="btn btn-link btn-sm p-0 ms-2 text-primary" style="font-size: 0.8rem;">Switch Back</a>
                  </div>
                  <?php endif; ?>
                </div>
              </li>

              <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" id="userDropdownToggle" data-bs-toggle="dropdown" aria-expanded="false">
                  <div class="avatar avatar-online">
                    <img src="<?php echo htmlspecialchars($avatarPath); ?>?v=<?php echo time(); ?>" alt class="w-px-40 h-auto rounded-circle" style="object-fit: cover;"/>
                  </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdownToggle">
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

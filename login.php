<?php
// login.php

// Ensure session is started. This should be the very first thing.
// config.php also does this, but it's safe to have it here too.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If a user is already logged in, redirect them to their dashboard.
// Avoid showing the login page to already authenticated users.
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    $dashboard_path = 'dashboards/index.php'; // Always use the main dashboard
    header("Location: " . $dashboard_path);
    exit;
}

// Require database configuration. This also ensures session_start() has been called.
require_once "includes/config.php"; // For $conn
// auth.php is not strictly needed by login.php itself, but good practice if you use its functions.
// require_once "includes/auth.php"; 

$error_message = '';
$success_message = '';

// Check for messages passed via GET parameters (e.g., after logout)
if (isset($_GET['message'])) {
    if ($_GET['message'] === 'logged_out') {
        $success_message = "You have been successfully logged out.";
    } elseif ($_GET['message'] === 'login_required') {
        $error_message = "Please log in to access the requested page.";
    } elseif ($_GET['message'] === 'session_expired') {
        $error_message = "Your session has expired. Please log in again.";
    }
    // Add more custom messages as needed
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ensure database connection is valid before proceeding
    if (!$conn || $conn->connect_error) {
        $error_message = "Database connection error. Please try again later or contact support.";
        error_log("Login attempt failed: Database connection error.");
    } else {
        $username_or_email = trim($_POST['username_or_email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username_or_email) || empty($password)) {
            $error_message = "Please enter both email and password.";
        } else {
            // Prepare statement to prevent SQL injection.
            // Users must have Status = 'Active' to log in.
            $sql = "SELECT UserID, Username, Password, Email, Role, RoleID, FirstName, LastName, AvatarPath 
                    FROM Users 
                    WHERE (Username = ? OR Email = ?) AND Status = 'Active'";
            
            $stmt = $conn->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param("ss", $username_or_email, $username_or_email);
                
                if (!$stmt->execute()) {
                    $error_message = "Login query execution failed. Please try again.";
                    error_log("Login page DB execute error: " . $stmt->error . " for input: " . $username_or_email); // Fixed PHP syntax error here
                } else {
                    $result = $stmt->get_result();

                    if ($result->num_rows === 1) {
                        $user = $result->fetch_assoc();
                        // Verify the password
                        if (password_verify($password, $user['Password'])) {
                            // Password is correct, set session variables
                            $_SESSION["user_id"] = $user["UserID"];
                            $_SESSION["username"] = $user["Username"];
                            $_SESSION["email"] = $user["Email"];
                            $_SESSION["user_role"] = $user["Role"];
                            $_SESSION["role_id"] = $user["RoleID"]; // Store RoleID for RBAC
                            $_SESSION["FullName"] = trim(($user["FirstName"] ?? '') . " " . ($user["LastName"] ?? ''));
                            $_SESSION["AvatarPath"] = $user["AvatarPath"] ?? 'assets/img/avatars/1.png'; // Default avatar
                            
                            // Regenerate session ID for security (prevents session fixation)
                            session_regenerate_id(true);
                            
                            // Load user permissions into session for faster access
                            require_once "includes/auth.php";
                            loadUserPermissions();

                            // Update LastLogin timestamp
                            $update_login_stmt = $conn->prepare("UPDATE Users SET LastLogin = NOW() WHERE UserID = ?");
                            if($update_login_stmt) {
                                $update_login_stmt->bind_param("i", $user["UserID"]);
                                $update_login_stmt->execute();
                                $update_login_stmt->close();
                            } else {
                                error_log("Failed to prepare UpdatedAt update statement: " . $conn->error);
                            }

                            // Always redirect to the main dashboard
                            $dashboard_path = 'dashboards/index.php';

                            header("Location: " . $dashboard_path);
                            exit;
                        } else {
                            // Invalid password
                            $error_message = "Invalid email or password.";
                        }
                    } else {
                        // User not found or account is not active
                        $error_message = "Invalid email or password, or account is inactive.";
                    }
                }
                $stmt->close();
            } else {
                // SQL statement preparation failed
                $error_message = "Database error (prepare failed). Please try again later.";
                error_log("Login page DB prepare error: " . $conn->error);
            }
        }
    }
}

// Close DB connection if it was opened (config.php opens it)
if (isset($conn) && !$conn->connect_error) {
    $conn->close();
}

// Determine base path for assets (login.php is in the root 'wa' directory)
$baseAssetPath = "assets/"; 
?>
<!DOCTYPE html>
<html
  lang="en"
  class="light-style layout-menu-fixed layout-compact"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="<?php echo htmlspecialchars($baseAssetPath); ?>"
  data-template="vertical-menu-template-free">
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Login | Water Academy</title>

    <meta name="description" content="Login to Water Academy Training Management System" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon/favicon.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet" />

    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css">

    <!-- Core CSS -->
    <link rel="stylesheet" href="assets/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="assets/css/base.css" />
    <link rel="stylesheet" href="assets/css/layout.css" />
    <link rel="stylesheet" href="assets/css/pages/login.css" />
    
    <!-- Helpers -->
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
  </head>

  <body class="login-page">
    <!-- Content -->

    <h2 class="login-title">Water Academy Visualizer</h2>
    <div class="container-xxl">
      <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
          <!-- Login Card -->
          <div class="login-card">
            <div class="card-body">
              <!-- Logo -->
              <div class="app-brand justify-content-center">
                <a href="index.php" class="app-brand-link gap-2">
                  <span class="app-brand-logo demo">
                    <img src="assets/img/logos/waLogoWhite.png" alt="Water Academy Logo" class="login-logo">
                  </span>
                </a>
              </div>
              <!-- /Logo -->

              <form id="formAuthentication" class="mb-3 login-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="mb-3">
                  <label for="username_or_email" class="form-label">Email</label>
                  <input
                    type="text"
                    class="form-control"
                    id="username_or_email"
                    name="username_or_email"
                    placeholder="Enter your email"
                    value="<?php echo isset($_POST['username_or_email']) ? htmlspecialchars($_POST['username_or_email']) : ''; ?>"
                    autofocus required />
                </div>
                <div class="mb-3 form-password-toggle">
                  <div class="d-flex justify-content-between">
                    <label class="form-label" for="password">Password</label>
                  </div>
                  <div class="input-group input-group-merge">
                    <input
                      type="password"
                      id="password"
                      class="form-control"
                      name="password"
                      placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                      aria-describedby="password" required />
                    <span class="input-group-text cursor-pointer" id="togglePassword"><i class="bx bx-hide"></i></span>
                  </div>
                  <div class="text-end mt-1">
                    <a href="#" class="text-muted"><small>Forgot Password?</small></a>
                  </div>
                </div>
                <div class="mb-3 text-center">
                  <button class="btn btn-primary d-grid w-100 mx-auto" type="submit">Sign in</button>
                </div>
              </form>
            </div>
          </div>
          <!-- /Login Card -->
          <img src="assets/img/bg/visu.png" alt="Visualizer Logo" class="visu-overlay-logo">
        </div>
      </div>
    </div>

    <!-- / Content -->

    <!-- Main JS (app.js now handles all initializations) -->
    <!-- Error Handler (if still needed, integrate into app.js or keep separate if truly standalone) -->
    <!-- <script src="assets/js/Login-error-handler.js"></script> -->
    <script>
      // Password toggle functionality (moved from main.js or old helpers)
      const togglePassword = document.querySelector('#togglePassword');
      const password = document.querySelector('#password');

      if (togglePassword && password) {
        togglePassword.addEventListener('click', function (e) {
          const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
          password.setAttribute('type', type);
          this.querySelector('i').classList.toggle('bx-show');
          this.querySelector('i').classList.toggle('bx-hide');
        });
      }
    </script>

  </body>
</html>
</content>

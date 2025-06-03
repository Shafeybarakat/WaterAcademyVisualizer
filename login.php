<?php
// login.php

// Ensure session is started. This should be the very first thing.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If a user is already logged in, redirect them to their dashboard.
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    $dashboard_path = 'dashboards/index.php'; // Always use the main dashboard
    header("Location: " . $dashboard_path);
    exit;
}

// Require database configuration.
require_once "includes/config.php"; // For $conn
require_once "includes/auth.php"; // For loadUserPermissions()

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
                    error_log("Login page DB execute error: " . $stmt->error . " for input: " . $username_or_email);
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
// Using BASE_ASSET_PATH defined in config.php
$baseAssetPath = BASE_ASSET_PATH; 
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Water Academy Visualizer</title>
    <meta name="description" content="Login to Water Academy Training Management System" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= $baseAssetPath ?>img/favicon/favicon.ico" />

    <!-- Tailwind CSS -->
    <link href="<?= $baseAssetPath ?>css/tailwind.css" rel="stylesheet">
    <link href="<?= $baseAssetPath ?>css/custom.css" rel="stylesheet">
    
    <!-- Alpine.js (deferred) -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <!-- Main Application JS -->
    <script src="<?= $baseAssetPath ?>js/app.js" defer></script>

    <style>
        body {
            background-image: url('<?= $baseAssetPath ?>img/bg/01x.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            font-family: 'Public Sans', sans-serif; /* Assuming Public Sans is desired */
        }
        .login-card-container {
            background-color: rgba(255, 255, 255, 0.9); /* 90% transparency */
            backdrop-filter: blur(5px); /* Optional: adds a blur effect to the background */
        }
        .login-title-overlay {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            color: white;
            font-size: 2.5rem; /* Adjust as needed */
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            z-index: 10;
            white-space: nowrap;
        }
        .visualizer-logo-bottom {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            text-align: center;
        }
        .visualizer-logo-bottom img {
            height: 80px; /* Adjust size as needed */
            width: auto;
        }
    </style>
</head>
<body class="h-full flex items-center justify-center">
    <div class="login-title-overlay">Water Academy Visualizer</div>
    <div class="min-h-screen flex items-center justify-center w-full">
        <div class="max-w-md w-full p-8 rounded-lg shadow-lg relative login-card-container">
            <div class="flex justify-center mb-6">
                <img src="<?= $baseAssetPath ?>img/logos/waLogoWhite.png" alt="Water Academy Logo" class="h-16">
            </div>
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Log in</h2>

            <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline"><?= htmlspecialchars($error_message) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Success!</strong>
                    <span class="block sm:inline"><?= htmlspecialchars($success_message) ?></span>
                </div>
            <?php endif; ?>

            <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="space-y-6">
                <div>
                    <label for="username_or_email" class="block text-sm font-medium text-gray-700">Username</label>
                    <input
                        type="text"
                        id="username_or_email"
                        name="username_or_email"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        placeholder="Enter your username or email"
                        value="<?= htmlspecialchars($_POST['username_or_email'] ?? ''); ?>"
                        required
                        autofocus
                    />
                </div>
                <div x-data="{ showPassword: false }">
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <a href="#" class="text-sm text-blue-600 hover:text-blue-500">Forgot Password?</a>
                    </div>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <input
                            :type="showPassword ? 'text' : 'password'"
                            id="password"
                            name="password"
                            class="block w-full pr-10 pl-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            placeholder="••••••••"
                            required
                        />
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center text-sm leading-5">
                            <button type="button" @click="showPassword = !showPassword" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                                <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 .98-3.14 3.28-5.58 6.29-7.04M9.5 12a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12c-1.274 4.057-5.064 7-9.542 7-1.056 0-2.087-.12-3.08-.35M12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                <div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Login
                    </button>
                </div>
            </form>
            <div class="visualizer-logo-bottom">
                <img src="<?= $baseAssetPath ?>img/visualizerlogo.png" alt="Visualizer Logo">
                <p class="text-xs text-gray-600 mt-1">Water Academy</p>
                <p class="text-xs text-gray-600">Shafey Barakat</p>
            </div>
        </div>
    </div>
</body>
</html>

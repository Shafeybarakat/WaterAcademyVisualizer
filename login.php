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
            $error_message = "Please enter both username/email and password.";
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
                            $error_message = "Invalid username/email or password.";
                        }
                    } else {
                        // User not found or account is not active
                        $error_message = "Invalid username/email or password, or account is inactive.";
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
        @import url('https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;700&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Michroma&display=swap'); /* For "Water Academy Visualizer" title */
        @import url('https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;700&display=swap'); /* For "Shafey Barakat" */

        body {
            background-image: url('<?= $baseAssetPath ?>img/bg/01x.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            font-family: 'Public Sans', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            position: relative; /* For absolute positioning of title/logo */
            overflow: hidden; /* Prevent scrollbars from background image */
        }
        .login-card-container {
            background-color: rgba(255, 255, 255, 0.1); /* 10% opacity for glass effect */
            backdrop-filter: blur(10px); /* Stronger blur for glass effect */
            border: 1px solid rgba(255, 255, 255, 0.2); /* Subtle white border */
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37); /* Stronger shadow */
            border-radius: 10px;
            padding: 2rem;
            width: 100%;
            max-width: 400px; /* Adjust max-width as needed */
            text-align: center;
            position: relative; /* For internal absolute positioning */
            display: flex;
            flex-direction: column;
            justify-content: space-between; /* Distribute content vertically */
            min-height: 500px; /* Adjust height to accommodate all elements */
        }
        .login-title-main {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            color: white;
            font-family: 'Michroma', sans-serif; /* Specific font for main title */
            font-size: 2.5rem; /* Adjust as needed */
            font-weight: normal; /* Michroma is usually bold by nature */
            text-shadow: 2px 2px 8px rgba(0,0,0,0.7); /* Stronger shadow */
            z-index: 10;
            white-space: nowrap;
            width: 100%; /* Ensure it spans full width for centering */
            text-align: center;
        }
        .visualizer-logo-bottom {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            text-align: center;
            color: white; /* Text color for bottom logo details */
        }
        .visualizer-logo-bottom img {
            height: 80px; /* Adjust size as needed */
            width: auto;
            filter: drop-shadow(0 0 5px rgba(0,0,0,0.5)); /* Shadow for the logo */
        }
        .visualizer-logo-bottom p {
            margin: 0;
            line-height: 1.2;
        }
        .visualizer-logo-bottom .water-academy-text {
            font-size: 0.8rem;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .visualizer-logo-bottom .shafey-barakat-text {
            font-family: 'Dancing Script', cursive; /* Specific font for signature */
            font-size: 1.2rem;
            margin-top: 5px;
        }
        .input-group-with-icon {
            position: relative;
        }
        .input-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #6B7280; /* gray-500 */
            z-index: 1; /* Ensure icon is above input */
        }
        .input-field-with-icon {
            padding-left: 2.5rem; /* Space for icon */
        }
        .password-toggle-button {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #6B7280; /* gray-500 */
            z-index: 1;
        }
        .login-button-gradient {
            background-image: linear-gradient(to right, #3B82F6, #1D4ED8); /* Blue gradient */
            border: none;
            color: white;
            font-weight: bold;
            transition: all 0.2s ease-in-out;
        }
        .login-button-gradient:hover {
            background-image: linear-gradient(to right, #1D4ED8, #3B82F6);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body class="h-full">
    <div class="login-title-main">Water Academy Visualizer</div>
    <div class="min-h-screen flex items-center justify-center w-full">
        <div class="login-card-container max-w-md w-full rounded-lg shadow-lg relative">
            <div class="flex justify-center mb-6">
                <img src="<?= $baseAssetPath ?>img/logos/waLogoWhite.png" alt="Water Academy Logo" class="h-24"> <!-- Larger logo -->
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
                <div class="input-group-with-icon">
                    <label for="username_or_email" class="sr-only">Username</label>
                    <div class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <input
                        type="text"
                        id="username_or_email"
                        name="username_or_email"
                        class="input-field-with-icon mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        placeholder="Username"
                        value="<?= htmlspecialchars($_POST['username_or_email'] ?? ''); ?>"
                        required
                        autofocus
                    />
                </div>
                <div x-data="{ showPassword: false }" class="input-group-with-icon">
                    <div class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 7V6a3 3 0 00-6 0v1h6z" />
                        </svg>
                    </div>
                    <input
                        :type="showPassword ? 'text' : 'password'"
                        id="password"
                        name="password"
                        class="input-field-with-icon block w-full pr-10 pl-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        placeholder="Password"
                        required
                    />
                    <button type="button" @click="showPassword = !showPassword" class="password-toggle-button">
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
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center">
                        <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="remember-me" class="ml-2 block text-gray-700">
                            Keep me logged in
                        </label>
                    </div>
                    <a href="#" class="font-medium text-blue-600 hover:text-blue-500">Forgot Password?</a>
                </div>
                <div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white login-button-gradient focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Login
                    </button>
                </div>
            </form>
            <div class="visualizer-logo-bottom">
                <img src="<?= $baseAssetPath ?>img/visualizerlogo.png" alt="Visualizer Logo">
                <p class="water-academy-text">Water Academy</p>
                <p class="shafey-barakat-text">Shafey Barakat</p>
            </div>
        </div>
    </div>
</body>
</html>

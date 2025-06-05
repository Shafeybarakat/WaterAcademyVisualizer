<?php
$pageTitle = "System Settings"; // Set page title
// Include the header - this also includes config.php and auth.php
include("../includes/header.php"); 

// RBAC guard: Only users with 'access_settings' permission can access this page.
if (!require_permission('access_settings', '../login.php')) {
    echo '<div class="container-xxl flex-grow-1 container-p-y"><div class="alert alert-danger" role="alert">' . ($_SESSION['access_denied_message'] ?? 'You do not have permission to access this page.') . '</div></div>';
    include_once "../includes/footer.php"; // Ensure footer is included
    die(); // Terminate script
}

// Process theme change if submitted
if (isset($_POST['action']) && $_POST['action'] == 'change_theme') {
    $theme = $_POST['theme'];
    // In a real implementation, you would save this to the user's preferences in the database
    // For now, we'll just set a cookie
    setcookie('wa_theme', $theme, time() + (86400 * 30), "/"); // 30 days
    
    // Redirect to avoid form resubmission
    header("Location: settings.php?tab=themes&success=theme_updated");
    exit();
}

// Determine which tab to show
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';

?>

<!-- Content specific to this page -->
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Tabs navigation -->
    <div class="nav-align-top mb-4">
        <ul class="nav nav-tabs nav-fill" role="tablist">
            <li class="nav-item">
                <a class="nav-link <?php echo ($tab == 'general') ? 'active' : ''; ?>" 
                   href="settings.php?tab=general" role="tab">
                    <i class="ri-settings-3-line me-1"></i> General Settings
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($tab == 'themes') ? 'active' : ''; ?>" 
                   href="settings.php?tab=themes" role="tab">
                    <i class="ri-palette-line me-1"></i> Color Themes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($tab == 'email') ? 'active' : ''; ?>" 
                   href="settings.php?tab=email" role="tab">
                    <i class="ri-mail-settings-line me-1"></i> Email Settings
                </a>
            </li>
        </ul>
        
        <div class="tab-content">
            <?php if ($tab == 'general'): ?>
            <!-- General Settings Tab -->
            <div class="tab-pane fade show active" id="general-settings" role="tabpanel">
                <div class="card">
                    <h5 class="card-header">System Settings</h5>
                    <div class="card-body">
                        <p>This page is reserved for system-level configuration and settings, accessible only to the Super Admin.</p>
                        <p>Future settings options will appear here, such as:</p>
                        <ul>
                            <li>API key management</li>
                            <li>Database maintenance tools</li>
                            <li>User role permission details (view/edit)</li>
                        </ul>
                        <div class="alert alert-info" role="alert">
                            <h6 class="alert-heading fw-bold mb-1">Under Construction!</h6>
                            <span>Some settings panels are currently under development.</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php elseif ($tab == 'themes'): ?>
            <!-- Color Themes Tab -->
            <div class="tab-pane fade show active" id="color-themes" role="tabpanel">
                <div class="card">
                    <h5 class="card-header">Color Themes</h5>
                    <div class="card-body">
                        <?php if (isset($_GET['success']) && $_GET['success'] == 'theme_updated'): ?>
                        <div class="alert alert-success alert-dismissible" role="alert">
                            Theme updated successfully!
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>
                        
                        <p>Choose a color theme for the Water Academy dashboard. The theme will be applied to all pages.</p>
                        
                        <form action="settings.php" method="post">
                            <input type="hidden" name="action" value="change_theme">
                            
                            <div class="row g-4 mb-4">
                                <!-- Default Theme -->
                                <div class="col-md-4">
                                    <div class="card theme-card <?php echo (!isset($_COOKIE['wa_theme']) || $_COOKIE['wa_theme'] == 'default') ? 'border-primary' : ''; ?>">
                                        <div class="card-body p-0">
                                            <div class="theme-preview default-theme">
                                                <div class="theme-sidebar"></div>
                                                <div class="theme-content">
                                                    <div class="theme-header"></div>
                                                    <div class="theme-main">
                                                        <div class="theme-card-1"></div>
                                                        <div class="theme-card-2"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="p-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="theme" id="theme-default" value="default" <?php echo (!isset($_COOKIE['wa_theme']) || $_COOKIE['wa_theme'] == 'default') ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="theme-default">
                                                        Default (Blue)
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Dark Theme -->
                                <div class="col-md-4">
                                    <div class="card theme-card <?php echo (isset($_COOKIE['wa_theme']) && $_COOKIE['wa_theme'] == 'dark') ? 'border-primary' : ''; ?>">
                                        <div class="card-body p-0">
                                            <div class="theme-preview dark-theme">
                                                <div class="theme-sidebar"></div>
                                                <div class="theme-content">
                                                    <div class="theme-header"></div>
                                                    <div class="theme-main">
                                                        <div class="theme-card-1"></div>
                                                        <div class="theme-card-2"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="p-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="theme" id="theme-dark" value="dark" <?php echo (isset($_COOKIE['wa_theme']) && $_COOKIE['wa_theme'] == 'dark') ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="theme-dark">
                                                        Dark
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Light Theme -->
                                <div class="col-md-4">
                                    <div class="card theme-card <?php echo (isset($_COOKIE['wa_theme']) && $_COOKIE['wa_theme'] == 'light') ? 'border-primary' : ''; ?>">
                                        <div class="card-body p-0">
                                            <div class="theme-preview light-theme">
                                                <div class="theme-sidebar"></div>
                                                <div class="theme-content">
                                                    <div class="theme-header"></div>
                                                    <div class="theme-main">
                                                        <div class="theme-card-1"></div>
                                                        <div class="theme-card-2"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="p-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="theme" id="theme-light" value="light" <?php echo (isset($_COOKIE['wa_theme']) && $_COOKIE['wa_theme'] == 'light') ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="theme-light">
                                                        Light
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Green Theme -->
                                <div class="col-md-4">
                                    <div class="card theme-card <?php echo (isset($_COOKIE['wa_theme']) && $_COOKIE['wa_theme'] == 'green') ? 'border-primary' : ''; ?>">
                                        <div class="card-body p-0">
                                            <div class="theme-preview green-theme">
                                                <div class="theme-sidebar"></div>
                                                <div class="theme-content">
                                                    <div class="theme-header"></div>
                                                    <div class="theme-main">
                                                        <div class="theme-card-1"></div>
                                                        <div class="theme-card-2"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="p-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="theme" id="theme-green" value="green" <?php echo (isset($_COOKIE['wa_theme']) && $_COOKIE['wa_theme'] == 'green') ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="theme-green">
                                                        Green
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Purple Theme -->
                                <div class="col-md-4">
                                    <div class="card theme-card <?php echo (isset($_COOKIE['wa_theme']) && $_COOKIE['wa_theme'] == 'purple') ? 'border-primary' : ''; ?>">
                                        <div class="card-body p-0">
                                            <div class="theme-preview purple-theme">
                                                <div class="theme-sidebar"></div>
                                                <div class="theme-content">
                                                    <div class="theme-header"></div>
                                                    <div class="theme-main">
                                                        <div class="theme-card-1"></div>
                                                        <div class="theme-card-2"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="p-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="theme" id="theme-purple" value="purple" <?php echo (isset($_COOKIE['wa_theme']) && $_COOKIE['wa_theme'] == 'purple') ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="theme-purple">
                                                        Purple
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">Apply Theme</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php elseif ($tab == 'email'): ?>
            <!-- Email Settings Tab -->
            <div class="tab-pane fade show active" id="email-settings" role="tabpanel">
                <div class="card">
                    <h5 class="card-header">Email Settings</h5>
                    <div class="card-body">
                        <p>Configure email server settings for system notifications and reports.</p>
                        
                        <div class="alert alert-info" role="alert">
                            <h6 class="alert-heading fw-bold mb-1">Under Construction!</h6>
                            <span>Email settings configuration is currently under development.</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- / Content specific to this page -->

<!-- CSS for theme previews -->
<style>
.theme-card {
    cursor: pointer;
    transition: all 0.3s ease;
    overflow: hidden;
}

.theme-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.theme-card.border-primary {
    border: 2px solid var(--primary-blue) !important;
}

.theme-preview {
    height: 160px;
    display: flex;
    overflow: hidden;
}

.theme-sidebar {
    width: 25%;
    height: 100%;
}

.theme-content {
    width: 75%;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.theme-header {
    height: 20%;
    margin-bottom: 5px;
}

.theme-main {
    height: 80%;
    display: flex;
    gap: 5px;
    padding: 5px;
}

.theme-card-1, .theme-card-2 {
    height: 100%;
    flex: 1;
    border-radius: 4px;
}

/* Default Theme */
.default-theme .theme-sidebar {
    background: linear-gradient(180deg, #2c3e50 0%, #1a252f 100%);
}
.default-theme .theme-header {
    background-color: #ffffff;
}
.default-theme .theme-content {
    background-color: #F8F9FC;
}
.default-theme .theme-card-1, .default-theme .theme-card-2 {
    background-color: #ffffff;
    border: 1px solid #3498db;
}

/* Dark Theme */
.dark-theme .theme-sidebar {
    background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
}
.dark-theme .theme-header {
    background-color: #1e293b;
}
.dark-theme .theme-content {
    background-color: #0f172a;
}
.dark-theme .theme-card-1, .dark-theme .theme-card-2 {
    background-color: #1e293b;
    border: 1px solid #4361ee;
}

/* Light Theme */
.light-theme .theme-sidebar {
    background: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%);
}
.light-theme .theme-header {
    background-color: #ffffff;
}
.light-theme .theme-content {
    background-color: #f1f5f9;
}
.light-theme .theme-card-1, .light-theme .theme-card-2 {
    background-color: #ffffff;
    border: 1px solid #3b82f6;
}

/* Green Theme */
.green-theme .theme-sidebar {
    background: linear-gradient(180deg, #064e3b 0%, #065f46 100%);
}
.green-theme .theme-header {
    background-color: #ffffff;
}
.green-theme .theme-content {
    background-color: #f0fdf4;
}
.green-theme .theme-card-1, .green-theme .theme-card-2 {
    background-color: #ffffff;
    border: 1px solid #10b981;
}

/* Purple Theme */
.purple-theme .theme-sidebar {
    background: linear-gradient(180deg, #4c1d95 0%, #5b21b6 100%);
}
.purple-theme .theme-header {
    background-color: #ffffff;
}
.purple-theme .theme-content {
    background-color: #f5f3ff;
}
.purple-theme .theme-card-1, .purple-theme .theme-card-2 {
    background-color: #ffffff;
    border: 1px solid #8b5cf6;
}
</style>

<script>
// Make the entire theme card clickable
document.addEventListener('DOMContentLoaded', function() {
    const themeCards = document.querySelectorAll('.theme-card');
    
    themeCards.forEach(card => {
        card.addEventListener('click', function() {
            // Find the radio input inside this card and check it
            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;
        });
    });
});
</script>

<?php
if (isset($conn)) $conn->close();
// Include the new footer
include("../includes/footer.php"); 
?>

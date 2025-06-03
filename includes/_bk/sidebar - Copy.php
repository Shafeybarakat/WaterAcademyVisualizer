<?php
// This file assumes config.php and auth.php (for permission functions and session variables)
// are included before it, typically by header.php.

// Helper function to generate nav items conditionally
if (!function_exists("navItem")) {
    function navItem(string $label, string $href, string $iconClass, ?array $allowed_roles = null, ?string $required_permission = null): string {
        // Check permission first if specified
        if ($required_permission !== null && !hasPermission($required_permission)) {
            return ""; // Don't render if user doesn't have the required permission
        }
        
        // For backward compatibility, also check roles if specified
        if ($allowed_roles !== null && !has_role($allowed_roles)) {
            return ""; // Don't render if user doesn't have an allowed role
        }

        // Determine active state - improved to check if the current page contains the target path
        $current_page_path = $_SERVER["PHP_SELF"]; 
        $target_href_path = parse_url($href, PHP_URL_PATH);
        $target_filename = basename($target_href_path);
        
        // Check if the current page matches the target path OR contains the target filename
        $is_active = ($current_page_path === $target_href_path) || (strpos($current_page_path, $target_filename) !== false);
        $active_class = $is_active ? " active" : "";

        return
        '<li class="menu-item' . $active_class . '">
          <a href="' . htmlspecialchars($href) . '" class="menu-link" style="position: relative; z-index: 20; pointer-events: auto;">
            <i class="menu-icon ' . htmlspecialchars($iconClass) . '"></i>
            <div class="menu-text">' . htmlspecialchars($label) . '</div>
            <span class="hover-effect"></span>
          </a>
        </li>';
    }
}

// Get user profile info if available
$userFullName = $_SESSION['user_first_name'] ?? 'User';
$userFullName .= ' ' . ($_SESSION['user_last_name'] ?? '');
$userRole = $_SESSION['user_role'] ?? 'Guest';
$userInitials = substr($_SESSION['user_first_name'] ?? 'U', 0, 1) . substr($_SESSION['user_last_name'] ?? 'S', 0, 1);
?>

<!-- Modern Glass-Effect Sidebar -->
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
<!-- Logo Container with Enhanced Styling -->
  <div class="app-brand demo">
    <a href="<?php echo htmlspecialchars($baseLinkPath); ?>dashboards/index.php" class="app-brand-link">
      <div class="logo-container">
        <!-- Logo will be dynamically switched based on theme -->
        <img src="<?php echo htmlspecialchars($baseAssetPath); ?>img/logos/waLogoBlue.png" alt="Water Academy Logo" id="sidebarLogo" class="theme-logo light-logo" style="width: 220px; height: 220px; object-fit: contain;" />
        <img src="<?php echo htmlspecialchars($baseAssetPath); ?>img/logos/waLogoWhite.png" alt="Water Academy Logo" id="sidebarLogoDark" class="theme-logo dark-logo" style="width: 220px; height: 220px; object-fit: contain; display: none;" />
      </div>
    </a>
    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
      <i class="ri-menu-fold-line align-middle"></i>
    </a>
  </div>

<!-- Add more vertical space below logo -->
  <div style="height: 20px;"></div> <!-- Increased space -->
  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">
    <!-- Home (renamed from Dashboard) -->
    <?php echo navItem("Home", $baseLinkPath . "dashboards/index.php", "ri-home-4-line"); ?>
    
    <!-- Reports - Visible to users with reporting permissions -->
    <?php
    $can_see_reports_menu = hasAnyPermission(['access_group_reports', 'access_trainee_reports', 'access_attendance_reports']);
    if ($can_see_reports_menu):
        // Check if current page is a report page
        $isReportsActive = false;
        $report_pages = ["reports.php", "report_group_performance.php", "report_trainee_performance.php", "report_attendance_summary.php", "group-analytics.php"];
        foreach($report_pages as $r_page){
            if(strpos($_SERVER['PHP_SELF'], $r_page) !== false){
                $isReportsActive = true;
                break;
            }
        }
    ?>
    <li class="menu-item<?php echo $isReportsActive ? ' active' : ''; ?>">
      <a href="<?php echo htmlspecialchars($baseLinkPath); ?>dashboards/reports.php" class="menu-link" style="position: relative; z-index: 20; pointer-events: auto;">
        <i class="menu-icon ri-line-chart-line"></i>
        <div class="menu-text">Analytics & Reports</div>
        <span class="hover-effect"></span>
      </a>
    </li>
    <?php endif; ?>
    
    <!-- Profile is accessible from the user's icon in the header -->

    <!-- Management - Visible to users with management permissions -->
    <?php if (hasAnyPermission(['manage_groups', 'view_groups', 'manage_trainees', 'view_trainees', 'manage_courses', 'view_courses'])): ?>
        <?php echo navItem("Groups", $baseLinkPath . "dashboards/groups.php", "ri-group-2-line", null, 'view_groups'); ?>
        <?php echo navItem("Trainees", $baseLinkPath . "dashboards/trainees.php", "ri-user-star-line", null, 'view_trainees'); ?>
        <?php echo navItem("Courses", $baseLinkPath . "dashboards/courses.php", "ri-book-open-line", null, 'view_courses'); ?>
        <?php echo navItem("Instructors", $baseLinkPath . "dashboards/instructors.php", "ri-user-voice-line", null, 'view_users'); ?>
        <?php echo navItem("Coordinators", $baseLinkPath . "dashboards/coordinators.php", "ri-user-settings-line", null, 'view_users'); ?>
    <?php endif; ?>

    <!-- Instructor Specific -->
    <?php if (hasAnyPermission(['record_grades', 'record_attendance'])): ?>
        <?php /* Removed "My Courses" nav button as requested */ ?>
        <?php echo navItem("Attendance & Grades", $baseLinkPath . "dashboards/attendance_grades.php", "ri-file-list-3-line", null, 'record_grades'); ?>
    <?php endif; ?>

    <!-- Coordinator Specific - Now handled by permissions in the Reports section -->

    <!-- Admin Section - Visible to users with admin permissions -->
    <?php if (hasPermission('manage_users')): ?>
        <?php echo navItem("Users", $baseLinkPath . "dashboards/users.php", "ri-user-settings-line", null, 'manage_users'); ?>
    <?php endif; ?>
    
    <?php /* Removed Settings nav button as it exists in the header dropdown menu */ ?>

    <!-- Sign Out -->
    <?php echo navItem("Sign Out", $baseLinkPath . "logout.php", "ri-logout-box-r-line"); ?>
    
    <!-- Theme Switcher removed from sidebar, now in header -->
  </ul>
  
  <!-- Enhanced Sidebar Footer -->
  <div class="sidebar-footer">
    <div class="version">Water Academy v2.1</div>
  </div>
</aside>

<!-- Add Remix Icons CDN with newer version -->
<link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

<!-- Theme switcher script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const themeSwitcher = document.getElementById('theme-switcher');
  if (themeSwitcher) {
    themeSwitcher.addEventListener('click', function() {
      // Get current theme or default to 'default'
      const currentTheme = document.body.className.match(/theme-\w+/) ? 
                          document.body.className.match(/theme-\w+/)[0].replace('theme-', '') : 
                          'default';
      
      // Available themes
      const themes = ['default', 'dark', 'light', 'green', 'purple', 'ocean'];
      
      // Get next theme
      const currentIndex = themes.indexOf(currentTheme);
      const nextIndex = (currentIndex + 1) % themes.length;
      const nextTheme = themes[nextIndex];
      
      // Remove all theme classes
      themes.forEach(theme => {
        document.body.classList.remove(`theme-${theme}`);
      });
      
      // Add new theme class
      document.body.classList.add(`theme-${nextTheme}`);
      
      // Save to localStorage
      localStorage.setItem('wa-theme', nextTheme);
      
      // Update icon based on theme
      const themeIcon = themeSwitcher.querySelector('.menu-icon');
      if (nextTheme === 'dark') {
        themeIcon.className = 'menu-icon ri-moon-line';
      } else if (nextTheme === 'light') {
        themeIcon.className = 'menu-icon ri-sun-line';
      } else {
        themeIcon.className = 'menu-icon ri-palette-line';
      }
    });
  }
  
  // Apply saved theme on load
  const savedTheme = localStorage.getItem('wa-theme');
  if (savedTheme) {
    document.body.classList.add(`theme-${savedTheme}`);
    
    // Update icon based on saved theme
    const themeIcon = document.querySelector('#theme-switcher .menu-icon');
    if (themeIcon) {
      if (savedTheme === 'dark') {
        themeIcon.className = 'menu-icon ri-moon-line';
      } else if (savedTheme === 'light') {
        themeIcon.className = 'menu-icon ri-sun-line';
      }
    }
  }
});
</script>

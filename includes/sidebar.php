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

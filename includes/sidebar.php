<?php
// includes/sidebar.php
$roleID = getUserRoleID();
?>
<!-- Sidebar -->
<aside
  x-show="true" 
  class="sidebar transform"
  :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen, 'sidebar-collapsed': sidebarCollapsed}"
  x-data="{ sidebarCollapsed: false }"
>
  <!-- Sidebar toggle button (visible only on desktop) -->
  <button 
    @click="sidebarCollapsed = !sidebarCollapsed; $dispatch('sidebar-toggle', {collapsed: sidebarCollapsed})" 
    class="sidebar-toggle hidden md:flex"
  >
    <i class="bx" :class="sidebarCollapsed ? 'bx-chevron-right' : 'bx-chevron-left'"></i>
  </button>
  <div class="sidebar-logo flex items-center justify-center p-4 border-b border-blue-700 dark:border-blue-900">
    <a href="<?= BASE_URL ?>dashboards/index.php" class="mx-auto">
      <img src="<?= BASE_ASSET_PATH ?>img/logos/waLogoBlue.png" alt="Water Academy" class="w-36 h-36 logo-dark">
      <img src="<?= BASE_ASSET_PATH ?>img/logos/waLogoWhite.png" alt="Water Academy" class="w-36 h-36 logo-light">
    </a>
    <button @click="sidebarOpen = false" class="text-white hover:text-gray-200 absolute right-4 sm:hidden">
      <i class="bx bx-x text-2xl"></i>
    </button>
  </div>

  <nav class="mt-2 menu-inner overflow-y-auto">
    <ul class="space-y-2">
      <!-- Home -->
      <li class="menu-item">
        <button onclick="window.location.href='<?= BASE_URL ?>dashboards/index.php'" class="sidebar-link py-3">
          <i class="bx bx-home-alt sidebar-icon text-xl"></i>
          <span class="font-medium">Home</span>
        </button>
      </li>
      
      <!-- Reports -->
      <li class="menu-item">
        <button onclick="window.location.href='<?= BASE_URL ?>dashboards/reports.php'" class="sidebar-link py-3">
          <i class="bx bx-line-chart sidebar-icon text-xl"></i>
          <span class="font-medium">Reports</span>
        </button>
      </li>
      
      <!-- Data Entry -->
      <li class="menu-item">
        <button onclick="window.location.href='<?= BASE_URL ?>dashboards/attendance_grades.php'" class="sidebar-link py-3">
          <i class="bx bx-spreadsheet sidebar-icon text-xl"></i>
          <span class="font-medium">Data Entry</span>
        </button>
      </li>
      
      <!-- Groups -->
      <li class="menu-item">
        <button onclick="window.location.href='<?= BASE_URL ?>dashboards/groups.php'" class="sidebar-link py-3">
          <i class="bx bx-group sidebar-icon text-xl"></i>
          <span class="font-medium">Groups</span>
        </button>
      </li>
      
      <!-- Courses -->
      <li class="menu-item">
        <button onclick="window.location.href='<?= BASE_URL ?>dashboards/courses.php'" class="sidebar-link py-3">
          <i class="bx bx-book-open sidebar-icon text-xl"></i>
          <span class="font-medium">Courses</span>
        </button>
      </li>
      
      <!-- Trainees -->
      <li class="menu-item">
        <button onclick="window.location.href='<?= BASE_URL ?>dashboards/trainees.php'" class="sidebar-link py-3">
          <i class="bx bx-user-check sidebar-icon text-xl"></i>
          <span class="font-medium">Trainees</span>
        </button>
      </li>
      
      <!-- Instructors -->
      <li class="menu-item">
        <button onclick="window.location.href='<?= BASE_URL ?>dashboards/instructors.php'" class="sidebar-link py-3">
          <i class="bx bx-user-voice sidebar-icon text-xl"></i>
          <span class="font-medium">Instructors</span>
        </button>
      </li>
      
      <!-- Coordinators -->
      <li class="menu-item">
        <button onclick="window.location.href='<?= BASE_URL ?>dashboards/coordinators.php'" class="sidebar-link py-3">
          <i class="bx bx-user-pin sidebar-icon text-xl"></i>
          <span class="font-medium">Coordinators</span>
        </button>
      </li>
      
      <!-- Users -->
      <li class="menu-item">
        <button onclick="window.location.href='<?= BASE_URL ?>dashboards/users.php'" class="sidebar-link py-3">
          <i class="bx bx-user sidebar-icon text-xl"></i>
          <span class="font-medium">Users</span>
        </button>
      </li>
      
      <!-- Logout (moved from bottom) -->
      <li class="menu-item mt-6">
        <button onclick="window.location.href='<?= BASE_URL ?>logout.php'" class="sidebar-link py-3">
          <i class="bx bx-log-out sidebar-icon text-xl"></i>
          <span class="font-medium">Logout</span>
        </button>
      </li>
    </ul>
  </nav>
  
  <div class="absolute bottom-0 left-0 right-0 border-t border-blue-700 dark:border-blue-900">
    <!-- Visualizer logo at bottom of sidebar - dark/light mode versions -->
    <div class="visualizer-logo-bottom">
      <img src="<?= BASE_ASSET_PATH ?>img/logos/visualizerlogob.png" alt="Visualizer" class="h-8 logo-dark">
      <img src="<?= BASE_ASSET_PATH ?>img/logos/visualizerlogow.png" alt="Visualizer" class="h-8 logo-light">
    </div>
  </div>
</aside>

<?php
// includes/header.php
require_once __DIR__ . '/config.php'; // defines $baseAssetPath, DB config
require_once __DIR__ . '/auth.php';

// The protect_authenticated_area function in auth.php handles redirection if not logged in.
// No need for a redundant check here.
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
  
  <!-- BoxIcons -->
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  
  <!-- JavaScript Libraries -->
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="<?= BASE_ASSET_PATH ?>js/app.js" defer></script>
  
  <!-- Global JavaScript Variables -->
  <script>
    // Make PHP constants available to JavaScript
    const BASE_URL = '<?= BASE_URL ?>';
    const BASE_ASSET_PATH = '<?= BASE_ASSET_PATH ?>';
  </script>
</head>
<body x-data="layout()" x-init="initLayout()" class="h-full bg-gray-100 dark:bg-gray-900">
  <!-- Page wrapper (sidebar + main) -->
  <div class="layout-wrapper">
    <?php include __DIR__ . '/sidebar.php'; ?>
    
    <!-- Main content wrapper -->
    <div class="layout-page">
      
      <!-- Top navigation bar -->
      <header class="top-nav px-6 py-3 flex items-center justify-between sticky top-0 z-10 bg-secondary-bg dark:bg-dark-bg text-white shadow-md h-20">
        <!-- Left side: Mobile menu button (mobile only) and page title -->
        <div class="flex items-center">
          <!-- Mobile menu button - only shown on mobile -->
          <button @click="sidebarOpen = !sidebarOpen" class="sm:hidden text-white hover:text-blue-200 focus:outline-none mr-4">
            <i class="bx bx-menu text-2xl"></i>
          </button>
          
          <!-- Page title -->
          <h1 class="text-xl font-michroma font-bold"><?= htmlspecialchars($pageTitle ?? 'Home') ?></h1>
        </div>
        
        <!-- Right side: Theme switcher and User info -->
        <div class="flex items-center">
          <!-- Dark mode toggle - moved right -->
          <button @click="toggleDarkMode()" id="theme-toggle-btn" class="text-white hover:text-blue-200 focus:outline-none mr-3">
            <i class="bx" :class="isDarkMode ? 'bx-sun' : 'bx-moon'" class="text-xl"></i>
          </button>
          
          <!-- User menu -->
          <div class="relative" x-data="{ userMenuOpen: false }">
            <div class="flex items-center">
              <!-- User welcome text and role -->
              <div class="text-right mr-3 hidden sm:block">
                <div class="user-welcome">Welcome, <?= htmlspecialchars($currentUser['firstName'] . ' ' . $currentUser['lastName']) ?></div>
                <div class="user-role"><?= htmlspecialchars($currentUser['role']) ?></div>
              </div>
              
              <!-- User avatar - properly circular with fixed size wrapper -->
              <div class="h-6 w-6 rounded-full overflow-hidden border border-white flex-shrink-0" style="max-height: 2rem;">
                <button @click="userMenuOpen = !userMenuOpen" class="w-full h-full">
                  <img src="<?= BASE_ASSET_PATH ?>img/avatars/3.png" alt="User Avatar" class="w-full h-full object-cover">
                </button>
              </div>
            </div>
          
            <!-- Dropdown menu -->
            <div x-show="userMenuOpen" 
                @click.away="userMenuOpen = false" 
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="dropdown-menu">
              
              <a href="<?= BASE_URL ?>dashboards/profile.php" class="dropdown-item">
                <i class="bx bx-user mr-2"></i> Profile
              </a>
              
              <?php if (hasPermission('manage_roles')): ?>
              <button type="button" 
                      @click="document.querySelector('#switchRoleModal').querySelector('[x-data]').__x.$data.open = true; userMenuOpen = false"
                      class="w-full text-left dropdown-item">
                <i class="bx bx-refresh mr-2"></i> Switch Role
              </button>
              <?php endif; ?>
              
              <a href="<?= BASE_URL ?>logout.php" class="dropdown-item">
                <i class="bx bx-log-out mr-2"></i> Logout
              </a>
            </div>
          </div>
        </div>
      </header>
      
      <!-- Main content container -->
      <div class="content-wrapper">
        <!-- Main content slot starts here -->

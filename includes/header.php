<?php
// includes/header.php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php'; // defines $baseAssetPath, DB config

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
</head>
<body x-data="layout()" x-init="initLayout()" class="h-full bg-gray-100">
  <!-- Page wrapper (sidebar + main) -->
  <div class="flex h-full">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <!-- Main content container -->
    <div :class="sidebarOpen || window.innerWidth >= 768 ? 'ml-64' : 'ml-0'" class="flex-1 flex flex-col transition-all duration-200">
      <!-- Top navigation bar -->
      <header class="flex items-center justify-between bg-white border-b shadow-sm px-4 py-3">
        <!-- Mobile menu button -->
        <button @click="sidebarOpen = !sidebarOpen" class="md:hidden text-gray-600 hover:text-gray-900">
          <!-- Heroicon: menu -->
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
        <div class="flex items-center space-x-4">
          <h1 class="text-xl font-semibold text-gray-800"><?= htmlspecialchars($pageTitle ?? '') ?></h1>
          <!-- Theme switcher or notifications could go here -->
        </div>
        <!-- User dropdown -->
        <div class="relative" x-data="{ open: false }">
          <button @click="open = !open" class="flex items-center text-gray-600 hover:text-gray-900 focus:outline-none">
            <span class="mr-2"><?= htmlspecialchars($currentUser['firstName'] . ' ' . $currentUser['lastName']) ?></span>
            <img src="<?= BASE_ASSET_PATH ?>images/avatar.png" alt="Avatar" class="h-8 w-8 rounded-full">
          </button>
          <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white border rounded-lg shadow-lg overflow-hidden z-20">
            <a href="/profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profile</a>
            <a href="/logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Logout</a>
          </div>
        </div>
      </header>
      <!-- Main content slot starts here -->

// assets/js/sidebar-toggle.js
(function() {
  const body = document.body;
  const sidebarToggle = document.querySelector('.layout-menu-toggle');
  const sidebar = document.querySelector('.layout-menu');
  const layoutOverlay = document.querySelector('.layout-overlay');

  if (sidebarToggle) {
    sidebarToggle.addEventListener('click', function() {
      WA_Logger.info('SidebarToggle', 'Sidebar toggle clicked');
      body.classList.toggle('layout-menu-expanded');
    });
  }

  if (layoutOverlay) {
    layoutOverlay.addEventListener('click', function() {
      WA_Logger.info('SidebarToggle', 'Layout overlay clicked (closing sidebar)');
      body.classList.remove('layout-menu-expanded');
    });
  }

  // Close sidebar on resize if it's expanded and screen is large enough
  window.addEventListener('resize', function() {
    if (window.innerWidth >= 1200 && body.classList.contains('layout-menu-expanded')) {
      WA_Logger.info('SidebarToggle', 'Resizing to desktop view, closing sidebar');
      body.classList.remove('layout-menu-expanded');
    }
  });

  // Initial check on load
  document.addEventListener('DOMContentLoaded', function() {
    if (window.innerWidth < 1200) {
      WA_Logger.info('SidebarToggle', 'Initial load on mobile, ensuring sidebar is closed');
      body.classList.remove('layout-menu-expanded');
    }
  });
})();

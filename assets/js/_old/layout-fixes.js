/**
 * Water Academy Layout Fixes
 * Minimal changes to fix mobile view while preserving original layout for desktop
 * Updated: May 30, 2025
 */

document.addEventListener('DOMContentLoaded', function() {
  console.log('Applying minimal layout fixes');
  
  // Fix mobile sidebar issues without changing desktop layout
  function fixMobileSidebar() {
    const sidebar = document.querySelector('.menu.menu-vertical');
    const layoutPage = document.querySelector('.layout-page');
    const isMobile = window.innerWidth < 1200;
    
    if (!sidebar || !layoutPage) return;
    
    if (isMobile) {
      // Mobile-specific fixes
      sidebar.style.position = 'fixed';
      sidebar.style.top = '0';
      sidebar.style.left = '0';
      sidebar.style.height = '100vh';
      sidebar.style.width = '260px';
      sidebar.style.zIndex = '2000';
      sidebar.style.transform = document.body.classList.contains('layout-menu-expanded') ? 
                                'translateX(0)' : 'translateX(-100%)';
      
      // Ensure menu items are clickable
      sidebar.querySelectorAll('.menu-item, .menu-link').forEach(item => {
        item.style.position = 'relative';
        item.style.zIndex = '20';
        item.style.pointerEvents = 'auto';
      });
      
      // Set layout page for mobile
      layoutPage.style.marginLeft = '0';
      layoutPage.style.width = '100%';
      
      // Handle overlay
      let overlay = document.querySelector('.layout-overlay');
      if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'layout-overlay';
        document.body.appendChild(overlay);
      }
      
      overlay.style.position = 'fixed';
      overlay.style.top = '0';
      overlay.style.left = '0';
      overlay.style.right = '0';
      overlay.style.bottom = '0';
      overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
      overlay.style.backdropFilter = 'blur(2px)';
      overlay.style.zIndex = '1990';
      overlay.style.display = document.body.classList.contains('layout-menu-expanded') ? 'block' : 'none';
      overlay.style.opacity = document.body.classList.contains('layout-menu-expanded') ? '1' : '0';
      overlay.style.visibility = document.body.classList.contains('layout-menu-expanded') ? 'visible' : 'hidden';
      
      // Add click handler to close sidebar when overlay is clicked
      overlay.onclick = function() {
        document.body.classList.remove('layout-menu-expanded');
        sidebar.style.transform = 'translateX(-100%)';
        overlay.style.opacity = '0';
        overlay.style.visibility = 'hidden';
        setTimeout(() => { overlay.style.display = 'none'; }, 300);
      };
    } else {
      // Desktop-specific fixes - minimal changes to preserve original layout
      sidebar.style.position = '';
      sidebar.style.top = '';
      sidebar.style.left = '';
      sidebar.style.transform = '';
      sidebar.style.zIndex = '';
      
      // Reset layout page for desktop - based on original layout
      if (document.body.classList.contains('layout-menu-collapsed')) {
        layoutPage.style.marginLeft = '78px';
      } else {
        layoutPage.style.marginLeft = '260px';
      }
      
      // Hide overlay on desktop
      const overlay = document.querySelector('.layout-overlay');
      if (overlay) {
        overlay.style.display = 'none';
        overlay.style.opacity = '0';
        overlay.style.visibility = 'hidden';
      }
    }
  }
  
  // Fix stats cards
  function fixStatsCards() {
    // Find all stats cards
    const statCards = document.querySelectorAll('.stat-card, .dashboard-stats .card');
    statCards.forEach(card => {
      // Apply theme
      const theme = document.documentElement.getAttribute('data-theme') || 
                    (document.documentElement.classList.contains('theme-dark') ? 'dark' : 'light');
      
      if (theme === 'dark') {
        card.style.backgroundColor = 'var(--card-background, #2b2c40)';
        card.style.color = 'var(--body-color, #a3a4cc)';
      } else {
        card.style.backgroundColor = 'var(--card-background, #fff)';
        card.style.color = 'var(--body-color, #697a8d)';
      }
    });
  }
  
  // Apply fixes
  fixMobileSidebar();
  fixStatsCards();
  
  // Listen for window resize
  window.addEventListener('resize', fixMobileSidebar);
  
  // Listen for theme changes
  window.addEventListener('themeChanged', fixStatsCards);
  
  // Apply fixes when body class changes
  const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
      if (mutation.attributeName === 'class') {
        fixMobileSidebar();
      }
    });
  });
  
  observer.observe(document.body, { attributes: true });
  
  // Listen for DOM changes that might affect the sidebar
  const contentObserver = new MutationObserver(function() {
    fixMobileSidebar();
  });
  
  contentObserver.observe(document.querySelector('.layout-page') || document.body, { 
    childList: true, 
    subtree: true 
  });
  
  // Apply fixes on page load and after a delay to catch any late DOM changes
  window.addEventListener('load', function() {
    fixMobileSidebar();
    fixStatsCards();
    
    setTimeout(function() {
      fixMobileSidebar();
      fixStatsCards();
    }, 500);
  });
});

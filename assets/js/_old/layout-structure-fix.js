/**
 * Water Academy Layout Structure Fix
 * Reverts to original layout structure while preserving mobile improvements
 */

document.addEventListener('DOMContentLoaded', function() {
  console.log('Simple layout structure fix initializing');
  
  // Function to fix the layout structure
  function fixLayoutStructure() {
    // Get the layout elements
    const layoutWrapper = document.querySelector('.layout-wrapper');
    const sidebar = document.querySelector('.menu.menu-vertical');
    const layoutPage = document.querySelector('.layout-page');
    
    if (!layoutWrapper || !sidebar || !layoutPage) {
      console.warn('Layout elements not found, retrying in 50ms');
      setTimeout(fixLayoutStructure, 50);
      return;
    }
    
    // Reset any styles that might be causing problems
    layoutWrapper.removeAttribute('style');
    sidebar.removeAttribute('style');
    layoutPage.removeAttribute('style');
    
    // Get the viewport width
    const isMobile = window.innerWidth < 1200;
    
    if (isMobile) {
      // Mobile-specific styles only
      sidebar.style.position = 'fixed';
      sidebar.style.top = '0';
      sidebar.style.left = '0';
      sidebar.style.height = '100vh';
      sidebar.style.width = '260px';
      sidebar.style.zIndex = '2000';
      sidebar.style.transform = 'translateX(-100%)';
      sidebar.style.transition = 'transform 0.3s ease';
      
      // Show sidebar if expanded
      if (document.body.classList.contains('layout-menu-expanded')) {
        sidebar.style.transform = 'translateX(0)';
      }
      
      // Ensure layout page takes full width on mobile
      layoutPage.style.marginLeft = '0';
      layoutPage.style.width = '100%';
    } else {
      // Desktop-specific styles - minimal changes to preserve original layout
      sidebar.style.height = '100vh';
      sidebar.style.overflowY = 'auto';
      
      // Reset transform that might be set by mobile view
      sidebar.style.transform = '';
      
      // Set layout page based on sidebar state
      if (document.body.classList.contains('layout-menu-collapsed')) {
        layoutPage.style.marginLeft = '78px';
      } else {
        layoutPage.style.marginLeft = '260px';
      }
    }
  }
  
  // Apply fixes immediately
  fixLayoutStructure();
  
  // Also apply on window resize
  window.addEventListener('resize', fixLayoutStructure);
  
  // Apply when body class changes (for sidebar toggle)
  const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
      if (mutation.attributeName === 'class') {
        fixLayoutStructure();
      }
    });
  });
  
  observer.observe(document.body, { attributes: true });
  
  // Apply fixes after a short delay to catch any delayed DOM changes
  setTimeout(fixLayoutStructure, 500);
});

/**
 * Water Academy Layout Module
 * Handles layout-related JavaScript, primarily for responsive adjustments.
 * Adopted and refactored from assets/js/old/layout-fixes.js.
 * Focuses on toggling CSS classes instead of inline styles.
 */

window.Layout_Module = (function() { // Expose to window
  /**
   * Initializes the layout module.
   */
  function init() {
    console.log('Layout_Module: Initializing module');
    
    // Apply fixes on initial load
    applyLayoutFixes();
    
    // Listen for window resize to re-apply mobile/desktop layout
    window.addEventListener('resize', applyLayoutFixes);
    
    // Listen for theme changes to update theme-dependent styles
    window.addEventListener('themeChanged', applyLayoutFixes); // Assuming 'themeChanged' custom event is dispatched
    
    // Removed aggressive MutationObserver for performance.
    // Layout changes should primarily be handled by CSS media queries and class toggling.
  }

  /**
   * Applies layout fixes based on screen size and theme.
   */
  function applyLayoutFixes() {
    fixMobileSidebar();
    fixStatsCardsTheme();
  }

  /**
   * Fixes mobile sidebar issues by toggling CSS classes.
   * Corresponding styles should be in assets/css/layout.css.
   */
  function fixMobileSidebar() {
    const sidebar = document.querySelector('.menu.menu-vertical');
    const layoutPage = document.querySelector('.layout-page');
    const isMobile = window.innerWidth < 1200;
    
    if (!sidebar || !layoutPage) return;
    
    if (isMobile) {
      // Add mobile-specific classes
      document.body.classList.add('is-mobile-layout');
      sidebar.classList.add('is-mobile-sidebar');
      layoutPage.classList.add('is-mobile-page');

      // Handle overlay
      let overlay = document.querySelector('.layout-overlay');
      if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'layout-overlay';
        document.body.appendChild(overlay);
      }
      overlay.classList.add('is-mobile-overlay');
      
      // Add click handler to close sidebar when overlay is clicked
      overlay.onclick = function() {
        document.body.classList.remove('layout-menu-expanded');
        // The CSS transition will handle the transform and opacity
      };

    } else {
      // Remove mobile-specific classes for desktop
      document.body.classList.remove('is-mobile-layout');
      sidebar.classList.remove('is-mobile-sidebar');
      layoutPage.classList.remove('is-mobile-page');
      
      // Hide overlay on desktop
      const overlay = document.querySelector('.layout-overlay');
      if (overlay) {
        overlay.classList.remove('is-mobile-overlay');
        // The CSS will handle display: none or similar
      }
    }
  }
  
  /**
   * Ensures stats cards apply theme correctly via CSS variables/classes.
   * Removes direct style manipulation.
   */
  function fixStatsCardsTheme() {
    // This function is now largely redundant if CSS variables are correctly applied
    // and theme classes are toggled on the <html> or <body> element.
    // The CSS should handle the theme application.
    console.log('Layout_Module: Stats card theme handled by CSS.');
  }
  
  // Public API
  return {
    init: init
  };
})();

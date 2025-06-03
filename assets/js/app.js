/**
 * Water Academy Main Application JavaScript (app.js)
 * New entry point for custom JavaScript modules.
 * Initializes core UI components and modules after dependencies are loaded.
 */

// Ensure jQuery is available globally (if not already)
if (typeof $ === 'undefined' && typeof jQuery !== 'undefined') {
  $ = jQuery;
}

document.addEventListener('DOMContentLoaded', function() {
  console.log('app.js: Initializing Water Academy application.');

  // Add a small delay to ensure all preceding scripts have fully executed and exposed their globals.
  // This is a pragmatic workaround for potential race conditions without a full bundler.
  setTimeout(function() {
    console.log('app.js: Checking module availability...');
    console.log('app.js: typeof UI_Components_Module:', typeof UI_Components_Module); // Updated to UI_Components_Module
    console.log('app.js: typeof Layout_Module:', typeof Layout_Module);
    console.log('app.js: typeof WA_Table:', typeof WA_Table);
    console.log('app.js: typeof initThemeSwitcher:', typeof initThemeSwitcher);
    console.log('app.js: typeof initSidebarToggle:', typeof initSidebarToggle);

    // Initialize UI Components module (only if present)
    if (typeof UI_Components_Module !== 'undefined' && typeof UI_Components_Module.init === 'function') { // Updated to UI_Components_Module
      UI_Components_Module.init(); // Updated to UI_Components_Module
    } else if (document.body.classList.contains('login-page')) {
      // Suppress error for login page where UI_Components is not expected
    } else {
      console.error('app.js: UI_Components module not found or not initialized.');
    }

    // Initialize Layout module (only if present)
    if (typeof Layout_Module !== 'undefined' && typeof Layout_Module.init === 'function') {
      Layout_Module.init();
    } else if (document.body.classList.contains('login-page')) {
      // Suppress error for login page where Layout_Module is not expected
    } else {
      console.error('app.js: Layout_Module not found or not initialized.');
    }

    // Initialize WA_Table module (only if present)
    if (typeof WA_Table !== 'undefined' && typeof WA_Table.init === 'function') {
      WA_Table.init();
    } else if (document.body.classList.contains('login-page')) {
      // Suppress error for login page where WA_Table is not expected
    } else {
      console.error('app.js: WA_Table module not found or not initialized.');
    }

    // Initialize Theme Switcher (only if present)
    if (typeof initThemeSwitcher === 'function') {
      initThemeSwitcher();
    } else if (document.body.classList.contains('login-page')) {
      // Suppress warning for login page where Theme Switcher is not expected
    } else {
      console.warn('app.js: initThemeSwitcher function not found. Theme switching may not work.');
    }

    // Initialize Sidebar Toggle (only if present)
    if (typeof initSidebarToggle === 'function') {
      initSidebarToggle();
    } else if (document.body.classList.contains('login-page')) {
      // Suppress warning for login page where Sidebar Toggle is not expected
    } else {
      console.warn('app.js: initSidebarToggle function not found. Sidebar toggle may not work.');
    }

    // Initialize Bootstrap components using jQuery if available, or suppress if not needed
    if (typeof $ !== 'undefined' && typeof $.fn.tooltip !== 'undefined') {
      // Initialize Tooltips
      $('[data-bs-toggle="tooltip"]').tooltip();
      console.log('app.js: Bootstrap Tooltips initialized using jQuery.');
    } else {
      console.warn('app.js: jQuery Tooltip not available. Tooltips may not function.');
    }

    if (typeof $ !== 'undefined' && typeof $.fn.popover !== 'undefined') {
      // Initialize Popovers
      $('[data-bs-toggle="popover"]').popover();
      console.log('app.js: Bootstrap Popovers initialized using jQuery.');
    } else {
      console.warn('app.js: jQuery Popover not available. Popovers may not function.');
    }

    // Toasts are generally not initialized this way, they are triggered programmatically.
    // Removing direct initialization to avoid potential conflicts.
    // console.log('app.js: Bootstrap Toasts are expected to be triggered programmatically.');

    console.log('app.js: Water Academy application initialization complete.');
  }, 100); // Small delay to ensure globals are defined
});

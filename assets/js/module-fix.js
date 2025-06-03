(function() {
  WA_Logger.info('ModuleFix', 'Initializing module fixes');
  
  // Create window.WA_Helpers if it doesn't exist
  if (typeof window.WA_Helpers === 'undefined') {
    window.WA_Helpers = {
      // Helper function to get CSS variables
      getCssVar: function(variableName, defaultValue) {
        try {
          // Ensure the variable name starts with '--'
          const cssVarName = variableName.startsWith('--') ? variableName : `--${variableName}`;
          const value = getComputedStyle(document.documentElement)
            .getPropertyValue(cssVarName)
            .trim();
          return value || defaultValue;
        } catch (e) {
          WA_Logger.error('WA_Helpers', `Error getting CSS variable ${variableName}`, e);
          return defaultValue;
        }
      },
      
      // More helper functions as needed
      // ...
    };
    
    WA_Logger.info('ModuleFix', 'Created window.WA_Helpers object');
  }
  
  // Fix jQuery undefined issues
  if (typeof jQuery === 'undefined' && typeof $ !== 'undefined') {
    window.jQuery = $;
    WA_Logger.info('ModuleFix', 'Assigned $ to jQuery');
  }
  
  // Make sure bootstrap is defined
  if (typeof bootstrap === 'undefined') {
    window.bootstrap = window.bootstrap || {};
    WA_Logger.warn('ModuleFix', 'Created empty bootstrap object as fallback');
  }
  
  // Load core dependencies first
  Promise.all([
    WA_DependencyManager.loadLibrary('jquery'),
    WA_DependencyManager.loadLibrary('popper'),
    WA_DependencyManager.loadLibrary('bootstrap')
  ]).then(() => {
    WA_Logger.info('ModuleFix', 'All core dependencies (jQuery, Popper, Bootstrap) are loaded');
    
    // Now load other libraries that depend on core ones or are generally needed
    return Promise.all([
      WA_DependencyManager.loadLibrary('chartjs'),
      WA_DependencyManager.loadLibrary('apexcharts'),
      WA_DependencyManager.loadLibrary('bsstepper')
    ]);
  }).then(() => {
    WA_Logger.info('ModuleFix', 'All additional dependencies are loaded');
    // Dispatch an event when all core and additional libraries are loaded
    document.dispatchEvent(new CustomEvent('wa.core.loaded'));
  }).catch(error => {
    WA_Logger.error('ModuleFix', 'Failed to load one or more dependencies:', error);
  });
})();

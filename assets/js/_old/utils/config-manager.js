const WA_Config = (function() {
  // Default configuration
  const DEFAULT_CONFIG = {
    // Feature flags
    features: {
      useJQueryForModals: true,
      mobileOptimizations: true,
      useStrictThemeMode: true
    },
    
    // Library versions and paths
    libraries: {
      jquery: {
        version: '3.6.0',
        path: 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js'
      },
      bootstrap: {
        version: '5.2.3',
        path: 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js'
      },
      popper: {
        version: '2.11.6',
        path: 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.8/umd/popper.min.js'
      },
      chartjs: {
        version: '3.9.1',
        path: 'https://cdn.jsdelivr.net/npm/chart.js'
      },
      apexcharts: {
        version: '3.45.1',
        path: 'https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js'
      },
      bsstepper: { // Corrected from bs-stepper to bsstepper for consistency
        version: '1.7.0',
        path: 'https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js'
      }
    },
    
    // Theme configuration
    theme: {
      defaultTheme: 'dark',
      persistTheme: true,
      themeStorageKey: 'wa_theme'
    },
    
    // Debug configuration
    debug: {
      logModuleLoading: true,
      logModalInteractions: true,
      logThemeSwitching: true
    }
  };
  
  // Current configuration (merges defaults with any overrides)
  let currentConfig = { ...DEFAULT_CONFIG };
  
  function get(path, defaultValue) {
    const parts = path.split('.');
    let current = currentConfig;
    
    for (const part of parts) {
      if (current[part] === undefined) {
        return defaultValue;
      }
      current = current[part];
    }
    
    return current;
  }
  
  function set(path, value) {
    const parts = path.split('.');
    let current = currentConfig;
    
    for (let i = 0; i < parts.length - 1; i++) {
      const part = parts[i];
      if (current[part] === undefined) {
        current[part] = {};
      }
      current = current[part];
    }
    
    current[parts[parts.length - 1]] = value;
    WA_Logger.debug('Config', `Set ${path} to ${JSON.stringify(value)}`);
  }
  
  function getAll() {
    return { ...currentConfig };
  }
  
  function init(overrides) {
    currentConfig = { ...DEFAULT_CONFIG, ...overrides };
    WA_Logger.info('Config', 'Configuration initialized', currentConfig);
  }
  
  return {
    get,
    set,
    getAll,
    init
  };
})();

// Make it global
window.WA_Config = WA_Config;

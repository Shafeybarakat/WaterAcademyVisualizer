const WA_DependencyManager = (function() {
  const loadedLibraries = {};
  const pendingPromises = {};
  
  // Define library dependencies
  const DEPENDENCY_MAP = {
    'jquery': [],
    'popper': [],
    'bootstrap': ['jquery', 'popper'],
    'chartjs': [], // Add Chart.js
    'apexcharts': [],
    'bs-stepper': ['jquery', 'bootstrap']
  };
  
  function isLoaded(libraryName) {
    return !!loadedLibraries[libraryName];
  }
  
  function markAsLoaded(libraryName) {
    loadedLibraries[libraryName] = true;
    WA_Logger.info('DependencyManager', `Library ${libraryName} marked as loaded`);
    
    // Resolve any pending promises
    if (pendingPromises[libraryName]) {
      pendingPromises[libraryName].forEach(resolve => resolve());
      delete pendingPromises[libraryName];
    }
  }
  
  function waitFor(libraryName) {
    if (isLoaded(libraryName)) {
      return Promise.resolve();
    }
    
    // Create a promise that will be resolved when the library is loaded
    return new Promise(resolve => {
      if (!pendingPromises[libraryName]) {
        pendingPromises[libraryName] = [];
      }
      pendingPromises[libraryName].push(resolve);
    });
  }
  
  function loadLibrary(libraryName) {
    if (isLoaded(libraryName)) {
      return Promise.resolve();
    }
    
    // Check if we already have a pending promise for this library
    if (pendingPromises[libraryName] && pendingPromises[libraryName].length > 0) {
      return waitFor(libraryName);
    }
    
    // Load dependencies first
    const dependencies = DEPENDENCY_MAP[libraryName] || [];
    
    return Promise.all(dependencies.map(dep => loadLibrary(dep)))
      .then(() => {
        const config = WA_Config.get('libraries.' + libraryName);
        
        if (!config || !config.path) {
          WA_Logger.error('DependencyManager', `No configuration found for library ${libraryName}`);
          return Promise.reject(`No configuration found for library ${libraryName}`);
        }
        
        WA_Logger.info('DependencyManager', `Loading library ${libraryName} from ${config.path}`);
        
        return new Promise((resolve, reject) => {
          const script = document.createElement('script');
          script.src = config.path;
          script.onload = () => {
            markAsLoaded(libraryName);
            resolve();
          };
          script.onerror = () => {
            WA_Logger.error('DependencyManager', `Failed to load library ${libraryName}`);
            reject(`Failed to load library ${libraryName}`);
          };
          document.head.appendChild(script);
        });
      });
  }
  
  function initWithExistingLibraries() {
    // Check for jQuery
    if (typeof window.$ !== 'undefined' && typeof window.$.fn !== 'undefined') {
      markAsLoaded('jquery');
    }
    
    // Check for Popper
    if (typeof Popper !== 'undefined') {
      markAsLoaded('popper');
    }
    
    // Check for Bootstrap
    if (typeof bootstrap !== 'undefined') {
      markAsLoaded('bootstrap');
    }
    
    // Check for Chart.js
    if (typeof Chart !== 'undefined') {
      markAsLoaded('chartjs');
    }

    // Check for ApexCharts
    if (typeof ApexCharts !== 'undefined') {
      markAsLoaded('apexcharts');
    }
    
    // Check for BS Stepper
    if (typeof window.$ !== 'undefined' && typeof window.$().stepper === 'function') {
      markAsLoaded('bs-stepper');
    }
    
    WA_Logger.info('DependencyManager', 'Initialized with existing libraries', loadedLibraries);
  }
  
  // Define require shim for compatibility
  function defineRequireShim() {
    if (typeof window.require === 'undefined') {
      window.require = function(moduleName) {
        WA_Logger.debug('DependencyManager', `require() called for ${moduleName}`);
        
        // Map CommonJS module names to global objects
        if (moduleName === 'jquery') {
          return window.jQuery || window.$;
        }
        if (moduleName === 'popper.js') {
          return window.Popper;
        }
        if (moduleName === 'bootstrap') {
          return window.bootstrap;
        }
        if (moduleName === 'chart.js') {
          return window.Chart;
        }
        
        WA_Logger.warn('DependencyManager', `Unknown module requested: ${moduleName}`);
        return null;
      };
      
      WA_Logger.info('DependencyManager', 'require() shim installed');
    }
  }
  
  function init() {
    defineRequireShim();
    initWithExistingLibraries();
  }
  
  return {
    isLoaded,
    waitFor,
    loadLibrary,
    init
  };
})();

// Initialize immediately
WA_DependencyManager.init();

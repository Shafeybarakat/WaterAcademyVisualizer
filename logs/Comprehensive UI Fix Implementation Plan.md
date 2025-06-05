# Comprehensive UI Fix Implementation Plan

## Phase 1: Setup and Preparation (1-2 days)

### Step 1: Create a Development Branch and Environment
```bash
git checkout -b ui-fixes-june-2025
```

### Step 2: Set Up Structured Logging for Debugging
1. Create a new utility file:
```javascript
// assets/js/utils/logger.js
const WA_Logger = (function() {
  const LOG_LEVELS = {
    ERROR: 0,
    WARN: 1,
    INFO: 2,
    DEBUG: 3
  };
  
  // Can be changed via localStorage for debugging
  let currentLevel = LOG_LEVELS.INFO;
  
  // Try to get level from localStorage
  try {
    const storedLevel = localStorage.getItem('wa_log_level');
    if (storedLevel && LOG_LEVELS[storedLevel] !== undefined) {
      currentLevel = LOG_LEVELS[storedLevel];
    }
  } catch (e) {
    // Ignore localStorage errors
  }
  
  function formatMessage(component, message) {
    return `[${component}] ${message}`;
  }
  
  function error(component, message, ...args) {
    if (currentLevel >= LOG_LEVELS.ERROR) {
      console.error(formatMessage(component, message), ...args);
    }
  }
  
  function warn(component, message, ...args) {
    if (currentLevel >= LOG_LEVELS.WARN) {
      console.warn(formatMessage(component, message), ...args);
    }
  }
  
  function info(component, message, ...args) {
    if (currentLevel >= LOG_LEVELS.INFO) {
      console.info(formatMessage(component, message), ...args);
    }
  }
  
  function debug(component, message, ...args) {
    if (currentLevel >= LOG_LEVELS.DEBUG) {
      console.debug(formatMessage(component, message), ...args);
    }
  }
  
  function setLevel(level) {
    if (LOG_LEVELS[level] !== undefined) {
      currentLevel = LOG_LEVELS[level];
      try {
        localStorage.setItem('wa_log_level', level);
      } catch (e) {
        // Ignore localStorage errors
      }
    }
  }
  
  return {
    error,
    warn,
    info,
    debug,
    setLevel,
    LEVELS: LOG_LEVELS
  };
})();

// Make it global
window.WA_Logger = WA_Logger;
```

2. Add to header.php before other scripts:
```php
<script src="<?php echo htmlspecialchars($baseAssetPath); ?>js/utils/logger.js?v=<?php echo time(); ?>"></script>
```

### Step 3: Create a Configuration Management System
1. Create a new utility file:
```javascript
// assets/js/utils/config-manager.js
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
        path: 'vendor/libs/jquery/jquery.js'
      },
      bootstrap: {
        version: '5.2.3',
        path: 'vendor/libs/bootstrap/bootstrap.bundle.min.js'
      },
      popper: {
        version: '2.11.6',
        path: 'vendor/libs/popper/popper.min.js'
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
```

2. Add to header.php after logger.js:
```php
<script src="<?php echo htmlspecialchars($baseAssetPath); ?>js/utils/config-manager.js?v=<?php echo time(); ?>"></script>
```

## Phase 2: Fix JavaScript Module Loading (2-3 days)

### Step 1: Create a Dependency Manager
1. Create a new utility file:
```javascript
// assets/js/utils/dependency-manager.js
const WA_DependencyManager = (function() {
  const loadedLibraries = {};
  const pendingPromises = {};
  
  // Define library dependencies
  const DEPENDENCY_MAP = {
    'bootstrap': ['jquery', 'popper'],
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
    if (typeof $ !== 'undefined' && typeof $.fn !== 'undefined') {
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
    
    // Check for ApexCharts
    if (typeof ApexCharts !== 'undefined') {
      markAsLoaded('apexcharts');
    }
    
    // Check for BS Stepper
    if (typeof $().stepper === 'function') {
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
```

2. Add to header.php after config-manager.js:
```php
<script src="<?php echo htmlspecialchars($baseAssetPath); ?>js/utils/dependency-manager.js?v=<?php echo time(); ?>"></script>
```

### Step 2: Update module-fix.js to Use the New System
1. Replace current module-fix.js with:
```javascript
// assets/js/module-fix.js
(function() {
  WA_Logger.info('ModuleFix', 'Initializing module fixes');
  
  // Create window.Helpers if it doesn't exist
  if (typeof window.Helpers === 'undefined') {
    window.Helpers = {
      // Helper function to get CSS variables
      getCssVar: function(variableName, defaultValue) {
        try {
          const value = getComputedStyle(document.documentElement)
            .getPropertyValue(variableName)
            .trim();
          return value || defaultValue;
        } catch (e) {
          WA_Logger.error('Helpers', `Error getting CSS variable ${variableName}`, e);
          return defaultValue;
        }
      },
      
      // More helper functions as needed
      // ...
    };
    
    WA_Logger.info('ModuleFix', 'Created window.Helpers object');
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
  
  // Load required libraries
  Promise.all([
    WA_DependencyManager.waitFor('jquery'),
    WA_DependencyManager.waitFor('popper'),
    WA_DependencyManager.waitFor('bootstrap')
  ]).then(() => {
    WA_Logger.info('ModuleFix', 'All core dependencies are loaded');
    
    // Dispatch an event when all core libraries are loaded
    document.dispatchEvent(new CustomEvent('wa.core.loaded'));
  }).catch(error => {
    WA_Logger.error('ModuleFix', 'Failed to load core dependencies', error);
  });
})();
```

### Step 3: Create Script Loading Sequence in header.php
Update the script loading sequence in header.php:

```php
<!-- Base dependencies -->
<script src="<?php echo htmlspecialchars($baseAssetPath); ?>js/utils/logger.js?v=<?php echo time(); ?>"></script>
<script src="<?php echo htmlspecialchars($baseAssetPath); ?>js/utils/config-manager.js?v=<?php echo time(); ?>"></script>
<script src="<?php echo htmlspecialchars($baseAssetPath); ?>js/utils/dependency-manager.js?v=<?php echo time(); ?>"></script>
<script src="<?php echo htmlspecialchars($baseAssetPath); ?>js/module-fix.js?v=<?php echo time(); ?>"></script>

<!-- jQuery (load before Bootstrap) -->
<script src="<?php echo htmlspecialchars($baseAssetPath); ?>vendor/libs/jquery/jquery.js?v=<?php echo time(); ?>"></script>

<!-- Popper.js (required by Bootstrap) -->
<script src="<?php echo htmlspecialchars($baseAssetPath); ?>vendor/libs/popper/popper.min.js?v=<?php echo time(); ?>"></script>

<!-- Bootstrap -->
<script src="<?php echo htmlspecialchars($baseAssetPath); ?>vendor/libs/bootstrap/bootstrap.bundle.min.js?v=<?php echo time(); ?>"></script>

<!-- Theme and UI components (after core libraries) -->
<script src="<?php echo htmlspecialchars($baseAssetPath); ?>js/theme-switcher.js?v=<?php echo time(); ?>"></script>
<script src="<?php echo htmlspecialchars($baseAssetPath); ?>js/sidebar-toggle.js?v=<?php echo time(); ?>"></script>

<!-- Application core JS -->
<script src="<?php echo htmlspecialchars($baseAssetPath); ?>js/app.js?v=<?php echo time(); ?>"></script>

<!-- Other libraries and components loaded as needed -->
```

## Phase 3: Standardize Modal Interactions (2 days)

### Step 1: Update wa-modal.js to Use the New System
1. Update wa-modal.js:
```javascript
// assets/js/wa-modal.js
const WA_Modal = (function() {
  // Store modal instances for reuse
  const modalInstances = {};
  
  /**
   * Initialize modal functionality
   */
  function init() {
    WA_Logger.info('Modal', 'Initializing modal handler');
    
    // Set up event listeners for WA custom declarative usage
    document.addEventListener('click', function(event) {
      WA_Logger.debug('Modal', 'Click event detected', event.target);
      
      // Check if the clicked element or its parent has data-modal-target attribute
      let target = event.target;
      let maxDepth = 3; // Check up to 3 levels up the DOM tree
      
      while (target && maxDepth > 0) {
        const targetId = target.getAttribute('data-modal-target');
        const action = target.getAttribute('data-modal-action') || 'show';
        
        if (targetId) {
          event.preventDefault();
          WA_Logger.info('Modal', `Handling click with data-modal-target="${targetId}"`);
          
          if (action === 'show') {
            show(targetId, {}, target);
          } else if (action === 'hide') {
            hide(targetId);
          }
          return; // Exit after handling
        }
        
        // Also support Bootstrap's data attributes for backward compatibility
        const bsTarget = target.getAttribute('data-bs-target');
        const bsToggle = target.getAttribute('data-bs-toggle');
        
        if (bsTarget && bsToggle === 'modal') {
          event.preventDefault();
          WA_Logger.info('Modal', `Handling click with data-bs-target="${bsTarget}"`);
          
          // Remove the # if present in the ID
          const modalId = bsTarget.startsWith('#') ? bsTarget.substring(1) : bsTarget;
          show(modalId, {}, target);
          return; // Exit after handling
        }
        
        // Move up the DOM tree
        target = target.parentElement;
        maxDepth--;
      }
    }, true); // Use capture phase to handle before other handlers
    
    // Handle ESC key to close modals
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        const openModals = document.querySelectorAll('.modal.show');
        if (openModals.length > 0) {
          const modalId = openModals[openModals.length - 1].id;
          hide(modalId);
        }
      }
    });
    
    // Handle backdrop clicks
    document.addEventListener('click', function(event) {
      if (event.target.classList.contains('modal') && event.target.getAttribute('data-backdrop') !== 'static') {
        hide(event.target.id);
      }
    });
  }
  
  /**
   * Show a modal by ID using the appropriate method based on configuration
   */
  function show(modalId, options = {}, relatedTarget = null) {
    WA_Logger.info('Modal', `Showing modal "${modalId}"`);
    
    // Get the modal element
    const modalElement = document.getElementById(modalId);
    if (!modalElement) {
      WA_Logger.error('Modal', `Modal with ID '${modalId}' not found`);
      return null;
    }
    
    // Prepare custom event before showing
    const beforeShowEvent = new CustomEvent('wa.modal.beforeShow', {
      bubbles: true,
      cancelable: true,
      detail: { 
        modalId, 
        relatedTarget: relatedTarget,
        userData: extractUserData(relatedTarget)
      }
    });
    
    // Dispatch the event and check if it was cancelled
    const eventResult = modalElement.dispatchEvent(beforeShowEvent);
    if (!eventResult) {
      WA_Logger.info('Modal', `Showing modal "${modalId}" was cancelled by event handler`);
      return null;
    }
    
    // Use jQuery if available and configured to use it
    if (WA_Config.get('features.useJQueryForModals', true) && 
        typeof $ !== 'undefined' && 
        typeof $.fn.modal !== 'undefined') {
      try {
        const jqModalElement = $(modalElement);
        jqModalElement.modal(options); // Show the modal using jQuery
        WA_Logger.info('Modal', `Showed modal ${modalId} using jQuery`);
        
        // Store the jQuery instance
        modalInstances[modalId] = jqModalElement;
        
        // Trigger custom event for extensibility after showing
        modalElement.dispatchEvent(new CustomEvent('wa.modal.shown', {
          bubbles: true,
          detail: { 
            modalId, 
            instance: jqModalElement,
            relatedTarget: relatedTarget,
            userData: extractUserData(relatedTarget)
          }
        }));
        
        return jqModalElement;
      } catch (error) {
        WA_Logger.error('Modal', `Error showing modal "${modalId}" with jQuery:`, error);
        // Fall back to native Bootstrap if jQuery fails
      }
    }
    
    // Fallback to native Bootstrap if jQuery is not available or configured
    try {
      WA_Logger.info('Modal', `Showing modal ${modalId} using native Bootstrap`);
      
      // Wait for Bootstrap to be loaded
      WA_DependencyManager.waitFor('bootstrap')
        .then(() => {
          // Create Bootstrap modal instance
          const bsModal = new bootstrap.Modal(modalElement, options);
          modalInstances[modalId] = bsModal;
          
          // Show the modal
          bsModal.show();
          
          // Trigger custom event for extensibility after showing
          modalElement.dispatchEvent(new CustomEvent('wa.modal.shown', {
            bubbles: true,
            detail: { 
              modalId, 
              instance: bsModal,
              relatedTarget: relatedTarget,
              userData: extractUserData(relatedTarget)
            }
          }));
        })
        .catch(error => {
          WA_Logger.error('Modal', `Error showing modal "${modalId}" with native Bootstrap:`, error);
          
          // Ultimate fallback - just show the modal with minimal functionality
          modalElement.classList.add('show');
          modalElement.style.display = 'block';
          document.body.classList.add('modal-open');
          
          // Create backdrop manually
          const backdrop = document.createElement('div');
          backdrop.className = 'modal-backdrop fade show';
          document.body.appendChild(backdrop);
          
          // Trigger custom event
          modalElement.dispatchEvent(new CustomEvent('wa.modal.shown', {
            bubbles: true,
            detail: { 
              modalId, 
              instance: null,
              relatedTarget: relatedTarget,
              userData: extractUserData(relatedTarget)
            }
          }));
        });
      
      return modalElement;
    } catch (error) {
      WA_Logger.error('Modal', `Error showing modal "${modalId}" with native Bootstrap:`, error);
      return null;
    }
  }
  
  /**
   * Extract user data from data attributes
   */
  function extractUserData(element) {
    if (!element) return {};
    
    const userData = {};
    
    // Get all data attributes
    for (let attr of element.attributes) {
      if (attr.name.startsWith('data-')) {
        // Convert data-attribute-name to attributeName
        const key = attr.name.substring(5).replace(/-([a-z])/g, (g) => g[1].toUpperCase());
        userData[key] = attr.value;
      }
    }
    
    return userData;
  }
  
  /**
   * Hide a modal by ID using the appropriate method
   */
  function hide(modalId) {
    WA_Logger.info('Modal', `Hiding modal "${modalId}"`);
    
    // Get the modal element
    const modalElement = document.getElementById(modalId);
    if (!modalElement) {
      WA_Logger.error('Modal', `Modal with ID '${modalId}' not found`);
      return false;
    }
    
    // Prepare custom event before hiding
    const beforeHideEvent = new CustomEvent('wa.modal.beforeHide', {
      bubbles: true,
      cancelable: true,
      detail: { modalId }
    });
    
    // Dispatch the event and check if it was cancelled
    const eventResult = modalElement.dispatchEvent(beforeHideEvent);
    if (!eventResult) {
      WA_Logger.info('Modal', `Hiding modal "${modalId}" was cancelled by event handler`);
      return false;
    }
    
    // Check if we have a stored instance
    const instance = modalInstances[modalId];
    
    // If we have a jQuery instance
    if (instance && typeof instance.modal === 'function') {
      try {
        instance.modal('hide');
        WA_Logger.info('Modal', `Hid modal "${modalId}" using jQuery`);
        
        // Trigger custom event for extensibility after hiding
        modalElement.dispatchEvent(new CustomEvent('wa.modal.hidden', {
          bubbles: true,
          detail: { modalId, instance }
        }));
        
        return true;
      } catch (error) {
        WA_Logger.error('Modal', `Error hiding modal "${modalId}" with jQuery:`, error);
      }
    }
    
    // If we have a Bootstrap instance
    if (instance && typeof instance.hide === 'function') {
      try {
        instance.hide();
        WA_Logger.info('Modal', `Hid modal "${modalId}" using native Bootstrap`);
        
        // Trigger custom event for extensibility after hiding
        modalElement.dispatchEvent(new CustomEvent('wa.modal.hidden', {
          bubbles: true,
          detail: { modalId, instance }
        }));
        
        return true;
      } catch (error) {
        WA_Logger.error('Modal', `Error hiding modal "${modalId}" with native Bootstrap:`, error);
      }
    }
    
    // Fallback to jQuery if available
    if (typeof $ !== 'undefined' && typeof $.fn.modal !== 'undefined') {
      try {
        $(modalElement).modal('hide');
        WA_Logger.info('Modal', `Hid modal "${modalId}" using jQuery (fallback)`);
        
        // Trigger custom event for extensibility after hiding
        modalElement.dispatchEvent(new CustomEvent('wa.modal.hidden', {
          bubbles: true,
          detail: { modalId, instance: null }
        }));
        
        return true;
      } catch (error) {
        WA_Logger.error('Modal', `Error hiding modal "${modalId}" with jQuery (fallback):`, error);
      }
    }
    
    // Ultimate fallback - just hide the modal manually
    try {
      modalElement.classList.remove('show');
      modalElement.style.display = 'none';
      document.body.classList.remove('modal-open');
      
      // Remove backdrop
      const backdrop = document.querySelector('.modal-backdrop');
      if (backdrop) {
        backdrop.parentNode.removeChild(backdrop);
      }
      
      WA_Logger.info('Modal', `Hid modal "${modalId}" manually (ultimate fallback)`);
      
      // Trigger custom event for extensibility after hiding
      modalElement.dispatchEvent(new CustomEvent('wa.modal.hidden', {
        bubbles: true,
        detail: { modalId, instance: null }
      }));
      
      return true;
    } catch (error) {
      WA_Logger.error('Modal', `Error hiding modal "${modalId}" manually:`, error);
      return false;
    }
  }
  
  /**
   * Update modal content
   * @param {string} modalId - The ID of the modal to update
   * @param {string} content - The HTML content to set
   * @param {string} target - The selector for the target element within the modal (optional)
   * @returns {boolean} Success status
   */
  function updateContent(modalId, content, target = '.modal-body') {
    WA_Logger.info('Modal', `Updating content for modal "${modalId}"`);
    
    const modalElement = document.getElementById(modalId);
    if (!modalElement) {
      WA_Logger.error('Modal', `Modal with ID '${modalId}' not found`);
      return false;
    }
    
    const targetElement = modalElement.querySelector(target);
    if (!targetElement) {
      WA_Logger.error('Modal', `Target element '${target}' not found in modal`);
      return false;
    }
    
    targetElement.innerHTML = content;
    return true;
  }
  
  /**
   * Set up a form inside a modal for AJAX submission
   * @param {string} modalId - The ID of the modal containing the form
   * @param {string} formSelector - The selector for the form element
   * @param {Function} onSubmit - Callback function when form is submitted
   * @param {Function} onSuccess - Callback function on successful submission
   * @param {Function} onError - Callback function on error
   */
  function setupFormSubmission(modalId, formSelector, onSubmit, onSuccess, onError) {
    WA_Logger.info('Modal', `Setting up form submission for modal "${modalId}"`);
    
    const modalElement = document.getElementById(modalId);
    if (!modalElement) {
      WA_Logger.error('Modal', `Modal with ID '${modalId}' not found`);
      return;
    }
    
    const form = modalElement.querySelector(formSelector);
    if (!form) {
      WA_Logger.error('Modal', `Form '${formSelector}' not found in modal`);
      return;
    }
    
    form.addEventListener('submit', function(event) {
      event.preventDefault();
      WA_Logger.info('Modal', `Form submitted in modal "${modalId}"`);
      
      // Call the onSubmit callback if provided
      if (typeof onSubmit === 'function') {
        const continueSubmission = onSubmit(form, event);
        if (continueSubmission === false) {
          return;
        }
      }
      
      // Get form data
      const formData = new FormData(form);
      
      // Send the AJAX request
      fetch(form.action || window.location.href, {
        method: form.method || 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
      })
      .then(data => {
        WA_Logger.info('Modal', `Form submission successful for modal "${modalId}"`);
        
        // Call the onSuccess callback if provided
        if (typeof onSuccess === 'function') {
          onSuccess(data, form);
        }
      })
      .catch(error => {
        WA_Logger.error('Modal', `Error submitting form for modal "${modalId}":`, error);
        
        // Call the onError callback if provided
        if (typeof onError === 'function') {
          onError(error, form);
        }
      });
    });
  }
  
  /**
   * Destroy a modal instance and clean up resources
   * @param {string} modalId - The ID of the modal to destroy
   * @returns {boolean} Success status
   */
  function destroy(modalId) {
    WA_Logger.info('Modal', `Destroying modal "${modalId}"`);
    
    // Get the modal element
    const modalElement = document.getElementById(modalId);
    if (!modalElement) {
      WA_Logger.error('Modal', `Modal with ID '${modalId}' not found`);
      return false;
    }
    
    try {
      // Try to get the Bootstrap modal instance
      let modalInstance;
      
      if (modalInstances[modalId]) {
        modalInstance = modalInstances[modalId];
        
        // Remove the instance from our cache
        delete modalInstances[modalId];
        
        // Dispose the Bootstrap modal instance
        if (modalInstance && typeof modalInstance.dispose === 'function') {
          modalInstance.dispose();
        }
        
        return true;
      }
      
      return false;
    } catch (error) {
      WA_Logger.error('Modal', 'Error destroying modal:', error);
      return false;
    }
  }
  
  // Initialize when the DOM is ready
  document.addEventListener('DOMContentLoaded', init);
  
  // Initialize when core libraries are loaded (backup in case DOMContentLoaded already fired)
  document.addEventListener('wa.core.loaded', init);
  
  // Public API
  return {
    init, // Expose init function
    show,
    hide,
    updateContent,
    setupFormSubmission,
    destroy
  };
})();
```

### Step 2: Update All Modal Triggers
Create a script to scan for and update modal triggers:

```javascript
// assets/js/utils/modal-upgrade.js
(function() {
  function upgradeModalTriggers() {
    WA_Logger.info('ModalUpgrade', 'Scanning for old-style modal triggers');
    
    // Find all buttons/links with onclick handlers containing modal show
    const oldTriggers = document.querySelectorAll('[onclick*="Modal"]');
    let upgradeCount = 0;
    
    oldTriggers.forEach(trigger => {
      const onclickValue = trigger.getAttribute('onclick');
      
      // Check if it's a modal trigger
      if (onclickValue.includes('.show(') || onclickValue.includes('.modal(')) {
        WA_Logger.debug('ModalUpgrade', `Found old-style trigger: ${onclickValue}`);
        
        // Extract modal ID from the onclick handler
        const modalIdMatch = onclickValue.match(/['"]([^'"]+)['"]\)/);
        if (modalIdMatch && modalIdMatch[1]) {
          const modalId = modalIdMatch[1];
          
          // Check if there are parameters being passed in the onclick
          const dataParams = {};
          const paramMatches = onclickValue.match(/\w+\(['"]([^'"]+)['"],\s*['"]([^'"]+)['"]\)/g);
          if (paramMatches) {
            paramMatches.forEach(match => {
              const parts = match.match(/(\w+)\(['"]([^'"]+)['"],\s*['"]([^'"]+)['"]\)/);
              if (parts && parts.length >= 4) {
                const funcName = parts[1];
                const paramName = parts[2];
                const paramValue = parts[3];
                
                // If this is a data-loading function, add the parameter as a data attribute
                if (funcName.includes('load') || funcName.includes('get')) {
                  dataParams[`data-${paramName.toLowerCase()}`] = paramValue;
                }
              }
            });
          }
          
          // Update the trigger
          trigger.removeAttribute('onclick');
          trigger.setAttribute('data-modal-target', modalId);
          trigger.setAttribute('data-modal-action', 'show');
          
          // Add any extracted data attributes
          for (const [key, value] of Object.entries(dataParams)) {
            trigger.setAttribute(key, value);
          }
          
          upgradeCount++;
          WA_Logger.info('ModalUpgrade', `Upgraded trigger for modal ${modalId}`);
        }
      }
    });
    
    WA_Logger.info('ModalUpgrade', `Upgraded ${upgradeCount} modal triggers`);
  }
  
  // Run upgrade when the DOM is ready
  document.addEventListener('DOMContentLoaded', upgradeModalTriggers);
})();
```

Add to header.php after wa-modal.js:
```php
<script src="<?php echo htmlspecialchars($baseAssetPath); ?>js/utils/modal-upgrade.js?v=<?php echo time(); ?>"></script>
```

## Phase 4: Fix Mobile Sidebar (1-2 days)

### Step 1: Create a Dedicated Mobile Sidebar CSS File
1. Create a new CSS file:
```css
/* assets/css/components/mobile-sidebar.css */

/* Mobile Sidebar Core Fixes */
@media (max-width: 1199.98px) {
  /* Fix sidebar positioning and visibility */
  .layout-menu-fixed .layout-menu,
  .layout-menu-fixed-offcanvas .layout-menu {
    position: fixed !important;
    top: 0 !important;
    height: 100% !important;
    left: 0 !important;
    z-index: 1050 !important; /* Higher than content but lower than modals */
    transform: translateX(-100%) !important;
    transition: transform 0.3s ease-in-out !important;
    pointer-events: none !important; /* Initially disable pointer events */
  }

  /* Show sidebar */
  .layout-menu-expanded .layout-menu {
    transform: translateX(0%) !important;
    pointer-events: auto !important; /* Enable pointer events when expanded */
  }

  /* Overlay for mobile sidebar */
  .layout-overlay {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    background-color: rgba(0, 0, 0, 0.5) !important;
    z-index: 1040 !important; /* Below sidebar, above content */
    opacity: 0 !important;
    visibility: hidden !important;
    transition: opacity 0.3s ease-in-out, visibility 0.3s ease-in-out !important;
  }

  /* Show overlay when menu is expanded */
  .layout-menu-expanded .layout-overlay {
    opacity: 1 !important;
    visibility: visible !important;
  }

  /* Ensure menu items are clickable */
  .menu-item {
    pointer-events: auto !important;
  }

  /* Adjust content wrapper when sidebar is expanded */
  .layout-page {
    transform: translateX(0) !important; /* No push effect, overlay handles it */
  }
}
```

2. Import this CSS file into `assets/css/main.css`. Add the following line at the end of `main.css`:
```css
/* Import mobile sidebar fixes */
@import 'components/mobile-sidebar.css';
```

### Step 2: Update sidebar-toggle.js
1. Update `assets/js/sidebar-toggle.js` to ensure it correctly toggles classes and handles the overlay:
```javascript
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
```

### Step 3: Update header.php to include mobile-sidebar.css
Add the new CSS import to `includes/header.php` (or ensure it's loaded via `main.css` as per previous step).

## Phase 5: Theme Switching and UI Component Consistency (2-3 days)

### Step 1: Review and Refine theme-switcher.js
1. Ensure `assets/js/theme-switcher.js` correctly uses `WA_Config` and applies themes consistently.
```javascript
// assets/js/theme-switcher.js
(function() {
  const themeToggle = document.getElementById('theme-toggle');
  const htmlElement = document.documentElement; // The <html> element
  const bodyElement = document.body; // The <body> element

  // Function to apply theme
  function applyTheme(theme) {
    WA_Logger.info('ThemeSwitcher', `Applying theme: ${theme}`);
    htmlElement.setAttribute('data-bs-theme', theme);
    bodyElement.setAttribute('data-bs-theme', theme); // Apply to body as well for consistency
    
    // Update specific classes if needed (e.g., for older components)
    if (theme === 'dark') {
      bodyElement.classList.add('theme-dark');
      bodyElement.classList.remove('theme-light');
    } else {
      bodyElement.classList.add('theme-light');
      bodyElement.classList.remove('theme-dark');
    }

    // Dispatch a custom event for other components to react to theme changes
    document.dispatchEvent(new CustomEvent('wa.theme.changed', { detail: { theme: theme } }));
  }

  // Function to get stored theme
  function getStoredTheme() {
    if (WA_Config.get('theme.persistTheme', true)) {
      return localStorage.getItem(WA_Config.get('theme.themeStorageKey', 'wa_theme'));
    }
    return null;
  }

  // Function to set stored theme
  function setStoredTheme(theme) {
    if (WA_Config.get('theme.persistTheme', true)) {
      localStorage.setItem(WA_Config.get('theme.themeStorageKey', 'wa_theme'), theme);
    }
  }

  // Initialize theme on load
  const initialTheme = getStoredTheme() || WA_Config.get('theme.defaultTheme', 'dark');
  applyTheme(initialTheme);

  // Set initial state of the toggle button
  if (themeToggle) {
    themeToggle.checked = (initialTheme === 'dark');
  }

  // Event listener for theme toggle
  if (themeToggle) {
    themeToggle.addEventListener('change', function() {
      const newTheme = this.checked ? 'dark' : 'light';
      applyTheme(newTheme);
      setStoredTheme(newTheme);
    });
  }

  WA_Logger.info('ThemeSwitcher', 'Theme switcher initialized');
})();
```

### Step 2: Review CSS for Theme Consistency
1. **`assets/css/base.css`**: Ensure it defines core variables like `--bs-body-bg`, `--bs-body-color`, `--bs-heading-color`, etc., and that these are used consistently.
2. **`assets/css/core.css`**: Check for any hardcoded colors that should be using CSS variables.
3. **`assets/css/components/*.css`**: Verify that all component-specific CSS files (e.g., `cards.css`, `dropdowns.css`, `modals.css`) use CSS variables for colors and backgrounds, and include specific adjustments for `.theme-dark` or `[data-bs-theme="dark"]` where necessary.

### Step 3: Implement Theme-Aware Component Initialization
For components that are dynamically loaded or initialized, ensure they react to `wa.theme.changed` event.

```javascript
// Example: In app.js or a new dashboard-init.js
document.addEventListener('wa.theme.changed', function(event) {
  WA_Logger.info('App', `Received theme change event: ${event.detail.theme}`);
  // Re-initialize or update components that need to react to theme changes
  // e.g., Chart.js graphs, dynamically loaded content
});
```

## Phase 6: Testing and Refinement (3-5 days)

### Step 1: Unit Testing (Manual/Automated)
- **Logger and Config Manager**: Verify logs appear correctly in console, and config values are retrieved/set.
- **Dependency Manager**: Test loading of jQuery, Popper, Bootstrap. Verify `require()` shim works.
- **Modal System**:
  - Test all modals (`switchRoleModal`, `groupWizardModal`, etc.).
  - Verify `data-modal-target` and `data-bs-target` work.
  - Test show/hide, content update, form submission.
  - Check for console errors related to modals.
- **Sidebar**:
  - Test on various mobile device emulators (Chrome DevTools).
  - Verify toggle, overlay, and menu item clickability.
  - Check behavior on resize.
- **Theme Switcher**:
  - Test light/dark mode toggle.
  - Verify all UI elements (cards, tables, forms, text) change theme correctly.
  - Check theme persistence across page loads.

### Step 2: Integration Testing
- Navigate through all major pages (`dashboards/index.php`, `dashboards/instructors.php`, `dashboards/attendance_grades.php`, etc.).
- Interact with all UI elements and forms.
- Monitor console for any errors or warnings.

### Step 3: Performance Monitoring
- Use browser developer tools to monitor page load times, rendering performance, and script execution.
- Identify any bottlenecks introduced by the changes.

### Step 4: Cross-Browser Compatibility Testing
- Test on Chrome, Firefox, Safari, Edge.

### Step 5: Documentation Update
- Update `logs/Projectinfo.md` with details of the implemented fixes and any new best practices.

## Plan Phases and Steps

### Phase 1: Setup and Preparation
- Step 1: Create a Development Branch and Environment
- Step 2: Set Up Structured Logging for Debugging
- Step 3: Create a Configuration Management System

### Phase 2: Fix JavaScript Module Loading
- Step 1: Create a Dependency Manager
- Step 2: Update module-fix.js to Use the New System
- Step 3: Create Script Loading Sequence in header.php

### Phase 3: Standardize Modal Interactions
- Step 1: Update wa-modal.js to Use the New System
- Step 2: Update All Modal Triggers

### Phase 4: Fix Mobile Sidebar
- Step 1: Create a Dedicated Mobile Sidebar CSS File
- Step 2: Update sidebar-toggle.js
- Step 3: Update header.php to include mobile-sidebar.css

### Phase 5: Theme Switching and UI Component Consistency
- Step 1: Review and Refine theme-switcher.js
- Step 2: Review CSS for Theme Consistency
- Step 3: Implement Theme-Aware Component Initialization

### Phase 6: Testing and Refinement
- Step 1: Unit Testing (Manual/Automated)
- Step 2: Integration Testing
- Step 3: Performance Monitoring
- Step 4: Cross-Browser Compatibility Testing
- Step 5: Documentation Update

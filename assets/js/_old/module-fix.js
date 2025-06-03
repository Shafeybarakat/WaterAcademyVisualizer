/**
 * Water Academy Module Fix
 * Addresses JavaScript module loading errors and ensures proper library loading
 * Updated: May 30, 2025
 */

(function() {
  console.log('Module fix initializing');
  
  // Define globals to prevent module loading errors
  window.jQuery = window.jQuery || (typeof $ !== 'undefined' ? $ : null);
  window.$ = window.jQuery;
  
  
  // Create shims for the problematic modules
  window.Popper = window.Popper || {};
  window.bootstrap = window.bootstrap || {
    Modal: function(element) {
      return {
        show: function() {
          element.style.display = 'block';
          element.classList.add('show');
          document.body.classList.add('modal-open');
          
          // Create backdrop if needed
          if (!document.querySelector('.modal-backdrop')) {
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
          }
        },
        hide: function() {
          element.style.display = 'none';
          element.classList.remove('show');
          document.body.classList.remove('modal-open');
          
          // Remove backdrop
          const backdrop = document.querySelector('.modal-backdrop');
          if (backdrop) backdrop.remove();
        }
      };
    }
  };
  
  // Create shims for import/export
  if (typeof module === 'undefined') {
    window.module = { exports: {} };
    window.exports = module.exports;
    window.require = function(name) {
      console.log('Attempted to require: ' + name);
      // Map common modules to global variables
      if (name === 'jquery') return window.jQuery;
      if (name === '@popperjs/core') return window.Popper;
      if (name === 'bootstrap') return window.bootstrap;
      return {};
    };
  }
  
  // Load essential libraries in the correct order
  function loadLibraries() {
    // Helper to load script and return a promise
    function loadScript(src) {
      return new Promise((resolve, reject) => {
        if (document.querySelector('script[src*="' + src + '"]')) {
          // Script already loaded or similar source already exists
          resolve();
          return;
        }
        
        const script = document.createElement('script');
        script.src = src;
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
      });
    }
    
    // Determine which libraries need to be loaded
    const needsjQuery = (typeof jQuery === 'undefined' && typeof $ === 'undefined');
    const needsPopper = (typeof Popper === 'undefined' || !Popper.createPopper);
    const needsBootstrap = (typeof bootstrap === 'undefined' || !bootstrap.Modal);
    
    console.log('Library needs - jQuery:', needsjQuery, 'Popper:', needsPopper, 'Bootstrap:', needsBootstrap);
    
    // Create a chain of promises to load libraries in the correct order
    let chain = Promise.resolve();
    
    // Step 1: Load jQuery if needed
    if (needsjQuery) {
      chain = chain.then(() => {
        console.log('Loading jQuery');
        return loadScript('https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js');
      }).then(() => {
        console.log('jQuery loaded successfully');
        window.jQuery = window.$;
      }).catch(error => {
        console.error('Error loading jQuery:', error);
      });
    }
    
    // Step 2: Load Popper if needed
    if (needsPopper) {
      chain = chain.then(() => {
        console.log('Loading Popper');
        return loadScript('https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.8/umd/popper.min.js');
      }).then(() => {
        console.log('Popper loaded successfully');
      }).catch(error => {
        console.error('Error loading Popper:', error);
      });
    }
    
    // Step 3: Load Bootstrap if needed
    if (needsBootstrap) {
      chain = chain.then(() => {
        console.log('Loading Bootstrap');
        return loadScript('https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.min.js');
      }).then(() => {
        console.log('Bootstrap loaded successfully');
        initializeBootstrap();
      }).catch(error => {
        console.error('Error loading Bootstrap:', error);
      });
    } else if (typeof bootstrap !== 'undefined') {
      // If Bootstrap is already loaded, ensure it's initialized
      chain = chain.then(() => {
        console.log('Bootstrap already available, initializing');
        initializeBootstrap();
      });
    }
    
    return chain;
  }
  
  // Initialize Bootstrap components
  function initializeBootstrap() {
    if (typeof bootstrap !== 'undefined') {
      try {
        console.log('Initializing Bootstrap components');
        
        // Enable tooltips
        if (bootstrap.Tooltip) {
          const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
          tooltipTriggerList.forEach(function(tooltipTriggerEl) {
            try {
              new bootstrap.Tooltip(tooltipTriggerEl);
            } catch (e) {
              console.warn('Error initializing tooltip:', e);
            }
          });
        }
        
        // Enable popovers
        if (bootstrap.Popover) {
          const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
          popoverTriggerList.forEach(function(popoverTriggerEl) {
            try {
              new bootstrap.Popover(popoverTriggerEl);
            } catch (e) {
              console.warn('Error initializing popover:', e);
            }
          });
        }
        
        // Initialize modal functionality
        if (bootstrap.Modal) {
          // This is handled in modal-fix.js
          console.log('Modal functionality available');
        }
        
        console.log('Bootstrap components initialized');
      } catch (error) {
        console.error('Error during Bootstrap initialization:', error);
      }
    }
  }
  
  // Load libraries after DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
      loadLibraries().then(() => {
        console.log('All libraries loaded and initialized');
        
        // Dispatch an event that other scripts can listen for
        document.dispatchEvent(new CustomEvent('librariesLoaded'));
      });
    });
  } else {
    // DOM already loaded, load libraries now
    loadLibraries().then(() => {
      console.log('All libraries loaded and initialized');
      
      // Dispatch an event that other scripts can listen for
      document.dispatchEvent(new CustomEvent('librariesLoaded'));
    });
  }
  
  // Add error handler to catch and log module-related errors
  window.addEventListener('error', function(event) {
    if (event.message && (
      event.message.includes('require is not defined') || 
      event.message.includes('Cannot read properties of undefined (reading \'getCssVar\')') ||
      event.message.includes('import') || 
      event.message.includes('export') ||
      event.message.includes('module')
    )) {
      console.warn('Module error handled:', event.message);
      event.preventDefault(); // Prevent the error from stopping execution
    }
  }, true);
  
  // Make sure $ is defined in global scope (for scripts that expect it)
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery !== 'undefined' && typeof $ === 'undefined') {
      window.$ = jQuery;
      console.log('Set $ to jQuery in global scope');
    }
  });
})();

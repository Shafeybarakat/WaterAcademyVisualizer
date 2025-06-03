/**
 * Water Academy Error Handler
 * Suppresses specific console errors and provides custom error handling
 */

(function() {
  'use strict';
  
  // Store the original console.error function
  const originalConsoleError = console.error;
  
  // Override console.error to filter out specific errors
  console.error = function(...args) {
    // Check if the error message contains specific patterns we want to suppress
    const errorMessage = args.join(' ');
    
    // List of error patterns to suppress
    const suppressPatterns = [
      'The message port closed before a response was received',
      'Unchecked runtime.lastError',
      'A listener indicated an asynchronous response by returning true, but the message channel closed'
    ];
    
    // Check if the error matches any of our suppress patterns
    const shouldSuppress = suppressPatterns.some(pattern => 
      errorMessage.includes(pattern)
    );
    
    // If it's not a suppressed error, pass it to the original console.error
    if (!shouldSuppress) {
      originalConsoleError.apply(console, args);
    }
  };
  
  // Handle uncaught errors
  window.addEventListener('error', function(event) {
    // Prevent the error from showing in console if it's one we want to suppress
    if (event.error && event.error.message) {
      const errorMessage = event.error.message;
      
      // Suppress specific errors
      if (errorMessage.includes('you is not defined')) {
        event.preventDefault();
        return false;
      }
      
      // Suppress import statement errors in non-module scripts
      if (errorMessage.includes('Cannot use import statement outside a module')) {
        event.preventDefault();
        return false;
      }
    }
  });
  
  // Log that the error handler is active
  console.log('Water Academy Error Handler initialized');
})();

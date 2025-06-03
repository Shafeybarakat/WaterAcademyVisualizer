/**
 * Error Handler Script
 * 
 * This script helps suppress common browser extension-related errors
 * that might appear in the console but don't affect functionality.
 */

// Handle runtime.lastError messages that might occur with browser extensions
window.addEventListener('error', function(event) {
  // Check if the error is related to runtime.lastError
  if (event.message && event.message.includes('runtime.lastError')) {
    // Prevent the error from appearing in console
    event.preventDefault();
    return true;
  }
});

// Handle promise rejection errors related to message channels
window.addEventListener('unhandledrejection', function(event) {
  // Check if the rejection is related to message channel closing
  if (event.reason && 
      (event.reason.message && event.reason.message.includes('message channel closed') ||
       event.reason.toString().includes('message channel closed'))) {
    // Prevent the rejection from appearing in console
    event.preventDefault();
    return true;
  }
});

// Suppress vendor.js errors related to message channels
(function() {
  // Store the original console.error
  const originalConsoleError = console.error;
  
  // Override console.error to filter out specific errors
  console.error = function() {
    // Check if the error is from vendor.js and related to message channels
    if (arguments[0] && 
        (typeof arguments[0] === 'string' && arguments[0].includes('message channel closed') ||
         arguments[0].stack && arguments[0].stack.includes('vendor.js'))) {
      // Don't log this error
      return;
    }
    
    // Pass through to the original console.error for all other errors
    return originalConsoleError.apply(console, arguments);
  };
})();

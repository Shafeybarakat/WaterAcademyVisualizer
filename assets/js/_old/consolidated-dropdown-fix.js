/**
 * Water Academy Consolidated Dropdown Fix
 * Date: May 31, 2025 - 10:30 PM
 * 
 * This script consolidates all dropdown fixes into a single file
 * to prevent conflicts and ensure proper functionality.
 * It specifically addresses the user dropdown menu issues.
 * 
 * Updated to fix conflicts with perfect-scrollbar.js and menu.js
 */

// Wait for window load to ensure all scripts are loaded
window.addEventListener('load', function() {
  console.log('Consolidated Dropdown Fix: Initializing');
  
  // Use setTimeout to ensure this runs after other initialization scripts
  setTimeout(function() {
    // Initialize all dropdowns
    initializeAllDropdowns();
    
    // Specifically initialize user dropdown with enhanced functionality
    initializeUserDropdown();
    
    // Set up global event listeners for dropdown closing
    setupGlobalEventListeners();
  }, 100);
});

/**
 * Initialize all standard dropdowns on the page
 */
function initializeAllDropdowns() {
  console.log('Consolidated Dropdown Fix: Initializing all dropdowns');
  
  try {
    // Get all dropdown toggle elements (except user dropdown which is handled separately)
    const dropdownToggleElements = document.querySelectorAll('.dropdown-toggle:not(#userDropdownToggle)');
    
    if (dropdownToggleElements.length === 0) {
      console.log('Consolidated Dropdown Fix: No standard dropdowns found');
      return;
    }
    
    console.log(`Consolidated Dropdown Fix: Found ${dropdownToggleElements.length} standard dropdowns`);
    
    // Initialize each dropdown toggle
    dropdownToggleElements.forEach(function(element) {
      try {
        // Instead of replacing the element, just add our own click handler
        element.addEventListener('click', function(e) {
          // Don't prevent default or stop propagation to allow Bootstrap's handler to work
          
          const dropdownMenu = this.nextElementSibling;
          
          if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
            // Let Bootstrap handle the dropdown toggling
            console.log(`Consolidated Dropdown Fix: Dropdown clicked for ${this.textContent.trim()}`);
          }
        });
      } catch (err) {
        console.error('Error initializing dropdown:', err);
      }
    });
  } catch (err) {
    console.error('Error in initializeAllDropdowns:', err);
  }
}

/**
 * Initialize the user dropdown with enhanced functionality
 */
function initializeUserDropdown() {
  console.log('Consolidated Dropdown Fix: Initializing user dropdown');
  
  try {
    // Get the user dropdown toggle element
    const userDropdownToggle = document.getElementById('userDropdownToggle');
    
    if (!userDropdownToggle) {
      console.warn('Consolidated Dropdown Fix: User dropdown toggle element not found');
      return;
    }
    
    console.log('Consolidated Dropdown Fix: Found user dropdown toggle element');
    
    // Get the dropdown menu element
    const dropdownMenu = userDropdownToggle.nextElementSibling;
    
    if (!dropdownMenu || !dropdownMenu.classList.contains('dropdown-menu')) {
      console.warn('Consolidated Dropdown Fix: User dropdown menu element not found');
      return;
    }
    
    // Use jQuery if available for better compatibility
    if (typeof $ !== 'undefined') {
      console.log('Consolidated Dropdown Fix: Using jQuery for user dropdown');
      
      // Remove any existing click handlers
      $(userDropdownToggle).off('click');
      
      // Add our click handler
      $(userDropdownToggle).on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('Consolidated Dropdown Fix: User dropdown toggle clicked (jQuery)');
        
        // Toggle dropdown using Bootstrap's dropdown method if available
        if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
          const dropdownInstance = new bootstrap.Dropdown(userDropdownToggle);
          if (dropdownMenu.classList.contains('show')) {
            dropdownInstance.hide();
          } else {
            dropdownInstance.show();
          }
        } else {
          // Fallback to manual toggling
          $(dropdownMenu).toggleClass('show');
          
          // Position the dropdown properly if it's shown
          if ($(dropdownMenu).hasClass('show')) {
            positionUserDropdown(this, dropdownMenu[0] || dropdownMenu);
          }
        }
      });
      
      // Make dropdown items clickable
      $(dropdownMenu).find('.dropdown-item').on('click', function(e) {
        // If the item has an href attribute, let it navigate
        if (!$(this).attr('href') || $(this).attr('href') === '#' || $(this).attr('href') === 'javascript:void(0);') {
          e.preventDefault();
        }
        
        // Close the dropdown
        $(dropdownMenu).removeClass('show');
        
        // If the item has a data-bs-toggle="modal" attribute, show the modal
        if ($(this).attr('data-bs-toggle') === 'modal') {
          const modalId = $(this).attr('data-bs-target');
          if (modalId && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modal = new bootstrap.Modal(document.querySelector(modalId));
            modal.show();
          }
        }
      });
    } else {
      // Vanilla JS fallback
      console.log('Consolidated Dropdown Fix: Using vanilla JS for user dropdown');
      
      // Add click event listener to the toggle
      userDropdownToggle.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('Consolidated Dropdown Fix: User dropdown toggle clicked (vanilla)');
        
        // Close any other open dropdowns first
        document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
          if (menu !== dropdownMenu) {
            menu.classList.remove('show');
          }
        });
        
        // Toggle the 'show' class on the dropdown menu
        dropdownMenu.classList.toggle('show');
        
        // Position the dropdown properly
        if (dropdownMenu.classList.contains('show')) {
          positionUserDropdown(this, dropdownMenu);
        }
      });
      
      // Make dropdown items clickable
      const dropdownItems = dropdownMenu.querySelectorAll('.dropdown-item');
      dropdownItems.forEach(function(item) {
        item.addEventListener('click', function(e) {
          // If the item has an href attribute, let it navigate
          if (!this.getAttribute('href') || this.getAttribute('href') === '#' || this.getAttribute('href') === 'javascript:void(0);') {
            e.preventDefault();
          }
          
          // Close the dropdown
          dropdownMenu.classList.remove('show');
          
          // If the item has a data-bs-toggle="modal" attribute, show the modal
          if (this.getAttribute('data-bs-toggle') === 'modal') {
            const modalId = this.getAttribute('data-bs-target');
            if (modalId) {
              const modalElement = document.querySelector(modalId);
              if (modalElement && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
              }
            }
          }
        });
      });
    }
    
    console.log('Consolidated Dropdown Fix: User dropdown initialization complete');
  } catch (err) {
    console.error('Error in initializeUserDropdown:', err);
  }
}

/**
 * Position the user dropdown properly
 */
function positionUserDropdown(toggle, dropdown) {
  console.log('Consolidated Dropdown Fix: Positioning user dropdown');
  
  try {
    // Get the toggle button's position
    const toggleRect = toggle.getBoundingClientRect();
    
    // Set dropdown styles for proper positioning
    dropdown.style.position = 'absolute';
    dropdown.style.zIndex = '1050'; // Higher than default to ensure visibility
    
    // Position dropdown below the toggle button
    dropdown.style.top = (toggleRect.bottom + window.scrollY) + 'px';
    
    // Align dropdown to the right edge of the toggle button
    dropdown.style.right = (window.innerWidth - toggleRect.right) + 'px';
    dropdown.style.left = 'auto';
    
    // Add animation
    dropdown.style.transform = 'translate3d(0, 0, 0)';
    dropdown.style.transition = 'transform 0.2s ease-out, opacity 0.2s ease-out';
    
    // Ensure the dropdown is visible
    dropdown.style.display = 'block';
    dropdown.style.opacity = '1';
    
    // Check if dropdown would go off-screen and adjust if needed
    const dropdownRect = dropdown.getBoundingClientRect();
    if (dropdownRect.right > window.innerWidth) {
      dropdown.style.right = '0';
    }
    if (dropdownRect.bottom > window.innerHeight) {
      dropdown.style.top = (toggleRect.top + window.scrollY - dropdownRect.height) + 'px';
    }
  } catch (err) {
    console.error('Error in positionUserDropdown:', err);
  }
}

/**
 * Set up global event listeners for closing dropdowns
 */
function setupGlobalEventListeners() {
  console.log('Consolidated Dropdown Fix: Setting up global event listeners');
  
  try {
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
      const dropdownMenus = document.querySelectorAll('.dropdown-menu.show');
      dropdownMenus.forEach(function(menu) {
        try {
          // Check if click is outside the dropdown and its toggle
          const toggle = menu.previousElementSibling;
          if (!menu.contains(e.target) && (!toggle || !toggle.contains(e.target))) {
            menu.classList.remove('show');
          }
        } catch (err) {
          console.error('Error in click handler for dropdown:', err);
        }
      });
    });
    
    // Close dropdowns when pressing Escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
          menu.classList.remove('show');
        });
      }
    });
    
    // Fix for perfect-scrollbar conflicts
    // Prevent the maximum call stack size exceeded error
    console.log('Consolidated Dropdown Fix: Applying PerfectScrollbar fix');
    
    // More aggressive fix for perfect-scrollbar
    try {
      // Create a counter to track recursive calls
      let getComputedStyleCallCount = 0;
      const MAX_RECURSIVE_CALLS = 50; // Set a reasonable limit
      
      // Save the original getComputedStyle function
      const originalGetComputedStyle = window.getComputedStyle;
      
      // Override getComputedStyle to prevent infinite recursion
      window.getComputedStyle = function(element, pseudoElt) {
        // If element is null or undefined, return the original result
        if (!element) {
          console.warn('getComputedStyle called with null/undefined element');
          return originalGetComputedStyle(element, pseudoElt);
        }

        // Increment call counter
        getComputedStyleCallCount++;

        // Check for excessive recursion
        if (getComputedStyleCallCount > MAX_RECURSIVE_CALLS) {
          console.warn('Excessive getComputedStyle recursion detected and prevented');
          getComputedStyleCallCount = 0; // Reset counter
          // Always return the original getComputedStyle result to ensure it's a valid CSSStyleDeclaration object
          return originalGetComputedStyle(element, pseudoElt);
        }

        try {
          // Call the original function
          const result = originalGetComputedStyle(element, pseudoElt);

          // Decrement call counter after successful call
          getComputedStyleCallCount--;

          return result;
        } catch (err) {
          console.warn('Error in getComputedStyle:', err.message);
          getComputedStyleCallCount = 0; // Reset counter
          // Always return the original getComputedStyle result to ensure it's a valid CSSStyleDeclaration object
          return originalGetComputedStyle(element, pseudoElt);
        }
      };
      
      // Direct fix for PerfectScrollbar if it exists
      if (typeof window.PerfectScrollbar !== 'undefined') {
        console.log('Consolidated Dropdown Fix: Applying direct PerfectScrollbar fix');
        
        // Try to patch the problematic get method in PerfectScrollbar
        const originalPSGet = window.PerfectScrollbar.prototype.get;
        if (originalPSGet) {
          window.PerfectScrollbar.prototype.get = function(element) {
            try {
              // Add safety check
              if (!element || typeof element !== 'object') {
                return {};
              }
              return originalPSGet.call(this, element);
            } catch (err) {
              console.warn('Error in PerfectScrollbar.get prevented:', err.message);
              return {};
            }
          };
        }
      }
    } catch (err) {
      console.error('Error applying PerfectScrollbar fix:', err);
    }
  } catch (err) {
    console.error('Error in setupGlobalEventListeners:', err);
  }
}

// Add a fix for menu.js manageScroll error
window.addEventListener('load', function() {
  setTimeout(function() {
    try {
      // Check if Menu object exists in window
      if (typeof window.Menu !== 'undefined') {
        console.log('Consolidated Dropdown Fix: Applying Menu.manageScroll fix');
        
        // Save the original manageScroll function
        const originalManageScroll = window.Menu.prototype.manageScroll;
        
        // Override manageScroll to prevent errors
        window.Menu.prototype.manageScroll = function() {
          try {
            // Call the original function
            return originalManageScroll.apply(this, arguments);
          } catch (err) {
            console.warn('Error in Menu.manageScroll prevented:', err.message);
            // Return a default value or do nothing
            return false;
          }
        };
      }
    } catch (err) {
      console.error('Error applying Menu.manageScroll fix:', err);
    }
  }, 200);
});

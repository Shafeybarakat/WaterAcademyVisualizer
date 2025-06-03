/**
 * Water Academy UI Components Module
 * Handles initialization and functionality for various UI components,
 * including dropdowns, specifically the user dropdown menu.
 * Adopted and refactored from assets/js/old/consolidated-dropdown-fix.js.
 */

const UI_Components_Module = (function() { // Use a local variable for the module
  /**
   * Initializes all UI components handled by this module.
   */
  function init() {
    console.log('UI_Components_Module: Initializing module');
    
    // Initialize all standard dropdowns on the page
    initializeAllDropdowns();
    
    // Specifically initialize user dropdown with enhanced functionality
    initializeUserDropdown();
    
    // Set up global event listeners for dropdown closing
    setupGlobalEventListeners();
  }

  /**
   * Initialize all standard dropdowns on the page
   */
  function initializeAllDropdowns() {
    console.log('UI_Components: Initializing all standard dropdowns');
    
    try {
      // Get all dropdown toggle elements (except user dropdown which is handled separately)
      const dropdownToggleElements = document.querySelectorAll('.dropdown-toggle:not(#userDropdownToggle)');
      
      if (dropdownToggleElements.length === 0) {
        console.log('UI_Components: No standard dropdowns found');
        return;
      }
      
      console.log(`UI_Components: Found ${dropdownToggleElements.length} standard dropdowns`);
      
      // Initialize each dropdown toggle
      dropdownToggleElements.forEach(function(element) {
        try {
          // Add our own click handler, allowing Bootstrap's handler to work if present
          element.addEventListener('click', function(e) {
            // Let Bootstrap handle the dropdown toggling, or fall back to manual if needed
            const dropdownMenu = this.nextElementSibling;
            if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
              console.log(`UI_Components: Standard dropdown clicked for ${this.textContent.trim()}`);
            }
          });
        } catch (err) {
          console.error('UI_Components: Error initializing standard dropdown:', err);
        }
      });
    } catch (err) {
      console.error('UI_Components: Error in initializeAllDropdowns:', err);
    }
  }

  /**
   * Initialize the user dropdown with enhanced functionality.
   * This function is forced to use jQuery/vanilla JS to bypass Bootstrap's native dropdown.
   */
  function initializeUserDropdown() {
    console.log('UI_Components: Initializing user dropdown (forced jQuery/Vanilla)');
    
    try {
      // Get the user dropdown toggle element
      const userDropdownToggle = document.getElementById('userDropdownToggle');
      
      if (!userDropdownToggle) {
        console.warn('UI_Components: User dropdown toggle element not found');
        return;
      }
      
      console.log('UI_Components: Found user dropdown toggle element');
      
      // Get the dropdown menu element
      const dropdownMenu = userDropdownToggle.nextElementSibling;
      
      if (!dropdownMenu || !dropdownMenu.classList.contains('dropdown-menu')) {
        console.warn('UI_Components: User dropdown menu element not found');
        return;
      }
      
      // ALWAYS use jQuery if available, otherwise vanilla JS, to bypass Bootstrap's native dropdown
      if (typeof $ !== 'undefined') {
        console.log('UI_Components: Using jQuery for user dropdown');
        
        // Remove any existing click handlers to prevent conflicts
        $(userDropdownToggle).off('click');
        
        // Add our click handler
        $(userDropdownToggle).on('click', function(e) {
          e.preventDefault();
          e.stopPropagation(); // Prevent propagation to avoid other handlers
          
          console.log('UI_Components: User dropdown toggle clicked (jQuery)');
          
          // Close any other open dropdowns first
          document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
            if (menu !== dropdownMenu) {
              $(menu).removeClass('show');
            }
          });
          
          // Toggle the 'show' class on the dropdown menu
          $(dropdownMenu).toggleClass('show');
          
          // Position the dropdown properly if it's shown
          if ($(dropdownMenu).hasClass('show')) {
            positionUserDropdown(this, dropdownMenu[0] || dropdownMenu);
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
              // Always use WA_Modal.show for consistency
              if (modalId && typeof WA_Modal !== 'undefined' && typeof WA_Modal.show === 'function') {
                WA_Modal.show(modalId.substring(1)); // Remove '#' from ID
              } else {
                console.warn(`UI_Components: WA_Modal.show not available for modal ${modalId}. Modal may not open.`);
              }
          }
        });
      } else {
        // Vanilla JS fallback
        console.log('UI_Components: Using vanilla JS for user dropdown');
        
        // Add click event listener to the toggle
        userDropdownToggle.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          
          console.log('UI_Components: User dropdown toggle clicked (vanilla)');
          
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
              // Always use WA_Modal.show for consistency
              if (modalId && typeof WA_Modal !== 'undefined' && typeof WA_Modal.show === 'function') {
                WA_Modal.show(modalId.substring(1)); // Remove '#' from ID
              } else {
                console.warn(`UI_Components: WA_Modal.show not available for modal ${modalId}. Modal may not open.`);
              }
            }
          });
        });
      }
      
      console.log('UI_Components: User dropdown initialization complete');
    } catch (err) {
      console.error('UI_Components: Error in initializeUserDropdown:', err);
    }
  }

  /**
   * Position the user dropdown properly
   */
  function positionUserDropdown(toggle, dropdown) {
    console.log('UI_Components: Positioning user dropdown');
    
    try {
      // Get the toggle button's position
      const toggleRect = toggle.getBoundingClientRect();
      
      // Set dropdown styles for proper positioning
      dropdown.style.position = 'absolute';
      dropdown.style.zIndex = '1050'; // Higher than default to ensure visibility
      
      // Ensure the dropdown is visible (display: block) and has initial opacity for transition
      dropdown.style.display = 'block';
      dropdown.style.opacity = '1';
      
      // Get dropdown dimensions after it's displayed
      const dropdownRect = dropdown.getBoundingClientRect();

      // Get footer height to account for it
      const footer = document.querySelector('.footer');
      const footerHeight = footer ? footer.offsetHeight : 0;

      const viewportHeight = window.innerHeight;
      const spaceBelow = viewportHeight - toggleRect.bottom - footerHeight; // Space below toggle, above footer
      const spaceAbove = toggleRect.top;

      // Default position: below the toggle, aligned to the right edge of the viewport
      dropdown.style.top = (toggleRect.bottom + window.scrollY) + 'px';
      dropdown.style.right = '0px !important'; // Align to the right edge of the viewport, force with !important
      dropdown.style.left = 'auto !important'; // Ensure left is auto when right is 0, force with !important

      // If not enough space below (considering footer), try to position above
      if (dropdownRect.height > spaceBelow && dropdownRect.height <= spaceAbove) {
        dropdown.style.top = (toggleRect.top + window.scrollY - dropdownRect.height) + 'px';
        console.log('UI_Components: Positioning user dropdown ABOVE toggle due to space constraints.');
      } else if (dropdownRect.height > spaceBelow && dropdownRect.height > spaceAbove) {
        // If not enough space above or below, try to fit best (keep below, but ensure it's visible)
        // In this case, it will extend below the viewport/footer, but at least it's aligned to the right.
        console.warn('UI_Components: Not enough space above or below for user dropdown. May still overflow vertically.');
      }
      
      // Ensure it doesn't go off the top of the screen if positioned above
      if (dropdown.getBoundingClientRect().top < 0) {
        dropdown.style.top = (window.scrollY) + 'px'; // Align to top of viewport
      }

      // Add animation (re-apply after positioning)
      dropdown.style.transform = 'translate3d(0, 0, 0)';
      dropdown.style.transition = 'transform 0.2s ease-out, opacity 0.2s ease-out';

    } catch (err) {
      console.error('UI_Components: Error in positionUserDropdown:', err);
    }
  }

  /**
   * Set up global event listeners for closing dropdowns
   */
  function setupGlobalEventListeners() {
    console.log('UI_Components: Setting up global event listeners');
    
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
            console.error('UI_Components: Error in click handler for dropdown:', err);
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
      
      // Removed: PerfectScrollbar and Menu.js patching logic
      // These issues will be addressed in Phase 2 (Vendor File Management)
      // by either replacing the libraries or ensuring proper initialization order.

    } catch (err) {
      console.error('UI_Components: Error in setupGlobalEventListeners:', err);
    }
  }

  // Public API
  return {
    init: init
  };
})();

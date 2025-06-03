/**
 * Water Academy Dropdown Menu Fix
 * Date: May 31, 2025 - Updated 9:28 PM
 * 
 * This script fixes the user dropdown menu functionality
 * by properly initializing Bootstrap dropdowns and ensuring
 * they work correctly with click events.
 */

document.addEventListener('DOMContentLoaded', function() {
  // Fix for user dropdown menu
  initUserDropdown();
  
  // Function to initialize the user dropdown
  function initUserDropdown() {
    const userDropdownToggle = document.getElementById('userDropdownToggle');
    
    if (userDropdownToggle) {
      console.log('Found user dropdown toggle element');
      
      // First, try using Bootstrap's native Dropdown if available
      if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
        try {
          // Force recreate the dropdown instance
          if (bootstrap.Dropdown.getInstance(userDropdownToggle)) {
            bootstrap.Dropdown.getInstance(userDropdownToggle).dispose();
          }
          
          const dropdown = new bootstrap.Dropdown(userDropdownToggle, {
            autoClose: true,
            boundary: 'viewport'
          });
          
          // Add event listener to position dropdown when shown
          userDropdownToggle.addEventListener('shown.bs.dropdown', function() {
            const dropdownMenu = this.nextElementSibling;
            if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
              positionDropdown(this, dropdownMenu);
            }
          });
          
          console.log('User dropdown initialized with Bootstrap Dropdown');
          return; // Exit if successful
        } catch (error) {
          console.warn('Failed to initialize with Bootstrap Dropdown:', error);
          // Continue to fallback method
        }
      }
      
      // Fallback method using jQuery if available
      if (typeof $ !== 'undefined' || typeof jQuery !== 'undefined') {
        try {
          const jq = $ || jQuery;
          
          // Destroy any existing dropdown
          if (jq(userDropdownToggle).data('bs.dropdown')) {
            jq(userDropdownToggle).dropdown('dispose');
          }
          
          // Initialize dropdown
          jq(userDropdownToggle).dropdown({
            autoClose: true,
            boundary: 'viewport'
          });
          
          // Add event listener to position dropdown when shown
          jq(userDropdownToggle).on('shown.bs.dropdown', function() {
            const dropdownMenu = jq(this).next('.dropdown-menu')[0];
            if (dropdownMenu) {
              positionDropdown(this, dropdownMenu);
            }
          });
          
          console.log('User dropdown initialized with jQuery dropdown');
          return; // Exit if successful
        } catch (error) {
          console.warn('Failed to initialize with jQuery dropdown:', error);
          // Continue to manual method
        }
      }
      
      // Manual method as last resort
      userDropdownToggle.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Close any other open dropdowns first
        document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
          if (menu !== this.nextElementSibling) {
            menu.classList.remove('show');
          }
        }, this);
        
        const dropdownMenu = this.nextElementSibling;
        if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
          // Toggle the 'show' class
          dropdownMenu.classList.toggle('show');
          
          // Position the dropdown properly
          positionDropdown(this, dropdownMenu);
          
          console.log('User dropdown toggled manually');
        }
      });
      
      // Close dropdown when clicking outside
      document.addEventListener('click', function(e) {
        const dropdownMenus = document.querySelectorAll('.dropdown-menu.show');
        dropdownMenus.forEach(function(menu) {
          // Check if click is outside the dropdown
          if (!menu.contains(e.target) && 
              !menu.previousElementSibling.contains(e.target)) {
            menu.classList.remove('show');
          }
        });
      });
      
      // Add keyboard navigation for accessibility
      document.addEventListener('keydown', function(e) {
        // Close dropdowns on Escape key
        if (e.key === 'Escape') {
          document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
            menu.classList.remove('show');
          });
        }
      });
      
      console.log('Manual dropdown handlers attached');
    } else {
      console.warn('User dropdown toggle element not found');
    }
  }
  
  // Function to position the dropdown properly
  function positionDropdown(toggle, dropdown) {
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
  }
  
  // Initialize all other dropdowns on the page
  initAllDropdowns();
  
  function initAllDropdowns() {
    const allDropdownToggles = document.querySelectorAll('[data-bs-toggle="dropdown"]');
    
    allDropdownToggles.forEach(function(toggle) {
      if (toggle.id !== 'userDropdownToggle') { // Skip user dropdown as it's handled separately
        if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
          try {
            new bootstrap.Dropdown(toggle);
          } catch (error) {
            console.warn('Failed to initialize dropdown:', error);
          }
        }
      }
    });
  }
});

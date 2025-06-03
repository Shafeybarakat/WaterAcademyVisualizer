/**
 * Water Academy Dropdown Fix
 * Fixes issues with Bootstrap dropdowns
 * Updated: May 31, 2025 - Enhanced user dropdown menu functionality
 */

document.addEventListener('DOMContentLoaded', function() {
  // Fix dropdowns that might be broken
  const dropdownToggleElements = document.querySelectorAll('.dropdown-toggle');
  dropdownToggleElements.forEach(function(element) {
    element.addEventListener('click', function(e) {
      const dropdownMenu = this.nextElementSibling;
      if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
        // Close any other open dropdowns first
        document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
          if (menu !== dropdownMenu) {
            menu.classList.remove('show');
          }
        });
        
        // Toggle this dropdown
        dropdownMenu.classList.toggle('show');
        
        // Position dropdown properly for user menu
        if (this.id === 'userDropdownToggle' || this.closest('.dropdown-user')) {
          positionUserDropdown(this, dropdownMenu);
        }
        
        e.preventDefault();
        e.stopPropagation();
      }
    });
  });
  
  // Close dropdowns when clicking outside
  document.addEventListener('click', function(e) {
    const dropdownMenus = document.querySelectorAll('.dropdown-menu.show');
    dropdownMenus.forEach(function(menu) {
      if (!menu.contains(e.target) && !menu.previousElementSibling.contains(e.target)) {
        menu.classList.remove('show');
      }
    });
  });
  
  // Specifically target the user dropdown menu
  const userDropdownToggle = document.getElementById('userDropdownToggle');
  if (userDropdownToggle) {
    // Try using Bootstrap's native Dropdown if available
    if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
      try {
        new bootstrap.Dropdown(userDropdownToggle);
        console.log('User dropdown initialized with Bootstrap Dropdown');
      } catch (error) {
        console.warn('Failed to initialize with Bootstrap Dropdown:', error);
        // Continue with fallback method
      }
    } else if (typeof $ !== 'undefined' || typeof jQuery !== 'undefined') {
      // Try jQuery method if available
      try {
        const jq = $ || jQuery;
        jq(userDropdownToggle).dropdown();
        console.log('User dropdown initialized with jQuery dropdown');
      } catch (error) {
        console.warn('Failed to initialize with jQuery dropdown:', error);
        // Continue with manual method
      }
    }
    
    // Ensure the user dropdown is properly positioned
    userDropdownToggle.addEventListener('click', function(e) {
      const dropdownMenu = this.nextElementSibling;
      if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
        positionUserDropdown(this, dropdownMenu);
      }
    });
  }
  
  // Function to position the user dropdown properly
  function positionUserDropdown(toggle, dropdown) {
    // Get the toggle button's position
    const toggleRect = toggle.getBoundingClientRect();
    
    // Ensure the dropdown is positioned correctly
    dropdown.style.position = 'absolute';
    dropdown.style.zIndex = '1000';
    
    // For right-aligned user dropdown (in the header)
    if (toggle.closest('.dropdown-user')) {
      // Position below the toggle button
      dropdown.style.top = (toggleRect.bottom + window.scrollY) + 'px';
      
      // Align to the right edge of the toggle button
      dropdown.style.right = (window.innerWidth - toggleRect.right) + 'px';
      dropdown.style.left = 'auto';
      
      // Add animation
      dropdown.style.transform = 'translate3d(0, 0, 0)';
      dropdown.style.transition = 'transform 0.2s ease-out, opacity 0.2s ease-out';
      
      // Ensure the dropdown is visible
      dropdown.style.display = 'block';
      dropdown.style.opacity = '1';
    }
  }
  
  // Add keyboard navigation for accessibility
  document.addEventListener('keydown', function(e) {
    // Close dropdowns on Escape key
    if (e.key === 'Escape') {
      document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
        menu.classList.remove('show');
      });
    }
  });
});

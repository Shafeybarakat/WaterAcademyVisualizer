/**
 * Water Academy User Dropdown Fix
 * Date: May 31, 2025 - 9:58 PM
 * 
 * This script specifically fixes the user dropdown menu functionality
 * by ensuring it works correctly with click events and proper positioning.
 */

document.addEventListener('DOMContentLoaded', function() {
  console.log('User Dropdown Fix: Initializing');
  
  // Get the user dropdown toggle element
  const userDropdownToggle = document.getElementById('userDropdownToggle');
  
  if (!userDropdownToggle) {
    console.warn('User Dropdown Fix: User dropdown toggle element not found');
    return;
  }
  
  console.log('User Dropdown Fix: Found user dropdown toggle element');
  
  // Get the dropdown menu element
  const dropdownMenu = userDropdownToggle.nextElementSibling;
  
  if (!dropdownMenu || !dropdownMenu.classList.contains('dropdown-menu')) {
    console.warn('User Dropdown Fix: Dropdown menu element not found');
    return;
  }
  
  // Remove any existing click event listeners from the toggle
  const newToggle = userDropdownToggle.cloneNode(true);
  userDropdownToggle.parentNode.replaceChild(newToggle, userDropdownToggle);
  
  // Add click event listener to the toggle
  newToggle.addEventListener('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    console.log('User Dropdown Fix: Toggle clicked');
    
    // Toggle the 'show' class on the dropdown menu
    dropdownMenu.classList.toggle('show');
    
    // Position the dropdown properly
    positionDropdown(this, dropdownMenu);
  });
  
  // Function to position the dropdown properly
  function positionDropdown(toggle, dropdown) {
    console.log('User Dropdown Fix: Positioning dropdown');
    
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
  }
  
  // Close dropdown when clicking outside
  document.addEventListener('click', function(e) {
    if (!dropdownMenu.contains(e.target) && !newToggle.contains(e.target)) {
      dropdownMenu.classList.remove('show');
    }
  });
  
  // Close dropdown when pressing Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && dropdownMenu.classList.contains('show')) {
      dropdownMenu.classList.remove('show');
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
  
  console.log('User Dropdown Fix: Initialization complete');
});

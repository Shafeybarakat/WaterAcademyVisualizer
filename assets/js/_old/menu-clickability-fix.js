/**
 * Water Academy Menu Clickability Fix
 * Ensures menu items are clickable regardless of viewport
 * Updated: May 30, 2025
 */

document.addEventListener('DOMContentLoaded', function() {
  console.log('Menu clickability fix initializing');
  
  // Make sure all menu items are clickable
  function fixMenuItemClickability() {
    // Select all menu items and links
    const menuItems = document.querySelectorAll('.menu-item');
    const menuLinks = document.querySelectorAll('.menu-link');
    const sidebar = document.querySelector('.menu.menu-vertical');
    
    // Fix sidebar if it exists
    if (sidebar) {
      sidebar.style.overflowY = 'auto';
      
      // For mobile, ensure proper styles
      if (window.innerWidth < 1200) {
        sidebar.style.position = 'fixed';
        sidebar.style.height = '100vh';
        sidebar.style.top = '0';
        sidebar.style.left = '0';
        sidebar.style.zIndex = '2000';
        sidebar.style.width = '260px';
        
        // Initialize the transform property based on expanded state
        const isExpanded = document.body.classList.contains('layout-menu-expanded');
        sidebar.style.transform = isExpanded ? 'translateX(0)' : 'translateX(-100%)';
      }
    }
    
    // Apply necessary styles and event listeners to menu items
    menuItems.forEach(function(item) {
      // Ensure proper z-index and pointer events
      item.style.position = 'relative';
      item.style.zIndex = '20';
      item.style.pointerEvents = 'auto';
      
      // Make sure menu items are visible
      item.style.display = 'block';
      item.style.opacity = '1';
      
      // Remove any transform that might be hiding the item
      item.style.transform = 'none';
    });
    
    // Apply necessary styles and event listeners to menu links
    menuLinks.forEach(function(link) {
      // Ensure proper z-index and pointer events
      link.style.position = 'relative';
      link.style.zIndex = '25';
      link.style.pointerEvents = 'auto';
      
      // Make sure links are visible
      link.style.display = 'flex';
      link.style.opacity = '1';
      
      // Remove any transform that might be hiding the link
      link.style.transform = 'none';
      
      // Fix any menu icons inside the link
      const menuIcon = link.querySelector('.menu-icon');
      if (menuIcon) {
        menuIcon.style.pointerEvents = 'none'; // Let clicks pass through to the link
      }
      
      // Fix any menu text inside the link
      const menuText = link.querySelector('.menu-text');
      if (menuText) {
        menuText.style.pointerEvents = 'none'; // Let clicks pass through to the link
      }
      
      // Clear any existing click handlers to avoid duplication
      link.onclick = null;
      
      // Add new click handler
      link.addEventListener('click', function(e) {
        console.log('Menu link clicked:', link.textContent.trim());
        
        // Get the href attribute
        const href = link.getAttribute('href');
        
        // If link has href attribute and it's not a placeholder, follow it
        if (href && href !== '#' && href !== 'javascript:void(0)') {
          // If it's not an external link, close the sidebar on mobile
          if (!href.startsWith('http') && window.innerWidth < 1200) {
            document.body.classList.remove('layout-menu-expanded');
            if (sidebar) sidebar.style.transform = 'translateX(-100%)';
            
            // Hide overlay
            const overlay = document.querySelector('.layout-overlay');
            if (overlay) {
              overlay.style.opacity = '0';
              overlay.style.visibility = 'hidden';
              setTimeout(() => {
                overlay.style.display = 'none';
              }, 300);
            }
          }
          
          // Let the default behavior handle navigation
        } else {
          // For links without href or with javascript:void(0), prevent default
          e.preventDefault();
        }
      });
    });
    
    // Fix mobile menu toggle button
    const mobileToggleBtn = document.querySelector('.d-xl-none .nav-link');
    if (mobileToggleBtn) {
      mobileToggleBtn.style.zIndex = '1050';
      mobileToggleBtn.style.position = 'relative';
      mobileToggleBtn.style.pointerEvents = 'auto';
      
      // Ensure the toggle button is visible
      mobileToggleBtn.style.display = 'block';
      mobileToggleBtn.style.opacity = '1';
      
      // Make sure any icons inside the toggle are properly visible
      const toggleIcon = mobileToggleBtn.querySelector('.bx');
      if (toggleIcon) {
        toggleIcon.style.display = 'inline-block';
        toggleIcon.style.opacity = '1';
      }
    }
    
    console.log('Menu item clickability fix applied');
  }
  
  // Apply fixes initially
  fixMenuItemClickability();
  
  // Re-apply fixes when layout changes
  window.addEventListener('resize', function() {
    fixMenuItemClickability();
  });
  
  // Re-apply fixes when body class changes (sidebar expanded/collapsed)
  const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
      if (mutation.attributeName === 'class') {
        fixMenuItemClickability();
      }
    });
  });
  
  observer.observe(document.body, { attributes: true });
  
  // Handle clicks on overlay to close sidebar
  document.addEventListener('click', function(e) {
    if (e.target.classList.contains('layout-overlay') && document.body.classList.contains('layout-menu-expanded')) {
      document.body.classList.remove('layout-menu-expanded');
      
      const sidebar = document.querySelector('.menu.menu-vertical');
      if (sidebar) sidebar.style.transform = 'translateX(-100%)';
      
      // Hide overlay
      const overlay = document.querySelector('.layout-overlay');
      if (overlay) {
        overlay.style.opacity = '0';
        overlay.style.visibility = 'hidden';
        setTimeout(() => {
          overlay.style.display = 'none';
        }, 300);
      }
    }
  });
  
  // Handle toggle button click manually
  const mobileToggleBtn = document.querySelector('.d-xl-none .nav-link');
  if (mobileToggleBtn) {
    mobileToggleBtn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation(); // Prevent event bubbling
      
      console.log('Mobile toggle button clicked');
      
      // Toggle layout-menu-expanded class on body
      document.body.classList.toggle('layout-menu-expanded');
      
      // Update sidebar transform
      const sidebar = document.querySelector('.menu.menu-vertical');
      if (sidebar) {
        if (document.body.classList.contains('layout-menu-expanded')) {
          sidebar.style.transform = 'translateX(0)';
        } else {
          sidebar.style.transform = 'translateX(-100%)';
        }
      }
      
      // Update overlay
      const overlay = document.querySelector('.layout-overlay');
      if (overlay) {
        if (document.body.classList.contains('layout-menu-expanded')) {
          overlay.style.display = 'block';
          // Force reflow
          overlay.offsetHeight;
          overlay.style.opacity = '1';
          overlay.style.visibility = 'visible';
        } else {
          overlay.style.opacity = '0';
          overlay.style.visibility = 'hidden';
          setTimeout(() => {
            overlay.style.display = 'none';
          }, 300);
        }
      }
    });
  }
});

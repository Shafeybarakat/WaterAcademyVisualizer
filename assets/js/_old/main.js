/**
 * Main
 */

'use strict';

let menu,
  animate;

// Expose the initialization function globally
window.initMainJs = function() {
  console.log('initMainJs function called.');
  // class for ios specific styles
  if (navigator.userAgent.match(/iPhone|iPad|iPod/i)) {
    document.body.classList.add('ios');
  }

  // Initialize menu
  //-----------------

  let layoutMenuEl = document.querySelectorAll('#layout-menu');
  layoutMenuEl.forEach(function (element) {
    // Ensure Menu is defined before trying to instantiate
    if (typeof Menu !== 'undefined') {
      menu = new Menu(element, {
        orientation: 'vertical',
        closeChildren: false
      });
      // Change parameter to true if you want scroll animation
      if (window.Helpers) { // Ensure Helpers is available
        window.Helpers.scrollToActive((animate = false));
        window.Helpers.mainMenu = menu;
      }
    } else {
      console.error('Menu class not found. Cannot initialize main menu.');
    }
  });

  // Initialize menu togglers and bind click on each
  let menuToggler = document.querySelectorAll('.layout-menu-toggle');
  menuToggler.forEach(item => {
    item.addEventListener('click', event => {
      event.preventDefault();
      if (window.Helpers) { // Ensure Helpers is available
        window.Helpers.toggleCollapsed();
      }
    });
  });

  // Display menu toggle (layout-menu-toggle) on hover with delay
  let delay = function (elem, callback) {
    let timeout = null;
    elem.onmouseenter = function () {
      // Set timeout to be a timer which will invoke callback after 300ms (not for small screen)
      if (window.Helpers && !Helpers.isSmallScreen()) {
        timeout = setTimeout(callback, 300);
      } else {
        timeout = setTimeout(callback, 0);
      }
    };

    elem.onmouseleave = function () {
      // Clear any timers set to timeout
      const layoutMenuToggle = document.querySelector('.layout-menu-toggle');
      if (layoutMenuToggle) {
        layoutMenuToggle.classList.remove('d-block');
      }
      clearTimeout(timeout);
    };
  };
  if (document.getElementById('layout-menu')) {
    delay(document.getElementById('layout-menu'), function () {
      // not for small screen
      if (window.Helpers && !Helpers.isSmallScreen()) {
        const layoutMenuToggle = document.querySelector('.layout-menu-toggle');
        if (layoutMenuToggle) {
          layoutMenuToggle.classList.add('d-block');
        }
      }
    });
  }

  // Display in main menu when menu scrolls
  let menuInnerContainer = document.getElementsByClassName('menu-inner'),
    menuInnerShadow = document.getElementsByClassName('menu-inner-shadow')[0];
  if (menuInnerContainer.length > 0 && menuInnerShadow) {
    menuInnerContainer[0].addEventListener('ps-scroll-y', function () {
      if (this.querySelector('.ps__thumb-y').offsetTop) {
        menuInnerShadow.style.display = 'block';
      } else {
        menuInnerShadow.style.display = 'none';
      }
    });
  }

  // Init helpers & misc
  // --------------------

  // Init BS Tooltip
  // Ensure bootstrap.Tooltip is available
  if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  } else {
    console.warn('Bootstrap Tooltip not available. Tooltips may not function.');
  }

  // Init BS Dropdown (using native data-bs-toggle)
  // No explicit JS initialization needed if data-bs-toggle is used correctly.
  // However, for dynamically added dropdowns, manual initialization might be required.

  // Init BS Modals
  if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
    const modalElementList = [].slice.call(document.querySelectorAll('.modal'));
    modalElementList.map(function (modalEl) {
      return new bootstrap.Modal(modalEl);
    });
    console.log('Bootstrap Modals initialized.');
  } else {
    console.warn('Bootstrap Modal not available. Modals may not function.');
  }

  // Accordion active class
  const accordionActiveFunction = function (e) {
    if (e.type == 'show.bs.collapse' || e.type == 'show.bs.collapse') {
      const accordionItem = e.target.closest('.accordion-item');
      if (accordionItem) {
        accordionItem.classList.add('active');
      }
    } else {
      const accordionItem = e.target.closest('.accordion-item');
      if (accordionItem) {
        accordionItem.classList.remove('active');
      }
    }
  };

  const accordionTriggerList = [].slice.call(document.querySelectorAll('.accordion'));
  accordionTriggerList.map(function (accordionTriggerEl) {
    accordionTriggerEl.addEventListener('show.bs.collapse', accordionActiveFunction);
    accordionTriggerEl.addEventListener('hide.bs.collapse', accordionActiveFunction);
  });

  // Auto update layout based on screen size
  if (window.Helpers) { // Ensure Helpers is available
    window.Helpers.setAutoUpdate(true);
  }

  // Toggle Password Visibility
  if (window.Helpers) { // Ensure Helpers is available
    window.Helpers.initPasswordToggle();
  }

  // Speech To Text
  if (window.Helpers) { // Ensure Helpers is available
    window.Helpers.initSpeechToText();
  }

  // Manage menu expanded/collapsed with templateCustomizer & local storage
  //------------------------------------------------------------------

  // If current layout is horizontal OR current window screen is small (overlay menu) than return from here
  if (window.Helpers && window.Helpers.isSmallScreen()) {
    return;
  }

  // If current layout is vertical and current window screen is > small

  // Auto update menu collapsed/expanded based on the themeConfig
  if (window.Helpers) { // Ensure Helpers is available
    window.Helpers.setCollapsed(true, false);
  }

}; // End of initMainJs function

// Utils
function isMacOS() {
  return /Mac|iPod|iPhone|iPad/.test(navigator.userAgent);
}

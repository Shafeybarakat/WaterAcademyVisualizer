/*!
 * Bootstrap v5.3.0 (https://getbootstrap.com/)
 * Copyright 2011-2023 The Bootstrap Authors (https://github.com/twbs/bootstrap/graphs/contributors)
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/main/LICENSE)
 */
(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports) :
  typeof define === 'function' && define.amd ? define(['exports'], factory) :
  (global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory(global.bootstrap = {}));
})(this, (function (exports) { 'use strict';
  
  // Create a bootstrap object with basic components
  var bootstrap = {
    Alert: function(element) {
      // Alert implementation
      this.close = function() {
        if (element) {
          element.classList.add('d-none');
        }
      };
    },
    Button: function(element) {
      // Button implementation
      this.toggle = function() {
        if (element) {
          element.classList.toggle('active');
        }
      };
    },
    Collapse: function(element, options) {
      // Collapse implementation
      this.toggle = function() {
        if (element) {
          element.classList.toggle('show');
        }
      };
    },
    Dropdown: function(element) {
      // Dropdown implementation
      this.toggle = function() {
        if (element) {
          element.classList.toggle('show');
        }
      };
    },
    Modal: function(element) {
      // Modal implementation
      this.show = function() {
        if (element) {
          element.classList.add('show');
          element.style.display = 'block';
        }
      };
      this.hide = function() {
        if (element) {
          element.classList.remove('show');
          element.style.display = 'none';
        }
      };
    },
    Popover: function(element, options) {
      // Popover implementation
      this.show = function() {
        if (element) {
          element.setAttribute('data-bs-popover-shown', 'true');
        }
      };
      this.hide = function() {
        if (element) {
          element.removeAttribute('data-bs-popover-shown');
        }
      };
    },
    Tab: function(element) {
      // Tab implementation
      this.show = function() {
        if (element) {
          element.classList.add('active');
          var target = document.querySelector(element.getAttribute('data-bs-target'));
          if (target) {
            target.classList.add('active', 'show');
          }
        }
      };
    },
    Toast: function(element) {
      // Toast implementation
      this.show = function() {
        if (element) {
          element.classList.add('show');
        }
      };
      this.hide = function() {
        if (element) {
          element.classList.remove('show');
        }
      };
    },
    Tooltip: function(element, options) {
      // Tooltip implementation
      this.show = function() {
        if (element) {
          element.setAttribute('data-bs-tooltip-shown', 'true');
        }
      };
      this.hide = function() {
        if (element) {
          element.removeAttribute('data-bs-tooltip-shown');
        }
      };
    }
  };
  
  // Expose bootstrap to the global object
  try {
    window.bootstrap = bootstrap;
  } catch (e) {}
  
  // Export for CommonJS/AMD/ES modules
  exports.Alert = bootstrap.Alert;
  exports.Button = bootstrap.Button;
  exports.Collapse = bootstrap.Collapse;
  exports.Dropdown = bootstrap.Dropdown;
  exports.Modal = bootstrap.Modal;
  exports.Popover = bootstrap.Popover;
  exports.Tab = bootstrap.Tab;
  exports.Toast = bootstrap.Toast;
  exports.Tooltip = bootstrap.Tooltip;
  
  // Remove ES module flag to prevent import statement errors
  // Object.defineProperty(exports, '__esModule', { value: true });
  
  return exports;
}));

/**
 * Water Academy Helpers Utilities Module
 * Provides essential, non-UI-specific helper functions.
 * Refactored from assets/vendor/js/helpers.js to remove layout-specific logic
 * and direct style manipulations.
 */

const WA_Helpers = (function() {

  // Guard function for required parameters
  function requiredParam(name) {
    throw new Error(`Parameter required${name ? `: \`${name}\`` : ''}`);
  }

  // Root Element
  const ROOT_EL = typeof window !== 'undefined' ? document.documentElement : null;

  // Large screens breakpoint (kept for utility checks)
  const LAYOUT_BREAKPOINT = 1200;

  // *******************************************************************************
  // * Utilities

  // Add classes
  function addClass(cls, el = ROOT_EL) {
    if (el && el.length !== undefined) {
      el.forEach(e => {
        if (e) {
          cls.split(' ').forEach(c => e.classList.add(c));
        }
      });
    } else if (el) {
      cls.split(' ').forEach(c => el.classList.add(c));
    }
  }

  // Remove classes
  function removeClass(cls, el = ROOT_EL) {
    if (el && el.length !== undefined) {
      el.forEach(e => {
        if (e) {
          cls.split(' ').forEach(c => e.classList.remove(c));
        }
      });
    } else if (el) {
      cls.split(' ').forEach(c => el.classList.remove(c));
    }
  }

  // Toggle classes
  function toggleClass(el = ROOT_EL, cls1, cls2) {
    if (el.classList.contains(cls1)) {
      el.classList.replace(cls1, cls2);
    } else {
      el.classList.replace(cls2, cls1);
    }
  }

  // Has class
  function hasClass(cls, el = ROOT_EL) {
    let result = false;
    cls.split(' ').forEach(c => {
      if (el.classList.contains(c)) result = true;
    });
    return result;
  }

  // Find parent with specific class
  function findParent(el, cls) {
    if ((el && el.tagName.toUpperCase() === 'BODY') || el.tagName.toUpperCase() === 'HTML') return null;
    el = el.parentNode;
    while (el && el.tagName.toUpperCase() !== 'BODY' && !el.classList.contains(cls)) {
      el = el.parentNode;
    }
    el = el && el.tagName.toUpperCase() !== 'BODY' ? el : null;
    return el;
  }

  // Trigger window event
  function triggerWindowEvent(name) {
    if (typeof window === 'undefined') return;

    if (document.createEvent) {
      let event;
      if (typeof Event === 'function') {
        event = new Event(name);
      } else {
        event = document.createEvent('Event');
        event.initEvent(name, false, true);
      }
      window.dispatchEvent(event);
    } else {
      window.fireEvent(`on${name}`, document.createEventObject());
    }
  }

  // Check if device is mobile
  function isMobileDevice() {
    return typeof window.orientation !== 'undefined' || navigator.userAgent.indexOf('IEMobile') !== -1;
  }

  // Check if screen is small
  function isSmallScreen() {
    return (
      (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth) < LAYOUT_BREAKPOINT
    );
  }

  // Get CSS variables for theme colors
  function getCssVar(color, isChartJs = false) {
    // Ensure prefix is correctly retrieved from CSS
    const prefix = getComputedStyle(document.documentElement).getPropertyValue('--prefix').trim() || '';
    if (isChartJs === true) {
      return getComputedStyle(document.documentElement).getPropertyValue(`--${prefix}${color}`).trim();
    }
    return `var(--${prefix}${color})`;
  }

  // Init Password Toggle
  function initPasswordToggle() {
    const toggler = document.querySelectorAll('.form-password-toggle i');
    if (typeof toggler !== 'undefined' && toggler !== null) {
      toggler.forEach(el => {
        el.addEventListener('click', e => {
          e.preventDefault();
          const formPasswordToggle = el.closest('.form-password-toggle');
          const formPasswordToggleIcon = formPasswordToggle.querySelector('i');
          const formPasswordToggleInput = formPasswordToggle.querySelector('input');

          if (formPasswordToggleInput.getAttribute('type') === 'text') {
            formPasswordToggleInput.setAttribute('type', 'password');
            formPasswordToggleIcon.classList.replace('bx-show', 'bx-hide');
          } else if (formPasswordToggleInput.getAttribute('type') === 'password') {
            formPasswordToggleInput.setAttribute('type', 'text');
            formPasswordToggleIcon.classList.replace('bx-hide', 'bx-show');
          }
        });
      });
    }
  }

  // Init Speech To Text
  function initSpeechToText() {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    const speechToText = document.querySelectorAll('.speech-to-text');
    if (SpeechRecognition !== undefined && SpeechRecognition !== null) {
      if (typeof speechToText !== 'undefined' && speechToText !== null) {
        const recognition = new SpeechRecognition();
        const toggler = document.querySelectorAll('.speech-to-text i');
        toggler.forEach(el => {
          let listening = false;
          el.addEventListener('click', () => {
            el.closest('.input-group').querySelector('.form-control').focus();
            recognition.onspeechstart = () => {
              listening = true;
            };
            if (listening === false) {
              recognition.start();
            }
            recognition.onerror = () => {
              listening = false;
            };
            recognition.onresult = event => {
              el.closest('.input-group').querySelector('.form-control').value = event.results[0][0].transcript;
            };
            recognition.onspeechend = () => {
              listening = false;
              recognition.stop();
            };
          });
        });
      }
    }
  }

  // Ajax Call Promise
  function ajaxCall(url) {
    return new Promise((resolve, reject) => {
      const req = new XMLHttpRequest();
      req.open('GET', url);
      req.onload = () => (req.status === 200 ? resolve(req.response) : reject(Error(req.statusText)));
      req.onerror = e => reject(Error(`Network Error: ${e}`));
      req.send();
    });
  }

  // Public API
  return {
    requiredParam,
    addClass,
    removeClass,
    toggleClass,
    hasClass,
    findParent,
    triggerWindowEvent,
    isMobileDevice,
    isSmallScreen,
    getCssVar,
    initPasswordToggle,
    initSpeechToText,
    ajaxCall
  };
})();

const WA_Modal = (function() {
  // Store modal instances for reuse
  const modalInstances = {};
  
  /**
   * Initialize modal functionality
   */
  function init() {
    WA_Logger.info('Modal', 'Initializing modal handler');
    
    // Set up event listeners for WA custom declarative usage
    document.addEventListener('click', function(event) {
      WA_Logger.debug('Modal', 'Click event detected', event.target);
      
      // Check if the clicked element or its parent has data-modal-target attribute
      let target = event.target;
      let maxDepth = 3; // Check up to 3 levels up the DOM tree
      
      while (target && maxDepth > 0) {
        const targetId = target.getAttribute('data-modal-target');
        const action = target.getAttribute('data-modal-action') || 'show';
        
        if (targetId) {
          event.preventDefault();
          WA_Logger.info('Modal', `Handling click with data-modal-target="${targetId}"`);
          
          if (action === 'show') {
            show(targetId, {}, target);
          } else if (action === 'hide') {
            hide(targetId);
          }
          return; // Exit after handling
        }
        
        // Also support Bootstrap's data attributes for backward compatibility
        const bsTarget = target.getAttribute('data-bs-target');
        const bsToggle = target.getAttribute('data-bs-toggle');
        
        if (bsTarget && bsToggle === 'modal') {
          event.preventDefault();
          WA_Logger.info('Modal', `Handling click with data-bs-target="${bsTarget}"`);
          
          // Remove the # if present in the ID
          const modalId = bsTarget.startsWith('#') ? bsTarget.substring(1) : bsTarget;
          show(modalId, {}, target);
          return; // Exit after handling
        }
        
        // Move up the DOM tree
        target = target.parentElement;
        maxDepth--;
      }
    }, true); // Use capture phase to handle before other handlers
    
    // Handle ESC key to close modals
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        const openModals = document.querySelectorAll('.modal.show');
        if (openModals.length > 0) {
          const modalId = openModals[openModals.length - 1].id;
          hide(modalId);
        }
      }
    });
    
    // Handle backdrop clicks
    document.addEventListener('click', function(event) {
      if (event.target.classList.contains('modal') && event.target.getAttribute('data-backdrop') !== 'static') {
        hide(event.target.id);
      }
    });
  }
  
  /**
   * Show a modal by ID using the appropriate method based on configuration
   */
  function show(modalId, options = {}, relatedTarget = null) {
    WA_Logger.info('Modal', `Showing modal "${modalId}"`);
    
    // Get the modal element
    const modalElement = document.getElementById(modalId);
    if (!modalElement) {
      WA_Logger.error('Modal', `Modal with ID '${modalId}' not found`);
      return null;
    }
    
    // Prepare custom event before showing
    const beforeShowEvent = new CustomEvent('wa.modal.beforeShow', {
      bubbles: true,
      cancelable: true,
      detail: { 
        modalId, 
        relatedTarget: relatedTarget,
        userData: extractUserData(relatedTarget)
      }
    });
    
    // Dispatch the event and check if it was cancelled
    const eventResult = modalElement.dispatchEvent(beforeShowEvent);
    if (!eventResult) {
      WA_Logger.info('Modal', `Showing modal "${modalId}" was cancelled by event handler`);
      return null;
    }
    
    // Use jQuery if available and configured to use it
    if (WA_Config.get('features.useJQueryForModals', true) && 
        typeof $ !== 'undefined' && 
        typeof $.fn.modal !== 'undefined') {
      try {
        const jqModalElement = $(modalElement);
        jqModalElement.modal(options); // Show the modal using jQuery
        WA_Logger.info('Modal', `Showed modal ${modalId} using jQuery`);
        
        // Store the jQuery instance
        modalInstances[modalId] = jqModalElement;
        
        // Trigger custom event for extensibility after showing
        modalElement.dispatchEvent(new CustomEvent('wa.modal.shown', {
          bubbles: true,
          detail: { 
            modalId, 
            instance: jqModalElement,
            relatedTarget: relatedTarget,
            userData: extractUserData(relatedTarget)
          }
        }));
        
        return jqModalElement;
      } catch (error) {
        WA_Logger.error('Modal', `Error showing modal "${modalId}" with jQuery:`, error);
        // Fall back to native Bootstrap if jQuery fails
      }
    }
    
    // Ultimate fallback - just show the modal with minimal functionality if jQuery is not available or fails
    WA_Logger.info('Modal', `Showing modal ${modalId} using manual DOM manipulation as ultimate fallback`);
    modalElement.classList.add('show');
    modalElement.style.display = 'block';
    document.body.classList.add('modal-open');
    
    // Create backdrop manually
    const backdrop = document.createElement('div');
    backdrop.className = 'modal-backdrop fade show';
    document.body.appendChild(backdrop);
    
    // Trigger custom event
    modalElement.dispatchEvent(new CustomEvent('wa.modal.shown', {
      bubbles: true,
      detail: { 
        modalId, 
        instance: null,
        relatedTarget: relatedTarget,
        userData: extractUserData(relatedTarget)
      }
    }));
    
    return modalElement;
  }
  
  /**
   * Extract user data from data attributes
   */
  function extractUserData(element) {
    if (!element) return {};
    
    const userData = {};
    
    // Get all data attributes
    for (let attr of element.attributes) {
      if (attr.name.startsWith('data-')) {
        // Convert data-attribute-name to attributeName
        const key = attr.name.substring(5).replace(/-([a-z])/g, (g) => g[1].toUpperCase());
        userData[key] = attr.value;
      }
    }
    
    return userData;
  }
  
  /**
   * Hide a modal by ID using the appropriate method
   */
  function hide(modalId) {
    WA_Logger.info('Modal', `Hiding modal "${modalId}"`);
    
    // Get the modal element
    const modalElement = document.getElementById(modalId);
    if (!modalElement) {
      WA_Logger.error('Modal', `Modal with ID '${modalId}' not found`);
      return false;
    }
    
    // Prepare custom event before hiding
    const beforeHideEvent = new CustomEvent('wa.modal.beforeHide', {
      bubbles: true,
      cancelable: true,
      detail: { modalId }
    });
    
    // Dispatch the event and check if it was cancelled
    const eventResult = modalElement.dispatchEvent(beforeHideEvent);
    if (!eventResult) {
      WA_Logger.info('Modal', `Hiding modal "${modalId}" was cancelled by event handler`);
      return false;
    }
    
    // Check if we have a stored instance
    const instance = modalInstances[modalId];
    
    // If we have a jQuery instance
    if (instance && typeof instance.modal === 'function') {
      try {
        instance.modal('hide');
        WA_Logger.info('Modal', `Hid modal "${modalId}" using jQuery`);
        
        // Trigger custom event for extensibility after hiding
        modalElement.dispatchEvent(new CustomEvent('wa.modal.hidden', {
          bubbles: true,
          detail: { modalId, instance }
        }));
        
        return true;
      } catch (error) {
        WA_Logger.error('Modal', `Error hiding modal "${modalId}" with jQuery:`, error);
      }
    }
    
    
    // Fallback to jQuery if available
    if (typeof $ !== 'undefined' && typeof $.fn.modal !== 'undefined') {
      try {
        $(modalElement).modal('hide');
        WA_Logger.info('Modal', `Hid modal "${modalId}" using jQuery (fallback)`);
        
        // Trigger custom event for extensibility after hiding
        modalElement.dispatchEvent(new CustomEvent('wa.modal.hidden', {
          bubbles: true,
          detail: { modalId, instance: null }
        }));
        
        return true;
      } catch (error) {
        WA_Logger.error('Modal', `Error hiding modal "${modalId}" with jQuery (fallback):`, error);
      }
    }
    
    // Ultimate fallback - just hide the modal manually
    try {
      modalElement.classList.remove('show');
      modalElement.style.display = 'none';
      document.body.classList.remove('modal-open');
      
      // Remove backdrop
      const backdrop = document.querySelector('.modal-backdrop');
      if (backdrop) {
        backdrop.parentNode.removeChild(backdrop);
      }
      
      WA_Logger.info('Modal', `Hid modal "${modalId}" manually (ultimate fallback)`);
      
      // Trigger custom event for extensibility after hiding
      modalElement.dispatchEvent(new CustomEvent('wa.modal.hidden', {
        bubbles: true,
        detail: { modalId, instance: null }
      }));
      
      return true;
    } catch (error) {
      WA_Logger.error('Modal', `Error hiding modal "${modalId}" manually:`, error);
      return false;
    }
  }
  
  /**
   * Update modal content
   * @param {string} modalId - The ID of the modal to update
   * @param {string} content - The HTML content to set
   * @param {string} target - The selector for the target element within the modal (optional)
   * @returns {boolean} Success status
   */
  function updateContent(modalId, content, target = '.modal-body') {
    WA_Logger.info('Modal', `Updating content for modal "${modalId}"`);
    
    const modalElement = document.getElementById(modalId);
    if (!modalElement) {
      WA_Logger.error('Modal', `Modal with ID '${modalId}' not found`);
      return false;
    }
    
    const targetElement = modalElement.querySelector(target);
    if (!targetElement) {
      WA_Logger.error('Modal', `Target element '${target}' not found in modal`);
      return false;
    }
    
    targetElement.innerHTML = content;
    return true;
  }
  
  /**
   * Set up a form inside a modal for AJAX submission
   * @param {string} modalId - The ID of the modal containing the form
   * @param {string} formSelector - The selector for the form element
   * @param {Function} onSubmit - Callback function when form is submitted
   * @param {Function} onSuccess - Callback function on successful submission
   * @param {Function} onError - Callback function on error
   */
  function setupFormSubmission(modalId, formSelector, onSubmit, onSuccess, onError) {
    WA_Logger.info('Modal', `Setting up form submission for modal "${modalId}"`);
    
    const modalElement = document.getElementById(modalId);
    if (!modalElement) {
      WA_Logger.error('Modal', `Modal with ID '${modalId}' not found`);
      return;
    }
    
    const form = modalElement.querySelector(formSelector);
    if (!form) {
      WA_Logger.error('Modal', `Form '${formSelector}' not found in modal`);
      return;
    }
    
    form.addEventListener('submit', function(event) {
      event.preventDefault();
      WA_Logger.info('Modal', `Form submitted in modal "${modalId}"`);
      
      // Call the onSubmit callback if provided
      if (typeof onSubmit === 'function') {
        const continueSubmission = onSubmit(form, event);
        if (continueSubmission === false) {
          return;
        }
      }
      
      // Get form data
      const formData = new FormData(form);
      
      // Send the AJAX request
      fetch(form.action || window.location.href, {
        method: form.method || 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
      })
      .then(data => {
        WA_Logger.info('Modal', `Form submission successful for modal "${modalId}"`);
        
        // Call the onSuccess callback if provided
        if (typeof onSuccess === 'function') {
          onSuccess(data, form);
        }
      })
      .catch(error => {
        WA_Logger.error('Modal', `Error submitting form for modal "${modalId}":`, error);
        
        // Call the onError callback if provided
        if (typeof onError === 'function') {
          onError(error, form);
        }
      });
    });
  }
  
  /**
   * Destroy a modal instance and clean up resources
   * @param {string} modalId - The ID of the modal to destroy
   * @returns {boolean} Success status
   */
  function destroy(modalId) {
    WA_Logger.info('Modal', `Destroying modal "${modalId}"`);
    
    // Get the modal element
    const modalElement = document.getElementById(modalId);
    if (!modalElement) {
      WA_Logger.error('Modal', `Modal with ID '${modalId}' not found`);
      return false;
    }
    
    try {
      // Remove the instance from our cache
      if (modalInstances[modalId]) {
        delete modalInstances[modalId];
      }
      return true;
    } catch (error) {
      WA_Logger.error('Modal', 'Error destroying modal:', error);
      return false;
    }
  }
  
  // Initialize when the DOM is ready
  document.addEventListener('DOMContentLoaded', init);
  
  // Initialize when core libraries are loaded (backup in case DOMContentLoaded already fired)
  document.addEventListener('wa.core.loaded', init);
  
  // Public API
  return {
    init, // Expose init function
    show,
    hide,
    updateContent,
    setupFormSubmission,
    destroy
  };
})();

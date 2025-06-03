/**
 * Water Academy Modal Handler (Consolidated)
 * Date: May 31, 2025 - 9:56 PM
 * 
 * This file consolidates and fixes modal functionality across the application.
 * It ensures proper initialization and handling of all modals, regardless of
 * how they are triggered or which Bootstrap version is being used.
 */

document.addEventListener('DOMContentLoaded', function() {
  console.log('Modal Handler: Initializing consolidated modal handler');
  
  // Initialize all modals on the page
  initializeAllModals();
  
  // Set up event listeners for modal triggers
  setupModalTriggers();
  
  // Initialize WA_Modal if available
  if (typeof WA_Modal !== 'undefined' && typeof WA_Modal.init === 'function') {
    console.log('Modal Handler: Initializing WA_Modal');
    WA_Modal.init();
  }
  
  // Initialize specific modals if they exist
  initializeSpecificModals();
});

/**
 * Initialize all modals on the page using Bootstrap's Modal constructor
 */
function initializeAllModals() {
  console.log('Modal Handler: Initializing all modals');
  
  // Get all modal elements
  const modalElements = document.querySelectorAll('.modal');
  
  if (modalElements.length === 0) {
    console.log('Modal Handler: No modals found on the page');
    return;
  }
  
  console.log(`Modal Handler: Found ${modalElements.length} modals`);
  
  // Check if Bootstrap is available
  if (typeof bootstrap !== 'undefined' && typeof bootstrap.Modal !== 'undefined') {
    console.log('Modal Handler: Using Bootstrap 5 Modal');
    
    // Initialize each modal with Bootstrap 5
    modalElements.forEach(function(modalElement) {
      try {
        // Check if modal is already initialized
        if (!bootstrap.Modal.getInstance(modalElement)) {
          // Initialize the modal
          new bootstrap.Modal(modalElement, {
            backdrop: modalElement.getAttribute('data-bs-backdrop') || true,
            keyboard: modalElement.getAttribute('data-bs-keyboard') !== 'false',
            focus: modalElement.getAttribute('data-bs-focus') !== 'false'
          });
          console.log(`Modal Handler: Initialized modal #${modalElement.id || 'unnamed'} with Bootstrap 5`);
        } else {
          console.log(`Modal Handler: Modal #${modalElement.id || 'unnamed'} already initialized with Bootstrap 5`);
        }
      } catch (error) {
        console.error(`Modal Handler: Error initializing modal #${modalElement.id || 'unnamed'}:`, error);
      }
    });
  } else if (typeof $ !== 'undefined' || typeof jQuery !== 'undefined') {
    // Fallback to jQuery if Bootstrap is not available
    const jq = $ || jQuery;
    console.log('Modal Handler: Using jQuery for modals');
    
    // Initialize each modal with jQuery
    modalElements.forEach(function(modalElement) {
      try {
        jq(modalElement).modal({
          backdrop: modalElement.getAttribute('data-bs-backdrop') || true,
          keyboard: modalElement.getAttribute('data-bs-keyboard') !== 'false',
          focus: modalElement.getAttribute('data-bs-focus') !== 'false',
          show: false // Don't show on initialization
        });
        console.log(`Modal Handler: Initialized modal #${modalElement.id || 'unnamed'} with jQuery`);
      } catch (error) {
        console.error(`Modal Handler: Error initializing modal #${modalElement.id || 'unnamed'} with jQuery:`, error);
      }
    });
  } else {
    console.warn('Modal Handler: Neither Bootstrap nor jQuery is available for modal initialization');
  }
}

/**
 * Set up event listeners for modal triggers
 */
function setupModalTriggers() {
  console.log('Modal Handler: Setting up modal triggers');
  
  // Get all elements with data-bs-toggle="modal"
  const modalTriggers = document.querySelectorAll('[data-bs-toggle="modal"]');
  
  if (modalTriggers.length === 0) {
    console.log('Modal Handler: No modal triggers found on the page');
    return;
  }
  
  console.log(`Modal Handler: Found ${modalTriggers.length} modal triggers`);
  
  // Set up click event listeners for each trigger
  modalTriggers.forEach(function(trigger) {
    // Remove existing click listeners to prevent duplication
    const newTrigger = trigger.cloneNode(true);
    trigger.parentNode.replaceChild(newTrigger, trigger);
    
    newTrigger.addEventListener('click', function(event) {
      event.preventDefault();
      
      const targetSelector = this.getAttribute('data-bs-target') || this.getAttribute('href');
      
      if (!targetSelector) {
        console.error('Modal Handler: No target specified for modal trigger');
        return;
      }
      
      console.log(`Modal Handler: Trigger clicked for ${targetSelector}`);
      
      const modalElement = document.querySelector(targetSelector);
      
      if (!modalElement) {
        console.error(`Modal Handler: Modal element ${targetSelector} not found`);
        return;
      }
      
      // Try to show the modal using Bootstrap 5
      if (typeof bootstrap !== 'undefined' && typeof bootstrap.Modal !== 'undefined') {
        try {
          let modalInstance = bootstrap.Modal.getInstance(modalElement);
          
          if (!modalInstance) {
            modalInstance = new bootstrap.Modal(modalElement);
          }
          
          modalInstance.show();
          console.log(`Modal Handler: Showed modal ${targetSelector} using Bootstrap 5`);
          return;
        } catch (error) {
          console.error(`Modal Handler: Error showing modal ${targetSelector} with Bootstrap 5:`, error);
        }
      }
      
      // Fallback to jQuery if Bootstrap failed or is not available
      if (typeof $ !== 'undefined' || typeof jQuery !== 'undefined') {
        const jq = $ || jQuery;
        
        try {
          jq(targetSelector).modal('show');
          console.log(`Modal Handler: Showed modal ${targetSelector} using jQuery`);
          return;
        } catch (error) {
          console.error(`Modal Handler: Error showing modal ${targetSelector} with jQuery:`, error);
        }
      }
      
      // Manual fallback if both Bootstrap and jQuery failed
      try {
        modalElement.style.display = 'block';
        modalElement.classList.add('show');
        document.body.classList.add('modal-open');
        
        // Create backdrop if it doesn't exist
        if (document.querySelector('.modal-backdrop') === null) {
          const backdrop = document.createElement('div');
          backdrop.className = 'modal-backdrop fade show';
          document.body.appendChild(backdrop);
        }
        
        console.log(`Modal Handler: Showed modal ${targetSelector} using manual DOM manipulation`);
      } catch (error) {
        console.error(`Modal Handler: Error showing modal ${targetSelector} manually:`, error);
      }
    });
  });
  
  // Set up event listeners for modal dismiss buttons
  const dismissButtons = document.querySelectorAll('[data-bs-dismiss="modal"]');
  
  dismissButtons.forEach(function(button) {
    // Remove existing click listeners to prevent duplication
    const newButton = button.cloneNode(true);
    button.parentNode.replaceChild(newButton, button);
    
    newButton.addEventListener('click', function(event) {
      event.preventDefault();
      
      const modalElement = this.closest('.modal');
      
      if (!modalElement) {
        console.error('Modal Handler: No modal found for dismiss button');
        return;
      }
      
      // Try to hide the modal using Bootstrap 5
      if (typeof bootstrap !== 'undefined' && typeof bootstrap.Modal !== 'undefined') {
        try {
          const modalInstance = bootstrap.Modal.getInstance(modalElement);
          
          if (modalInstance) {
            modalInstance.hide();
            console.log(`Modal Handler: Hid modal #${modalElement.id || 'unnamed'} using Bootstrap 5`);
            return;
          }
        } catch (error) {
          console.error(`Modal Handler: Error hiding modal #${modalElement.id || 'unnamed'} with Bootstrap 5:`, error);
        }
      }
      
      // Fallback to jQuery if Bootstrap failed or is not available
      if (typeof $ !== 'undefined' || typeof jQuery !== 'undefined') {
        const jq = $ || jQuery;
        
        try {
          jq(modalElement).modal('hide');
          console.log(`Modal Handler: Hid modal #${modalElement.id || 'unnamed'} using jQuery`);
          return;
        } catch (error) {
          console.error(`Modal Handler: Error hiding modal #${modalElement.id || 'unnamed'} with jQuery:`, error);
        }
      }
      
      // Manual fallback if both Bootstrap and jQuery failed
      try {
        modalElement.style.display = 'none';
        modalElement.classList.remove('show');
        document.body.classList.remove('modal-open');
        
        // Remove backdrop
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
          backdrop.remove();
        }
        
        console.log(`Modal Handler: Hid modal #${modalElement.id || 'unnamed'} using manual DOM manipulation`);
      } catch (error) {
        console.error(`Modal Handler: Error hiding modal #${modalElement.id || 'unnamed'} manually:`, error);
      }
    });
  });
  
  // Handle backdrop clicks to close modals
  document.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal') && event.target.classList.contains('show')) {
      const modalElement = event.target;
      
      // Check if modal has static backdrop
      if (modalElement.getAttribute('data-bs-backdrop') === 'static') {
        return;
      }
      
      // Try to hide the modal using Bootstrap 5
      if (typeof bootstrap !== 'undefined' && typeof bootstrap.Modal !== 'undefined') {
        try {
          const modalInstance = bootstrap.Modal.getInstance(modalElement);
          
          if (modalInstance) {
            modalInstance.hide();
            return;
          }
        } catch (error) {
          console.error(`Modal Handler: Error hiding modal #${modalElement.id || 'unnamed'} on backdrop click:`, error);
        }
      }
      
      // Fallback to jQuery if Bootstrap failed or is not available
      if (typeof $ !== 'undefined' || typeof jQuery !== 'undefined') {
        const jq = $ || jQuery;
        
        try {
          jq(modalElement).modal('hide');
          return;
        } catch (error) {
          console.error(`Modal Handler: Error hiding modal #${modalElement.id || 'unnamed'} on backdrop click with jQuery:`, error);
        }
      }
      
      // Manual fallback if both Bootstrap and jQuery failed
      try {
        modalElement.style.display = 'none';
        modalElement.classList.remove('show');
        document.body.classList.remove('modal-open');
        
        // Remove backdrop
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
          backdrop.remove();
        }
      } catch (error) {
        console.error(`Modal Handler: Error hiding modal #${modalElement.id || 'unnamed'} on backdrop click manually:`, error);
      }
    }
  });
  
  // Handle ESC key to close modals
  document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
      const openModals = document.querySelectorAll('.modal.show');
      
      if (openModals.length === 0) {
        return;
      }
      
      // Get the topmost modal (last in the list)
      const modalElement = openModals[openModals.length - 1];
      
      // Check if modal has keyboard=false
      if (modalElement.getAttribute('data-bs-keyboard') === 'false') {
        return;
      }
      
      // Try to hide the modal using Bootstrap 5
      if (typeof bootstrap !== 'undefined' && typeof bootstrap.Modal !== 'undefined') {
        try {
          const modalInstance = bootstrap.Modal.getInstance(modalElement);
          
          if (modalInstance) {
            modalInstance.hide();
            return;
          }
        } catch (error) {
          console.error(`Modal Handler: Error hiding modal #${modalElement.id || 'unnamed'} on ESC key:`, error);
        }
      }
      
      // Fallback to jQuery if Bootstrap failed or is not available
      if (typeof $ !== 'undefined' || typeof jQuery !== 'undefined') {
        const jq = $ || jQuery;
        
        try {
          jq(modalElement).modal('hide');
          return;
        } catch (error) {
          console.error(`Modal Handler: Error hiding modal #${modalElement.id || 'unnamed'} on ESC key with jQuery:`, error);
        }
      }
      
      // Manual fallback if both Bootstrap and jQuery failed
      try {
        modalElement.style.display = 'none';
        modalElement.classList.remove('show');
        document.body.classList.remove('modal-open');
        
        // Remove backdrop
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
          backdrop.remove();
        }
      } catch (error) {
        console.error(`Modal Handler: Error hiding modal #${modalElement.id || 'unnamed'} on ESC key manually:`, error);
      }
    }
  });
}

/**
 * Initialize specific modals that require special handling
 */
function initializeSpecificModals() {
  console.log('Modal Handler: Initializing specific modals');
  
  // Initialize Switch Role Modal if it exists
  const switchRoleModal = document.getElementById('switchRoleModal');
  if (switchRoleModal) {
    console.log('Modal Handler: Initializing Switch Role Modal');
    
    // Set up role selection change handler
    const roleSelect = document.getElementById('switch_role_select');
    const userSelect = document.getElementById('switch_user_select');
    
    if (roleSelect && userSelect) {
      roleSelect.addEventListener('change', function() {
        const selectedRole = this.value;
        
        if (!selectedRole) {
          userSelect.disabled = true;
          userSelect.innerHTML = '<option value="">Select a user</option>';
          return;
        }
        
        // Show loading state
        userSelect.disabled = true;
        userSelect.innerHTML = '<option value="">Loading users...</option>';
        
        // Fetch users for the selected role
        fetch('get_users_by_role.php?role=' + encodeURIComponent(selectedRole))
          .then(response => response.json())
          .then(data => {
            userSelect.innerHTML = '<option value="">Select a user</option>';
            
            data.forEach(user => {
              const option = document.createElement('option');
              option.value = user.UserID;
              option.textContent = user.FullName;
              userSelect.appendChild(option);
            });
            
            userSelect.disabled = false;
          })
          .catch(error => {
            console.error('Error fetching users:', error);
            userSelect.innerHTML = '<option value="">Error loading users</option>';
          });
      });
    }
  }
  
  // Initialize Group Wizard Modal if it exists
  const groupWizardModal = document.getElementById('groupWizardModal');
  if (groupWizardModal) {
    console.log('Modal Handler: Initializing Group Wizard Modal');
    
    // Set up event listener to load content when modal is shown
    groupWizardModal.addEventListener('shown.bs.modal', function() {
      // Check if content is already loaded
      const modalBody = this.querySelector('.modal-body');
      
      if (modalBody && modalBody.querySelector('.bs-stepper')) {
        console.log('Modal Handler: Group Wizard content already loaded');
        return;
      }
      
      console.log('Modal Handler: Loading Group Wizard content');
      
      // Show loading spinner
      modalBody.innerHTML = `
        <div class="d-flex justify-content-center align-items-center" style="min-height: 200px;">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
      `;
      
      // Load content via AJAX
      fetch('dashboards/group_wizard.php')
        .then(response => response.text())
        .then(html => {
          modalBody.innerHTML = html;
          
          // Initialize stepper if BS Stepper is available
          if (typeof window.Stepper !== 'undefined') {
            const stepper = new window.Stepper(document.querySelector('.bs-stepper'), {
              linear: true,
              animation: true
            });
            
            // Store stepper instance for later use
            window.groupWizardStepper = stepper;
          }
        })
        .catch(error => {
          console.error('Error loading Group Wizard content:', error);
          modalBody.innerHTML = `
            <div class="alert alert-danger">
              <i class="bx bx-error-circle me-2"></i>
              Error loading wizard content. Please try again.
            </div>
            <button class="btn btn-primary" onclick="location.reload()">Reload</button>
          `;
        });
    });
  }
  
  // Initialize any other specific modals here
}

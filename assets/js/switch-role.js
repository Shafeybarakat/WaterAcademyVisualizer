/**
 * assets/js/switch-role.js
 * Handles the functionality for the "Switch Role" modal.
 * This script dynamically populates the user dropdown based on the selected role
 * and handles the form submission for role switching.
 * 
 * Updated to work with Alpine.js modal system
 */

(function() {
  // Simple logger replacement
  const Logger = {
    log: (msg) => console.log(`[Switch Role] ${msg}`),
    error: (msg) => console.error(`[Switch Role] ${msg}`),
    info: (msg) => console.info(`[Switch Role] ${msg}`),
    debug: (msg) => console.debug(`[Switch Role] ${msg}`)
  };
  
  // Wait for DOM to be fully loaded
  document.addEventListener('DOMContentLoaded', function() {
    const switchRoleSelect = document.getElementById('switch_role_select');
    const switchUserSelect = document.getElementById('switch_user_select');
    const switchRoleForm = document.getElementById('switch_role_form');
    const switchRoleSubmitBtn = document.getElementById('switchRoleSubmitBtn');

    if (!switchRoleSelect || !switchUserSelect || !switchRoleForm || !switchRoleSubmitBtn) {
      Logger.error('One or more required elements for Switch Role modal not found. Functionality disabled.');
      return;
    }

    Logger.info('Initializing Switch Role functionality.');

    /**
     * Populates the user dropdown based on the selected role.
     * @param {string} roleName - The name of the selected role.
     */
    function populateUsersByRole(roleName) {
      Logger.debug(`Populating users for role: ${roleName}`);
      switchUserSelect.innerHTML = '<option value="">Loading users...</option>';
      switchUserSelect.disabled = true;

      if (!roleName) {
        switchUserSelect.innerHTML = '<option value="">Select a user</option>';
        return;
      }

      // AJAX call to fetch users by role
      // Using BASE_URL global variable defined in header.php
      fetch(`${window.location.origin}${BASE_URL}dashboards/get_users_by_role.php?role=${encodeURIComponent(roleName)}`)
        .then(response => {
          if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
          }
          return response.json();
        })
        .then(data => {
          if (data.success) {
            switchUserSelect.innerHTML = '<option value="">Select a user</option>'; // Reset options
            data.users.forEach(user => {
              const option = document.createElement('option');
              option.value = user.UserID;
              option.textContent = user.FullName;
              switchUserSelect.appendChild(option);
            });
            switchUserSelect.disabled = false;
            Logger.info(`Successfully loaded ${data.users.length} users for role ${roleName}.`);
          } else {
            switchUserSelect.innerHTML = '<option value="">Error loading users</option>';
            Logger.error(`Failed to load users: ${data.message}`);
          }
        })
        .catch(error => {
          switchUserSelect.innerHTML = '<option value="">Error loading users</option>';
          Logger.error(`Error fetching users by role: ${error}`);
        });
    }

    // Event listener for role selection change
    switchRoleSelect.addEventListener('change', function() {
      const selectedRole = this.value;
      populateUsersByRole(selectedRole);
    });

    // Set up submit button handler - Alpine.js handles the modal visibility
    switchRoleSubmitBtn.addEventListener('click', function(e) {
      e.preventDefault();
      
      // Validate form
      if (!switchRoleSelect.value || !switchUserSelect.value) {
        alert('Please select both a role and a user.');
        return;
      }
      
      Logger.info('Submitting role switch form');
      
      // Create FormData and submit using fetch
      const formData = new FormData(switchRoleForm);
      
      fetch(switchRoleForm.action, {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          Logger.info('Role switch successful. Reloading page.');
          // Close modal through Alpine - find the Alpine component and set open to false
          const modalComponent = document.querySelector('[x-data*="open"]');
          if (modalComponent && modalComponent.__x) {
            modalComponent.__x.$data.open = false;
          }
          // Reload the page to apply new role permissions and UI changes
          window.location.reload();
        } else {
          Logger.error(`Role switch failed: ${data.message}`);
          alert('Error switching role: ' + data.message);
        }
      })
      .catch(error => {
        Logger.error(`AJAX error during role switch: ${error}`);
        alert('An unexpected error occurred during role switch.');
      });
    });
    
    // For Alpine.js modal, we need to set up a reset when the modal opens
    document.addEventListener('alpine:initialized', () => {
      // Find the modal element with Alpine data
      const alpineModal = document.querySelector('[x-data*="open"]');
      if (alpineModal) {
        // Watch for changes to the open state
        alpineModal.addEventListener('x-on:open-changed', () => {
          if (alpineModal.__x.$data.open) {
            // Modal is opening, reset the form
            Logger.debug('Modal opened, resetting form');
            switchRoleForm.reset();
            switchUserSelect.innerHTML = '<option value="">Select a user</option>';
            switchUserSelect.disabled = true;
          }
        });
      }
    });
  });
})();

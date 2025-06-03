/**
 * assets/js/switch-role.js
 * Handles the functionality for the "Switch Role" modal.
 * This script dynamically populates the user dropdown based on the selected role
 * and handles the form submission for role switching.
 */

(function() {
  const switchRoleModal = document.getElementById('switchRoleModal');
  const switchRoleSelect = document.getElementById('switch_role_select');
  const switchUserSelect = document.getElementById('switch_user_select');
  const switchRoleForm = document.getElementById('switch_role_form');
  const switchRoleSubmitBtn = document.getElementById('switchRoleSubmitBtn');

  if (!switchRoleModal || !switchRoleSelect || !switchUserSelect || !switchRoleForm || !switchRoleSubmitBtn) {
    WA_Logger.error('SwitchRole', 'One or more required elements for Switch Role modal not found. Functionality disabled.');
    return;
  }

  WA_Logger.info('SwitchRole', 'Initializing Switch Role functionality.');

  /**
   * Populates the user dropdown based on the selected role.
   * @param {string} roleName - The name of the selected role.
   */
  function populateUsersByRole(roleName) {
    WA_Logger.debug('SwitchRole', `Populating users for role: ${roleName}`);
    switchUserSelect.innerHTML = '<option value="">Loading users...</option>';
    switchUserSelect.disabled = true;

    if (!roleName) {
      switchUserSelect.innerHTML = '<option value="">Select a user</option>';
      return;
    }

    // AJAX call to fetch users by role
    fetch(`${WA_Config.get('baseLinkPath')}dashboards/get_users_by_role.php?role=${encodeURIComponent(roleName)}`)
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
          WA_Logger.info('SwitchRole', `Successfully loaded ${data.users.length} users for role ${roleName}.`);
        } else {
          switchUserSelect.innerHTML = '<option value="">Error loading users</option>';
          WA_Logger.error('SwitchRole', 'Failed to load users:', data.message);
        }
      })
      .catch(error => {
        switchUserSelect.innerHTML = '<option value="">Error loading users</option>';
        WA_Logger.error('SwitchRole', 'Error fetching users by role:', error);
      });
  }

  // Event listener for role selection change
  switchRoleSelect.addEventListener('change', function() {
    const selectedRole = this.value;
    populateUsersByRole(selectedRole);
  });

  // Event listener for modal beforeShow to reset form and populate initial roles
  switchRoleModal.addEventListener('wa.modal.beforeShow', function() {
    WA_Logger.debug('SwitchRole', 'Switch Role modal beforeShow event. Resetting form.');
    switchRoleForm.reset();
    switchUserSelect.innerHTML = '<option value="">Select a user</option>';
    switchUserSelect.disabled = true;
    // The PHP part in footer.php already populates the initial roles, so no need to re-fetch them here.
  });

  // Handle form submission using WA_Modal's setupFormSubmission
  WA_Modal.setupFormSubmission(
    'switchRoleModal',
    '#switch_role_form',
    function(form) {
      // onSubmit callback: optional pre-submission logic
      WA_Logger.info('SwitchRole', 'Attempting to submit switch role form.');
      return true; // Allow submission to proceed
    },
    function(data) {
      // onSuccess callback
      if (data.success) {
        WA_Logger.info('SwitchRole', 'Role switch successful. Reloading page.');
        WA_Modal.hide('switchRoleModal');
        // Reload the page to apply new role permissions and UI changes
        window.location.reload(); 
      } else {
        WA_Logger.error('SwitchRole', 'Role switch failed:', data.message);
        alert('Error switching role: ' + data.message); // Display error to user
      }
    },
    function(error) {
      // onError callback
      WA_Logger.error('SwitchRole', 'AJAX error during role switch:', error);
      alert('An unexpected error occurred during role switch.'); // Generic error for user
    }
  );

  // Attach click handler to the submit button if it's not handled by form.submit() directly
  // This is important because the button has an onclick that calls form.submit()
  // We need to ensure our event listener on the form's submit event is the primary handler.
  // The WA_Modal.setupFormSubmission already attaches to the form's submit event.
  // If the button's onclick directly calls submit(), it bypasses our event listener.
  // So, we should remove the onclick from the button in footer.php or ensure it's handled.
  // For now, assuming WA_Modal.setupFormSubmission is sufficient.
  // If issues persist, the onclick attribute on the button in footer.php might need to be removed
  // and replaced with data-attributes for WA_Modal to handle.
  
})();

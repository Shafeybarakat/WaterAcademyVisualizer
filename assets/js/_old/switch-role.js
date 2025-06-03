/**
 * Switch Role functionality for admin users
 */
document.addEventListener('DOMContentLoaded', function() {
    // Get the role and user select elements
    const roleSelect = document.getElementById('switch_role_select');
    const userSelect = document.getElementById('switch_user_select');
    
    // If the elements don't exist, we're not on a page with the switch role functionality
    if (!roleSelect || !userSelect) return;
    
    // Handle role selection change
    roleSelect.addEventListener('change', function() {
        const selectedRole = this.value;
        
        // Clear and disable user select
        userSelect.innerHTML = '<option value="">Select a user</option>';
        userSelect.disabled = true;
        
        if (!selectedRole) return;
        
        // Show loading indicator
        userSelect.innerHTML = '<option value="">Loading users...</option>';
        
        // Fetch users with the selected role
        fetch('get_users_by_role.php?role=' + encodeURIComponent(selectedRole))
            .then(response => response.json())
            .then(data => {
                // Clear loading indicator
                userSelect.innerHTML = '<option value="">Select a user</option>';
                
                // Add users to the select element
                if (data.length > 0) {
                    data.forEach(user => {
                        const option = document.createElement('option');
                        option.value = user.UserID;
                        option.textContent = user.FullName;
                        userSelect.appendChild(option);
                    });
                    
                    // Enable user select
                    userSelect.disabled = false;
                } else {
                    // No users found
                    userSelect.innerHTML = '<option value="">No users found</option>';
                }
            })
            .catch(error => {
                console.error('Error fetching users:', error);
                userSelect.innerHTML = '<option value="">Error loading users</option>';
            });
    });
    
    // Handle form submission
    const switchRoleForm = document.getElementById('switch_role_form');
    if (switchRoleForm) {
        switchRoleForm.addEventListener('submit', function(event) {
            // Validate form
            if (!roleSelect.value || !userSelect.value) {
                event.preventDefault();
                alert('Please select both a role and a user.');
                return;
            }
            
            // Ensure the role value is passed to the server
            const roleInput = document.createElement('input');
            roleInput.type = 'hidden';
            roleInput.name = 'role';
            roleInput.value = roleSelect.value;
            
            // Check if the role input already exists
            const existingRoleInput = switchRoleForm.querySelector('input[name="role"]');
            if (existingRoleInput) {
                existingRoleInput.value = roleSelect.value;
            } else {
                switchRoleForm.appendChild(roleInput);
            }
            
            // Disable the submit button to prevent double submission
            const submitButton = switchRoleForm.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
            }
        });
    }
});

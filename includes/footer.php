      <!-- Footer -->
      <footer class="footer bg-primary-blue dark:bg-dark-bg text-white py-4 sticky bottom-0 shadow-md">
        <div class="container mx-auto px-4">
          <div class="text-center">
            © <script>document.write(new Date().getFullYear());</script>,
            Water Academy Visualizer - Designed & Implemented by Alshafei Barakat
          </div>
        </div>
      </footer>
      <!-- / Footer -->
      
      </div><!-- End of flex-1 overflow-auto (main content container) -->
    </div><!-- End of flex-1 flex flex-col (main content wrapper) -->
  </div><!-- End of flex h-full (page wrapper) -->


  <!-- Switch Role Modal (Alpine.js) -->
  <?php if (isLoggedIn() && hasPermission('manage_roles')): ?>
  <div x-data="{ open: false }" x-show="open" @keydown.escape.window="open = false" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen p-4">
      <div @click.away="open = false" class="bg-white rounded-lg shadow-xl max-w-md w-full">
        <div class="flex justify-between items-center p-4 border-b">
          <h5 class="text-lg font-medium text-gray-900">Switch Role</h5>
          <button @click="open = false" type="button" class="text-gray-400 hover:text-gray-500">
            <span class="sr-only">Close</span>
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="p-4">
          <?php if (isset($_SESSION['switched_user_id'])): ?>
          <!-- Currently switched to another user's role -->
          <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-sm text-blue-700">
                  You are currently viewing the system as <strong><?php echo htmlspecialchars($_SESSION['switched_user_name']); ?></strong> 
                  with role <strong><?php echo htmlspecialchars($_SESSION['switched_user_role']); ?></strong>.
                </p>
              </div>
            </div>
          </div>
          <a href="<?php echo htmlspecialchars(BASE_URL); ?>dashboards/switch_back.php" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700">
            <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Switch Back to Original Role
          </a>
          <?php else: ?>
          <!-- Form to switch to another user's role -->
          <p class="mb-4 text-gray-700">Select a role and user to view the system from their perspective.</p>
          <form id="switch_role_form" action="<?php echo htmlspecialchars(BASE_URL); ?>dashboards/switch_role.php" method="post">
            <input type="hidden" name="action" value="switch_role">
            <div class="mb-4">
              <label for="switch_role_select" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
              <select id="switch_role_select" name="role" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md" required>
                <option value="">Select a role</option>
                <?php
                // Get all roles from the database
                $roles_query = "SELECT RoleID, RoleName FROM Roles WHERE RoleName != 'Super Admin' ORDER BY RoleID";
                $roles_result = $conn->query($roles_query);
                $available_roles = [];
                
                if ($roles_result) {
                  while ($role_row = $roles_result->fetch_assoc()) {
                    // Only show roles with lower privilege (higher RoleID means lower privilege)
                    // Super Admin (RoleID 1) can see all roles except itself
                    // Admin (RoleID 2) can see all roles except Super Admin and itself
                    if ($_SESSION['role_id'] < $role_row['RoleID']) {
                      $available_roles[] = $role_row['RoleName'];
                    }
                  }
                }
                
                foreach ($available_roles as $role): ?>
                <option value="<?php echo htmlspecialchars($role); ?>"><?php echo htmlspecialchars($role); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-4">
              <label for="switch_user_select" class="block text-sm font-medium text-gray-700 mb-1">User</label>
              <select id="switch_user_select" name="user_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md" required disabled>
                <option value="">Select a user</option>
              </select>
            </div>
          </form>
          <?php endif; ?>
        </div>
        <div class="px-4 py-3 bg-gray-50 text-right sm:px-6 rounded-b-lg">
          <button type="button" @click="open = false" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 mr-2">
            Cancel
          </button>
          <?php if (!isset($_SESSION['switched_user_id'])): ?>
          <button type="button" id="switchRoleSubmitBtn" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            Switch
          </button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <!-- Include switch-role.js for the role switching functionality -->
  <script src="<?= BASE_ASSET_PATH ?>js/switch-role.js"></script>
  <?php endif; ?>

  <!-- Generic Modal for dynamic content loading (e.g., Group Wizard) - Alpine.js -->
  <div x-data="{ open: false }" x-show="open" @keydown.escape.window="open = false" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" id="groupWizardModal">
    <div class="flex items-center justify-center min-h-screen p-4">
      <div @click.away="open = false" class="bg-white rounded-lg shadow-xl max-w-6xl w-full">
        <div class="flex justify-between items-center p-4 border-b">
          <h5 class="text-lg font-medium text-gray-900" id="groupWizardModalLabel">New Group Wizard</h5>
          <button @click="open = false" type="button" class="text-gray-400 hover:text-gray-500">
            <span class="sr-only">Close</span>
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="p-4" id="groupWizardModalContent">
          <!-- Content will be loaded here via AJAX -->
          <div class="flex justify-center items-center" style="min-height: 200px;">
            <svg class="animate-spin h-10 w-10 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </div>
        </div>
      </div>
    </div>
  </div>

</body>
</html>
<?php
// Close the database connection if it was opened and is still active
if (isset($conn) && $conn instanceof mysqli && $conn->thread_id) {
    $conn->close();
}

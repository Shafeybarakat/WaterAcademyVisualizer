          <!-- / Content -->

          <!-- Footer -->
          <footer class="content-footer footer bg-footer-theme" style="position: sticky; bottom: 0; z-index: 1000; margin-top: auto;">
            <div class="container-xxl d-flex flex-wrap justify-content-center py-2 flex-md-row flex-column">
              <div class="mb-2 mb-md-0 text-center">
                © <script>document.write(new Date().getFullYear());</script>,
                Water Academy Visualizer - Designed & Implemented by Alshafei Barakat
              </div>
            </div>
          </footer>
          <!-- / Footer -->

          <div class="content-backdrop fade"></div>
        </div>
        <!-- Content wrapper -->
      </div>
      <!-- / Layout page -->
    </div>

    <!-- Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>
  </div>
  <!-- / Layout wrapper -->

  <!-- Core JS -->
  <script src="<?php echo htmlspecialchars($baseAssetPath); ?>vendor/libs/jquery/jquery.js"></script>
  <script src="<?php echo htmlspecialchars($baseAssetPath); ?>vendor/libs/popper/popper.js"></script>
  <script src="<?php echo htmlspecialchars($baseAssetPath); ?>vendor/js/bootstrap.js"></script>
  <script src="<?php echo htmlspecialchars($baseAssetPath); ?>vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
  <script src="<?php echo htmlspecialchars($baseAssetPath); ?>vendor/js/menu.js"></script>

  <!-- Vendors JS -->
  <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>

  <!-- Main JS -->
  <script src="<?php echo htmlspecialchars($baseAssetPath); ?>js/main.js"></script>

  <!-- Page Specific JS can be included by individual pages before this closing body tag -->

  <!-- Switch Role Modal -->
  <?php if (isLoggedIn() && hasPermission('manage_roles')): ?>
  <div class="modal fade" id="switchRoleModal" tabindex="-1" aria-labelledby="switchRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="switchRoleModalLabel">Switch Role</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <?php if (isset($_SESSION['switched_user_id'])): ?>
          <!-- Currently switched to another user's role -->
          <div class="alert alert-info mb-3">
            <i class="bx bx-info-circle me-2"></i>
            You are currently viewing the system as <strong><?php echo htmlspecialchars($_SESSION['switched_user_name']); ?></strong> 
            with role <strong><?php echo htmlspecialchars($_SESSION['switched_user_role']); ?></strong>.
          </div>
          <a href="<?php echo htmlspecialchars($baseLinkPath); ?>dashboards/switch_back.php" class="btn btn-primary">
            <i class="bx bx-reset me-1"></i> Switch Back to Original Role
          </a>
          <?php else: ?>
          <!-- Form to switch to another user's role -->
          <p class="mb-3">Select a role and user to view the system from their perspective.</p>
          <form id="switch_role_form" action="<?php echo htmlspecialchars($baseLinkPath); ?>dashboards/switch_role.php" method="post">
            <input type="hidden" name="action" value="switch_role">
            <div class="mb-3">
              <label for="switch_role_select" class="form-label">Role</label>
              <select id="switch_role_select" name="role" class="form-select" required>
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
            <div class="mb-3">
              <label for="switch_user_select" class="form-label">User</label>
              <select id="switch_user_select" name="user_id" class="form-select" required disabled>
                <option value="">Select a user</option>
              </select>
            </div>
          </form>
          <?php endif; ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <?php if (!isset($_SESSION['switched_user_id'])): ?>
          <button type="button" class="btn btn-primary" id="switchRoleSubmitBtn" onclick="document.getElementById('switch_role_form').submit();">Switch</button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <!-- Include switch-role.js for the role switching functionality -->
  <script src="<?php echo htmlspecialchars($baseAssetPath); ?>js/switch-role.js"></script>
  <?php endif; ?>

</body>
</html>
<?php
// Close the database connection if it was opened and is still active
if (isset($conn) && $conn instanceof mysqli && $conn->thread_id) {
    $conn->close();
}
?>

<?php
$pageTitle = "System Groups"; // Set the page title for the header
require_once __DIR__ . '/../includes/header.php';

// Enforce permissions
enforceAnyPermission(['view_groups']);
// If execution continues, permissions are granted.

// Function to format date to dd/mm/yyyy
function formatDateToDDMMYYYY($dateStr) {
    if (empty($dateStr)) return '';
    $date = new DateTime($dateStr);
    return $date->format('d/m/Y');
}

$sql = "
  SELECT
    GroupID      AS id,
    GroupName    AS name,
    Description  AS description,
    StartDate    AS start_date,
    EndDate      AS end_date,
    Room         AS room_number
  FROM `Groups`
  ORDER BY GroupName
";
$res    = $conn->query($sql);
$groups = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

// Format dates for display
foreach ($groups as &$group) {
    $group['start_date'] = formatDateToDDMMYYYY($group['start_date']);
    $group['end_date'] = formatDateToDDMMYYYY($group['end_date']);
}
?>

<!-- Content wrapper for the page -->
<div class="container-xxl flex-grow-1 container-p-y pt-0">
  
  <div class="action-card mb-4">
    <div class="action-card-header d-flex justify-content-between align-items-center">
      <h5 class="action-card-title">All Groups</h5>
      <div class="d-flex align-items-center">
        <div class="input-group me-2" style="width: 250px;">
          <span class="input-group-text"><i class="bx bx-search"></i></span>
          <input type="text" id="tableSearch" class="form-control" placeholder="Search groups...">
        </div>
        <button type="button" class="btn btn-primary" onclick="document.getElementById('addGroupModal').__x.$data.open = true;">
            <i class="bx bx-plus me-1"></i> Add New Group
        </button>
      </div>
    </div>
    <div class="action-card-content p-0">
      <div class="table-responsive">
        <table id="groupsTable" class="table table-striped table-hover align-middle">
          <thead>
            <tr>
              <th data-sort="name">Name <i class="bx bx-sort-alt-2 text-muted"></i></th>
              <th data-sort="description">Description</th>
              <th data-sort="start_date" class="date-sort">Start Date <i class="bx bx-sort-alt-2 text-muted"></i></th>
              <th data-sort="end_date" class="date-sort">End Date <i class="bx bx-sort-alt-2 text-muted"></i></th>
              <th data-sort="room_number">Room <i class="bx bx-sort-alt-2 text-muted"></i></th>
              <th style="width: 150px">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($groups as $g): ?>
              <tr>
                <td><?= htmlspecialchars($g['name']) ?></td>
                <td><?= nl2br(htmlspecialchars($g['description'])) ?></td>
                <td><?= htmlspecialchars($g['start_date']) ?></td>
                <td><?= htmlspecialchars($g['end_date']) ?></td>
                <td><?= htmlspecialchars($g['room_number']) ?></td>
                <td>
                  <div class="d-flex">
                    <button
                      class="btn btn-primary edit-group-btn me-2"
                      onclick="document.getElementById('groupDetailModal').__x.$data.open = true; fetchGroupDetails(<?= $g['id'] ?>);"
                    ><i class="bx bx-edit-alt me-1"></i> Edit</button>
                    <a
                      href="../delete_group.php?id=<?= $g['id'] ?>"
                      class="btn btn-danger"
                      onclick="return confirm('Are you sure you want to delete the group: ' + <?= json_encode($g['name']) ?> + '? This action cannot be undone.');"
                    ><i class="bx bx-trash me-1"></i> Delete</a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Edit Group Modal -->
<div x-data="{ open: false }" id="groupDetailModal" 
    x-show="open" 
    class="fixed inset-0 z-50 overflow-y-auto" 
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0">
    
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50" @click="open = false"></div>
    
    <!-- Modal content -->
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-xl mx-auto overflow-hidden">
            <!-- Header -->
            <div class="modal-header bg-primary text-white px-6 py-4 flex justify-between items-center">
                <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Edit Group</h5>
                <button type="button" class="btn-close btn-close-white" @click="open = false" aria-label="Close"></button>
            </div>
            <div class="modal-body p-6">
                <div id="groupDetailContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Loading group data...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-gray-50 px-6 py-3 flex justify-end space-x-2">
                <button type="button" class="btn btn-secondary" @click="open = false">
                    <i class="bx bx-x me-1"></i>Cancel
                </button>
                <button id="saveGroupBtn" type="button" class="btn btn-primary">
                    <i class="bx bx-save me-1"></i>Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Group Modal -->
<div x-data="{ open: false }" id="addGroupModal" 
    x-show="open" 
    class="fixed inset-0 z-50 overflow-y-auto" 
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0">
    
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50" @click="open = false"></div>
    
    <!-- Modal content -->
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-auto overflow-hidden">
            <!-- Header -->
            <div class="modal-header bg-primary text-white px-6 py-4 flex justify-between items-center">
                <h5 class="modal-title"><i class="bx bx-plus-circle me-2"></i>Add New Group</h5>
                <button type="button" class="btn-close btn-close-white" @click="open = false" aria-label="Close"></button>
            </div>
            <form id="addGroupForm" action="add_group.php" method="post">
                <div class="modal-body p-6">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Group Name <span class="text-danger">*</span></label>
                            <input type="text" name="group_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Room Number</label>
                            <input type="text" name="room_number" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-gray-50 px-6 py-3 flex justify-end space-x-2">
                    <button type="button" class="btn btn-secondary" @click="open = false">
                        <i class="bx bx-x me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-check me-1"></i>Create Group
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<script src="../assets/js/groupModal.js"></script>

<script>
// Function to fetch group details for the edit modal
function fetchGroupDetails(groupId) {
    const groupDetailContent = document.getElementById('groupDetailContent');
    groupDetailContent.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading group data...</p>
        </div>
    `; // Show loading spinner

    fetch(`get_group.php?id=${groupId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            groupDetailContent.innerHTML = html;
            // Re-execute scripts within the loaded content (if any)
            const scripts = groupDetailContent.querySelectorAll('script');
            scripts.forEach(script => {
                const newScript = document.createElement('script');
                Array.from(script.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                newScript.appendChild(document.createTextNode(script.innerHTML));
                script.parentNode.replaceChild(newScript, script);
            });
        })
        .catch(error => {
            console.error('Error loading group details:', error);
            groupDetailContent.innerHTML = `<p class="text-danger">Failed to load group details: ${error.message}</p>`;
        });
}

document.addEventListener('DOMContentLoaded', function() {
  // Table search functionality
  const searchInput = document.getElementById('tableSearch');
  const table = document.getElementById('groupsTable');
  const rows = table.querySelectorAll('tbody tr');

  searchInput.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase().trim();
    
    rows.forEach(row => {
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
  });

  // Sorting functionality
  // Make sure table headers are readable in dark mode
  const darkModeHandler = () => {
    if (document.body.classList.contains('theme-dark')) {
      document.querySelectorAll('th').forEach(th => {
        th.style.color = '#ffffff';
      });
    } else {
      document.querySelectorAll('th').forEach(th => {
        th.style.color = '';
      });
    }
  };
  
  // Run initially and add observer for theme changes
  darkModeHandler();
  const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
      if (mutation.attributeName === 'class') {
        darkModeHandler();
      }
    });
  });
  observer.observe(document.body, { attributes: true });

  const headers = table.querySelectorAll('th[data-sort]');
  
  headers.forEach(header => {
    header.style.cursor = 'pointer'; // Add pointer cursor to sortable columns
    header.addEventListener('click', function() {
      const sortKey = this.dataset.sort;
      const isDateSort = this.classList.contains('date-sort');
      const isAscending = this.classList.contains('sort-asc');
      
      // Reset all headers
      headers.forEach(h => {
        h.classList.remove('sort-asc', 'sort-desc');
        h.querySelector('i').className = 'bx bx-sort-alt-2 text-muted';
      });
      
      // Set new sort direction
      if (isAscending) {
        this.classList.add('sort-desc');
        this.querySelector('i').className = 'bx bx-sort-down text-primary';
      } else {
        this.classList.add('sort-asc');
        this.querySelector('i').className = 'bx bx-sort-up text-primary';
      }
      
      // Get rows as array for sorting
      const rowsArray = Array.from(rows);
      
      // Sort rows
      rowsArray.sort((a, b) => {
        let aValue = a.children[Array.from(headers).indexOf(this)].textContent.trim();
        let bValue = b.children[Array.from(headers).indexOf(this)].textContent.trim();
        
        if (isDateSort) {
          // Convert DD/MM/YYYY to sortable format
          const parseDate = dateStr => {
            if (!dateStr) return 0;
            const parts = dateStr.split('/');
            return new Date(parts[2], parts[1] - 1, parts[0]).getTime();
          };
          
          aValue = parseDate(aValue);
          bValue = parseDate(bValue);
        }
        
        if (aValue < bValue) return isAscending ? -1 : 1;
        if (aValue > bValue) return isAscending ? 1 : -1;
        return 0;
      });
      
      // Remove all rows
      const tbody = table.querySelector('tbody');
      while (tbody.firstChild) {
        tbody.removeChild(tbody.firstChild);
      }
      
      // Append sorted rows
      rowsArray.forEach(row => tbody.appendChild(row));
    });
  });
});
</script>

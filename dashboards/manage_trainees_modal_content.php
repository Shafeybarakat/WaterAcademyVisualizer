<?php
// manage_trainees_modal_content.php
// This file will be loaded via AJAX into the modal body.

require_once "../includes/auth.php";
require_once "../includes/config.php";

// Check permissions
if (!isLoggedIn() || !hasPermission('manage_trainees')) {
    echo "<div class='alert alert-danger'>You do not have permission to manage trainees.</div>";
    exit;
}

// Fetch all groups for the dropdown filter
$groupsQuery = "SELECT GroupID, GroupName FROM `Groups` ORDER BY GroupName";
$groupsResult = $conn->query($groupsQuery);
$groups = [];
if ($groupsResult) {
    while ($row = $groupsResult->fetch_assoc()) {
        $groups[] = $row;
    }
} else {
    error_log("Error fetching groups: " . $conn->error);
}
?>

<?php
// manage_trainees_modal_content.php
// This file will be loaded via AJAX into the modal body.

require_once "../includes/auth.php";
require_once "../includes/config.php";

// Check permissions
if (!isLoggedIn() || !hasPermission('manage_trainees')) {
    echo "<div class='alert alert-danger'>You do not have permission to manage trainees.</div>";
    exit;
}

// Get group_id from GET parameters
$groupId = isset($_GET['group_id']) ? (int)$_GET['group_id'] : null;

// Fetch group name for display
$groupName = "Selected Group";
if ($groupId) {
    $groupNameQuery = "SELECT GroupName FROM `Groups` WHERE GroupID = ?";
    $stmt = $conn->prepare($groupNameQuery);
    if ($stmt) {
        $stmt->bind_param("i", $groupId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $groupName = htmlspecialchars($row['GroupName']);
        }
        $stmt->close();
    }
}
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <h5 class="mb-3">Trainees in Group: <span class="text-primary"><?= $groupName ?></span></h5>
        </div>
    </div>

    <div id="trainee_management_area">
        <div class="table-responsive">
            <table id="trainees_data_table" class="table table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Gov ID</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Trainee data will be loaded here via AJAX -->
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end mt-3">
            <button type="button" class="btn btn-success me-2" id="add_new_trainee_row">
                <i class="ri-user-add-line me-1"></i> Add New Trainee
            </button>
            <button type="button" class="btn btn-primary" id="save_trainees_btn">
                <i class="ri-save-line me-1"></i> Save Changes
            </button>
        </div>
    </div>

    <div id="modal_loader" class="d-flex justify-content-center align-items-center py-5" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    <div id="modal_message" class="alert mt-3" style="display: none;"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const traineeManagementArea = document.getElementById('trainee_management_area');
    const traineesDataTableBody = document.querySelector('#trainees_data_table tbody');
    const addNewTraineeRowBtn = document.getElementById('add_new_trainee_row');
    const saveTraineesBtn = document.getElementById('save_trainees_btn');
    const modalLoader = document.getElementById('modal_loader');
    const modalMessage = document.getElementById('modal_message');

    let currentTraineesData = []; // Store current trainees for tracking changes

    // Get group_id from URL
    const urlParams = new URLSearchParams(window.location.search);
    const groupId = urlParams.get('group_id');

    // Function to show loader
    function showLoader() {
        modalLoader.style.display = 'flex';
        traineeManagementArea.style.display = 'none';
        modalMessage.style.display = 'none';
    }

    // Function to hide loader
    function hideLoader() {
        modalLoader.style.display = 'none';
        traineeManagementArea.style.display = 'block';
    }

    // Function to display messages
    function displayMessage(message, type = 'info') {
        modalMessage.textContent = message;
        modalMessage.className = `alert mt-3 alert-${type}`;
        modalMessage.style.display = 'block';
    }

    // Function to fetch and render trainees
    function fetchAndRenderTrainees(currentGroupId) {
        console.log('fetchAndRenderTrainees called with Group ID:', currentGroupId);
        if (currentGroupId) {
            showLoader();
            fetch(`api/trainee_management_api.php?action=get_trainees_by_group&group_id=${currentGroupId}`)
                .then(response => {
                    console.log('Response received from get_trainees_by_group:', response);
                    if (!response.ok) {
                        console.error('Network response not ok:', response.status, response.statusText);
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Data received from get_trainees_by_group:', data);
                    hideLoader();
                    if (data.success) {
                        currentTraineesData = data.trainees.map(t => ({ ...t, isNew: false, isModified: false }));
                        renderTraineeTable(currentTraineesData);
                        displayMessage(`Loaded ${data.trainees.length} trainees for this group.`, 'success');
                    } else {
                        renderTraineeTable([]); // Clear table
                        displayMessage(data.error || 'Failed to load trainees.', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error during fetch or data processing:', error);
                    hideLoader();
                    displayMessage('An error occurred while fetching trainees.', 'danger');
                });
        } else {
            traineeManagementArea.style.display = 'none';
            traineesDataTableBody.innerHTML = '';
            currentTraineesData = [];
            displayMessage('No group selected. Please select a group from the main page.', 'info');
        }
    }

    // Initial load of trainees based on passed group_id
    if (groupId) {
        fetchAndRenderTrainees(groupId);
    } else {
        traineeManagementArea.style.display = 'none';
        displayMessage('No group selected. Please select a group from the main page.', 'info');
    }

    // Render trainee table
    function renderTraineeTable(trainees) {
        traineesDataTableBody.innerHTML = '';
        if (trainees.length === 0) {
            traineesDataTableBody.innerHTML = '<tr><td colspan="7" class="text-center">No trainees found for this group. Click "Add New Trainee" to add one.</td></tr>';
            return;
        }

        // Limit to 20 rows
        const traineesToDisplay = trainees.slice(0, 20);

        traineesToDisplay.forEach((trainee, index) => {
            const row = traineesDataTableBody.insertRow();
            row.dataset.traineeId = trainee.TID || 'new';
            row.dataset.isNew = trainee.isNew;
            row.dataset.isModified = trainee.isModified;

            // Add banded row class
            if (index % 2 === 1) {
                row.classList.add('table-striped'); // Assuming Bootstrap's table-striped works on tr
            }

            row.innerHTML = `
                <td>${trainee.TID || 'New'}</td>
                <td><input type="text" class="form-control form-control-sm" value="${trainee.FirstName || ''}" data-field="FirstName" required></td>
                <td><input type="text" class="form-control form-control-sm" value="${trainee.LastName || ''}" data-field="LastName" required></td>
                <td><input type="text" class="form-control form-control-sm" value="${trainee.GovID || ''}" data-field="GovID"></td>
                <td><input type="text" class="form-control form-control-sm" value="${trainee.PhoneNumber || ''}" data-field="PhoneNumber"></td>
                <td><input type="email" class="form-control form-control-sm" value="${trainee.Email || ''}" data-field="Email" required></td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm delete-trainee-row">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </td>
            `;

            // Add event listeners for input changes to mark as modified
            row.querySelectorAll('input').forEach(input => {
                input.addEventListener('input', () => {
                    row.dataset.isModified = true;
                });
            });
        });
    }

    // Add new trainee row
    addNewTraineeRowBtn.addEventListener('click', function() {
        const newTrainee = {
            TID: 'new',
            FirstName: '',
            LastName: '',
            GovID: '',
            PhoneNumber: '',
            Email: '',
            isNew: true,
            isModified: true
        };
        currentTraineesData.push(newTrainee);
        renderTraineeTable(currentTraineesData);
    });

    // Delete trainee row
    traineesDataTableBody.addEventListener('click', function(event) {
        if (event.target.closest('.delete-trainee-row')) {
            const row = event.target.closest('tr');
            const traineeId = row.dataset.traineeId;

            if (traineeId === 'new') {
                // If it's a new unsaved row, just remove it from the array and DOM
                currentTraineesData = currentTraineesData.filter(t => t.TID !== 'new' || t !== currentTraineesData[Array.from(traineesDataTableBody.children).indexOf(row)]);
                row.remove();
                displayMessage('New trainee row removed.', 'info');
            } else {
                // For existing trainees, mark for deletion (or implement actual delete API call)
                if (confirm(`Are you sure you want to delete trainee ID ${traineeId}? This action cannot be undone.`)) {
                    // In a real application, you'd send a delete request to the server here.
                    // For this example, we'll just remove it from the UI.
                    currentTraineesData = currentTraineesData.filter(t => t.TID !== traineeId);
                    row.remove();
                    displayMessage(`Trainee ID ${traineeId} marked for deletion (removed from UI).`, 'warning');
                }
            }
            if (currentTraineesData.length === 0) {
                traineesDataTableBody.innerHTML = '<tr><td colspan="7" class="text-center">No trainees found for this group. Click "Add New Trainee" to add one.</td></tr>';
            }
        }
    });

    // Save changes
    saveTraineesBtn.addEventListener('click', function() {
        if (!groupId) {
            displayMessage('No group selected. Cannot save trainees.', 'danger');
            return;
        }

        const traineesToSave = [];
        traineesDataTableBody.querySelectorAll('tr').forEach(row => {
            const traineeId = row.dataset.traineeId;
            const isNew = row.dataset.isNew === 'true';
            const isModified = row.dataset.isModified === 'true';

            if (isNew || isModified) {
                const traineeData = {
                    TID: isNew ? null : traineeId,
                    GroupID: groupId, // Use the groupId from the URL
                    FirstName: row.querySelector('[data-field="FirstName"]').value,
                    LastName: row.querySelector('[data-field="LastName"]').value,
                    GovID: row.querySelector('[data-field="GovID"]').value,
                    PhoneNumber: row.querySelector('[data-field="PhoneNumber"]').value,
                    Email: row.querySelector('[data-field="Email"]').value,
                    isNew: isNew
                };
                traineesToSave.push(traineeData);
            }
        });

        if (traineesToSave.length === 0) {
            displayMessage('No changes to save.', 'info');
            return;
        }

        showLoader();
        fetch('api/trainee_management_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ action: 'save_trainees', trainees: traineesToSave })
        })
        .then(response => response.json())
        .then(data => {
            hideLoader();
            if (data.success) {
                displayMessage(data.message || 'Changes saved successfully!', 'success');
                // Re-fetch trainees to update IDs for new entries and clear modified flags
                fetchAndRenderTrainees(groupId); // Re-fetch with the same group ID
            } else {
                displayMessage(data.error || 'Failed to save changes.', 'danger');
            }
        })
        .catch(error => {
            hideLoader();
            console.error('Error saving trainees:', error);
            displayMessage('An error occurred while saving changes.', 'danger');
        });
    });

    // Initialize sorting for the table (similar to trainees.php)
    const traineesTable = document.getElementById('trainees_data_table');
    if (traineesTable) {
        const headers = traineesTable.querySelectorAll('th[data-sort]');
        headers.forEach(header => {
            header.addEventListener('click', function() {
                const sortKey = this.dataset.sort;
                const isAscending = this.classList.contains('sort-asc');
                
                // Reset all headers
                headers.forEach(h => {
                    h.classList.remove('sort-asc', 'sort-desc');
                    const icon = h.querySelector('i');
                    if (icon) icon.className = 'bx bx-sort-alt-2 text-muted';
                });
                
                // Set new sort direction
                if (isAscending) {
                    this.classList.add('sort-desc');
                    const icon = this.querySelector('i');
                    if (icon) icon.className = 'bx bx-sort-down text-primary';
                } else {
                    this.classList.add('sort-asc');
                    const icon = this.querySelector('i');
                    if (icon) icon.className = 'bx bx-sort-up text-primary';
                }
                
                // Get rows as array for sorting
                const rowsArray = Array.from(traineesDataTableBody.querySelectorAll('tr'));
                
                // Sort rows
                rowsArray.sort((a, b) => {
                    const aValue = a.children[Array.from(headers).indexOf(this)].querySelector('input') ? a.children[Array.from(headers).indexOf(this)].querySelector('input').value.trim() : a.children[Array.from(headers).indexOf(this)].textContent.trim();
                    const bValue = b.children[Array.from(headers).indexOf(this)].querySelector('input') ? b.children[Array.from(headers).indexOf(this)].querySelector('input').value.trim() : b.children[Array.from(headers).indexOf(this)].textContent.trim();
                    
                    if (sortKey === 'ID') { // Numeric sort for ID
                        return isAscending ? parseInt(aValue) - parseInt(bValue) : parseInt(bValue) - parseInt(aValue);
                    } else { // Alphabetical sort for others
                        return isAscending ? aValue.localeCompare(bValue) : bValue.localeCompare(aValue);
                    }
                });
                
                // Remove all rows
                while (traineesDataTableBody.firstChild) {
                    traineesDataTableBody.removeChild(traineesDataTableBody.firstChild);
                }
                
                // Append sorted rows
                rowsArray.forEach(row => traineesDataTableBody.appendChild(row));
            });
        });
    }
});
</script>

<?php
// Page title
$pageTitle = 'Home';

// Include header (which includes sidebar)
include_once '../includes/header.php';

// Get counts from database (dummy data for now)
$groupCount = 10;
$courseCount = 12;
$traineeCount = 25;
$instructorCount = 8;
$coordinatorCount = 5;
?>

<!-- Main content -->
<div class="container-xxl">
    <!-- Stats Cards -->
    <div class="dashboard-stats">
        <!-- Groups Card -->
        <div class="stat-card">
            <i class="bx bx-group stat-icon icon-groups"></i>
            <div class="stat-value"><?php echo $groupCount; ?></div>
            <div class="stat-label">GROUPS</div>
        </div>
        
        <!-- Courses Card -->
        <div class="stat-card">
            <i class="bx bx-book-open stat-icon icon-courses"></i>
            <div class="stat-value"><?php echo $courseCount; ?></div>
            <div class="stat-label">COURSES</div>
        </div>
        
        <!-- Trainees Card -->
        <div class="stat-card">
            <i class="bx bx-user-check stat-icon icon-trainees"></i>
            <div class="stat-value"><?php echo $traineeCount; ?></div>
            <div class="stat-label">TRAINEES</div>
        </div>
        
        <!-- Instructors Card -->
        <div class="stat-card">
            <i class="bx bx-user-voice stat-icon icon-instructors"></i>
            <div class="stat-value"><?php echo $instructorCount; ?></div>
            <div class="stat-label">INSTRUCTORS</div>
        </div>
        
        <!-- Coordinators Card -->
        <div class="stat-card">
            <i class="bx bx-user-pin stat-icon icon-coordinators"></i>
            <div class="stat-value"><?php echo $coordinatorCount; ?></div>
            <div class="stat-label">COORDINATORS</div>
        </div>
    </div>
    
    <!-- New Action Cards Section -->
    <div class="action-cards">
        <!-- New Group Wizard Card -->
        <div class="action-card">
            <div class="action-card-header">
                <i class="bx bx-plus-circle action-card-icon"></i>
                <h5 class="action-card-title">New Group Wizard</h5>
            </div>
            <div class="action-card-content">
                Create a new training group with our step-by-step wizard. Add trainees, assign courses, and set up schedules in a simple, guided process.
            </div>
            <div class="action-card-footer">
                <button type="button" class="btn btn-primary action-card-button" id="newGroupWizardBtn"
                        data-modal-url="dashboards/group_wizard.php">
                    <i class="bx bx-wand-magic"></i> Start Wizard
                </button>
            </div>
        </div>
        
<?php
// Fetch all groups for the dropdown filter on index.php
$groupsQueryIndex = "SELECT GroupID, GroupName FROM `Groups` ORDER BY GroupName";
$groupsResultIndex = $conn->query($groupsQueryIndex);
$groupsIndex = [];
if ($groupsResultIndex) {
    while ($row = $groupsResultIndex->fetch_assoc()) {
        $groupsIndex[] = $row;
    }
} else {
    error_log("Error fetching groups for index.php: " . $conn->error);
}
?>
        <!-- Add/Delete Trainees Card -->
        <div class="action-card">
            <div class="action-card-header">
                <i class="bx bx-user-plus action-card-icon"></i>
                <h5 class="action-card-title">Add / Delete Trainees</h5>
            </div>
            <div class="action-card-content">
                Manage your trainee roster by adding new trainees or removing existing ones. You can also import multiple trainees using our bulk import tool.
            </div>
            <div class="select-group-container col-md-6 mx-auto"> <!-- Added col-md-6 and mx-auto for centering and width control -->
                <label for="index_group_filter" class="form-label fw-bold">Select Group</label>
                <div class="input-group">
                    <span class="input-group-text bg-primary text-white">
                        <i class="ri-team-line"></i>
                    </span>
                    <select id="index_group_filter" name="group_id" class="form-select"> <!-- Removed form-select-lg -->
                        <option value="">-- Select Group --</option>
                        <?php foreach($groupsIndex as $group): ?>
                            <option value="<?= $group['GroupID'] ?>"><?= htmlspecialchars($group['GroupName']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="action-card-footer">
                <button type="button" class="btn btn-primary action-card-button" id="manageTraineesBtn"
                        data-bs-toggle="modal" data-bs-target="#manageTraineesModal">
                    <i class="bx bx-user-plus"></i> Manage Trainees
                </button>
            </div>
        </div>
    </div>
    
    <!-- Upcoming Events Section -->
    <div class="card events-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">Upcoming Events</h5>
            <i class="bx bx-bell text-primary"></i>
        </div>
        <div class="card-body">
            <!-- Events Table -->
            <div class="table-responsive">
                <table class="table table-hover events-table">
                    <thead>
                        <tr>
                            <th>COURSE</th>
                            <th>GROUP</th>
                            <th>START DATE</th>
                            <th>DAYS</th>
                            <th>STATUS</th> <!-- New Status Column -->
                            <th class="text-center">ACTIONS</th> <!-- Center align actions -->
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Sample event row -->
                        <tr>
                            <td>Water Management Basics</td>
                            <td>Group A</td>
                            <td>June 1, 2025</td>
                            <td>5</td>
                            <td>Starting Soon</td> <!-- Dummy Status -->
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-primary event-details-btn" data-bs-toggle="modal" data-bs-target="#eventDetailsModal" data-event-type="course_instance_starting" data-event-id="1">View</button>
                            </td>
                        </tr>
                        <tr>
                            <td>Advanced Irrigation Techniques</td>
                            <td>Group B</td>
                            <td>June 5, 2025</td>
                            <td>7</td>
                            <td>Starting Soon</td> <!-- Dummy Status -->
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-primary event-details-btn" data-bs-toggle="modal" data-bs-target="#eventDetailsModal" data-event-type="course_instance_starting" data-event-id="2">View</button>
                            </td>
                        </tr>
                        <tr>
                            <td>Water Quality Assessment</td>
                            <td>Group C</td>
                            <td>June 10, 2025</td>
                            <td>3</td>
                            <td>Starting Soon</td> <!-- Dummy Status -->
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-primary event-details-btn" data-bs-toggle="modal" data-bs-target="#eventDetailsModal" data-event-type="course_instance_starting" data-event-id="3">View</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventDetailsModal" tabindex="-1" aria-labelledby="eventDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="eventDetailsModalLabel">Event Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="eventDetailsLoading" class="d-flex justify-content-center align-items-center" style="min-height: 150px;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div id="eventDetailsError" class="alert alert-danger d-none" role="alert">
                    <i class="bx bx-error-circle me-2"></i>
                    <span id="eventDetailsErrorMessage"></span>
                </div>
                <div id="eventDetailsContent" class="d-none">
                    <!-- Event details will be loaded here via AJAX -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-info d-none" id="sendEmailBtn">
                    <i class="bx bx-envelope"></i> Send Email
                </button>
                <button type="button" class="btn btn-warning d-none" id="extendDateBtn">
                    <i class="bx bx-calendar-plus"></i> Extend Date
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Date Extension Modal -->
<div class="modal fade" id="dateExtensionModal" tabindex="-1" aria-labelledby="dateExtensionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="dateExtensionModalLabel">Extend Course End Date</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="dateExtensionForm">
                    <input type="hidden" id="extensionEventId" name="event_id">
                    <input type="hidden" id="extensionEventType" name="event_type">
                    <div class="mb-3">
                        <label for="currentEndDate" class="form-label">Current End Date</label>
                        <input type="text" class="form-control" id="currentEndDate" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="newEndDate" class="form-label">New End Date</label>
                        <input type="date" class="form-control" id="newEndDate" name="new_end_date" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="saveExtensionBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Action Buttons -->
<?php
// Include footer
include_once '../includes/footer.php';
?>

<!-- Manage Trainees Modal -->
<div class="modal fade" id="manageTraineesModal" tabindex="-1" aria-labelledby="manageTraineesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="manageTraineesModalLabel">
                    <i class="bx bx-user-plus me-2"></i> Manage Trainees
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Content will be loaded here via AJAX from manage_trainees_modal_content.php -->
                <div class="d-flex justify-content-center align-items-center" style="min-height: 200px;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="../assets/dashjs/upcoming-events.js"></script> <!-- Include the moved JS file -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // New Group Wizard Button Handler
    const newGroupWizardBtn = document.getElementById('newGroupWizardBtn');
    const groupWizardModalEl = document.getElementById('groupWizardModal');
    const groupWizardModalBody = groupWizardModalEl ? groupWizardModalEl.querySelector('.modal-body') : null;

    // Ensure jQuery is loaded before attempting to use it
    if (typeof jQuery !== 'undefined' && newGroupWizardBtn && groupWizardModalEl && groupWizardModalBody) {
        $(newGroupWizardBtn).on('click', function() {
            // Show loading spinner immediately
            groupWizardModalBody.innerHTML = `
                <div class="d-flex justify-content-center align-items-center" style="min-height: 200px;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            
            // Show the modal using jQuery API
            $(groupWizardModalEl).modal('show');

            // Load content via AJAX
            fetch('<?php echo $baseLinkPath; ?>dashboards/group_wizard.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(html => {
                    groupWizardModalBody.innerHTML = html;
                    // Re-execute scripts within the loaded content (if any)
                    const scripts = groupWizardModalBody.querySelectorAll('script');
                    scripts.forEach(script => {
                        const newScript = document.createElement('script');
                        Array.from(script.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                        newScript.appendChild(document.createTextNode(script.innerHTML));
                        script.parentNode.replaceChild(newScript, script);
                    });

                    // Initialize the wizard after content is loaded and rendered
                    if (typeof initGroupWizard === 'function') {
                        initGroupWizard();
                    } else {
                        console.error('initGroupWizard function not found. Ensure assets/dashjs/group_wizard.js is loaded and exposes initGroupWizard.');
                    }
                })
                .catch(error => {
                    console.error('Error loading group wizard content:', error);
                    groupWizardModalBody.innerHTML = `<p class="text-danger">Failed to load wizard content: ${error.message}</p>`;
                });
        });
    }

    // Manage Trainees Button Handler
    const manageTraineesBtn = document.getElementById('manageTraineesBtn');
    const indexGroupFilter = document.getElementById('index_group_filter');
    const manageTraineesModalEl = document.getElementById('manageTraineesModal');
    const manageTraineesModalBody = manageTraineesModalEl ? manageTraineesModalEl.querySelector('.modal-body') : null;

    // Ensure jQuery is loaded before attempting to use it
    if (typeof jQuery !== 'undefined' && manageTraineesBtn && indexGroupFilter && manageTraineesModalEl && manageTraineesModalBody) {
        // Attach a click listener to the button that triggers the modal
        $(manageTraineesBtn).on('click', function(event) {
            const selectedGroupId = indexGroupFilter.value;
            if (!selectedGroupId) {
                alert('Please select a group before managing trainees.');
                event.stopPropagation(); // Prevent modal from opening
                return;
            }

            // Show loading spinner immediately
            manageTraineesModalBody.innerHTML = `
                <div class="d-flex justify-content-center align-items-center" style="min-height: 200px;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            
            // Show the modal using jQuery API
            $(manageTraineesModalEl).modal('show');

            // Load content via AJAX, passing the selected group ID
            console.log('Fetching modal content for group ID:', selectedGroupId);
            fetch(`<?php echo $baseLinkPath; ?>dashboards/manage_trainees_modal_content.php?group_id=${selectedGroupId}`)
                .then(response => {
                    console.log('Received response from manage_trainees_modal_content.php:', response);
                    if (!response.ok) {
                        console.error('Network response not ok:', response.status, response.statusText);
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(html => {
                    console.log('Modal content HTML loaded successfully.');
                    manageTraineesModalBody.innerHTML = html;
                    // Re-execute scripts within the loaded content
                    const scripts = manageTraineesModalBody.querySelectorAll('script');
                    scripts.forEach(script => {
                        const newScript = document.createElement('script');
                        Array.from(script.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                        newScript.appendChild(document.createTextNode(script.innerHTML));
                        script.parentNode.replaceChild(newScript, script);
                    });
                    console.log('Scripts re-executed in modal content.');
                })
                .catch(error => {
                    console.error('Error loading manage trainees modal content:', error);
                    manageTraineesModalBody.innerHTML = `<p class="text-danger">Failed to load content: ${error.message}</p>`;
                });
        });
    }
});
</script>

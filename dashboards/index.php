<?php
// Page title
$pageTitle = 'Home';

// Include header (which includes sidebar)
include_once '../includes/header.php';

// RBAC guard: Only users with 'access_dashboard' permission can access this page.
if (!require_permission('access_dashboard', '../login.php')) {
    echo '<div class="container-xxl flex-grow-1 container-p-y"><div class="alert alert-danger" role="alert">' . ($_SESSION['access_denied_message'] ?? 'You do not have permission to access this page.') . '</div></div>';
    include_once "../includes/footer.php"; // Ensure footer is included
    die(); // Terminate script
}

// Get counts from database (dummy data for now)
$groupCount = 10;
$courseCount = 12;
$traineeCount = 25;
$instructorCount = 8;
$coordinatorCount = 5;

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

// Dummy data for Upcoming Events (as per original index.php)
$upcomingEvents = [
    ['Water Management Basics', 'Group A', 'June 1, 2025', '5', 'Starting Soon', '1'],
    ['Advanced Irrigation Techniques', 'Group B', 'June 5, 2025', '7', 'Starting Soon', '2'],
    ['Water Quality Assessment', 'Group C', 'June 10, 2025', '3', 'Starting Soon', '3'],
];

?>

<main class="p-6 bg-gray-50 dark:bg-gray-900 flex-1">
    <!-- Stats Cards Row -->
    <div class="dashboard-stats">
        <?php
        // KPI Cards - adapting from kpi-card.php and user description
        // Note: These are not actual charts, just styled cards with values.
        // The kpi-card.php component expects chartId, but for these simple stats, we can pass empty string or adapt the component.
        // For now, I'll create the HTML directly based on the user's description and the old index.php structure.
        ?>
        <div class="stat-card">
            <i class="bx bx-group text-5xl text-blue-500 mb-3 stat-icon"></i>
            <div class="stat-value"><?= $groupCount; ?></div>
            <div class="stat-label">GROUPS</div>
        </div>
        <div class="stat-card">
            <i class="bx bx-book-open text-5xl text-green-500 mb-3 stat-icon"></i>
            <div class="stat-value"><?= $courseCount; ?></div>
            <div class="stat-label">COURSES</div>
        </div>
        <div class="stat-card">
            <i class="bx bx-user-check text-5xl text-yellow-500 mb-3 stat-icon"></i>
            <div class="stat-value"><?= $traineeCount; ?></div>
            <div class="stat-label">TRAINEES</div>
        </div>
        <div class="stat-card">
            <i class="bx bx-user-voice text-5xl text-red-500 mb-3 stat-icon"></i>
            <div class="stat-value"><?= $instructorCount; ?></div>
            <div class="stat-label">INSTRUCTORS</div>
        </div>
        <div class="stat-card">
            <i class="bx bx-user-pin text-5xl text-purple-500 mb-3 stat-icon"></i>
            <div class="stat-value"><?= $coordinatorCount; ?></div>
            <div class="stat-label">COORDINATORS</div>
        </div>
    </div>

    <!-- Action Cards Section -->
    <div class="action-cards">
        <!-- New Group Wizard Card -->
        <div class="action-card">
            <div class="action-card-header">
                <i class="bx bx-plus-circle text-3xl text-blue-600 action-card-icon"></i>
                <h5 class="action-card-title">New Group Wizard</h5>
            </div>
            <div class="action-card-content">
                Create a new training group with our step-by-step wizard. Add trainees, assign courses, and set up schedules in a simple, guided process.
            </div>
            <div class="flex justify-center">
                <button type="button" class="action-card-button shining-btn w-4/5" id="newGroupWizardBtn"
                        @click="document.getElementById('groupWizardModal').__x.$data.open = true; fetch('<?= BASE_URL ?>dashboards/group_wizard.php').then(response => response.text()).then(html => { document.getElementById('groupWizardModal').querySelector('.modal-body').innerHTML = html; });">
                    <i class="bx bx-wand-magic mr-2"></i> Start Wizard
                </button>
            </div>
        </div>
        
        <!-- Add/Delete Trainees Card -->
        <div class="action-card">
            <div class="action-card-header">
                <i class="bx bx-user-plus text-3xl text-green-600 action-card-icon"></i>
                <h5 class="action-card-title">Add / Delete Trainees</h5>
            </div>
            <div class="action-card-content">
                Manage your trainee roster by adding new trainees or removing existing ones. You can also import multiple trainees using our bulk import tool.
            </div>
            <div class="flex-grow"></div>
            <div class="flex justify-center mb-8">
                <div class="w-4/5">
                    <label for="index_group_filter" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Select Group</label>
                    <select id="index_group_filter" name="group_id" class="block w-full pl-3 pr-10 py-4 text-base border border-gray-300 dark:border-gray-700 focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100 font-medium sm:text-sm rounded-md">
                        <option value="" class="py-2 bg-gray-50 dark:bg-gray-800">-- Select Group --</option>
                        <?php foreach($groupsIndex as $group): ?>
                            <option value="<?= $group['GroupID'] ?>" class="py-2 bg-gray-50 hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700"><?= htmlspecialchars($group['GroupName']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="flex justify-center">
                <button type="button" class="action-card-button shining-btn w-4/5" id="manageTraineesBtn"
                        @click="const selectedGroupId = document.getElementById('index_group_filter').value; if (!selectedGroupId) { alert('Please select a group before managing trainees.'); return; } document.getElementById('manageTraineesModal').__x.$data.open = true; fetch('<?= BASE_URL ?>dashboards/manage_trainees_modal_content.php?group_id=' + selectedGroupId).then(response => response.text()).then(html => { document.getElementById('manageTraineesModal').querySelector('.modal-body').innerHTML = html; });">
                    <i class="bx bx-user-plus mr-2"></i> Manage Trainees
                </button>
            </div>
        </div>
    </div>
    
    <!-- Upcoming Events Section -->
    <div class="action-card mt-8">
        <div class="action-card-header">
            <i class="bx bx-calendar-event text-3xl text-blue-600 action-card-icon"></i>
            <h5 class="action-card-title">Upcoming Events</h5>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="data-table min-w-full">
                    <thead>
                        <tr>
                            <th class="sortable" data-sort="course">COURSE <i class="bx bx-sort-alt-2 ml-1"></i></th>
                            <th class="sortable" data-sort="group">GROUP <i class="bx bx-sort-alt-2 ml-1"></i></th>
                            <th class="sortable" data-sort="start-date">START DATE <i class="bx bx-sort-alt-2 ml-1"></i></th>
                            <th class="sortable" data-sort="days">DAYS <i class="bx bx-sort-alt-2 ml-1"></i></th>
                            <th class="sortable" data-sort="status">STATUS <i class="bx bx-sort-alt-2 ml-1"></i></th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($upcomingEvents as $index => $event): ?>
                        <tr class="<?= $index % 2 === 0 ? 'even-row' : 'odd-row' ?>">
                            <td><?= htmlspecialchars($event[0]) ?></td>
                            <td><?= htmlspecialchars($event[1]) ?></td>
                            <td><?= htmlspecialchars($event[2]) ?></td>
                            <td><?= htmlspecialchars($event[3]) ?></td>
                            <td>
                                <span class="status-badge status-upcoming"><?= htmlspecialchars($event[4]) ?></span>
                            </td>
                            <td class="text-center">
                                <button type="button" class="action-button" @click="document.getElementById('eventDetailsModal').__x.$data.open = true; document.getElementById('eventDetailsModal').__x.$data.event = {id: '<?= htmlspecialchars($event[5]) ?>', type: 'course_instance_starting'}">
                                    <i class="bx bx-show-alt mr-1"></i> View
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Group Wizard Modal (Alpine.js) -->
<div x-data="{ open: false }" id="groupWizardModal" 
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
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl mx-auto overflow-hidden">
            <!-- Header -->
            <div class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center">
                <h5 class="text-xl font-medium" id="groupWizardModalLabel">New Group Wizard</h5>
                <button type="button" @click="open = false" class="text-white hover:text-gray-200">
                    <i class="bx bx-x text-2xl"></i>
                </button>
            </div>
            
            <!-- Body -->
            <div class="p-6 modal-body">
                <!-- Content will be loaded here via AJAX -->
                <div class="flex justify-center items-center min-h-[200px]">
                    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-3 flex justify-end">
                <button type="button" @click="open = false" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Event Details Modal (Alpine.js) -->
<div x-data="{ open: false, event: null }" id="eventDetailsModal" 
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
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl mx-auto overflow-hidden">
            <!-- Header -->
            <div class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center">
                <h5 class="text-xl font-medium" id="eventDetailsModalLabel">Event Details</h5>
                <button type="button" @click="open = false" class="text-white hover:text-gray-200">
                    <i class="bx bx-x text-2xl"></i>
                </button>
            </div>
            
            <!-- Body -->
            <div class="p-6">
                <!-- Loading spinner -->
                <div id="eventDetailsLoading" class="flex justify-center items-center min-h-[150px]">
                    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
                </div>
                
                <!-- Error message -->
                <div id="eventDetailsError" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="alert">
                    <div class="flex items-center">
                        <i class="bx bx-error-circle mr-2 text-xl"></i>
                        <span id="eventDetailsErrorMessage"></span>
                    </div>
                </div>
                
                <!-- Content area -->
                <div id="eventDetailsContent" class="hidden">
                    <!-- Event details will be loaded here via AJAX -->
                </div>
            </div>
            
            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-3 flex justify-end space-x-2">
                <button type="button" @click="open = false" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                    Close
                </button>
                <button type="button" id="sendEmailBtn" class="hidden px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    <i class="bx bx-envelope mr-1"></i> Send Email
                </button>
                <button type="button" id="extendDateBtn" class="hidden px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                    <i class="bx bx-calendar-plus mr-1"></i> Extend Date
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Date Extension Modal (Alpine.js) -->
<div x-data="{ open: false }" id="dateExtensionModal" 
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
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-auto overflow-hidden">
            <!-- Header -->
            <div class="bg-yellow-500 text-white px-6 py-4 flex justify-between items-center">
                <h5 class="text-xl font-medium" id="dateExtensionModalLabel">Extend Course End Date</h5>
                <button type="button" @click="open = false" class="text-white hover:text-gray-200">
                    <i class="bx bx-x text-2xl"></i>
                </button>
            </div>
            
            <!-- Body -->
            <div class="p-6">
                <form id="dateExtensionForm">
                    <input type="hidden" id="extensionEventId" name="event_id">
                    <input type="hidden" id="extensionEventType" name="event_type">
                    
                    <div class="mb-4">
                        <label for="currentEndDate" class="block text-sm font-medium text-gray-700 mb-1">Current End Date</label>
                        <input type="text" id="currentEndDate" class="bg-gray-100 w-full px-3 py-2 border border-gray-300 rounded-md" readonly>
                    </div>
                    
                    <div class="mb-4">
                        <label for="newEndDate" class="block text-sm font-medium text-gray-700 mb-1">New End Date</label>
                        <input type="date" id="newEndDate" name="new_end_date" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500" required>
                    </div>
                </form>
            </div>
            
            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-3 flex justify-end space-x-2">
                <button type="button" @click="open = false" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                    Cancel
                </button>
                <button type="button" id="saveExtensionBtn" class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Manage Trainees Modal (Alpine.js) -->
<div x-data="{ open: false }" id="manageTraineesModal" 
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
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl mx-auto overflow-hidden">
            <!-- Header -->
            <div class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center">
                <h5 class="text-xl font-medium" id="manageTraineesModalLabel">
                    <i class="bx bx-user-plus mr-2"></i> Manage Trainees
                </h5>
                <button type="button" @click="open = false" class="text-white hover:text-gray-200">
                    <i class="bx bx-x text-2xl"></i>
                </button>
            </div>
            
            <!-- Body -->
            <div class="p-6">
                <!-- Content will be loaded here via AJAX from manage_trainees_modal_content.php -->
                <div class="flex justify-center items-center min-h-[200px]">
                    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-3 flex justify-end">
                <button type="button" @click="open = false" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                    Close
                </button>
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
            fetch('<?= BASE_URL ?>dashboards/group_wizard.php')
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
            fetch(`<?= BASE_URL ?>dashboards/manage_trainees_modal_content.php?group_id=${selectedGroupId}`)
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

<?php
// Include footer
include_once '../includes/footer.php';
?>

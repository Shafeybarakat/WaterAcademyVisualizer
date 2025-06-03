<?php
$pageTitle = "Group Performance Report"; // Set page title
include_once("../includes/config.php");
include_once("../includes/auth.php"); // Ensure user is logged in and sets $_SESSION["user_id"], $_SESSION["role"]

// Define allowed roles for this report
$allowed_roles = ["Super Admin", "Admin", "Instructor", "Coordinator"];
if (!isset($_SESSION["user_role"]) || !in_array($_SESSION["user_role"], $allowed_roles)) {
    // Redirect to dashboard with error message instead of login page
    // This prevents redirect loop when user is logged in but doesn't have the required role
    $_SESSION['access_denied_message'] = 'You do not have permission to access the Group Performance Report. Required roles: Super Admin, Admin, Instructor, or Coordinator.';
    header("Location: index.php?error=permission_denied");
    exit();
}

// Include the header - this also includes the sidebar
include_once("../includes/header.php"); 

$user_role = $_SESSION["user_role"];
$user_id = $_SESSION["user_id"]; // Assuming user ID is stored in session

// --- Fetch initial data for filters (Groups) ---
$group_sql = "SELECT GroupID, GroupName FROM Groups";
$group_params = [];
$group_types = "";

// Apply filtering for Coordinators
if ($user_role === "Coordinator") {
    $group_sql .= " WHERE CoordinatorID = ?";
    $group_params[] = $user_id;
    $group_types .= "i";
} elseif ($user_role === "Instructor") {
    // Instructors see groups they teach in
    $group_sql = "SELECT DISTINCT g.GroupID, g.GroupName 
                  FROM Groups g
                  JOIN GroupCourses gc ON g.GroupID = gc.GroupID 
                  WHERE gc.InstructorID = ?";
    $group_params[] = $user_id;
    $group_types .= "i";
}

$group_sql .= " ORDER BY GroupName";

$group_stmt = $conn->prepare($group_sql);
$groups = [];
if ($group_stmt) {
    if (!empty($group_params)) {
        $group_stmt->bind_param($group_types, ...$group_params);
    }
    $group_stmt->execute();
    $group_result = $group_stmt->get_result();
    $groups = $group_result->fetch_all(MYSQLI_ASSOC);
    $group_stmt->close();
} else {
    error_log("Error preparing group statement: " . $conn->error);
    // Handle error appropriately
}

?>

<!-- Include Google Fonts for Canva-like styling -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<!-- Include Chart.js BEFORE the page content to ensure it's loaded -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<!-- Content specific to this page -->
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Back to Reports Button -->
    <div class="mb-4">
        <a href="reports.php?group_id=<?= $_GET['group_id'] ?? '' ?>&course_id=<?= $_GET['course_id'] ?? '' ?>" class="btn btn-primary" style="font-weight: bold; padding: 10px 20px; font-size: 16px;">
            <i class="bx bx-arrow-back me-1"></i> Back to Reports
        </a>
    </div>

    <!-- Report Actions -->
    <div class="card mb-4">
        <h5 class="card-header">Report Actions</h5>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <button id="printReportBtn" class="btn btn-outline-primary w-100">
                        <i class="bx bx-printer me-1"></i> Print Report
                    </button>
                </div>
                <div class="col-md-6">
                    <button id="exportReportBtn" class="btn btn-outline-success w-100">
                        <i class="bx bx-export me-1"></i> Export Data
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- / Report Actions -->

    <!-- Report Content Area (Initially Hidden) -->
    <div id="reportContent" style="display: none;">
        <!-- Summary Cards -->
        <div style="display: flex; flex-direction: row; flex-wrap: wrap; margin-left: -0.75rem; margin-right: -0.75rem; margin-bottom: 1.5rem;">
            <div style="width: 25%; padding: 0 0.75rem; margin-bottom: 1rem;" class="d-flex">
                <div class="card" style="width: 100%;">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span>Trainees</span>
                                <div class="d-flex align-items-end mt-2">
                                    <h4 class="mb-0 me-2" id="summaryTraineeCount">0</h4>
                                </div>
                                <small>In selected group/course</small>
                            </div>
                            <span class="badge bg-label-primary rounded p-2">
                                <i class="bx bx-user bx-sm"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div style="width: 25%; padding: 0 0.75rem; margin-bottom: 1rem;" class="d-flex">
                <div class="card" style="width: 100%;">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span>Avg. Attendance</span>
                                <div class="d-flex align-items-end mt-2">
                                    <h4 class="mb-0 me-2" id="summaryAvgAttendance">0%</h4>
                                </div>
                                <small>Overall percentage</small>
                            </div>
                            <span class="badge bg-label-success rounded p-2">
                                <i class="bx bx-user-check bx-sm"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div style="width: 25%; padding: 0 0.75rem; margin-bottom: 1rem;" class="d-flex">
                <div class="card" style="width: 100%;">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span>Avg. Total Score</span>
                                <div class="d-flex align-items-end mt-2">
                                    <h4 class="mb-0 me-2" id="summaryAvgTotal">0</h4>
                                </div>
                                <small>Based on final grades</small>
                            </div>
                            <span class="badge bg-label-info rounded p-2">
                                <i class="bx bx-bar-chart-alt-2 bx-sm"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div style="width: 25%; padding: 0 0.75rem; margin-bottom: 1rem;" class="d-flex">
                <div class="card" style="width: 100%;">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span>Avg. LGI</span>
                                <div class="d-flex align-items-end mt-2">
                                    <h4 class="mb-0 me-2" id="summaryAvgLGI">0%</h4>
                                </div>
                                <small>Learning Gain Index</small>
                            </div>
                            <span class="badge bg-label-warning rounded p-2">
                                <i class="bx bx-trending-up bx-sm"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- / Summary Cards -->

        <!-- Charts Row -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Performance Distribution</h5>
                    </div>
                    <div class="card-body">
                        <div id="performanceChart"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Attendance Summary</h5>
                    </div>
                    <div class="card-body">
                        <div id="attendanceChart"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- / Charts Row -->

        <!-- Trainee Table Placeholder -->
        <div class="card" id="traineeTableContainer" style="display: none;">
            <h5 class="card-header">Trainee Details</h5>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>TID</th>
                            <th>Name</th>
                            <th>Attendance (%)</th>
                            <th>Pre-Test</th>
                            <th>Quiz Avg</th>
                            <th>Final</th>
                            <th>Total</th>
                            <th>LGI (%)</th>
                            <th>Actions</th> 
                        </tr>
                    </thead>
                    <tbody id="traineeTableBody">
                        <!-- Trainee rows will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
        <!-- / Trainee Table Placeholder -->

    </div>
    <!-- / Report Content Area -->

    <!-- Placeholder for initial state or when no group/course is selected -->
    <div id="reportPlaceholder" class="alert alert-info" role="alert">
        Please select filters and click "Apply Filters" to view the report.
    </div>

</div>
<!-- / Content specific to this page -->

<?php
// Don't close $conn here, let footer.php handle it
// Include the footer
include_once("../includes/footer.php"); 
?>

<!-- Add our custom JavaScript file -->
<script src="../assets/js/group-performance-report.js"></script>
  

<!-- Add custom styling for the cards and charts to match Canva design -->
<style>
.card {
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
}

.card-header {
    border-bottom: none;
    background-color: transparent;
    padding-bottom: 0;
}

.card-title {
    font-family: 'Poppins', sans-serif;
    font-weight: 500;
    color: #333;
}

.bg-label-primary, .bg-label-success, .bg-label-info, .bg-label-warning {
    border-radius: 8px;
}

.btn {
    border-radius: 8px;
    font-family: 'Poppins', sans-serif;
    transition: all 0.3s ease;
}

.btn-primary {
    background-color: #3aa5ff;
    border-color: #3aa5ff;
}

.btn-primary:hover {
    background-color: #2e96f2;
    border-color: #2e96f2;
}

.btn-outline-primary {
    color: #3aa5ff;
    border-color: #3aa5ff;
}

.btn-outline-primary:hover {
    background-color: #3aa5ff;
    border-color: #3aa5ff;
}

.table th {
    font-family: 'Poppins', sans-serif;
    font-weight: 500;
}

.badge {
    font-family: 'Poppins', sans-serif;
    font-weight: 500;
    padding: 5px 10px;
    border-radius: 6px;
}

.rounded-pill {
    border-radius: 50px !important;
}

/* Card content styling */
.card-body h4 {
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    color: #333;
}

.card-body small {
    color: #6c757d;
    font-family: 'Poppins', sans-serif;
}

/* Chart container dimensions */
#performanceChart, #attendanceChart {
    height: 300px;
    margin: 10px 0;
}
</style>

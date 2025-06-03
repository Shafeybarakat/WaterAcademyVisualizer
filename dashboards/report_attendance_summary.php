<?php
$pageTitle = "Attendance Summary Report"; // Set page title
include_once("../includes/config.php");
include_once("../includes/auth.php"); // Ensure user is logged in and sets $_SESSION["user_id"], $_SESSION["role"]

// Define allowed roles for this report
$allowed_roles = ["Super Admin", "Admin", "Instructor", "Coordinator"];
if (!isset($_SESSION["user_role"]) || !in_array($_SESSION["user_role"], $allowed_roles)) {
    // Redirect to dashboard with error message instead of login page
    // This prevents redirect loop when user is logged in but doesn't have the required role
    $_SESSION['access_denied_message'] = 'You do not have permission to access the Attendance Summary Report. Required roles: Super Admin, Admin, Instructor, or Coordinator.';
    header("Location: index.php?error=permission_denied");
    exit();
}

// Include the new header - this also includes the sidebar
include_once("../includes/header.php"); 

$user_role = $_SESSION["user_role"];
$user_id = $_SESSION["user_id"]; // Assuming user ID is stored in session

// Base SQL query
$sql = "SELECT att.TID, CONCAT(t.FirstName, ' ', t.LastName) AS FullName, att.CourseID, att.CourseName, att.GroupName, att.TotalSessions, att.PresentCount, att.LateCount, att.AbsentCount, att.ExcusedCount, att.AttendancePercentage 
        FROM vw_AttendanceSummary att
        JOIN Trainees t ON att.TID = t.TID";

$params = [];
$types = "";

// Apply filtering based on role
if ($user_role === "Instructor" || $user_role === "Coordinator") {
    // The view already includes joins to GroupCourses and Groups tables
    // We need to join to these tables again to filter by InstructorID or CoordinatorID
    $sql .= " JOIN GroupCourses gc ON att.CourseID = gc.CourseID AND att.GroupID = gc.GroupID";
    
    if ($user_role === "Instructor") {
        $sql .= " WHERE gc.InstructorID = ?";
        $params[] = $user_id;
        $types .= "i";
    } elseif ($user_role === "Coordinator") {
        $sql .= " JOIN Groups g ON att.GroupID = g.GroupID WHERE g.CoordinatorID = ?";
        $params[] = $user_id;
        $types .= "i";
    }
}
// Admins, Editors, Designer Admins see all data - no WHERE clause needed

$sql .= " ORDER BY FullName, att.CourseName";

// Prepare and execute the statement
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    // In a real app, log this error instead of dying
    error_log("Error preparing statement: " . $conn->error);
    // Display user-friendly error within the layout
    echo "<div class=\"container-xxl flex-grow-1 container-p-y\"><div class=\"alert alert-danger\">Error loading report data. Please contact support.</div></div>";
    include_once("../includes/footer.php");
    exit(); 
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

?>

<!-- Include Google Fonts for Canva-like styling -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<!-- Include Select2 CSS and JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Content specific to this page -->
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Back to Reports Button -->
    <div class="mb-4">
        <a href="reports.php?group_id=<?= $_GET['group_id'] ?? '' ?>&course_id=<?= $_GET['course_id'] ?? '' ?>" class="btn btn-primary" style="font-weight: bold; padding: 10px 20px; font-size: 16px;">
            <i class="bi bi-arrow-left me-1"></i> Back to Reports
        </a>
    </div>

    <!-- Report Actions -->
    <div class="card mb-4">
        <h5 class="card-header">Report Actions</h5>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <button type="button" id="printReportBtn" class="btn btn-outline-primary w-100">
                        <i class="bi bi-file-pdf"></i> Export PDF
                    </button>
                </div>
                <div class="col-md-6">
                    <button type="button" id="exportDataBtn" class="btn btn-outline-success w-100">
                        <i class="bi bi-file-excel"></i> Export Data
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- / Report Actions -->

    <!-- Attendance Summary Table -->
    <div class="card">
        <h5 class="card-header">Trainee Attendance Summary</h5>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Trainee Name</th>
                        <th>Course</th>
                        <th>Group</th>
                        <th>Total Sessions</th>
                        <th>Present</th>
                        <th>Late</th>
                        <th>Absent</th>
                        <th>Excused</th>
                        <th>Attendance (%)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    <?php
                    // Create a new query for filtering
                    $filterSql = "SELECT att.TID, CONCAT(t.FirstName, ' ', t.LastName) AS FullName, att.CourseID, att.CourseName, att.GroupName, att.TotalSessions, att.PresentCount, att.LateCount, att.AbsentCount, att.ExcusedCount, att.AttendancePercentage 
                                  FROM vw_AttendanceSummary att
                                  JOIN Trainees t ON att.TID = t.TID";
                    
                    $whereConditions = [];
                    $filterParams = [];
                    $filterTypes = "";
                    
                    // For trainee filtering (highest priority)
                    if (isset($_GET['trainee_id']) && !empty($_GET['trainee_id'])) {
                        $traineeId = $_GET['trainee_id'];
                        $whereConditions[] = "att.TID = ?";
                        $filterParams[] = $traineeId;
                        $filterTypes .= "i";
                        error_log("Added trainee filter for TID: " . $traineeId);
                    }
                    
                    // For course filtering
                    if (isset($_GET['course_id']) && !empty($_GET['course_id'])) {
                        $courseId = $_GET['course_id'];
                        $whereConditions[] = "att.CourseID = ?";
                        $filterParams[] = $courseId;
                        $filterTypes .= "s";
                        error_log("Added course filter for CourseID: " . $courseId);
                    }
                    
                    // For group filtering - Fixed SQL syntax error
                    if (isset($_GET['group_id']) && !empty($_GET['group_id'])) {
                        $groupId = $_GET['group_id'];
                        // Direct filter on GroupID rather than using a subquery
                        $whereConditions[] = "att.GroupID = ?";
                        $filterParams[] = $groupId;
                        $filterTypes .= "i";
                        error_log("Added group filter for GroupID: " . $groupId);
                    }
                    
                    // Role-based filtering
                    if ($user_role === "Instructor") {
                        $whereConditions[] = "EXISTS (SELECT 1 FROM GroupCourses gc WHERE gc.CourseID = att.CourseID AND gc.GroupID = att.GroupID AND gc.InstructorID = ?)";
                        $filterParams[] = $user_id;
                        $filterTypes .= "i";
                    } elseif ($user_role === "Coordinator") {
                        $whereConditions[] = "EXISTS (SELECT 1 FROM Groups g WHERE g.GroupID = att.GroupID AND g.CoordinatorID = ?)";
                        $filterParams[] = $user_id;
                        $filterTypes .= "i";
                    }
                    
                    // Add WHERE clause if any conditions exist
                    if (!empty($whereConditions)) {
                        $filterSql .= " WHERE " . implode(" AND ", $whereConditions);
                    }
                    
                    // Add ORDER BY clause
                    $filterSql .= " ORDER BY FullName, att.CourseName";
                    
                    // Debug: Log the SQL query and parameters
                    error_log("Final Filter SQL Query: " . $filterSql);
                    error_log("Filter Params Count: " . count($filterParams));
                    error_log("Filter Types: " . $filterTypes);
                    
                    // Re-prepare and execute the statement with filters if any filter is applied
                    if (count($filterParams) > count($params)) {
                        try {
                            $stmt = $conn->prepare($filterSql);
                            if ($stmt === false) {
                                // Log the error and display a user-friendly message
                                error_log("Error preparing filtered statement: " . $conn->error);
                                echo "<tr><td colspan=\"10\" class=\"text-center\">Error applying filters. Please try again or contact support.</td></tr>";
                            } else {
                                if (!empty($filterParams)) {
                                    $stmt->bind_param($filterTypes, ...$filterParams);
                                }
                                $stmt->execute();
                                $result = $stmt->get_result();
                                error_log("Filter query executed successfully. Rows returned: " . $result->num_rows);
                            }
                        } catch (Exception $e) {
                            error_log("Exception in filter query: " . $e->getMessage());
                            echo "<tr><td colspan=\"10\" class=\"text-center\">An error occurred while processing your request.</td></tr>";
                        }
                    }
                    
                    if ($result && $result->num_rows > 0) {
                        // Output data of each row
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td><strong>" . htmlspecialchars($row["FullName"]) . "</strong><br><small class=\"text-muted\">ID: " . htmlspecialchars($row["TID"]) . "</small></td>";
                            echo "<td>" . htmlspecialchars($row["CourseName"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["GroupName"]) . "</td>";
                            // Use null coalescing operator
                            echo "<td>" . htmlspecialchars($row["TotalSessions"] ?? "0") . "</td>";
                            echo "<td>" . htmlspecialchars($row["PresentCount"] ?? "0") . "</td>";
                            echo "<td>" . htmlspecialchars($row["LateCount"] ?? "0") . "</td>";
                            echo "<td>" . htmlspecialchars($row["AbsentCount"] ?? "0") . "</td>";
                            echo "<td>" . htmlspecialchars($row["ExcusedCount"] ?? "0") . "</td>";
                            // Format percentage
                            echo "<td><strong>" . htmlspecialchars(number_format($row["AttendancePercentage"] ?? 0, 1)) . "%</strong></td>";
                            // Add action buttons
                            echo "<td>";
                            echo "<a href='trainee_report.php?id=" . $row["TID"] . "&course_id=" . $row["CourseID"] . "' class='btn btn-sm btn-primary me-1'><i class='bi bi-bar-chart'></i> Course Report</a>";
                            echo "<a href='trainee_report_all.php?id=" . $row["TID"] . "' class='btn btn-sm btn-outline-primary'><i class='bi bi-list-ul'></i> All Courses</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan=\"10\" class=\"text-center\">No attendance data found matching your criteria.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <!--/ Attendance Summary Table -->

</div>
<!-- / Content specific to this page -->

<!-- Add custom styling to match group-analytics.php -->
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

.select2-container--default .select2-selection--single {
    height: 38px;
    border-radius: 8px;
    border: 1px solid #ced4da;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 38px;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}
</style>

<script>
$(document).ready(function() {
    // Initialize Select2 for trainee search
    $('#trainee_search').select2({
        placeholder: 'Search for a trainee...',
        allowClear: true,
        minimumInputLength: 1,
        ajax: {
            url: 'ajax_search_trainees.php',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    term: params.term
                };
            },
            processResults: function(data) {
                return {
                    results: data.results
                };
            },
            cache: true
        }
    });

    // Handle group filter change to update course options and auto-submit
    $('#group_filter').change(function() {
        const groupId = $(this).val();
        const courseSelect = $('#course_filter');
        
        if (groupId) {
            // Enable course select
            courseSelect.prop('disabled', false);
            
            // Fetch courses for the selected group
            $.ajax({
                url: 'ajax_get_courses_by_group.php',
                data: { group_id: groupId },
                dataType: 'json',
                success: function(data) {
                    courseSelect.empty().append('<option value="">All Courses</option>');
                    
                    if (data.courses && data.courses.length > 0) {
                        $.each(data.courses, function(i, course) {
                            courseSelect.append($('<option></option>').val(course.CourseID).text(course.CourseName));
                        });
                    }
                    
                    // Auto-submit the form after populating the course dropdown
                    $('#filterForm').submit();
                },
                error: function() {
                    console.error('Failed to fetch courses');
                }
            });
        } else {
            // Disable and reset course select
            courseSelect.prop('disabled', true).empty().append('<option value="">All Courses</option>');
            
            // Auto-submit the form when group is cleared
            $('#filterForm').submit();
        }
    });

    // Configure print button
    $('#printReportBtn').click(function() {
        window.print();
    });
});
</script>

<?php
if (isset($stmt)) $stmt->close();
// Don't close $conn here, let footer.php handle it
// Include the new footer
include_once("../includes/footer.php"); 
?>

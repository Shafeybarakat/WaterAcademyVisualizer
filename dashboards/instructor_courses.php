<?php
$pageTitle = "My Courses"; // Set page title
include_once("../includes/config.php");
include_once("../includes/auth.php");

// Protect this page - only users with view_courses permission can access
if (!isLoggedIn() || !hasPermission('view_courses')) {
    header("Location: ../login.php?message=access_denied");
    exit;
}

// Include the header - this also includes the sidebar
include_once("../includes/header.php");

// Get instructor ID
$instructorId = $_SESSION['user_id'];

// Query to get groups and courses assigned to this instructor
$query = "
    SELECT 
        g.GroupID, 
        g.GroupName, 
        g.Program,
        gc.ID as GroupCourseID, 
        c.CourseID, 
        c.CourseName,
        gc.StartDate,
        gc.EndDate,
        gc.Status,
        (SELECT COUNT(*) FROM Enrollments e WHERE e.GroupCourseID = gc.ID) as EnrolledTrainees
    FROM GroupCourses gc
    JOIN Groups g ON gc.GroupID = g.GroupID
    JOIN Courses c ON gc.CourseID = c.CourseID
    WHERE gc.InstructorID = ?
    ORDER BY g.GroupName, c.CourseName
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $instructorId);
$stmt->execute();
$result = $stmt->get_result();

// Organize results by group
$groups = [];
while ($row = $result->fetch_assoc()) {
    $groupId = $row['GroupID'];
    
    if (!isset($groups[$groupId])) {
        $groups[$groupId] = [
            'GroupID' => $row['GroupID'],
            'GroupName' => $row['GroupName'],
            'Program' => $row['Program'],
            'Courses' => []
        ];
    }
    
    $groups[$groupId]['Courses'][] = [
        'GroupCourseID' => $row['GroupCourseID'],
        'CourseID' => $row['CourseID'],
        'CourseName' => $row['CourseName'],
        'StartDate' => $row['StartDate'],
        'EndDate' => $row['EndDate'],
        'Status' => $row['Status'],
        'EnrolledTrainees' => $row['EnrolledTrainees']
    ];
}
?>

<!-- Include Google Fonts for Canva-like styling -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<!-- Content specific to this page -->
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Instructor /</span> My Courses
    </h4>

    <?php if (empty($groups)): ?>
    <div class="card">
        <div class="card-body">
            <div class="alert alert-info mb-0">
                <h6 class="alert-heading fw-bold mb-1">No courses assigned</h6>
                <p class="mb-0">You currently don't have any courses assigned to you. Please contact an administrator if you believe this is an error.</p>
            </div>
        </div>
    </div>
    <?php else: ?>
        <?php foreach ($groups as $group): ?>
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><?php echo htmlspecialchars($group['GroupName']); ?></h5>
                <small class="text-muted float-end"><?php echo htmlspecialchars($group['Program'] ?? 'No Program Specified'); ?></small>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Enrolled Trainees</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        <?php foreach ($group['Courses'] as $course): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($course['CourseName']); ?></strong></td>
                            <td>
                                <?php 
                                    $startDate = !empty($course['StartDate']) ? date('M d, Y', strtotime($course['StartDate'])) : 'Not set';
                                    $endDate = !empty($course['EndDate']) ? date('M d, Y', strtotime($course['EndDate'])) : 'Not set';
                                    echo $startDate . ' - ' . $endDate;
                                ?>
                            </td>
                            <td>
                                <?php 
                                    $statusClass = '';
                                    switch ($course['Status']) {
                                        case 'Scheduled':
                                            $statusClass = 'bg-label-primary';
                                            break;
                                        case 'In Progress':
                                            $statusClass = 'bg-label-warning';
                                            break;
                                        case 'Completed':
                                            $statusClass = 'bg-label-success';
                                            break;
                                        case 'Cancelled':
                                            $statusClass = 'bg-label-danger';
                                            break;
                                        default:
                                            $statusClass = 'bg-label-secondary';
                                    }
                                ?>
                                <span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($course['Status']); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($course['EnrolledTrainees']); ?> trainees</td>
                            <td>
                                <div class="dropdown">
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                        <i class="ri-more-2-fill"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="submit_grades.php?group_course_id=<?php echo $course['GroupCourseID']; ?>">
                                            <i class="ri-edit-2-line me-2"></i> Enter Grades
                                        </a>
                                        <a class="dropdown-item" href="submit_attendance.php?group_course_id=<?php echo $course['GroupCourseID']; ?>">
                                            <i class="ri-calendar-check-line me-2"></i> Enter Attendance
                                        </a>
                                        <a class="dropdown-item" href="report_trainee_performance.php?course_id=<?php echo $course['CourseID']; ?>&group_id=<?php echo $group['GroupID']; ?>">
                                            <i class="ri-bar-chart-line me-2"></i> View Performance
                                        </a>
                                        <a class="dropdown-item" href="report_attendance_summary.php?course_id=<?php echo $course['CourseID']; ?>&group_id=<?php echo $group['GroupID']; ?>">
                                            <i class="ri-calendar-event-line me-2"></i> View Attendance
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<!-- / Content -->

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

.table th {
    font-family: 'Poppins', sans-serif;
    font-weight: 500;
}

.badge {
    padding: 0.5rem 0.75rem;
    font-weight: 500;
    border-radius: 6px;
}

.bg-label-primary {
    background-color: rgba(58, 165, 255, 0.15) !important;
    color: #3aa5ff !important;
}

.bg-label-warning {
    background-color: rgba(255, 171, 0, 0.15) !important;
    color: #ffab00 !important;
}

.bg-label-success {
    background-color: rgba(40, 199, 111, 0.15) !important;
    color: #28c76f !important;
}

.bg-label-danger {
    background-color: rgba(234, 84, 85, 0.15) !important;
    color: #ea5455 !important;
}

.bg-label-secondary {
    background-color: rgba(108, 117, 125, 0.15) !important;
    color: #6c757d !important;
}
</style>

<?php
// Close statement and connection
if (isset($stmt)) $stmt->close();

// Include the footer
include_once("../includes/footer.php");
?>

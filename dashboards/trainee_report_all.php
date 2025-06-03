<?php
$pageTitle = "Trainee Report";
include_once "../includes/config.php"; // Contains $conn
include_once "../includes/auth.php";   // Session and permission checks
include_once "../includes/header.php"; // Includes session checks, header HTML
include_once "../includes/sidebar.php";

// Get trainee ID from URL parameter
$traineeId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If accessed directly without an ID, $traineeId will be 0, and we'll show a search.
// If an invalid ID (e.g., non-numeric) is somehow passed, intval makes it 0.
/* // We will handle $traineeId <= 0 by showing a search form instead of redirecting.
    // Redirect to a more appropriate page, like a student list or dashboard
    header("Location: trainees.php?error=invalid_id"); // Example redirect
    exit;
} */

// --- Fetch Trainee Data ---
$trainee = null;
$stmt = $conn->prepare("SELECT TID, CONCAT(FirstName, ' ', LastName) AS FullName, GroupID FROM Trainees WHERE TID = ?");
if ($stmt) {
    $stmt->bind_param("i", $traineeId);
    $stmt->execute();
    $traineeResult = $stmt->get_result();
    $trainee = $traineeResult->fetch_assoc();
    $stmt->close();
} else {
    error_log("Failed to prepare trainee query: " . $conn->error);
    // Display user-friendly error or redirect
}

// If trainee doesn't exist (and an ID was provided), we might show an error or allow searching again.
if ($traineeId > 0 && !$trainee) {
    // Optionally, set an error message to display
    $errorMessage = "Trainee with ID " . htmlspecialchars($_GET['id']) . " not found.";
    $traineeId = 0; // Reset traineeId to show search form
}

// --- Fetch Courses Trainee is Associated With ---
// Using StudentGradeSummary as the link between student and course
$courses = [];
$coursesQuery = "
    SELECT
           c.CourseID, c.CourseName, c.DurationWeeks, 
           COALESCE(CONCAT(u.FirstName, ' ', u.LastName), 'Not Assigned') AS InstructorName,
           e.Status AS EnrollmentStatus, -- Added Enrollment Status
           COALESCE(AVG(tg.Score), 0) AS AverageScore, -- Simple Average from TraineeGrades
           COUNT(DISTINCT tg.ComponentID) AS ComponentsGraded, -- Count graded components
           (SELECT COUNT(*) FROM GradeComponents WHERE IsDefault = 1) AS TotalComponents -- Fixed: GradeComponents doesn't have CourseID column
    FROM Courses c
    JOIN GroupCourses gc ON c.CourseID = gc.CourseID
    JOIN Enrollments e ON gc.ID = e.GroupCourseID AND e.TID = ?
    LEFT JOIN Users u ON gc.InstructorID = u.UserID -- Get instructor from GroupCourses table
    LEFT JOIN TraineeGrades tg ON e.TID = tg.TID AND gc.ID = tg.GroupCourseID
    GROUP BY c.CourseID, c.CourseName, c.DurationWeeks, InstructorName, e.Status -- Added e.Status to GROUP BY
    ORDER BY c.CourseName
";
$stmt = $conn->prepare($coursesQuery);
if ($stmt) {
    $stmt->bind_param("i", $traineeId);
    $stmt->execute();
    $coursesResult = $stmt->get_result();
    while ($row = $coursesResult->fetch_assoc()) {
        // Calculate completion percentage here if needed
        $row['ProgressPercentage'] = ($row['TotalComponents'] > 0)
            ? round(($row['ComponentsGraded'] / $row['TotalComponents']) * 100)
            : 0;
        $courses[] = $row;
    }
    $stmt->close();
} else {
    error_log("Failed to prepare courses query: " . $conn->error);
}


// --- Calculate Overall Trainee Attendance ---
$overallAttendance = 0;
$overallAttendanceQuery = "
    SELECT AVG(AttendancePercentage) AS AverageAttendance
    FROM Attendance
    WHERE TID = ?
";
$stmt = $conn->prepare($overallAttendanceQuery);
if ($stmt) {
    $stmt->bind_param("i", $traineeId);
    $stmt->execute();
    $overallAttendanceResult = $stmt->get_result();
    $attendanceData = $overallAttendanceResult->fetch_assoc();
    $overallAttendance = $attendanceData ? round($attendanceData['AverageAttendance'] ?? 0) : 0;
    $stmt->close();
} else {
    error_log("Failed to prepare overall attendance query: " . $conn->error);
}

// --- Get Strengths ---
$strengths = [];
$strengthsQuery = "
    SELECT tg.PositiveFeedback, c.CourseName, tg.GradeDate AS FeedbackDate, CONCAT(u.FirstName, ' ', u.LastName) AS InstructorName
    FROM TraineeGrades tg
    JOIN GroupCourses gc ON tg.GroupCourseID = gc.ID
    JOIN Courses c ON gc.CourseID = c.CourseID
    LEFT JOIN Users u ON tg.RecordedBy = u.UserID
    WHERE tg.TID = ? AND tg.PositiveFeedback IS NOT NULL AND tg.PositiveFeedback != ''
    GROUP BY gc.CourseID, tg.ComponentID
    ORDER BY tg.GradeDate DESC
";
$stmt = $conn->prepare($strengthsQuery);
if ($stmt) {
    $stmt->bind_param("i", $traineeId);
    $stmt->execute();
    $strengthsResult = $stmt->get_result();
    while ($row = $strengthsResult->fetch_assoc()) {
        $strengths[] = $row;
    }
    $stmt->close();
} else {
    error_log("Failed to prepare strengths query: " . $conn->error);
}


// --- Get Areas for Improvement ---
$improvements = [];
$improvementsQuery = "
    SELECT tg.AreasToImprove, c.CourseName, tg.GradeDate AS FeedbackDate, CONCAT(u.FirstName, ' ', u.LastName) AS InstructorName
    FROM TraineeGrades tg
    JOIN GroupCourses gc ON tg.GroupCourseID = gc.ID
    JOIN Courses c ON gc.CourseID = c.CourseID
    LEFT JOIN Users u ON tg.RecordedBy = u.UserID
    WHERE tg.TID = ? AND tg.AreasToImprove IS NOT NULL AND tg.AreasToImprove != ''
    GROUP BY gc.CourseID, tg.ComponentID
    ORDER BY tg.GradeDate DESC
";
$stmt = $conn->prepare($improvementsQuery);
if ($stmt) {
    $stmt->bind_param("i", $traineeId);
    $stmt->execute();
    $improvementsResult = $stmt->get_result();
    while ($row = $improvementsResult->fetch_assoc()) {
        $improvements[] = $row;
    }
    $stmt->close();
} else {
    error_log("Failed to prepare improvements query: " . $conn->error);
}

// --- Get Detailed Course Components and Grades ---
$courseComponents = []; // Initialize the array

$courseDetailsQuery = "
    SELECT
        c.CourseID,
        c.CourseName,
        gc.ComponentID,      -- From GradeComponents
        gc.ComponentName,    -- From GradeComponents
        gc.MaxPoints,        -- From GradeComponents
        tg.Score             -- Score might be null if not graded
    FROM Courses c
    JOIN GroupCourses gcr ON c.CourseID = gcr.CourseID
    JOIN Enrollments e ON gcr.ID = e.GroupCourseID AND e.TID = ? -- Link trainee to course first
    JOIN GradeComponents gc ON gc.IsDefault = 1 -- Join with default grade components only
    LEFT JOIN TraineeGrades tg ON e.TID = tg.TID AND gcr.ID = tg.GroupCourseID AND gc.ComponentID = tg.ComponentID -- Left join grades
    WHERE e.TID = ? -- Filter again just in case
    ORDER BY c.CourseID, gc.ComponentID -- Order for grouping
";
$stmt = $conn->prepare($courseDetailsQuery);
if ($stmt) {
    // Assuming the query structure requires TID twice, adjust if StudentGradeSummary join is sufficient
    $stmt->bind_param("ii", $traineeId, $traineeId);
    $stmt->execute();
    $componentResult = $stmt->get_result();
    while ($row = $componentResult->fetch_assoc()) {
        $courseId = $row['CourseID'];
        if (!isset($courseComponents[$courseId])) {
            $courseComponents[$courseId] = [
                'CourseName' => $row['CourseName'],
                'Components' => [],
                'WeightedTotalScore' => 0,
                'TotalWeightAchieved' => 0,
                'TotalMaxWeight' => 0
            ];
        }
         // Calculate normalized score (percentage) if MaxPoints exists and score is not null
         $normalizedScore = null;
         if ($row['Score'] !== null && isset($row['MaxPoints']) && $row['MaxPoints'] > 0) {
             $normalizedScore = ($row['Score'] / $row['MaxPoints']) * 100;
         }
         $row['NormalizedScore'] = $normalizedScore;

         // Use MaxPoints as weight for calculation
         $componentWeight = $row['MaxPoints'] ?? 0;
         if ($normalizedScore !== null && $componentWeight > 0) {
             $courseComponents[$courseId]['WeightedTotalScore'] += $normalizedScore * $componentWeight;
             $courseComponents[$courseId]['TotalWeightAchieved'] += $componentWeight;
         }
         // Accumulate total possible weight for the course components
          if ($componentWeight > 0) {
             $courseComponents[$courseId]['TotalMaxWeight'] += $componentWeight;
          }


        $courseComponents[$courseId]['Components'][] = $row;
    }
    $stmt->close();

    // Calculate final weighted average for each course
    foreach ($courseComponents as $courseId => &$courseData) {
       if ($courseData['TotalWeightAchieved'] > 0) {
          $courseData['FinalWeightedAverage'] = round($courseData['WeightedTotalScore'] / $courseData['TotalWeightAchieved']);
       } else {
          $courseData['FinalWeightedAverage'] = 0; // Or null, or 'N/A'
       }
       // You might also want a potential max score based on TotalMaxWeight
       if ($courseData['TotalMaxWeight'] > 0) {
           $courseData['PotentialWeightedAverage'] = round($courseData['WeightedTotalScore'] / $courseData['TotalMaxWeight']);
       } else {
           $courseData['PotentialWeightedAverage'] = 0;
       }
    }
    unset($courseData); // Unset reference


} else {
    error_log("Failed to prepare course details query: " . $conn->error);
}


// --- Get Recent Attendance Details ---
$attendanceHistory = [];
$attendanceQuery = "
    SELECT
        a.CreatedAt AS AttendanceDate, -- Using CreatedAt since there's no AttendanceDate field
        CASE 
            WHEN a.PresentHours > 0 THEN 'Present'
            WHEN a.ExcusedHours > 0 THEN 'Excused'
            WHEN a.LateHours > 0 THEN 'Late'
            ELSE 'Absent'
        END AS Status,
        c.CourseName,
        a.Notes
    FROM Attendance a
    JOIN GroupCourses gc ON a.GroupCourseID = gc.ID
    JOIN Courses c ON gc.CourseID = c.CourseID
    WHERE a.TID = ?
    ORDER BY a.CreatedAt DESC
    LIMIT 20 -- Limit history for performance
";
$stmt = $conn->prepare($attendanceQuery);
if ($stmt) {
    $stmt->bind_param("i", $traineeId);
    $stmt->execute();
    $attendanceResult = $stmt->get_result();
    while ($row = $attendanceResult->fetch_assoc()) {
        $attendanceHistory[] = $row;
    }
    $stmt->close();
} else {
    error_log("Failed to prepare attendance history query: " . $conn->error);
}

// --- Calculate Class Ranks for Each Course ---
// This rank is based on SIMPLE AVERAGE score from StudentGrades
// Uses Window Functions (Requires MariaDB 10.2+)
$courseRanks = [];
$rankQuery = "
    WITH CourseAverages AS (
        SELECT
            gc.CourseID,
            e.TID,
            AVG(tg.Score) AS AvgScore
        FROM Enrollments e
        JOIN GroupCourses gc ON e.GroupCourseID = gc.ID
        JOIN TraineeGrades tg ON e.TID = tg.TID AND e.GroupCourseID = tg.GroupCourseID
        GROUP BY gc.CourseID, e.TID
    ),
    RankedScores AS (
        SELECT
            CourseID,
            TID,
            AvgScore,
            RANK() OVER (PARTITION BY CourseID ORDER BY AvgScore DESC) as RankInCourse
        FROM CourseAverages
    ),
    CourseStudentCounts AS (
        SELECT
            gc.CourseID,
            COUNT(DISTINCT e.TID) as TotalStudents
        FROM Enrollments e
        JOIN GroupCourses gc ON e.GroupCourseID = gc.ID
        GROUP BY gc.CourseID
    )
    SELECT
        rs.CourseID,
        rs.RankInCourse as Rank,
        csc.TotalStudents
    FROM RankedScores rs
    JOIN CourseStudentCounts csc ON rs.CourseID = csc.CourseID
    WHERE rs.TID = ?
";
$stmt = $conn->prepare($rankQuery);
if ($stmt) {
    // Corrected bind_param: only one parameter needed
    $stmt->bind_param("i", $traineeId);
    $stmt->execute();
    $rankResult = $stmt->get_result();
    while ($row = $rankResult->fetch_assoc()) {
        $courseRanks[$row['CourseID']] = [
            'Rank' => $row['Rank'],
            'TotalStudents' => $row['TotalStudents']
        ];
    }
    $stmt->close();
} else {
    error_log("Failed to prepare rank query: " . $conn->error);
}

// Get current date for the report
$reportDate = date('F j, Y, g:i a');

?>

<main class="main-content p-4">
    <div class="container-fluid">
        <?php if ($traineeId > 0 && $trainee): ?>
        <!-- Report Header -->
        <div class="report-header mb-4 p-3 border rounded bg-light">
            <div class="row">
                <div class="col-md-6">
                    <h3 class="mb-1">Trainee Performance Report</h3>
                    <h2 class="mb-2 fw-bold"><?= htmlspecialchars($trainee['FullName']) ?></h2>
                    <p class="mb-0"><strong>Trainee ID:</strong> <?= htmlspecialchars($trainee['TID']) ?></p>
                    <!-- Add Group Name if available -->
                    <?php
                    $groupName = 'N/A';
                    if ($trainee['GroupID']) {
                        $groupStmt = $conn->prepare("SELECT GroupName FROM Groups WHERE GroupID = ?");
                        if($groupStmt) {
                            $groupStmt->bind_param("s", $trainee['GroupID']);
                            $groupStmt->execute();
                            $groupResult = $groupStmt->get_result();
                            $groupData = $groupResult->fetch_assoc();
                            if ($groupData) $groupName = $groupData['GroupName'];
                            $groupStmt->close();
                        }
                    }
                    ?>
                    <p class="mb-0"><strong>Group:</strong> <?= htmlspecialchars($groupName) ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0"><strong>Report Date:</strong> <?= $reportDate ?></p>
                    <p class="mb-0"><strong>Overall Attendance:</strong> <?= $overallAttendance ?>%</p>
                    <div class="report-actions mt-2">
                        <button id="printReportBtn" class="btn btn-sm btn-primary"><i class="bi bi-file-pdf"></i> Export PDF</button>
                        <button id="emailReportBtn" class="btn btn-sm btn-secondary"><i class="bi bi-envelope"></i> Email Report</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Courses Summary -->
        <div class="report-section card mb-4">
            <div class="card-header">
                <h4 class="mb-0">Courses Summary</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($courses)): ?>
                <table class="table table-sm table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Course Name</th>
                            
                            <th>Status</th>
                            <th>Avg. Score (Simple)</th>
                            <th>Rank</th>
                           <!-- <th>Progress</th> -->
                        </tr>
                    </thead>
                    <tbody>
                                  <?php foreach ($courses as $course): ?>
                        <tr>
                            <td><?= htmlspecialchars($course["CourseName"]) ?></td>
                            
                            <td><span class="badge bg-<?= strtolower($course["EnrollmentStatus"] ?? "secondary") ?>"><?= htmlspecialchars($course["EnrollmentStatus"] ?? "N/A") ?></span></td>
                            <td><?= round($course["AverageScore"], 1) ?>%</td>
                            <td>
                                <?php if (isset($courseRanks[$course["CourseID"]])):
                                    echo $courseRanks[$course["CourseID"]]["Rank"] . " of " . $courseRanks[$course["CourseID"]]["TotalStudents"];
                                else:
                                    echo "N/A";
                                endif; ?>
                            </td>
                           <!-- <td><?= $course["ProgressPercentage"] ?>% (<?= $course["ComponentsGraded"] ?>/<?= $course["TotalComponents"] ?>)</td> -->
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="text-muted">No course summary data available for this trainee.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Strengths & Improvements -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="report-section card h-100">
                     <div class="card-header bg-success text-white">
                         <h4 class="mb-0"><i class="bi bi-hand-thumbs-up me-2"></i>Strengths</h4>
                     </div>
                     <div class="card-body">
                        <?php if (!empty($strengths)): ?>
                            <ul class="list-group list-group-flush">
                            <?php foreach($strengths as $item): ?>
                                <li class="list-group-item">
                                    <p class="mb-1"><?= htmlspecialchars($item["PositiveFeedback"]) ?></p>
                                    <small class="text-muted">
                                        <span class="badge bg-primary"><?= htmlspecialchars($item["CourseName"]) ?></span> - 
                                        by <?= htmlspecialchars($item["InstructorName"]) ?> on 
                                        <?= date("M j, Y", strtotime($item["FeedbackDate"])) ?>
                                    </small>
                                </li>
                            <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted">No specific strengths recorded.</p>
                        <?php endif; ?>
                     </div>
                </div>
            </div>
            <div class="col-md-6">
                 <div class="report-section card h-100">
                     <div class="card-header bg-warning">
                         <h4 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Areas for Improvement</h4>
                     </div>
                     <div class="card-body">
                         <?php if (!empty($improvements)): ?>
                             <ul class="list-group list-group-flush">
                             <?php foreach($improvements as $item): ?>
                                 <li class="list-group-item">
                                     <p class="mb-1"><?= htmlspecialchars($item["AreasToImprove"]) ?></p>
                                     <small class="text-muted">
                                         <span class="badge bg-primary"><?= htmlspecialchars($item['CourseName']) ?></span> - 
                                         by <?= htmlspecialchars($item['InstructorName']) ?> on 
                                         <?= date('M j, Y', strtotime($item['FeedbackDate'])) ?>
                                     </small>
                                 </li>
                             <?php endforeach; ?>
                             </ul>
                         <?php else: ?>
                             <p class="text-muted">No specific areas for improvement recorded.</p>
                         <?php endif; ?>
                     </div>
                </div>
            </div>
        </div>

         <!-- Detailed Course Breakdown -->
        <div class="report-section card mb-4">
            <div class="card-header">
                <h4 class="mb-0">Detailed Course Grades</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($courseComponents)): ?>
                    <?php foreach ($courseComponents as $courseId => $details): ?>
                        <h5 class="mt-3"><?= htmlspecialchars($details['CourseName']) ?> (Weighted Avg: <?= $details['FinalWeightedAverage'] ?>%)</h5>
                        <div class="course-header mb-3" style="background-color: #f0f8ff; padding: 10px; border-left: 5px solid #0056b3;">
                            <h5 class="mt-0" style="color: #0056b3; font-weight: bold;"><?= htmlspecialchars($details['CourseName']) ?></h5>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-primary">Final Score: <?= $details['FinalWeightedAverage'] ?>%</span>
                                <?php if (isset($courseRanks[$courseId])): ?>
                                <span class="badge bg-info">Rank: <?= $courseRanks[$courseId]["Rank"] ?> of <?= $courseRanks[$courseId]["TotalStudents"] ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <table class="table table-sm table-bordered table-striped mb-4">
                             <thead class="table-primary">
                                <tr>
                                    <th>Component</th>
                                    <th>Max Points</th>
                                    <th>Score</th>
                                    <th>Visual Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($details['Components'] as $component): ?>
                                <?php 
                                    $scorePercent = 0;
                                    $progressClass = 'bg-secondary';
                                    
                                    if ($component['Score'] !== null && isset($component['MaxPoints']) && $component['MaxPoints'] > 0) {
                                        $scorePercent = min(100, round(($component['Score'] / $component['MaxPoints']) * 100));
                                        
                                        if ($scorePercent >= 90) {
                                            $progressClass = 'bg-success';
                                        } elseif ($scorePercent >= 75) {
                                            $progressClass = 'bg-info';
                                        } elseif ($scorePercent >= 60) {
                                            $progressClass = 'bg-warning';
                                        } else {
                                            $progressClass = 'bg-danger';
                                        }
                                    }
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($component['ComponentName']) ?></td>
                                    <td><?= htmlspecialchars($component['MaxPoints'] ?? 'N/A') ?></td>
                                    <td><?= ($component['Score'] !== null) ? htmlspecialchars($component['Score']) . (isset($component['MaxPoints']) ? ' / ' . htmlspecialchars($component['MaxPoints']) : '') : 'Not Graded' ?></td>
                                    <td>
                                        <?php if ($component['Score'] !== null): ?>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar <?= $progressClass ?>" role="progressbar" 
                                                 style="width: <?= $scorePercent ?>%;" 
                                                 aria-valuenow="<?= $scorePercent ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                <?= $scorePercent ?>%
                                            </div>
                                        </div>
                                        <?php else: ?>
                                        <span class="badge bg-secondary">Not Graded</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No detailed grade components available for this trainee.</p>
                <?php endif; ?>
            </div>
        </div>


        <!-- Attendance History -->
        <div class="report-section card mb-4">
             <div class="card-header"><h4 class="mb-0">Recent Attendance</h4></div>
             <div class="card-body">
                <?php if (!empty($attendanceHistory)): ?>
                <table class="table table-sm table-striped">
                    <thead><tr><th>Date</th><th>Course</th><th>Status</th><th>Notes</th></tr></thead>
                    <tbody>
                        <?php foreach($attendanceHistory as $att): ?>
                        <tr>
                            <td><?= date('M j, Y', strtotime($att['AttendanceDate'])) ?></td>
                            <td><?= htmlspecialchars($att['CourseName']) ?></td>
                            <td><?= htmlspecialchars($att['Status']) ?></td>
                            <td><?= htmlspecialchars($att['Notes']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p class="text-muted">No recent attendance records found.</p>
                <?php endif; ?>
        <?php else: ?>
            <!-- Trainee Selection Interface -->
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Select Trainee to View Report</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($errorMessage)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    <form method="GET" action="trainee_report.php" id="selectTraineeForm">
                        <div class="row justify-content-center">
                            <div class="col-md-8 col-lg-6"> {/* Adjust column size as needed */}
                                <div class="mb-3">
                                    <label for="trainee_search" class="form-label">Search for Trainee (by Name or ID)</label>
                                    {/* Removed form-control class from select, Select2 will style it */}
                                    <select id="trainee_search" name="id" style="width: 100%;"> {/* Style for initial state before Select2 */}
                                        <option value="">Type to search...</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary w-100" disabled id="viewReportButton">View Report</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Include Select2 CSS and JS -->
            <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
            <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

            <script>
            $(document).ready(function() {
                $('#trainee_search').select2({
                    placeholder: 'Type to search for a trainee...',
                    width: '100%', // Make Select2 100% width of its parent container
                    minimumInputLength: 1,
                    ajax: {
                        url: 'ajax_search_trainees.php',
                        dataType: 'json',
                        delay: 250, // wait 250 milliseconds before triggering the request
                        data: function (params) {
                            return {
                                term: params.term // search term
                            };
                        },
                        processResults: function (data) {
                            return { results: data.results };
                        },
                        cache: true
                    }
                }).on('select2:select', function (e) {
                    $('#viewReportButton').prop('disabled', false);
                });
            });
            </script>
        <?php endif; ?>
    </div><!-- /.container-fluid -->
</main>

<?php include_once "../includes/footer.php"; ?>

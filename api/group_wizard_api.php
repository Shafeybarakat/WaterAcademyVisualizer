<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'data' => null];

if (!isLoggedIn()) {
    $response['message'] = 'Authentication required.';
    echo json_encode($response);
    exit();
}

// Function to add a new trainee and enroll them in existing group courses
function addNewTraineeAndEnroll($conn, $traineeData, $groupId) {
    $firstName = $traineeData['FirstName'] ?? null;
    $lastName = $traineeData['LastName'] ?? null;
    $email = $traineeData['Email'] ?? null;
    $govID = $traineeData['GovID'] ?? null;
    $phone = $traineeData['Phone'] ?? null;

    if (empty($firstName) || empty($lastName) || empty($email)) {
        return ['success' => false, 'message' => 'Missing required trainee fields (FirstName, LastName, Email).'];
    }

    // Check if trainee with this email already exists
    $stmt = $conn->prepare("SELECT TID FROM Trainees WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        return ['success' => false, 'message' => "Trainee with email {$email} already exists."];
    }
    $stmt->close();

    // Insert trainee
    $stmt = $conn->prepare("INSERT INTO Trainees (FirstName, LastName, Email, GovID, Phone, GroupID, Status) VALUES (?, ?, ?, ?, ?, ?, 'Active')");
    if (!$stmt) {
        return ['success' => false, 'message' => 'Failed to prepare trainee insert statement: ' . $conn->error];
    }
    $stmt->bind_param("sssssi", $firstName, $lastName, $email, $govID, $phone, $groupId);
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to insert trainee: ' . $error];
    }
    $newTraineeID = $stmt->insert_id;
    $stmt->close();

    // Enroll trainee in all existing courses for this group
    $stmt = $conn->prepare("SELECT ID FROM GroupCourses WHERE GroupID = ?");
    if (!$stmt) {
        return ['success' => false, 'message' => 'Failed to prepare group courses select statement: ' . $conn->error];
    }
    $stmt->bind_param("i", $groupId);
    $stmt->execute();
    $groupCoursesResult = $stmt->get_result();
    $stmt->close();

    $enrollmentErrors = [];
    while ($groupCourse = $groupCoursesResult->fetch_assoc()) {
        $groupCourseID = $groupCourse['ID'];
        $enrollmentDate = date('Y-m-d'); // Current date

        $enrollStmt = $conn->prepare("INSERT INTO Enrollments (TID, GroupCourseID, EnrollmentDate, Status) VALUES (?, ?, ?, 'Enrolled')");
        if (!$enrollStmt) {
            $enrollmentErrors[] = "Failed to prepare enrollment statement for GroupCourseID {$groupCourseID}: " . $conn->error;
            continue;
        }
        $enrollStmt->bind_param("iis", $newTraineeID, $groupCourseID, $enrollmentDate);
        if (!$enrollStmt->execute()) {
            $enrollmentErrors[] = "Failed to enroll trainee {$newTraineeID} in GroupCourseID {$groupCourseID}: " . $enrollStmt->error;
        }
        $enrollStmt->close();
    }

    if (!empty($enrollmentErrors)) {
        return ['success' => true, 'message' => "Trainee added, but some enrollments failed: " . implode("; ", $enrollmentErrors), 'trainee_id' => $newTraineeID];
    }

    return ['success' => true, 'message' => 'Trainee added and enrolled successfully.', 'trainee_id' => $newTraineeID];
}


// Function to enroll existing trainees in a newly assigned course
function enrollExistingTraineesInCourse($conn, $groupId, $newGroupCourseID) {
    // Get all trainees in the specified group
    $stmt = $conn->prepare("SELECT TID FROM Trainees WHERE GroupID = ?");
    if (!$stmt) {
        return ['success' => false, 'message' => 'Failed to prepare trainee select statement for enrollment: ' . $conn->error];
    }
    $stmt->bind_param("i", $groupId);
    $stmt->execute();
    $traineesResult = $stmt->get_result();
    $stmt->close();

    $enrollmentErrors = [];
    while ($trainee = $traineesResult->fetch_assoc()) {
        $traineeID = $trainee['TID'];
        $enrollmentDate = date('Y-m-d'); // Current date

        // Check if enrollment already exists to prevent duplicates
        $checkStmt = $conn->prepare("SELECT EnrollmentID FROM Enrollments WHERE TID = ? AND GroupCourseID = ?");
        $checkStmt->bind_param("ii", $traineeID, $newGroupCourseID);
        $checkStmt->execute();
        $checkStmt->store_result();
        if ($checkStmt->num_rows > 0) {
            $checkStmt->close();
            continue; // Skip if already enrolled
        }
        $checkStmt->close();

        $enrollStmt = $conn->prepare("INSERT INTO Enrollments (TID, GroupCourseID, EnrollmentDate, Status) VALUES (?, ?, ?, 'Enrolled')");
        if (!$enrollStmt) {
            $enrollmentErrors[] = "Failed to prepare enrollment statement for Trainee {$traineeID} and GroupCourseID {$newGroupCourseID}: " . $conn->error;
            continue;
        }
        $enrollStmt->bind_param("iis", $traineeID, $newGroupCourseID, $enrollmentDate);
        if (!$enrollStmt->execute()) {
            $enrollmentErrors[] = "Failed to enroll trainee {$traineeID} in GroupCourseID {$newGroupCourseID}: " . $enrollStmt->error;
        }
        $enrollStmt->close();
    }

    if (!empty($enrollmentErrors)) {
        return ['success' => false, 'message' => "Some trainees could not be enrolled: " . implode("; ", $enrollmentErrors)];
    }

    return ['success' => true, 'message' => 'Existing trainees enrolled in new course successfully.'];
}


$action = $_POST['action'] ?? '';

switch ($action) {
    case 'create_group':
        if (!hasPermissionFast('manage_groups')) {
            $response['message'] = 'Permission denied to create groups.';
            break;
        }

        $groupName = $_POST['groupName'] ?? '';
        $program = $_POST['program'] ?? null;
        $duration = $_POST['duration'] ?? null;
        $semesters = $_POST['semesters'] ?? null;
        $startDate = $_POST['startDate'] ?? null;
        $endDate = $_POST['endDate'] ?? null;
        $description = $_POST['description'] ?? null;
        $status = $_POST['status'] ?? 'Active';
        $room = $_POST['room'] ?? null;
        $coordinatorID = $_POST['coordinatorID'] ?? null;

        if (empty($groupName)) {
            $response['message'] = 'Group Name is required.';
            break;
        }

        // Basic validation for dates
        if (!empty($startDate) && !empty($endDate) && $startDate > $endDate) {
            $response['message'] = 'Start Date cannot be after End Date.';
            break;
        }

        $stmt = $conn->prepare("INSERT INTO `Groups` (GroupName, Program, Duration, Semesters, StartDate, EndDate, Description, Status, Room, CoordinatorID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            $response['message'] = 'Failed to prepare statement: ' . $conn->error;
            break;
        }

        // Handle NULL for optional integer/date fields
        $duration = $duration === '' ? null : $duration;
        $semesters = $semesters === '' ? null : $semesters;
        $coordinatorID = $coordinatorID === '' ? null : $coordinatorID;
        $startDate = $startDate === '' ? null : $startDate;
        $endDate = $endDate === '' ? null : $endDate;

        $stmt->bind_param("ssiisssssi", $groupName, $program, $duration, $semesters, $startDate, $endDate, $description, $status, $room, $coordinatorID);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Group created successfully.';
            $response['data']['groupID'] = $stmt->insert_id;
        } else {
            $response['message'] = 'Failed to create group: ' . $stmt->error;
        }
        $stmt->close();
        break;

    case 'add_new_trainees_to_group':
        if (!hasPermissionFast('manage_trainees')) {
            $response['message'] = 'Permission denied to add trainees.';
            break;
        }

        $groupID = $_POST['groupID'] ?? null;
        $traineesData = json_decode($_POST['trainees'] ?? '[]', true);

        if (empty($groupID) || !is_array($traineesData) || empty($traineesData)) {
            $response['message'] = 'Invalid group ID or trainee data.';
            break;
        }

        $results = [];
        foreach ($traineesData as $trainee) {
            $results[] = addNewTraineeAndEnroll($conn, $trainee, $groupID);
        }

        $response['success'] = true;
        $response['message'] = 'Trainee addition process completed.';
        $response['data']['results'] = $results;
        break;

    case 'assign_course_instances_to_group':
        if (!hasPermissionFast('assign_courses')) {
            $response['message'] = 'Permission denied to assign courses.';
            break;
        }

        $groupID = $_POST['groupID'] ?? null;
        $coursesToAssign = json_decode($_POST['courses'] ?? '[]', true);

        if (empty($groupID) || !is_array($coursesToAssign) || empty($coursesToAssign)) {
            $response['message'] = 'Invalid group ID or course data.';
            break;
        }

        $results = [];
        foreach ($coursesToAssign as $course) {
            $courseID = $course['CourseID'] ?? null;
            $instructorID = $course['InstructorID'] ?? null;
            $startDate = $course['StartDate'] ?? null;
            $endDate = $course['EndDate'] ?? null;
            $location = $course['Location'] ?? null;
            $scheduleDetails = $course['ScheduleDetails'] ?? null;

            if (empty($courseID)) {
                $results[] = ['success' => false, 'message' => 'Missing CourseID for a course.'];
                continue;
            }

            // Handle NULL for optional integer/date fields
            $instructorID = $instructorID === '' ? null : $instructorID;
            $startDate = $startDate === '' ? null : $startDate;
            $endDate = $endDate === '' ? null : $endDate;

            $stmt = $conn->prepare("INSERT INTO GroupCourses (GroupID, CourseID, InstructorID, StartDate, EndDate, Location, ScheduleDetails, Status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Scheduled')");
            if (!$stmt) {
                $results[] = ['success' => false, 'message' => 'Failed to prepare GroupCourse insert statement: ' . $conn->error];
                continue;
            }
            $stmt->bind_param("iiissss", $groupID, $courseID, $instructorID, $startDate, $endDate, $location, $scheduleDetails);

            if ($stmt->execute()) {
                $newGroupCourseID = $stmt->insert_id;
                $enrollmentResult = enrollExistingTraineesInCourse($conn, $groupID, $newGroupCourseID);
                $results[] = ['success' => true, 'message' => 'Course assigned and trainees enrolled.', 'group_course_id' => $newGroupCourseID, 'enrollment_status' => $enrollmentResult];
            } else {
                $results[] = ['success' => false, 'message' => 'Failed to assign course: ' . $stmt->error];
            }
            $stmt->close();
        }

        $response['success'] = true;
        $response['message'] = 'Course assignment process completed.';
        $response['data']['results'] = $results;
        break;

    default:
        $response['message'] = 'Invalid action.';
        break;
}

echo json_encode($response);
?>

<?php
// api/trainee_management_api.php

require_once "../includes/auth.php";
require_once "../includes/config.php";

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// Check if user is logged in and has permission to manage trainees
if (!isLoggedIn() || !hasPermission('manage_trainees')) {
    $response['message'] = 'Permission denied.';
    echo json_encode($response);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_trainees_by_group':
        if (!isset($_GET['group_id']) || empty($_GET['group_id'])) {
            $response['message'] = 'Group ID is required.';
            echo json_encode($response);
            exit;
        }
        $groupId = (int)$_GET['group_id'];

        try {
            $query = "SELECT TID, FirstName, LastName, GovID, Email, PhoneNumber FROM Trainees WHERE GroupID = ? ORDER BY LastName, FirstName";
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Database prepare error: " . $conn->error);
            }
            $stmt->bind_param("i", $groupId);
            $stmt->execute();
            $result = $stmt->get_result();

            $trainees = [];
            while ($row = $result->fetch_assoc()) {
                $trainees[] = $row;
            }
            $response['success'] = true;
            $response['trainees'] = $trainees;
            $response['message'] = 'Trainees fetched successfully.';
        } catch (Exception $e) {
            $response['message'] = 'Error fetching trainees: ' . $e->getMessage();
            error_log("Error in get_trainees_by_group: " . $e->getMessage());
        }
        break;

    case 'save_trainees':
        $input = json_decode(file_get_contents('php://input'), true);
        $traineesToSave = $input['trainees'] ?? [];

        if (empty($traineesToSave)) {
            $response['message'] = 'No trainee data provided for saving.';
            echo json_encode($response);
            exit;
        }

        $conn->begin_transaction();
        try {
            foreach ($traineesToSave as $traineeData) {
                $tid = $traineeData['TID'] ?? null;
                $groupId = $traineeData['GroupID'];
                $firstName = $traineeData['FirstName'];
                $lastName = $traineeData['LastName'];
                $govId = $traineeData['GovID'];
                $phoneNumber = $traineeData['PhoneNumber'];
                $email = $traineeData['Email'];
                $isNew = $traineeData['isNew'] ?? false;

                if ($isNew) {
                    // Insert new trainee
                    $insertQuery = "INSERT INTO Trainees (FirstName, LastName, GovID, Email, PhoneNumber, GroupID) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($insertQuery);
                    if (!$stmt) {
                        throw new Exception("Database prepare error for insert: " . $conn->error);
                    }
                    $stmt->bind_param("sssssi", $firstName, $lastName, $govId, $email, $phoneNumber, $groupId);
                    $stmt->execute();
                    $newTraineeId = $conn->insert_id;

                    // Check if the group has any courses assigned before enrolling the trainee
                    $checkCoursesQuery = "SELECT COUNT(*) AS course_count FROM GroupCourses WHERE GroupID = ?";
                    $stmtCheckCourses = $conn->prepare($checkCoursesQuery);
                    if (!$stmtCheckCourses) {
                        throw new Exception("Database prepare error for checking courses: " . $conn->error);
                    }
                    $stmtCheckCourses->bind_param("i", $groupId);
                    $stmtCheckCourses->execute();
                    $resultCheckCourses = $stmtCheckCourses->get_result();
                    $courseCountRow = $resultCheckCourses->fetch_assoc();
                    $hasCourses = $courseCountRow['course_count'] > 0;
                    $stmtCheckCourses->close();

                    if ($hasCourses) {
                        // Enroll new trainee in the Enrollment table ONLY if the group has courses
                        $enrollmentQuery = "INSERT INTO Enrollment (TraineeID, GroupID, EnrollmentDate) VALUES (?, ?, NOW())";
                        $stmtEnroll = $conn->prepare($enrollmentQuery);
                        if (!$stmtEnroll) {
                            throw new Exception("Database prepare error for enrollment: " . $conn->error);
                        }
                        $stmtEnroll->bind_param("ii", $newTraineeId, $groupId);
                        $stmtEnroll->execute();
                    } else {
                        // Log or message if not enrolled due to no courses
                        error_log("New trainee (ID: $newTraineeId) not enrolled in Enrollment table because GroupID $groupId has no courses.");
                    }
                } else {
                    // Update existing trainee
                    $updateQuery = "UPDATE Trainees SET FirstName = ?, LastName = ?, GovID = ?, Email = ?, PhoneNumber = ?, GroupID = ? WHERE TID = ?";
                    $stmt = $conn->prepare($updateQuery);
                    if (!$stmt) {
                        throw new Exception("Database prepare error for update: " . $conn->error);
                    }
                    $stmt->bind_param("sssssii", $firstName, $lastName, $govId, $email, $phoneNumber, $groupId, $tid);
                    $stmt->execute();
                }
            }
            $conn->commit();
            $response['success'] = true;
            $response['message'] = 'Trainees saved successfully.';
        } catch (Exception $e) {
            $conn->rollback();
            $response['message'] = 'Error saving trainees: ' . $e->getMessage();
            error_log("Error in save_trainees: " . $e->getMessage());
        }
        break;

    default:
        $response['message'] = 'Invalid action.';
        break;
}

echo json_encode($response);
?>

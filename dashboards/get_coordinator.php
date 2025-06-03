<?php
// get_coordinator.php - Returns coordinator details as JSON
require_once "../includes/config.php";
require_once "../includes/auth.php";

// Check if user is logged in and has permission to view users
if (!isLoggedIn() || !hasPermission('view_users')) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

// Get coordinator ID
$userId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($userId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid user ID']);
    exit;
}

// Get the RoleID for Coordinator
$roleStmt = $conn->prepare("SELECT RoleID FROM Roles WHERE RoleName = 'Coordinator'");
$roleStmt->execute();
$roleResult = $roleStmt->get_result();
$roleRow = $roleResult->fetch_assoc();
$roleID = $roleRow['RoleID'];
$roleStmt->close();

// Query the coordinator details
$stmt = $conn->prepare("SELECT 
                        UserID, 
                        Username, 
                        FirstName, 
                        LastName, 
                        Email, 
                        Phone, 
                        IsActive,
                        Status,
                        LastLogin,
                        (SELECT COUNT(*) FROM Groups WHERE CoordinatorID = Users.UserID) as GroupCount
                    FROM Users 
                    WHERE UserID = ? AND RoleID = ?");
$stmt->bind_param("ii", $userId, $roleID);
$stmt->execute();
$result = $stmt->get_result();
$coordinator = $result->fetch_assoc();

if (!$coordinator) {
    http_response_code(404);
    echo json_encode(['error' => 'Coordinator not found']);
    exit;
}

// Return coordinator data as JSON
header('Content-Type: application/json');
echo json_encode($coordinator);

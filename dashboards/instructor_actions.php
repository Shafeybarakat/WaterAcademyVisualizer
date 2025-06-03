e<?php
// instructor_actions.php - Handle instructor CRUD operations
require_once "../includes/config.php";
require_once "../includes/auth.php";

// Check if user is logged in and has permission to manage instructors
if (!isLoggedIn() || !hasPermission('manage_users')) {
    die("Access denied");
}

// Get the action
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        addInstructor();
        break;
    case 'edit':
        editInstructor();
        break;
    case 'delete':
        deleteInstructor();
        break;
    default:
        header("Location: instructors.php?error=invalid_action");
        exit;
}

// Function to add a new instructor
function addInstructor() {
    global $conn;
    
    // Get form data
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $specialty = $_POST['specialty'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $qualifications = $_POST['qualifications'] ?? '';
    
    // Validate required fields
    if (empty($firstName) || empty($lastName) || empty($email) || empty($username) || empty($password)) {
        header("Location: instructors.php?error=missing_fields");
        exit;
    }
    
    // Check if username or email already exists
    $checkStmt = $conn->prepare("SELECT UserID FROM Users WHERE Username = ? OR Email = ?");
    $checkStmt->bind_param("ss", $username, $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        header("Location: instructors.php?error=duplicate_user");
        exit;
    }
    
    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Get the RoleID for Instructor
    $roleStmt = $conn->prepare("SELECT RoleID FROM Roles WHERE RoleName = 'Instructor'");
    $roleStmt->execute();
    $roleResult = $roleStmt->get_result();
    $roleRow = $roleResult->fetch_assoc();
    $roleID = $roleRow['RoleID'];
    $roleStmt->close();
    
    // Insert the new instructor
    $stmt = $conn->prepare("INSERT INTO Users (Username, FirstName, LastName, Password, Email, Phone, Specialty, Role, RoleID, Qualifications, Status, IsActive) VALUES (?, ?, ?, ?, ?, ?, ?, 'Instructor', ?, ?, 'Active', 1)");
    
    if ($stmt === false) {
        error_log("SQL Error: " . $conn->error);
        header("Location: instructors.php?error=db_error");
        exit;
    }
    
    $stmt->bind_param("sssssssiss", $username, $firstName, $lastName, $hashedPassword, $email, $phone, $specialty, $roleID, $qualifications);
    
    if ($stmt->execute()) {
        header("Location: instructors.php?success=instructor_added");
    } else {
        header("Location: instructors.php?error=db_error");
    }
    
    $stmt->close();
    exit;
}

// Function to edit an instructor
function editInstructor() {
    global $conn;
    
    // Get form data
    $instructorId = $_POST['instructor_id'] ?? 0;
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $specialty = $_POST['specialty'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $qualifications = $_POST['qualifications'] ?? '';
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    // Validate required fields
    if (empty($instructorId) || empty($firstName) || empty($lastName) || empty($email)) {
        header("Location: instructors.php?error=missing_fields");
        exit;
    }
    
    // Check if email exists for another user
    $checkStmt = $conn->prepare("SELECT UserID FROM Users WHERE Email = ? AND UserID != ?");
    $checkStmt->bind_param("si", $email, $instructorId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        header("Location: instructors.php?error=duplicate_email");
        exit;
    }
    
    // Get the RoleID for Instructor
    $roleStmt = $conn->prepare("SELECT RoleID FROM Roles WHERE RoleName = 'Instructor'");
    $roleStmt->execute();
    $roleResult = $roleStmt->get_result();
    $roleRow = $roleResult->fetch_assoc();
    $roleID = $roleRow['RoleID'];
    $roleStmt->close();
    
    // Update the instructor
    $stmt = $conn->prepare("UPDATE Users SET FirstName = ?, LastName = ?, Email = ?, Phone = ?, Specialty = ?, Qualifications = ?, IsActive = ? WHERE UserID = ? AND RoleID = ?");
    $stmt->bind_param("ssssssiis", $firstName, $lastName, $email, $phone, $specialty, $qualifications, $isActive, $instructorId, $roleID);
    
    if ($stmt->execute()) {
        header("Location: instructors.php?success=instructor_updated");
    } else {
        header("Location: instructors.php?error=db_error");
    }
    
    $stmt->close();
    exit;
}

// Function to delete an instructor
function deleteInstructor() {
    global $conn;
    
    // Get instructor ID
    $instructorId = $_POST['instructor_id'] ?? 0;
    
    if (empty($instructorId)) {
        header("Location: instructors.php?error=missing_id");
        exit;
    }
    
    // Get the RoleID for Instructor
    $roleStmt = $conn->prepare("SELECT RoleID FROM Roles WHERE RoleName = 'Instructor'");
    $roleStmt->execute();
    $roleResult = $roleStmt->get_result();
    $roleRow = $roleResult->fetch_assoc();
    $roleID = $roleRow['RoleID'];
    $roleStmt->close();
    
    // Get instructor name for error messages
    $nameStmt = $conn->prepare("SELECT CONCAT(FirstName, ' ', LastName) as InstructorName FROM Users WHERE UserID = ? AND RoleID = ?");
    $nameStmt->bind_param("ii", $instructorId, $roleID);
    $nameStmt->execute();
    $nameResult = $nameStmt->get_result();
    $instructorName = '';
    
    if ($nameResult->num_rows > 0) {
        $instructorName = $nameResult->fetch_assoc()['InstructorName'];
    }
    
    // Check if instructor has active courses in GroupCourses
    // We need to check if there are any courses where:
    // 1. The instructor is assigned
    // 2. The course is currently active (EndDate is in the future or null)
    $currentDate = date('Y-m-d');
    
    try {
        // Try to query GroupCourses with InstructorID and date check
        $checkStmt = $conn->prepare("
            SELECT gc.GroupCourseID, g.GroupName, c.CourseName, gc.StartDate, gc.EndDate 
            FROM GroupCourses gc
            JOIN Groups g ON gc.GroupID = g.GroupID
            JOIN Courses c ON gc.CourseID = c.CourseID
            WHERE gc.InstructorID = ? 
            AND (gc.EndDate IS NULL OR gc.EndDate >= ?)
        ");
        
        if (!$checkStmt) {
            // If the query fails because InstructorID doesn't exist in GroupCourses,
            // proceed with deletion
            $stmt = $conn->prepare("DELETE FROM Users WHERE UserID = ? AND RoleID = ?");
            $stmt->bind_param("ii", $instructorId, $roleID);
            
            if ($stmt->execute()) {
                header("Location: instructors.php?success=instructor_deleted");
            } else {
                header("Location: instructors.php?error=db_error");
            }
            
            $stmt->close();
            exit;
        }
        
        $checkStmt->bind_param("is", $instructorId, $currentDate);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        // If instructor has active courses, show error with course details
        if ($checkResult->num_rows > 0) {
            $activeCourses = [];
            while ($row = $checkResult->fetch_assoc()) {
                $activeCourses[] = $row['CourseName'] . ' (Group: ' . $row['GroupName'] . ')';
            }
            
            $courseList = implode(', ', $activeCourses);
            $errorMessage = "Cannot delete instructor " . htmlspecialchars($instructorName) . 
                           " because they are currently assigned to the following active courses: " . 
                           htmlspecialchars($courseList) . 
                           ". The instructor can only be deleted after these courses are completed.";
            
            // Store error message in session to display on instructors.php
            $_SESSION['instructor_error'] = $errorMessage;
            header("Location: instructors.php?error=instructor_has_active_courses");
            exit;
        }
        
        // If no active courses, proceed with deletion
        $stmt = $conn->prepare("DELETE FROM Users WHERE UserID = ? AND RoleID = ?");
        $stmt->bind_param("ii", $instructorId, $roleID);
        
        if ($stmt->execute()) {
            header("Location: instructors.php?success=instructor_deleted");
        } else {
            header("Location: instructors.php?error=db_error");
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        // If any error occurs, log it and show a generic error
        error_log("Error deleting instructor: " . $e->getMessage());
        header("Location: instructors.php?error=db_error");
        exit;
    }
}

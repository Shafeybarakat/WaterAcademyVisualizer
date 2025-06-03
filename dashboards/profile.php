<?php
$pageTitle = "My Profile"; // Set the page title explicitly
/**
 * profile.php
 *
 * Displays and updates the current user's profile, including avatar selection, uploads,
 * extended user details, and password change, using Post/Redirect/Get to avoid resubmission.
 * Default avatars: /assets/img/avatars/1.png .. /assets/img/avatars/4.png
 * Uploaded photos: /assets/userphotos/{UserID}_{timestamp}.{ext}
 * The 'AvatarPath' column in the 'Users' table stores the absolute web path to the image.
 */

// Include database config and header markup


include_once '../includes/header.php'; // Then HTML structure
// Sidebar is already included by header.php, so this line is redundant and likely causing layout issues.

// Start session if needed
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
// Authentication check
if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}
$UserID = (int) $_SESSION['user_id'];

// Avatar directories (absolute web paths)
$defaultDir  = '../assets/img/avatars/'; // Corrected to be a directory path
$uploadDir   = '../assets/userphotos/';
$uploadFsDir = __DIR__ . '/../assets/userphotos/';
if (!file_exists($uploadFsDir)) {
    mkdir($uploadFsDir, 0755, true);
}

// Initialize messages
$profileMsg  = '';
$passwordMsg = '';

// Show messages after redirect
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (isset($_GET['profile_updated'])) {
        $profileMsg = 'Profile updated successfully.';
    }
    if (isset($_GET['password_updated'])) {
        $passwordMsg = 'Password changed successfully.';
    }
}

// Handle POST submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Change Password Flow
    if (isset($_POST['change_password'])) {
        $current = trim($_POST['current_password']);
        $new1    = trim($_POST['new_password']);
        $new2    = trim($_POST['repeat_password']);

        // Fetch existing password and email/username
        $stmt = $conn->prepare('SELECT Password, Email, Username FROM `Users` WHERE UserID = ?');
        $stmt->bind_param('i', $UserID);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!password_verify($current, $user['Password'])) {
            $passwordMsg = 'Current password is incorrect.';
        } elseif ($new1 !== $new2) {
            $passwordMsg = 'New passwords do not match.';
        } else {
            $hash = password_hash($new1, PASSWORD_DEFAULT);
            $up   = $conn->prepare('UPDATE `Users` SET Password = ? WHERE UserID = ?');
            $up->bind_param('si', $hash, $UserID);
            if ($up->execute()) {
                // Send email notification
                $to   = $user['Email'];
                $sub  = 'Your Water Academy password has been changed';
                $body = "Hello {$user['Username']},\nYour password was successfully changed on " . date('Y-m-d H:i:s') . ".";
                mail($to, $sub, $body);
                // Redirect to avoid resubmission
                header('Location: profile.php?password_updated=1');
                exit;
            }
        }
    }
    // Profile Update Flow
    else {
        $fields   = ['Username','Email','Phone','Department','FirstName','LastName','Biography','Qualifications','PreferredLanguage','Specialty'];
        $sqlParts = [];
        $params   = [];

        // Collect field updates
        foreach ($fields as $f) {
            $key = strtolower($f);
            if (isset($_POST[$key])) {
                $sqlParts[] = "`{$f}` = ?";
                $params[]   = trim($_POST[$key]);
            }
        }
        // Default avatar selection
        if (!empty($_POST['default_avatar'])) {
            $sel = basename($_POST['default_avatar']);
            if (in_array($sel, ['1.png','2.png','3.png','4.png'], true)) {
                $sqlParts[] = 'AvatarPath = ?';
                $params[]   = $defaultDir . $sel; // Corrected path concatenation
            }
        }
        // New avatar upload
        if (isset($_FILES['new_avatar']) && $_FILES['new_avatar']['error'] === UPLOAD_ERR_OK) {
            $tmp = $_FILES['new_avatar']['tmp_name'];
            $ext = strtolower(pathinfo($_FILES['new_avatar']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','gif'], true) && $_FILES['new_avatar']['size'] <= 2 * 1024 * 1024) {
                $fname = $UserID . '_' . time() . '.' . $ext;
                $dest  = $uploadFsDir . $fname;
                if (move_uploaded_file($tmp, $dest)) {
                    $sqlParts[] = 'AvatarPath = ?';
                    $params[]   = $uploadDir . $fname;
                } else {
                    $profileMsg = "Failed to upload new avatar. Please check permissions.";
                }
            } else {
                if (!in_array($ext, ['jpg','jpeg','png','gif'], true)) {
                    $profileMsg = "Invalid avatar file type. Only JPG, JPEG, PNG, GIF are allowed.";
                } elseif ($_FILES['new_avatar']['size'] > 2 * 1024 * 1024) {
                    $profileMsg = "Avatar file size exceeds 2MB limit.";
                }
            }
        }
        // Execute update only if there are changes and no critical errors (like avatar upload failure)
        if (!empty($sqlParts) && empty($profileMsg)) {
            $sql      = 'UPDATE `Users` SET ' . implode(', ', $sqlParts) . ' WHERE UserID = ?';
            $params[] = $UserID;
            $stmt     = $conn->prepare($sql);
            if ($stmt) {
                $types    = str_repeat('s', count($params) - 1) . 'i';
                // Use splat operator for bind_param if PHP 5.6+
                $stmt->bind_param($types, ...$params);
                // Fallback for older PHP versions:
                // $bindArgs = [];
                // $bindArgs[] = $types;
                // for ($i = 0; $i < count($params); $i++) {
                //    $bindArgs[] = &$params[$i];
                // }
                // call_user_func_array([$stmt, 'bind_param'], $bindArgs);

                if ($stmt->execute()) {
                    header('Location: profile.php?profile_updated=1');
                    exit;
                } else {
                    $profileMsg = "Error updating profile: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $profileMsg = "Error preparing profile update: " . $conn->error;
            }
        } elseif (empty($sqlParts) && empty($profileMsg)) {
            // No changes were made, or only an avatar upload was attempted and failed setting $profileMsg
            if (isset($_POST) && !empty($_POST) && !isset($_FILES['new_avatar']['tmp_name'])) { // Check if form was submitted with data but no changes applied
                 $profileMsg = "No changes detected to save.";
            }
        }
    }
}

// Fetch current user data
$stmt = $conn->prepare(
    'SELECT Username, Email, AvatarPath, Phone, Department, FirstName, LastName, Biography, Qualifications, PreferredLanguage, Specialty
     FROM `Users` WHERE UserID = ?'
);
$stmt->bind_param('i', $UserID);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Sanitize for display
foreach ($user as $k => $v) {
    $user[$k] = htmlspecialchars($v, ENT_QUOTES);
}
$username          = $user['Username'];
$email             = $user['Email'];
$phone             = $user['Phone'];
$department        = $user['Department'];
$firstname         = $user['FirstName'];
$lastname          = $user['LastName'];
$biography         = $user['Biography'];
$qualifications    = $user['Qualifications'];
$preferredlanguage = $user['PreferredLanguage'];
$specialty         = $user['Specialty'];
$avatarUrl         = $user['AvatarPath'] ?: ($defaultDir . '1.png'); // Corrected default path
?>

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="container" style="max-width:700px;">
    <?php if ($profileMsg): ?><div class="alert alert-success"><?php echo $profileMsg; ?></div><?php endif; ?>
    <?php if ($passwordMsg): ?><div class="alert alert-info"><?php echo $passwordMsg; ?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data" id="mainProfileForm">
      <!-- Avatar -->
      <div class="text-center mb-3">
        <img src="<?php echo $avatarUrl; ?>" class="rounded-circle border" width="150" alt="Avatar">
      </div>
      <div class="text-center mb-3">
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#avatarModal">Change Photo</button>
      </div>
      <!-- Personal Fields -->
      <div class="row">
        <div class="col-md-6 mb-3"><label>First Name</label><input type="text" name="firstname" class="form-control" value="<?php echo $firstname; ?>"></div>
        <div class="col-md-6 mb-3"><label>Last Name</label><input type="text" name="lastname" class="form-control" value="<?php echo $lastname; ?>"></div>
      </div>
      <div class="mb-3"><label>Username</label><input type="text" name="username" class="form-control" value="<?php echo $username; ?>"></div>
      <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" value="<?php echo $email; ?>"></div>
      <div class="mb-3"><label>Phone</label><input type="text" name="phone" class="form-control" value="<?php echo $phone; ?>"></div>
      <div class="mb-3"><label>Department</label><input type="text" name="department" class="form-control" value="<?php echo $department; ?>"></div>
      <div class="mb-3"><label>Specialty</label><input type="text" name="specialty" class="form-control" value="<?php echo $specialty; ?>"></div>
      <div class="mb-3"><label>Preferred Language</label><input type="text" name="preferredlanguage" class="form-control" value="<?php echo $preferredlanguage; ?>"></div>
      <div class="mb-3"><label>Biography</label><textarea name="biography" class="form-control" rows="3"><?php echo $biography; ?></textarea></div>
      <div class="mb-3"><label>Qualifications</label><textarea name="qualifications" class="form-control" rows="2"><?php echo $qualifications; ?></textarea></div>
      <div class="d-flex justify-content-between">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#passModal">Change Password</button>
      </div>
    </form>
  </div>

  <!-- Avatar Modal -->
  <div class="modal fade" id="avatarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Choose Avatar</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body"><div class="row text-center">
        <?php for ($i = 1; $i <= 4; $i++): ?><div class="col-3 mb-3">
          <label><input type="radio" name="default_avatar" form="mainProfileForm" value="<?php echo $i . '.png'; ?>" hidden <?php echo ($avatarUrl === $defaultDir . $i . '.png') ? 'checked' : ''; ?>><img src="<?php echo $defaultDir . $i . '.png'; ?>" class="rounded-circle border <?php echo ($avatarUrl === $defaultDir . $i . '.png') ? 'border-primary' : 'border-secondary'; ?>" width="80"></label>
        </div><?php endfor; ?></div>
        <hr>
        <label>Or upload new photo</label>
        <input type="file" name="new_avatar" form="mainProfileForm" class="form-control">
      </div>
      <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button class="btn btn-primary" data-bs-dismiss="modal">Select</button></div>
    </div></div>
  </div>

  <!-- Password Modal -->
  <div class="modal fade" id="passModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
      <form method="post"><input type="hidden" name="change_password" value="1">
      <div class="modal-header"><h5 class="modal-title">Change Password</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="mb-3"><label>Current Password</label><input type="password" name="current_password" class="form-control"></div>
        <div class="mb-3"><label>New Password</label><input type="password" name="new_password" class="form-control"></div>
        <div class="mb-3"><label>Repeat Password</label><input type="password" name="repeat_password" class="form-control"></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Submit</button></div>
      </form>
    </div></div>
  </div>
</div>

<?php include_once '../includes/footer.php'; ?>

<?php
$pageTitle = "Home";

require_once "../includes/auth.php";

if (!isLoggedIn() || !hasPermission('view_dashboards')) {
    header("Location: ../login.php");
    exit;
}

include_once "../includes/config.php";
include_once "../includes/header.php";
include_once "../includes/sidebar.php";



// Get live counts
$traineeCount = $conn->query("SELECT COUNT(*) AS total FROM Trainees")->fetch_assoc()['total'] ?? 0;
$courseCount = $conn->query("SELECT COUNT(*) AS total FROM Courses")->fetch_assoc()['total'] ?? 0;
$instructorCount = $conn->query("SELECT COUNT(*) AS total FROM vw_Instructors")->fetch_assoc()['total'] ?? 0;
$groupCount = $conn->query("SELECT COUNT(*) AS total FROM Groups")->fetch_assoc()['total'] ?? 0;
?>

<main class="main-content p-4">
  <h1 class="mb-4">Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>

  <div class="row g-4">
    <div class="col-md-3">
      <div class="card shadow text-center">
        <div class="card-body">
          <h5 class="card-title">Trainees</h5>
          <p class="display-4"><?= $traineeCount ?></p>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow text-center">
        <div class="card-body">
          <h5 class="card-title">Courses</h5>
          <p class="display-4"><?= $courseCount ?></p>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow text-center">
        <div class="card-body">
          <h5 class="card-title">Instructors</h5>
          <p class="display-4"><?= $instructorCount ?></p>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow text-center">
        <div class="card-body">
          <h5 class="card-title">Groups</h5>
          <p class="display-4"><?= $groupCount ?></p>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include_once "../includes/footer.php"; ?>

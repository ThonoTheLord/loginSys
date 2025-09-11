<?php 
// session_start();
if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) !== 'login.php') {
  header("Location: login.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Digi's Clock'in</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link href="css/style.css" rel="stylesheet">
</head>

<body>
  <nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
      <a class="navbar-brand me-auto" href="#">iClocked</a>
      <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel ">
        <div class="offcanvas-header">
          <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Digital CitiZen's</h5>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
          <ul class="navbar-nav justify-content-center flex-grow-1 pe-3">
            <li class="nav-item">
              <a class="nav-link mx-lg-2 <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">Dashboard</a>
            </li>

            <?php if (isset($_SESSION['role'])): ?>
              <?php if ($_SESSION['role'] === 'admin'): ?>
                <li class="nav-item">
                  <a class="nav-link mx-lg-2 <?= basename($_SERVER['PHP_SELF']) === 'manage_employees.php' ? 'active' : '' ?>" href="manage_employees.php">Manage Employees</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link mx-lg-2 <?= basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : '' ?>" href="reports.php">Reports</a>
                </li>
              <?php else: ?>
                <li class="nav-item">
                  <a class="nav-link mx-lg-2 <?= basename($_SERVER['PHP_SELF']) === 'history.php' ? 'active' : '' ?>" href="history.php">Clock-in History</a>
                </li>
              <?php endif; ?>

              <li class="nav-item">
                <a class="nav-link mx-lg-2 <?= basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : '' ?>" href="profile.php">Profile</a>
              </li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
      <?php if (isset($_SESSION['user_id'])): ?>
        <div class="d-flex align-items-center">
          <!-- <span class="me-3 text-light">Welcome, <?= htmlspecialchars($_SESSION['name'] ?? 'User') ?></span> -->
          <a class="logout-button" href="logout.php">Logout</a>
        </div>
      <?php endif; ?>
      <button class="navbar-toggler pe-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
    </div>
  </nav>
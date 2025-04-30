<?php
session_start();
include("connection.php");
include("functions.php");
// If you have a check_login function, call it here:
// check_login($con);

$pageTitle = 'Home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo htmlspecialchars($pageTitle); ?> | Statimate</title>

  <!-- Google Font -->
  <link 
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" 
    rel="stylesheet"
  />

  <!-- Bootstrap 5 CSS -->
  <link 
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" 
    rel="stylesheet"
  />

  <!-- Font Awesome -->
  <link 
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" 
    rel="stylesheet"
  />

  <!-- Custom overrides -->
  <link href="assets/css/custom.css" rel="stylesheet" />
</head>
<body class="bg-light" style="font-family: 'Inter', sans-serif;">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand" href="index.php">
        <i class="fas fa-drafting-compass"></i> Statimate
      </a>
      <button 
        class="navbar-toggler" 
        type="button" 
        data-bs-toggle="collapse" 
        data-bs-target="#mainNav"
        aria-controls="mainNav" 
        aria-expanded="false" 
        aria-label="Toggle navigation"
      >
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="events.php">Events</a></li>
          <li class="nav-item"><a class="nav-link" href="stat_sheet.php">Stat Sheet</a></li>
          <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
          <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Main Content -->
  <div class="container my-5">
    <div class="text-center mb-5">
      <h1 class="display-5">Welcome to Statimate!</h1>
      <p class="lead">Track games, collect stats, and analyze player performance.</p>
    </div>

    <div class="row g-4">
      <div class="col-md-4">
        <a href="teams.php" class="btn btn-primary w-100 py-4 shadow-sm">
          <i class="fas fa-chart-bar fa-2x mb-2"></i><br>
          Create A Team
        </a>
      </div>
      <div class="col-md-4">
        <a href="events.php" class="btn btn-success w-100 py-4 shadow-sm">
          <i class="fas fa-calendar-alt fa-2x mb-2"></i><br>
          Manage Events
        </a>
      </div>
      <div class="col-md-4">
        <a href="stat_sheet.php" class="btn btn-info w-100 py-4 shadow-sm text-white">
          <i class="fas fa-file-alt fa-2x mb-2"></i><br>
          View Stat Sheets
        </a>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS Bundle -->
  <script 
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
  ></script>
</body>
</html>

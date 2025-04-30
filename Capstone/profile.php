<?php
session_start();
include("connection.php");

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user info
$user_id = $_SESSION['user_id'];
$stmt = $con->prepare("SELECT user_name, email FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch this user’s teams
$stmt = $con->prepare("
  SELECT team_id, team_name, team_colors, team_manager 
  FROM teams 
  WHERE created_by = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$teams = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Profile | Statimate</title>

  <!-- Google Font -->
  <link 
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" 
    rel="stylesheet"
  />
  <!-- Bootstrap CSS -->
  <link 
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" 
    rel="stylesheet"
  />
  <!-- Font Awesome -->
  <link 
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" 
    rel="stylesheet"
  />
  <!-- Custom CSS -->
  <link href="assets/css/custom.css" rel="stylesheet" />
</head>
<body class="bg-light" style="font-family:'Inter',sans-serif;">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand" href="index.php">
        <i class="fas fa-drafting-compass me-1"></i> Statimate
      </a>
      <button class="navbar-toggler" type="button" 
              data-bs-toggle="collapse" data-bs-target="#mainNav" 
              aria-controls="mainNav" aria-expanded="false" 
              aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="events.php">Events</a></li>
          <li class="nav-item"><a class="nav-link" href="teams.php">Teams</a></li>
          <li class="nav-item"><a class="nav-link" href="stat_sheet.php">Stat Sheets</a></li>
          <li class="nav-item"><a class="nav-link active" href="#">Profile</a></li>
          <li class="nav-item">
            <a class="nav-link" href="logout.php">
              <i class="fas fa-sign-out-alt"></i> Logout
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Main Container -->
  <div class="container my-5">
    <div class="row gy-4">

      <!-- User Info Card -->
      <div class="col-12">
        <div class="card shadow-sm">
          <div class="card-body text-center">
            <h3 class="card-title mb-1">Welcome, <?= htmlspecialchars($user_data['user_name']) ?></h3>
            <p class="text-muted mb-0"><i class="fas fa-envelope me-1"></i><?= htmlspecialchars($user_data['email']) ?></p>
          </div>
        </div>
      </div>

      <!-- Teams List -->
      <div class="col-12">
        <div class="card shadow-sm">
          <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Your Teams</h5>
          </div>
          <div class="card-body">
            <?php if (count($teams) > 0): ?>
              <div class="row g-3">
                <?php foreach ($teams as $t): ?>
                  <div class="col-md-6">
                    <div class="card h-100">
                      <div class="card-body">
                        <h6 class="card-title"><?= htmlspecialchars($t['team_name']) ?></h6>
                        <p class="card-text mb-1"><strong>Colors:</strong> <?= htmlspecialchars($t['team_colors']) ?></p>
                        <p class="card-text mb-3"><strong>Coach:</strong> <?= htmlspecialchars($t['team_manager']) ?></p>
                        <a 
                          href="edit_team.php?team_id=<?= $t['team_id'] ?>" 
                          class="btn btn-outline-primary btn-sm"
                        >
                          <i class="fas fa-edit me-1"></i> Edit Team
                        </a>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <p class="text-center text-muted mb-0">You have not created any teams yet.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- Bootstrap JS -->
  <script 
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
  ></script>
</body>
</html>

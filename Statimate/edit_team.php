<?php
session_start();
include("connection.php");

// Ensure the user is logged in.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$team_id = $_GET['team_id'] ?? '';

if (empty($team_id)) {
    echo "Team ID is missing.";
    exit();
}

// Fetch team details
$stmt = $con->prepare("
  SELECT team_id, team_name, team_colors, team_manager 
  FROM teams 
  WHERE team_id = ? AND created_by = ?
");
$stmt->bind_param("ii", $team_id, $user_id);
$stmt->execute();
$team = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$team) {
    echo "Team not found or you do not have permission to edit this team.";
    exit();
}

// Fetch roster
$stmt = $con->prepare("SELECT player_id, first_name, last_name FROM players WHERE team_id = ?");
$stmt->bind_param("i", $team_id);
$stmt->execute();
$players = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Edit Team | Statimate</title>
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
  <link href="assets/css/custom.css" rel="stylesheet"/>
</head>
<body class="bg-light" style="font-family:'Inter',sans-serif;">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand" href="index.php">
        <i class="fas fa-drafting-compass"></i> Statimate
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
          <li class="nav-item"><a class="nav-link" href="stat_sheet.php">Stat Sheets</a></li>
          <li class="nav-item"><a class="nav-link active" href="#">Profile</a></li>
          <li class="nav-item"><a class="nav-link" href="logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
          </a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Main Container -->
  <div class="container my-5">

    <!-- Team Details -->
    <div class="card shadow-sm mb-4">
      <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-pencil-alt me-2"></i>Edit Team Details</h5>
      </div>
      <div class="card-body">
        <?php if (!empty($success)): ?>
          <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" class="row g-3">
          <div class="col-md-4">
            <label for="team_name" class="form-label">Team Name</label>
            <input type="text" id="team_name" name="team_name"
                   class="form-control" 
                   value="<?= htmlspecialchars($team['team_name']) ?>" required>
          </div>
          <div class="col-md-4">
            <label for="team_colors" class="form-label">Team Colors</label>
            <input type="text" id="team_colors" name="team_colors"
                   class="form-control" 
                   value="<?= htmlspecialchars($team['team_colors']) ?>" required>
          </div>
          <div class="col-md-4">
            <label for="team_manager" class="form-label">Manager</label>
            <input type="text" id="team_manager" name="team_manager"
                   class="form-control"
                   value="<?= htmlspecialchars($team['team_manager']) ?>" required>
          </div>
          <div class="col-12 text-end">
            <button type="submit" name="update_team" class="btn btn-primary">
              <i class="fas fa-save me-1"></i> Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Add New Player -->
    <div class="card shadow-sm mb-4">
      <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Add New Player</h5>
      </div>
      <div class="card-body">
        <form method="POST" class="row g-3">
          <div class="col-md-4">
            <label class="form-label">First Name</label>
            <input type="text" name="first_name" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Last Name</label>
            <input type="text" name="last_name" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Number</label>
            <input type="number" name="number" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Line</label>
            <select name="line" class="form-select" required>
              <option value="">Choose...</option>
              <option value="O">O</option>
              <option value="D">D</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Position</label>
            <select name="position" class="form-select" required>
              <option value="">Choose...</option>
              <option value="H">H</option>
              <option value="C">C</option>
              <option value="Hy">Hy</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Nickname (optional)</label>
            <input type="text" name="nickname" class="form-control">
          </div>
          <div class="col-12 text-end">
            <button type="submit" name="add_player" class="btn btn-success">
              <i class="fas fa-plus me-1"></i> Add Player
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Roster List -->
    <div class="card shadow-sm">
      <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Team Roster</h5>
      </div>
      <div class="card-body">
        <?php if (count($players) === 0): ?>
          <p class="text-muted">No players in this team yet.</p>
        <?php else: ?>
          <ul class="list-group">
            <?php foreach ($players as $p): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?>
                <form method="POST" class="m-0">
                  <input type="hidden" name="player_id" value="<?= $p['player_id'] ?>">
                  <button type="submit" name="delete_player" 
                          class="btn btn-sm btn-danger">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </form>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>

  </div><!-- /.container -->

  <script 
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
  ></script>
</body>
</html>

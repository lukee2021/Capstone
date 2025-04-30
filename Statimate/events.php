<?php
session_start();
include("connection.php");

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$pageTitle = 'Manage Events';
$user_id   = $_SESSION['user_id'];

// Fetch teams belonging to this user
$stmt = $con->prepare("SELECT team_id, team_name FROM teams WHERE created_by = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$teams = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
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

  <!-- Custom Overrides -->
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
          <li class="nav-item"><a class="nav-link active" href="events.php">Events</a></li>
          <li class="nav-item"><a class="nav-link" href="stat_sheet.php">Stat Sheets</a></li>
          <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
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
    <h2 class="text-center mb-4">Let's Get Started!</h2>
    <form onsubmit="return false;">
      <!-- Team Selection -->
      <div class="mb-3">
        <label for="team" class="form-label">Select Team</label>
        <select id="team" class="form-select" required>
          <option value="">-- Select a Team --</option>
          <?php foreach ($teams as $t): ?>
            <option value="<?php echo $t['team_id']; ?>">
              <?php echo htmlspecialchars($t['team_name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Event Type Selection -->
      <div class="mb-3">
        <label for="event_type" class="form-label">Event Type</label>
        <select 
          id="event_type" 
          class="form-select" 
          onchange="toggleEventOptions()"
          required
        >
          <option value="">-- Select Event Type --</option>
          <option value="2day_tournament">2-Day Tournament</option>
          <option value="1day_round_robin">1-Day Round Robin</option>
        </select>
      </div>

      <!-- 2-Day Tournament Options -->
      <div id="options_2day" class="mb-4 d-none">
        <div class="mb-3">
          <label for="tournament_name_2day" class="form-label">Tournament Name</label>
          <input 
            type="text" 
            id="tournament_name_2day" 
            class="form-control" 
            placeholder="Enter tournament name"
          />
        </div>
        <div class="mb-3">
          <label for="opponent_2day" class="form-label">Opponent Name</label>
          <input 
            type="text" 
            id="opponent_2day" 
            class="form-control" 
            placeholder="Enter opponent name"
          />
        </div>
        <button 
          type="button" 
          class="btn btn-primary w-100"
          onclick="startGame()"
        >
          <i class="fas fa-play me-2"></i> Start Tournament
        </button>
      </div>

      <!-- 1-Day Round Robin Options -->
      <div id="options_rr" class="mb-4 d-none">
        <div class="mb-3">
          <label for="tournament_name_rr" class="form-label">Tournament Name</label>
          <input 
            type="text" 
            id="tournament_name_rr" 
            class="form-control" 
            placeholder="Enter tournament name"
          />
        </div>
        <div class="mb-3">
          <label for="opponent_rr" class="form-label">Opponent Name</label>
          <input 
            type="text" 
            id="opponent_rr" 
            class="form-control" 
            placeholder="Enter opponent name"
          />
        </div>
        <button 
          type="button" 
          class="btn btn-success w-100"
          onclick="startRoundRobin()"
        >
          <i class="fas fa-retweet me-2"></i> Start Round Robin
        </button>
      </div>
    </form>
  </div>

  <!-- Bootstrap JS Bundle -->
  <script 
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
  ></script>
  <script>
    function toggleEventOptions() {
      const val = document.getElementById('event_type').value;
      document.getElementById('options_2day')
        .classList.toggle('d-none', val !== '2day_tournament');
      document.getElementById('options_rr')
        .classList.toggle('d-none', val !== '1day_round_robin');
    }

    function startGame() {
      const team = document.getElementById('team').value,
            tn   = document.getElementById('tournament_name_2day').value.trim(),
            op   = document.getElementById('opponent_2day').value.trim();

      if (!team || !tn || !op) {
        return alert("Please fill in all fields before starting the tournament.");
      }

      localStorage.setItem("team_score", "0");
      localStorage.setItem("opponent_score", "0");

      window.location.href = 
        `game.php?team_id=${team}` +
        `&event_type=2day_tournament` +
        `&tournament_name=${encodeURIComponent(tn)}` +
        `&opponent=${encodeURIComponent(op)}`;
    }

    function startRoundRobin() {
      const team = document.getElementById('team').value,
            tn   = document.getElementById('tournament_name_rr').value.trim(),
            op   = document.getElementById('opponent_rr').value.trim();

      if (!team || !tn || !op) {
        return alert("Please fill in all fields before starting the round robin.");
      }

      localStorage.setItem("team_score", "0");
      localStorage.setItem("opponent_score", "0");

      window.location.href = 
        `game.php?team_id=${team}` +
        `&event_type=1day_round_robin` +
        `&tournament_name=${encodeURIComponent(tn)}` +
        `&opponent=${encodeURIComponent(op)}`;
    }
  </script>
</body>
</html>

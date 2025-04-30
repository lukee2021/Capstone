<?php
session_start();
require_once("connection.php");
include("get_players.php");

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch this user’s team ID
$user_id = $_SESSION['user_id'];
$stmt = $con->prepare("SELECT team_id FROM users WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($team_id);
$stmt->fetch();
$stmt->close();

// Fetch players for that team
$stmt = $con->prepare("SELECT player_id AS id, CONCAT(first_name,' ',last_name) AS name FROM players WHERE team_id = ?");
$stmt->bind_param("i", $team_id);
$stmt->execute();
$result = $stmt->get_result();
$players = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Stat Collection | Statimate</title>

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
  <!-- Custom Overrides -->
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
          <li class="nav-item"><a class="nav-link" href="teams.php">Teams</a></li>
          <li class="nav-item"><a class="nav-link active" href="#">Stats</a></li>
          <li class="nav-item"><a class="nav-link" href="stat_sheet.php">Stat Sheets</a></li>
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
    <div class="card shadow-sm">
      <div class="card-header text-center">
        <h2 class="mb-0">Stat Collection</h2>
      </div>
      <div class="card-body">
        <div class="mb-4">
          <input 
            type="text" 
            id="search" 
            class="form-control" 
            placeholder="Search players..." 
            onkeyup="filterPlayers()"
          />
        </div>
        <div id="playerList" class="row g-3">
          <?php foreach ($players as $p): ?>
            <div 
              class="col-md-6" 
              data-name="<?= strtolower($p['name']) ?>"
            >
              <div class="card h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                  <strong><?= htmlspecialchars($p['name']) ?></strong>
                  <div class="btn-group btn-group-sm" role="group">
                    <button 
                      class="btn btn-success" 
                      onclick="updateStat(<?= $p['id'] ?>,'goal',1)"
                    >
                      <i class="fas fa-plus"></i>
                    </button>
                    <button 
                      class="btn btn-danger" 
                      onclick="updateStat(<?= $p['id'] ?>,'goal',-1)"
                    >
                      <i class="fas fa-minus"></i>
                    </button>
                  </div>
                  <!-- repeat similarly for assist/block if desired -->
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script 
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
  ></script>
  <script>
    function filterPlayers() {
      const q = document.getElementById('search').value.toLowerCase();
      document.querySelectorAll('#playerList [data-name]').forEach(card => {
        card.style.display = 
          card.dataset.name.includes(q) ? 'block' : 'none';
      });
    }

    function updateStat(playerId, stat, value) {
      fetch('update_stats.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `player_id=${playerId}&stat=${stat}&value=${value}`
      })
      .then(r => r.text())
      .then(console.log)
      .catch(console.error);
    }
  </script>
</body>
</html>

<?php
session_start();
include("connection.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$pageTitle    = 'Collect Stats';
$game_id      = $_GET['game_id'] ?? '';
$selected_ids = explode(',', $_GET['players'] ?? '');

// Fetch game details
$tournament_name = 'Unknown Tournament';
$opponent        = 'Unknown Opponent';
if ($game_id) {
    $q = "SELECT tournament_name, opponent FROM games WHERE game_id = ?";
    $stmt = $con->prepare($q);
    $stmt->bind_param("i", $game_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row) {
        $tournament_name = $row['tournament_name'];
        $opponent        = $row['opponent'];
    }
    $stmt->close();
}

// Fetch player info
$players_info = [];
if ($selected_ids) {
    $in = implode(',', array_fill(0, count($selected_ids), '?'));
    $types = str_repeat('i', count($selected_ids));
    $q = "SELECT player_id, first_name, last_name FROM players WHERE player_id IN ($in)";
    $stmt = $con->prepare($q);
    $stmt->bind_param($types, ...$selected_ids);
    $stmt->execute();
    $players_info = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?> | Statimate</title>

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
  <!-- Custom CSS -->
  <link href="assets/css/custom.css" rel="stylesheet" />
</head>
<body class="bg-light" style="font-family:'Inter',sans-serif;">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand" href="index.php">
        <i class="fas fa-drafting-compass"></i> Statimate
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
              data-bs-target="#mainNav" aria-controls="mainNav" 
              aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="events.php">Events</a></li>
          <li class="nav-item"><a class="nav-link active" href="#">Stats</a></li>
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
    <div class="card shadow-sm">
      <div class="card-body">
        <h3 class="card-title">Track Stats for Game #<?= htmlspecialchars($game_id) ?></h3>
        <p class="mb-1"><strong>Tournament:</strong> <?= htmlspecialchars($tournament_name) ?></p>
        <p><strong>Opponent:</strong> <?= htmlspecialchars($opponent) ?></p>

        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle mt-4">
            <thead class="table-dark">
              <tr>
                <th>Player</th>
                <th>Goals</th>
                <th>Assists</th>
                <th>Throwaways</th>
                <th>Drops</th>
                <th>Blocks</th>
                <th>Completed Passes</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($players_info as $pl): ?>
                <?php $pid = $pl['player_id']; ?>
                <tr data-player="<?= $pid ?>">
                  <td><?= htmlspecialchars($pl['first_name'].' '.$pl['last_name']) ?></td>
                  <?php foreach (['goal','assist','throwaway','drop','block','completed-pass'] as $stat): ?>
                    <td>
                      <div class="btn-group btn-group-sm" role="group">
                        <button 
                          class="btn btn-outline-secondary" 
                          onclick="updateStat(<?= $pid ?>,'<?= $stat ?>',-1)">
                          <i class="fas fa-minus"></i>
                        </button>
                        <span id="<?= $stat ?>_<?= $pid ?>" class="px-2">0</span>
                        <button 
                          class="btn btn-outline-secondary" 
                          onclick="updateStat(<?= $pid ?>,'<?= $stat ?>',1)">
                          <i class="fas fa-plus"></i>
                        </button>
                      </div>
                    </td>
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-footer text-end">
        <button class="btn btn-primary" onclick="saveStats()">
          <i class="fas fa-save me-1"></i> Save Stats
        </button>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS & jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script 
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
  ></script>
  <script>
    function updateStat(playerId, stat, change) {
      const el = document.getElementById(`${stat}_${playerId}`);
      let val = parseInt(el.textContent) || 0;
      val = Math.max(0, val + change);
      el.textContent = val;
    }

    function saveStats() {
      const stats = [];
      document.querySelectorAll('tbody tr').forEach(row => {
        const pid = row.dataset.player;
        stats.push({
          player_id: pid,
          goals: parseInt(document.getElementById(`goal_${pid}`).textContent),
          assists: parseInt(document.getElementById(`assist_${pid}`).textContent),
          throwaways: parseInt(document.getElementById(`throwaway_${pid}`).textContent),
          drops: parseInt(document.getElementById(`drop_${pid}`).textContent),
          blocks: parseInt(document.getElementById(`block_${pid}`).textContent),
          completes: parseInt(document.getElementById(`completed-pass_${pid}`).textContent)
        });
      });

      $.post("save_stats.php", {
        game_id: '<?= $game_id ?>',
        stats: JSON.stringify(stats)
      }).done(response => {
        alert("Stats saved!");
        window.location.href = `game.php?game_id=<?= $game_id ?>`;
      }).fail((_,__,err) => {
        alert("Error: " + err);
      });
    }
  </script>
</body>
</html>

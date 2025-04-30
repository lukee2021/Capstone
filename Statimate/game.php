<?php
session_start();
include("connection.php");

// Get game details from URL
$team_id = $_GET['team_id'] ?? '';
$event_type = $_GET['event_type'] ?? '';
$game_id = $_GET['game_id'] ?? '';
$tournament_name = $_GET['tournament_name'] ?? 'Unknown Tournament';  // Default value
$opponent = $_GET['opponent'] ?? 'Unknown Opponent';  // Default value

// Fetch team name and insert game if necessary
$team_name = 'Unknown Team';  // Default value
$players = [];
if ($team_id) {
    $team_query = "SELECT team_name FROM teams WHERE team_id = '$team_id'";
    $result = mysqli_query($con, $team_query);
    if ($result && mysqli_num_rows($result) > 0) {
        $team_data = mysqli_fetch_assoc($result);
        $team_name = $team_data['team_name'];
    }}

    // Fetch players from this team
    $player_query = "SELECT player_id, first_name, last_name FROM players WHERE team_id = '$team_id'";
    $player_result = mysqli_query($con, $player_query);
    $players = mysqli_fetch_all($player_result, MYSQLI_ASSOC);

    // Insert new game into the database (if new game is being created)
    if (!empty($game_id)) {
        $game_query = "SELECT team_id, tournament_name, opponent FROM games WHERE game_id = ?";
        $stmt = $con->prepare($game_query);
        $stmt->bind_param("i", $game_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $team_id = $row['team_id'];
            $tournament_name = $row['tournament_name'];
            $opponent = $row['opponent'];
            error_log("game.php - Found game: Team ID: $team_id, Tournament: $tournament_name, Opponent: $opponent");
        } else {
            error_log("game.php - No game found for game_id: $game_id");
        }
    } else {
        error_log("game.php - game_id is empty or missing");
    }
    if (!empty($team_id)) {  // Ensure team_id exists before querying
        // Fetch team name
        $team_query = "SELECT team_name FROM teams WHERE team_id = ?";
        $stmt = $con->prepare($team_query);
        $stmt->bind_param("i", $team_id);
        $stmt->execute();
        $team_result = $stmt->get_result();

        if ($team_result->num_rows > 0) {
            $team_data = $team_result->fetch_assoc();
            $team_name = $team_data['team_name'];
        }

        // Fetch players for this team
        $player_query = "SELECT player_id, first_name, last_name FROM players WHERE team_id = ?";
        $stmt = $con->prepare($player_query);
        $stmt->bind_param("i", $team_id);
        $stmt->execute();
        $player_result = $stmt->get_result();
        $players = $player_result->fetch_all(MYSQLI_ASSOC);
    }
    if (empty($game_id) && !empty($team_id)) {
    $insert_game_query = "INSERT INTO games (team_id, event_type, tournament_name, opponent) VALUES (?, ?, ?, ?)";
    $stmt = $con->prepare($insert_game_query);
    $stmt->bind_param("isss", $team_id, $event_type, $tournament_name, $opponent);

    if ($stmt->execute()) {
        $game_id = $stmt->insert_id;  // Get the new game_id
        error_log("game.php - New game created with ID: $game_id");
    } else {
        error_log("game.php - Game insertion failed: " . $stmt->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Game Tracker | Statimate</title>

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
<body class="bg-light" style="font-family: 'Inter', sans-serif;">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand" href="index.php">
        <i class="fas fa-drafting-compass"></i> Statimate
      </a>
      <button 
        class="navbar-toggler" type="button" 
        data-bs-toggle="collapse" data-bs-target="#mainNav"
        aria-controls="mainNav" aria-expanded="false" 
        aria-label="Toggle navigation"
      >
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="events.php">Events</a></li>
          <li class="nav-item"><a class="nav-link active" href="#">Game</a></li>
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

  <!-- Main Content -->
  <div class="container my-5">
    <div class="card shadow-sm">
      <div class="card-body text-center">
        <h3 class="card-title mb-1"><?php echo htmlspecialchars($tournament_name); ?></h3>
        <h5 class="card-subtitle text-muted mb-4">
          <?php echo htmlspecialchars($team_name); ?> vs <?php echo htmlspecialchars($opponent); ?>
        </h5>

        <!-- Score Display -->
        <div class="d-flex justify-content-center align-items-center mb-4">
          <button class="btn btn-outline-primary me-2" id="subtractTeamScoreBtn">
            <i class="fas fa-minus"></i>
          </button>
          <span class="fs-2 px-3" id="team_score">0</span>
          <button class="btn btn-outline-primary me-4" id="addTeamScoreBtn">
            <i class="fas fa-plus"></i>
          </button>

          <button class="btn btn-outline-danger me-2" id="subtractOpponentScoreBtn">
            <i class="fas fa-minus"></i>
          </button>
          <span class="fs-2 px-3" id="opponent_score">0</span>
          <button class="btn btn-outline-danger" id="addOpponentScoreBtn">
            <i class="fas fa-plus"></i>
          </button>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-center gap-3">
          <button class="btn btn-secondary" id="startGameBtn">
            <i class="fas fa-users"></i> Select Players
          </button>
          <button class="btn btn-success" id="finishGameBtn">
            <i class="fas fa-flag-checkered"></i> Finish Game
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Player Selection Modal -->
  <div 
    class="modal fade" id="playerModal" tabindex="-1" 
    aria-labelledby="playerModalLabel" aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="playerModalLabel">
            Select 7 Players from <?php echo htmlspecialchars($team_name); ?>
          </h5>
          <button 
            type="button" class="btn-close" 
            data-bs-dismiss="modal" aria-label="Close"
          ></button>
        </div>
        <div class="modal-body">
          <div class="mb-3 d-flex justify-content-between">
            <button 
              type="button" class="btn btn-primary" id="startPointBtn" 
              disabled onclick="startPoint()"
            >
              Start Point
            </button>
          </div>
          <input 
            type="text" id="searchPlayer" 
            class="form-control mb-3" 
            placeholder="Search players..." 
            onkeyup="filterPlayers()"
          />
          <div class="list-group player-list" id="playerList">
            <?php foreach ($players as $p): ?>
              <button 
                type="button" 
                class="list-group-item list-group-item-action player-item" 
                data-player-id="<?php echo $p['player_id']; ?>"
                onclick="selectPlayer(this, <?php echo $p['player_id']; ?>)"
              >
                <?php echo htmlspecialchars($p['first_name'] . ' ' . $p['last_name']); ?>
              </button>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS & your script -->
  <script 
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
  ></script>
  <script>
    let selectedPlayers = [], lastSelectedPlayers = [];
    const playerModal = new bootstrap.Modal(document.getElementById('playerModal'));

    document.getElementById('startGameBtn').onclick = () => {
      selectedPlayers = [];
      lastSelectedPlayers = JSON.parse(localStorage.getItem('lastSelectedPlayers')||'[]');
      document.getElementById('startPointBtn').disabled = true;
      document.querySelectorAll('.player-item').forEach(el=>{
        el.classList.toggle('active', false);
      });
      playerModal.show();
    };

    function selectPlayer(el, pid) {
      const idx = selectedPlayers.indexOf(pid);
      if (idx > -1) {
        selectedPlayers.splice(idx,1);
        el.classList.remove('active');
      } else if (selectedPlayers.length < 7) {
        selectedPlayers.push(pid);
        el.classList.add('active');
      }
      document.getElementById('startPointBtn').disabled = (selectedPlayers.length !== 7);
    }

    function filterPlayers() {
      const q = document.getElementById('searchPlayer').value.toLowerCase();
      document.querySelectorAll('.player-item').forEach(el=>{
        el.style.display = el.textContent.toLowerCase().includes(q)?'':'none';
      });
    }

    function confirmSame7() {
      if (lastSelectedPlayers.length !== 7) {
        return alert('No previous selection available.');
      }
      if (!confirm('Use the same 7 players for the next point?')) return;
      selectedPlayers = [...lastSelectedPlayers];
      document.querySelectorAll('.player-item').forEach(el=>{
        const pid = Number(el.dataset.playerId);
        el.classList.toggle('active', selectedPlayers.includes(pid));
      });
      document.getElementById('startPointBtn').disabled = false;
    }

    function startPoint() {
      lastSelectedPlayers = [...selectedPlayers];
      localStorage.setItem('lastSelectedPlayers', JSON.stringify(lastSelectedPlayers));
      playerModal.hide();
      if (selectedPlayers.length !== 7) return alert('Select exactly 7 players.');
      const gid = '<?php echo $game_id;?>';
      window.location.href = `stat_gathering.php?game_id=${gid}&players=${selectedPlayers.join(',')}`;
    }

    // Score buttons & finish logic (unchanged, but styled)
    function updateScore(team, delta) {
      const el = document.getElementById(team+'_score');
      let val = parseInt(el.textContent)||0;
      val = Math.max(0,val+delta);
      el.textContent=val;
      localStorage.setItem(team+'_score',val);
      playerModal.show();
    }
    document.getElementById('addTeamScoreBtn').onclick=()=>updateScore('team',1);
    document.getElementById('subtractTeamScoreBtn').onclick=()=>updateScore('team',-1);
    document.getElementById('addOpponentScoreBtn').onclick=()=>updateScore('opponent',1);
    document.getElementById('subtractOpponentScoreBtn').onclick=()=>updateScore('opponent',-1);

    window.onload = ()=>{
      ['team','opponent'].forEach(t=>{
        const v=localStorage.getItem(t+'_score');
        if (v!==null) document.getElementById(t+'_score').textContent=v;
      });
    };

    document.getElementById('finishGameBtn').onclick = ()=>{
      const teamScore = parseInt(document.getElementById('team_score').textContent),
            oppScore  = parseInt(document.getElementById('opponent_score').textContent),
            gid       = '<?php echo $game_id;?>';
      if (isNaN(teamScore)||isNaN(oppScore)) return alert('Invalid scores.');
      fetch('save_score.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`game_id=${gid}&team_score=${teamScore}&opponent_score=${oppScore}`
      }).then(r=>r.text()).then(txt=>{
        if (txt.trim()==='Score updated successfully.') {
          window.location.href='events.php';
        } else alert(txt);
      });
    };
  </script>
</body>
</html>
<?php
session_start();
require_once("connection.php");
include("get_players.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user's team and players
$user_id = $_SESSION['user_id'];
$team_query = "SELECT team_id FROM users WHERE user_id = '$user_id' LIMIT 1";
$team_result = mysqli_query($con, $team_query);
$team_data = mysqli_fetch_assoc($team_result);
$team_id = $team_data['team_id'];

$players_query = "SELECT * FROM players WHERE team_id = '$team_id'";
$players_result = mysqli_query($con, $players_query);
$players = mysqli_fetch_all($players_result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Stats | Ultimate Frisbee</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; }
        .container { max-width: 900px; margin: auto; padding: 20px; background: white; border-radius: 10px; box-shadow: 0px 0px 10px #ccc; }
        h2 { text-align: center; }

        .top-nav {
            background: #333;
            padding: 10px;
            text-align: right;
        }
        .top-nav a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
        }

        /* Second navigation bar */
        .second-nav {
            background: #eee;
            padding: 10px;
            text-align: center;
            border-bottom: 2px solid #ccc;
        }
        .second-nav a {
            color: #333;
            text-decoration: none;
            margin: 0 20px;
            font-weight: bold;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .second-nav a:hover {
            background: #ddd;
        }
        .active-tab {
            background: #ccc;
        }

        .search-bar { width: 100%; padding: 10px; margin-bottom: 10px; border-radius: 5px; border: 1px solid #ccc; }

        .player-card {
            display: flex;
            justify-content: space-between;
            background: #fff;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0px 2px 5px #ddd;
        }

        .stat-buttons button {
            margin: 0 5px;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .increase { background: green; color: white; }
        .decrease { background: red; color: white; }
    </style>
</head>
<body>

    <!-- Main Navigation Bar -->
    <div class="top-nav">
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </div>

    <!-- Second Navigation Bar -->
    <div class="second-nav">
        <a href="teams.php" class="active-tab">Teams</a>
        <a href="events.php">Events</a>
        <a href="stats.php">Stats</a>
    </div>

    <!-- Main Content -->
    <div class="container">
        <h2>Stat Collection</h2>

        <input type="text" class="search-bar" id="search" placeholder="Search Players..." onkeyup="filterPlayers()">
        
        <div id="playerList">
            <?php foreach ($players as $player) { ?>
                <div class="player-card" data-name="<?php echo strtolower($player['name']); ?>">
                    <span><strong><?php echo $player['name']; ?></strong></span>
                    <div class="stat-buttons">
                        <button class="increase" onclick="updateStat(<?php echo $player['id']; ?>, 'goal', 1)">+ Goal</button>
                        <button class="decrease" onclick="updateStat(<?php echo $player['id']; ?>, 'goal', -1)">- Goal</button>
                        <button class="increase" onclick="updateStat(<?php echo $player['id']; ?>, 'assist', 1)">+ Assist</button>
                        <button class="decrease" onclick="updateStat(<?php echo $player['id']; ?>, 'assist', -1)">- Assist</button>
                        <button class="increase" onclick="updateStat(<?php echo $player['id']; ?>, 'block', 1)">+ Block</button>
                        <button class="decrease" onclick="updateStat(<?php echo $player['id']; ?>, 'block', -1)">- Block</button>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <script>
        function filterPlayers() {
            let input = document.getElementById('search').value.toLowerCase();
            let players = document.querySelectorAll('.player-card');
            players.forEach(player => {
                let name = player.getAttribute('data-name');
                player.style.display = name.includes(input) ? '' : 'none';
            });
        }

        function updateStat(playerId, stat, value) {
            fetch('update_stats.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `player_id=${playerId}&stat=${stat}&value=${value}`
            }).then(response => response.text()).then(data => console.log(data));
        }
    </script>
</body>
</html>
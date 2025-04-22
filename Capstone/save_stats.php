<?php
ob_start(); // Start output buffering

session_start();
include("connection.php");

$game_id = $_POST['game_id'] ?? '';
$stats = json_decode($_POST['stats'], true);

// Loop through each player's stats and process them
foreach ($stats as $stat) {
    $player_id = $stat['player_id'];
    $goals = $stat['goals'];
    $assists = $stat['assists'];
    $throwaways = $stat['throwaways'];
    $drops = $stat['drops'];
    $blocks = $stat['blocks'];
    $completes = $stat['completes'];

    // Process goals
    if ($goals > 0) {
        $query = "SELECT * FROM player_stats WHERE game_id = '$game_id' AND player_id = '$player_id' AND stat_type = 'goal'";
        $result = mysqli_query($con, $query);
        if (mysqli_num_rows($result) > 0) {
            $update_query = "UPDATE player_stats SET value = value + $goals WHERE game_id = '$game_id' AND player_id = '$player_id' AND stat_type = 'goal'";
            mysqli_query($con, $update_query);
        } else {
            $insert_query = "INSERT INTO player_stats (game_id, player_id, stat_type, value, timestamp) VALUES ('$game_id', '$player_id', 'goal', '$goals', NOW())";
            mysqli_query($con, $insert_query);
        }
    }

    // Process assists
    if ($assists > 0) {
        $query = "SELECT * FROM player_stats WHERE game_id = '$game_id' AND player_id = '$player_id' AND stat_type = 'assist'";
        $result = mysqli_query($con, $query);
        if (mysqli_num_rows($result) > 0) {
            $update_query = "UPDATE player_stats SET value = value + $assists WHERE game_id = '$game_id' AND player_id = '$player_id' AND stat_type = 'assist'";
            mysqli_query($con, $update_query);
        } else {
            $insert_query = "INSERT INTO player_stats (game_id, player_id, stat_type, value, timestamp) VALUES ('$game_id', '$player_id', 'assist', '$assists', NOW())";
            mysqli_query($con, $insert_query);
        }
    }

    // Process throwaways
    if ($throwaways > 0) {
        $query = "SELECT * FROM player_stats WHERE game_id = '$game_id' AND player_id = '$player_id' AND stat_type = 'throwaway'";
        $result = mysqli_query($con, $query);
        if (mysqli_num_rows($result) > 0) {
            $update_query = "UPDATE player_stats SET value = value + $throwaways WHERE game_id = '$game_id' AND player_id = '$player_id' AND stat_type = 'throwaway'";
            mysqli_query($con, $update_query);
        } else {
            $insert_query = "INSERT INTO player_stats (game_id, player_id, stat_type, value, timestamp) VALUES ('$game_id', '$player_id', 'throwaway', '$throwaways', NOW())";
            mysqli_query($con, $insert_query);
        }
    }

    // Process drops
    if ($drops > 0) {
        $query = "SELECT * FROM player_stats WHERE game_id = '$game_id' AND player_id = '$player_id' AND stat_type = 'drop'";
        $result = mysqli_query($con, $query);
        if (mysqli_num_rows($result) > 0) {
            $update_query = "UPDATE player_stats SET value = value + $drops WHERE game_id = '$game_id' AND player_id = '$player_id' AND stat_type = 'drop'";
            mysqli_query($con, $update_query);
        } else {
            $insert_query = "INSERT INTO player_stats (game_id, player_id, stat_type, value, timestamp) VALUES ('$game_id', '$player_id', 'drop', '$drops', NOW())";
            mysqli_query($con, $insert_query);
        }
    }

    // Process blocks
    if ($blocks > 0) {
        $query = "SELECT * FROM player_stats WHERE game_id = '$game_id' AND player_id = '$player_id' AND stat_type = 'block'";
        $result = mysqli_query($con, $query);
        if (mysqli_num_rows($result) > 0) {
            $update_query = "UPDATE player_stats SET value = value + $blocks WHERE game_id = '$game_id' AND player_id = '$player_id' AND stat_type = 'block'";
            mysqli_query($con, $update_query);
        } else {
            $insert_query = "INSERT INTO player_stats (game_id, player_id, stat_type, value, timestamp) VALUES ('$game_id', '$player_id', 'block', '$blocks', NOW())";
            mysqli_query($con, $insert_query);
        }
    }

    // Process completed passes (using stat_type 'completed-pass')
    if ($completes > 0) {
        $query = "SELECT * FROM player_stats WHERE game_id = '$game_id' AND player_id = '$player_id' AND stat_type = 'completed-pass'";
        $result = mysqli_query($con, $query);
        if (mysqli_num_rows($result) > 0) {
            $update_query = "UPDATE player_stats SET value = value + $completes WHERE game_id = '$game_id' AND player_id = '$player_id' AND stat_type = 'completed-pass'";
            mysqli_query($con, $update_query);
        } else {
            $insert_query = "INSERT INTO player_stats (game_id, player_id, stat_type, value, timestamp) VALUES ('$game_id', '$player_id', 'completed-pass', '$completes', NOW())";
            mysqli_query($con, $insert_query);
        }
    }
}

// Redirect back to the previous page (game.php)
header("Location: game.php?game_id=$game_id");
exit; // Ensure no further code is executed after the header
?>

<?php
// update_score.php
session_start();
include('connection.php');

$game_id = $_POST['game_id'];
$team_score = $_POST['team_score'];
$opponent_score = $_POST['opponent_score'];

// Update the score in the database
$sql = "UPDATE games SET team_score = ?, opponent_score = ? WHERE game_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("iii", $team_score, $opponent_score, $game_id);

if ($stmt->execute()) {
    echo "Scores updated successfully.";
} else {
    echo "Error updating scores: " . $stmt->error;
}

$stmt->close();
?>

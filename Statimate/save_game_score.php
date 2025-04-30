<?php
session_start();
include("connection.php"); // Include your database connection

// Get the game_id, team_score, and opponent_score from the POST request
$game_id = $_POST['game_id'] ?? null;
$team_score = $_POST['team_score'] ?? null;
$opponent_score = $_POST['opponent_score'] ?? null;

// Check if all necessary variables are present
if ($game_id && $team_score !== null && $opponent_score !== null) {
    // Prepare the update query to set the scores
    $query = "UPDATE games SET team_score = ?, opponent_score = ? WHERE game_id = ?";
    
    // Prepare the statement
    $stmt = $con->prepare($query);
    
    // Bind the parameters to the query
    $stmt->bind_param("iii", $team_score, $opponent_score, $game_id);
    
    // Execute the query
    if ($stmt->execute()) {
        // Redirect or give success message
        header("Location: events.php"); // Redirect back to the events page
        exit();
    } else {
        // Error handling if the query fails
        echo "Error updating game score: " . $stmt->error;
    }
} else {
    // If any required parameters are missing
    echo "Missing required data!";
}
$_SESSION['team_score'] = 0;
$_SESSION['opponent_score'] = 0;
header("Location: events.php");  // Redirect back to events page
exit();
?>

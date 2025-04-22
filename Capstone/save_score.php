<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get POST data
    $game_id = isset($_POST['game_id']) ? $_POST['game_id'] : '';
    $team_score = isset($_POST['team_score']) ? $_POST['team_score'] : '';
    $opponent_score = isset($_POST['opponent_score']) ? $_POST['opponent_score'] : '';

    // Validate the data
    if (empty($game_id) || $team_score === '' || $opponent_score === '') {
        echo "Error: Missing data.";
        exit;
    }

    include('connection.php');

    // Prepare SQL
    $stmt = $con->prepare("UPDATE games SET team_score = ?, opponent_score = ? WHERE game_id = ?");
    $stmt->bind_param("iii", $team_score, $opponent_score, $game_id);

    // Execute query
    if ($stmt->execute()) {
        echo "Score updated successfully.";
    } else {
        echo "Error updating score: " . $stmt->error;
    }

    $stmt->close();
    $con->close();
} else {
    echo "Error: Invalid request method.";
}
?>

<?php
include("connection.php");

if (isset($_GET['game_id'])) {
    $game_id = $_GET['game_id'];
    $delete_query = "DELETE FROM games WHERE game_id = '$game_id'";

    if (mysqli_query($con, $delete_query)) {
        echo "Game deleted successfully.";
    } else {
        echo "Error deleting game: " . mysqli_error($con);
    }
}
?>

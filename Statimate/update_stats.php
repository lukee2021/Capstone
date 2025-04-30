<?php
session_start();
include("connection.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$player_id = $data['player_id'] ?? null;
$game_id = $data['game_id'] ??, '';
$stat = $data['stat'] ?? null;
$change = $data['change'] ?? 0;

if (!$player_id || !$game_id || !in_array($stat, ['goal', 'assist', 'throwaway', 'drop', 'huck', 'block', 'completed-pass'])) {
    echo json_encode(["error" => "Invalid data"]);
    exit;
}


$query = "INSERT INTO player_stats (game_id, player_id, stat_type, value) 
          VALUES (?, ?, ?, ?) 
          ON DUPLICATE KEY UPDATE value = value + VALUES(value)";

$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "iisi", $game_id, $player_id, $stat, $change);
mysqli_stmt_execute($stmt);

if (mysqli_stmt_affected_rows($stmt) > 0) {
    echo json_encode(["success" => true, "player_id" => $player_id, "stat" => $stat, "change" => $change]);
} else {
    echo json_encode(["error" => "Failed to update stat"]);
}
?>

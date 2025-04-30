<?php

function check_login($con)
{

	if(isset($_SESSION['user_id']))
	{

		$id = $_SESSION['user_id'];
		$query = "select * from users where user_id = '$id' limit 1";

		$result = mysqli_query($con, $query);
		if($result && mysqli_num_rows($result) > 0)
		{
			$user_data = mysqli_fetch_assoc($result);
			return $user_data;
		}
	}
	//redirect to login
	header("Location: login.php");
	die;

}
function random_num($length)
{
	$text = "";
	if($length < 5)
	{
		$length = 5;
	}

	$len = rand(4, $length);

	for ($i=0; $i < $len; $i++) { 
		// code...

		$text .= rand(0,9);

	}

	return $text;
}
require 'connection.php';

function getPlayersByTeam($team_id) {
    global $pdo; // Ensure $pdo is accessible

    $query = "SELECT player_id, player_name FROM players WHERE team_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$team_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify a plaintext password against a hash.
 *
 * @param string $password The plaintext password.
 * @param string $hash The hashed password.
 * @return bool True if the password matches the hash, false otherwise.
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

?>
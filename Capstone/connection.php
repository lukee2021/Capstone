<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "frisbeestats_capstone";

// Create connection
$con = new mysqli($servername, $username, $password, $dbname);

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
<?php
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "chat_app";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
  die("Connection Failed: " . mysqli_connect_error());
}
?>
<?php
include '../db/db.php';

// Supposons qu'on reçoit les données via POST de l'API du terminal
$user_id = $_POST['user_id'];
$access_type = $_POST['access_type']; // "entry" ou "exit"

$sql = "INSERT INTO access_log (user_id, access_type) VALUES ('$user_id','$access_type')";
$conn->query($sql);
?>

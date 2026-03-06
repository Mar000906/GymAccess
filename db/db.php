<?php
// Paramètres de connexion
$servername = "localhost";
$username = "root"; // utilisateur MySQL par défaut
$password = "";     // mot de passe MySQL (vide si par défaut XAMPP)
$database = "gymaccess";

// Créer la connexion
$conn = new mysqli($servername, $username, $password, $database);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

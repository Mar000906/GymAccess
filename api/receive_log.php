<?php
include '../db/db.php';

// Vérification des données envoyées par l'appareil
if (!isset($_REQUEST['type']) || (!isset($_REQUEST['user_id']) && !isset($_REQUEST['employeeNo']))) {
    die("Missing data");
}

$type = $_REQUEST['type']; // entry ou exit

// Priorité : user_id direct, sinon employeeNo
if (isset($_REQUEST['user_id'])) {
    $user_id = intval($_REQUEST['user_id']);
} else {
    $employeeNo = intval($_REQUEST['employeeNo']);
    // Récupérer l'user_id correspondant à employeeNo
    $res = $conn->query("SELECT id FROM users WHERE id = $employeeNo LIMIT 1");
    if ($res->num_rows === 0) {
        die("User not found");
    }
    $row = $res->fetch_assoc();
    $user_id = intval($row['id']);
}

// Insérer le log
$stmt = $conn->prepare("INSERT INTO access_log (user_id, access_type, access_time) VALUES (?, ?, NOW())");
$stmt->bind_param("is", $user_id, $type);
$stmt->execute();

// Réponse à l'appareil
echo "OK";
?>

<?php

// Connexion base de données
$conn = new mysqli("localhost", "root", "", "gymaccess");

if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}

// API Hikvision
$url = "http://uniondev1.ddns.net:8090/ISAPI/AccessControl/AcsEvent?format=json";

$data = '{
    "AcsEventCond": {
        "searchID": "1",
        "searchResultPosition": 0,
        "maxResults": 30,
        "major": 5,
        "minor": 0
    }
}';

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, "admin:@Llomaroc19"); // <-- Remplacer
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

$response = curl_exec($ch);

if(curl_errno($ch)){
    die("Erreur CURL: " . curl_error($ch));
}

curl_close($ch);

$result = json_decode($response, true);

if(!isset($result['AcsEvent']['InfoList'])){
    die("Aucun événement trouvé.");
}

$inserted = 0;

foreach($result['AcsEvent']['InfoList'] as $event){

    $user_id = null;

    // 1️⃣ Chercher par employeeNoString
    if(isset($event['employeeNoString'])){

        $emp = $conn->real_escape_string($event['employeeNoString']);
        $query = $conn->query("SELECT id FROM users WHERE id = '$emp'");

        if($query && $query->num_rows > 0){
            $user = $query->fetch_assoc();
            $user_id = $user['id'];
        }
    }

    // 2️⃣ Sinon chercher par cardNo
    elseif(isset($event['cardNo'])){

        $card = $conn->real_escape_string($event['cardNo']);
        $query = $conn->query("SELECT user_id FROM cartes_acces WHERE card_uid = '$card'");

        if($query && $query->num_rows > 0){
            $user = $query->fetch_assoc();
            $user_id = $user['user_id'];
        }
    }

    if(!$user_id) continue;

    $time = date('Y-m-d H:i:s', strtotime($event['time']));
    $minor = $event['minor'];

    // Déterminer type accès
    if($minor == 38 || $minor == 75){
        $access_type = "entry";
    }
    elseif($minor == 22){
        $access_type = "exit";
    }
    else{
        continue;
    }

    // Vérifier doublon
    $check = $conn->query("SELECT id FROM access_log 
                           WHERE user_id='$user_id' 
                           AND access_time='$time'");

    if($check && $check->num_rows == 0){

        $conn->query("INSERT INTO access_log 
                      (user_id, access_type, access_time)
                      VALUES ('$user_id', '$access_type', '$time')");

        $inserted++;
    }
}

echo "Synchronisation terminée ✅<br>";
echo "Nouveaux enregistrements ajoutés : " . $inserted;

$conn->close();

?>

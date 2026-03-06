<?php

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
curl_setopt($ch, CURLOPT_USERPWD, "admin:@Llomaroc19");
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);

$response = curl_exec($ch);

if(curl_errno($ch)){
    echo 'Erreur CURL: ' . curl_error($ch);
}

curl_close($ch);

echo "<pre>";
print_r($response);
echo "</pre>";
?>

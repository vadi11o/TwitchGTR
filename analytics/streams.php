<?php

$TwitchClientId = 'obl5c2tqnowx1ihivi6qlwd5dp2d0c';
$TwitchClientSecret = '6quagkprun03rxzngemtntly5jl79d';

$dataBaseServername = "localhost";
$dataBaseUsername = "id21862142_equipogtr"; 
$dataBasepassword = "fahber-Xenmu0-siffat";
$dataBaseName = "id21862142_topsofthetopsbbdd";

$url = 'https://api.twitch.tv/helix/streams';

function obtainTokenFromDataBase($servername, $username, $password, $database) {
    $conexionDataBase = new mysqli($servername, $username, $password, $database);
    if ($conexionDataBase->connect_error) {
        die("Fallo en la conexión: " . $conexionDataBase->connect_error);
    }

    $querySql = "SELECT access_token FROM token WHERE id = 1";
    $resultQuerySql = $conexionDataBase->query($querySql);
    if ($resultQuerySql->num_rows > 0) {
        $row = $resultQuerySql->fetch_assoc();
        $conexionDataBase->close();
        return $row['access_token'];
    } else {
        $conexionDataBase->close();
        return false;
    }
}

function refreshToken() {

    include "tokenUpdater.php"; 
}

function validateResponseFromTwitch($tiwtchResponse,$servername, $username, $password, $database, $url, $client_id) {
    
    if ($tiwtchResponse['status'] == 401) { 
        
        refreshToken();
        $token = obtainTokenFromDataBase($servername, $username, $password, $database); 
        $tiwtchResponse = curlPetition($url, $token, $client_id); 
    }

    
    if ($tiwtchResponse['status'] != 200) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Error al realizar la solicitud cURL para obtener información sobre los streams: HTTP Status ' . $tiwtchResponse['status']]);
        exit;
    }


}
function decodeJsonfromTwitch($tiwtchResponse){

    $result = json_decode($tiwtchResponse['body'], true);
    return $result;
}

function verifyActiveStreams($decodeJsonFromTwitch){

    $respuesta = [];
    // Verificar si hay streams activos
    if (isset($decodeJsonFromTwitch['data']) && !empty($decodeJsonFromTwitch['data'])) {
        foreach ($decodeJsonFromTwitch['data'] as $stream) {
            $respuesta[] = [
                'title' => $stream['title'],
                'user_name' => $stream['user_name']
            ];
        }
    } else {
        $respuesta['message'] = 'No hay streams activos en este momento.';
    }
    return $respuesta;
}

function curlPetition($url, $token, $client_id) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Client-Id: $client_id"
    ]);
    $body = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['status' => $status, 'body' => $body];
}

$token = obtainTokenFromDataBase($dataBaseServername, $dataBaseUsername, $dataBasepassword, $dataBaseName);
if (!$token) {
    die("No se pudo obtener el token de la base de datos.");
}

$tiwtchResponse = curlPetition($url, $token, $TwitchClientId);
$decodeJsonFromTwitch = decodeJsonfromTwitch($tiwtchResponse);
$resultFromVerificationActiveStreams = verifyActiveStreams($decodeJsonFromTwitch);

header('Content-Type: application/json');
echo json_encode($resultFromVerificationActiveStreams, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

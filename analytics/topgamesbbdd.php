<?php

$twitchClientID = 'obl5c2tqnowx1ihivi6qlwd5dp2d0c';

$dbServername = "localhost";
$dbUsername = "id21862142_equipogtr";
$dbPassword = "fahber-Xenmu0-siffat";
$dbName = "id21862142_topsofthetopsbbdd";

function fetchTopGamesFromTwitchAPI($accessToken, $clientID) {
    $url = 'https://api.twitch.tv/helix/games/top?first=3';
    $headers = array(
        "Authorization: Bearer $accessToken",
        "Client-Id: $clientID"
    );

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
    ));

    $response = curl_exec($curl);

    if ($response === false) {
        echo 'Error en la solicitud: ' . curl_error($curl);
        exit;
    }

    curl_close($curl);
    return json_decode($response, true);
}


function connectToDatabase($servername, $username, $password, $database) {
    $conn = new mysqli($servername, $username, $password, $database);

    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    return $conn;
}

function clearTopGamesTable($conn) {
    $sql = "DELETE FROM topGames";
    if (!$conn->query($sql)) {
        echo "Error al vaciar la tabla topGames: " . $conn->error;
    }
}

function insertTopGamesIntoDatabase($conn, $games) {
    foreach ($games as $game) {
        $sql = "INSERT INTO topGames (game_id, game_name) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $game['id'], $game['name']);
        
        if (!$stmt->execute()) {
            echo "Error al insertar registro: " . $stmt->error;
        }
        $stmt->close();
    }
}

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

$twitchAccessToken = obtainTokenFromDataBase($dbServername, $dbUsername, $dbPassword, $dbName);
$twitchResponse = fetchTopGamesFromTwitchAPI($twitchAccessToken, $twitchClientID);

if (!isset($twitchResponse['data']) || empty($twitchResponse['data'])) {
    echo 'No se encontraron datos válidos en la respuesta de la API de Twitch.';
    exit;
}

$topGames = array_slice($twitchResponse['data'], 0, 3);

$databaseConexion = connectToDatabase($dbServername, $dbUsername, $dbPassword, $dbName);
clearTopGamesTable($databaseConexion);
insertTopGamesIntoDatabase($databaseConexion, $topGames);
$databaseConexion->close();
?>

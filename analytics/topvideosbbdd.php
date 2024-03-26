<?php

$accessToken = 'lnm1pu5arycs3d30nhujdeybitflqv';
$TwitchClientId = 'obl5c2tqnowx1ihivi6qlwd5dp2d0c';

$dataBaseServername = "localhost";
$dataBaseUsername = "id21862142_equipogtr";
$dataBasepassword = "fahber-Xenmu0-siffat";
$dataBaseName = "id21862142_topsofthetopsbbdd";

function connectToDatabase($servername, $username, $password, $database) {
    $conn = new mysqli($servername, $username, $password, $database);
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

function truncateTable($conn, $tableName) {
    $sql = "TRUNCATE TABLE " . $tableName;
    if (!$conn->query($sql)) {
        echo "Error al vaciar la tabla $tableName: " . $conn->error;
    }
}

function fetchTwitchData($url, $access_token, $TwitchClientId) {
    $headers = [
        'Authorization: Bearer ' . $access_token,
        'Client-Id: ' . $TwitchClientId
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
    ]);

    $response = curl_exec($curl);
    if ($response === false) {
        echo 'Error en la solicitud: ' . curl_error($curl);
        curl_close($curl);
        exit;
    }

    curl_close($curl);
    return json_decode($response, true);
}

function insertVideoData($conn, $data, $game_id) {
    foreach ($data['data'] as $video) {
        $video_id = $conn->real_escape_string($video['id']);
        $title = $conn->real_escape_string($video['title']);
        $views = $video['view_count'];
        $user_name = $conn->real_escape_string($video['user_name']);
        $duration = $conn->real_escape_string($video['duration']);
        $created_at = $conn->real_escape_string($video['created_at']);

        $sql = "INSERT INTO topVideos (video_id, game_id, title, views, user_name, duration, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sisssss", $video_id, $game_id, $title, $views, $user_name, $duration, $created_at);

        if (!$stmt->execute()) {
            echo "Error al insertar datos del video: " . $conn->error;
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

$accessToken = obtainTokenFromDataBase($dataBaseServername, $dataBaseUsername, $dataBasepassword, $dataBaseName);
$databaseConexion = connectToDatabase($dataBaseServername, $dataBaseUsername, $dataBasepassword, $dataBaseName);
truncateTable($databaseConexion, "topVideos");

$url = 'https://api.twitch.tv/helix/videos?game_id=' . $game_id . '&first=40&sort=views';
$twitchData = fetchTwitchData($url, $accessToken, $TwitchClientId);

if (!isset($twitchData['data']) || empty($twitchData['data'])) {
    echo 'No se encontraron datos válidos en la respuesta de la API de Twitch.';
} else {
    insertVideoData($databaseConexion, $twitchData, $game_id);
}

$databaseConexion->close();
?>

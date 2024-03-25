<?php

$servername = "localhost";
$username = "id21862142_equipogtr"; 
$password = "fahber-Xenmu0-siffat"; 
$database = "id21862142_topsofthetopsbbdd"; 


$clientId = 'obl5c2tqnowx1ihivi6qlwd5dp2d0c';
$clientSecret = '6quagkprun03rxzngemtntly5jl79d';

function getUserIdFromUrl() {
    if (isset($_GET['id'])) {
        return $_GET['id'];
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'El parámetro "id" no se proporcionó en la URL.']);
        exit; 
    }
}
function getInfoUserFromDataBase($servername, $username, $password, $database, $user_id) {
    $conn = new mysqli($servername, $username, $password, $database);
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    $sql = "SELECT * FROM users WHERE twitch_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        header('Content-Type: application/json');
        echo json_encode($user_data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        $stmt->close();
        $conn->close();
        exit;
    }

    $stmt->close();
    $conn->close();
    return false;
}

function obtainTokenFromDataBase($servername, $username, $password, $database) {
    $conn = new mysqli($servername, $username, $password, $database);
    if ($conn->connect_error) {
        die("Fallo en la conexión: " . $conn->connect_error);
    }

    $sql = "SELECT access_token FROM token WHERE id = 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $conn->close();
        return $row['access_token'];
    } else {
        $conn->close();
        return false;
    }
}

function curlPetitionToTwitch($clientId, $token, $user_id) {
    $url = 'https://api.twitch.tv/helix/users?id=' . $user_id;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . $token,
        'Client-Id: ' . $clientId
    ));

    $twitchResponse = curl_exec($ch);
    if (curl_errno($ch)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Error al realizar la solicitud cURL: ' . curl_error($ch)]);
        exit; 
    }
    curl_close($ch);
    return $twitchResponse;
}


function tiwtchResponseDecoder($twitchResponse) {
    $result_user = json_decode($twitchResponse, true);
    if (!empty($result_user['data'])) {
        $user_data = $result_user['data'][0];
        header('Content-Type: application/json');
        echo json_encode($user_data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'No se encontraron datos de usuario para el ID proporcionado.']);
    }
}


$user_id = getUserIdFromUrl();


if (!getInfoUserFromDataBase($servername, $username, $password, $database, $user_id)) {
   
    $token = obtainTokenFromDataBase($servername, $username, $password, $database);
    if ($token) {

        $twitchResponse = curlPetitionToTwitch($clientId, $token, $user_id);
        tiwtchResponseDecoder($twitchResponse);
    } else {
       
        header('Content-Type: application/json');
        echo json_encode(['error' => 'No se pudo obtener el token de acceso desde la base de datos.']);
        exit;
    }
}
?>


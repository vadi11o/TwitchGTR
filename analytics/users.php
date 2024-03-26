<?php
function getUserIdFromParameter() {
    if (isset($_GET['id'])) {
        return $_GET['id'];
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'El parámetro "id" no se proporcionó en la URL.']);
        exit; 
    }
}
function getInfoUserFromDataBase($userId) {

    $dataBaseServername = "localhost";
    $dataBaseUsername = "id21862142_equipogtr"; 
    $dataBasepassword = "fahber-Xenmu0-siffat"; 
    $dataBaseName = "id21862142_topsofthetopsbbdd";

    $dataBaseConnection = new mysqli($dataBaseServername, $dataBaseUsername, $dataBasepassword, $dataBaseName);
    if ($dataBaseConnection->connect_error) {
        die("Conexión fallida: " . $dataBaseConnection->connect_error);
    }

    $sqlQuery = "SELECT * FROM users WHERE twitch_id = ?";
    $preparedQuery = $dataBaseConnection->prepare($sqlQuery);
    $preparedQuery->bind_param("s", $userId);
    $preparedQuery->execute();
    $dataFromDataBase = $preparedQuery->get_result();

    if ($dataFromDataBase->num_rows > 0) {
        $userData = $dataFromDataBase->fetch_assoc();
        header('Content-Type: application/json');
        echo json_encode($userData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        $preparedQuery->close();
        $dataBaseConnection->close();
        exit;
    }
    else{

        $preparedQuery->close();
        $dataBaseConnection->close();
        return false;
    }

}

function obtainTokenFromDataBase() {

    $dataBaseServername = "localhost";
    $dataBaseUsername = "id21862142_equipogtr"; 
    $dataBasepassword = "fahber-Xenmu0-siffat"; 
    $dataBaseName = "id21862142_topsofthetopsbbdd";

    $dataBaseConnection = new mysqli($dataBaseServername, $dataBaseUsername, $dataBasepassword, $dataBaseName);

    if ($dataBaseConnection->connect_error) {
        die("Fallo en la conexión: " . $dataBaseConnection->connect_error);
    }

    $sqlQuery = "SELECT access_token FROM token WHERE id = 1";
    $result = $dataBaseConnection->query($sqlQuery);

    if ($result->num_rows > 0) {

        $row = $result->fetch_assoc();
        $dataBaseConnection->close();
        return $row['access_token'];
    }
    else {

        $dataBaseConnection->close();
        return false;
    }
}

function curlPetitionToTwitch($token, $userId) {

    $clientId = 'obl5c2tqnowx1ihivi6qlwd5dp2d0c';
    $urlTwitchEndPoint = 'https://api.twitch.tv/helix/users?id=' . $userId;
    $curlSession = curl_init($urlTwitchEndPoint);
    curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curlSession, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . $token,
        'Client-Id: ' . $clientId
    ));

    $twitchResponse = curl_exec($curlSession);
    if (curl_errno($curlSession)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Error al realizar la solicitud cURL: ' . curl_error($curlSession)]);
        exit; 
    }
    curl_close($curlSession);
    return $twitchResponse;
}


function tiwtchResponseDecoder($twitchResponse) {
    $dataBaseServername = "localhost";
    $dataBaseUsername = "id21862142_equipogtr"; 
    $dataBasepassword = "fahber-Xenmu0-siffat"; 
    $dataBaseName = "id21862142_topsofthetopsbbdd";
    $twitchUserDataRecibed = json_decode($twitchResponse, true);

    if (!empty($twitchUserDataRecibed['data'])) {
        $userData = $twitchUserDataRecibed['data'][0];
        $dataBaseConnection = new mysqli($dataBaseServername, $dataBaseUsername, $dataBasepassword, $dataBaseName);
        if ($dataBaseConnection->connect_error) {
            die("Fallo en la conexión: " . $dataBaseConnection->connect_error);
        }

        insertUserIntoDatabase($dataBaseConnection, $userData);
        $dataBaseConnection->close();
        header('Content-Type: application/json');
        echo json_encode($userData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'No se encontraron datos de usuario para el ID proporcionado.']);
        exit;
    }
}


function insertUserIntoDatabase($conn, $user_data) {
    $formattedDate = date('Y-m-d H:i:s', strtotime($user_data['created_at']));

    $sqlQuery = "INSERT INTO users (twitch_id, login, display_name, type, broadcaster_type, description, profile_image_url, offline_image_url, view_count, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    if ($stmt = $conn->prepare($sqlQuery)) {
        $stmt->bind_param("ssssssssis", 
            $user_data['id'], $user_data['login'], $user_data['display_name'], 
            $user_data['type'], $user_data['broadcaster_type'], $user_data['description'], 
            $user_data['profile_image_url'], $user_data['offline_image_url'], 
            $user_data['view_count'], $formattedDate);

        $stmt->execute();
        $stmt->close();
    } else {
        echo "Error al preparar la inserción: " . $conn->error;
    }
}



$userId = getUserIdFromParameter();

if (!getInfoUserFromDataBase($userId)) {
   
    $token = obtainTokenFromDataBase();
    if ($token) {
        $twitchResponse = curlPetitionToTwitch($token, $userId);
        tiwtchResponseDecoder($twitchResponse);
        exit;

    }
    else {
        
        header('Content-Type: application/json');
        echo json_encode(['error' => 'No se pudo obtener el token de acceso desde la base de datos.']);
        exit;
    }
}
?>


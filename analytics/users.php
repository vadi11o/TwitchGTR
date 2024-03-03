<?php

$user_id = '';

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'El parámetro "id" no se proporcionó en la URL.']);
    exit;
}

$servername = "localhost";
$username = "id21862142_equipogtr"; 
$password = "fahber-Xenmu0-siffat"; 
$database = "id21862142_topsofthetopsbbdd"; 

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
} else {
    // Datos de tu aplicación en Twitch para obtener el token
    $client_id = 'obl5c2tqnowx1ihivi6qlwd5dp2d0c';
    $client_secret = '6quagkprun03rxzngemtntly5jl79d';
    $url = 'https://id.twitch.tv/oauth2/token';
    $data = array(
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'grant_type' => 'client_credentials'
    );

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Error al realizar la solicitud cURL: ' . curl_error($ch)]);
        exit;
    }
    curl_close($ch);
    $result_token = json_decode($response, true);
    $token = $result_token['access_token'];

    // Usar el token para obtener información del usuario desde la API de Twitch
    $url = 'https://api.twitch.tv/helix/users?id=' . $user_id;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . $token,
        'Client-Id: ' . $client_id
    ));
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Error al realizar la solicitud cURL: ' . curl_error($ch)]);
        exit;
    }
    curl_close($ch);
    $result_user = json_decode($response, true);

    if (!empty($result_user['data'])) {
        $user_data = $result_user['data'][0];
        $insert_sql = "INSERT INTO users (twitch_id, username) VALUES (?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ss", $user_id, $user_data['display_name']);
        $insert_stmt->execute();

        if ($insert_stmt->affected_rows > 0) {
            header('Content-Type: application/json');
            echo json_encode($user_data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No se pudo guardar el usuario en la base de datos.']);
        }

        $insert_stmt->close();
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'No se encontraron datos de usuario para el ID proporcionado.']);
    }
}

$stmt->close();
$conn->close();

?>

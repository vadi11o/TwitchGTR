<?php

$user_id = '';

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'El parámetro "id" no se proporcionó en la URL.']);
    exit;
}

// Datos de conexión a la base de datos
$servername = "localhost";
$username = "id21862142_equipogtr"; 
$password = "fahber-Xenmu0-siffat"; 
$database = "id21862142_topsofthetopsbbdd"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT id FROM users  WHERE twitch_id  = '$user_id'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    // El usuario no existe en la base de datos, realizar petición a la API de Twitch
    $client_id = 'obl5c2tqnowx1ihivi6qlwd5dp2d0c';
    $oauth_token = '6quagkprun03rxzngemtntly5jl79d';
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, "https://api.twitch.tv/helix/users?id=$user_id");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Client-ID: $client_id",
        "Authorization: Bearer $oauth_token"
    ));
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    if (!empty($data['data'])) {
        // Asumiendo que la API de Twitch devolvió la información correctamente
        // Ajusta los siguientes campos según los datos que quieras guardar y que devuelva la API
        $nombre_usuario = $data['data'][0]['login'];
        // Insertar el usuario en la base de datos
        $sql_insert = "INSERT INTO tu_tabla_de_usuarios (id, nombre_usuario) VALUES ('$user_id', '$nombre_usuario')";
        
        if ($conn->query($sql_insert) === TRUE) {
            echo "Nuevo usuario creado exitosamente.";
        } else {
            echo "Error al crear el usuario: " . $conn->error;
        }
    } else {
        echo "No se pudo obtener información del usuario de Twitch.";
    }
} else {
    echo "El usuario ya existe en la base de datos.";
}

$conn->close();
?>


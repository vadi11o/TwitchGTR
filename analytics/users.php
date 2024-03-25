<?php

$user_id = '';

// Verificar si se proporcionó el parámetro "id" en la URL
if (isset($_GET['id'])) {
    // Obtener el valor del parámetro "id"
    $user_id = $_GET['id'];
} else {
    // Si no se proporcionó el parámetro "id", mostrar un mensaje de error en formato JSON
    header('Content-Type: application/json');
    echo json_encode(['error' => 'El parámetro "id" no se proporcionó en la URL.']);
    exit; // Terminar la ejecución del script
}


//Comprobación existencia en BBDD
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
$decodeJsonFromTwitch = $stmt->get_result();

if ($decodeJsonFromTwitch->num_rows > 0) {

    $user_data = $decodeJsonFromTwitch->fetch_assoc();
    header('Content-Type: application/json');
    echo json_encode($user_data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    $stmt->close();
    $conn->close();
    exit;
}

$stmt->close();
$conn->close();


// Datos de tu aplicación en Twitch
$client_id = 'obl5c2tqnowx1ihivi6qlwd5dp2d0c';
$client_secret = '6quagkprun03rxzngemtntly5jl79d';

// URL de la API de Twitch para obtener el token
$url = 'https://id.twitch.tv/oauth2/token';

// Datos a enviar en la solicitud POST
$data = array(
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'grant_type' => 'client_credentials'
);

// Inicializar el recurso cURL
$ch = curl_init($url);

// Configurar las opciones de cURL
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/x-www-form-urlencoded'
));

// Ejecutar la solicitud cURL y obtener la respuesta
$tiwtchResponse = curl_exec($ch);

// Verificar si hay errores
if (curl_errno($ch)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error al realizar la solicitud cURL: ' . curl_error($ch)]);
    exit; // Terminar la ejecución del script
}

// Cerrar la sesión cURL
curl_close($ch);

// Decodificar la respuesta JSON
$result_token = json_decode($tiwtchResponse, true);

// Extraer el token de acceso
$token = $result_token['access_token'];

// URL de la API de Twitch para obtener información del usuario
$url = 'https://api.twitch.tv/helix/users?id=' . $user_id;

// Inicializar el recurso cURL
$ch = curl_init($url);

// Configurar las opciones de cURL
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $token,
    'Client-Id: ' . $client_id
));

// Ejecutar la solicitud cURL y obtener la respuesta
$tiwtchResponse = curl_exec($ch);

// Verificar si hay errores
if (curl_errno($ch)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error al realizar la solicitud cURL: ' . curl_error($ch)]);
    exit; // Terminar la ejecución del script
}

// Cerrar la sesión cURL
curl_close($ch);

// Decodificar la respuesta JSON
$result_user = json_decode($tiwtchResponse, true);

// Verificar si se encontraron datos de usuario
if (!empty($result_user['data'])) {
    // Obtener los datos del primer usuario (asumiendo que solo hay uno)
    $user_data = $result_user['data'][0];

    // Establecer el encabezado para indicar que el contenido es JSON
    header('Content-Type: application/json');

    // Imprimir la respuesta en formato JSON con la estructura deseada
    echo json_encode($user_data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
} else {
    // Mostrar un mensaje de error si no se encontraron datos de usuario
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No se encontraron datos de usuario para el ID proporcionado.']);
}

?>

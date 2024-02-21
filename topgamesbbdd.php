<?php
// Token de acceso de Twitch
$accessToken = 'lnm1pu5arycs3d30nhujdeybitflqv';
$client_id = 'obl5c2tqnowx1ihivi6qlwd5dp2d0c';
$client_secret = '6quagkprun03rxzngemtntly5jl79d';

// URL de la API de Twitch para obtener los juegos más populares
$url = 'https://api.twitch.tv/helix/games/top';

$headers = array(
    'Authorization: Bearer ' . $access_token,
    'Client-Id: ' . $client_id
);

// Configuración de la solicitud a la API de Twitch
$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api.twitch.tv/helix/games/top?first=3',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => $headers,
));

// Realizar la solicitud a la API de Twitch
$response = curl_exec($curl);

// Verificar si hay errores en la solicitud
if ($response === false) {
    echo 'Error en la solicitud: ' . curl_error($curl);
    exit;
}

// Decodificar la respuesta JSON
$data = json_decode($response, true);

// Verificar si la respuesta contiene datos válidos
if (!isset($data['data']) || empty($data['data'])) {
    echo 'No se encontraron datos válidos en la respuesta de la API de Twitch.';
    exit;
}

// Seleccionar los tres primeros juegos más populares
$topGames = array_slice($data['data'], 0, 3);

// Conexión a la base de datos
$servername = "localhost";
$username = "id21862142_equipogtr"; // Reemplaza con tu nombre de usuario de MySQL
$password = "fahber-Xenmu0-siffat"; // Reemplaza con tu contraseña de MySQL
$database = "id21862142_topsofthetopsbbdd"; // Reemplaza con el nombre de tu base de datos

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Insertar los juegos más populares en la base de datos
foreach ($topGames as $game) {
    $game_id = $game['id'];
    $game_name = $game['name'];

    $sql = "INSERT INTO topGames (game_id, game_name) VALUES ('$game_id', '$game_name')";

    if ($conn->query($sql) === true) {
        echo "Registro insertado correctamente: $game_name\n";
    } else {
        echo "Error al insertar registro: " . $conn->error;
    }
}

// Cerrar conexión a la base de datos
$conn->close();

// Cerrar la conexión cURL
curl_close($curl);
?>

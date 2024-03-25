<?php
// Token de acceso de Twitch
$access_token = 'lnm1pu5arycs3d30nhujdeybitflqv';
$client_id = 'obl5c2tqnowx1ihivi6qlwd5dp2d0c';

//$game_id = 509658;

// Vaciar la tabla topVideos
$servername = "localhost";
$username = "id21862142_equipogtr";
$password = "fahber-Xenmu0-siffat";
$database = "id21862142_topsofthetopsbbdd";

// Crear conexión a la base de datos
$conn = new mysqli($servername, $username, $password, $database);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Configurar la conexión para ignorar caracteres no reconocidos
$conn->set_charset('utf8mb4');

// Vaciar la tabla topVideos
$sql_truncate = "TRUNCATE TABLE topVideos";
if ($conn->query($sql_truncate) === true) {
    //echo "La tabla topVideos se ha vaciado correctamente.\n";
} else {
    echo "Error al vaciar la tabla topVideos: " . $conn->error;
}

// URL de la API de Twitch para obtener los 40 mejores vídeos para un juego específico
$url = 'https://api.twitch.tv/helix/videos?game_id=' . $game_id . '&first=40&sort=views';

$headers = array(
    'Authorization: Bearer ' . $access_token,
    'Client-Id: ' . $client_id
);

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
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
$tiwtchResponse = curl_exec($curl);

// Verificar si hay errores en la solicitud
if ($tiwtchResponse === false) {
    echo 'Error en la solicitud: ' . curl_error($curl);
    exit;
}

// Decodificar la respuesta JSON
$data = json_decode($tiwtchResponse, true);

// Verificar si la respuesta contiene datos válidos
if (!isset($data['data']) || empty($data['data'])) {
    echo 'No se encontraron datos válidos en la respuesta de la API de Twitch.';
    exit;
}

// Insertar los datos de los videos en la tabla topVideos
foreach ($data['data'] as $video) {
    $video_id = mysqli_real_escape_string($conn, $video['id']);
    $title = mysqli_real_escape_string($conn, $video['title']);
    $views = $video['view_count'];
    $user_name = mysqli_real_escape_string($conn, $video['user_name']);
    $duration = mysqli_real_escape_string($conn, $video['duration']);
    $created_at = mysqli_real_escape_string($conn, $video['created_at']);

    $sql = "INSERT INTO topVideos (video_id, game_id, title, views, user_name, duration, created_at) VALUES ('$video_id', '$game_id', '$title', $views, '$user_name', '$duration', '$created_at')";

    if ($conn->query($sql) === true) {
        //echo "Datos del video insertados correctamente: $title\n";
    } else {
        echo "Error al insertar datos del video: " . $conn->error;
    }
}

// Cerrar conexión a la base de datos
$conn->close();

// Cerrar la conexión cURL
curl_close($curl);
?>

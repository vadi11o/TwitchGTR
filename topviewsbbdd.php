<?php
// Token de acceso de Twitch
$access_token = 'lnm1pu5arycs3d30nhujdeybitflqv';
$client_id = 'obl5c2tqnowx1ihivi6qlwd5dp2d0c';

// Comprobar si se ha proporcionado un game_id como parámetro


    // URL de la API de Twitch para obtener los 40 mejores vídeos para un juego específico
    $url = 'https://api.twitch.tv/helix/videos?game_id=' . $game_id . '&first=40&sort=views';

    $headers = array(
        'Authorization: Bearer ' . $access_token,
        'Client-Id: ' . $client_id
    );

    // Configuración de la solicitud a la API de Twitch
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

    $servername = "localhost";
    $username = "id21862142_equipogtr"; // Reemplaza con tu nombre de usuario de MySQL
    $password = "fahber-Xenmu0-siffat"; // Reemplaza con tu contraseña de MySQL
    $database = "id21862142_topsofthetopsbbdd"; // Reemplaza con el nombre de tu base de datos

    // Crear conexión a la base de datos
    $conn = new mysqli($servername, $username, $password, $database);

    // Verificar la conexión
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    // Insertar los datos de los videos en la tabla topVideos
    foreach ($data['data'] as $video) {
        $video_id = $video['id'];
        $title = $video['title'];
        $views = $video['view_count'];
        $user_name = $video['user_name'];
        $duration = $video['duration'];
        $created_at = $video['created_at'];

        $sql = "INSERT INTO topVideos (video_id, game_id, title, views, user_name, duration, created_at) VALUES ('$video_id', '$game_id', '$title', $views, '$user_name', '$duration', '$created_at')";

        if ($conn->query($sql) === true) {
            echo "Datos del video insertados correctamente: $title\n";
        } else {
            echo "Error al insertar datos del video: " . $conn->error;
        }
    }

    // Cerrar conexión a la base de datos
    $conn->close();

    // Cerrar la conexión cURL
    curl_close($curl);

?>

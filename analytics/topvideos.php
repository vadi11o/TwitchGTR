<?php
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

// Inicializar cURL para obtener el token de acceso
$curl = curl_init();

// Configurar opciones de cURL para obtener el token de acceso
curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => http_build_query($data),
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/x-www-form-urlencoded'
    ),
));

// Ejecutar la consulta cURL para obtener el token de acceso
$response = curl_exec($curl);

// Verificar si hay errores en la consulta cURL
if (curl_errno($curl)) {
    echo 'Error:' . curl_error($curl);
}

// Cerrar la conexión cURL para obtener el token de acceso
curl_close($curl);

// Decodificar la respuesta JSON para obtener el token de acceso
$token_data = json_decode($response, true);

if(isset($token_data['access_token'])) {
    // Token de acceso obtenido correctamente
    $access_token = $token_data['access_token'];

    // Cabeceras de la petición cURL para obtener los 40 videos más vistos
    $headers = array(
        'Authorization: Bearer ' . $access_token,
        'Client-Id: ' . $client_id
    );

    // Obtener game_ids de la tabla topGames
    $servername = "localhost";
    $username = "id21862142_equipogtr"; // Reemplaza con tu nombre de usuario de MySQL
    $password = "fahber-Xenmu0-siffat"; // Reemplaza con tu contraseña de MySQL
    $database = "id21862142_topsofthetopsbbdd"; // Reemplaza con el nombre de tu base de datos

    // Establecer conexión a la base de datos
    $conn = new mysqli($servername, $username, $password, $database);

    // Verificar la conexión
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    $game_ids = array();
    $sql_game_ids = "SELECT game_id FROM topGames";
    $result = $conn->query($sql_game_ids);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $game_ids[] = $row['game_id'];
        }
    }

    // Cerrar la conexión a la base de datos
    $conn->close();

    // Inicializar cURL para obtener los 40 videos más vistos
    $curl = curl_init();

    // Configurar opciones de cURL para obtener los 40 videos más vistos
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.twitch.tv/helix/videos/top?first=40&sort=views&game_id=' . implode(',', $game_ids),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => $headers,
    ));

    // Ejecutar la consulta cURL para obtener los 40 videos más vistos
    $response = curl_exec($curl);

    // Verificar si hay errores en la consulta cURL
    if (curl_errno($curl)) {
        echo 'Error:' . curl_error($curl);
    }

    // Cerrar la conexión cURL para obtener los 40 videos más vistos
    curl_close($curl);

    // Decodificar la respuesta JSON para obtener los 40 videos más vistos
    $data = json_decode($response, true);

    // Guardar los datos en la tabla topViews de la base de datos
    if (isset($data['data']) && !empty($data['data'])) {
        // Datos de conexión a la base de datos
        $servername = "localhost";
        $username = "id21862142_equipogtr"; // Reemplaza con tu nombre de usuario de MySQL
        $password = "fahber-Xenmu0-siffat"; // Reemplaza con tu contraseña de MySQL
        $database = "id21862142_topsofthetopsbbdd"; // Reemplaza con el nombre de tu base de datos

        // Establecer conexión a la base de datos
        $conn = new mysqli($servername, $username, $password, $database);

        // Verificar la conexión
        if ($conn->connect_error) {
            die("Conexión fallida: " . $conn->connect_error);
        }

        // Iterar sobre los videos y guardarlos en la base de datos
        foreach ($data['data'] as $video) {
            $video_id = $video['id'];
            $video_title = $video['title'];
            $video_views = $video['view_count'];
            $game_id = $video['game_id'];
            $user_id = $video['user_id'];

            // Insertar los datos en la tabla topViews de la base de datos
            $sql = "INSERT INTO topViews (video_id, video_title, video_views, game_id, user_id) VALUES ('$video_id', '$video_title', '$video_views', '$game_id', '$user_id')";
            if ($conn->query($sql) === TRUE) {
                echo "Registro insertado con éxito.";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }

        // Cerrar la conexión a la base de datos
        $conn->close();
    } else {
        echo "No se encontraron datos para insertar.";
    }
} else {
    // Error al obtener el token de acceso
    echo "Error al obtener el token de acceso.";
}
?>

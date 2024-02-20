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

    // Cabeceras de la petición cURL para obtener los juegos principales
    $headers = array(
        'Authorization: Bearer ' . $access_token,
        'Client-Id: ' . $client_id
    );

    // Inicializar cURL para obtener los juegos principales
    $curl = curl_init();

    // Configurar opciones de cURL para obtener los juegos principales
    // Configurar opciones de cURL para obtener los juegos principales limitados a los primeros 3
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


    // Ejecutar la consulta cURL para obtener los juegos principales
    $response = curl_exec($curl);

    // Verificar si hay errores en la consulta cURL
    if (curl_errno($curl)) {
        echo 'Error:' . curl_error($curl);
    }

    // Cerrar la conexión cURL para obtener los juegos principales
    curl_close($curl);

    // Decodificar la respuesta JSON para obtener los juegos principales
    $data = json_decode($response, true);

    // Guardar los datos en la tabla topGames de la base de datos
    if (isset($data['data']) && !empty($data['data'])) {
        // Datos de conexión a la base de datos
        $servername = "localhost";
        $username = "id21862142_equipogtr"; 
        $password = "fahber-Xenmu0-siffat"; 
        $database = "id21862142_topsofthetopsbbdd"; 

        // Establecer conexión a la base de datos
        $conn = new mysqli($servername, $username, $password, $database);

        // Verificar la conexión
        if ($conn->connect_error) {
            die("Conexión fallida: " . $conn->connect_error);
        }

        foreach ($data['data'] as $game) {
            $game_id = $game['id'];
            $game_name = $game['name'];

            // Verificar si el juego ya existe en la tabla
            $sql_check_game = "SELECT * FROM topGames WHERE game_id = '$game_id'";
            $result_check_game = $conn->query($sql_check_game);

            if ($result_check_game->num_rows > 0) {
                // El juego ya existe, actualizar el nombre si es necesario
                $sql_update_game = "UPDATE topGames SET game_name = '$game_name' WHERE game_id = '$game_id'";
                if ($conn->query($sql_update_game) === TRUE) {
                    echo "Juego actualizado con éxito.";
                } else {
                    echo "Error al actualizar el juego: " . $conn->error;
                }
            } else {
                // El juego no existe, insertarlo en la tabla
                $sql_insert_game = "INSERT INTO topGames (game_id, game_name) VALUES ('$game_id', '$game_name')";
                if ($conn->query($sql_insert_game) === TRUE) {
                    echo "Juego insertado con éxito.";
                } else {
                    echo "Error al insertar el juego: " . $conn->error;
                }
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

<?php

function realizarPeticionTwitch($accessToken, $clientId, $first) {
    $url = 'https://api.twitch.tv/helix/games/top';
    $headers = [
        'Authorization: Bearer ' . $accessToken,
        'Client-Id: ' . $clientId,
    ];

    // Agregar el parámetro 'first' a la URL
    $url .= '?first=' . $first;

    // Inicializar cURL
    $ch = curl_init($url);

    // Configurar opciones de cURL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // Realizar la solicitud y obtener la respuesta
    $response = curl_exec($ch);

    // Cerrar la conexión cURL
    curl_close($ch);

    // Procesar la respuesta JSON
    $data = json_decode($response, true);

    // Verificar si la respuesta contiene datos
    if (isset($data['data']) && is_array($data['data'])) {
        // Conectar a la base de datos (reemplaza 'tudb', 'tuusuario', 'tupassword' y 'localhost' con tus propias credenciales)
        $conn = new mysqli('localhost', 'tuusuario', 'tupassword', 'tudb');

        // Verificar la conexión
        if ($conn->connect_error) {
            die('Error de conexión a la base de datos: ' . $conn->connect_error);
        }

        // Iterar sobre los juegos y guardar en la base de datos
        foreach ($data['data'] as $game) {
            $gameId = $game['id'];
            $gameName = $game['name'];

            // Insertar en la tabla topGames
            $sql = "INSERT INTO topGames (game_id, game_name) VALUES ('$gameId', '$gameName')";

            if ($conn->query($sql) !== TRUE) {
                echo 'Error al insertar en la base de datos: ' . $conn->error;
            }
        }

        // Cerrar la conexión a la base de datos
        $conn->close();
    }
}

// Ejemplo de uso
$accessToken = 'cfabdegwdoklmawdzdo98xt2fo512y';
$clientId = 'uo6dggojyb8d6soh92zknwmi5ej1q2';
$first = 3;

realizarPeticionTwitch($accessToken, $clientId, $first);

?>

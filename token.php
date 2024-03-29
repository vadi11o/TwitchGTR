<?php

function actualizar_token() {

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
    $response = curl_exec($ch);

    // Verificar si hay errores
    if (curl_errno($ch)) {
        // Devolver un error en formato JSON y terminar la ejecución
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Error al realizar la solicitud cURL para obtener el token: ' . curl_error($ch)]);
        exit;
    }

    // Cerrar la sesión cURL
    curl_close($ch);

    // Decodificar la respuesta JSON
    $result = json_decode($response, true);

    // Verificar si hay errores en la respuesta para obtener el token
    if (isset($result['error'])) {
        // Devolver un error en formato JSON y terminar la ejecución
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Error al obtener el token: ' . $result['error_description']]);
        exit;
    }

    // Token
    $token = $result['access_token'];

    // Guardar el token en la base de datos
    $servername = "localhost";
    $username = "id21862142_equipogtr";
    $password = "fahber-Xenmu0-siffat"; 
    $database = "id21862142_topsofthetopsbbdd";

    // Crear la conexión a la base de datos
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Verificar la conexión
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    // Preparar la consulta SQL
    $sql = "UPDATE token SET access_token='$token' WHERE id=1"; // Ajusta la condición WHERE según tu esquema

    // Ejecutar la consulta
    if ($conn->query($sql) === TRUE) {
        echo "Token actualizado correctamente en la base de datos";
    } else {
        echo "Error al actualizar el token en la base de datos: " . $conn->error;
    }

    // Cerrar la conexión
    $conn->close();
}

function recibir_token() {
    // Datos de conexión a la base de datos
    $servername = "localhost";
    $username = "id21862142_equipogtr";
    $password = "fahber-Xenmu0-siffat"; 
    $database = "id21862142_topsofthetopsbbdd";

    // Crear la conexión a la base de datos
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Verificar la conexión
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    // Preparar la consulta SQL para obtener el token
    $sql = "SELECT access_token FROM token WHERE id=1"; // Ajusta la condición WHERE según tu esquema

    // Ejecutar la consulta
    $result = $conn->query($sql);

    // Verificar si se obtuvieron resultados
    if ($result->num_rows > 0) {
        // Obtener el primer resultado (asumiendo que solo hay un registro)
        $row = $result->fetch_assoc();

        // Obtener el token
        $token = $row['access_token'];

        // Cerrar la conexión
        $conn->close();

        // Devolver el token
        return $token;
    } else {
        // Si no se encuentra ningún token, devolver un valor por defecto o manejar según sea necesario
        $conn->close();
        return null;
    }
}

?>

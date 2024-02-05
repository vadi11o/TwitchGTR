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
    echo 'Error al realizar la solicitud cURL para obtener el token: ' . curl_error($ch);
    exit;
}

// Cerrar la sesión cURL
curl_close($ch);

// Decodificar la respuesta JSON
$result = json_decode($response, true);

// Verificar si hay errores en la respuesta para obtener el token
if (isset($result['error'])) {
    echo 'Error al obtener el token: ' . $result['error_description'];
    exit;
}

// Token
$token = $result['access_token'];

// URL de la API de Twitch para obtener información sobre los streams
$url = 'https://api.twitch.tv/helix/streams';

// Inicializar el recurso cURL
$ch = curl_init($url);

// Configurar las opciones de cURL
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $token,
    'Client-Id: ' . $client_id
));

// Ejecutar la solicitud cURL y obtener la respuesta
$response = curl_exec($ch);

// Verificar si hay errores
if (curl_errno($ch)) {
    echo 'Error al realizar la solicitud cURL para obtener información sobre los streams: ' . curl_error($ch);
    exit;
}

// Cerrar la sesión cURL
curl_close($ch);

// Decodificar la respuesta JSON
$result = json_decode($response, true);

// Verificar si hay streams activos
if (isset($result['data']) && !empty($result['data'])) {
    // Iterar sobre los streams y mostrar información
    foreach ($result['data'] as $stream) {
        echo 'title: ' . $stream['title'] . PHP_EOL;
        echo 'user_name: ' . $stream['user_name'] . PHP_EOL;
        echo '---' . PHP_EOL;
    }
} else {
    echo 'No hay streams activos en este momento.';
}

?>

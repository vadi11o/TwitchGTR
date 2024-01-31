<?php

$user_id = '';

// Verificar si se proporcionó el parámetro "id" en la URL
if (isset($_GET['id'])) {
    // Obtener el valor del parámetro "id"
    $user_id = $_GET['id'];

    // Puedes realizar otras acciones según tus necesidades aquí
} else {
    // Si no se proporcionó el parámetro "id", mostrar un mensaje de error o realizar acciones adicionales según sea necesario
    echo 'Error: El parámetro "id" no se proporcionó en la URL.';
}

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
    echo 'Error al realizar la solicitud cURL: ' . curl_error($ch);
}

// Cerrar la sesión cURL
curl_close($ch);

// Decodificar la respuesta JSON
$result = json_decode($response, true);


// Token
$token = $result['access_token'];

// URL de la API de Twitch
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
$response = curl_exec($ch);

// Verificar si hay errores
if (curl_errno($ch)) {
    echo 'Error al realizar la solicitud cURL: ' . curl_error($ch);
}

// Cerrar la sesión cURL
curl_close($ch);

// Decodificar la respuesta JSON
$result = json_decode($response, true);

// Imprimir la respuesta
print_r($result);

?>

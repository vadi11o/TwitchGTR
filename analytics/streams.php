<?php

// Datos de tu aplicación en Twitch
$client_id = 'obl5c2tqnowx1ihivi6qlwd5dp2d0c';
$client_secret = '6quagkprun03rxzngemtntly5jl79d';
include "token.php";
// Token
$token = recibir_token();

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
    // Devolver un error en formato JSON y terminar la ejecución
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error al realizar la solicitud cURL para obtener información sobre los streams: ' . curl_error($ch)]);
    exit;
}else{
	curl_close($ch);
	actualizar_token();
	$token = recibir_token();
	$ch = curl_init($url);

// Configurar las opciones de cURL
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $token,
    'Client-Id: ' . $client_id
));

// Ejecutar la solicitud cURL y obtener la respuesta
$response = curl_exec($ch);
header('Content-Type: application/json');
    echo json_encode(['error' => 'Error al realizar la solicitud cURL para obtener información sobre los streams: ' . curl_error($ch)]);
    curl_close($ch);
	
}

// Cerrar la sesión cURL
curl_close($ch);

// Decodificar la respuesta JSON
$result = json_decode($response, true);

// Preparar la respuesta
$respuesta = [];

// Verificar si hay streams activos
if (isset($result['data']) && !empty($result['data'])) {
    foreach ($result['data'] as $stream) {
        $respuesta[] = [
            'title' => $stream['title'],
            'user_name' => $stream['user_name']
        ];
    }
} else {
    $respuesta['message'] = 'No hay streams activos en este momento.';
}

// Establecer el encabezado de contenido como JSON
header('Content-Type: application/json');

// Devolver la respuesta en formato JSON
echo json_encode($respuesta);

?>

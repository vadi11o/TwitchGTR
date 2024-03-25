<?php

$TwitchClientId = 'obl5c2tqnowx1ihivi6qlwd5dp2d0c';
$TwitchClientSecret = '6quagkprun03rxzngemtntly5jl79d';

$url = "https://id.twitch.tv/oauth2/token";

$servername = "localhost";
$username = "id21862142_equipogtr"; 
$password = "fahber-Xenmu0-siffat";
$database = "id21862142_topsofthetopsbbdd";


function solicitarToken($client_id, $client_secret, $url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'grant_type' => 'client_credentials'
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    return $data['access_token'] ?? null;
}
function actualizarTokenBbdd($token, $servername, $username, $password, $database){

    if ($token) {
        // Conectar a la base de datos
        $conn = new mysqli($servername, $username, $password, $database);
    
        // Verificar la conexión
        if ($conn->connect_error) {
            die("Fallo en la conexión: " . $conn->connect_error);
        }
    
        // Preparar la consulta SQL para actualizar el token específicamente para el id = 1
        $sql = $conn->prepare("UPDATE token SET access_token = ? WHERE id = 1;");
        $sql->bind_param("s", $token);
        $sql->execute();
    
        if ($sql->affected_rows > 0) {
            echo "Token actualizado con éxito.";
        } else {
            // Es posible que no haya habido cambios si el token no ha cambiado
            echo "No se actualizó el token, puede que ya tuviera el valor más reciente.";
        }
    
        // Cerrar la conexión
        $conn->close();
    } else {
        echo "Error al solicitar el token.";
    }

}

$token = solicitarToken($TwitchClientId, $TwitchClientSecret, $url);
actualizarTokenBbdd($token, $servername, $username, $password, $database);

?>

<?php
function getTokenFromTwitch() {

    $TwitchClientId = 'obl5c2tqnowx1ihivi6qlwd5dp2d0c';
    $TwitchClientSecret = '6quagkprun03rxzngemtntly5jl79d';
    $urlTwitchEndPoint = "https://id.twitch.tv/oauth2/token"; 

    $curlSession = curl_init();
    curl_setopt($curlSession, CURLOPT_URL, $urlTwitchEndPoint);
    curl_setopt($curlSession, CURLOPT_POST, 1);
    curl_setopt($curlSession, CURLOPT_POSTFIELDS, http_build_query([
        'client_id' => $TwitchClientId,
        'client_secret' => $TwitchClientSecret,
        'grant_type' => 'client_credentials'
    ]));
    curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);

    $twitchResponse = curl_exec($curlSession);
    curl_close($curlSession);

    $decodedDataFromTwitch = json_decode($twitchResponse, true);
    return $decodedDataFromTwitch['access_token'] ?? null;
}
function updateTokenDataBase($token){

    $dataBaseServername = "localhost";
    $dataBaseUsername = "id21862142_equipogtr"; 
    $dataBasepassword = "fahber-Xenmu0-siffat";
    $dataBaseName = "id21862142_topsofthetopsbbdd";

    if ($token) {
        $dataBaseConnection = new mysqli($dataBaseServername, $dataBaseUsername, $dataBasepassword, $dataBaseName);
        if ($dataBaseConnection->connect_error) {
            die("Fallo en la conexión: " . $dataBaseConnection->connect_error);
        }
        else{

            $sqlQuery = $dataBaseConnection->prepare("UPDATE token SET access_token = ? WHERE id = 1;");
            $sqlQuery->bind_param("s", $token);
            $sqlQuery->execute();
        
            if ($sqlQuery->affected_rows > 0) {
                
                echo "Token actualizado con éxito.";
                $dataBaseConnection->close();
            } 
            else {

                echo "No se actualizó el token, puede que ya tuviera el valor más reciente.";
                $dataBaseConnection->close();
            }
            
        }
    } 
    else {

        die("Error al solicitar el token.");
    }

}

$token = getTokenFromTwitch();
updateTokenDataBase($token);

?>

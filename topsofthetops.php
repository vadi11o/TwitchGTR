<?php
function videosTopPorGame($game_id){
    include "topviewsbbdd.php";

    
}

// Conectar a la base de datos (reemplaza 'tudb', 'tuusuario', 'tupassword' y 'localhost' con tus propias credenciales)
$conn = new mysqli('localhost', 'tuusuario', 'tupassword', 'tudb');

// Verificar la conexión
if ($conn->connect_error) {
    die('Error de conexión a la base de datos: ' . $conn->connect_error);
}

// Consultar todos los game_id de la tabla topGames
$sql = "SELECT game_id FROM topGames";
$result = $conn->query($sql);

$sqlTopOfTheTops = "SELECT game_id FROM topOfTheTops";
$resultTopOfTheTops = $conn->query($sqlTopOfTheTops);

if ($resultTopOfTheTops->num_rows > 0) {
    // Inicializar un array para almacenar los game_id de topOfTheTops
    $gameIdsTopOfTheTops = [];

    // Iterar sobre los resultados y almacenar en el array
    while ($rowTopOfTheTops = $resultTopOfTheTops->fetch_assoc()) {
        $gameIdsTopOfTheTops[] = $rowTopOfTheTops['game_id'];
    }
}

// Verificar si se obtuvieron resultados
if ($result->num_rows > 0) {
    // Iterar sobre los resultados 
    while ($row = $result->fetch_assoc()) {
        $game_id = = $row['game_id'];
        if(empty($gameIdsTopOfTheTops) || in_array($game_id, $gameIdsTopOfTheTops)){
            mivideosTopPorGame($game_id);

            ////////////////////////////////////////////////////////////////////////HAY QUE TRATAR LOS VIDEOS





            ////////////////////////////////////////////////////////////////////////SOLO LLAMA A LOS VIDEOS SI NO ESTA LA ID
        }
    }

    // Mostrar o utilizar el array según sea necesario
    print_r($gameIds);
} else {
    echo "No se encontraron resultados.";
}

// Cerrar la conexión a la base de datos
$conn->close();

$game_id = 21779;
mivideosTopPorGame($game_id);

?>

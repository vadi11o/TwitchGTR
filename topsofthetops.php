<?php

// Verificar si se proporcionó el parámetro "since" en la URL
if (isset($_GET['since'])) {
    // Obtener el valor del parámetro "id"
    $since = $_GET['since'];
    //echo "since $since";
} else {
    //echo "trabajando con since 600";
    // Si no se proporcionó el parámetro "since", since seran 10 minutos
    $since = 600;
}

include "topgamesbbdd.php";

$games = obtenerGameIDs();
$lista_json = array();

foreach ($games as $game) {
    $game_id = $game['game_id'];
    $game_name = $game['game_name'];

    //echo "Game ID: " . $game['game_id'] . ", Game Name: " . $game['game_name'] . "\n";

    // Llamar a la función para actualizar los tops si el game_id no existe en la tabla topOfTheTops
    $actualizado = hayQueActualizar($game_id, $since);
    switch($actualizado) { // 1 si estan actualizados hace menos tiempo que el since, 2 si estan pero desactualizados y 3 si no esta el juego en la tabla
        case 1:
            //echo "JUEGO [$game_name] esta actualizado";
            break;
        
        case 2:
            //echo "JUEGO [$game_name] esta pero hay que actualizarlo";
            actualizaTops($game_id);
            actualizarTopOfTheTop($game_id, $game_name, true);
            break;
        
        case 3:
            //echo "JUEGO [$game_name] no esta ";
            actualizaTops($game_id);
            actualizarTopOfTheTop($game_id, $game_name, false);
            break;
    } 
}
foreach($games as $game){
    $lista_json[] = obtenerTopsOfTheTops($game['game_id'], conexionBBDD());
}

$json = json_encode($lista_json, JSON_PRETTY_PRINT);

header('Content-Type: application/json');

echo $json;


function obtenerTopsOfTheTops($game_id, $conn) {
    $sql = "SELECT * FROM topOfTheTops WHERE game_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $game_id);  
    $stmt->execute();

    $result_game_id = null;
    $result_game_name = null;
    $result_user_name = null;
    $result_total_videos = null;
    $result_total_views = null;
    $result_most_viewed_title = null;
    $result_most_viewed_views = null;
    $result_most_viewed_duration = null;
    $result_most_viewed_created_at = null;
    $actualizacion = null;

    // Vincular las columnas a variables
    $stmt->bind_result($result_game_id, $result_game_name, $result_user_name, $result_total_videos, $result_total_views, $result_most_viewed_title, $result_most_viewed_views, $result_most_viewed_duration, $result_most_viewed_created_at,$actualizacion);

    // Obtener los resultados
    $stmt->fetch();

    // Cerrar la declaración
    $stmt->close();

    // Crear un array asociativo con los datos
    $datos = array(
        "game_id" => $result_game_id,
        "game_name" => $result_game_name,
        "user_name" => $result_user_name,
        "total_videos" => $result_total_videos,
        "total_views" => $result_total_views,
        "most_viewed_title" => $result_most_viewed_title,
        "most_viewed_views" => $result_most_viewed_views,
        "most_viewed_duration" => $result_most_viewed_duration,
        "most_viewed_created_at" => $result_most_viewed_created_at
    );

    $conn->close();

    return $datos;
}


// Ejecutar la función para insertar los datos en la tabla topOfTheTops

function conexionBBDD(){
    $servername = "localhost";
    $username = "id21862142_equipogtr"; // Reemplaza con tu nombre de usuario de MySQL
    $password = "fahber-Xenmu0-siffat"; // Reemplaza con tu contraseña de MySQL
    $database = "id21862142_topsofthetopsbbdd";

    // Crear conexión a la base de datos
    $conn = new mysqli($servername, $username, $password, $database);

    // Verificar la conexión
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    return $conn;
}


//actualiza si es necesario la tabla videos
function actualizaTops($game_id) {
    include "topvideosbbdd.php";
}
//obtiene los 3 ids de la tabla topGames
function obtenerGameIDs() {
    // Datos de conexión a la base de datos
    /*$servername = "localhost";
    $username = "id21862142_equipogtr"; // Reemplaza con tu nombre de usuario de MySQL
    $password = "fahber-Xenmu0-siffat"; // Reemplaza con tu contraseña de MySQL
    $database = "id21862142_topsofthetopsbbdd";

    // Crear conexión a la base de datos
    $conn = new mysqli($servername, $username, $password, $database);

    // Verificar la conexión
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }*/
    
    $conn = conexionBBDD();
    // Inicializar un array para almacenar los game_ids
    $game_data = array();

    // Consultar los game_id y game_name de la tabla topGames y guardarlos en el array
    $sql = "SELECT game_id, game_name FROM topGames";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $game_data[] = array(
                'game_id' => $row['game_id'],
                'game_name' => $row['game_name']
            );
        }
    } else {
        //echo "No se encontraron game_id en la tabla topGames.";
    }

    // Cerrar conexión a la base de datos
    $conn->close();

    // Retornar el array de game_data
    return $game_data;
}


function actualizarTopOfTheTop($game_id, $game_name, $esta_en_BBDD) {
    // Datos de conexión a la base de datos
    /*$servername = "localhost";
    $username = "id21862142_equipogtr"; // Reemplaza con tu nombre de usuario de MySQL
    $password = "fahber-Xenmu0-siffat"; // Reemplaza con tu contraseña de MySQL
    $database = "id21862142_topsofthetopsbbdd";

    // Crear conexión a la base de datos
    $conn = new mysqli($servername, $username, $password, $database);

    // Verificar la conexión
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }*/
    
    $conn = conexionBBDD();

    // Consultar la fila con más views en la tabla topVideos
    $sql = "SELECT user_name, duration AS most_viewed_duration, created_at AS most_viewed_created_at, title AS most_viewed_title, views AS most_viewed_views, SUM(views) AS total_views, COUNT(*) AS total_videos FROM topVideos GROUP BY user_name ORDER BY total_views DESC, total_videos DESC LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $video_popular = $result->fetch_assoc();

        // Insertar los datos en la tabla topOfTheTops
        if($esta_en_BBDD){
            $top_sql = "UPDATE topOfTheTops 
                SET game_name = ?, 
                    user_name = ?, 
                    total_videos = ?, 
                    total_views = ?, 
                    most_viewed_title = ?, 
                    most_viewed_views = ?, 
                    most_viewed_duration = ?, 
                    most_viewed_created_at = ?, 
                    ultima_actualizacion = NOW()
                WHERE game_id = ?";
            
            $stmt = $conn->prepare($top_sql);
            
           $stmt->bind_param("ssiisssss",  $game_name, $video_popular['user_name'], $video_popular['total_videos'], $video_popular['total_views'], $video_popular['most_viewed_title'], $video_popular['most_viewed_views'], $video_popular['most_viewed_duration'], $video_popular['most_viewed_created_at'],$game_id);
            
            // Ejecutar la consulta
            $stmt->execute();

 
            // Ejecutar la sentencia de update
            if ($stmt->execute()) {
                //echo "Datos insertados correctamente en la tabla topOfTheTops.";
            } else {
                //echo "Error al insertar datos en la tabla topOfTheTops: " . $stmt->error;
            }

            // Cerrar la sentencia y la conexión
            $stmt->close();
        } else {
            $top_sql = "INSERT INTO topOfTheTops (game_id, game_name, user_name, total_videos, total_views, most_viewed_title, most_viewed_views, most_viewed_duration, most_viewed_created_at, ultima_actualizacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            // Preparar la sentencia de inserción
            $stmt = $conn->prepare($top_sql);
            $stmt->bind_param("sssiissss", $game_id, $game_name, $video_popular['user_name'], $video_popular['total_videos'], $video_popular['total_views'], $video_popular['most_viewed_title'], $video_popular['most_viewed_views'], $video_popular['most_viewed_duration'], $video_popular['most_viewed_created_at']);

            // Ejecutar la sentencia de inserción
            if ($stmt->execute()) {
                //echo "Datos insertados correctamente en la tabla topOfTheTops.";
            } else {
                //echo "Error al insertar datos en la tabla topOfTheTops: " . $stmt->error;
            }

            // Cerrar la sentencia y la conexión
            $stmt->close();
        }

        // Preparar la sentencia de inserción
        $stmt = $conn->prepare($top_sql);
        $stmt->bind_param("sssiissss", $game_id, $game_name, $video_popular['user_name'], $video_popular['total_videos'], $video_popular['total_views'], $video_popular['most_viewed_title'], $video_popular['most_viewed_views'], $video_popular['most_viewed_duration'], $video_popular['most_viewed_created_at']);

        // Ejecutar la sentencia de inserción
        if ($stmt->execute()) {
            //echo "Datos insertados correctamente en la tabla topOfTheTops.";
        } else {
            //echo "Error al insertar datos en la tabla topOfTheTops: " . $stmt->error;
        }

        // Cerrar la sentencia y la conexión
        $stmt->close();
    } else {
        //echo "No se encontraron videos en la tabla topVideos.";
    }

    // Cerrar conexión a la base de datos
    $conn->close();
}




function hayQueActualizar($game_id, $since) {
    // Datos de conexión a la base de datos
    /*$servername = "localhost";
    $username = "id21862142_equipogtr"; // Reemplaza con tu nombre de usuario de MySQL
    $password = "fahber-Xenmu0-siffat"; // Reemplaza con tu contraseña de MySQL
    $database = "id21862142_topsofthetopsbbdd";

    // Crear conexión a la base de datos
    $conn = new mysqli($servername, $username, $password, $database);

    // Verificar la conexión
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }*/
    
    $conn = conexionBBDD();

    // Consultar si el game_id existe en la tabla topOfTheTops
    $sql = "SELECT ultima_actualizacion FROM topOfTheTops WHERE game_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $game_id);
    $stmt->execute();
    $stmt->store_result();
    $numRows = $stmt->num_rows;

    // Verificar si se encontraron filas
    if ($numRows > 0) {
        // El ID existe, obtener la fecha de actualización
        $stmt->bind_result($fechaActualizacion);
        $stmt->fetch();

        // Verificar la diferencia de tiempo
        $fechaActual = new DateTime();  // Fecha y hora actual
        $fechaActualizacion = new DateTime($fechaActualizacion);  // Fecha de actualización desde la base de datos

        $diferencia = $fechaActual->getTimestamp() - $fechaActualizacion->getTimestamp();

        // Cerrar la conexión y liberar recursos
        $stmt->close();
        $conn->close();

        if($diferencia < $since){
            return 1;
        } else {
            return 2;
        }
        // Devolver true si la diferencia es menor a syns, false en caso contrario
        return $diferencia < $since;
    } else {
        // No se encontró el ID en la base de datos
        // Cerrar la conexión y liberar recursos
        $stmt->close();
        $conn->close();

        return 3;
    }
}


?>


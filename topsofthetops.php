<?php

include "topgamesbbdd.php";

$games = obtenerGameIDs();
foreach ($games as $game) {
    $game_id = $game['game_id'];
    $game_name = $game['game_name'];

    echo "Game ID: " . $game['game_id'] . ", Game Name: " . $game['game_name'] . "\n";

    // Llamar a la función para actualizar los tops si el game_id no existe en la tabla topOfTheTops
    actualizaTops($game_id);
    obtenerVideoMasPopular($game_id, $game_name);
}

// Ejecutar la función para insertar los datos en la tabla topOfTheTops



//actualiza si es necesario la tabla videos
function actualizaTops($game_id) {
    // Verificar si el game_id ya existe en la tabla topOfTheTops
    if (!gameIdExiste($game_id)) {
        // Llamar a la función para actualizar los tops
        include "topvideosbbdd.php";
        // Aquí puedes colocar el código para actualizar los tops utilizando $game_id y $game_name
        echo "Actualizando tops para el juego con ID: $game_id\n";
    } else {
        echo "El juego con ID: $game_id ya existe en la tabla topOfTheTops. No es necesario actualizar.\n";
    }
}
//obtiene los 3 ids de la tabla topGames
function obtenerGameIDs() {
    // Datos de conexión a la base de datos
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
        echo "No se encontraron game_id en la tabla topGames.";
    }

    // Cerrar conexión a la base de datos
    $conn->close();

    // Retornar el array de game_data
    return $game_data;
}


function obtenerVideoMasPopular($game_id, $game_name) {
    // Datos de conexión a la base de datos
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

    // Consultar la fila con más views en la tabla topVideos
    $sql = "SELECT user_name, duration AS most_viewed_duration, created_at AS most_viewed_created_at, title AS most_viewed_title, views AS most_viewed_views, SUM(views) AS total_views, COUNT(*) AS total_videos FROM topVideos GROUP BY user_name ORDER BY total_views DESC, total_videos DESC LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $video_popular = $result->fetch_assoc();

        // Insertar los datos en la tabla topOfTheTops
        $insert_sql = "INSERT INTO topOfTheTops (game_id, game_name, user_name, total_videos, total_views, most_viewed_title, most_viewed_views, most_viewed_duration, most_viewed_created_at, ultima_actualizacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        // Preparar la sentencia de inserción
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("sssiissss", $game_id, $game_name, $video_popular['user_name'], $video_popular['total_videos'], $video_popular['total_views'], $video_popular['most_viewed_title'], $video_popular['most_viewed_views'], $video_popular['most_viewed_duration'], $video_popular['most_viewed_created_at']);

        // Ejecutar la sentencia de inserción
        if ($stmt->execute()) {
            echo "Datos insertados correctamente en la tabla topOfTheTops.";
        } else {
            echo "Error al insertar datos en la tabla topOfTheTops: " . $stmt->error;
        }

        // Cerrar la sentencia y la conexión
        $stmt->close();
    } else {
        echo "No se encontraron videos en la tabla topVideos.";
    }

    // Cerrar conexión a la base de datos
    $conn->close();
}




function gameIdExiste($game_id) {
    // Datos de conexión a la base de datos
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

    // Consultar si el game_id existe en la tabla topOfTheTops
    $sql = "SELECT COUNT(*) AS count FROM topOfTheTops WHERE game_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $game_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();

    // Cerrar la conexión y liberar recursos
    $stmt->close();
    $conn->close();

    // Devolver true si el game_id existe, false en caso contrario
    return $count > 0;
}


?>


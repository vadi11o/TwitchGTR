<?php

function conexionBBDD() {
    $servername = "localhost";
    $username = "id21862142_equipogtr"; 
    $password = "fahber-Xenmu0-siffat";
    $database = "id21862142_topsofthetopsbbdd";

    $conn = new mysqli($servername, $username, $password, $database);
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }
    return $conn;
}

function setSince() {
    if (isset($_GET['since'])) {
        $since = $_GET['since'];
    } else {
        $since = 600; // Valor por defecto de 10 minutos
    }
    return $since;
}

function obtenerGameIDs($conn) {
    $game_data = array();
    $sql = "SELECT game_id, game_name FROM topGames";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $game_data[] = array(
                'game_id' => $row['game_id'],
                'game_name' => $row['game_name']
            );
        }
    }
    return $game_data;
}

function actualizaTops($game_id) {
    include_once 'topvideosbbdd.php';
}

function actualizarTopOfTheTop($game_id, $game_name, $esta_en_BBDD, $conn) {
    
    $sql = "SELECT user_name, duration AS most_viewed_duration, created_at AS most_viewed_created_at, title AS most_viewed_title, views AS most_viewed_views, SUM(views) AS total_views, COUNT(*) AS total_videos FROM topVideos GROUP BY user_name ORDER BY total_views DESC, total_videos DESC LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $video_popular = $result->fetch_assoc();

        
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
           $stmt->execute();
           $stmt->close();
        } else {
            $top_sql = "INSERT INTO topOfTheTops (game_id, game_name, user_name, total_videos, total_views, most_viewed_title, most_viewed_views, most_viewed_duration, most_viewed_created_at, ultima_actualizacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($top_sql);
            $stmt->bind_param("sssiissss", $game_id, $game_name, $video_popular['user_name'], $video_popular['total_videos'], $video_popular['total_views'], $video_popular['most_viewed_title'], $video_popular['most_viewed_views'], $video_popular['most_viewed_duration'], $video_popular['most_viewed_created_at']);
            $stmt->execute();
            $stmt->close();
        }
    } 

}

function obtenerTopsOfTheTops($game_id, $conn) {
    $sql = "SELECT * FROM topOfTheTops WHERE game_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $game_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return $row;
    } else {
        return null; 
    }
}

function hayQueActualizar($game_id, $since, $conn) {   
    
    $sql = "SELECT ultima_actualizacion FROM topOfTheTops WHERE game_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $game_id);
    $stmt->execute();
    $stmt->store_result();
    $numRows = $stmt->num_rows;
    $fechaActualizacion = null;

    if ($numRows > 0) {
       
        $stmt->bind_result($fechaActualizacion);
        $stmt->fetch();

        
        $fechaActual = new DateTime();  
        $fechaActualizacion = new DateTime($fechaActualizacion);  

        $diferencia = $fechaActual->getTimestamp() - $fechaActualizacion->getTimestamp();

        $stmt->close();
        if($diferencia < $since){
            return 1;
        } else {
            return 2;
        }
    
    } else {
        
        $stmt->close();
        $conn->close();

        return 3;
    }
}

function updatingDataBaseInCaseNeeded($games, $since, $conn) {
    foreach ($games as $game) {
        $actualizado = hayQueActualizar($game['game_id'], $since, $conn);
        if ($actualizado) {
            actualizaTops($game['game_id']);
            actualizarTopOfTheTop($game['game_id'], $game['game_name'], $actualizado, $conn);
        }
    }
}

function getGameInfoFromDataBase($games, $conn) {
    $lista_json = array();
    foreach ($games as $game) {
        $info = obtenerTopsOfTheTops($game['game_id'], $conn);
        if ($info !== null) {
            $lista_json[] = $info;
        }
    }
    return $lista_json;
}

$conn = conexionBBDD();
$since = setSince();
$games = obtenerGameIDs($conn);
updatingDataBaseInCaseNeeded($games, $since, $conn);
$json = json_encode(getGameInfoFromDataBase($games, $conn), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

header('Content-Type: application/json');
echo $json;

$conn->close();

?>

<?php

//Connessione al db
require_once 'connessione_db.php';

//Cors Policy
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

//Check se user esiste
function user_exists($user_id, $connection) {
    try {
        $query = "SELECT COUNT(*) AS user_count FROM users WHERE id = :user_id";
        $statement = $connection->prepare($query);
        $statement->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        return ($result['user_count'] > 0);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["detail" => "Errore nel recupero delle informazioni dal database: " . $e->getMessage()]);
        exit();
    }
}

//Check se post esiste
function post_exists($post_id, $connection) {
    try {
        $query = "SELECT COUNT(*) AS post_count FROM post WHERE id_post = :post_id";
        $statement = $connection->prepare($query);
        $statement->bindParam(":post_id", $post_id, PDO::PARAM_INT);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        return ($result['post_count'] > 0);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["detail" => "Errore nel recupero delle informazioni dal database: " . $e->getMessage()]);
        exit();
    }
}

function get_like_info($user_id, $post_id, $connection) {
    try {
        //L'utente ha già messo like al post?
        $query = "SELECT id_like FROM like_instagram WHERE id_post = :post_id AND id_utente_like = :user_id";
        $statement = $connection->prepare($query);
        $statement->bindParam(":post_id", $post_id, PDO::PARAM_INT);
        $statement->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            http_response_code(200);
            echo json_encode(["like" => true]);
        } else {
            http_response_code(200);
            echo json_encode(["like" => false]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["detail" => "Errore nel recupero delle informazioni sul follow: " . $e->getMessage()]);
    } finally {
        if ($statement !== null) {
            $statement->closeCursor();
        }
    }
}

//Get InputDati
$user_id = $_GET['loggedInUserId'];
$post_id = $_GET['idPost'];

//Check utente
if (!user_exists($user_id, $connection)) {
    http_response_code(404);
    echo json_encode(["detail" => "L'utente specificato non esiste"]);
    exit();
}

//Check post
if (!post_exists($post_id, $connection)) {
    http_response_code(404);
    echo json_encode(["detail" => "Il post specificato non esiste"]);
    exit();
}

get_like_info($user_id, $post_id, $connection);

?>

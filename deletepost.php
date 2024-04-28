<?php

//Cors Policy
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type"); 

//Verifica se è una Delete
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    //Richiesta Options quindi esci
    http_response_code(200);
    exit();
}

function deletepost() {
    global $host, $username, $password, $db, $port;

    //Check se l'url contiene dati
    $data = json_decode(file_get_contents('php://input'), true);

    //Check se idPost esiste
    if (!isset($_GET['idPost'])) {
        http_response_code(400);
        echo json_encode(["detail" => "Parametro idPost mancante"]);
        exit();
    }
    
    //Get idPost
    $id_post = $_GET['idPost'];

    //Dati connessione al db
    $host = "localhost";
    $username = "root";
    $password = "root";
    $db = "instagram";
    $port = 3306;
 
    //Connesione al db
    $connection = new mysqli($host, $username, $password, $db, $port);

    //Check Connesssione
    if ($connection->connect_error) {
        http_response_code(500);
        echo json_encode(["detail" => "Errore di connessione al database: " . $connection->connect_error]);
        exit();
    }

    try {
        //Check if post exists
        $check_post_query = "SELECT id_post FROM post WHERE id_post = ?";
        $check_post_statement = $connection->prepare($check_post_query);
        $check_post_statement->bind_param("i", $id_post);
        $check_post_statement->execute();
        $check_post_statement->store_result();

        if ($check_post_statement->num_rows === 0) {
            //non esiste quindi error
            http_response_code(404);
            echo json_encode(["detail" => "Il post non esiste"]);
            exit();
        }

        $connection->begin_transaction();

        //Elimina i commenti del post
        $delete_comments_query = "DELETE FROM commenti WHERE id_post = ?";
        $delete_comments_statement = $connection->prepare($delete_comments_query);
        $delete_comments_statement->bind_param("i", $id_post);
        $delete_comments_statement->execute();

        //Elimina i like del post
        $delete_likes_query = "DELETE FROM like_instagram WHERE id_post = ?";
        $delete_likes_statement = $connection->prepare($delete_likes_query);
        $delete_likes_statement->bind_param("i", $id_post);
        $delete_likes_statement->execute();

        
        //Commentato perchè almeno funziona al prof
        //ottengo il percorso dell'immagine
         $select_image_query = "SELECT img_post FROM post WHERE id_post = ?";
         $select_image_statement = $connection->prepare($select_image_query);
         $select_image_statement->bind_param("i", $id_post);
         $select_image_statement->execute();
         $select_image_statement->bind_result($img_path);
         $select_image_statement->fetch();
         $select_image_statement->close();
        //elimino l'immagine dalla cartella
         if (file_exists($img_path)) {
             unlink($img_path); // Elimina il file
         }
        
        //Elimina il post
        $delete_post_query = "DELETE FROM post WHERE id_post = ?";
        $delete_post_statement = $connection->prepare($delete_post_query);
        $delete_post_statement->bind_param("i", $id_post);
        $delete_post_statement->execute();
    
        //Check if tutto ok
        if ($delete_post_statement->affected_rows > 0) {
            //tutto ok
            $connection->commit();
            http_response_code(200);
            echo json_encode(["Message" => "OK"]);
        } else {
            //error
            $connection->rollback();
            http_response_code(500);
            echo json_encode(["Message" => "Errore durante l'eliminazione nella tabella post"]);
        }
    } catch (Exception $e) {
        //error nel try
        $connection->rollback();
        http_response_code(500);
        echo json_encode(["Message" => "Errore durante l'eliminazione nella tabella post: " . $e->getMessage()]);
        var_dump($e); 
    } finally {
        //chiudi connessione e dichiarazioni
        $check_post_statement->close();
        $delete_comments_statement->close();
        $delete_likes_statement->close();
        $delete_post_statement->close();
        $connection->close();
    }
}

deletepost();

?>

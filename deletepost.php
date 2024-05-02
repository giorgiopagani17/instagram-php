<?php
//Connessione al db
require_once 'connessione_db.php';

//Cors Policy
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type"); // Aggiungi questa riga per consentire il campo "content-type"

//Gestione richieste Option
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

function deletePost() {
    global $connection; 
    //Check se Delete
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        http_response_code(405); // Metodo non consentito
        echo json_encode(["detail" => "Metodo non consentito: " . $_SERVER['REQUEST_METHOD']]);
        exit();
    }

    //Check InputDati
    if (!isset($_GET['idPost'])) {
        http_response_code(400); // Bad Request
        echo json_encode(["detail" => "Parametro idPost mancante"]);
        exit();
    }

    //Get InputDati
    $id_post = $_GET['idPost'];

    try {
        //Inizia la transazione
        $connection->beginTransaction();

        //Check se post esiste
        $check_post_query = "SELECT id_post FROM post WHERE id_post = ?";
        $check_post_statement = $connection->prepare($check_post_query);
        $check_post_statement->execute([$id_post]);

        if ($check_post_statement->rowCount() === 0) {
            http_response_code(404); 
            echo json_encode(["detail" => "Il post non esiste"]);
            exit();
        }

        //Siccome collegati da chiavi esterne devo prima eliminare Commenti e Like
        //Delete commenti
        $delete_comments_query = "DELETE FROM commenti WHERE id_post = ?";
        $delete_comments_statement = $connection->prepare($delete_comments_query);
        $delete_comments_statement->execute([$id_post]);

        //Delete like
        $delete_likes_query = "DELETE FROM like_instagram WHERE id_post = ?";
        $delete_likes_statement = $connection->prepare($delete_likes_query);
        $delete_likes_statement->execute([$id_post]);

        //Ottieni il percorso dell'immagine ed eliminala dalla cartella
        $select_image_query = "SELECT img_post FROM post WHERE id_post = ?";
        $select_image_statement = $connection->prepare($select_image_query);
        $select_image_statement->execute([$id_post]);
        $img_path = $select_image_statement->fetchColumn();
        $select_image_statement->closeCursor();
        
        //Eliminala se esiste
        // if (file_exists($img_path)) {
        //     unlink($img_path);
        // }
        
        //Delete post
        $delete_post_query = "DELETE FROM post WHERE id_post = ?";
        $delete_post_statement = $connection->prepare($delete_post_query);
        $delete_post_statement->execute([$id_post]);

        if ($delete_post_statement->rowCount() > 0) {
            $connection->commit();
            http_response_code(200); // OK
            echo json_encode(["Message" => "Post eliminato con successo!"]);
        } else {
            $connection->rollBack();
            http_response_code(500);
            echo json_encode(["Message" => "Errore durante l'eliminazione nella tabella post"]);
        }
    } catch (PDOException $e) {
        $connection->rollBack();
        http_response_code(500);
        echo json_encode(["Message" => "Errore durante l'eliminazione nella tabella post: " . $e->getMessage()]);
    } finally {
        unset($check_post_statement);
        unset($delete_comments_statement);
        unset($delete_likes_statement);
        unset($delete_post_statement);
    }
}

deletePost();
?>

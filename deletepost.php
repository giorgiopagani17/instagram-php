<?php
// Includi il file per la connessione al database
require_once 'connessione_db.php';

// Imposta l'header CORS per consentire le richieste da qualsiasi origine
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type"); // Aggiungi questa riga per consentire il campo "content-type"

// Verifica se è una richiesta OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Rispondi alla richiesta OPTIONS
    http_response_code(200);
    exit();
}

// Funzione per gestire la richiesta di eliminazione del post
function deletePost() {
    global $connection; // Utilizza la variabile di connessione globale
    
    // Verifica se il metodo della richiesta è DELETE
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        http_response_code(405); // Metodo non consentito
        echo json_encode(["detail" => "Metodo non consentito: " . $_SERVER['REQUEST_METHOD']]);
        exit();
    }

    // Check se il parametro idPost è presente nell'URL
    if (!isset($_GET['idPost'])) {
        http_response_code(400); // Bad Request
        echo json_encode(["detail" => "Parametro idPost mancante"]);
        exit();
    }

    // Ottieni idPost dall'URL
    $id_post = $_GET['idPost'];

    try {
        // Inizia la transazione
        $connection->beginTransaction();

        // Check se il post esiste
        $check_post_query = "SELECT id_post FROM post WHERE id_post = ?";
        $check_post_statement = $connection->prepare($check_post_query);
        $check_post_statement->execute([$id_post]);

        if ($check_post_statement->rowCount() === 0) {
            // Il post non esiste
            http_response_code(404); // Not Found
            echo json_encode(["detail" => "Il post non esiste"]);
            exit();
        }

        // Elimina i commenti del post
        $delete_comments_query = "DELETE FROM commenti WHERE id_post = ?";
        $delete_comments_statement = $connection->prepare($delete_comments_query);
        $delete_comments_statement->execute([$id_post]);

        // Elimina i like del post
        $delete_likes_query = "DELETE FROM like_instagram WHERE id_post = ?";
        $delete_likes_statement = $connection->prepare($delete_likes_query);
        $delete_likes_statement->execute([$id_post]);

        // Ottieni il percorso dell'immagine e elimina l'immagine dalla cartella
        $select_image_query = "SELECT img_post FROM post WHERE id_post = ?";
        $select_image_statement = $connection->prepare($select_image_query);
        $select_image_statement->execute([$id_post]);
        $img_path = $select_image_statement->fetchColumn();
        $select_image_statement->closeCursor();
        
        // Elimina l'immagine dalla cartella se esiste
        if (file_exists($img_path)) {
            unlink($img_path);
        }
        
        // Elimina il post
        $delete_post_query = "DELETE FROM post WHERE id_post = ?";
        $delete_post_statement = $connection->prepare($delete_post_query);
        $delete_post_statement->execute([$id_post]);

        // Check se tutto ok
        if ($delete_post_statement->rowCount() > 0) {
            // Tutto ok
            $connection->commit();
            http_response_code(200); // OK
            echo json_encode(["Message" => "OK"]);
        } else {
            // Errore
            $connection->rollBack();
            http_response_code(500);
            echo json_encode(["Message" => "Errore durante l'eliminazione nella tabella post"]);
        }
    } catch (PDOException $e) {
        // Errore nel try
        $connection->rollBack();
        http_response_code(500);
        echo json_encode(["Message" => "Errore durante l'eliminazione nella tabella post: " . $e->getMessage()]);
    } finally {
        // Chiudi connessione e dichiarazioni
        unset($check_post_statement);
        unset($delete_comments_statement);
        unset($delete_likes_statement);
        unset($delete_post_statement);
    }
}

// Chiamare la funzione deletePost per gestire la richiesta DELETE
deletePost();
?>

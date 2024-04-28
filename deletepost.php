<?php

// Imposta l'header CORS per consentire le richieste da qualsiasi origine
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE, OPTIONS"); // Aggiungi OPTIONS qui
header("Access-Control-Allow-Headers: Content-Type"); // Aggiungi questa riga per consentire il campo "content-type"

// Verifica il metodo della richiesta
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Questa è una richiesta OPTIONS, rispondi con OK e termina lo script
    http_response_code(200);
    exit();
}

// Funzione per gestire la richiesta di follow
function deletepost() {
    global $host, $username, $password, $db, $port;

    // Verifica se il corpo della richiesta contiene dati JSON
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($_GET['idPost'])) {
        // Se non è presente, restituisci un errore
        http_response_code(400);
        echo json_encode(["detail" => "Parametro idPost mancante"]);
        exit();
    }
    
    // Verifica se il valore di idPost è un numero
    if (!is_numeric($_GET['idPost'])) {
        // Se non è un numero, restituisci un errore
        http_response_code(400);
        echo json_encode(["detail" => "Parametro idPost non valido"]);
        exit();
    }
    
    $id_post = $_GET['idPost'];

    // Connessione al database MySQL
    $host = "localhost";
    $username = "root";
    $password = "root";
    $db = "instagram";
    $port = 3306;
 
    // Connessione al database
    $connection = new mysqli($host, $username, $password, $db, $port);

    // Verifica se la connessione è riuscita
    if ($connection->connect_error) {
        http_response_code(500);
        echo json_encode(["detail" => "Errore di connessione al database: " . $connection->connect_error]);
        exit();
    }

    try {
        // Verifica se il post esiste
        $check_post_query = "SELECT id_post FROM post WHERE id_post = ?";
        $check_post_statement = $connection->prepare($check_post_query);
        $check_post_statement->bind_param("i", $id_post);
        $check_post_statement->execute();
        $check_post_statement->store_result();

        if ($check_post_statement->num_rows === 0) {
            // Il post non esiste, restituisci un errore
            http_response_code(404);
            echo json_encode(["detail" => "Il post non esiste"]);
            exit();
        }

        // Inizia una transazione
        $connection->begin_transaction();

        // Elimina i commenti correlati al post specificato
        $delete_comments_query = "DELETE FROM commenti WHERE id_post = ?";
        $delete_comments_statement = $connection->prepare($delete_comments_query);
        $delete_comments_statement->bind_param("i", $id_post);
        $delete_comments_statement->execute();

        // Elimina le righe correlate nella tabella like_instagram per il post specificato
        $delete_likes_query = "DELETE FROM like_instagram WHERE id_post = ?";
        $delete_likes_statement = $connection->prepare($delete_likes_query);
        $delete_likes_statement->bind_param("i", $id_post);
        $delete_likes_statement->execute();

        
        //Commentato perchè almeno funziona al prof
        //ottengo il percorso dell'immagine
        // $select_image_query = "SELECT img_post FROM post WHERE id_post = ?";
        // $select_image_statement = $connection->prepare($select_image_query);
        // $select_image_statement->bind_param("i", $id_post);
        // $select_image_statement->execute();
        // $select_image_statement->bind_result($img_path);
        // $select_image_statement->fetch();
        // $select_image_statement->close();
        //elimino l'immagine dalla cartella
        // if (file_exists($img_path)) {
        //     unlink($img_path); // Elimina il file
        // }
        
        // Prepara e esegui l'eliminazione nella tabella post
        $delete_post_query = "DELETE FROM post WHERE id_post = ?";
        $delete_post_statement = $connection->prepare($delete_post_query);
        $delete_post_statement->bind_param("i", $id_post);
        $delete_post_statement->execute();
    
        // Verifica se l'eliminazione è avvenuta con successo
        if ($delete_post_statement->affected_rows > 0) {
            // Conferma la transazione
            $connection->commit();
            http_response_code(200);
            echo json_encode(["Message" => "OK"]);
        } else {
            // Rollback in caso di errore
            $connection->rollback();
            http_response_code(500);
            echo json_encode(["Message" => "Errore durante l'eliminazione nella tabella post"]);
        }
    } catch (Exception $e) {
        // Rollback in caso di eccezione
        $connection->rollback();
        http_response_code(500);
        echo json_encode(["Message" => "Errore durante l'eliminazione nella tabella post: " . $e->getMessage()]);
        var_dump($e); // Visualizza l'eccezione per il debugging
    } finally {
        // Chiudi le dichiarazioni e la connessione
        $check_post_statement->close();
        $delete_comments_statement->close();
        $delete_likes_statement->close();
        $delete_post_statement->close();
        $connection->close();
    }
}

// Chiamare la funzione follow per gestire la richiesta POST
deletepost();

?>

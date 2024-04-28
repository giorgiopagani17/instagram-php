<?php

// Imposta l'header CORS per consentire le richieste da qualsiasi origine
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type"); // Aggiungi questa riga per consentire il campo "content-type"

// Funzione per gestire la richiesta di like
function like() {
    // Verifica se il corpo della richiesta contiene dati JSON
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($_GET['loggedInUserId'])) {
        // Se non è presente, restituisci un errore
        http_response_code(400);
        echo json_encode(["detail" => "Parametro loggedInUserId mancante"]);
        exit();
    }
    
    // Verifica se il valore di loggedInUserId è un numero
    if (!is_numeric($_GET['loggedInUserId'])) {
        // Se non è un numero, restituisci un errore
        http_response_code(400);
        echo json_encode(["detail" => "Parametro loggedInUserId non valido"]);
        exit();
    }

    if (!isset($_GET['idPost'])) {
        // Se non è presente, restituisci un errore
        http_response_code(400);
        echo json_encode(["detail" => "Parametro idUserFollowed mancante"]);
        exit();
    }
    
    // Verifica se il valore di idPost è un numero
    if (!is_numeric($_GET['idPost'])) {
        // Se non è un numero, restituisci un errore
        http_response_code(400);
        echo json_encode(["detail" => "Parametro idPost non valido"]);
        exit();
    }
    
    $loggedInUser = intval($_GET['loggedInUserId']);
    $id_post = intval($_GET['idPost']);

    // Connessione al database MySQL
    require_once 'connessione_db.php';

    try {
        // Verifica se il post è già stato likato dall'utente
        $query_check_like = "SELECT id_like FROM like_instagram WHERE id_post = ? AND id_utente_like = ?";
        $statement_check_like = $connection->prepare($query_check_like);
        $statement_check_like->bindParam(1, $id_post, PDO::PARAM_INT);
        $statement_check_like->bindParam(2, $loggedInUser, PDO::PARAM_INT);
        $statement_check_like->execute();
        $result_check_like = $statement_check_like->fetch(PDO::FETCH_ASSOC);

        // Se esiste già un like, restituisci un messaggio di errore
        if ($result_check_like) {
            http_response_code(400);
            echo json_encode(["detail" => "Il post è già stato likato da questo utente"]);
            exit();
        }

        // Prepara e esegui l'inserimento nella tabella like
        $query_insert_like = "INSERT INTO like_instagram (id_post, id_utente_like) VALUES (?, ?)";
        $statement_insert_like = $connection->prepare($query_insert_like);
        $statement_insert_like->bindParam(1, $id_post, PDO::PARAM_INT);
        $statement_insert_like->bindParam(2, $loggedInUser, PDO::PARAM_INT);
        $statement_insert_like->execute();

        // Verifica se l'inserimento è avvenuto con successo
        if ($statement_insert_like->rowCount() > 0) {
            http_response_code(200);
            echo json_encode(["Message" => "OK"]);
        } else {
            http_response_code(500);
            echo json_encode(["Message" => "Errore durante l'inserimento nella tabella like"]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["Message" => "Errore: " . $e->getMessage()]);
    } finally {
        // Chiudi il cursore
        if ($statement_check_like) {
            $statement_check_like->closeCursor();
        }
        // Chiudi la connessione
        $connection = null;
    }
}

// Chiamare la funzione like per gestire la richiesta POST
like();

?>

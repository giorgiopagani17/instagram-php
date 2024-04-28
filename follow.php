<?php

// Imposta l'header CORS per consentire le richieste da qualsiasi origine
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type"); // Aggiungi questa riga per consentire il campo "content-type"

// Funzione per gestire la richiesta di follow
function follow() {
    require_once 'connessione_db.php'; // Includi il file per la connessione al database

    // Verifica se il corpo della richiesta contiene dati JSON
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($_GET['loggedInUserId'])) {
        // Se il parametro loggedInUserId non è presente, restituisci un errore
        http_response_code(400);
        echo json_encode(["detail" => "Parametro loggedInUserId mancante"]);
        exit();
    }
    
    // Verifica se il valore di loggedInUserId è un numero
    if (!is_numeric($_GET['loggedInUserId'])) {
        // Se loggedInUserId non è un numero, restituisci un errore
        http_response_code(400);
        echo json_encode(["detail" => "Parametro loggedInUserId non valido"]);
        exit();
    }

    if (!isset($_GET['idUserFollowed'])) {
        // Se il parametro idUserFollowed non è presente, restituisci un errore
        http_response_code(400);
        echo json_encode(["detail" => "Parametro idUserFollowed mancante"]);
        exit();
    }
    
    // Verifica se il valore di idUserFollowed è un numero
    if (!is_numeric($_GET['idUserFollowed'])) {
        // Se idUserFollowed non è un numero, restituisci un errore
        http_response_code(400);
        echo json_encode(["detail" => "Parametro idUserFollowed non valido"]);
        exit();
    }
    
    $loggedInUser = intval($_GET['loggedInUserId']);
    $idUserFollowed = intval($_GET['idUserFollowed']);

    try {
        // Controlla se l'utente è già seguendo l'altro utente
        $check_query = "SELECT * FROM follow WHERE id_utente_follower = ? AND id_utente_seguito = ?";
        $check_statement = $connection->prepare($check_query);
        $check_statement->bindParam(1, $loggedInUser, PDO::PARAM_INT);
        $check_statement->bindParam(2, $idUserFollowed, PDO::PARAM_INT);
        $check_statement->execute();
        $result = $check_statement->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // Se l'utente sta già seguendo l'altro utente, restituisci un errore
            http_response_code(400);
            echo json_encode(["detail" => "L'utente sta già seguendo l'altro utente"]);
            exit();
        }

        // Prepara e esegui l'inserimento nella tabella follow
        $query = "INSERT INTO follow (id_utente_follower, id_utente_seguito) VALUES (?, ?)";
        $statement = $connection->prepare($query);
        $statement->bindParam(1, $loggedInUser, PDO::PARAM_INT);
        $statement->bindParam(2, $idUserFollowed, PDO::PARAM_INT);
        $statement->execute();

        // Verifica se l'inserimento è avvenuto con successo
        if ($statement->rowCount() > 0) {
            // Se l'inserimento ha avuto successo, restituisci una conferma
            http_response_code(200);
            echo json_encode(["Message" => "OK"]);
        } else {
            // Se l'inserimento non ha avuto successo, restituisci un errore
            http_response_code(500);
            echo json_encode(["Message" => "Errore durante l'inserimento nella tabella follow"]);
        }
    } catch (PDOException $e) {
        // Gestione dell'eccezione in caso di errore durante l'inserimento
        http_response_code(500);
        echo json_encode(["Message" => "Errore durante l'inserimento nella tabella follow: " . $e->getMessage()]);
    } finally {
        // Chiudi il cursore
        $check_statement->closeCursor();
        $statement->closeCursor();
    }
}

// Chiamare la funzione follow per gestire la richiesta POST
follow();

?>

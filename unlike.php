<?php

// Imposta l'header CORS per consentire le richieste da qualsiasi origine
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type"); // Aggiungi questa riga per consentire il campo "content-type"

// Funzione per gestire la richiesta di "unlike"
function unlike() {
    global $host, $username, $password, $db, $port;

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

    if (!isset($_GET['idPost'])) {
        // Se il parametro idPost non è presente, restituisci un errore
        http_response_code(400);
        echo json_encode(["detail" => "Parametro idPost mancante"]);
        exit();
    }
    
    // Verifica se il valore di idPost è un numero
    if (!is_numeric($_GET['idPost'])) {
        // Se idPost non è un numero, restituisci un errore
        http_response_code(400);
        echo json_encode(["detail" => "Parametro idPost non valido"]);
        exit();
    }
    
    $loggedInUser = intval($_GET['loggedInUserId']);
    $id_post = intval($_GET['idPost']);

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
        // Se la connessione al database fallisce, restituisci un errore
        http_response_code(500);
        echo json_encode(["detail" => "Errore di connessione al database: " . $connection->connect_error]);
        exit();
    }

    try {
        // Prepara e esegui l'eliminazione del like nella tabella like_instagram
        $query = "DELETE FROM like_instagram WHERE id_post = ? AND id_utente_like = ?";
        $statement = $connection->prepare($query);
        $statement->bind_param("ii", $id_post, $loggedInUser);
        $statement->execute();
    
        // Verifica se l'eliminazione è avvenuta con successo
        if ($statement->affected_rows > 0) {
            // Se l'eliminazione ha avuto successo, restituisci una conferma
            http_response_code(200);
            echo json_encode(["Message" => "OK"]);
        } else {
            // Se non ci sono stati like da eliminare, restituisci un errore
            http_response_code(400);
            echo json_encode(["Message" => "Non è stato trovato alcun like da eliminare per questo post"]);
        }
    } catch (Exception $e) {
        // Gestione dell'eccezione in caso di errore durante l'eliminazione
        http_response_code(500);
        echo json_encode(["Message" => "Errore durante l'eliminazione nella tabella like: " . $e->getMessage()]);    
    } finally {
        // Chiudi la connessione e il cursore
        $statement->close();
        $connection->close();
    }
}

// Chiamare la funzione unlike per gestire la richiesta DELETE
unlike();


?>

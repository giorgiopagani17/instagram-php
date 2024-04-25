<?php

// Imposta l'header CORS per consentire le richieste da qualsiasi origine
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type"); // Aggiungi questa riga per consentire il campo "content-type"

// Funzione per gestire la richiesta di unfollow
function unfollow() {
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
        // Controlla se l'utente è già seguito
        $check_query = "SELECT * FROM follow WHERE id_utente_follower = ? AND id_utente_seguito = ?";
        $check_statement = $connection->prepare($check_query);
        $check_statement->bind_param("ii", $loggedInUser, $idUserFollowed);
        $check_statement->execute();
        $result = $check_statement->get_result();

        if ($result->num_rows === 0) {
            // Se l'utente non sta già seguendo l'altro utente, restituisci un errore
            http_response_code(400);
            echo json_encode(["detail" => "L'utente non sta seguendo già l'altro utente"]);
            exit();
        }

        // Prepara e esegui l'eliminazione nella tabella follow
        $query = "DELETE FROM follow WHERE id_utente_follower = ? AND id_utente_seguito = ?";
        $statement = $connection->prepare($query);
        $statement->bind_param("ii", $loggedInUser, $idUserFollowed);
        $statement->execute();
    
        // Verifica se l'eliminazione è avvenuta con successo
        if ($statement->affected_rows > 0) {
            // Se l'eliminazione ha avuto successo, restituisci una conferma
            http_response_code(200);
            echo json_encode(["Message" => "OK"]);
        } else {
            // Se non ci sono stati record da eliminare, restituisci un errore
            http_response_code(400);
            echo json_encode(["Message" => "Nessun record da eliminare nella tabella follow"]);
        }
    } catch (Exception $e) {
        // Gestione dell'eccezione in caso di errore durante l'eliminazione
        http_response_code(500);
        echo json_encode(["Message" => "Errore durante l'eliminazione nella tabella follow: " . $e->getMessage()]);    
    } finally {
        // Chiudi la connessione e il cursore
        $check_statement->close();
        $statement->close();
        $connection->close();
    }
}

// Chiamare la funzione unfollow per gestire la richiesta DELETE
unfollow();

?>

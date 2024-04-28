<?php

// Imposta l'header CORS per consentire le richieste da qualsiasi origine
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT"); // Aggiungi i metodi consentiti per le richieste CORS
header("Access-Control-Allow-Headers: Content-Type"); // Aggiungi gli header consentiti per le richieste CORS

// Assicurati che il server risponda correttamente alle richieste OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Connessione al database MySQL
require_once 'connessione_db.php';

// Funzione per aggiornare le informazioni dell'utente nel database
function updateUserInformation($user_id, $username, $password, $description, $connection) {
    try {
        // Esegui una query per aggiornare le informazioni dell'utente nel database
        $query = "UPDATE users SET username = ?, password = ?, descrizione = ? WHERE id = ?";
        $statement = $connection->prepare($query);
        $statement->bindParam(1, $username);
        $statement->bindParam(2, $password);
        $statement->bindParam(3, $description);
        $statement->bindParam(4, $user_id);
        $statement->execute();
        
        // Ottieni il numero di righe interessate dall'aggiornamento
        $affected_rows = $statement->rowCount();
        
        return $affected_rows;
    } catch (PDOException $e) {
        // Gestisci eventuali errori durante l'aggiornamento delle informazioni
        throw new Exception("Errore durante l'aggiornamento delle informazioni dell'utente: " . $e->getMessage());
    }
}

// Ottieni i dati dalla richiesta JSON
$request_body = file_get_contents('php://input');
$data = json_decode($request_body, true);

// Verifica se tutti i campi richiesti sono presenti nei dati
if (isset($data["username"]) && isset($data["password"]) && isset($data["description"])) {
    // Ottieni i dati dalla richiesta JSON
    $username = $data["username"];
    $password = $data["password"];
    $description = $data["description"];
    
    // Ottieni l'id dell'utente dal percorso dell'URL
    $user_id = isset($_GET['loggedInUserId']) ? intval($_GET['loggedInUserId']) : null;
    
    // Verifica se l'id dell'utente è valido
    if ($user_id !== null) {
        try {
            // Aggiorna le informazioni dell'utente nel database
            $affected_rows = updateUserInformation($user_id, $username, $password, $description, $connection);
            
            // Verifica se le informazioni sono state aggiornate con successo
            if ($affected_rows > 0) {
                // Restituisci un messaggio di successo
                echo json_encode(["message" => "Informazioni utente aggiornate con successo"]);
            } else {
                // Restituisci un messaggio di errore se le informazioni non sono state aggiornate
                http_response_code(404);
                echo json_encode(["detail" => "Modifiche non avvenute"]);
            }
        } catch (Exception $e) {
            // Restituisci un messaggio di errore se si verifica un'eccezione durante l'aggiornamento
            http_response_code(500);
            echo json_encode(["detail" => $e->getMessage()]);
        }
    } else {
        // Restituisci un messaggio di errore se l'id dell'utente non è stato fornito
        http_response_code(400);
        echo json_encode(["detail" => "ID dell'utente non fornito"]);
    }
} else {
    // Restituisci un messaggio di errore se i dati richiesti non sono stati forniti
    http_response_code(400);
    echo json_encode(["detail" => "Dati richiesti non forniti"]);
}

?>

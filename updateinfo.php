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
function updateUserInformation($user_id, $username, $new_password, $description, $connection) {
    try {
        // Esegui una query per ottenere le informazioni attuali dell'utente
        $stmt_get_user_info = $connection->prepare("SELECT username, password, descrizione FROM users WHERE id = ?");
        $stmt_get_user_info->execute([$user_id]);
        $current_user_info = $stmt_get_user_info->fetch(PDO::FETCH_ASSOC);
        
        $encrypted_new_password = encryptPassword($new_password);

        // Array per memorizzare le query da eseguire
        $queries = [];

        // Verifica se la descrizione è diversa
        if ($current_user_info['descrizione'] !== $description) {
            // Aggiungi la query per aggiornare la descrizione
            $queries[] = ["UPDATE users SET descrizione = ? WHERE id = ?", [$description, $user_id]];
        }

        // Verifica se la password è stata fornita e se è diversa
        if (!empty($new_password) && !password_verify($new_password, $current_user_info['password'])) {
            // Aggiungi la query per aggiornare la password
            $queries[] = ["UPDATE users SET password = ? WHERE id = ?", [$encrypted_new_password, $user_id]];
        }

        // Verifica se lo username è diverso
        if ($username !== $current_user_info['username']) {
            // Aggiungi la query per aggiornare lo username
            $queries[] = ["UPDATE users SET username = ? WHERE id = ?", [$username, $user_id]];
        }

        // Esegui tutte le query
        foreach ($queries as $query) {
            $stmt = $connection->prepare($query[0]);
            $stmt->execute($query[1]);
        }

        // Verifica se sono state eseguite query
        return count($queries) > 0;
    } catch (PDOException $e) {
        // Gestisci eventuali errori durante l'aggiornamento delle informazioni
        throw new Exception("Errore durante l'aggiornamento delle informazioni dell'utente: " . $e->getMessage());
    }
}

// Funzione per cifrare la password
function encryptPassword($password) {
    // Utilizza un algoritmo di hashing sicuro per cifrare la password
    return password_hash($password, PASSWORD_DEFAULT);
}

// Ottieni i dati dalla richiesta JSON
$request_body = file_get_contents('php://input');
$data = json_decode($request_body, true);

// Verifica se tutti i campi richiesti sono presenti nei dati
if (isset($data["username"]) && isset($data["password"]) && isset($data["description"])) {
    // Ottieni i dati dalla richiesta JSON
    $username = $data["username"];
    $new_password = $data["password"];
    $description = $data["description"];

    // Ottieni l'id dell'utente dal percorso dell'URL
    $user_id = isset($_GET['loggedInUserId']) ? intval($_GET['loggedInUserId']) : null;

    // Verifica se l'id dell'utente è valido
    if ($user_id !== null) {
        try {
            // Aggiorna le informazioni dell'utente nel database
            $update_performed = updateUserInformation($user_id, $username, $new_password, $description, $connection);

            // Verifica se l'aggiornamento è stato effettuato
            if ($update_performed) {
                // Restituisci un messaggio di successo
                echo json_encode(["message" => "Informazioni utente aggiornate con successo"]);
            } else {
                // Restituisci un messaggio di errore se nessun aggiornamento è stato effettuato
                http_response_code(204);
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

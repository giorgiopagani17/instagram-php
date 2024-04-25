<?php

// Imposta l'header CORS per consentire le richieste da qualsiasi origine
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type"); // Aggiungi questa riga per consentire il campo "content-type"

// Connessione al database MySQL
$host = "localhost";
$username = "root";
$password = "root";
$db = "instagram";
$port = 3306;

// Connessione al database
$connection = new mysqli($host, $username, $password, $db, $port);

// Verifica la connessione
if ($connection->connect_error) {
    die("Connessione al database fallita: " . $connection->connect_error);
}

// Funzione per ottenere la biografia e la password dell'utente
function getUserInfoToUpdate($user_id) {
    global $connection;
    $user_id = intval($user_id); // Converte l'id in un intero per sicurezza
    
    // Esegui una query per ottenere la biografia e la password dell'utente specificato
    $query = "SELECT descrizione, password FROM users WHERE id = $user_id";
    $result = $connection->query($query);
    
    if ($result->num_rows > 0) {
        // Se ci sono risultati, restituisci il risultato come array associativo
        $row = $result->fetch_assoc();
        return $row;
    } else {
        return null; // Se l'utente non viene trovato, restituisci null
    }
}

// Ottieni l'id dell'utente loggato
$loggedInUser = isset($_GET['loggedInUserId']) ? intval($_GET['loggedInUserId']) : null;

// Se l'id dell'utente loggato è valido, esegui la funzione per ottenere le informazioni dell'utente
if ($loggedInUser !== null) {
    $userInfo = getUserInfoToUpdate($loggedInUser);
    if ($userInfo !== null) {
        // Se le informazioni dell'utente sono state ottenute con successo, stampale come JSON
        echo json_encode($userInfo);
    } else {
        // Se l'utente non è stato trovato, restituisci un messaggio di errore JSON
        echo json_encode(["detail" => "Utente non trovato"]);
    }
} else {
    // Se l'id dell'utente loggato non è stato fornito, restituisci un messaggio di errore JSON
    echo json_encode(["detail" => "ID dell'utente non fornito"]);
}

// Chiudi la connessione al database
$connection->close();
?>

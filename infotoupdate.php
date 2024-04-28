<?php

// Imposta l'header CORS per consentire le richieste da qualsiasi origine
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type"); // Aggiungi questa riga per consentire il campo "content-type"

// Includi il file per la connessione al database
require_once 'connessione_db.php';

// Funzione per ottenere la biografia e la password dell'utente
function getUserInfoToUpdate($user_id, $connection) {
    $user_id = intval($user_id); // Converte l'id in un intero per sicurezza
    
    // Prepara e esegui una query per ottenere la biografia e la password dell'utente specificato
    $query = "SELECT descrizione, password FROM users WHERE id = :user_id";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    // Ottieni il risultato come array associativo
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $userInfo;
}

// Ottieni l'id dell'utente loggato
$loggedInUser = isset($_GET['loggedInUserId']) ? intval($_GET['loggedInUserId']) : null;

// Se l'id dell'utente loggato è valido, esegui la funzione per ottenere le informazioni dell'utente
if ($loggedInUser !== null) {
    $userInfo = getUserInfoToUpdate($loggedInUser, $connection);
    if ($userInfo !== false) {
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
$connection = null;

?>

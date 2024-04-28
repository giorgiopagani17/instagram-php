<?php
//Connessione al db
require_once 'connessione_db.php';

// Imposta l'header CORS per consentire le richieste da qualsiasi origine
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type"); // Aggiungi questa riga per consentire il campo "content-type"

// Funzione per la registrazione di un nuovo utente
function registerUser($username, $email, $password) {
    global $connection;

    // Verifica se lo username esiste già
    $stmt_check_username = $connection->prepare("SELECT * FROM users WHERE username = ?");
    $stmt_check_username->execute([$username]);
    $existing_username = $stmt_check_username->fetch();

    if ($existing_username) {
        http_response_code(400);
        echo json_encode(["detail" => "Lo username è già in uso."]);
        exit();
    }

    // Verifica se l'email esiste già
    $stmt_check_email = $connection->prepare("SELECT * FROM users WHERE email = ?");
    $stmt_check_email->execute([$email]);
    $existing_email = $stmt_check_email->fetch();

    if ($existing_email) {
        http_response_code(400);
        echo json_encode(["detail" => "L'email è già in uso."]);
        exit();
    }

    // Inserimento del nuovo utente nel database
    $imgUtente = 'C:/Users/giorg/Instagram/imgUtenti/default.jpg'; // Assumiamo un'immagine predefinita per tutti gli utenti
    $description = ""; // Assumiamo una descrizione vuota per tutti gli utenti

    $stmt_insert_user = $connection->prepare("INSERT INTO users (username, email, password, img, descrizione) VALUES (?, ?, ?, ?, ?)");
    $stmt_insert_user->execute([$username, $email, $password, $imgUtente, $description]);

    echo json_encode(["Message" => "OK"]);
}

// Gestione della richiesta di registrazione
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'];
    $email = $data['email'];
    $password = $data['password'];

    try {
        registerUser($username, $email, $password);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["detail" => "Errore durante la registrazione: " . $e->getMessage()]);
    }
}

?>

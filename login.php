<?php
// Connessione al database
require_once 'connessione_db.php';

// Imposta l'header CORS per consentire le richieste da qualsiasi origine
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type"); // Aggiungi questa riga per consentire il campo "content-type"

// Login Utente
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data['email'];
        $password = $data['password'];
        
        // Ottieni le informazioni utente dal database utilizzando l'email
        $stmt = $connection->prepare("SELECT id, username, password, img FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user_row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifica se l'utente esiste e la password è corretta
        if ($user_row && password_verify($password, $user_row['password'])) {
            // Se le credenziali sono valide, restituisci solo le informazioni necessarie dell'utente
            unset($user_row['password']); // Rimuovi la password dalla risposta
            echo json_encode(["success" => true, "user" => $user_row], JSON_UNESCAPED_SLASHES);
        } else {
            // Se le credenziali non sono valide, restituisci un errore 401
            http_response_code(401);
            echo json_encode(["detail" => "Credenziali non valide"]);
        }
    } catch (PDOException $e) {
        // Gestisci gli errori di connessione al database
        http_response_code(500);
        echo json_encode(["detail" => "Errore durante il login: Impossibile accedere al database"]);
    }
}

?>

<?php
//Connessione al db
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

        $stmt = $connection->prepare("SELECT id, username, img FROM users WHERE email = ? AND password = ?");
        $stmt->execute([$email, $password]);
        $user_row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user_row) {
            echo json_encode(["success" => true, "user" => $user_row], JSON_UNESCAPED_SLASHES);
        } else {
            http_response_code(401);
            echo json_encode(["detail" => "Credenziali non valide"]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["detail" => "Errore durante il login: Impossibile accedere al database"]);
    }
}

// Altro codice...
?>

<?php

// Connessione al database MySQL
$host = "localhost";
$username = "root";
$password = "root";
$db = "instagram";
$port = 3306;

try {
    $connection = new PDO("mysql:host=$host;dbname=$db;port=$port", $username, $password);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["detail" => "Errore di connessione al database: " . $e->getMessage()]);
    exit(); // Esci dallo script in caso di errore di connessione al database
}

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

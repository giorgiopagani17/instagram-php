<?php

// Connessione al database MySQL
$host = "localhost";
$username = "root";
$password = "root";
$db = "instagram";
$port = 3306;

// Connessione al database
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
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type"); // Aggiungi questa riga per consentire il campo "content-type"

// Verifica se il parametro search è presente nell'URL
if (isset($_GET['idUser'])) {
    try {
        // recupera max 5 utenti che non sono seguiti dall'utente specificato
        $idUser = $_GET['idUser'];
        $query = "SELECT u.id, u.username
        FROM users u 
        LEFT JOIN follow f ON u.id = f.id_utente_seguito AND f.id_utente_follower = ?
        WHERE f.id_utente_seguito IS NULL
        AND u.id <> ?
        ORDER BY RAND()
        LIMIT 5";
        $stmt = $connection->prepare($query);
        $stmt->execute([$idUser, $idUser]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($users);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["detail" => "Errore nel recupero degli utenti: " . $e->getMessage()]);
    }
} else {
    // Se il parametro search non è presente nell'URL, restituisci un errore
    http_response_code(400);
    echo json_encode(["detail" => "Parametro 'idUser' mancante nell'URL"]);
}
?>

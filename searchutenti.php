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
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type"); // Aggiungi questa riga per consentire il campo "content-type"

// Verifica se il parametro search è presente nell'URL
if (!isset($_GET['search'])) {
    // Se non è presente, restituisci tutti gli utenti
    try {
        $query = "SELECT id, username, img FROM users";
        $stmt = $connection->query($query);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        shuffle($users); // Mescola gli utenti
        echo json_encode($users);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["detail" => "Errore nel recupero degli utenti: " . $e->getMessage()]);
    }
} else {
    // Se è presente, effettua la ricerca degli utenti con il nome specificato
    $search = $_GET['search'];
    try {
        $query = "SELECT id, username, img FROM users WHERE username LIKE ?";
        $stmt = $connection->prepare($query);
        $stmt->execute(["%$search%"]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        shuffle($users);
        echo json_encode($users, JSON_UNESCAPED_SLASHES);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["detail" => "Errore nella ricerca degli utenti: " . $e->getMessage()]);
    }
}
?>

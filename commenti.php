<?php

// Connessione al database MySQL
$host = "localhost";
$username = "root";
$password = "root";
$db = "instagram";
$port = 3306;

// Imposta l'header CORS per consentire le richieste da qualsiasi origine
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

// Verifica se il parametro idPost è presente nell'URL
if (!isset($_GET['idPost'])) {
    // Se non è presente, restituisci un errore
    http_response_code(400);
    echo json_encode(["detail" => "Parametro idPost mancante"]);
    exit();
}

// Verifica se il valore di idPost è un numero
if (!is_numeric($_GET['idPost'])) {
    // Se non è un numero, restituisci un errore
    http_response_code(400);
    echo json_encode(["detail" => "Parametro idPost non valido"]);
    exit();
}

// Ottieni l'ID del post dalla richiesta GET e convertilo in un intero
$post_id = intval($_GET['idPost']);

try {
    $connection = new PDO("mysql:host=$host;dbname=$db;port=$port", $username, $password);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepara le query SQL
    $query = "SELECT u.username, u.id, c.text_commento FROM commenti c INNER JOIN users u ON u.id = c.id_utente WHERE c.id_post = ?";

    $stmt = $connection->prepare($query);
    $stmt->execute([$post_id]);
    
    // Fetch comments
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Reverse the order of comments
    $comments = array_reverse($comments);
    
    // Send JSON response
    echo json_encode($comments, JSON_UNESCAPED_SLASHES);
    
} catch (PDOException $e) {
    // Gestisci gli errori di connessione al database
    http_response_code(500);
    echo json_encode(["detail" => "Errore nel recupero delle informazioni dal database: " . $e->getMessage()]);
    exit();
}
?>

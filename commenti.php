<?php

//Dati connessione al db
$host = "localhost";
$username = "root";
$password = "root";
$db = "instagram";
$port = 3306;

//Cors Policy
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

//Check se idPost esiste
if (!isset($_GET['idPost'])) {
    http_response_code(400);
    echo json_encode(["detail" => "Parametro idPost mancante"]);
    exit();
}

//Get idPost
$post_id = intval($_GET['idPost']);

// Include il file per la connessione PDO al database
require_once 'connessione_db.php';

try {
    //Query SQL
    $query = "SELECT u.username, u.id, c.text_commento FROM commenti c INNER JOIN users u ON u.id = c.id_utente WHERE c.id_post = ?";

    $stmt = $connection->prepare($query);
    $stmt->execute([$post_id]);
    
    //Estrai i commenti
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //Inverti l'ordine in modo che siano in ordine
    $comments = array_reverse($comments);
    
    echo json_encode($comments, JSON_UNESCAPED_SLASHES);
    
} catch (PDOException $e) {
    //Errori di connessione al db
    http_response_code(500);
    echo json_encode(["detail" => "Errore nel recupero delle informazioni dal database: " . $e->getMessage()]);
    exit();
}
?>

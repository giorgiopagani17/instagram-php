<?php
//Cors Policy
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

//Check InputDati 
if (!isset($_GET['idPost'])) {
    http_response_code(400);
    echo json_encode(["detail" => "Parametro idPost mancante"]);
    exit();
}

//Get InputDati
$post_id = intval($_GET['idPost']);

//Connesione al db
require_once 'connessione_db.php';

try {
    //Check se post esiste
    $check_query = "SELECT COUNT(*) AS post_count FROM post WHERE id_post = ?";
    $check_statement = $connection->prepare($check_query);
    $check_statement->execute([$post_id]);
    $post_count = $check_statement->fetchColumn();

    if ($post_count === 0) {
        http_response_code(404);
        echo json_encode(["detail" => "Il post specificato non esiste"]);
        exit();
    }

    //Get commenti
    $query = "SELECT u.username, u.id, c.text_commento FROM commenti c INNER JOIN users u ON u.id = c.id_utente WHERE c.id_post = ?";

    $stmt = $connection->prepare($query);
    $stmt->execute([$post_id]);

    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //Reverse così sono in ordine cronologico
    $comments = array_reverse($comments);

    echo json_encode($comments, JSON_UNESCAPED_SLASHES);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["detail" => "Errore nel recupero delle informazioni dal database: " . $e->getMessage()]);
    exit();
}
?>

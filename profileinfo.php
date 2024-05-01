<?php

//Connessione al db
require_once 'connessione_db.php';

//Cors Policy
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

//Check se utente esiste
function user_exists($user_id, $connection) {
    try {
        $query = "SELECT COUNT(*) AS count FROM users WHERE id = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["detail" => "Errore nel verificare l'esistenza dell'utente: " . $e->getMessage()]);
        exit();
    }
}

//Check InputDati
if (!isset($_GET['user_id'])) {
    http_response_code(400);
    echo json_encode(["detail" => "Parametro user_id mancante"]);
    exit();
}

//Get InputDati
$user_id = intval($_GET['user_id']);

//Check utente
if (!user_exists($user_id, $connection)) {
    http_response_code(404);
    echo json_encode(["detail" => "L'utente specificato non esiste"]);
    exit();
}

try {
    //Get info del profilo dell'utente
    $query_username = "SELECT username FROM users WHERE id = ?";
    $query_description = "SELECT descrizione FROM users WHERE id = ?";
    $query_follower = "SELECT COUNT(id_utente_follower) AS num_follower FROM follow WHERE id_utente_seguito = ?";
    $query_following = "SELECT COUNT(id_utente_seguito) AS num_following FROM follow WHERE id_utente_follower = ?";
    $query_posts = "SELECT COUNT(id_post) AS num_posts FROM post WHERE id_utente = ?";

    //Esecuzione query
    $stmt = $connection->prepare($query_username);
    $stmt->execute([$user_id]);
    $username_row = $stmt->fetch(PDO::FETCH_ASSOC);
    $username = $username_row['username'] ?? null;

    $stmt = $connection->prepare($query_description);
    $stmt->execute([$user_id]);
    $description_row = $stmt->fetch(PDO::FETCH_ASSOC);
    $description = $description_row['descrizione'] ?? null;

    $stmt = $connection->prepare($query_follower);
    $stmt->execute([$user_id]);
    $num_follower_row = $stmt->fetch(PDO::FETCH_ASSOC);
    $num_follower = $num_follower_row['num_follower'] ?? 0;

    $stmt = $connection->prepare($query_following);
    $stmt->execute([$user_id]);
    $num_following_row = $stmt->fetch(PDO::FETCH_ASSOC);
    $num_following = $num_following_row['num_following'] ?? 0;

    $stmt = $connection->prepare($query_posts);
    $stmt->execute([$user_id]);
    $num_posts_row = $stmt->fetch(PDO::FETCH_ASSOC);
    $num_posts = $num_posts_row['num_posts'] ?? 0;

    //Dati restituiti
    echo json_encode([
        "username" => $username,
        "description" => $description,
        "num_follower" => $num_follower,
        "num_following" => $num_following,
        "num_posts" => $num_posts
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["detail" => "Errore nel recupero delle informazioni dal database: " . $e->getMessage()]);
    exit();
}
?>

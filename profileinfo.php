<?php

// Connessione al database MySQL
$host = "localhost";
$username = "root";
$password = "root";
$db = "instagram";
$port = 3306;

// Imposta l'header CORS per consentire le richieste da qualsiasi origine
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type"); // Aggiungi questa riga per consentire il campo "content-type"

// Verifica se il parametro user_id è presente nell'URL
if (!isset($_GET['user_id'])) {
    // Se non è presente, restituisci un errore
    http_response_code(400);
    echo json_encode(["detail" => "Parametro user_id mancante"]);
    exit();
}

// Verifica se il valore di user_id è un numero
if (!is_numeric($_GET['user_id'])) {
    // Se non è un numero, restituisci un errore
    http_response_code(400);
    echo json_encode(["detail" => "Parametro user_id non valido"]);
    exit();
}

// Ottieni l'ID dell'utente dalla richiesta GET e convertilo in un intero
$user_id = intval($_GET['user_id']);

try {
    $connection = new PDO("mysql:host=$host;dbname=$db;port=$port", $username, $password);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepara le query SQL
    $query_username = "SELECT username FROM users WHERE id = ?";
    $query_description = "SELECT descrizione FROM users WHERE id = ?";
    $query_follower = "SELECT COUNT(id_utente_follower) AS num_follower FROM follow WHERE id_utente_seguito = ?";
    $query_following = "SELECT COUNT(id_utente_seguito) AS num_following FROM follow WHERE id_utente_follower = ?";
    $query_posts = "SELECT COUNT(id_post) AS num_posts FROM post WHERE id_utente = ?";

    // Esegui le query SQL
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

    // Restituisci le informazioni del profilo come JSON
    echo json_encode([
        "username" => $username,
        "description" => $description,
        "num_follower" => $num_follower,
        "num_following" => $num_following,
        "num_posts" => $num_posts
    ]);
} catch (PDOException $e) {
    // Gestisci gli errori di connessione al database
    http_response_code(500);
    echo json_encode(["detail" => "Errore nel recupero delle informazioni dal database: " . $e->getMessage()]);
    exit();
}
?>
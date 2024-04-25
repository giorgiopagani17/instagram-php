<?php

// Gestione delle richieste OPTIONS per consentire le richieste preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    http_response_code(200);
    exit();
}

// Imposta l'header CORS per consentire le richieste da qualsiasi origine
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

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
    exit();
}

function inserisciCommento($connection, $user_id, $post_id, $text) {
    try {
        $query = "INSERT INTO commenti (id_utente, id_post, text_commento) VALUES (?, ?, ?)";
        $statement = $connection->prepare($query);
        $statement->bindParam(1, $user_id, PDO::PARAM_INT);
        $statement->bindParam(2, $post_id, PDO::PARAM_INT);
        $statement->bindParam(3, $text, PDO::PARAM_STR);
        $statement->execute();

        if ($statement->rowCount() > 0) {
            http_response_code(200);
            echo json_encode(["Message" => "Commento inserito con successo"]);
        } else {
            http_response_code(500);
            echo json_encode(["Message" => "Errore durante l'inserimento del commento"]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["Message" => "Errore durante l'inserimento del commento: " . $e->getMessage()]);
    }
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $data['idUser'];
$post_id = $data['idPost'];
$text = $data['text'];

inserisciCommento($connection, $user_id, $post_id, $text);

?>

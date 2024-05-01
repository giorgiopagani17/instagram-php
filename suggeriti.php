<?php

//Connessione al db
require_once 'connessione_db.php';

//Cors Policy
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
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

//Check & Get InputDati
if (isset($_GET['idUser'])) {
    $user_id = $_GET['idUser'];
    if (!user_exists($user_id, $connection)) {
        http_response_code(404);
        echo json_encode(["detail" => "L'utente specificato non esiste"]);
        exit();
    }

    try {
        //Recupera max 5 utenti che lo user non segue
        $query = "SELECT u.id, u.username
        FROM users u 
        LEFT JOIN follow f ON u.id = f.id_utente_seguito AND f.id_utente_follower = ?
        WHERE f.id_utente_seguito IS NULL
        AND u.id <> ?
        ORDER BY RAND()
        LIMIT 5";
        $stmt = $connection->prepare($query);
        $stmt->execute([$user_id, $user_id]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($users);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["detail" => "Errore nel recupero degli utenti: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["detail" => "Parametro 'idUser' mancante nell'URL"]);
}

?>

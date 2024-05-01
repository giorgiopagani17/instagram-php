<?php

//Connessione al db
require_once 'connessione_db.php';

//Cors Policy
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type"); 

//Check parametro di ricerca
if (!isset($_GET['search'])) {
    //Se non c'è allora restituisci tutti gli utenti
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
    //Se c'è allora fai la Select con il LIKE
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

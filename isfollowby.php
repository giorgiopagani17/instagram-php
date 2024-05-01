<?php

//Cors Policy
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

//Connessione al db
require_once 'connessione_db.php';

//Check se users esistono
function user_exists($user_id, $connection) {
    try {
        $query = "SELECT COUNT(*) AS user_count FROM users WHERE id = :user_id";
        $statement = $connection->prepare($query);
        $statement->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        return ($result['user_count'] > 0);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["detail" => "Errore nel recupero delle informazioni dal database: " . $e->getMessage()]);
        exit();
    }
}

function get_follow_info($user_id, $id_followed, $connection) {
    try {
        //L'utente segue l'altro utente?
        $query = "SELECT id_follow FROM follow WHERE id_utente_follower = :user_id AND id_utente_seguito = :id_followed";
        $statement = $connection->prepare($query);
        $statement->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $statement->bindParam(":id_followed", $id_followed, PDO::PARAM_INT);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            echo json_encode(["follow" => true]);
        } else {
            echo json_encode(["follow" => false]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["detail" => "Errore nel recupero delle informazioni sul follow: " . $e->getMessage()]);
    }
}

//Get InputDati
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
$id_followed = isset($_GET['id_followed']) ? intval($_GET['id_followed']) : null;

//Check InputDati
if ($user_id !== null && $id_followed !== null) {
    //Check users
    if (user_exists($user_id, $connection) && user_exists($id_followed, $connection)) {
        get_follow_info($user_id, $id_followed, $connection);
    } else {
        http_response_code(404);
        echo json_encode(["detail" => "Uno o entrambi gli utenti non esistono"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["detail" => "Parametri non validi"]);
}

?>

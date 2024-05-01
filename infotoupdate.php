<?php

//Cors Policy
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type"); 

//Connessione al db
require_once 'connessione_db.php';

function getUserInfoToUpdate($user_id, $connection) {
    $user_id = intval($user_id);

    //Get biografia utente
    $query = "SELECT descrizione FROM users WHERE id = :user_id";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $userInfo;
}

//Get InputDati
$loggedInUser = isset($_GET['loggedInUserId']) ? intval($_GET['loggedInUserId']) : null;

//Check InputDati
if ($loggedInUser !== null) {
    $userInfo = getUserInfoToUpdate($loggedInUser, $connection);
    if ($userInfo !== false) {
        echo json_encode($userInfo);
    } else {
        echo json_encode(["detail" => "Utente non trovato"]);
    }
} else {
    echo json_encode(["detail" => "ID dell'utente non fornito"]);
}

$connection = null;

?>

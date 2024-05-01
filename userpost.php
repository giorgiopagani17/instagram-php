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
        $query = "SELECT COUNT(*) AS count FROM users WHERE id = :user_id";
        $statement = $connection->prepare($query);
        $statement->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["detail" => "Errore nel verificare l'esistenza dell'utente: " . $e->getMessage()]);
        exit(); 
    }
}

try {
    //Get InputDati
    $user_id = $_GET['user_id'] ?? null;

    //Check InputDati
    if ($user_id === null) {
        http_response_code(400);
        echo json_encode(["detail" => "Parametro 'user_id' mancante nella richiesta"]);
        exit();
    }

    //Check utente
    if (!user_exists($user_id, $connection)) {
        http_response_code(404);
        echo json_encode(["detail" => "L'utente specificato non esiste"]);
        exit();
    }

    //Get post utente
    $query = "SELECT img_post FROM post WHERE id_utente = :user_id";
    $statement = $connection->prepare($query);
    $statement->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $statement->execute();
    $images = $statement->fetchAll(PDO::FETCH_ASSOC);

    //Check se ci sono post
    if (!$images) {
        echo json_encode([]);
        exit();
    }

    //Array per contenere le img dei post
    $image_urls = [];

    //Costruzione degli url delle img
    foreach ($images as $image) {
        $image_name = urlencode(str_replace("C:/Users/giorg/Instagram/postUtenti/", "", $image["img_post"]));

        $image_url = "http://localhost/instagram/imgpost.php?post_name=" . $image_name;

        $image_urls[] = $image_url;
    }

    //Reverse così sono in ordine cronologico
    $image_urls = array_reverse($image_urls);

    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode($image_urls, JSON_UNESCAPED_SLASHES);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["detail" => "Error: " . $e->getMessage()]);
    exit(); 
}
?>

<?php

//Cors Policy
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

//Connessione al db
require_once 'connessione_db.php';

//Check se user esiste
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

try {
    //Check InputDati
    if (!isset($_GET['loggedInUserId'])) {
        http_response_code(400);
        echo json_encode(["detail" => "Parametro loggedInUserId mancante"]);
        exit();
    }

    //Get InputDati
    $user_id = $_GET['loggedInUserId'];

    //Check utente
    if (!user_exists($user_id, $connection)) {
        http_response_code(404);
        echo json_encode(["detail" => "L'utente specificato non esiste"]);
        exit();
    }

    //Get post degli users che l'utente non segue
    $sql = "SELECT img_post FROM post WHERE id_utente != :user_id";
    $stmt = $connection->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //Array per contenere le img dei post
    $image_urls = array();

    //Costruzione degli url delle img
    foreach ($images as $image) {
        $image_name = urlencode(str_replace("C:/Users/giorg/Instagram/postUtenti/", "", $image["img_post"]));

        $image_url = "http://localhost/instagram/imgpost.php?post_name=" . $image_name;

        $image_urls[] = $image_url;
    }

    //Shuffle cosi sono in ordine sparso
    shuffle($image_urls);

    echo json_encode($image_urls, JSON_UNESCAPED_SLASHES);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["detail" => "Errore di connessione al database: " . $e->getMessage()]);
}

?>

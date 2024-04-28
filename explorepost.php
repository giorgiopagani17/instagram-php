<?php

//Cors Policy
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type"); 

//Check se loggedInUserId esiste
if (!isset($_GET['loggedInUserId'])) {
    http_response_code(400);
    echo json_encode(["detail" => "Parametro loggedInUserId mancante"]);
    exit();
}

// Include il file per la connessione PDO al database
require_once 'connessione_db.php';

try {
    // Ottieni l'ID dell'utente dalla query string
    $user_id = $_GET['loggedInUserId'];

    // Esegui la query per ottenere le immagini degli utenti diversi da quello specificato
    $sql = "SELECT img_post FROM post WHERE id_utente != :user_id";
    $stmt = $connection->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Inizializza un array per contenere gli URL delle immagini
    $image_urls = array();

    // Costruisci gli URL delle immagini
    foreach ($images as $image) {
        // Rimuovi la parte fissa dal nome dell'immagine e codifica l'URL
        $image_name = urlencode(str_replace("C:/Users/giorg/Instagram/postUtenti/", "", $image["img_post"]));

        // Costruisci l'URL dell'immagine utilizzando solo il nome dell'immagine
        $image_url = "http://localhost/instagram/imgpost.php?post_name=" . $image_name;

        // Aggiungi l'URL dell'immagine all'array degli URL delle immagini
        $image_urls[] = $image_url;
    }

    // Inverti l'ordine degli URL delle immagini
    shuffle($image_urls);

    // Restituisci gli URL delle immagini come JSON
    echo json_encode($image_urls, JSON_UNESCAPED_SLASHES);
} catch (PDOException $e) {
    // Gestisci gli errori di connessione al database
    http_response_code(500);
    echo json_encode(["detail" => "Errore di connessione al database: " . $e->getMessage()]);
}

?>

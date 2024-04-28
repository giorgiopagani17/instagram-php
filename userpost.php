<?php
//Connessione al db
require_once 'connessione_db.php';

// Imposta l'header CORS per consentire le richieste da qualsiasi origine
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type"); // Aggiungi questa riga per consentire il campo "content-type"

// Connessione al database
try {
    // Ottieni il parametro 'user_id' dalla richiesta GET
    $user_id = $_GET['user_id'] ?? null;

    // Verifica se il parametro 'user_id' è stato fornito
    if ($user_id === null) {
        // Se il parametro non è stato fornito, restituisci un errore 400 Bad Request
        http_response_code(400);
        echo json_encode(["detail" => "Parametro 'user_id' mancante nella richiesta"]);
        exit();
    }

    // Query per ottenere le immagini dell'utente
    $query = "SELECT img_post FROM post WHERE id_utente = :user_id";
    $statement = $connection->prepare($query);
    $statement->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $statement->execute();
    $images = $statement->fetchAll(PDO::FETCH_ASSOC);

    // Verifica se sono state trovate immagini per l'utente
    if (!$images) {
        echo json_encode([]);
        exit();
    }

    // Array per memorizzare gli URL delle immagini
    $image_urls = [];

    // Costruisci gli URL delle immagini e aggiungili all'array
    foreach ($images as $image) {
        // Rimuovi la parte fissa dal nome dell'immagine e codifica l'URL
        $image_name = urlencode(str_replace("C:/Users/giorg/Instagram/postUtenti/", "", $image["img_post"]));

        // Costruisci l'URL dell'immagine utilizzando solo il nome dell'immagine
        $image_url = "http://localhost/instagram/imgpost.php?post_name=" . $image_name;

        // Aggiungi l'URL dell'immagine all'array degli URL delle immagini
        $image_urls[] = $image_url;
    }

    // Reverse cosi i post sono in ordine secondo la data
    $image_urls = array_reverse($image_urls);

    // Restituisci gli URL delle immagini come JSON
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode($image_urls, JSON_UNESCAPED_SLASHES);

} catch (PDOException $e) {
    // Se si verifica un errore durante la connessione, restituisci un'eccezione HTTP 500
    http_response_code(500);
    echo json_encode(["detail" => "Error: " . $e->getMessage()]);
    exit(); // Esci dallo script in caso di errore di connessione al database
}
?>

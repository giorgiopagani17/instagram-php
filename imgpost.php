<?php
//Connessione al db
require_once 'connessione_db.php';


// Connessione al database
try {
    // Endpoint per ottenere l'immagine del post
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['post_name'])) {
            $post_name = $_GET['post_name'];
            $query = "SELECT img_post FROM post WHERE img_post LIKE :post_name";
            $statement = $connection->prepare($query);
            $post_name = '%' . $post_name . '%'; // Aggiungi i caratteri jolly per cercare corrispondenze parziali
            $statement->bindParam(':post_name', $post_name, PDO::PARAM_STR);
            $statement->execute();
            $post_image_path = $statement->fetchColumn();

            if ($post_image_path) {
                // Leggi il contenuto dell'immagine e restituiscilo come output
                $image_content = file_get_contents($post_image_path);
                header('Content-Type: image/jpeg');
                echo $image_content;
                exit();
            } else {
                http_response_code(404);
                echo json_encode(["detail" => "Immagine non trovata per il post con nome $post_name"]);
                exit();
            }
        }
    }
} catch (PDOException $e) {
    // Se si verifica un errore durante la connessione, restituisci un'eccezione HTTP 500
    http_response_code(500);
    echo json_encode(["detail" => "Error: " . $e->getMessage()]);
    exit(); // Esci dallo script in caso di errore di connessione al database
}
?>

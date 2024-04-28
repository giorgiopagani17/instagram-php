<?php
//Connessione al db
require_once 'connessione_db.php';

// Connessione al database
try {
    // Endpoint per ottenere l'immagine dell'utente
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['user_id'])) {
            $user_id = $_GET['user_id'];
            $query = "SELECT img FROM users WHERE id = :user_id";
            $statement = $connection->prepare($query);
            $statement->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $statement->execute();
            $user_image_path = $statement->fetchColumn();

            if ($user_image_path) {
                // Leggi il contenuto dell'immagine e restituiscilo come output
                $image_content = file_get_contents($user_image_path);
                header('Content-Type: image/jpeg');
                echo $image_content;
                exit();
            } else {
                http_response_code(404);
                echo json_encode(["detail" => "Immagine non trovata per l'utente con ID $user_id"]);
                exit();
            }
        }
    }
} catch (PDOException $e) {
    // Se si verifica un errore durante la connessione, restituisci un'eccezione HTTP 500
    http_response_code(500);
    echo json_encode(["detail" => "Error" . $e->getMessage()]);
    exit(); // Esci dallo script in caso di errore di connessione al database
}
?>
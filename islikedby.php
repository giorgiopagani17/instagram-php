<?php
// Connessione al database
require_once 'connessione_db.php';

// Imposta l'header CORS per consentire le richieste da qualsiasi origine
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type"); // Aggiungi questa riga per consentire il campo "content-type"

// Funzione per ottenere le informazioni sul follow
function get_like_info($user_id, $id_post, $connection) {
    try {
        // Esegui la query per controllare se c'è un like
        $query = "SELECT id_like FROM like_instagram WHERE id_post = :id_post AND id_utente_like = :user_id";
        $statement = $connection->prepare($query);
        $statement->bindParam(":id_post", $id_post, PDO::PARAM_INT);
        $statement->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        // Verifica se c'è un risultato
        if ($result) {
            http_response_code(200);
            echo json_encode(["like" => true]);
        } else {
            http_response_code(200);
            echo json_encode(["like" => false]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["detail" => "Errore nel recupero delle informazioni sul follow: " . $e->getMessage()]);
    } finally {
        // Chiudi il cursore
        if ($statement !== null) {
            $statement->closeCursor();
        }
    }
}

// Ottieni i parametri dalla richiesta GET
$user_id = $_GET['loggedInUserId'];
$id_post = $_GET['idPost'];

// Chiamare la funzione get_like_info per ottenere le informazioni sul follow
get_like_info($user_id, $id_post, $connection);

?>

<?php

// Imposta l'header CORS per consentire le richieste da qualsiasi origine
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type"); // Aggiungi questa riga per consentire il campo "content-type"

// Includi il file per la connessione al database
require_once 'connessione_db.php';

// Funzione per ottenere le informazioni sul follow
function get_follow_info($user_id, $id_followed, $connection) {
    try {
        // Esegui la query per controllare se c'è un follow
        $query = "SELECT id_follow FROM follow WHERE id_utente_follower = :user_id AND id_utente_seguito = :id_followed";
        $statement = $connection->prepare($query);
        $statement->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $statement->bindParam(":id_followed", $id_followed, PDO::PARAM_INT);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        // Verifica se c'è un risultato
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

// Ottieni i parametri dalla richiesta GET
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
$id_followed = isset($_GET['id_followed']) ? intval($_GET['id_followed']) : null;

// Verifica se i parametri sono validi
if ($user_id !== null && $id_followed !== null) {
    // Chiamare la funzione get_follow_info per ottenere le informazioni sul follow
    get_follow_info($user_id, $id_followed, $connection);
} else {
    // Se i parametri non sono validi, restituisci un errore
    http_response_code(400);
    echo json_encode(["detail" => "Parametri non validi"]);
}

?>

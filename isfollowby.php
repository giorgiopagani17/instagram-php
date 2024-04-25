<?php

// Connessione al database MySQL
$host = "localhost";
$username = "root";
$password = "root";
$db = "instagram";
$port = 3306;

// Imposta l'header CORS per consentire le richieste da qualsiasi origine
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type"); // Aggiungi questa riga per consentire il campo "content-type"

// Funzione per ottenere le informazioni sul follow
function get_follow_info($user_id, $id_followed) {
    global $host, $username, $password, $db, $port;

    // Connessione al database
    $connection = new mysqli($host, $username, $password, $db, $port);

    // Verifica se la connessione è riuscita
    if ($connection->connect_error) {
        http_response_code(500);
        echo json_encode(["detail" => "Errore di connessione al database: " . $connection->connect_error]);
        exit();
    }

    try {
        // Esegui la query per controllare se c'è un follow
        $query = "SELECT id_follow FROM follow WHERE id_utente_follower = ? AND id_utente_seguito = ?";
        $statement = $connection->prepare($query);
        $statement->bind_param("ii", $user_id, $id_followed);
        $statement->execute();
        $result = $statement->get_result();

        // Verifica se c'è un risultato
        if ($result->num_rows > 0) {
            http_response_code(200);
            echo json_encode(["follow" => true]);
        } else {
            http_response_code(200);
            echo json_encode(["follow" => false]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["detail" => "Errore nel recupero delle informazioni sul follow: " . $e->getMessage()]);
    } finally {
        // Chiudi la connessione e il cursore
        $statement->close();
        $connection->close();
    }
}

// Ottieni i parametri dalla richiesta GET
$user_id = $_GET['user_id'];
$id_followed = $_GET['id_followed'];

// Chiamare la funzione get_follow_info per ottenere le informazioni sul follow
get_follow_info($user_id, $id_followed);

?>

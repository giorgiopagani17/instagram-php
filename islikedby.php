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
function get_like_info($user_id, $id_post) {
    global $host, $username, $password, $db, $port;

    // Connessione al database
    $connection = new mysqli($host, $username, $password, $db, $port);

    // Verifica se la connessione è riuscita
    if ($connection->connect_error) {
        http_response_code(500);
        echo json_encode(["detail" => "Errore di connessione al database: " . $connection->connect_error]);
        exit();
    }

    $statement = null;

    try {
        // Esegui la query per controllare se c'è un follow
        $query = "SELECT id_like
        FROM like_instagram
        WHERE id_post = ? AND id_utente_like = ?";
        $statement = $connection->prepare($query);
        $statement->bind_param("ii", $id_post, $user_id);
        $statement->execute();
        $result = $statement->get_result();

        // Verifica se c'è un risultato
        if ($result->num_rows > 0) {
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
        // Chiudi la connessione e il cursore
        if ($statement !== null) {
            $statement->close();
        }
        $connection->close();
    }
}

// Ottieni i parametri dalla richiesta GET
$user_id = $_GET['loggedInUserId'];
$idPost = $_GET['idPost'];

// Chiamare la funzione get_follow_info per ottenere le informazioni sul follow
get_like_info($user_id, $idPost);

?>

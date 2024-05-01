<?php
//Connessione al db
require_once 'connessione_db.php';

//Cors Policy
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

//Gestione richiesta options (possibile errore)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    http_response_code(200);
    exit();
}

function inserisciCommento($connection, $user_id, $post_id, $text) {
    try {
        //Check se utente esiste
        $query_check_user = "SELECT id FROM users WHERE id = ?";
        $statement_check_user = $connection->prepare($query_check_user);
        $statement_check_user->bindParam(1, $user_id, PDO::PARAM_INT);
        $statement_check_user->execute();
        $result_check_user = $statement_check_user->fetch(PDO::FETCH_ASSOC);

        if (!$result_check_user) {
            http_response_code(400);
            echo json_encode(["detail" => "L'utente non esiste"]);
            exit();
        }

        //Check se post esiste
        $query_check_post = "SELECT id_post FROM post WHERE id_post = ?";
        $statement_check_post = $connection->prepare($query_check_post);
        $statement_check_post->bindParam(1, $post_id, PDO::PARAM_INT);
        $statement_check_post->execute();
        $result_check_post = $statement_check_post->fetch(PDO::FETCH_ASSOC);

        if (!$result_check_post) {
            http_response_code(400);
            echo json_encode(["detail" => "Il post non esiste"]);
            exit();
        }

        //Inserimento commento nel db
        $query = "INSERT INTO commenti (id_utente, id_post, text_commento) VALUES (?, ?, ?)";
        $statement = $connection->prepare($query);
        $statement->bindParam(1, $user_id, PDO::PARAM_INT);
        $statement->bindParam(2, $post_id, PDO::PARAM_INT);
        $statement->bindParam(3, $text, PDO::PARAM_STR);
        $statement->execute();

        if ($statement->rowCount() > 0) {
            http_response_code(200);
            echo json_encode(["Message" => "Commento inserito con successo"]);
        } else {
            http_response_code(500);
            echo json_encode(["Message" => "Errore durante l'inserimento del commento"]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["Message" => "Errore durante l'inserimento del commento: " . $e->getMessage()]);
    }
}

//Check & Get InputDati
$data = json_decode(file_get_contents('php://input'), true);
$user_id = $data['idUser'];
$post_id = $data['idPost'];
$text = $data['text'];

inserisciCommento($connection, $user_id, $post_id, $text);

?>

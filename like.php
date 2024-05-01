<?php

//Cors Policy
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type"); 

function like() {
    //Get InputDati
    $data = json_decode(file_get_contents('php://input'), true);

    //Check se InputDati presenti
    if (!isset($_GET['loggedInUserId'])) {
        http_response_code(400);
        echo json_encode(["detail" => "Parametro loggedInUserId mancante"]);
        exit();
    }

    //Check se InputDati presenti
    if (!isset($_GET['idPost'])) {
        http_response_code(400);
        echo json_encode(["detail" => "Parametro idPost mancante"]);
        exit();
    }
    
    $loggedInUser = intval($_GET['loggedInUserId']);
    $id_post = intval($_GET['idPost']);

    //Connessione al db
    require_once 'connessione_db.php';

    try {
        //Check se post esiste
        $query_check_post = "SELECT id_post FROM post WHERE id_post = ?";
        $statement_check_post = $connection->prepare($query_check_post);
        $statement_check_post->bindParam(1, $id_post, PDO::PARAM_INT);
        $statement_check_post->execute();
        $result_check_post = $statement_check_post->fetch(PDO::FETCH_ASSOC);

        if (!$result_check_post) {
            http_response_code(400);
            echo json_encode(["detail" => "Il post non esiste"]);
            exit();
        }

        //Check se utente
        $query_check_user = "SELECT id FROM users WHERE id = ?";
        $statement_check_user = $connection->prepare($query_check_user);
        $statement_check_user->bindParam(1, $loggedInUser, PDO::PARAM_INT);
        $statement_check_user->execute();
        $result_check_user = $statement_check_user->fetch(PDO::FETCH_ASSOC);

        if (!$result_check_user) {
            http_response_code(400);
            echo json_encode(["detail" => "L'utente non esiste"]);
            exit();
        }

        //Check se post ha già il like
        $query_check_like = "SELECT id_like FROM like_instagram WHERE id_post = ? AND id_utente_like = ?";
        $statement_check_like = $connection->prepare($query_check_like);
        $statement_check_like->bindParam(1, $id_post, PDO::PARAM_INT);
        $statement_check_like->bindParam(2, $loggedInUser, PDO::PARAM_INT);
        $statement_check_like->execute();
        $result_check_like = $statement_check_like->fetch(PDO::FETCH_ASSOC);

        if ($result_check_like) {
            http_response_code(400);
            echo json_encode(["detail" => "L'utente ha già messo like a questo post"], JSON_UNESCAPED_UNICODE);
            exit();
        }

        //Inserimento like nel db
        $query_insert_like = "INSERT INTO like_instagram (id_post, id_utente_like) VALUES (?, ?)";
        $statement_insert_like = $connection->prepare($query_insert_like);
        $statement_insert_like->bindParam(1, $id_post, PDO::PARAM_INT);
        $statement_insert_like->bindParam(2, $loggedInUser, PDO::PARAM_INT);
        $statement_insert_like->execute();

        //Check inserimento corretto
        if ($statement_insert_like->rowCount() > 0) {
            http_response_code(200);
            echo json_encode(["Message" => "Like inserito con successo!"]);
        } else {
            http_response_code(500);
            echo json_encode(["Message" => "Errore durante l'inserimento nella tabella like"]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["Message" => "Errore: " . $e->getMessage()]);
    } finally {
        if ($statement_check_like) {
            $statement_check_like->closeCursor();
        }
        $connection = null;
    }
}

like();

?>

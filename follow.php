<?php

//Cors Policy
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type"); 

function follow() {
    //Connessione al db
    require_once 'connessione_db.php'; 

    //Get InputDati
    $data = json_decode(file_get_contents('php://input'), true);

    //Check se InputDati presenti
    if (!isset($_GET['loggedInUserId'])) {
        http_response_code(400);
        echo json_encode(["detail" => "Parametro loggedInUserId mancante"]);
        exit();
    }

    //Check se InputDati presenti
    if (!isset($_GET['idUserFollowed'])) {
        http_response_code(400);
        echo json_encode(["detail" => "Parametro idUserFollowed mancante"]);
        exit();
    }
    
    $loggedInUser = intval($_GET['loggedInUserId']);
    $idUserFollowed = intval($_GET['idUserFollowed']);

    try {
        //Check se follower esiste
        $query_check_follower = "SELECT id FROM users WHERE id = ?";
        $statement_check_follower = $connection->prepare($query_check_follower);
        $statement_check_follower->bindParam(1, $loggedInUser, PDO::PARAM_INT);
        $statement_check_follower->execute();
        $result_check_follower = $statement_check_follower->fetch(PDO::FETCH_ASSOC);

        if (!$result_check_follower) {
            http_response_code(400);
            echo json_encode(["detail" => "L'utente follower non esiste"]);
            exit();
        }

        //Check se seguito esiste
        $query_check_followed = "SELECT id FROM users WHERE id = ?";
        $statement_check_followed = $connection->prepare($query_check_followed);
        $statement_check_followed->bindParam(1, $idUserFollowed, PDO::PARAM_INT);
        $statement_check_followed->execute();
        $result_check_followed = $statement_check_followed->fetch(PDO::FETCH_ASSOC);

        if (!$result_check_followed) {
            http_response_code(400);
            echo json_encode(["detail" => "L'utente seguito non esiste"]);
            exit();
        }

        //Check se l'utente segue già l'altro utente
        $check_query = "SELECT * FROM follow WHERE id_utente_follower = ? AND id_utente_seguito = ?";
        $check_statement = $connection->prepare($check_query);
        $check_statement->bindParam(1, $loggedInUser, PDO::PARAM_INT);
        $check_statement->bindParam(2, $idUserFollowed, PDO::PARAM_INT);
        $check_statement->execute();
        $result = $check_statement->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            http_response_code(400);
            echo json_encode(["detail" => "L'utente sta già seguendo l'altro utente"], JSON_UNESCAPED_UNICODE);
            exit();
        }

        //Inserimento follow
        $query = "INSERT INTO follow (id_utente_follower, id_utente_seguito) VALUES (?, ?)";
        $statement = $connection->prepare($query);
        $statement->bindParam(1, $loggedInUser, PDO::PARAM_INT);
        $statement->bindParam(2, $idUserFollowed, PDO::PARAM_INT);
        $statement->execute();

        //Check inserimento nel db
        if ($statement->rowCount() > 0) {
            http_response_code(200);
            echo json_encode(["Message" => "Utente seguito con successo!"]);
        } else {
            http_response_code(500);
            echo json_encode(["Message" => "Errore durante l'inserimento nella tabella follow"]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["Message" => "Errore durante l'inserimento nella tabella follow: " . $e->getMessage()]);
    } finally {
        $check_statement->closeCursor();
        $statement->closeCursor();
    }
}

follow();

?>

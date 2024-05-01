<?php

//Cors Policy
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type"); 

//Gestione richieste Option
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

function unfollow() {
    //Connessione al db
    require_once 'connessione_db.php'; 

    //Check InputDati
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($_GET['loggedInUserId'])) {
        http_response_code(400);
        echo json_encode(["detail" => "Parametro loggedInUserId mancante"]);
        exit();
    }
    
    if (!isset($_GET['idUserFollowed'])) {
        http_response_code(400);
        echo json_encode(["detail" => "Parametro idUserFollowed mancante"]);
        exit();
    }

    //Get InputDati
    $loggedInUser = intval($_GET['loggedInUserId']);
    $idUserFollowed = intval($_GET['idUserFollowed']);

    try {
        //Check se l'utente segue l'altro utente
        $check_query = "SELECT * FROM follow WHERE id_utente_follower = ? AND id_utente_seguito = ?";
        $check_statement = $connection->prepare($check_query);
        $check_statement->bindParam(1, $loggedInUser, PDO::PARAM_INT);
        $check_statement->bindParam(2, $idUserFollowed, PDO::PARAM_INT);
        $check_statement->execute();
        $result = $check_statement->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            http_response_code(400);
            echo json_encode(["detail" => "L'utente non sta seguendo l'altro utente"]);
            exit();
        }

        //Delete follow
        $query = "DELETE FROM follow WHERE id_utente_follower = ? AND id_utente_seguito = ?";
        $statement = $connection->prepare($query);
        $statement->bindParam(1, $loggedInUser, PDO::PARAM_INT);
        $statement->bindParam(2, $idUserFollowed, PDO::PARAM_INT);
        $statement->execute();
    
        if ($statement->rowCount() > 0) {
            http_response_code(200);
            echo json_encode(["Message" => "Utente unfollowato con successo!"]);
        } else {
            http_response_code(400);
            echo json_encode(["Message" => "Nessun record da eliminare nella tabella follow"]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["Message" => "Errore durante l'eliminazione nella tabella follow: " . $e->getMessage()]);    
    } finally {
        $check_statement->closeCursor();
        $statement->closeCursor();
    }
}

unfollow();

?>

<?php

//Cors Policy
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type");

//Gestione richieste Option
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    //Richiesta Options quindi esci
    http_response_code(200);
    exit();
}

function unlike() {
    //Check InputDati
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($_GET['loggedInUserId'])) {
        http_response_code(400);
        echo json_encode(["detail" => "Parametro loggedInUserId mancante"]);
        exit();
    }
    
    if (!isset($_GET['idPost'])) {
        // Se il parametro idPost non è presente, restituisci un errore
        http_response_code(400);
        echo json_encode(["detail" => "Parametro idPost mancante"]);
        exit();
    }

    //Get InputDati
    $loggedInUser = intval($_GET['loggedInUserId']);
    $id_post = intval($_GET['idPost']);

    //Connessione al db
    require_once 'connessione_db.php';

    try {
        //Delete like
        $query = "DELETE FROM like_instagram WHERE id_post = ? AND id_utente_like = ?";
        $statement = $connection->prepare($query);
        $statement->bindParam(1, $id_post, PDO::PARAM_INT);
        $statement->bindParam(2, $loggedInUser, PDO::PARAM_INT);
        $statement->execute();
    
        if ($statement->rowCount() > 0) {
            http_response_code(200);
            echo json_encode(["Message" => "Like tolto con successo!"]);
        } else {
            //Se non c'è un like
            http_response_code(400);
            echo json_encode(["Message" => "Non è stato trovato alcun like da eliminare per questo post"]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["Message" => "Errore durante l'eliminazione nella tabella like: " . $e->getMessage()]);    
    } finally {
        if ($statement) {
            $statement->closeCursor();
        }
        $connection = null;
    }
}

// Chiamare la funzione unlike per gestire la richiesta DELETE
unlike();


?>

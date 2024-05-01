<?php
//Connessione al db
require_once 'connessione_db.php';

//Cors Policy
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

//Check se user esiste
function user_exists($user_id, $connection) {
    try {
        $query = "SELECT COUNT(*) AS user_count FROM users WHERE id = :user_id";
        $statement = $connection->prepare($query);
        $statement->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        return ($result['user_count'] > 0);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["detail" => "Errore nel recupero delle informazioni dal database: " . $e->getMessage()]);
        exit();
    }
}

//Check & Get InputData
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    //Check user
    if (!user_exists($user_id, $connection)) {
        http_response_code(404);
        echo json_encode(["detail" => "L'utente specificato non esiste"]);
        exit();
    }

    $search = isset($_GET['search']) ? $_GET['search'] : null;

    function search_follower($id, $search, $connection) {
        $result = array();
        //Se il parametro di ricerca è nullo allora ritorna tutti gli utenti
        if ($search !== null) {
            $query = "
                SELECT u_follower.id,  u_follower.username, u_follower.img
                FROM users u_follower
                INNER JOIN follow f ON u_follower.id = f.id_utente_follower
                WHERE f.id_utente_seguito = ? AND u_follower.username LIKE CONCAT('%', ?, '%')
                LIMIT 0, 25";
            if ($stmt = $connection->prepare($query)) {
                $stmt->bindParam(1, $id, PDO::PARAM_INT);
                $stmt->bindParam(2, $search, PDO::PARAM_STR);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } else {
            //Altrimenti Select con il LIKE
            $query = "
                SELECT u.id, u.username, u.img 
                FROM users u 
                INNER JOIN follow f ON u.id = f.id_utente_follower
                WHERE f.id_utente_seguito = ?";
            if ($stmt = $connection->prepare($query)) {
                $stmt->bindParam(1, $id, PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }
        return $result;
    }

    $follower = search_follower($user_id, $search, $connection);

    echo json_encode($follower, JSON_UNESCAPED_SLASHES);
} else {
    http_response_code(400);
    echo json_encode(["detail" => "Parametro 'user_id' mancante nell'URL"]);
}

?>

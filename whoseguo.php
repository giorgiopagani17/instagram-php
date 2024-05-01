<?php
//Connessione al db
require_once 'connessione_db.php';

//Cors Policy
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

//Check se user esiste
if (isset($_GET['user_id'])) {
    $id = $_GET['user_id'];
    $search = isset($_GET['search']) ? $_GET['search'] : null;

    $query_check_user = "SELECT id FROM users WHERE id = ?";
    $stmt_check_user = $connection->prepare($query_check_user);
    $stmt_check_user->execute([$id]);
    $user_exists = $stmt_check_user->fetch();

    if (!$user_exists) {
        echo json_encode(array('error' => 'L\'utente specificato non esiste'), JSON_UNESCAPED_SLASHES);
        exit();
    }

    function search_seguiti($id, $search, $connection) {
        $result = array();
        if ($search !== null) {
        //Se il parametro di ricerca è nullo allora ritorna tutti gli utenti
        $query = "
                SELECT u_followed.id, u_followed.username, u_followed.img
                FROM users u_followed
                INNER JOIN follow f ON u_followed.id = f.id_utente_seguito
                WHERE f.id_utente_follower = ? AND u_followed.username LIKE CONCAT('%', ?, '%')
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
                INNER JOIN follow f ON u.id = f.id_utente_seguito
                WHERE f.id_utente_follower = ?";
            if ($stmt = $connection->prepare($query)) {
                $stmt->bindParam(1, $id, PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }
        return $result;
    }

    $seguiti = search_seguiti($id, $search, $connection);

    echo json_encode($seguiti, JSON_UNESCAPED_SLASHES);
}

?>

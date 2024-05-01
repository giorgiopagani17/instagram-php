<?php
//Connessione al db
require_once 'connessione_db.php';

//Cors Policy
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

//Check se post esiste
if (isset($_GET['id_post'])) {
    $id = $_GET['id_post'];
    $search = isset($_GET['search']) ? $_GET['search'] : null;

    $query_check_post = "SELECT id_post FROM post WHERE id_post = ?";
    $stmt_check_post = $connection->prepare($query_check_post);
    $stmt_check_post->execute([$id]);
    $post_exists = $stmt_check_post->fetch();

    if (!$post_exists) {
        echo json_encode(array('error' => 'Il post specificato non esiste'), JSON_UNESCAPED_SLASHES);
        exit(); 
    }

    function search_like($id, $search, $connection) {
        $result = array();
        //Se il parametro di ricerca è nullo allora ritorna tutti gli utenti
        if ($search !== null) {
            $query = "
                SELECT u.id, u.username, u.img
                FROM users u
                INNER JOIN like_instagram l ON u.id = l.id_utente_like
                INNER JOIN post p ON l.id_post = p.id_post
                WHERE p.id_post = ? AND u.username LIKE CONCAT('%', ?, '%')
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
                INNER JOIN like_instagram l ON u.id = l.id_utente_like
                INNER JOIN post p ON l.id_post = p.id_post
                WHERE p.id_post = ?
                LIMIT 0, 25";
            if ($stmt = $connection->prepare($query)) {
                $stmt->bindParam(1, $id, PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }
        return $result;
    }

    $like = search_like($id, $search, $connection);

    echo json_encode($like, JSON_UNESCAPED_SLASHES);
}

?>

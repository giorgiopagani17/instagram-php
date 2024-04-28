<?php
// Connessione al database
require_once 'connessione_db.php';

if (isset($_GET['id_post'])) {
    $id = $_GET['id_post'];
    $search = isset($_GET['search']) ? $_GET['search'] : null;

    // Funzione per cercare i like
    function search_like($id, $search, $connection) {
        $result = array();
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

    // Chiamata alla funzione search_like
    $like = search_like($id, $search, $connection);

    // Imposta header CORS per consentire l'accesso da qualsiasi origine
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET");
    header("Access-Control-Allow-Headers: Content-Type");

    // Ritorna i risultati come JSON
    echo json_encode($like, JSON_UNESCAPED_SLASHES);
}

// Chiudi la connessione al database (se necessario)
// $connection = null;
?>

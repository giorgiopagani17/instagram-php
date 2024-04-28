<?php
// Connessione al database
require_once 'connessione_db.php';

if (isset($_GET['user_id'])) {
    $id = $_GET['user_id'];
    $search = isset($_GET['search']) ? $_GET['search'] : null;

    // Funzione per cercare i seguiti
    function search_seguiti($id, $search, $connection) {
        $result = array();
        if ($search !== null) {
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

    // Chiamata alla funzione search_seguiti
    $seguiti = search_seguiti($id, $search, $connection);

    // Imposta header CORS per consentire l'accesso da qualsiasi origine
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET");
    header("Access-Control-Allow-Headers: Content-Type");

    // Ritorna i risultati come JSON
    echo json_encode($seguiti, JSON_UNESCAPED_SLASHES);
}

// Chiudi la connessione al database (se necessario)
// $connection = null;
?>

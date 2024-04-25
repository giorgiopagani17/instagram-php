<?php
// Credenziali di accesso al database
$host = "localhost";
$username = "root";
$password = "root";
$db = "instagram";
$port = 3306;

// Connessione al database
$connection = new mysqli($host, $username, $password, $db, $port);

if ($connection->connect_error) {
    die("Connessione al database fallita: " . $connection->connect_error);
}

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
                $stmt->bind_param("is", $id, $search);
                $stmt->execute();
                $stmt->bind_result($userId, $username, $img);
                while ($stmt->fetch()) {
                    $result[] = array(
                        "id" => $userId,
                        "username" => $username,
                        "img" => $img
                    );
                }
                $stmt->close();
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
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->bind_result($userId, $username, $img);
                while ($stmt->fetch()) {
                    $result[] = array(
                        "id" => $userId,
                        "username" => $username,
                        "img" => $img
                    );
                }
                $stmt->close();
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

// Chiudi la connessione al database
$connection->close();
?>

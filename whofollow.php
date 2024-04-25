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

if (isset($_GET['user_id'])) {
    $id = $_GET['user_id'];
    $search = isset($_GET['search']) ? $_GET['search'] : null;

    // Funzione per cercare i follower
    function search_follower($id, $search, $connection) {
        $result = array();
        if ($search !== null) {
            $query = "
                SELECT u_follower.id,  u_follower.username, u_follower.img
                FROM users u_follower
                INNER JOIN follow f ON u_follower.id = f.id_utente_follower
                WHERE f.id_utente_seguito = ? AND u_follower.username LIKE CONCAT('%', ?, '%')
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
                INNER JOIN follow f ON u.id = f.id_utente_follower
                WHERE f.id_utente_seguito = ?";
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

    // Chiamata alla funzione search_follower
    $follower = search_follower($id, $search, $connection);

    // Imposta header CORS per consentire l'accesso da qualsiasi origine
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET");
    header("Access-Control-Allow-Headers: Content-Type");

    // Ritorna i risultati come JSON
    echo json_encode($follower, JSON_UNESCAPED_SLASHES);
}

// Chiudi la connessione al database
$connection->close();
?>

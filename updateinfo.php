<?php
//Cors Policy
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT");+
header("Access-Control-Allow-Headers: Content-Type"); 

//Gestione richieste Options
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

//Connessione al db
require_once 'connessione_db.php';

function updateUserInformation($user_id, $username, $new_password, $description, $connection) {
    try {
        //Array per memorizzare le  query da eseguire
        $queries = [];

        //Get info attuali utente
        $stmt_get_user_info = $connection->prepare("SELECT username, password, descrizione FROM users WHERE id = ?");
        $stmt_get_user_info->execute([$user_id]);
        $current_user_info = $stmt_get_user_info->fetch(PDO::FETCH_ASSOC);
        
        $encrypted_new_password = encryptPassword($new_password);

        //Check se descrizione è diversa
        if ($current_user_info['descrizione'] !== $description) {
            //Add query per aggiornare la descrizione
            $queries[] = ["UPDATE users SET descrizione = ? WHERE id = ?", [$description, $user_id]];
        }

        //Check se password è diversa
        if (!empty($new_password) && !password_verify($new_password, $current_user_info['password'])) {
            //Add query per aggiornare la password
            $queries[] = ["UPDATE users SET password = ? WHERE id = ?", [$encrypted_new_password, $user_id]];
        }

        //Check se lo username è diverso
        if ($username !== $current_user_info['username']) {
            //Check se lo username esiste già
            $stmt_check_username = $connection->prepare("SELECT COUNT(*) as count FROM users WHERE username = ?");
            $stmt_check_username->execute([$username]);
            $username_exists = $stmt_check_username->fetch(PDO::FETCH_ASSOC);

            if ($username_exists['count'] > 0) {
                return ["error" => "Lo username $username esiste già nel database"];
            } else {
                // Aggiungi query per aggiornare lo username
                $queries[] = ["UPDATE users SET username = ? WHERE id = ?", [$username, $user_id]];
            }
        }

        //Esegui le query
        foreach ($queries as $query) {
            $stmt = $connection->prepare($query[0]);
            $stmt->execute($query[1]);
        }

        if(count($queries) > 0){
            return (["message" => "Informazioni aggiornate con successo"]);
        } else {
            return (["message" => "Nessuna modifica avvenuta perchè i dati sono uguali"]);
        }
    } catch (PDOException $e) {
        throw new Exception("Errore durante l'aggiornamento delle informazioni dell'utente: " . $e->getMessage());
    }
}

//Crypt nuova password
function encryptPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

$request_body = file_get_contents('php://input');
$data = json_decode($request_body, true);

//Check & Get InputDati
if (isset($data["username"]) && isset($data["password"]) && isset($data["description"])) {
    $username = $data["username"];
    $new_password = $data["password"];
    $description = $data["description"];

    $user_id = isset($_GET['loggedInUserId']) ? intval($_GET['loggedInUserId']) : null;

    if ($user_id !== null) {
        try {
            //Check se user esiste
            $query_get_user = "SELECT * FROM users WHERE id = ?";
            $stmt_get_user = $connection->prepare($query_get_user);
            $stmt_get_user->execute([$user_id]);
            $user_exists = $stmt_get_user->fetch(PDO::FETCH_ASSOC);
    
            if (!$user_exists) {
                echo json_encode(["message" => "Utente non trovato con ID $user_id"]);
                exit(); // Esci dallo script se l'utente non esiste
            }

            //Se esiste svolgi l'update
            $update_performed = updateUserInformation($user_id, $username, $new_password, $description, $connection);

            echo json_encode([$update_performed], JSON_UNESCAPED_SLASHES);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["detail" => $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["detail" => "ID dell'utente non fornito"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["detail" => "Dati richiesti non forniti"]);
}
?>

<?php
//Connessione al db
require_once 'connessione_db.php';

//Cors Policy
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

//Path cartella in cui salvare il post
$save_folder = "C:/Users/giorg/Instagram/postUtenti/";

function upload_image($user_id, $file, $description, $connection) {
    global $save_folder;

    try {
        //Se non esiste la cartella la crea
        if (!file_exists($save_folder)) {
            mkdir($save_folder, 0777, true);
        }

        //Salva l'immagine nella cartella
        $file_path = $save_folder . basename($file["name"]);
        move_uploaded_file($file["tmp_name"], $file_path);

        //Inserimento post
        $img_path = $save_folder . $file["name"];
        $current_date = date("Y-m-d");
        $query = "INSERT INTO post (id_utente, img_post, descrizione, date) VALUES (?, ?, ?, ?)";
        $statement = $connection->prepare($query);
        $statement->bindParam(1, $user_id, PDO::PARAM_INT);
        $statement->bindParam(2, $img_path, PDO::PARAM_STR);
        $statement->bindParam(3, $description, PDO::PARAM_STR);
        $statement->bindParam(4, $current_date, PDO::PARAM_STR);
        $statement->execute();
        
        return ["filename" => $img_path, "description" => $description, "user_id" => $user_id];
    } catch (Exception $e) {
        error_log("Errore durante l'upload e l'inserimento nel database: " . $e->getMessage());
        return ["error" => "Error"];
    }
}

//Check & Get InputDati
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["file"])) {
    $user_id = $_POST["userId"];
    $file = $_FILES["file"];
    $description = $_POST["description"];
    
    //Check utente
    if ($user_id !== null) {
        try {
            //Check se user esiste
            $query_get_user = "SELECT * FROM users WHERE id = ?";
            $stmt_get_user = $connection->prepare($query_get_user);
            $stmt_get_user->execute([$user_id]);
            $user_exists = $stmt_get_user->fetch(PDO::FETCH_ASSOC);

            if (!$user_exists) {
                http_response_code(404); // Utente non trovato
                echo json_encode(["error" => "Utente non trovato con ID $user_id"]);
                exit(); // Esci dallo script
            }

            //Se esiste svolgi l'upload
            $result = upload_image($user_id, $file, $description, $connection);

            if (isset($result["error"])) {
                $response = ["error" => $result["error"]];
            } else {
                $response = ["message" => "Post inserito correttamente"];
            }

            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }
}

$connection = null;

?>

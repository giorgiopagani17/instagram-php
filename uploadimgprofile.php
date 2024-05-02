<?php
//Cors Policy
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

//Connessione al db
require_once 'connessione_db.php';

function uploadProfileImage($user_id, $file) {
    global $connection;

    try {
        //Cartella in cui salvare le img
        $save_folder = "C:/Users/giorg/Instagram/imgUtenti/";
        //Se la cartella non esiste la crea
        if (!file_exists($save_folder)) {
            mkdir($save_folder, 0777, true);
        }

        //Get path img vecchia
        $query = "SELECT img FROM users WHERE id = :user_id";
        $stmt = $connection->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $old_img_path = $stmt->fetchColumn();

        // //Elimina img vecchia dalla cartella
        // if ($old_img_path && $old_img_path != 'C:/Users/giorg/Instagram/imgUtenti/default.jpg') {
        //     if (file_exists($old_img_path)) {
        //         unlink($old_img_path);
        //     }
        // }

        //Get nome file
        $temp_file = $file['tmp_name'];

        //Costruisci la path
        $file_path = $save_folder . "user_" . $user_id . ".jpg";

        // //Sposta il file nella path inserita
        // if (!move_uploaded_file($temp_file, $file_path)) {
        //     throw new Exception("Errore durante il salvataggio del file.");
        // }

        //Update img dello user
        $query = "UPDATE users SET img = :file_path WHERE id = :user_id";
        $stmt = $connection->prepare($query);
        $stmt->bindParam(':file_path', $file_path, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        return ["filename" => $file_path, "user_id" => $user_id];
    } catch (Exception $e) {
        error_log("Errore durante l'upload e l'inserimento nel database: " . $e->getMessage());
        return ["Message" => "Error"];
    } finally {
        if (isset($stmt)) {
            $stmt->closeCursor();
        }
    }
}

//Get InputDati
$user_id = intval($_POST['user_id']);

$file = $_FILES['file'];

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
        $result = uploadProfileImage($user_id, $file);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["detail" => $e->getMessage()]);
    }
}

header('Content-Type: application/json');
echo json_encode($result);
?>

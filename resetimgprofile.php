<?php
//Cors Policy
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type");

//Connessione al db
require_once 'connessione_db.php';

function uploadProfileImage($user_id, $imageName) {
    global $connection;

    try {
        //Check se user esiste
        $query_check_user = "SELECT id FROM users WHERE id = ?";
        $stmt_check_user = $connection->prepare($query_check_user);
        $stmt_check_user->bindValue(1, $user_id, PDO::PARAM_INT);
        $stmt_check_user->execute();
        $user_exists = $stmt_check_user->fetch();

        if (!$user_exists) {
            return ["Message" => "Utente non trovato con ID $user_id"];
        }

        //Check se cartella esiste
        $save_folder = "C:/Users/giorg/Instagram/imgUtenti/";
        if (!file_exists($save_folder)) {
            mkdir($save_folder, 0777, true);
        }

        //Get path img vecchia
        $query = "SELECT img FROM users WHERE id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $old_img_path = $stmt->fetchColumn();

        //Elimina l'img vecchia dal pc
        if ($old_img_path && $old_img_path != 'C:/Users/giorg/Instagram/imgUtenti/default.jpg') {
            if (file_exists($old_img_path)) {
                unlink($old_img_path);
            }
        }

        //Update path nuova img
        $file_path = $save_folder . $imageName;
        $update_query = "UPDATE users SET img = ? WHERE id = ?";
        $update_stmt = $connection->prepare($update_query);
        $update_stmt->bindValue(1, $file_path, PDO::PARAM_STR);
        $update_stmt->bindValue(2, $user_id, PDO::PARAM_INT);
        $update_stmt->execute();

        return ["filename" => $file_path, "user_id" => $user_id];
    } catch (PDOException $e) {
        error_log("Errore durante l'upload e l'inserimento nel database: " . $e->getMessage());
        return ["Message" => "Error"];
    } finally {
        if (isset($stmt)) {
            $stmt->closeCursor();
        }
    }
}

//Check InputDati
$request_body = file_get_contents('php://input');
$data = json_decode($request_body, true);

//Get InputDati
$user_id = intval($data['user_id']);
$imageName = $data['imageName'];

$result = uploadProfileImage($user_id, $imageName);

header('Content-Type: application/json');
echo json_encode($result);
?>

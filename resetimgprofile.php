<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type");

// Include il file per la connessione PDO al database
require_once 'connessione_db.php';

// Funzione per caricare l'immagine del profilo
function uploadProfileImage($user_id, $imageName) {
    global $connection;

    try {
        // Assicurati di avere una cartella dove salvare le immagini
        $save_folder = "C:/Users/giorg/Instagram/imgUtenti/";
        if (!file_exists($save_folder)) {
            mkdir($save_folder, 0777, true);
        }

        // Selezione l'immagine vecchia nel database
        $query = "SELECT img FROM users WHERE id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $old_img_path = $stmt->fetchColumn();

        // Elimina l'immagine precedente se esiste
        if ($old_img_path && $old_img_path != 'C:/Users/giorg/Instagram/imgUtenti/default.jpg') {
            if (file_exists($old_img_path)) {
                unlink($old_img_path);
            }
        }

        // Aggiorna il percorso dell'immagine nel database
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

// Ottieni i dati dalla richiesta JSON
$request_body = file_get_contents('php://input');
$data = json_decode($request_body, true);

// Ottieni l'ID dell'utente e il nome dell'immagine
$user_id = intval($data['user_id']);
$imageName = $data['imageName'];

// Chiamata alla funzione per caricare l'immagine del profilo
$result = uploadProfileImage($user_id, $imageName);

// Restituisci la risposta JSON
header('Content-Type: application/json');
echo json_encode('Reset avvenuto con successo');
?>

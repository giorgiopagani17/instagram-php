<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Include il file per la connessione PDO al database
require_once 'connessione_db.php';

// Funzione per caricare l'immagine del profilo
function uploadProfileImage($user_id, $file) {
    global $connection;

    try {
        // Assicurati di avere una cartella dove salvare le immagini
        $save_folder = "C:/Users/giorg/Instagram/imgUtenti/";
        if (!file_exists($save_folder)) {
            mkdir($save_folder, 0777, true);
        }

        // Ottieni il percorso dell'immagine vecchia dal database
        $query = "SELECT img FROM users WHERE id = :user_id";
        $stmt = $connection->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $old_img_path = $stmt->fetchColumn();

        // Elimina l'immagine precedente se esiste
        if ($old_img_path && $old_img_path != 'C:/Users/giorg/Instagram/imgUtenti/default.jpg') {
            if (file_exists($old_img_path)) {
                unlink($old_img_path);
            }
        }

        // Ottieni il percorso del file temporaneo
        $temp_file = $file['tmp_name'];

        // Costruisci il percorso del file finale
        $file_path = $save_folder . "user_" . $user_id . ".jpg";

        // Sposta il file temporaneo nella posizione finale
        if (!move_uploaded_file($temp_file, $file_path)) {
            throw new Exception("Errore durante il salvataggio del file.");
        }

        // Aggiorna il percorso dell'immagine nel database
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

// Ottieni l'ID dell'utente e il file dall'array $_FILES
$user_id = intval($_POST['user_id']);
$file = $_FILES['file'];

// Chiamata alla funzione per caricare l'immagine del profilo
$result = uploadProfileImage($user_id, $file);

// Restituisci la risposta JSON
header('Content-Type: application/json');
echo json_encode($result);
?>

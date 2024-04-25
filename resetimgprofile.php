<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type");

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
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $old_img_path = $row['img'];

        // Elimina l'immagine precedente se esiste
        if ($old_img_path && $old_img_path != 'C:/Users/giorg/Instagram/imgUtenti/default.jpg') {
            if (file_exists($old_img_path)) {
                unlink($old_img_path);
            }
        }

        // Aggiorna il percorso dell'immagine nel database
        $file_path = $save_folder . $imageName;
        $query = "UPDATE users SET img = ? WHERE id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param("si", $file_path, $user_id);
        $stmt->execute();

        return ["filename" => $file_path, "user_id" => $user_id];
    } catch (Exception $e) {
        error_log("Errore durante l'upload e l'inserimento nel database: " . $e->getMessage());
        return ["Message" => "Error"];
    } finally {
        if (isset($stmt)) {
            $stmt->close();
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

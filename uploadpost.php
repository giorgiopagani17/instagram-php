<?php
// Connessione al database
require_once 'connessione_db.php';

// Cors Policy
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Path della cartella in cui salvare il post
$save_folder = "C:/Users/giorg/Instagram/postUtenti/";

// Funzione per caricare l'immagine e inserire il post nel database
function upload_image($user_id, $file, $description, $connection) {
    global $save_folder;

    try {
        // Controlla se la cartella di salvataggio esiste, altrimenti creala
        if (!file_exists($save_folder)) {
            mkdir($save_folder, 0777, true);
        }

        // Salva l'immagine nella cartella
        $file_path = $save_folder . basename($file["name"]);
        move_uploaded_file($file["tmp_name"], $file_path);

        // Inserisce il post nel database
        $img_path = $save_folder . $file["name"];
        $current_date = date("Y-m-d");
        $query = "INSERT INTO post (id_utente, img_post, descrizione, date) VALUES (?, ?, ?, ?)";
        $statement = $connection->prepare($query);
        $statement->bindParam(1, $user_id, PDO::PARAM_INT);
        $statement->bindParam(2, $img_path, PDO::PARAM_STR);
        $statement->bindParam(3, $description, PDO::PARAM_STR);
        $statement->bindParam(4, $current_date, PDO::PARAM_STR);
        $statement->execute();
        $statement->close();

        // Restituisce i dettagli del post
        return ["filename" => $img_path, "description" => $description, "user_id" => $user_id];
    } catch (Exception $e) {
        // In caso di errore, restituisce un messaggio di errore
        return ["error" => "Errore durante l'upload e l'inserimento nel database: " . $e->getMessage()];
    }
}

// Funzione per verificare se l'utente esiste nel database
function check_user_existence($user_id, $connection) {
    $query = "SELECT id FROM users WHERE id = ?";
    $statement = $connection->prepare($query);
    $statement->bindParam(1, $user_id, PDO::PARAM_INT);
    $statement->execute();
    $num_rows = $statement->rowCount();
    return $num_rows > 0;
}

// Verifica se la richiesta è di tipo POST e se sono stati ricevuti i dati corretti
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["file"]) && isset($_POST["userId"]) && isset($_POST["description"])) {
    $user_id = $_POST["userId"];
    $file = $_FILES["file"];
    $description = $_POST["description"];
    
    // Controlla se l'utente esiste nel database
    if (!check_user_existence($user_id, $connection)) {
        // Se l'utente non esiste, restituisce un messaggio di errore
        $response = ["error" => "L'utente non esiste"];
    } else {
        // Altrimenti, procede con il caricamento dell'immagine e l'inserimento nel database
        $result = upload_image($user_id, $file, $description, $connection);

        if (isset($result["error"])) {
            // Se si verifica un errore durante il caricamento o l'inserimento, restituisce un messaggio di errore
            $response = ["error" => $result["error"]];
        } else {
            // Altrimenti, restituisce un messaggio di successo
            $response = ["message" => "Post inserito correttamente"];
        }
    }
} else {
    // Se i dati non sono corretti o mancanti, restituisce un messaggio di errore
    $response = ["error" => "Dati mancanti o non validi nella richiesta"];
}

// Invia la risposta JSON al client
echo json_encode($response);

// Chiude la connessione al database
$connection = null;
?>

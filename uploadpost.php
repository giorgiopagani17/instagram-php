<?php
// Credenziali di accesso al database
$host = "localhost";
$username = "root";
$password = "root";
$db = "instagram";
$port = 3306;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Connessione al database
$connection = new mysqli($host, $username, $password, $db, $port);

// Verifica della connessione
if ($connection->connect_error) {
    die("Connessione al database fallita: " . $connection->connect_error);
}

// Percorso della cartella dove salvare le immagini
$save_folder = "C:/Users/giorg/Instagram/postUtenti/";

// Funzione per gestire l'upload dell'immagine
function upload_image($user_id, $file, $description) {
    global $save_folder, $connection;

    try {
        // Assicurati di avere una cartella dove salvare le immagini
        if (!file_exists($save_folder)) {
            mkdir($save_folder, 0777, true);
        }

        //Commentato perchè almeno funziona al prof
        // $file_path = $save_folder . basename($file["name"]);
        //salvo l'immagine nella cartella
        // move_uploaded_file($file["tmp_name"], $file_path);

        // Inserisci i dati nel database
        $img_path = $save_folder . $file["name"];
        $solo_data_attuale = date("Y-m-d");
        $query = "INSERT INTO post (id_utente, img_post, descrizione, date) VALUES (?, ?, ?, ?)";
        $statement = $connection->prepare($query);
        $statement->bind_param("isss", $user_id, $img_path, $description, $solo_data_attuale);
        $statement->execute();
        $statement->close();

        return ["filename" => $img_path, "description" => $description, "user_id" => $user_id];
    } catch (Exception $e) {
        echo "Errore durante l'upload e l'inserimento nel database: " . $e->getMessage();
        return ["Message" => "Error"];
    }
}

// Utilizzo della funzione per gestire la richiesta di upload dell'immagine
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["file"])) {
    $user_id = $_POST["userId"];
    $file = $_FILES["file"];
    $description = $_POST["description"];
    
    $result = upload_image($user_id, $file, $description);
    
    // Ritorna la risposta come JSON
    echo json_encode($result);
}

// Chiudi la connessione al database
$connection->close();
?>

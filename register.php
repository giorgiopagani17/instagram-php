<?php
//Connessione al db
require_once 'connessione_db.php';

//Cors Policy
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

function registerUser($username, $email, $password) {
    global $connection;

    //Check se username esiste già
    $stmt_check_username = $connection->prepare("SELECT * FROM users WHERE username = ?");
    $stmt_check_username->execute([$username]);
    $existing_username = $stmt_check_username->fetch();

    if ($existing_username) {
        http_response_code(400);
        echo json_encode(["detail" => "Lo username è già in uso."], JSON_UNESCAPED_UNICODE);
        exit();
    }

    //Check se email esiste già
    $stmt_check_email = $connection->prepare("SELECT * FROM users WHERE email = ?");
    $stmt_check_email->execute([$email]);
    $existing_email = $stmt_check_email->fetch();

    if ($existing_email) {
        http_response_code(400);
        echo json_encode(["detail" => "L'email è già in uso."], JSON_UNESCAPED_UNICODE);
        exit();
    }

    //Crypt passsowrd
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    //Inserimento utente nel db
    $imgUtente = 'C:/Users/giorg/Instagram/imgUtenti/default.jpg'; //Img predefinita per tutti gli utenti
    $description = ""; //Descrizione vuota default

    $stmt_insert_user = $connection->prepare("INSERT INTO users (username, email, password, img, descrizione) VALUES (?, ?, ?, ?, ?)");
    $stmt_insert_user->execute([$username, $email, $hashed_password, $imgUtente, $description]);

    echo json_encode(["Message" => "Registrazione effettuata con successo!"]);
}

//Check & Get InputDati
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'];
    $email = $data['email'];
    $password = $data['password'];

    try {
        registerUser($username, $email, $password);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["detail" => "Errore durante la registrazione: " . $e->getMessage()]);
    }
}

?>

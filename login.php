<?php
//Connessione al db
require_once 'connessione_db.php';

//Cors Policy
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

//Check & Get InputDati
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data['email'];
        $password = $data['password'];
        
        //Get info utente
        $stmt = $connection->prepare("SELECT id, username, password, img FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user_row = $stmt->fetch(PDO::FETCH_ASSOC);

        //Check se user esiste e se la password è corretta
        if ($user_row && password_verify($password, $user_row['password'])) {
            //Restituisci le info necessarie per la session utente
            unset($user_row['password']); //rimuovi la password dalla risposta
            echo json_encode(["success" => true, "user" => $user_row], JSON_UNESCAPED_SLASHES);
        } else {
            //Errore credenziali
            http_response_code(401);
            echo json_encode(["detail" => "Credenziali non valide"]);
        }
    } catch (PDOException $e) {
        //Errore di connessione al db
        http_response_code(500);
        echo json_encode(["detail" => "Errore durante il login: Impossibile accedere al database"]);
    }
}

?>

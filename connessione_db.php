<?php
//Dati connessione al db
$host = "localhost";
$username = "root";
$password = "root";
$db = "instagram";
$port = 3306;

try {
    //Connessione al db
    $connection = new PDO("mysql:host=$host;dbname=$db;port=$port", $username, $password);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Gestione degli errori di connessione PDO
    die("Errore di connessione al database: " . $e->getMessage());
}
?>

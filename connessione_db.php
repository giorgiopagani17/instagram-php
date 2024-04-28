<?php
//Dati connessione al db
$host = "localhost";
$username = "root";
$password = "root";
$db = "instagram";
$port = 3306;

//Connessione al db
$connection = new PDO("mysql:host=$host;dbname=$db;port=$port", $username, $password);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//Check connessione
if ($connection->connect_error) {
    die("Errore di connessione al database: " . $connection->connect_error);
}
?>

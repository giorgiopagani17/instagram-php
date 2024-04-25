<?php

// Connessione al database MySQL
$host = "localhost";
$username = "root";
$password = "root";
$db = "instagram";
$port = 3306;

// Imposta l'header CORS per consentire le richieste da qualsiasi origine
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type"); // Aggiungi questa riga per consentire il campo "content-type"

// Ottieni il nome dell'immagine dalla query string
if(isset($_GET['imageName'])) {
    // Ottieni il nome dell'immagine dal parametro $_GET['post_name']
    $imageName = $_GET['imageName'];

    // Rimuovi il prefisso "imgpost.php?post_name=" dalla variabile $imageName
    $prefixToRemove = 'imgpost.php?post_name=';
    if(strpos($imageName, $prefixToRemove) === 0) {
        $imageName = substr($imageName, strlen($prefixToRemove));
    }

    // Ora $imageName contiene solo il nome dell'immagine
}

// Connessione al database
$connection = mysqli_connect($host, $username, $password, $db, $port);

// Controlla se la connessione è riuscita
if (!$connection) {
    die("Connessione al database fallita: " . mysqli_connect_error());
}

// Query per ottenere le informazioni del post
$query = "
    SELECT post.id_post, post.id_utente, post.descrizione, post.date, users.username, users.img
    FROM post
    INNER JOIN users ON post.id_utente = users.id
    WHERE post.img_post LIKE ?
";

// Aggiungi i caratteri jolly direttamente alla variabile $imageName
$imageName = '%' . $imageName . '%';

// Esegui la query per ottenere le informazioni del post
$stmt_post = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt_post, "s", $imageName);
mysqli_stmt_execute($stmt_post);
$result_post = mysqli_stmt_get_result($stmt_post);

// Seleziona il risultato della query principale
if ($row_post = mysqli_fetch_assoc($result_post)) {
    $post_id = $row_post['id_post'];
    $user_id = $row_post['id_utente'];
    $description = isset($row_post['descrizione']) ? utf8_encode($row_post['descrizione']) : ""; // Utilizza utf8_encode per eventuali caratteri non visualizzati
    $date = $row_post['date'];
    $username = $row_post['username'];
    $imgProfile = "http://localhost/instagram/imgprofile.php?user_id={$user_id}";

    // Esegui un'altra query per ottenere il numero di like del post
    $like_query = "
        SELECT COUNT(*) AS num_likes
        FROM like_instagram
        WHERE id_post = ?
    ";

    $stmt_like = mysqli_prepare($connection, $like_query);
    mysqli_stmt_bind_param($stmt_like, "i", $post_id);
    mysqli_stmt_execute($stmt_like);
    $result_like = mysqli_stmt_get_result($stmt_like);

    if ($row_like = mysqli_fetch_assoc($result_like)) {
        $num_likes = $row_like['num_likes'];
    } else {
        $num_likes = 0;
    }

    // Restituisci le informazioni del post
    $response = [
        'post_id' => $post_id,
        'user_id' => $user_id,
        'username' => $username,
        'descrizionepost' => $description,
        'datepost' => $date,
        'num_likes' => $num_likes,
        'imgProfile' => $imgProfile
    ];
    
    echo json_encode($response, JSON_UNESCAPED_SLASHES);
} else {
    // Se il post non è stato trovato
    echo json_encode(["message" => "Post non trovato"]);
}
    

// Chiudi la connessione al database
mysqli_close($connection);

?>

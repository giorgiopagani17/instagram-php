<?php

//Cors Policy
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type"); 

//Check InputDati
if(isset($_GET['imageName'])) {
    //Get InputDati
    $imageName = $_GET['imageName'];

    //Rimuovi il prefisso "imgpost.php?post_name=" 
    $prefixToRemove = 'imgpost.php?post_name=';
    if(strpos($imageName, $prefixToRemove) === 0) {
        $imageName = substr($imageName, strlen($prefixToRemove));
    }}

//Connessione al db
require_once 'connessione_db.php';

try {
    //Get info post
    $query = "
        SELECT post.id_post, post.id_utente, post.descrizione, post.date, users.username, users.img
        FROM post
        INNER JOIN users ON post.id_utente = users.id
        WHERE post.img_post LIKE ?
    ";

    $stmt_post = $connection->prepare($query);
    $stmt_post->execute(['%' . $imageName . '%']);
    $result_post = $stmt_post->fetch(PDO::FETCH_ASSOC);

    if ($result_post) {
        $post_id = $result_post['id_post'];
        $user_id = $result_post['id_utente'];
        $description = isset($result_post['descrizione']) ? utf8_encode($result_post['descrizione']) : ""; // Utilizza utf8_encode per eventuali caratteri non visualizzati
        $date = $result_post['date'];
        $username = $result_post['username'];
        $imgProfile = "http://localhost/instagram/imgprofile.php?user_id={$user_id}";

        //Get like post
        $like_query = "
            SELECT COUNT(*) AS num_likes
            FROM like_instagram
            WHERE id_post = ?
        ";

        $stmt_like = $connection->prepare($like_query);
        $stmt_like->execute([$post_id]);
        $result_like = $stmt_like->fetch(PDO::FETCH_ASSOC);

        if ($result_like) {
            $num_likes = $result_like['num_likes'];
        } else {
            $num_likes = 0;
        }

        //Restituisci info post
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
        echo json_encode(["message" => "Post non trovato"]);
    }
} catch (PDOException $e) {
    echo json_encode(["message" => "Errore durante l'esecuzione delle query: " . $e->getMessage()]);
}

$connection = null;

?>

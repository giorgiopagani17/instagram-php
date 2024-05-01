<?php
//Connessione al db
require_once 'connessione_db.php';

//Cors Policy
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

use DateTime;
use stdClass;

//Classe Post
class Post {
    public $id;
    public $img_post;
    public $descrizione;
    public $datepost;
    public $num_like;
    public $username;
    public $user_img;
    public $user_id;
}

//Check se utente esiste
function user_exists($user_id, $connection) {
    try {
        $query = "SELECT COUNT(*) AS count FROM users WHERE id = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["detail" => "Errore nel verificare l'esistenza dell'utente: " . $e->getMessage()]);
        exit();
    }
}

function get_user_posts($user_id) {
    global $connection;

    //Check utente
    if (!user_exists($user_id, $connection)) {
        http_response_code(404);
        echo json_encode(["detail" => "L'utente non esiste"]);
        exit();
    }

    $result_posts = [];
    try {
        //Get post degli utenti che lo user segue
        $query = "
            SELECT p.id_post, p.img_post, p.descrizione, p.date, COUNT(l.id_like) AS num_like, u.username, p.id_utente
            FROM post p
            JOIN follow f ON p.id_utente = f.id_utente_seguito
            LEFT JOIN like_instagram l ON p.id_post = l.id_post
            JOIN users u ON p.id_utente = u.id
            WHERE f.id_utente_follower = ?
            GROUP BY p.id_post
        ";
        $stmt = $connection->prepare($query);
        $stmt->execute([$user_id]);
        $result = $stmt->fetchAll();

        //Array di oggetti Post
        foreach ($result as $row) {
            $post = new stdClass();
            $post->id = $row["id_post"];
            $post->img_post = str_replace("C:/Users/giorg/Instagram/postUtenti/", "http://localhost/instagram/imgpost.php?post_name=", $row["img_post"]);
            $post->descrizione = $row["descrizione"];
            $post->datepost = (new DateTime($row["date"]))->format("Y-m-d");
            $post->num_like = $row["num_like"];
            $post->username = $row["username"];
            $post->user_img = "http://localhost/instagram/imgprofile.php?user_id=" . $row["id_utente"];
            $post->user_id = $row["id_utente"];
            $result_posts[] = $post;
        }

        //Reverse ordine così sono in ordine cronologico
        $result_posts = array_reverse($result_posts);

        return $result_posts;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["detail" => "Errore nel recupero dei post: " . $e->getMessage()]);
        exit();
    }
}

//Check InputDati
if (!isset($_GET['user_id'])) {
    http_response_code(400);
    echo json_encode(["detail" => "Parametro user_id mancante"]);
    exit();
}

//Get InputDati
$user_id = intval($_GET['user_id']);

$result_posts = get_user_posts($user_id);

echo json_encode($result_posts);

?>

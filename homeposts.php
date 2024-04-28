<?php
//Connessione al db
require_once 'connessione_db.php';

// Imposta l'header CORS per consentire le richieste da qualsiasi origine
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET"); // Aggiungi qui tutti i metodi HTTP supportati (GET, POST, etc.)
header("Access-Control-Allow-Headers: Content-Type"); // Aggiungi questa riga per consentire il campo "content-type"

use DateTime;
use stdClass;

// Definizione della classe Post
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

// Funzione per ottenere i post di un utente
function get_user_posts($user_id) {
    global $connection;

    $result_posts = [];
    try {
        // Esegui la query SQL per ottenere le informazioni sui post dell'utente specificato
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

        // Creare un array di oggetti Post anziché un array di stringhe
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

        // Inverti l'ordine dei post
        $result_posts = array_reverse($result_posts);

        return $result_posts;
    } catch (PDOException $e) {
        // Se si verifica un errore durante l'esecuzione della query, restituisci un'eccezione HTTP 500
        http_response_code(500);
        echo json_encode(["detail" => "Errore nel recupero dei post: " . $e->getMessage()]);
        exit();
    }
}

// Verifica se il parametro user_id è presente nell'URL
if (!isset($_GET['user_id'])) {
    // Se non è presente, restituisci un errore
    http_response_code(400);
    echo json_encode(["detail" => "Parametro user_id mancante"]);
    exit();
}

// Verifica se il valore di user_id è un numero
if (!is_numeric($_GET['user_id'])) {
    // Se non è un numero, restituisci un errore
    http_response_code(400);
    echo json_encode(["detail" => "Parametro user_id non valido"]);
    exit();
}

// Ottieni l'ID dell'utente dalla richiesta GET e convertilo in un intero
$user_id = intval($_GET['user_id']);

// Chiamata alla funzione get_user_posts per ottenere i post dell'utente
$result_posts = get_user_posts($user_id);

// Restituisci i post come JSON
echo json_encode($result_posts);

?>

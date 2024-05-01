<?php
//Connessione al db
require_once 'connessione_db.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        //Check & Get InputDati
        if (isset($_GET['post_name'])) {
            $post_name = $_GET['post_name'];
            $query = "SELECT img_post FROM post WHERE img_post LIKE :post_name";
            $statement = $connection->prepare($query);
            $post_name = '%' . $post_name . '%'; //Concat %
            $statement->bindParam(':post_name', $post_name, PDO::PARAM_STR);
            $statement->execute();
            $post_image_path = $statement->fetchColumn();

            if ($post_image_path) {
                //Restituisci in output l'img jpg
                $image_content = file_get_contents($post_image_path);
                header('Content-Type: image/jpeg');
                echo $image_content;
                exit();
            } else {
                http_response_code(404);
                echo json_encode(["detail" => "Immagine non trovata per il post con nome $post_name"]);
                exit();
            }
        }
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["detail" => "Error: " . $e->getMessage()]);
    exit(); 
}
?>

<?php
require_once 'config.php'; // Database setting constants [DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD]
class DbHelper {
    private $db;

    function __construct() {
        $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8';

        try {
            $this->db = new PDO($dsn, DB_USERNAME, DB_PASSWORD, 
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        } catch (PDOException $e) {
            $response["status"] = "error";
            $response["message"] = 'Connection failed: ' . $e->getMessage();
            $response["data"] = null;
            exit;
        }
    }

    /**
    * Retrieves story ID's from "digitalt fortalt" with digitaltmuseum.no's API
    */
    function addStoriesToDatabase(){
        $startDoc = 0;
        $numberOfDocs = -1;

        while($startDoc < $numberOfDocs || $numberOfDocs == -1){
            $obj = json_decode(file_get_contents(API_URL.'select?q=artifact.event.place:(s%C3%B8r-tr%C3%B8ndelag%20OR%20nord-tr%C3%B8ndelag)&fq=(identifier.owner:H-DF)&start='.$startDoc.'&wt=json&api.key='.API_KEY));
            $numberOfDocs = $obj->response->numFound;
            foreach($obj->response->docs as $doc) {
                $doc = get_object_vars($doc);
                $startDoc += 1;
                $stmt = $this->db->prepare(
                    'INSERT INTO story (storyId) VALUES (:storyId) 
                    ON DUPLICATE KEY UPDATE storyId = :storyId');
                $stmt->execute(array(':storyId' => (string)$doc['identifier.id']));
            }
        }
    }


    // function addCategoryMapping(){

    // }
    // function addCategories(){
    //     $categories = array("Art and design", "Architecture", "History",
    //         "Local traditions and food", "Nature and adventure",
    //         "Religion/Spiritual experience", "Science and technology");
    //     $subcategories = array();

    // }

    function insert($table, $columnsArray, $valuesArray) {
        $cols = implode(", ", $columnsArray);
        $vals = implode(", ", $valuesArray);

        $stmt =  $this->db->prepare(
            "INSERT INTO $table ($cols) VALUES ($vals)");
        $stmt->execute();
    }
}

?>

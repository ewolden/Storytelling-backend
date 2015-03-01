<?php
require_once 'config.php'; // Database setting constants [DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD]
header('Content-type: text/plain; charset=utf-8');//Just to make it look nice in the browser
class DbHelper {

    private $db;	
	private $tableColumns = array(
			/*false = not auto incremented primary key*/
			'story' => array(false,'storyId','title','author','thumbnailURL','institution','introduction'),
			'user' => array(false,'userId','mail','age_group','gender','use_of_location'),
			'subcategory' => array(false,'subcategoryId','subcategoryName'),
			'story_subcategory' => array(false,'storyId', 'subcategoryId'),
			'dftag' => array(false,'DFTagName'),
			'story_dftags' => array(false,'storyId', 'DFTagName'),
			'story_media' => array(false, 'storyId', 'mediaId'),
			'category_mapping' => array(false, 'categoryId', 'subcategoryId'),
			);
	private $categoryMapping = array(
			/*The numbers 1-9 is the primary keys in the category table*/
			'art and design' => array(1,'bildekunst', 'design og formgjeving', 'film', 'fotografi', 'media', 'teater', 'dans'),
			'architecture' => array(2,'arkitektur'),
			'archeology' => array(3,'arkeologi og forminne'),
			'history' => array(4,'historie', 'historie og geografi', 'språkhistorie', 'sjøfart og kystkultur','kulturminne'),
			'local traditions and food' => array(5,'bunader og folkedrakter', 'hordaland', 'kulturminne', 'kultur og samfunn', 'rallarvegen', 'tradisjonsmat og drikke', 'dans', 'språk', 'fiske og fiskeindustri', 'samer', 'musikk', 'sjøfart og kystkultur', 'fleirkultur og minoritetar'),
			'nature and adventure' => array(6,'fiske og fiskeindustri', 'naturhistorie', 'sport og friluftsliv', 'sjøfart og kystkultur', 'natur, teknikk og næring', 'fiske og fiskeindustri'),
			'literature' => array(7,'teikneseriar', 'litteratur'),
			'music' => array(8,'musikk'),
			'science and technology' => array(9,'kjøretøy, bil og motor, veitransport', 'skip- og båtbygging', 'teknikk, industri og bergverk', 'natur, teknikk og næring', 'media', 'fotografi', 'fiske og fiskeindustri'),
			);
	
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

	function getDB(){
		return $this->db;
	}
	
	function getTableColumn($tableName){
		return $this->tableColumns[$tableName];
	}
	
	function getCategories($subcategory){
		$categories = array();
		foreach ($this->categoryMapping as $subCategoryArray){
			$key = in_array($subcategory, $subCategoryArray);
			if($key){
				array_push($categories, $subCategoryArray[0]);
			}
		}
		return $categories;
	}
	
	/* Inserts all values in $valuesArray in table $tableName. 
		If the primary key already exists, it updates all other values.
		Only works if the primary key consists of just one attribute and is the first column in the table 
		(perhaps - seems to work on story_subcategory which has a two-attribute primary key) */
    function insert($tableName,$valuesArray) {
        $columnsArray = $this->getTableColumn($tableName);
		$cols = implode(",", $columnsArray);
		$cols = trim($cols,","); //Remove the comma before first attribute
		
		$update = array();
		$values = array();
		
		/*Checking if the primary key is auto incremented or not*/
		if($columnsArray[0] == false){
			$insert = '?,';
			/*The first $valuesArray is for the placeholders inside VALUES(), the sliced $valuesArray for the updating placeholders*/
			$values = array_merge($valuesArray, array_slice($valuesArray,1));
		}
		else {
			/* If the primary key is auto incremented, we need to remove the boolean true and the primary key from the array */
			$cols = implode(",", array_slice($columnsArray,2));
			$insert = '';
			/* If the primary key is auto incremented, the parameter $valuesArray doesn't include a key, so we don't need to slice*/
			$values = array_merge($valuesArray, $valuesArray);
		}
		
		for ($x = 2; $x < sizeof($columnsArray); $x++){
			/*Creating plateholders for each value for updating, except for primary key*/
			$update[] = ''.$columnsArray[$x].'= ?';
			/*Creating placeholders for each value*/
			$insert .= '?,';
		}
		$insert = trim($insert,","); //Remove the extra comma at the end
		$updateString = implode(",", $update);
				
		$query = 'INSERT INTO '.$tableName.' ('.$cols.') VALUES ('.$insert.') ON DUPLICATE KEY UPDATE ';
		if(!empty($updateString)){
			$query .= ''.$updateString.'';
		}
		else {
			/*Just a meaningless operation to avoid primary key error*/
			$duplicatePrimary = ''.$columnsArray[1].'='.$columnsArray[1].'';
			$query .= ''.$duplicatePrimary.'';
		}
		
        $stmt = $this->db->prepare($query);

        $stmt->execute($values);
    }
}

?>

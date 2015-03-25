<?php
require_once 'config.php'; // Database setting constants [DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD]
require_once '../models/storyModel.php';
header('Content-type: text/plain; charset=utf-8');//Just to make it look nice in the browser
class DbHelper {

    private $db;	
	private $tableColumns = array(
			/*false = not auto incremented primary key
			* The first number is the number of primary keys in the table
			* It is assumed that the primary key columns are placed first in the table
			*/
			'story' => array(1,false,'storyId','title','author','date','institution','introduction', 'lastChangedTime'),
			'user' => array(1,true,'userId','mail','age_group','gender','use_of_location'),
			'subcategory' => array(1,false,'subcategoryId','subcategoryName'),
			'story_subcategory' => array(2,false,'storyId', 'subcategoryId'),
			'story_dftags' => array(2,false,'storyId', 'DFTagName'),
			'story_media' => array(2,false, 'storyId', 'mediaId'),
			'category_mapping' => array(2,false, 'categoryId', 'subcategoryId'),
			'category_preference' => array(2,false,'userId','categoryId'),
			'media_preference' => array(2,false,'userId','mediaId','ranking'),
			'user_tag' => array(2, false, 'userId', 'tagName'),
			'user_storytag' => array(3,false,'userId', 'storyId', 'tagName'),
			'stored_story' => array(2,false, 'userId', 'storyId', 'explanation', 'rating', 'false_recommend', 'type_of_recommendation'),
			'story_state' => array(1,true, 'recordedStateId', 'storyId', 'userId', 'stateId'),
			'user_usage' => array(1, true, 'usageId', 'userId')
			);
	private $categoryMapping = array(
			/*The numbers 1-9 are the primary keys in the category table*/
			'art and design' => array(1,'bildekunst', 'design og formgjeving', 'film', 'fotografi', 'media', 'teater', 'dans'),
			'architecture' => array(2,'arkitektur'),
			'archeology' => array(3,'arkeologi og forminne'),
			'history' => array(4,'historie', 'historie og geografi', 'språkhistorie', 'sjøfart og kystkultur','kulturminne'),
			'local traditions and food' => array(5,'bunader og folkedrakter', 'hordaland', 'kulturminne', 'kultur og samfunn', 'rallarvegen', 'tradisjonsmat og drikke', 'dans', 'språk', 'fiske og fiskeindustri', 'samer', 'musikk', 'sjøfart og kystkultur', 'fleirkultur og minoritetar'),
			'nature and adventure' => array(6,'fiske og fiskeindustri', 'naturhistorie', 'sport og friluftsliv', 'fiske og fiskeindustri'),
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
			print_r($response);
			print_r($e->getTraceAsString());
            exit;
        }
    }

    /**
    * Retrieves story ID's from "digitalt fortalt" with digitaltmuseum.no's API 
	* and add the stories to the database
    */
    function addStoriesToDatabase($harvestTime){
        $startDoc = 0;
        $numberOfDocs = -1;

        while($startDoc < $numberOfDocs || $numberOfDocs == -1){
            $obj = json_decode(file_get_contents(API_URL.'select?q=artifact.event.place:(s%C3%B8r-tr%C3%B8ndelag%20)&fq=(identifier.owner:H-DF)&start='.$startDoc.'&wt=json&api.key='.API_KEY));
            $numberOfDocs = $obj->response->numFound;
            foreach($obj->response->docs as $doc) {
                $doc = get_object_vars($doc);
                $startDoc += 1;
                $stmt = $this->db->prepare(
                    'INSERT INTO story (storyId) VALUES (:storyId) 
                    ON DUPLICATE KEY UPDATE storyId = :storyId');
                $id = (string)$doc['identifier.id'];
				$stmt->execute(array(':storyId' => $id));
				$storyModel = new storyModel();
				$storyModel->getFromDF($id);
				$this->insertStory($storyModel, $harvestTime);
            }
        }
		$this->deleteNotUpdatedStories($harvestTime);
		print_r('done harvesting');
    }

	/*Remove the stories that are no longer there, according to the harvesting*/
	public function deleteNotUpdatedStories($harvestTime){
		$stories = $this->getAllSelected('story', array('storyId', 'lastChangedTime'), null, null);
		foreach($stories as $story){
			/*If the lastChangedTime was not updated to harvestTime, we must remove the story*/
			if ($story['lastChangedTime'] !== $harvestTime ){
				$this->deleteFromTable('story_dftags', 'storyId', $story['storyId']);
				$this->deleteFromTable('story_media', 'storyId', $story['storyId']);
				$subcategories = $this->getAllSelected('story_subcategory', 'subcategoryId', 'storyId',$story['storyId']);
				$this->deleteFromTable('story_subcategory', 'storyId', $story['storyId']);
				
				/*Remove the whole subcategory and the mapping if this was
				 the last story in this subcategory*/
				if(!is_null($subcategories)){
					foreach ($subcategories as $subcategory){
						$numberOfStories = $this->getAllSelected('story_subcategory', 'storyId','subcategoryId', $subcategory['subcategoryId']);
						/*Find the subcategories with zeros stories connected to them and remove them*/
						if(is_null($numberOfStories)){
							$this->deleteFromTable('category_mapping', 'subcategoryId', $subcategory['subcategoryId']);
							$this->deleteFromTable('subcategory', 'subcategoryId', $subcategory['subcategoryId']);	
						}					
					}
				}
				$this->deleteFromTable('story', 'storyId', $story['storyId']);
			}
		}
	}
	
	public function getConn(){
		return $this->db;
	}
	
	/*Inserting story in story table and related tables*/
	public function insertStory($story, $harvestTime){
		
		/*Inserting story in story table*/
		$values = array($story->getstoryId(),$story->gettitle(),$story->getCreatorList()[0],$story->getDate(),$story->getInstitution(),$story->getIntroduction(), $harvestTime);
		$this->insertUpdateAll('story',$values);
		
		/*Inserting subcategories, connects them to the story and maps them to our categories*/
		if(!empty($story->getsubCategoryList())){
			for($x=0; $x<sizeof($story->getsubCategoryList()); $x++){
				$subcategory = ''.$story->getsubCategoryNames()[$x].'';
				$categories = array();
				$this->insertUpdateAll('subcategory', array($story->getsubCategoryList()[$x], $subcategory));
				$categories = $this->getCategories($subcategory);
				if(!empty($categories)){
					foreach($categories as $category){
						/*Assumes that there exists a category table with ids 1-9*/
						$this->insertUpdateAll('category_mapping', array($category, $story->getsubCategoryList()[$x]));
					}
				}
				$this->insertUpdateAll('story_subcategory', array($story->getstoryId(), $story->getsubCategoryList()[$x]));
			}
		}
			
		/*Inserting tags and connects them to the story*/
		if(!empty($story->getSubjectList())){
			foreach($story->getSubjectList() as $tag){
				$this->insertUpdateAll('story_dftags', array($story->getstoryId(), $tag));	
			}
		}
		
		/*Inserting the stories media format
		  Assumes that a media_format table with 1=picture, 2=audio, 3=video exists
		  array_filter removes empty values*/
		if(array_filter(array($story->getImageList()))){
			if(array_filter($story->getImageList())){
				$this->insertUpdateAll('story_media', array($story->getstoryId(), 1));
			}
		}
		if(array_filter(array($story->getAudioList()))){
			if(array_filter($story->getAudioList())){
				$this->insertUpdateAll('story_media', array($story->getstoryId(), 2));
			}
		}
		if(array_filter(array($story->getVideoList()))){
			if(array_filter($story->getVideoList())){
				$this->insertUpdateAll('story_media', array($story->getstoryId(), 3));
			}
		}
	}
    public function uptadeUserInfo($user){
        $values = array();
        /*Inserting story in story table*/
        if($user->getUserId() == -1){
            $values = array($user->getMail(),$user->getAgeGroup(),$user->getGender(),$user->getLocation());
        } 
        else{
             $values = array($user->getUserId(),$user->getMail(),$user->getAgeGroup(),$user->getGender(),$user->getLocation());
        }
        
        $this->insertUpdateAll('user',$values);
        $userId = $this->db->lastInsertId();
        /*Inserting category preferences*/
        foreach($user->getCategoryPrefs() as $category){
        	print_r($userId ,$category);
            $this->insertUpdateAll('category_preference', array($userId,$category)); 
        }
    }	

	function close(){
		$this->db = null;
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
	
	/*Updates $insertColumn in $tableName with $updateValue
	* $keyValues define which row to update
	* $keyValues might be a string or an array, depending on the number of primary keys.
	*/
	function updateOneValue($tableName, $insertColumn, $updateValue, $keyValues){
		/*Get the columns in the table we are updating*/
		$tableColumns = $this->getTableColumn($tableName);
		
		/*Find the key columns in the table. Assumes that these columns is placed first in the table
		* and start from number 2 in $tableColumns
		* $tableColumns[0] is the number of primary keys in the table*/
		$keyColumns = array_slice($tableColumns, 2, $tableColumns[0]);
		$whereString = '';
		
		/*If $keyValues is an array we (probably) have a multiple-valued primary key (works with an array of one value as well)
		* We have to loop through the primary key columns to create placeholders.
		*/
		if (is_array($keyValues)){
			$whereString .= ''.$keyColumns[0].'=? ';
			for($x=1; $x<sizeof($keyColumns); $x++){
				$whereString .= 'AND '.$keyColumns[$x].'=? ';
			}	
			$values = array_merge(array($updateValue), $keyValues);
		}
		/*If $keyValues is not an array, we only have one where clause and 
		* we need to create an array for the values we are inserting in the query.*/
		else {
			$keyColumn = implode(',', $keyColumns); 
			$whereString .= ''.$keyColumn.'=? ';
			$values = array($updateValue, $keyValues);
		}
		
		$query = 'UPDATE '.$tableName.' SET '.$insertColumn.'=? WHERE '.$whereString.'';
		$stmt = $this->db->prepare($query);
		$stmt->execute($values);
	}
	
	/*  Inserts all values in $valuesArray in table $tableName. 
		The number of values in $valuesArray needs to match the number of columns in the table.
		If the primary key already exists, it updates all other values.
	*/
    public function insertUpdateAll($tableName,$valuesArray) {
        $columnsArray = array_slice($this->getTableColumn($tableName),1);//Slice off the primary key number
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
		/*If the primary key is auto incremented*/
		else {
			/*If we are updating a row with primary key that exists
			$valuesArray+1 because $columnsArray[0] = the boolean value*/
			if (sizeof($columnsArray) == sizeof($valuesArray)+1){
				/*If we are updating,we need to remove the boolean true but not the primary key 
				* (we need the primary key to know which row to update)*/
				$cols = implode(",", array_slice($columnsArray,1));
				$insert = '?,';
				/*The $valuesArray include the primary key. We need this value in values(?,?,...,?),
				* but not in the updateString, so we have to slice it away*/
				$values = array_merge($valuesArray, array_slice($valuesArray, 1));
			}
			/*If we are inserting a new row*/
			else {
				/* If the primary key is auto incremented, we need to remove the boolean true and the primary key from the array */
				$cols = implode(",", array_slice($columnsArray,2));
				$insert = '';
				/* If the primary key is auto incremented, the parameter $valuesArray doesn't include a key, so we don't need to slice*/
				$values = array_merge($valuesArray, $valuesArray);
			}
		}
		/*Looping through the columns to create placeholders*/
		for ($x = 2; $x < sizeof($columnsArray); $x++){
			/*Creating placeholders for each value for inserting values*/
			$insert .= '?,';
			/*Creating plateholders for each value for updating, except for primary key*/
			$update[] = ''.$columnsArray[$x].'=?';
		}
		$insert = trim($insert,","); //Remove the extra comma at the end
		$updateString = implode(",", $update);
				
		$query = 'INSERT INTO '.$tableName.' ('.$cols.') VALUES ('.$insert.') ON DUPLICATE KEY UPDATE ';
		if(!empty($updateString)){
			$query .= ''.$updateString.'';
		}
		else {
			/*Just a meaningless operation to avoid primary key error*/
			$duplicatePrimary = ''.$columnsArray[1].'='.$columnsArray[1].''; //Means that we are not updating anything
			$query .= ''.$duplicatePrimary.''; 
		}

        $stmt = $this->db->prepare($query);

        $stmt->execute($values);
    }


    public function fetchStories($id_array){
    	foreach($id_array as $id){
    		$this->fetchStory($id);
    	}
    }

    /**Returns story information stored in database, should take userId as parameter*/
    public function fetchStory($storyId){
    	$category = $this->db->prepare(
    		"SELECT group_concat(distinct categoryName) as categories
    		FROM story as s, category_mapping as cm, story_subcategory as ss, subcategory as sc, category as c, story_media as sm
    		WHERE sc.subcategoryId = cm.subcategoryId 
    		AND c.categoryId = cm.categoryId
    		AND ss.subcategoryId = sc.subcategoryId
    		AND s.storyId = ss.storyId
    		AND s.storyId = ?");
    	//$storedStory = $this->db->prepare(
    	//	"SELECT * FROM stored_story WHERE storyId = ? AND userId = ?");
		$category->execute(array($storyId));
		//$storedStory->execute(array($storyId, $userId));
		$rows = $category->fetchAll(PDO::FETCH_ASSOC);
		//$rows2 = $storedStory->fetchAll(PDO::FETCH_ASSOC);
		//if(count($rows2) > 0) return array_merge($rows[0], $rows2[0]);
		return $rows[0];
    }

    function getAllStories(){
		$stmt = $this->db->prepare(
			"SELECT story.storyId, title, author, introduction, group_concat(distinct categoryName) as categories, mediaId
			FROM story, category_mapping, story_subcategory, subcategory, category, story_media
			WHERE subcategory.subcategoryId = category_mapping.subcategoryId 
			AND category.categoryId = category_mapping.categoryId
			AND story_subcategory.subcategoryId = subcategory.subcategoryId
			AND story.storyId = story_subcategory.storyId
			AND story.storyId = story_media.storyId
			GROUP BY story.storyId LIMIT 20");
		$stmt->execute();
		$stmt2 = $this->db->prepare(
			"SELECT story.storyId, title, author, introduction, mediaId
			FROM story, story_media
			WHERE story.storyId = story_media.storyId AND story.storyId NOT IN (SELECT storyId FROM story_subcategory)
group by story.storyId");
		$stmt2->execute();
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
		return array_merge($rows, $rows2);
	}
	/* Get $selectColumns in $tableName based on $whereValues*/
	function getAllSelected($tableName, $selectColumns, $whereColumns, $whereValues){
		$values = array();
		if (is_array($selectColumns)){
			$selectColumns = implode(",", $selectColumns);
		}
		if(!is_null($whereValues)){
			list($where, $values) = $this->getWhereStringAndValuesArray($whereColumns, $whereValues);
			$query = "SELECT ".$selectColumns." FROM ".$tableName." WHERE ".$where."";
			$stmt = $this->db->prepare($query);
			$stmt->execute($values);
		}
		else{
			$query = "SELECT ".$selectColumns." FROM ".$tableName."";
			$stmt = $this->db->prepare($query);
			$stmt->execute();
		}
		//$stmt = $this->db->prepare($query);
		//$stmt->execute($values);
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if ($stmt->rowCount() > 0){
			return($rows);
		}
		else {
			return null;
		}
	}
	/*Get all stories a user has tagged with $tagName*/
	function getStoryList($userId, $tagName){
		$query = "SELECT s.storyId, title, author, introduction, date, us.tagName, 
				group_concat(distinct categoryName) as categories, mediaId
				FROM story as s, user_storytag as us, story_subcategory as ss, 
				category_mapping as cm, category as c, story_media as sm
				WHERE s.storyId = us.storyId
				AND us.userId = ? AND us.tagName = ?
				AND s.storyId = ss.storyId
				AND ss.subcategoryId = cm.subcategoryId
				AND cm.categoryId = c.categoryId
				AND s.storyId = sm.storyId
				GROUP BY s.storyId";
		$stmt = $this->db->prepare($query);
		$stmt->execute(array($userId, $tagName));
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return($rows);
	}
	function getWhereStringAndValuesArray($whereColumns, $whereValues){
		$whereString = "";
		if (is_array($whereValues)){
			if (is_array($whereColumns)){
				$whereString .= ''.$whereColumns[0].'=? ';
				for($x=1; $x<sizeof($whereColumns); $x++){
					$whereString .= 'AND '.$whereColumns[$x].'=? ';
				}
			}
			else {
				$whereString .= ''.$whereColumns.'=? ';
			}
			$values = $whereValues;
		}
		else { 
			if (is_array($whereColumns)){
				$whereColumn = implode(",", $whereColumns);
				$whereString .= ''.$whereColumn.'=? ';
			}
			else {
				$whereString .= ''.$whereColumns.'=? ';
			}
			$values = array($whereValues);
		}	
		return array($whereString, $values);
	}
	/*Delete the rows in $tableName that match the where-clauses*/
	function deleteFromTable($tableName, $whereColumns, $whereValues){
		list($where, $values) = $this->getWhereStringAndValuesArray($whereColumns, $whereValues);
		$query = "DELETE FROM ".$tableName." WHERE ".$where."";
		$stmt = $this->db->prepare($query);
		$stmt->execute($values);
	}

}
$db = new DbHelper();
//$db->insertUpdateAll('user_storytag', array(1, 'DF.1295', 'test'));
//$db->deleteFromTable('user_storytag', array('userId', 'tagName'), array(1, 'test'));
//$db->deleteFromTable('user_tag', array('userId', 'tagName'), array(1, 'test'));
?>

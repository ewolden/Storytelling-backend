<?php
require_once 'dbHelper.php';
require_once '../models/storyModel.php';

/**
* This class handles database communication related to stories
*/
class dbStory extends dbHelper{
		
	/*Construct a new dbHelper-instance*/
	public function __construct() {
		parent::__construct();
	}
	
	/**
    * Retrieves story ID's from "digitalt fortalt" with digitaltmuseum.no's API 
	* and add the stories to the database
    */
    public function addStoriesToDatabase($harvestTime){
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
		$stories = $this->getSelected('story', array('storyId', 'lastChangedTime'), null, null);
		foreach($stories as $story){
			/*If the lastChangedTime was not updated to harvestTime, we must remove the story*/
			if ($story['lastChangedTime'] !== $harvestTime ){
				$this->deleteFromTable('story_dftags', 'storyId', $story['storyId']);
				$this->deleteFromTable('story_media', 'storyId', $story['storyId']);
				$subcategories = $this->getSelected('story_subcategory', 'subcategoryId', 'storyId',$story['storyId']);
				$this->deleteFromTable('story_subcategory', 'storyId', $story['storyId']);
				
				/*Remove the whole subcategory and the mapping if this was
				 the last story in this subcategory*/
				if(!is_null($subcategories)){
					foreach ($subcategories as $subcategory){
						$numberOfStories = $this->getSelected('story_subcategory', 'storyId','subcategoryId', $subcategory['subcategoryId']);
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
	
	/*Inserting story in story table and related tables*/
	public function insertStory($story, $harvestTime){
		
		/*Inserting story in story table*/
		$values = array($story->getstoryId(),$story->getnumericalId(),$story->gettitle(),$story->getCreatorList()[0],$story->getDate(),$story->getInstitution(),$story->getIntroduction(), $harvestTime);
		$this->insertUpdateAll('story',$values);
		
		/*Delete the current subcategories connected to this story to make sure it's up to date*/
		$this->deleteFromTable('story_subcategory', 'storyId', $story->getstoryId());
	
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
		
		$this->deleteFromTable('story_media', 'storyId', $story->getstoryId());
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

	
    public function getAllStories(){
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
	
	/*Get all stories a user has tagged with $tagName*/
	public function getStoryList($userId, $tagName){
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

}
?>
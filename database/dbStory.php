<?php
/*Contributors: Kjersti Fagerholt, Roar Gjøvaag, Ragnhild Krogh, Espen Strømjordet,
 Audun Sæther, Hanne Marie Trelease, Eivind Halmøy Wolden

 "Copyright 2015 The TAG CLOUD/SINTEF project

 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at

 http://www.apache.org/licenses/LICENSE-2.0

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License."
 */

require_once (__DIR__.'/dbHelper.php');
require_once (__DIR__.'/../models/storyModel.php');

/**
 * This class handles database communication related to stories
 * @author Audun Sæther
 * @author Kjersti Fagerholt
 * @author Eivind Halmøy Wolden
 * @author Hanne Marie Trelease
 */
class dbStory extends dbHelper{
		
	/** Construct a new dbHelper-instance */
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * Retrieves story ID's from "digitalt fortalt" with digitaltmuseum.no's API 
	 * and add the stories to the database
	 * @param date $harvestTime	Time of harvest
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
	
    /**
     * Remove the stories that are no longer in digitalt fortalt, according to the harvesting
     * @param date $harvestTime time 
     */
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
				$this->deleteFromTable('preference_value', 'storyId', $story['storyId']);
				$this->deleteFromTable('stored_story', 'storyId', $story['storyId']);
				$this->deleteFromTable('story_state', 'storyId', $story['storyId']);
				$this->deleteFromTable('user_storytag', 'storyId', $story['storyId']);
				$this->deleteFromTable('story', 'storyId', $story['storyId']);
			}
		}
	}
	
	/**
	 * Inserting story in story table and related tables
	 * @param storyModel $story
	 * @param date $harvestTime time of harvest
	 */
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

	/**
	 * Returns story information and storyinfo related to user stored in database 
	 * @param String $storyId
	 * @param int $userId
	 * @return $rows rows returned from the database query
	 */
    public function fetchStory($storyId, $userId){
    	$categories = $this->db->prepare(
    		"SELECT group_concat(distinct categoryId) as categories
    		FROM story as s, category_mapping as cm, story_subcategory as ss, subcategory as sc, story_media as sm
    		WHERE sc.subcategoryId = cm.subcategoryId
    		AND ss.subcategoryId = sc.subcategoryId
    		AND s.storyId = ss.storyId
    		AND s.storyId = ?");
		$categories->execute(array($storyId));
		$data = $categories->fetchAll(PDO::FETCH_ASSOC)[0];

		$storedStory = $this->getSelected('stored_story', '*', array('userId', 'storyId'), array($userId, $storyId));
		$storyTags = $this->getSelected('user_storytag', 'tagName', array('userId', 'storyId'), array($userId, $storyId));
		if(count($storedStory) > 0) $data = array_merge($data, array('storedStory' => $storedStory[0]));
		if(count($storyTags) > 0) $data = array_merge($data, array('tags' => $storyTags));
		return $data;
    }
	
    /**
     * Gets story recommendations for a user
     * @param int $userId
     * @return $rows array of stories
     */
	public function getRecommendedStories($userId){
		$stmt = $this->db->prepare(
			"select ss.userId, ss.storyId, ss.recommend_ranking, ss.explanation, ss.false_recommend, nes.title, nes.introduction,nes.author,group_concat(distinct nes.categories) as categories, group_concat(distinct nes.mediaId) as mediaId
			from stored_story as ss
			left join (SELECT s.storyId as storyId ,s.title as title, s.introduction as introduction, s.author as author,group_concat(distinct sm.mediaId) as mediaId, group_concat(distinct nested.categoryId) as categories
						FROM story as s
						LEFT JOIN (SELECT ss.storyId as storyId, cm.categoryId as categoryId
						FROM category_mapping as cm, story_subcategory as ss, subcategory as sub,category as c
						WHERE sub.subcategoryId = cm.subcategoryId
						AND cm.categoryId = c.categoryId
						AND ss.subcategoryId = sub.subcategoryId) as nested ON s.storyId = nested.storyId
			left join story_media as sm ON s.storyId=sm.storyId
			group by s.storyId) as nes
			on ss.storyId=nes.storyId
			where userId=? and recommend_ranking IS NOT NULL
			GROUP BY ss.storyId
			order by recommend_ranking asc");
		$stmt->execute(array($userId));
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		/*When getRecommendedStories is called, the stories are put in the stories array at frontend.
		This is recorded in the database with this method*/
		$this->addToFrontendArray($rows, $userId);
		return $rows;
	}
	
	/**
	 * Removes stories in the frontend-array for a user
	 * @param int $userId
	 */
	public function emptyFrontendArray($userId){
		$stmt = $this->db->prepare("UPDATE stored_story SET in_frontend_array=? WHERE userId=?");
		$stmt->execute(array(0,$userId));
	}
	
	/**
	 * Adds the stories in $rows to the frontend-array in the database
	 * @param array $rows recommended stories
	 * @param int $userId
	 */
	private function addToFrontendArray($rows, $userId){		
		$stmt = $this->db->prepare("UPDATE stored_story SET in_frontend_array=? WHERE userId=? AND storyId=?");
		foreach($rows as $story) {
			$stmt->execute(array(1,$userId, $story['storyId']));
		}		
	}
	
	/**
	 * Get all stories a user has tagged with $tagName
	 * @param int $userId
	 * @param String $tagName
	 * @return $rows rows returned from the database query
	 */
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
				GROUP BY s.storyId
				ORDER BY us.insertion_time";
		$stmt = $this->db->prepare($query);
		$stmt->execute(array($userId, $tagName));
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return($rows);
	}

	/**
	 * Get the subcategory-IDs connected to each story. 
	 * Using LEFT JOIN to also get stories not connected to any subcategory 
	 * @return $rows rows returned from the database query
	 */
	public function getSubcategoriesPerStory(){
		$query = "SELECT s.numericalId, group_concat(ss.subcategoryId) as subcategories
				 FROM story as s
				 LEFT JOIN story_subcategory as ss ON s.storyId = ss.storyId
				 GROUP BY s.numericalId";
		$stmt = $this->db->prepare($query);
		$stmt->execute();
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return($rows);
	}
	
	/**
	 * Get categories for each story, included those without categories
	 * @return $rows rows returned from the database query
	 */
	public function getStories(){
		$query = "SELECT s.storyId,numericalId,group_concat(distinct nested.categoryId) as categories
				 FROM story as s
				LEFT JOIN (SELECT ss.storyId as storyId, cm.categoryId as categoryId FROM category_mapping as cm, story_subcategory as ss, subcategory as sub
							WHERE sub.subcategoryId = cm.subcategoryId 
							AND ss.subcategoryId = sub.subcategoryId) as nested
				ON s.storyId = nested.storyId
				GROUP BY s.storyId";
		$stmt = $this->db->prepare($query);
		$stmt->execute();
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return($rows);
	}
	
	/**
	 * Retrieve the number of times a story has had a state for a given user and a given story
	 * @param int $userId
	 * @param String $storyId
	 * @return $rows rows returned from the database query
	 */
	public function getStatesPerStory($userId, $storyId){
		$query = "SELECT stateId, count(storyId) as numTimesRecorded, max(point_in_time) as latestStateTime
				  FROM story_state
				  WHERE userId = ? AND storyId = ?
				  GROUP BY stateId";
		$stmt = $this->db->prepare($query);
		$stmt->execute(array($userId, $storyId));
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return($rows);
	}	
}
?>
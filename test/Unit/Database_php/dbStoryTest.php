<?php
require_once ('../../../models/storyModel.php');
require_once ('../../../database/dbHelper.php');
require_once ('../../../database/dbStory.php');

class dbStoryTest extends PHPUnit_Framework_TestCase{

	public $db;
	public $arrayOfStories;

	public function setup(){
		$this->db = new dbStory();
		$this->arrayOfStories = array('DF.1115','DF.1160','DF.1295','DF.1375','DF.1600','DF.1501',
			'DF.1547','DF.1812','DF.1813','DF.1815','DF.5230','DF.5247','DF.5278','DF.5504','DF.5559',
			'DF.5669','DF.5670','DF.5672','DF.5673','DF.5674','DF.5675','DF.5702','DF.5709','DF.5712',
			'DF.5716','DF.5717','DF.5747','DF.5861','DF.5886','DF.5905','DF.6028','DF.6029','DF.6030');
		
	}
	public function testFetchStory(){

		////////Testing fetching Story////////////////////
		//////////////////////////////////////////////////
		$storyId = 'DF.1812';
		$userId = 105;
		
		$data = $this->db->fetchStory($storyId, $userId);
		print_r("fetch story");
		print_r($data);
		//This user has tagged this story so this should be returned
		$this->assertArrayHasKey("categories",$data);
		$this->assertArrayHasKey("tags",$data);
		$this->assertArrayHasKey("tagName",$data['tags'][0]);

	}
	public function testGetRecommendedStories(){
		$userId = rand(1,312);
		
		$rows = $this->db->getRecommendedStories($userId);
		print_r($rows);
		$this->assertEquals(10,count($rows));
		for ($i=0; $i < count($rows); $i++) { 
			$this->assertArrayHasKey("userId", $rows[$i]);
			$this->assertArrayHasKey("storyId", $rows[$i]);
			$this->assertArrayHasKey("recommend_ranking", $rows[$i]);
			$this->assertArrayHasKey("explanation", $rows[$i]);
			$this->assertArrayHasKey("false_recommend", $rows[$i]);
			$this->assertArrayHasKey("title", $rows[$i]);
			$this->assertArrayHasKey("introduction", $rows[$i]);
			$this->assertArrayHasKey("author", $rows[$i]);
			$this->assertArrayHasKey("categories", $rows[$i]);
			$this->assertArrayHasKey("mediaId", $rows[$i]);

		}

		//SHOULD GET 100 Assertions. 
	}

	public function testGetStoryList(){
	//using a user that actually has a tagList.
		$exampleUserId = 103;
		$tagName = 'NyTestTag';

		$result = $this->db->getStoryList($exampleUserId, $tagName);

		for ($i=0; $i < count($result); $i++) { 
			$this->assertArrayHasKey("storyId", $result[$i]);
			$this->assertArrayHasKey("title", $result[$i]);
			$this->assertArrayHasKey("author", $result[$i]);
			$this->assertArrayHasKey("introduction", $result[$i]);
			$this->assertArrayHasKey("date", $result[$i]);
			$this->assertArrayHasKey("tagName", $result[$i]);
			$this->assertArrayHasKey("categories", $result[$i]);
			$this->assertArrayHasKey("mediaId", $result[$i]);

		}

	}

	public function testGetSubcategoriesPerStory(){
		$result = $this->db->getSubcategoriesPerStory();

		//Should return an array of 167 stories with numericalId and subcategories.//
		$this->assertInternalType("array", $result);
		$this->assertInternalType("array", $result[0]);

		$this->assertEquals(167, count($result));
		for ($i=0; $i < count($result); $i++) { 
			$this->assertArrayHasKey("numericalId", $result[0]);
			$this->assertArrayHasKey("subcategories", $result[0]);
		}
		

	}

	public function testGetStories(){
		$result = $this->db->getStories();
		//Should return an array with the 167 stories with storyId, numericalId and categories//

		$this->assertInternalType("array", $result);
		$this->assertInternalType("array", $result[0]);

		$this->assertEquals(167, count($result));
		for ($i=0; $i < count($result); $i++) { 
			$this->assertArrayHasKey("storyId", $result[$i]);
			$this->assertArrayHasKey("numericalId", $result[$i]);
			$this->assertArrayHasKey("categories", $result[$i]);
		}
	}

	public function testGetStatesPerStory(){
		$userId = 258;
		$storyId = 'DF.1600';

		$result = $this->db->getStatesPerStory($userId, $storyId);
		//Test if the array include the right elements
		//print_r($result);
		if($result != null){
			for ($i=0; $i < count($result); $i++) { 
				$this->assertArrayHasKey("stateId", $result[$i]);
				$this->assertArrayHasKey("numTimesRecorded", $result[$i]);
				$this->assertArrayHasKey("latestStateTime", $result[$i]);

			}
		}
	}
}
?>
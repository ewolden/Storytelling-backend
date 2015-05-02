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
		///////////
		$storyId = $this->arrayOfStories[array_rand($this->arrayOfStories)];
		$userId = rand(1,312);
		
		$data = $this->db->fetchStory($storyId, $userId);
		print_r($data); 
		//$this->assertTrue($this->db->updateOneValue($tableName, $insertColumn, $updateValue, $keyValues));



	}
	public function testGetRecommendedStories(){
		$userId = rand(1,312);
		
		$rows = $this->db->getRecommendedStories($userId);
		print_r($rows); 
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
}
?>
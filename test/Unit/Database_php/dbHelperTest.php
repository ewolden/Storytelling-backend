<?php
require_once ('../../../models/storyModel.php');
require_once ('../../../database/dbHelper.php');
class dbHelperTest extends PHPUnit_Framework_TestCase{

	public $db;
	public $dbStory;
	public $arrayOfStories;

	public function setup(){
		$this->db = new dbHelper();
		$this->arrayOfStories = array('DF.1115','DF.1160','DF.1295','DF.1375','DF.1600','DF.1501',
			'DF.1547','DF.1812','DF.1813','DF.1815','DF.5230','DF.5247','DF.5278','DF.5504','DF.5559',
			'DF.5669','DF.5670','DF.5672','DF.5673','DF.5674','DF.5675','DF.5702','DF.5709','DF.5712',
			'DF.5716','DF.5717','DF.5747','DF.5861','DF.5886','DF.5905','DF.6028','DF.6029','DF.6030');
		
	}
	public function testUpdateOneValue(){

		////////Testing updateOneValue////////////////////
		////////This function in used to update ratings///
		$tableName = 'stored_story';
		$insertColumn = 'rating';
		$updateValue = 1;
		print_r(rand(1,312));
		$userId = rand(1,312);
		print_r($userId);
		$storyId = $this->arrayOfStories[array_rand($this->arrayOfStories)];
		print_r($storyId);
		$keyValues = array($userId, $storyId);

		//$this->assertTrue($this->db->updateOneValue($tableName, $insertColumn, $updateValue, $keyValues));



	}

	public function testInsertUpdateAll(){
	
		////////////RATING////////////////
		////////////////////////////

		$tableName = 'stored_story';
		$userId = rand(1,312);
		print_r("\n".$userId);
		$storyId = $this->arrayOfStories[array_rand($this->arrayOfStories)];
		print_r("\n".$storyId);
		$rating = 5;
		$valuesArray = array($userId, $storyId, null, $rating, 0, 0, null, 0);

		$this->assertTrue($this->db->insertUpdateAll($tableName, $valuesArray));

		////////////addNewTag////////////////
		////////////////////////////

		$tableName = 'user_tag';
		$userId = rand(1,312);
		print_r("\n".$userId);

		$tagName = 'NyTestTag2';
		$valuesArray = array($userId, $tagName);

		$this->assertTrue($this->db->insertUpdateAll($tableName, $valuesArray));

		$tableName = 'user_storytag';
		
		$storyId = $this->arrayOfStories[array_rand($this->arrayOfStories)];
		print_r($storyId);
		$valuesArray = array($userId, $storyId,$tagName);

		$this->assertTrue($this->db->insertUpdateAll($tableName, $valuesArray));

		////////////TagStory////////////////
		////////////////////////////
		$tableName = 'story_state';
		$userId = rand(1,312);
		
		$tagName = 'Les senere';
		$valuesArray = array($storyId, $userId, 3);

		$this->assertTrue($this->db->insertUpdateAll($tableName, $valuesArray));		
		
		///IF TAGNAME IS NOT LES SENERE
		$tableName = 'user_storytag';
		$userId = rand(1,312);
		$storyId = $this->arrayOfStories[array_rand($this->arrayOfStories)];

		$tagName = 'Some tag';
		$valuesArray = array($userId, $storyId, $tagName);

		$this->assertTrue($this->db->insertUpdateAll($tableName, $valuesArray));		
		
		////////////reject Story////////////////
		////////////////////////////
		$tableName = 'story_state';
		$userId = rand(1,312);
		$storyId = $this->arrayOfStories[array_rand($this->arrayOfStories)];
		$valuesArray = array($storyId, $userId,2);

		$this->assertTrue($this->db->insertUpdateAll($tableName, $valuesArray));


		////////////add Usage////////////////
		//////////////THIS ONE IS NOT DONE YET //////////////
		// $tableName = 'user_usage';
		// $userId = rand(1,312);
		// $usageType = null;
		// $valuesArray = array($storyId, $userId);

		// $this->assertTrue($this->db->insertUpdateAll($tableName, $valuesArray));
	
		////////////recommendedStory////////////////
		////////////////////////////////////////////

		$tableName = 'story_state';
		$userId = rand(1,312);
		$storyId = $this->arrayOfStories[array_rand($this->arrayOfStories)];
		$valuesArray = array($storyId, $userId,1);

		$this->assertTrue($this->db->insertUpdateAll($tableName, $valuesArray));

	}

	public function testGetSelected(){

		//HOW MANY RATINGS IN COMPUTEPREFERENCEVALUE//
		$userId = rand(1,312);
		$storyId = $this->arrayOfStories[array_rand($this->arrayOfStories)];
		$tableName = 'stored_story';
		$selectColumns = 'rating';
		$whereColumns = array('storyId', 'userId');
		$whereValues = array($storyId, $userId);
		$row = $this->db->getSelected($tableName, $selectColumns, $whereColumns, $whereValues);
		print_r($row);

		//GET ALL TAGS CONNECTED TO A USER - CONTROLLER.PHP// 
		$userId = rand(1,312);
		$tableName = 'user_tag';
		$selectColumns = 'tagName';
		$whereColumns = array('userId');
		$whereValues = array($userId);

		$row = $this->db->getSelected($tableName, $selectColumns, $whereColumns, $whereValues);
		//Testing that the result is a row. Could be empty, could contain tag names
		$this->assertInternalType("array",$row);

		//GET ALL TAGS CONNECTED TO A STORY TO A USER//
		//CANNOT USE RANDOM USERID AND STORYID HERE//
		$userId = 105;
		$storyId = 'DF.1295';
		$tableName = 'user_storytag';
		$selectColumns = 'tagName';
		$whereColumns = array('userId','storyId');
		$whereValues = array($userId, $storyId);

		$row = $this->db->getSelected($tableName, $selectColumns, $whereColumns, $whereValues);
		//print_r($row);
		//Testing that the result is a row. Could be empty, could contain tag names
		$this->assertInternalType("array",$row);

		//GET STORIES : USED IN HELP TO REMOVE ALL STORIES THAT ARE NO LONGER THERE. 

		$tableName = 'story';
		$selectColumns = array('storyId','lastChangedTime');
		$whereColumns = null;
		$whereValues = null;

		$row = $this->db->getSelected($tableName, $selectColumns, $whereColumns, $whereValues);
		//Should be a array of all the stories with connected lastChangedTime.
		$this->assertInternalType("array",$row);
		if($row!=null){
			for ($i=0; $i < count($row); $i++) { 
				$this->assertArrayHasKey("storyId",$row[$i]);		
				$this->assertArrayHasKey("lastChangedTime",$row[$i]);
			}
		}

		//GET SUBCATEGORIES: USED IN HELP TO REMOVE ALL STORIES THAT ARE NO LONGER THERE.

		$tableName = 'story_subcategory';
		$selectColumns = 'subcategoryId';
		$whereColumns = 'storyId';
		$whereValues = $this->arrayOfStories[array_rand($this->arrayOfStories)];

		$row = $this->db->getSelected($tableName, $selectColumns, $whereColumns, $whereValues);
		//Should be a array of all subcategory for this.
		//print_r($row);
		$this->assertInternalType("array",$row);
		//check that every 
		if($row!=null){
			for ($i=0; $i < count($row); $i++) { 
			$this->assertArrayHasKey("subcategoryId",$row[$i]);
			}
		}
		

		////RETURNING STORY INFORMATION///////////
		///CANNOT USE RANDOM USERID AND STORYID///
		$userId = 105;
		$storyId = 'DF.5220';
		$tableName = 'stored_story';
		$selectColumns = '*';
		$whereColumns = array('userId','storyId');
		$whereValues =array($userId, $storyId);

		$row = $this->db->getSelected($tableName, $selectColumns, $whereColumns, $whereValues);
	//	print_r("stored story"."\n");
	//	print_r($row);

		for ($i=0; $i < count($row); $i++) { 
			$this->assertArrayHasKey("userId",$row[$i]);
			$this->assertArrayHasKey("storyId",$row[$i]);
			$this->assertArrayHasKey("explanation",$row[$i]);
			$this->assertArrayHasKey("rating",$row[$i]);
			$this->assertArrayHasKey("false_recommend",$row[$i]);
			$this->assertArrayHasKey("recommend_ranking",$row[$i]);
			$this->assertArrayHasKey("in_frontend_array",$row[$i]);
			$this->assertArrayHasKey("estimated_Rating",$row[$i]);
		}

		$tableName = 'user_storytag';
		$selectColumns = 'tagName';
		$whereColumns = array('userId','storyId');
		$whereValues =array($userId, $storyId);

		$row = $this->db->getSelected($tableName, $selectColumns, $whereColumns, $whereValues);
	//	print_r("user storytag"."\n");
	//	print_r($row);
		for ($i=0; $i < count($row); $i++) { 
			$this->assertArrayHasKey("tagName",$row[$i]);
		}

	}

}

?>
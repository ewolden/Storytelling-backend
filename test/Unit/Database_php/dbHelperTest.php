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
		$this->arrayOfTestUsers = array(519,524,528,532,534,535,537,543,547,552,554,562,571,573,575,577,580);
	}
	public function testUpdateOneValue(){

		////////Testing updateOneValue////////////////////
		////////This function in used to update ratings///
		$tableName = 'stored_story';
		$insertColumn = 'rating';
		$updateValue = 2;
		$userId = $this->arrayOfTestUsers[array_rand($this->arrayOfTestUsers)];

		print_r("testUpdateOneValue :"."\n");
		print_r($userId);
		print_r("\n");
		$storyId = $this->arrayOfStories[array_rand($this->arrayOfStories)];
		print_r($storyId);
		print_r("\n");

		$keyValues = array($userId, $storyId);

		$this->assertTrue($this->db->updateOneValue($tableName, $insertColumn, $updateValue, $keyValues));
	}

	public function testInsertUpdateAll(){
	
		////////////RATING////////////////
		////////////////////////////

		$tableName = 'stored_story';
		$userId = $this->arrayOfTestUsers[array_rand($this->arrayOfTestUsers)];
		print_r("testInsertUpdateAll :"."\n");

		print_r($userId);
		$storyId = $this->arrayOfStories[array_rand($this->arrayOfStories)];
		print_r("\n".$storyId);
		$rating = 5;
		$valuesArray = array($userId, $storyId, null, $rating, 0, 0, null, 0);

		$this->assertTrue($this->db->insertUpdateAll($tableName, $valuesArray));

		////////////addNewTag////////////////
		////////////////////////////

		$tableName = 'user_tag';
		$userId = $this->arrayOfTestUsers[array_rand($this->arrayOfTestUsers)];
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
		$userId = $this->arrayOfTestUsers[array_rand($this->arrayOfTestUsers)];
		
		$tagName = 'Les senere';
		$valuesArray = array($storyId, $userId, 3);

		$this->assertTrue($this->db->insertUpdateAll($tableName, $valuesArray));		
		
		///IF TAGNAME IS NOT LES SENERE
		$tableName = 'user_storytag';
		$userId = $this->arrayOfTestUsers[array_rand($this->arrayOfTestUsers)];
		$storyId = $this->arrayOfStories[array_rand($this->arrayOfStories)];

		$tagName = 'Some tag';
		$valuesArray = array($userId, $storyId, $tagName);

		$this->assertTrue($this->db->insertUpdateAll($tableName, $valuesArray));		
		
		////////////reject Story////////////////
		////////////////////////////
		$tableName = 'story_state';
		$userId = $this->arrayOfTestUsers[array_rand($this->arrayOfTestUsers)];
		$storyId = $this->arrayOfStories[array_rand($this->arrayOfStories)];
		$valuesArray = array($storyId, $userId,2);

		$this->assertTrue($this->db->insertUpdateAll($tableName, $valuesArray));


		////////////add Usage////////////////
		//////////////THIS ONE IS NOT DONE YET //////////////
		// $tableName = 'user_usage';
		// $userId = $this->arrayOfTestUsers[array_rand($this->arrayOfTestUsers)];
		// $usageType = null;
		// $valuesArray = array($storyId, $userId);

		// $this->assertTrue($this->db->insertUpdateAll($tableName, $valuesArray));
	
		////////////recommendedStory////////////////
		////////////////////////////////////////////

		$tableName = 'story_state';
		$userId = $this->arrayOfTestUsers[array_rand($this->arrayOfTestUsers)];
		$storyId = $this->arrayOfStories[array_rand($this->arrayOfStories)];
		$valuesArray = array($storyId, $userId,1);

		$this->assertTrue($this->db->insertUpdateAll($tableName, $valuesArray));

	}

	public function testGetSelected(){

		//HOW MANY RATINGS IN COMPUTEPREFERENCEVALUES.PHP//
		$userId = 1;
		$storyId = "DF.4320";
		$tableName = 'stored_story';
		$selectColumns = 'rating';
		$whereColumns = array('storyId', 'userId');
		$whereValues = array($storyId, $userId);
		$row = $this->db->getSelected($tableName, $selectColumns, $whereColumns, $whereValues);
		// print_r("testGetSelected"."\n");
		// print_r($row);
		$this->assertInternalType("int", intval($row[0]['rating']));


		//GET ALL TAGS CONNECTED TO A USER - CONTROLLER.PHP//
		//CANNOT USE RANDOM USERID HERE//

		//ALL USERs HAS TAGS NAMES "LEST" AND "LES SENERE" CONNECTED TO IT, 
		//THIS CODE SHOULD RETURN AN ARRAY WITH THOSE TAGS  IN INDEX 0 AND 1//

		$userId = $this->arrayOfTestUsers[array_rand($this->arrayOfTestUsers)];
		$tableName = 'user_tag';
		$selectColumns = 'tagName'; 
		$whereColumns = array('userId');
		$whereValues = array($userId);

		$row = $this->db->getSelected($tableName, $selectColumns, $whereColumns, $whereValues);
		print_r("testGetSelected All tags :"."\n");
		print_r($userId);

		print_r($row);
		$this->assertArrayHasKey('tagName',$row[0]);
		$this->assertArrayHasKey('tagName',$row[1]);
		$this->assertEquals("Les senere", $row[0]['tagName']);
		$this->assertEquals("Lest", $row[1]['tagName']);


		//GET ALL TAGS CONNECTED TO A STORY TO A USER//
		//CANNOT USE RANDOM USERID AND STORYID HERE//
		$userId = 105;
		$storyId = 'DF.1295';
		$tableName = 'user_storytag';
		$selectColumns = 'tagName';
		$whereColumns = array('userId','storyId');
		$whereValues = array($userId, $storyId);

		$row = $this->db->getSelected($tableName, $selectColumns, $whereColumns, $whereValues);
		print_r("testGetSelected Get all tags connected to a story to a user :"."\n");
		print_r($row);
		$this->assertEquals("NyTestTag", $row[0]['tagName']);

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
		//Should be a array of all subcategory for this .
		//print_r($row);
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
<?php
require_once ('../../../personalization/computePreferenceValues.php');
require_once ('../../../database/dbUser.php');
require_once ('../../../models/userModel.php');

class computePreferenceValuesTest extends PHPUnit_Framework_TestCase{

	public $compute;
	public $userId;
	public $categories;
	public $user;
	public $dbuser;
	public $dbStory;

	public function setup(){
		$this->dbuser = new dbUser();
		$this->dbstory = new dbstory();
		//array of users with category preferences
		$this->arrayOfUsers = array(1,2,3,5,6,7,8,9,10,11,12,13,14,15,16,17,20,23,24,27,28,33,34,37,38,43,52,88,89,93,98,99,105,106,109,111,112,113,114,115,117,127,129,130,161,162,166,176,200);
		
		$this->userId = $this->arrayOfUsers[array_rand($this->arrayOfUsers)];

		//Making a userModel for this random user
		$row = $this->dbuser->getUserFromId($this->userId);
		if($row[2] !=null){
			$this->categories = array_map('intval',explode(',',$row[2]['categories']));
		}else{
			$this->categories = null;
		}
		$this->user= new userModel;
		$this->user->addUser($this->userId, $row[1]['mail']);
		$this->user->addUserValues($row[1]['mail'],$row[1]['age_group'], $row[1]['gender'],$row[1]['use_of_location'],$this->categories);
		//making an instance of the class to be tested
		$this->compute = new computePreferenceValues($this->user);
		
		//Array of stories
		$this->arrayOfStories = array('DF.1115','DF.1160','DF.1295','DF.1375','DF.1600','DF.1501',
			'DF.1547','DF.1812','DF.1813','DF.1815','DF.5230','DF.5247','DF.5278','DF.5504','DF.5559',
			'DF.5669','DF.5670','DF.5672','DF.5673','DF.5674','DF.5675','DF.5702','DF.5709','DF.5712',
			'DF.5716','DF.5717','DF.5747','DF.5861','DF.5886','DF.5905','DF.6028','DF.6029','DF.6030');

	}
	public function testComputeAllValues(){

		////////Testing computeAllValues///////////////////
		///////////

		//assume that this function only runs when ////
		//a user has chosen some categories ////
		if($this->categories != null){
		 	$this->compute->computeAllValues();
		
		////check if insert worked by using db request
		$rows = $this->dbuser->getSelected('preference_value',array('userId','storyId', 'numericalId', 'preferenceValue'), 'userId', $this->userId);
		 	//print_r("Resultat fra db-spørring");
		 	//print_r($rows);
		 	//Test that it has 167 story and returns the right array objects
		 	$this->assertEquals(167,count($rows));
		 	$this->assertArrayHasKey('storyId',$rows[0]);
		 	$this->assertArrayHasKey('userId',$rows[0]);
		 	$this->assertArrayHasKey('numericalId',$rows[0]);
		 	$this->assertArrayHasKey('preferenceValue',$rows[0]);
		}
	}

	public function testComputeOneValue(){
		//assume that this function only is called  ////
		//from computeAllValues ////
		$storyModel = new storyModel();
		$storyModel->setstoryId('DF.1098');
		$storyModel->setCategoryList("4,5,9");
		$storyModel->setNumericalId(substr('DF.1098',3));		
		$calledFromComputeAllValues = true;
		$result = array();
		$result[] = $this->compute->computeOneValue($storyModel, $calledFromComputeAllValues);
		//print_r("Result from testComputeOneValue"."\n");
		//print_r("\n");
		$resultArray = explode(',',$result[0]);

		$this->assertInternalType("int",intval($resultArray[0]));
		$this->assertInternalType("string",$resultArray[1]);
		//check if this is an DF-ID. 
		$this->assertEquals("DF.",substr($resultArray[1],0,3));
		$this->assertInternalType("int",intval($resultArray[2]));
		$this->assertInternalType("double",floatval($resultArray[2]));
	}


	public function testComputePreferenceValue(){
		//this is runned from computeOneValue
		$stories = $this->dbstory->getStories();
		//print_r($stories);
		$storyModel = new storyModel();
		$storyModel->setstoryId('DF.1098');
		$storyModel->setCategoryList("4,5,9");

		$storyModel->setNumericalId(substr($storyModel->getstoryId(),3));
		$rating= $this->dbstory->getSelected('stored_story', 'rating', array('storyId', 'userId'),array($storyModel->getstoryId(), $this->user->getUserId()));
		$storyModel->setRating($rating[0]['rating']);
		$preferenceValue = new preferenceValue($storyModel, $this->user);
		$result = $this->compute->computePreferenceValue($preferenceValue);
		$this->assertInternalType("double", floatval($result));

	}


}
?>
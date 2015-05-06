<?php
require_once ('../../../database/dbUser.php');
require_once ('../../../models/userModel.php');
require_once('../../../personalization/runRecommender.php');
class computePreferenceValuesTest extends PHPUnit_Framework_TestCase{
	public $run;
	public $user;
	public $dbuser;
	public function setup(){
		$this->dbuser = new dbUser();

		//Array of userId who have chosen categories//
		$this->arrayOfUsers = array(1,2,3,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,23,24,27,28,33,34,37,38,43,52,88,89,93,98,99,105,106,109,111,112,113,114,115,117,127,129,130,161,162,166,176,200);
		
		//Choosing a random userID from the array
		$this->userId = $this->arrayOfUsers[array_rand($this->arrayOfUsers)];

		//Fetching a user from the database.
		$row = $this->dbuser->getUserFromId($this->userId);
		
		if($row[2] !=null){
			$this->categories = array_map('intval',explode(',',$row[2]['categories']));
		}else{
			$this->categories = null;
		}

		$this->user= new userModel;
		$this->user->addUser($this->userId, $row[1]['mail']);
		$this->user->addUserValues($row[1]['mail'],$row[1]['age_group'], $row[1]['gender'],$row[1]['use_of_location'],$this->categories);

		$this->run = new runRecommender($this->user);

	}


	public function testRunRecommender(){
		$result = $this->run->runRecommender();
		print_r("Result from run recommender: "."\n");
		print_r($result);

		
	}

}
?>
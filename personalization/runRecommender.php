<?php
require_once('computePreferenceValues.php');
require_once('../database/dbUser.php');
require_once('../models/userModel.php');
class runRecommender {

	private $user;
	private $method;
	private $db;

	public function __construct($user){
		$this->user = $user;
		$this->findMethod();
		$cpv = new computePreferenceValues($user);
		$cpv->computeAllValues();
	}

	public function findMethod(){
		//TODO: find out what type of recommendation to run
		$this->db = new dbUser();
		$numberOfUsers = $this->db->getNumberOfUsers();
		$numberOfRates = $this->db->getNumberOfRatedStories($this->user->getUserId());
		$numberOfRatesByThisUser = $this->db->getNumberOfRatedStoriesByThisUser($this->user->getUserId());
		print_r($numberOfRatesByThisUser);
		/*If the number of users are above 5, the number of rated done by this user is above 10 and the number of rates rates done by other users is above 15 (These values can be changed)*/
		if($numberOfUsers > 5 AND $numberOfRates > 15 AND $numberOfRatesByThisUser > 10){
			$this->method = 'collaborative';
			//print_r("Run collaborative");
		}
		$this->method = 'content';
	}
	
	public function getUser(){
		return $this->user;
	}
	
	public function getMethod(){
		return $this->method;
	}
	
	public function runRecommender(){	
		$output = shell_exec("java -jar ../java/recommender/recommender.jar ".$this->getUser()->getUserId()." ".$this->getMethod()." 2>&1");
		return $output;
	}
}

?>

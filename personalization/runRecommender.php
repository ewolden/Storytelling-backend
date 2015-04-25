<?php
require_once(__DIR__.'/computePreferenceValues.php');
require_once(__DIR__.'/../database/dbUser.php');
require_once(__DIR__.'/../models/userModel.php');
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
		//print_r($numberOfRatesByThisUser);
		/*If there is more than ten stories rated by more than 10 people shared by this user their other recommendations are valid*/
		if($this->db->getNumRatedStoriesShared($this->user->getUserId()) >= 10){
			$this->method = 'collaborative';
		} else{
			$this->method = 'content';
		}
		
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

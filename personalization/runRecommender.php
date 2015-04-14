<?php
//Including these just for testing
require_once '../models/userModel.php';
require_once 'computePreferenceValues.php';

class runRecommender {

	private $userId;
	private $method;

	public function __construct($userId){
		$this->userId = $userId;
		$this->findMethod();
	}

	public function findMethod(){
		//TODO: find out what type of recommendation to run
		$this->method = 'content';
	}
	
	public function getUserId(){
		return $this->userId;
	}
	
	public function getMethod(){
		return $this->method;
	}
	
	public function runRecommender(){		
		echo shell_exec("java -jar ../java/recommender/recommender.jar ".$this->getUserId()." ".$this->getMethod());
	}
}
//For testing
$start = microtime(true);
$user = new userModel();
$user->addUserValues(1, null, null, null, null, array(5,4,8));
$c = new computePreferenceValues($user);
$c->computeAllValues();
$recommend = new runRecommender($user->getUserId());
$recommend->runRecommender();
$elapsed = microtime(true)-$start;
print_r("Time that has passed: ");
print_r($elapsed);
?>
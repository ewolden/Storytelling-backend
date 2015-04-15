<?php
require_once('computePreferenceValues.php');
class runRecommender {

	private $user;
	private $method;

	public function __construct($user){
		$this->user = $user;
		$this->findMethod();
		$cpv = new computePreferenceValues($user);
		$cpv->computeAllValues();
	}

	public function findMethod(){
		//TODO: find out what type of recommendation to run
		$this->method = 'content';
	}
	
	public function getUser(){
		return $this->user;
	}
	
	public function getMethod(){
		return $this->method;
	}
	
	public function runRecommender(){		
		shell_exec("java -jar ../java/recommender/recommender.jar ".$this->getUser()->getUserId()." ".$this->getMethod()."");
	}
}
?>
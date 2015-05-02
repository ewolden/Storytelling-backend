<?php

/*Contributors: Kjersti Fagerholt, Roar Gj�vaag, Ragnhild Krogh, Espen Str�mjordet,
 Audun S�ther, Hanne Marie Trelease, Eivind Halm�y Wolden

 "Copyright 2015 The TAG CLOUD/SINTEF project

 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at

 http://www.apache.org/licenses/LICENSE-2.0

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License."
 */

require_once(__DIR__.'/computePreferenceValues.php');
require_once(__DIR__.'/../database/dbUser.php');
require_once(__DIR__.'/../models/userModel.php');

class runRecommender {

	private $user;
	private $method;
	private $db;
	/*If we are adding recommendations to the existing ones, this will be "true", otherwise "false"
	Both "true" and "false" are string-values, not boolean */
	private $add;

	public function __construct($user){
		$this->user = $user;
		/*By default, we are creating brand new recommendations. If we should recommend
		stories not in the recommendation view at front end, the setAdd-method needs to be called*/
		$this->add = "false";
		$this->findMethod();
		$cpv = new computePreferenceValues($user);
		$cpv->computeAllValues();
	}

	/**
	 * Find out which recommendation type to run, collaborative or content-based.
	 */
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
	
	public function setAdd($add){
		$this->add = $add;
	}
	
	public function getAdd(){
		return $this->add;
	}
	
	public function getUser(){
		return $this->user;
	}
	
	public function getMethod(){
		return $this->method;
	}
	
	public function runRecommender(){	
		$output = shell_exec("java -jar ../java/recommender/recommender.jar ".$this->getUser()->getUserId()." ".$this->getMethod()." ".$this->getAdd()." 2>&1");
		return $output;
	}
}

?>

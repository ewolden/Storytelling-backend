<?php
/*Contributors: Kjersti Fagerholt, Roar Gjøvaag, Ragnhild Krogh, Espen Strømjordet,
 Audun Sæther, Hanne Marie Trelease, Eivind Halmøy Wolden

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

class PreferenceValue {

	/*Should be a userModel-instance*/
	private $user;
	/*Should be a storyModel-instance*/
	private $story;
	
	private $numRecommended;
	private $numRead;
	private $numRated;
	private $numToBeRead;
	private $numSwipedPast;	
	private $numCommonCategories;

	public function __construct($story, $user){
		$this->story = $story;
		$this->user = $user;
	}
	
	public function setUser($user){
		$this->user = $user;
	}
	
	public function setStory($story){
		$this->story = $story;
	}
	
	public function setNumRecommended($numRecommended){
		$this->numRecommended = $numRecommended;
	}
	
	public function setNumRead($numRead){
		$this->numRead= $numRead;
	}
	
	public function setNumRated($numRated){
		$this->numRated = $numRated;
	}
	
	public function setNumToBeRead($numToBeRead){
		$this->numToBeRead = $numToBeRead;
	}
	
	public function setNumSwipedPast($numSwipedPast){
		$this->numSwipedPast = $numSwipedPast;
	}
		
	public function getUser(){
		return $this->user;
	}
	
	public function getStory(){
		return $this->story;
	}
	
	public function getNumRecommended(){
		return $this->numRecommended;
	}
	
	public function getNumRead(){
		return $this->numRead;
	}
	
	public function getNumRated(){
		return $this->numRated;
	}
	
	public function getNumToBeRead(){
		return $this->numToBeRead;
	}
	
	public function getNumSwipedPast(){
		return $this->numSwipedPast;
	}
	
	public function getNumCommonCategories(){
		$userCat = $this->getUser()->getCategoryPrefs();
		$storyCat = explode(',',$this->getStory()->getCategoryList());
		$common = array_intersect($userCat, $storyCat);
		return sizeof($common);
	}
	
	public function getCommonCategoryPercentage(){
		if (sizeof($this->getStory()->getCategoryList()) == 0){
			return 0;
		}
		return $this->getNumCommonCategories()/sizeof(explode(',',$this->getStory()->getCategoryList()));
	}
	
	/*If the story has not been rated, set the rating to 2.5
	* This means that a 1 or 2 star rating is considered worse than not rated at all
	* "Not interested" is considered worse than a 1 star rating, not sure if that's correct?*/
	public function getRescoredRating(){
		$actualRating = $this->getStory()->getRating();
		if($actualRating == null){
			$actualRating = 2.5;
		}	
		return $actualRating;
	}
}

?>
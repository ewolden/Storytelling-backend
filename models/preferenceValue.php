<?php

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
	private $numRejected;
	private $notInterested;
	
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
	
	public function setNumRejected($numRejected){
		$this->numRejected = $numRejected;
	}
	
	public function setNotInterested($notInterested){
		$this->notInterested = $notInterested;
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
	
	public function getNumRejected(){
		return $this->numRejected;
	}
	
	public function getNotInterested(){
		return $this->notInterested;
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
		if ($this->getNotInterested() == true){
			$actualRating = 0;
		}
		return $actualRating;
	}
}

?>
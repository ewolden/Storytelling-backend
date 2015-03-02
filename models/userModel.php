<?php
class userModel{
	private $userId;
	private $email;
	private $age_group;
	private $gender;
	private $user_of_location;
	
	
	//SETTERS
	 public function setUserId($userId){
        $this->userId= $userId;
    }
    
    public function setMail($email){
        $this->email = $email;
    }
    
    public function setAgeGroup($age_group){
        $this->age_group= $age_group;
    }
    
      public function setGender($gender) {
        $this->gender= $gender;
    }
    
      public function setLocation($user_of_location){
        $this->user_of_location= $user_of_location;
    }
    
	
	//GETTERS
	public function getUserId(){
        return $this->storyId;
    }
    
	public function getMail() {
        return $this->email;
    }
    
	public function getAgeGroup() {
        return $this->age_group;
    }
    
    public function getGender(){
        return $this->gender;
    }
    
	public function getLocation() {
        return $this->user_of_location;
    }
}

?>
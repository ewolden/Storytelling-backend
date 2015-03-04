<?php
class userModel{
	private $userId;
	private $email;
	private $age_group;
	private $gender;
	private $user_of_location;
    private $category_preference; //list of categories preffered
    private $media_ranking; // arry positions (0, text), (1, picture), (2, audio), (3, video)
	
    //CONSTRUCTOR
    function __construct($userId,$email,$age_group,$gender,$user_of_location,$category_preference,$media_ranking){
        $this->userId = $userId;
        $this->email = $email;
        $this->age_group = $age_group;
        $this->gender = $gender;
        $this->user_of_location = $user_of_location;
        $this->category_preference = $category_preference;
        $this->media_ranking = $media_ranking;
    }

	//SETTERS
	public function setUserId($userId)
    {
        $this->userId = $userId;
    }
    
    public function setMail($email)
    {
        $this->email = $email;
    }
    
    public function setAgeGroup($age_group)
    {
        $this->age_group = $age_group;
    }
    
    public function setGender($gender)
    {
        $this->gender = $gender;
    }
    
    public function setLocation($user_of_location)
    {
        $this->user_of_location = $user_of_location;
    }

    public function setCategoryPrefs($category_preference)
    {
        $this->category_preference = $category_preference;
    }    

    public function setMediaPrefs($media_ranking)
    {
        $this->media_ranking = $media_ranking;
    }    
	
	//GETTERS
	public function getUserId()
    {
        return $this->storyId;
    }
    
	public function getMail()
    {
        return $this->email;
    }
    
	public function getAgeGroup()
    {
        return $this->age_group;
    }
    
    public function getGender()
    {
        return $this->gender;
    }
    
	public function getLocation()
    {
        return $this->user_of_location;
    }

    public function getCategoryPrefs()
    {
        return $this->category_preference;
    }

    public function getMediaPrefs()
    {
        return $this->media_ranking;
    }

    //DATABASE
    public function uptadeUserInfo(){
        $conn = new dbHelper();
        
        /*Inserting story in story table*/
        $values = array($this->getUserId(),$this->getMail(),$this->getAgeGroup(),$this->getGender(),$this->getLocation());
        $conn->insertUpdateAll('user',$values);
        
        /*Inserting category preferences*/
        foreach($this->getCategoryPrefs() as $category){
                $conn->insertUpdateAll('category_preference', array($this->getUserId,$category)); 
            }
            
        /*Inserting media preferances*/
        $conn->insertUpdateAll('media_preference',1,$this->getMediaPrefs()[0]); //picture
        $conn->insertUpdateAll('media_preference',2,$this->getMediaPrefs()[1]); //picture
        $conn->insertUpdateAll('media_preference',3,$this->getMediaPrefs()[2]); //audio
        $conn->insertUpdateAll('media_preference',4,$this->getMediaPrefs()[3]); //video
    }
}



?>
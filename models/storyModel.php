<?php
class storyModel{
	
	private $storyId;
	private $title;
	private $creator;
	private $introduction;
	private $theStory;
	private $municipality; 
	private $county;
	private $rights;
	private $url; 
	private $institution;
	private $imageList;
	private $videoList;
	private $audioList;
	private $categoryList;
	private $subjectList;
	
	
	
	//SETTERS
	public function setstoryId()
    {
        return $this->storyId;
    }
    
	public function settitle()
    {
        return $this->title;
    }
    
	public function setCreator()
    {
        return $this->creator;
    }
    
    public function setIntroduction()
    {
        return $this->introduction;
    }
    
	public function setTheStory()
    {
        return $this->theStory;
    }   
    
    public function setMunicipality()
    {
        return $this->municipality;
    }   
    
    public function setCounty()
    {
        return $this->county;
    }
    
    public function setRights()
    {
        return $this->rights;
    }
    
    public function setUrl()
    {
        return $this->url;
    }
    
    public function setInstitution()
    {
        return $this->institution;
    }
    
    public function setImageList()
    {
        return $this->imageList;
    }
    
    public function setVideoList()
    {
        return $this->videoList;
    }
    
    public function setAudioList()
    {
        return $this->audioList;
    }
	
	public function setCategoryList()
    {
        return $this->categoryList;
    }
	}
	public function setSubjectList()
    {
        return $this->subjectList;
    }
	
	//GETTERS
	public function getstoryId()
    {
        return $this->storyId;
    }
    
	public function gettitle()
    {
        return $this->title;
    }
    
	public function getCreator()
    {
        return $this->creator;
    }
    
    public function getIntroduction()
    {
        return $this->introduction;
    }
    
	public function getTheStory()
    {
        return $this->theStory;
    }   
    
    public function getMunicipality()
    {
        return $this->municipality;
    }   
    
    public function getCounty()
    {
        return $this->county;
    }
    
    public function getRights()
    {
        return $this->rights;
    }
    
    public function getUrl()
    {
        return $this->url;
    }
    
    public function getInstitution()
    {
        return $this->institution;
    }
    
    public function getImageList()
    {
        return $this->imageList;
    }
    
    public function getVideoList()
    {
        return $this->videoList;
    }
    
    public function getAudioList()
    {
        return $this->audioList;
    }
	
	public function getCategoryList()
    {
        return $this->categoryList;
    }
	}
	public function getSubjectList()
    {
        return $this->subjectList;
    }



?>
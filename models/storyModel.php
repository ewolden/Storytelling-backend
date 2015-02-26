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
	public function setstoryId($storyId)
    {
        $this->storyId =$storyId;
    }
    
	public function settitle($title)
    {
        $this->title = $title;
    }
    
	public function setCreator( $creator)
    {
        $this->creator = $creator;
    }
    
    public function setIntroduction($introduction)
    {
        $this->introduction = $introduction;
    }
    
	public function setTheStory($theStory)
    {
        $this->theStory = $theStory;
    }   
    
    public function setMunicipality($municipality)
    {
    	$this->municipality = $municipality; 
    }   
    
    public function setCounty($county)
    {
        $this->county = $county;
    }
    
    public function setRights($rights)
    {
        $this->rights = $rights;
    }
    
    public function setUrl($url)
    {
        $this->url = $url;
    }
    
    public function setInstitution($institution)
    {
        $this->institution = $institution;
    }
    
    public function setImageList($imageList)
    {
        $this->imageList = $imageList;
    }
    
    public function setVideoList($videoList)
    {
        $this->videoList = $videoList;
    }
    
    public function setAudioList($audioList)
    
        $this->audioList =$audioList;
    }
	
	public function setCategoryList($catergoryList)
    {
        $this->categoryList = $catergoryList;
    }
	
	
	public function setSubjectList($subjectList)
    {
        $this->subjectList = $subjectList;
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

	public function getAll(){
		return $this->storyId + $this->storyId + $this->title+ $this->creator+ $this->introduction+
		$this->theStory+ $this->municipality+$this->county+$this->rights+$this->url+
		$this->institution+ $this->imageList+$this->videoList+$this->audioList+$this->categoryList+
		$this->subjectList;
	}
	
	public function sendStory(){
		$data = get_class($this).getAll();
		echo json_encode($data);
		return json_encode($data);
}

	
?>
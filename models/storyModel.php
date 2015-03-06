<?php
include '../database/dbhelper.php';
class storyModel{
    private $storyId;
    private $title;
    private $creatorList;
    private $introduction;
    private $theStory;
    private $municipality; 
    private $county;
    private $rights;
    private $institution;
    private $imageList;
    private $videoList;
    private $audioList;
    private $subCategoryList;
    private $subCategoryNames;
    private $subjectList;
    private $url;
    private $conn;

    //Constructor
    public function getFromDF($id)
    {

        $xml_from_API = $this->file_get_contents_utf8('http://api.digitaltmuseum.no/artifact?owner=H-DF&identifier='.$id.'&mapping=ABM&api.key=demo');
        $xml = simplexml_load_string($xml_from_API);
        
        $this->storyId = $id;
        $this->title = (string) $xml->children('dc', TRUE)->title;
        $this->introduction = (string) $xml->children('abm', TRUE)->introduction;
        $this->theStory = (string) $xml->children('dc', TRUE)->description;
        $this->county = (string) $xml->children('abm', TRUE)->county;
        $this->municipality = (string) $xml->children('abm', TRUE)->municipality;
        $this->rights = (string) $xml->children('dc', TRUE)->rights;
        $this->institution = (string) $xml->children('europeana', TRUE)->dataProvider;

        //Create a list of all creators for the story
        foreach ($xml->children('dc', TRUE)->creator as $element)
        {
            $this->creatorList[] = (string) $element;
        }
        //Create a list of all image IDs for the story
        foreach ($xml->children('abm', TRUE)->image as $element) 
        {
            preg_match('/\/\d{1,5}/',(string) $element->children('abm', TRUE)->imageUri,$match); //selects image ID from the URL
            $this->imageList[] = substr($match[0],1);
        }
        //Create a list of all video URLs for the story
        foreach ($xml->children('abm', TRUE)->media as $element)
        {
            $this->videoList[] = (string) $element->children('abm', TRUE)->videoUri;
        }
        //Create a list of all audio URLs for the story
        foreach ($xml->children('abm', TRUE)->media as $element)
        {
            $this->audioList[] = (string) $element->children('abm', TRUE)->soundUri;
        }
        //Create a list of all category IDs for the story
        foreach ($xml->children('abm', TRUE)->classification as $element)
        {
            preg_match('/\d+/',(string) $element,$match); //Selects only the numerical category name
            $this->subCategoryList[] = $match[0];
            $name = substr((string) $element, 0, strpos((string) $element, '('));
            $this->subCategoryNames[] = strtolower((string) $name);
        }
        //Create a list of all subjects for the story        
        foreach ($xml->children('dc', TRUE)->subject as $element)
        {
            $this->subjectList[] = (string) $element;
        }
    }

    //SETTERS
    public function setstoryId($storyId)
    {
        $this->storyId =$storyId;
    }
    
    public function settitle($title)
    {
        $this->title = $title;
    }
    
    public function setCreatorList( $creatorList)
    {
        $this->creatorList = $creatorList;
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
    {
        $this->audioList =$audioList;
    }

    public function setsubCategoryList($catergoryList)
    {
        $this->subCategoryList = $catergoryList;
    }
    
    public function setsubCategoryNames($subCategoryNames)
    {
        $this->subCategoryNames = $subCategoryNames;
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
    
    public function getCreatorList()
    {
        return $this->creatorList;
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
    public function getSubjectList()
    {
        return $this->subjectList;
    }
    public function getsubCategoryList()
    {
        return $this->subCategoryList;
    }
    public function getsubCategoryNames()
    {
        return $this->subCategoryNames;
    }
    public function getSubjectList()
    {
        return $this->subjectList;
    }

    public function getAll(){
      return getstoryId() + gettitle() + getCreator() + 
      getIntroduction() + getTheStory() + getMunicipality() + 
      getCounty() + getRights() + getUrl() + getInstitution() + 
      getImageList() + getVideoList() + getAudioList() + getSubjectList();
    }

    public function sendStory(){
      $data = get_class($this).getAll();
      echo json_encode($data);
      return json_encode($data);
    }

    //Helper functions
    public function print_all_info(){
        print_r('Story ID - '.$this->storyId.PHP_EOL.PHP_EOL);
        print_r('Title- '.$this->title.PHP_EOL.PHP_EOL);
        print_r('Creators- ');
        print_r($this->creatorList);
        print_r(PHP_EOL.PHP_EOL);
        print_r('Introduction- '.$this->introduction.PHP_EOL.PHP_EOL);
        print_r('The Story - '.$this->theStory.PHP_EOL.PHP_EOL);
        print_r('Municipality - '.$this->municipality.PHP_EOL.PHP_EOL);
        print_r('County - '.$this->county.PHP_EOL.PHP_EOL);
        print_r('Rights - '.$this->rights.PHP_EOL.PHP_EOL);
        print_r('Institution - '.$this->institution.PHP_EOL.PHP_EOL);
        print_r('ImageID List - ');
        print_r($this->imageList);
        print_r(PHP_EOL.PHP_EOL);
        print_r('VideoURL List - ');
        print_r($this->videoList);
        print_r(PHP_EOL.PHP_EOL);
        print_r('AudioURL List - ');
        print_r($this->audioList);
        print_r(PHP_EOL.PHP_EOL);
        print_r('Category List - ');
        print_r($this->subCategoryList);
        print_r(PHP_EOL.PHP_EOL);
        print_r('Subject List - ');
        print_r($this->subjectList);
        print_r(PHP_EOL.PHP_EOL);
    }

    private function file_get_contents_utf8($fn) { //needed because the normal file_get_contents is not unicode
        $content = file_get_contents($fn);
        return mb_convert_encoding($content, 'UTF-8',
           mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
    }
    
}

//$story = new storyModel(); //example usage
//$story->getFromDF('DF.5736');
//$story->print_all_info();

?>
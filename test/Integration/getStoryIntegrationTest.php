<?php
require_once '../../database/dbStory.php';
require_once '../../models/userModel.php';

class getStoryIntegrationTest extends PHPUnit_Framework_TestCase{

	public $dbUser;
	public $userId;
	public $storyId;
	public function setUp(){
		$this->dbStory = new dbStory();
	}

	public function testgetStories(){
		//GET RECOMMENDED STORIES FOR THIS USER
		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'getStories', 'userId'=>105));
 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
 
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postarray);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
 
		$response = curl_exec($ch);
		//print_r("\n".$response);
		curl_close($ch);
		
		//TESTING
		//Does it return stories? 
		$data = json_decode($response);
		//print_r("Result from testGetStories: ");
		//print_r($response);
		//Test if the data contains 10 objects as it should.

		$this->assertCount(10,$data);

		//Looping trough all the stories

		for ($i=0; $i <10 ; $i++) { 
			$id = $data[$i]->id;
			//print_r("\n".$id);
			//Test if every instance include a ID, as it should

			$this->assertEquals("DF",substr($id,0,2));
			$this->assertEquals(".", substr($id,2,1));
			$this->assertInternalType("int", intval(substr($id,4,7)));

			//Test if all the stories contains the array elements they should

			$this->assertObjectHasAttribute('id',$data[$i]);
			$this->assertObjectHasAttribute('title',$data[$i]);
			$this->assertObjectHasAttribute('description',$data[$i]);
			$this->assertObjectHasAttribute('false_recommend',$data[$i]);
			$this->assertObjectHasAttribute('explanation',$data[$i]);
			$this->assertObjectHasAttribute('picture',$data[$i]);
			$this->assertObjectHasAttribute('thumbnail',$data[$i]);
			$this->assertObjectHasAttribute('categories',$data[$i]);
			$this->assertObjectHasAttribute('mediaType',$data[$i]);
			$this->assertObjectHasAttribute('author',$data[$i]);
			$this->assertObjectHasAttribute('date',$data[$i]);

		}


	}

	public function testGetStory(){
		//GET ONE SPECIFIC STORY
		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'getStory','storyId'=>'DF.5221', 'userId'=>105));
 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
 
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postarray);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
 
		$response = curl_exec($ch);
		//print_r("\n".$response);
		curl_close($ch);
		
		//TESTING
		
		$data = json_decode($response);	
		$storyId = $data->storyId; 
		//print_r("\n"."Results from testGetStory:");
		//print_r($data);
		//Test that the repsonse include a story id as it should every time
		$this->assertEquals($storyId,"DF.5221");
		//Test that the story has a valid url as it should every time
		$url = $data->url;
		$this->assertNotFalse(filter_var($url, FILTER_VALIDATE_URL));

		//Test that the story has all the Attributes it should have
		$this->assertObjectHasAttribute('storyId',$data);
		$this->assertObjectHasAttribute('title',$data);
		$this->assertObjectHasAttribute('creatorList',$data);
		$this->assertObjectHasAttribute('introduction',$data);
		$this->assertObjectHasAttribute('theStory',$data);
		$this->assertObjectHasAttribute('municipality',$data);
		$this->assertObjectHasAttribute('county',$data);
		$this->assertObjectHasAttribute('rights',$data);
		$this->assertObjectHasAttribute('institution',$data);
		$this->assertObjectHasAttribute('imageList',$data);
		$this->assertObjectHasAttribute('videoList',$data);
		$this->assertObjectHasAttribute('audioList',$data);
		$this->assertObjectHasAttribute('subCategoryNames',$data);
		$this->assertObjectHasAttribute('url',$data);
		$this->assertObjectHasAttribute('rating',$data);
		$this->assertObjectHasAttribute('categoryList',$data);
		$this->assertObjectHasAttribute('typeOfRecommendation',$data);
		$this->assertObjectHasAttribute('explanation',$data);
		$this->assertObjectHasAttribute('falseRecommend',$data);
		$this->assertObjectHasAttribute('userTags',$data);

	}

	public function testGetMoreRecommendations(){
		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'getMoreRecommendations','userId'=>105));
 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
 
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postarray);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
 
		$response = curl_exec($ch);
		//print_r("\n".$response);
		curl_close($ch);
		
		//TESTING
		
		$data = json_decode($response);	
		print_r($data);

		$this->assertCount(10,$data);

		//Looping trough all the stories

		for ($i=0; $i <10 ; $i++) { 
			$id = $data[$i]->id;
			//print_r("\n".$id);
			//Test if every instance include a ID, as it should

			$this->assertEquals("DF",substr($id,0,2));
			$this->assertEquals(".", substr($id,2,1));
			$this->assertInternalType("int", intval(substr($id,4,7)));

			//Test if all the stories contains the array elements they should

			$this->assertObjectHasAttribute('id',$data[$i]);
			$this->assertObjectHasAttribute('title',$data[$i]);
			$this->assertObjectHasAttribute('description',$data[$i]);
			$this->assertObjectHasAttribute('false_recommend',$data[$i]);
			$this->assertObjectHasAttribute('explanation',$data[$i]);
			$this->assertObjectHasAttribute('picture',$data[$i]);
			$this->assertObjectHasAttribute('thumbnail',$data[$i]);
			$this->assertObjectHasAttribute('categories',$data[$i]);
			$this->assertObjectHasAttribute('mediaType',$data[$i]);
			$this->assertObjectHasAttribute('author',$data[$i]);
			$this->assertObjectHasAttribute('date',$data[$i]);

		}
	}

	public function testRecommendedStory(){
		//TEST SET STORY AS RECOMMENDED 
		$this->userId = 105;
		$this->storyId = 'DF.1901';
		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'recommendedStory','userId'=>$this->userId, 'storyId'=>$this->storyId));
 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
 
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postarray);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
 
		$response = curl_exec($ch);
		//print_r("\n".$response);
		curl_close($ch);
		
		//TESTING??? 

	}
	
}
?>	
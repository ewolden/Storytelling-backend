<?php
require_once '../../database/dbStory.php';
require_once '../../models/userModel.php';

class getStoryIntegrationTest extends PHPUnit_Framework_TestCase{

	public $dbUser;
	public function setUp(){
		$this->dbStory = new dbStory();
	}

	public function testgetStories(){
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
		//Looping trough all the ids, check if they actually are Digitalt Fortalt Ids.
		print_r("Result from testGetStories: ");
		
		for ($i=0; $i <10 ; $i++) { 
			$id = $data[$i]->id;
			print_r("\n".$id);
			$this->assertEquals("DF",substr($id,0,2));
			$this->assertEquals(".", substr($id,2,1));
			$this->assertInternalType("int", intval(substr($id,4,7)));
		}
		//Test if the data contains 10 objects.
		$this->assertCount(10,$data);
	}

	public function testGetStory(){
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
		print_r("\n"."Results from testGetStory:");
		print_r("\n".$data->storyId);
		//check that the repsonse include a story id as it should every time
		$this->assertEquals($storyId,"DF.5221");
		//Check that the story has a valid url as it should every time
		$url = $data->url;
		$this->assertNotFalse(filter_var($url, FILTER_VALIDATE_URL));

	}
}
?>	
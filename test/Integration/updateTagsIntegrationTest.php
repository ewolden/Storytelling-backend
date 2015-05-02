<?php
require_once '../../database/dbUser.php';
require_once '../../models/userModel.php';

class updateRatingsIntegrationTest extends PHPUnit_Framework_TestCase{

	public $dbUser;
	public function setUp(){
		$this->dbUser = new dbUser();
	}

	public function testAddNewTag(){
		//http request for adding a new Tag with name newTag

		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'addNewTag','userId'=>105,'tagName'=>'newTag', 'storyId'=>'DF.5221'));
 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
 
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postarray);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
 
		$response = curl_exec($ch);
		print_r("Result of testAddNewTag: ");
		curl_close($ch);
		$data = json_decode($response,true);

		//TEST IF THE DATABASE HAVE STORIED THESE

	}
	public function testTagStory(){
		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'tagStory','userId'=>105,'storyId'=>'DF.3283','tagName'=>'newTag'));
 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
 
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postarray);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
 
		$response = curl_exec($ch);
		curl_close($ch);


		$data = json_decode($response,true);
		print_r($data);

		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'getList','userId'=>105, 'tagName'=>'newTag'));
 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
 
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postarray);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
 
		$response = curl_exec($ch);
		curl_close($ch);
		print_r("\n"."Result from Get List :"."\n");
		$data = json_decode($response,true);
		print_r($data);
		//The last element added to the list is first in the list, therefore $data[0]
		//Checks that the story is put in the list of tags
		$this->assertEquals('DF.3283',$data[0]['id']);
		
		//Checks if every array with story includes the right keys
		for ($i=0; $i < count($data); $i++) { 
			$this->assertArrayHasKey('id',$data[$i]);
			$this->assertArrayHasKey('title',$data[$i]);
			$this->assertArrayHasKey('description',$data[$i]);	
			$this->assertArrayHasKey('false_recommend',$data[$i]);
			$this->assertArrayHasKey('explanation',$data[$i]);
			$this->assertArrayHasKey('picture',$data[$i]);
			$this->assertArrayHasKey('thumbnail',$data[$i]);
			$this->assertArrayHasKey('categories',$data[$i]);
			$this->assertArrayHasKey('mediaType',$data[$i]);
			$this->assertArrayHasKey('author',$data[$i]);
			$this->assertArrayHasKey('date',$data[$i]);	
		}
		


	}

	public function testAddExsistingTag(){
		//Http request for adding a Tag with name Les senere
		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'tagStory','userId'=>105,'storyId'=>'DF.5223','tagName'=>'Les senere'));
 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
 
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postarray);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
 
		$response = curl_exec($ch);
		print_r("Result of testAddExsistingTag");
		curl_close($ch);
		$data = json_decode($response,true);
		print_r($data);

		
		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'getList','userId'=>105, 'tagName'=>'newTag'));
 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
 
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postarray);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
 
		$response = curl_exec($ch);
		curl_close($ch);
		print_r("\n"."Result from Get List :"."\n");
		$data = json_decode($response,true);
		print_r($data);


	}

	public function testRemoveTag(){
		//Make a tag we can delete later
		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'addNewTag','userId'=>105,'tagName'=>'newTag', 'storyId'=>'DF.6081'));
 
 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
 
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postarray);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
 
		$response = curl_exec($ch);
		print_r("\n"."Result of testRemoveTag");
		print_r("\n".$response);
		curl_close($ch);
		$data = json_decode($response,true);
		print_r($data);


		//Remove tag we created earlier
		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'removeTagStory','userId'=>105,'storyId'=>'DF.6081','tagName'=>'newTag'));
 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
 
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postarray);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
 
		$response = curl_exec($ch);
		curl_close($ch);
		
		$data = json_decode($response,true);
		print_r($data);

	}


}
?>	
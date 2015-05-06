<?php
//REMEMBER TO UPDATE USERID BEFORE TESTING

require_once '../../database/dbUser.php';
require_once '../../models/userModel.php';
require_once '../../database/dbStory.php';


class updateRatingsIntegrationTest extends PHPUnit_Framework_TestCase{

	public $dbUser;
	public $userId;
	public $tagName;
	public $storyId;
	public $numberOfTags;
	public function setUp(){
		$this->dbUser = new dbUser();
		$this->dbstory = new dbStory();
		$this->userId = 102;
		$this->tagName = "newTag1";

	}

	public function testAddNewTag(){
		//create a new user 
		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'addUser','email' => null));
 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
 
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postarray);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
 
		$response = curl_exec($ch);
		print_r("\n".$response);
		curl_close($ch);

		$data = json_decode($response,true);
		$this->userId = $data['userId'];

		//response message should be sucessful
		$this->assertEquals("sucessfull", $data['status']);
		//http request for adding a new Tag with name newTag
		
		//Create new tag for the new user

		$storyId = 'DF.5221';
		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'addNewTag','userId'=>$this->userId,'tagName'=>$this->tagName, 'storyId'=>$this->storyId));
 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
 
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postarray);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
 
		$response = curl_exec($ch);
		curl_close($ch);

		$data = json_decode($response,true);
		print_r($response);


		//TEST IF THE DATABASE HAVE STORIED THESE

		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'getList','userId'=>$this->userId, 'tagName'=>$this->tagName));
 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
 
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postarray);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
 
		$response = curl_exec($ch);
		curl_close($ch);
		print_r("\n"."Result from add new tag get list :"."\n");
		$data = json_decode($response,true);
		print_r($data);

		$this->assertEquals($this->storyId, $data[0]['id']);

		// $result = $this->dbstory->getStoryList($this->userId, $this->tagName);
		
		// print_r("Result of testAddNewTag: ");
		// print_r($result);

		// //TEST IF THE ARRAY CONTAINS THE STORY WE STORED, SHOULD BE THE ONLY STORY IN THIS LIST
		// $this->assertEquals($this->storyId, $result[0]['id']);

		// for ($i=0; $i < count($result); $i++) { 
		// 	$this->assertArrayHasKey("id", $result[$i]);
		// 	$this->assertArrayHasKey("title", $result[$i]);
		// 	$this->assertArrayHasKey("author", $result[$i]);
		// 	$this->assertArrayHasKey("introduction", $result[$i]);
		// 	$this->assertArrayHasKey("date", $result[$i]);
		// 	$this->assertArrayHasKey("tagName", $result[$i]);
		// 	// $this->assertEquals("newTag",$result[])
		// 	$this->assertArrayHasKey("categories", $result[$i]);
		// 	$this->assertArrayHasKey("mediaId", $result[$i]);
		//}

	}
	public function testTagStory(){
		$this->storyId = 'DF.3283';
		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'tagStory','userId'=>$this->userId,'storyId'=>$this->storyId,'tagName'=>$this->tagName));
 
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
		$postarray = json_encode(array('type'=>'getList','userId'=>$this->userId, 'tagName'=>$this->tagName));
 
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
		$this->assertEquals($this->storyId,$data[0]['id']);
		
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
		$this->storyId = 'DF.5223';
		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'tagStory','userId'=>$this->userId,'storyId'=>$this->storyId,'tagName'=>'Les senere'));
 
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
		$postarray = json_encode(array('type'=>'getList','userId'=>$this->userId, 'tagName'=>'newTag'));
 
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

		//CHECK IF THE TAG IS IN THE LIST 



	}

	public function testRemoveTag(){
		//Make a tag we can delete later
		$this->userId = 105;
		$this->tagName = 'tagToBeRemoved';
		$this->storyId = 'DF.6081';
		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'addNewTag','userId'=>$this->userId,'tagName'=>$this->tagName, 'storyId'=>$this->storyId));
 
 
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

		//CHECK IF TAG IS SAVED

		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'getAllLists','userId'=>$this->userId));
 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
 
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postarray);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
 
		$response = curl_exec($ch);
		curl_close($ch);
		print_r("\n"."Result from add testRemoveTag :"."\n");
		$data = json_decode($response,true);
		print_r($data);

		$this->numberOfTags = count($data);


		//Remove tag we created earlier
		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'removeTag','userId'=>$this->userId,'storyId'=>$this->storyId,'tagName'=>$this->tagName));
 
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

		//CHECK IF TAG REMOVED 

		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'getAllLists','userId'=>$this->userId, 'tagName'=>$this->tagName));
 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
 
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postarray);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
 
		$response = curl_exec($ch);
		curl_close($ch);
		print_r("\n"."check if tag is removed :"."\n");
		$data = json_decode($response,true);
		print_r($data);

		//TEST IF THE LAST ELEMENT OF THE LIST HAVE BEEN REMOVED	
		$this->assertEquals($this->numberOfTags-1,count($data));

	}

	public function testGetList(){

		$this->userId = 105;
		$this->tagName = "Les senere";
		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'getList','userId'=>$this->userId, 'tagName'=>$this->tagName));
 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
 
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postarray);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
 
		$response = curl_exec($ch);
		curl_close($ch);
		$data = json_decode($response,true);
		print_r("TEST GET LIST: "."\n");
		print_r($data);

		//Test if this request return the array with the right elements 
		for ($i=0; $i < count($data); $i++) { 
			$this->assertArrayHasKey("id", $data[$i]);
			$this->assertArrayHasKey("title", $data[$i]);
			$this->assertArrayHasKey("description", $data[$i]);
			$this->assertArrayHasKey("false_recommend", $data[$i]);
			$this->assertArrayHasKey("explanation", $data[$i]);
			$this->assertArrayHasKey("picture", $data[$i]);
			$this->assertArrayHasKey("thumbnail", $data[$i]);
			$this->assertArrayHasKey("categories", $data[$i]);
			$this->assertArrayHasKey("mediaType", $data[$i]);
			$this->assertArrayHasKey("author", $data[$i]);
			$this->assertArrayHasKey("date", $data[$i]);
		}
	}

	public function testGetAllLists(){
		$this->userId = 105;
		$this->tagName = "Les senere";
		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'getAllLists','userId'=>$this->userId));
 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
 
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postarray);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
 
		$response = curl_exec($ch);
		curl_close($ch);
		$data = json_decode($response,true);
		print_r("TEST GET ALL LIST: "."\n");
		print_r($data);

		for ($i=0; $i < count($data) ; $i++) { 
			$this->assertArrayHasKey("text",$data[$i]);
			$this->assertArrayHasKey("checked",$data[$i]);
		}
	}

	public function testGetStoryTags(){
		// TEST GET ALL TAGS CONNECTED TO A STORY FOR A USER 
		$this->userId = 105 ;
		$this->storyId = 'DF.1812';
		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'getStoryTags','userId'=>$this->userId,'storyId'=>$this->storyId));
 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
 
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postarray);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
 
		$response = curl_exec($ch);
		curl_close($ch);
		$data = json_decode($response,true);
		print_r("TEST GET story tags: "."\n");
		print_r($data);

		for ($i=0; $i < count($data); $i++) { 
			$this->assertArrayHasKey('text',$data[$i]);
			$this->assertArrayHasKey('checked',$data[$i]);
		}
	}

}
?>	
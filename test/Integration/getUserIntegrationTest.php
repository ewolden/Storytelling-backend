<?php
require_once '../../database/dbUser.php';
require_once '../../models/userModel.php';

class getUserIntegrationTest extends PHPUnit_Framework_TestCase{

	public $dbUser;
	public $mail;
	public $exampleuserId;
	public function setUp(){
		$this->dbUser = new dbUser();
	}

	public function testGetUserFromEmail(){
		//CREATING A NEW USER FOR THIS TEST
		//REMEMBER TO UPDATE THE EMAIL 
		$this->mail = "GetUserFromEmailTest2@bleh.com";
		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'addUser','email' => $this->mail));
 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
 
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postarray);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
 
		$response = curl_exec($ch);
		print_r("\n".$response);
		curl_close($ch);

		$data = json_decode($response, true);
		print_r("\n"."Add new user");
		print_r($data);

		$this->exampleuserId = $data['userId'];

		//GET USER FROM MAIL 
		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'getUserFromEmail', 'email'=>$this->mail));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postarray);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
 
		$response = curl_exec($ch);
		//print_r("\n".$response);
		curl_close($ch);
		
		$data = json_decode($response,true);
		print_r($data);
		
		$userId = $data['userModel']['userId'];
		$this->assertEquals($userId, $this->exampleuserId);

		$email = $data['userModel']['email'];
		$this->assertEquals($this->mail,$email);

		//Test if the array contains all the elements it should 
		if($this->assertEquals("successfull",$data['status'])){
			$this->assertArrayHasKey('userId',$data['userModel']);
			$this->assertArrayHasKey('email',$data['userModel']);
			$this->assertArrayHasKey('age_group',$data['userModel']);
			$this->assertArrayHasKey('gender',$data['userModel']);
			$this->assertArrayHasKey('use_of_location',$data['userModel']);
		}

		//Test if this is an int and that it is between 0 and 4
		$age_group = $data['userModel']['age_group'];
		$this->assertInternalType("int", intval($age_group));
		$this->assertTrue((0<= intval($age_group)) && (intval($age_group) <= 4));

		//Test if this is a boolean
		$gender = $data['userModel']['gender'];
		$this->assertInternalType("boolean", (bool)intval($gender));
		//Test if this is a boolean
		$use_of_location = $data['userModel']['use_of_location'];
		$this->assertInternalType("boolean", (bool)intval($use_of_location));

		$category_preference = $data['userModel']['category_preference'];		
		//Test if this is an array
		if($category_preference != null){
			$this->assertInternalType("array", $category_preference);
		}
	}

	public function testGetUserFromId(){
		/////////////CREATE A USER FOR THIS TEST ///////	

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

		$this->exampleuserId = $data['userId'];

		//GET USER FROM THIS ID
		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'getUserFromId', 'userId'=>$this->exampleuserId));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postarray);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
 
		$response = curl_exec($ch);
		//print_r("\n".$response);
		curl_close($ch);
		
		$data = json_decode($response,true);
		print_r($data);
		
		$userId = $data['userModel']['userId'];
		$this->assertEquals($userId, $this->exampleuserId);

		$email = $data['userModel']['email'];

		//Test if this is an int and that it is between 0 and 4
		$age_group = $data['userModel']['age_group'];
		$this->assertInternalType("int", intval($age_group));
		$this->assertTrue((0<= intval($age_group)) && (intval($age_group) <= 4));

		//Test if gender is a boolean
		$gender = $data['userModel']['gender'];
		$this->assertInternalType("boolean", (bool)intval($gender));
		//Check if use_of_location is a boolean
		$use_of_location = $data['userModel']['use_of_location'];
		$this->assertInternalType("boolean", (bool)intval($use_of_location));

		$category_preference = $data['userModel']['category_preference'];		
		//Check if category_preference is an array
		if($category_preference != null){
			$this->assertInternalType("array", $category_preference);
		}	
	}
}
?>
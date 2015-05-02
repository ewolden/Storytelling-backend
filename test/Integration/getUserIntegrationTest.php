<?php
require_once '../../database/dbUser.php';
require_once '../../models/userModel.php';

class getUserIntegrationTest extends PHPUnit_Framework_TestCase{

	public $dbUser;
	public function setUp(){
		$this->dbUser = new dbUser();
	}

	public function testGetUserFromEmail(){
		/////////////CHECK IF THE THIS STILL EXISTS IN THE DATABSE//////////////////////////
		/////////////ELSE CREATE A NEW ONE AND TEST THAT USER //////////////////////////
		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'getUserFromEmail', 'email'=>'nr2'));
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
		$this->assertEquals($userId, 2);

		$email = $data['userModel']['email'];

		//Check if this is an int and that it is between 0 and 4
		$age_group = $data['userModel']['age_group'];
		$this->assertInternalType("int", intval($age_group));
		$this->assertTrue((0<= intval($age_group)) && (intval($age_group) <= 4));

		//Check if this is a boolean
		$gender = $data['userModel']['gender'];
		$this->assertInternalType("boolean", (bool)intval($gender));
		//Check if this is a boolean
		$use_of_location = $data['userModel']['use_of_location'];
		$this->assertInternalType("boolean", (bool)intval($use_of_location));

		$category_preference = $data['userModel']['category_preference'];		
		//Check if this is an array
		$this->assertInternalType("array", $category_preference);
	}

	public function testGetUserFromId(){
		/////////////CHECK IF THE THIS STILL EXISTS IN THE DATABSE//////////////////////////
		/////////////ELSE CREATE A NEW ONE AND TEST THAT USER //////////////////////////	
		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'getUserFromId', 'userId'=>2));
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
		$this->assertEquals($userId, 2);

		$email = $data['userModel']['email'];

		//Check if this is an int and that it is between 0 and 4
		$age_group = $data['userModel']['age_group'];
		$this->assertInternalType("int", intval($age_group));
		$this->assertTrue((0<= intval($age_group)) && (intval($age_group) <= 4));

		//Check if this is a boolean
		$gender = $data['userModel']['gender'];
		$this->assertInternalType("boolean", (bool)intval($gender));
		//Check if this is a boolean
		$use_of_location = $data['userModel']['use_of_location'];
		$this->assertInternalType("boolean", (bool)intval($use_of_location));

		$category_preference = $data['userModel']['category_preference'];		
		//Check if this is an array
		$this->assertInternalType("array", $category_preference);
	}
}
?>
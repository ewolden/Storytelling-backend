<?php
require_once '../../database/dbUser.php';
require_once '../../models/userModel.php';

class LoginIntegrationTest extends PHPUnit_Framework_TestCase{
	public $newUser;
	public $dbuser;
	public $dbStory;
	public $returnedUserModel;
	public $updatedUser;

	public function setUp(){
		//TODO: simuler http requests som sendes til controller.php
		$this->dbUser = new dbUser();


	}

	public function testLoginFirstTime(){
		//Add new user with http request
		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'addUser','email' => 'testnr11'));
 
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

		//response message should be sucessful
		$this->assertEquals("sucessfull", $data['status']);

		$row = $this->dbUser->getUserFromEmail("testnr11");
		$userId = $row[1]['userId'];
		
		//check that the user got an id 
		$this->assertInternalType("int", intval($userId));

		//check that categorypref is null
		$category = $this->dbUser->getUserCategories($userId);
		$this->assertEquals(null,$category);
	}	

	public function testLoginFirstTimeNoMail(){
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

		//response message should be sucessful
		$this->assertEquals("sucessfull", $data['status']);

		$id = $data['userId'];
		//Check if the userid is int
		$this->assertInternalType("int", intval($id));

		$row = $this->dbUser->getUserFromId($id);
		$userId = $row[1]['userId'];
		//Checking id is indeed stored in DB
		$this->assertEquals($id,$userId);
		//Check that categorypref is null
		$category = $this->dbUser->getUserCategories($userId);
		$this->assertEquals(null,$category);
	}
}

?>
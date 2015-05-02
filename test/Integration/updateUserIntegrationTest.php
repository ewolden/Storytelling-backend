<?php
require_once '../../database/dbUser.php';
require_once '../../models/userModel.php';

class updateUserIntegrationTest extends PHPUnit_Framework_TestCase{

	public $dbUser;
	public function setUp(){
		$this->dbUser = new dbUser();
	}

	public function testUpdateUser(){

		//update user with http request to controller.php

		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'updateUser', 'userId'=>105, 'gender' => 1, 
				'age_group' => 0, 'email' => 'testnr5',
				'use_of_location' => null, 'category_preference' => array(3)));
 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
 
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postarray);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
 
		$response = curl_exec($ch);
		//print_r("\n".$response);
		curl_close($ch);
		
		// $data = json_decode($response,true);

		// //response message should be sucessful
		// $this->assertEquals("successful", $data['status']);
		
		$row = $this->dbUser->getUserFromId(105);
		$email = $row[1]['mail'];
		$age_group = $row[1]['age_group'];
		$gender = $row[1]['gender'];
		$categories = $row[2]['categories'];
		//print_r($row);
		$this->assertEquals($email,'testnr5');
		$this->assertEquals($age_group,0);
		$this->assertEquals($gender,1);
		$this->assertEquals($categories,3);

		//CHECK THAT RECOMMENDATION FOLLOW THIS REQUEST RESPONSE. 
		$data = json_decode($response,true);
		print_r($data['userId']);
		$newarray = explode("\n", $data['userId']);
		print_r($newarray);
		//if this array contains 16 elements we know that the response include 10 recommendedItems
		$this->assertCount(16, $newarray);

	}
	public function testUpdateEmail(){
		//adding a profile without a mail with http request
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

		//Update user mail, try to update with a email that already exist in the database 
		///REMEMBER TO PUT IN A  EMAIL THAT SOMEONE ELSE HAS, BEFORE TESTING //
	
		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'updateUser', 'userId'=>$id, 'gender' => 1, 
				'age_group' => 0, 'email' => 'testMail18',
				'use_of_location' => null, 'category_preference' => null));
 
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

		//response message should be failure because the email is used by someone else
		$this->assertEquals("failed", $data['status']);

		
		$row = $this->dbUser->getUserFromId($id);
		$email = $row[1]['mail'];
		$this->assertEquals($email,'');



		//Update user mail, a user who have no mail 
		///REMEMBER TO PUT IN A NEW EMAIL EXAMPLE BEFORE TESTING //
		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'updateUser', 'userId'=>$id, 'gender' => 1, 
				'age_group' => 0, 'email' => 'testMail17',
				'use_of_location' => null, 'category_preference' => null));
 
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

		//response message should be sucessful
		$this->assertEquals("successfull", $data['status']);

		
		$row = $this->dbUser->getUserFromId($id);
		$email = $row[1]['mail'];
		$this->assertEquals($email,'testMail16');

		
	}
}
?>	
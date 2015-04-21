<?php
require_once (__DIR__.'/../models/storyModel.php');
require_once (__DIR__.'/../database/dbUser.php');
require_once (__DIR__.'/../models/userModel.php');
class dbconnectionTest extends PHPUnit_Framework_TestCase{

	public $test;

	//tilkobling ok?

	public function setup(){
		$this->db = New dbUser();

		//create user
		$this->firstUser = New userModel(55,"hi@ha.ho", 1,1,1," ",1 );
		$this->secondUser = New userModel();



	}
//Test update user profile
	public function testUpdateUser(){
		//update user correctly
		$firstUser->setUserId(55);
		$firstUser->setMail("kjersti@hei.no");
		$firstUser->setAgeGroup(1);
		$firstUser->setGender(1);
		$firstUser->setLocation(0);
		$firstUser->setCategoryPrefs("");
		$this->db->updateUserInfo($newuser);

		//check if user is updated correctly
		$this->assertTrue($firstUser.getUserId() = 55);
		$this->assertTrue($firstUser.getMail() = "kjersti@hei.no");
		$this->assertTrue($firstUser.getGender() = 1);
		$this->assertTrue($firstUser.getAgeGroup() = 1);
		$this->assertTrue($firstUser.getCategoryPrefs() = "");
//		$this->assertTrue($newUser.get() = "");

		//update user incorrectly
		$secondUser->setUserId(55);
		$secondUser->setMail("feilformat");
		$secondUser->setAgeGroup(60);
		$secondUser->setGender("");
		$secondUser->setLocation(0);
		$secondUser->setCategoryPrefs("litterature");
		$this->db->updateUserInfo($secondUser);


		//check if user is updated or what message is revealed
		$this->assertFalse($secondUser.getUserId() = 55);
		$this->assertFalse($secondUser.getMail() = "feilformat");
		$this->assertFalse($secondUser.getAgeGroup() = "");
		$this->assertFalse($secondUser.getGender() = 60);
		$this->assertFalse($secondUser.getCategoryPrefs() = "litterature");

	}
}

?>
<?php
require_once (__DIR__.'/../models/storyModel.php');
require_once (__DIR__.'/../database/dbUser.php');
require_once (__DIR__.'/../models/userModel.php');
class dbconnectionTest extends PHPUnit_Framework_TestCase{

	public $test;
	public $firstUser;
	public $secondUser;
	public $db;

	//tilkobling ok?

	public function setup(){
		$this->db = new dbUser();

		//create user
		$this->firstUser = new userModel(55,"hi@ha.ho", 1,1,1," ",1 );
		$this->secondUser = new userModel();



	}
//Test update user profile
	public function testUpdateUser(){
		//update user correctly
		$this->firstUser->setUserId(55);
		$this->firstUser->setMail("kjersti@hei.no");
		$this->firstUser->setAgeGroup(1);
		$this->firstUser->setGender(1);
		$this->firstUser->setLocation(0);
		//$this->firstUser->setCategoryPrefs("");
		$this->db->updateUserInfo($this->firstUser);

		//check if user is updated correctly
		$this->assertTrue($this->firstUser->getUserId() == 55);
		$this->assertTrue($this->firstUser->getMail() == "kjersti@hei.no");
		$this->assertTrue($this->firstUser->getGender() == 1);
		$this->assertTrue($this->firstUser->getAgeGroup() == 1);
		$this->assertTrue($this->firstUser->getCategoryPrefs() == "");
//		$this->assertTrue($newUser->get() = "");

		//update user incorrectly
		$this->secondUser->setUserId(50);
		$this->secondUser->setMail("feilformat");
		$this->secondUser->setAgeGroup(60);
		$this->secondUser->setGender("");
		$this->secondUser->setLocation(0);
		//$this->secondUser->setCategoryPrefs("litterature");
		$this->db->updateUserInfo($this->secondUser);


		//check if user is updated or what message is revealed
		$this->assertFalse($this->secondUser->getUserId() == 55);
		$this->assertFalse($this->secondUser->getMail() == "feilformat");
		$this->assertFalse($this->secondUser->getAgeGroup() == "");
		$this->assertFalse($this->secondUser->getGender() == 60);
		$this->assertFalse($this->secondUser->getCategoryPrefs() == "litterature");

	}
}

?>
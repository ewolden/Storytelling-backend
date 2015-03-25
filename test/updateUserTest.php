<?php
/**
* @backupGlobals disabled
* @backupStaticAttributes disabled
*/
require_once '../models/storyModel.php';
require_once '../database/dbHelper.php';
require_once '../models/userModel.php';
class updateUserTest extends PHPUnit_Framework_TestCase{

	public $test;

	public function setup(){
		/*Connection to database*/
		$this->db = New dbHelper();
		
		//SKRIV INN DET SOM PASSER TIL DIN LOKALE DB

		/*Create a user that does not exist in the system. SHOULD BE TRUE*/
		$this->newUser1 = New userModel('-1','nhgjgjfi@gmail.com', '1', '1', '0', array('1','4','3'));

		/*Try to create a user with an existing email. SHOULD BE FALSE*/
	//	$this->newUser2 = New userModel('kjersti@gmail.com', '1', '1', '0','0');

		/*Update other fields in userprofile (user has a email registered in the system)*/
	//	$this->updateuser1 = New userModel('kjersti@gmail.com', '0', '0', '0','0');

		/*Update other fields in userprofile (user do not have a email registered)*/
	//	$this->updateuser2 = New userModel('-1' , '1', '1', '0','0');

		/*Try to update userprofile with typing an already existing email*/
	//	$this->updateuser3 = New userModel('kjerstiii@gmail.com', '0', '1', '0','0');
		/**/


	}
	public function testUpdateUser(){

		//actualId should be the new id the db created for this user. 
		$newId = $this->db->updateUserInfo($this->newUser1);
		echo $newId;
		/*Create a user that does not exist in the system. SHOULD BE TRUE*/
	
		$returnedUser = $this->db->getUserFromId($newId);

		$returnedId = $returnedUser->getUserId();
		$returnedEmail = $returnedUser->getMail();
		$returnedGender = $returnedUser->getGender();
		$returnedAge = $returnedUser->getAgeGroup();
		$returnedLocation = $returnedUser->getLocation();
		$returnedCategoryPrefs = $returnedUser->getCategoryPrefs();

		$this->assertTrue(!empty($newId) == $returnedId);
		/*Try to create a user with an existing email. SHOULD BE FALSE*/
//		$this->assertFalse($this->db->updateUserInfo($this->newUser2));

		/*Update other fields in userprofile (user has a email registered in the system)*/

	//	$this->assertTrue($this->db->updateUserInfo($this->updateuser1) = $this->updateuser1->getUserId());

		/*Update other fields in userprofile (user do not have a email registered)*/
	//	$this->assertTrue($this->db->updateUserInfo($this->updateuser2) = $this->updateuser2->getUserId());

		/*Try to update a user that type in a already existing email*/
//		$this->assertFalse($this->db->updateUserInfo($this->updateuser3));


		/**/
	}
}
?>
		
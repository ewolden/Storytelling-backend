<?php

/*Contributors: Kjersti Fagerholt, Roar Gjøvaag, Ragnhild Krogh, Espen Strømjordet,
 Audun Sæther, Hanne Marie Trelease, Eivind Halmøy Wolden

 "Copyright 2015 The TAG CLOUD/SINTEF project

 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at

 http://www.apache.org/licenses/LICENSE-2.0

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License."
 */

/**
 * Unittests for the dbUser class, assumes dbHelper is already tested
 * @author Audun Sæther
 * @author Kjersti Fagerholt
 * @author Eivind Halmøy Wolden
 * @author Hanne Marie Trelease
 */

require_once (__DIR__.'/../User_php/userModelTest.php');

class dbUserTest extends userModelTest{
	public $user;
	public $db;
	
	public function setup(){
		$this->setupUser();
		$this->user = new userModel();
		$this->user->addUser(-1, '54@54.com');
		$this->user->addUserValues('54@54.com', 1, 0, 0, [1,3,5,7,9]);
		$this->db = new dbUser();
	}
	
	public function testAddUser(){
		$this->setup();
		$userId = $this->db->updateUserInfo($this->user);
		$this->assertTrue($userId != 0);
		$this->db->deleteFromTable("category_preference", "userId", $userId);
		$this->db->deleteFromTable("user_tag", "userId", $userId);
		$this->db->deleteFromTable("user", "userId", $userId);
	}
	
	public function testGetUserFromId(){
		$this->setup();
		$userId = $this->db->updateUserInfo($this->user);
		
		$userInsert = new userModel();
		$userInsert = $this->user;
		$userInsert->setUserId($userId);
		
		$userFromDB = new userModel();
		$userFromDB->addFromDB($this->db->getUserFromId($userId));
		
		$this->db->deleteFromTable("category_preference", "userId", $userId);
		$this->db->deleteFromTable("user_tag", "userId", $userId);
		$this->db->deleteFromTable("user", "userId", $userId);
		
		$this->assertEquals($userInsert,$userFromDB);
		
		
	}
	
	public function testUpdateUserMail(){
		$this->setup();
		$userId = $this->db->updateUserInfo($this->user);
		
		$userInsert = new userModel();
		$userInsert = $this->user;
		$userInsert->setUserId($userId);
		$userInsert->setMail("NEW@EMAIL.com");
		
		$this->db->updateUserInfo($userInsert);
		
		$userFromDB = new userModel();
		$userFromDB->addFromDB($this->db->getUserFromId($userId));
		
		$this->assertSame($userInsert->getMail(), $userFromDB->getMail());
		
		$this->db->deleteFromTable("category_preference", "userId", $userId);
		$this->db->deleteFromTable("user_tag", "userId", $userId);
		$this->db->deleteFromTable("user", "userId", $userId);
	}
	
	public function testUpdateUserAge(){
		$this->setup();
		$userId = $this->db->updateUserInfo($this->user);
		
		$userInsert = new userModel();
		$userInsert = $this->user;
		$userInsert->setUserId($userId);
		$userInsert->setAgeGroup(0);
		
		$this->db->updateUserInfo($userInsert);
		
		$userFromDB = new userModel();
		$userFromDB->addFromDB($this->db->getUserFromId($userId));
		
		$this->assertEquals($userInsert->getAgeGroup(), $userFromDB->getAgeGroup());
		
		$this->db->deleteFromTable("category_preference", "userId", $userId);
		$this->db->deleteFromTable("user_tag", "userId", $userId);
		$this->db->deleteFromTable("user", "userId", $userId);
	}
	
	public function testUpdateUserCategories(){
		$this->setup();
		$userId = $this->db->updateUserInfo($this->user);
		
		$userInsert = new userModel();
		$userInsert = $this->user;
		$userInsert->setUserId($userId);
		$userInsert->setCategoryPrefs([2]);
		
		$this->db->updateUserInfo($userInsert);
		
		$userFromDB = new userModel();
		$userFromDB->addFromDB($this->db->getUserFromId($userId));
		
		$this->assertEquals($userInsert->getCategoryPrefs(), $userFromDB->getCategoryPrefs());
		
		$this->db->deleteFromTable("category_preference", "userId", $userId);
		$this->db->deleteFromTable("user_tag", "userId", $userId);
		$this->db->deleteFromTable("user", "userId", $userId);
	}
	
	public function testGetUserFromEmail(){
		$this->setup();
		$userId = $this->db->updateUserInfo($this->user);
	
		$userInsert = new userModel();
		$userInsert = $this->user;
		$userInsert->setUserId($userId);
		
		$userFromDB = new userModel();
		$userFromDB->addFromDB($this->db->getUserFromEmail($this->user->getMail()));
	
		$this->assertEquals($userInsert, $userFromDB);
	
		$this->db->deleteFromTable("category_preference", "userId", $userId);
		$this->db->deleteFromTable("user_tag", "userId", $userId);
		$this->db->deleteFromTable("user", "userId", $userId);
	}
	
	public function testGetUserCategories(){
		$this->setup();
		$userId = $this->db->updateUserInfo($this->user);
	
		$userInsert = new userModel();
		$userInsert = $this->user;
		$userInsert->setUserId($userId);
		
		$categoriesFromDB = $this->db->getUserCategories($userId);

		$this->assertEquals($userInsert->getCategoryPrefs(), explode(",",$categoriesFromDB['categories']));
	
		$this->db->deleteFromTable("category_preference", "userId", $userId);
		$this->db->deleteFromTable("user_tag", "userId", $userId);
		$this->db->deleteFromTable("user", "userId", $userId);
	}
	
	public function testGetMailFromId(){
		$this->setup();
		$userId = $this->db->updateUserInfo($this->user);
		
		$userInsert = new userModel();
		$userInsert = $this->user;
		$userInsert->setUserId($userId);
		
		
		$mailFromDB = $this->db->getMailFromId($userId);

		$this->assertSame($userInsert->getMail(), $mailFromDB['mail']);
		
		$this->db->deleteFromTable("category_preference", "userId", $userId);
		$this->db->deleteFromTable("user_tag", "userId", $userId);
		$this->db->deleteFromTable("user", "userId", $userId);
	}
	
}

?>
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
 *Unittests for userModel class
 * @author Audun Sæther
 * @author Kjersti Fagerholt
 * @author Eivind Halmøy Wolden
 * @author Hanne Marie Trelease
 */

require_once (__DIR__.'/../../../database/dbUser.php');
require_once (__DIR__.'/../../../models/userModel.php');

class userModelTest extends PHPUnit_Framework_TestCase{
	public $userId;
	public $mail;
	public $gender;
	public $age_group;
	public $use_of_location;
	public $category_preference;
	
	public function setupUser(){
		$userId = 1;
		$mail = '54@54.com';
		$gender = 0;
		$age_group = 1;
		$use_of_location = 0;
		$category_preference = [1,3,5,7,9];
	}
	
	public function testInitiateUser(){
		$this->setupUser();
		$user = new userModel();
		$user->addUser($this->userId, $this->mail);
		$this->assertTrue($user->getUserId() == $this->userId);
		$this->assertSame($user->getMail(),$this->mail);
	}
	
	public function testSetAllUserDetails(){
		$this->setupUser();
		$user = new userModel();
		$user->addUser($this->userId, $this->mail);
		$user->addUserValues($this->mail, $this->age_group, $this->gender, $this->use_of_location, $this->category_preference);
		$this->assertSame($user->getUserId(),$this->userId);
		$this->assertSame($user->getMail(), $this->mail);
		$this->assertSame($user->getAgeGroup(), $this->age_group);
		$this->assertSame($user->getGender(), $this->gender);
		$this->assertSame($user->getLocation(), $this->use_of_location);
		$this->assertSame($user->getCategoryPrefs(), $this->category_preference);
	}
	
	public function testPrintAll(){
		$this->setupUser();
		$user = new userModel();
		$user->addUser($this->userId, $this->mail);
		$user->addUserValues($this->mail, $this->age_group, $this->gender, $this->use_of_location, $this->category_preference);
		$userArray = $user->printAll();
		$this->assertSame($userArray['userId'],$this->userId);
		$this->assertSame($userArray['email'], $this->mail);
		$this->assertSame($userArray['age_group'], $this->age_group);
		$this->assertSame($userArray['gender'], $this->gender);
		$this->assertSame($userArray['use_of_location'], $this->use_of_location);
		$this->assertSame($userArray['category_preference'], $this->category_preference);
	}
	
	public function testSetsGets(){
		$this->setupUser();
		$user = new userModel();
		$user->setUserId($this->userId);
		$user->setMail($this->mail);
		$user->setAgeGroup($this->age_group);
		$user->setGender($this->gender);
		$user->setLocation($this->use_of_location);
		$user->setCategoryPrefs($this->category_preference);
		
		$this->assertSame($user->getUserId(), $this->userId);
		$this->assertSame($user->getMail(), $this->mail);
		$this->assertSame($user->getAgeGroup(), $this->age_group);
		$this->assertSame($user->getGender(), $this->gender);
		$this->assertSame($user->getLocation(), $this->use_of_location);
		$this->assertSame($user->getCategoryPrefs(), $this->category_preference);
	}
}

?>
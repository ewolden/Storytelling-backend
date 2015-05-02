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
 * Class containing information related to a user
 * @author Audun Sæther
 * @author Kjersti Fagerholt
 * @author Eivind Halmøy Wolden
 * @author Hanne Marie Trelease
 */
class userModel{
    private $userId;
    private $email;
    private $age_group;
    private $gender;
    private $use_of_location;
    private $category_preference; //list of categories preferred
    
    /**
     * Add a new userId and email
     * @param int $userId
     * @param String $email
     */
    function addUser($userId, $email){
        $this->userId = $userId;
        $this->email = $email;
    }
    
    /**
     * Add new user values
     * @param String $email
     * @param int $age_group
     * @param boolean $gender 0 or 1
     * @param boolean $use_of_location 0 or 1
     * @param array $category_preference 
     */
    function addUserValues($email, $age_group,$gender,$use_of_location,$category_preference){
        if(!is_null($email) && $email != -1)
            $this->email = $email;
        if(!is_null($age_group))
            $this->age_group = $age_group;
        if(!is_null($gender))
            $this->gender = $gender;
        if(!is_null($use_of_location))
            $this->use_of_location = $use_of_location;
        $this->category_preference = $category_preference;
    }

    /**
     * Adds userinformation stored in database to a userModel
     * @param array $userFromDB array retrieved from db
     */
    function addFromDB($userFromDB){
        $this->setUserId($userFromDB[1]['userId']);
        $this->setMail($userFromDB[1]['mail']);
        $this->setAgeGroup($userFromDB[1]['age_group']);
        $this->setGender($userFromDB[1]['gender']);
        $this->setLocation($userFromDB[1]['use_of_location']);
        if(is_array($userFromDB[2]))
            $this->setCategoryPrefs(explode(",",$userFromDB[2]['categories']));
    }

    /**
     * Print userModel as array for exporting
     * @return storyArray
     */
    public function printAll(){
        return array(
            'userId' => $this->getUserId(),
            'email' => $this->getMail(),
            'age_group' => $this->getAgeGroup(),
            'gender' => $this->getGender(),
            'use_of_location' => $this->getLocation(),
            'category_preference' => $this->getCategoryPrefs()
            );
    }

    //SETTERS
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }
    
    public function setMail($email)
    {
        $this->email = $email;
    }
    
    public function setAgeGroup($age_group)
    {
        $this->age_group = $age_group;
    }
    
    public function setGender($gender)
    {
        $this->gender = $gender;
    }
    
    public function setLocation($use_of_location)
    {
        $this->use_of_location = $use_of_location;
    }

    public function setCategoryPrefs($category_preference)
    {
        $this->category_preference = $category_preference;
    }    

    //GETTERS
    public function getUserId()
    {
        return $this->userId;
    }
    
    public function getMail()
    {
        return $this->email;
    }
    
    public function getAgeGroup()
    {
        return $this->age_group;
    }
    
    public function getGender()
    {
        return $this->gender;
    }
    
    public function getLocation()
    {
        return $this->use_of_location;
    }

    public function getCategoryPrefs()
    {
        return $this->category_preference;
    }


}

?>
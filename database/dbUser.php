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

require_once (__DIR__.'/dbHelper.php');
require_once (__DIR__.'/../models/userModel.php');
require_once (__DIR__.'/../phpmailer/PHPMailerAutoload.php');

/**
 * This class handles database communication related to users
 * @author Audun Sæther
 * @author Kjersti Fagerholt
 * @author Eivind Halmøy Wolden
 * @author Hanne Marie Trelease
 */
class dbUser extends dbHelper {
	
	/** Construct a new dbHelper-instance*/
	function __construct() {
		parent::__construct();
	}
	
     /**
      * Adds or updates a user and chosen preferences to the database, 
      * does not allow duplicate email adresses in the DB
      * @param unknown $user
      * @return boolean|userId returns userId if user is added/changed, false if email exists
      */
     public function updateUserInfo($user){
        $values = array();
        /* Find if the email address that the user tries to add/change is in the DB */
        $sql = "SELECT COUNT(*) as count from user WHERE mail = (:mail)";
        $stmt = $this->db->prepare($sql);
		$mail = $user->getMail();
        $stmt->bindParam(':mail', $mail);
        $stmt->execute();
		
		$numberOfEmailsFound = $stmt->fetch(PDO::FETCH_ASSOC);
		if($numberOfEmailsFound['count'] == 0){ /* Checks if there are no users with that email address */
			if($user->getUserId() == -1 && $user->getMail() == -1){ /* Create user without email */
				$values = array(null,$user->getAgeGroup(),$user->getGender(),$user->getLocation());
			}
			else if($user->getUserId() == -1){ /* User is creating a new user with unique email address */
				$values = array($user->getMail(),$user->getAgeGroup(),$user->getGender(),$user->getLocation());
				$this->sendMail(false, $user->getMail());
			}
			else if($user->getUserId() != -1 && $user->getMail() == -1){ /*Update user who has not got an email registrated in db*/
				$values = array($user->getUserId(),null,$user->getAgeGroup(),$user->getGender(),$user->getLocation());
			}
			else { /* We are updating a user who have inputed a new unique email address */
				$values = array($user->getUserId(),$user->getMail(),$user->getAgeGroup(),$user->getGender(),$user->getLocation());
				$this->sendMail(true, $user->getMail());
			}
		} else{ /* There exists a user with the same email in the DB */
			if($user->getUserId() == -1){ /* User is trying to create a new user and assign an existing email */
				return false;
			}
			else{ /* User is either updating other fields or is chaning his email to an already chosen address */
				$sql = "SELECT mail from user where userId = (:userid)";
				$stmt = $this->db->prepare($sql);
				//Setting the parameters
				$userid=$user->getUserId();
				//binding the parameters
				$stmt->bindParam(':userid', $userid);
				$stmt->execute();
        		if(strcmp($stmt->fetch(PDO::FETCH_ASSOC)['mail'],$user->getMail()) == 0){ /* Compares DB mail to user mail. The user is trying to change something other than his email */
        			$values = array($user->getUserId(),$user->getMail(),$user->getAgeGroup(),$user->getGender(),$user->getLocation());
				} 
				else{ /* The user is trying to change from an email that is in the DB, to a new different email that already exists in the DB */
					return false;
				}
			}
		}
        $this->insertUpdateAll('user',$values);
        $userId = $this->db->lastInsertId();
        /*If we are updating a user lastInsertId will return 0. 
		This userId doesn't exist and will cause problems further down.
		So if we are updating, we get the userId from the input user-model*/
        if($userId == 0){
        	if($user->getUserId() != -1)
        		$userId = $user->getUserId();
        	else return false;
        } else {
        	$this->insertUpdateAll('user_tag', array($userId, "Lest"));
        	$this->insertUpdateAll('user_tag', array($userId, "Les senere"));
        }

         /*Inserting category preferences*/
        if(!is_null($user->getCategoryPrefs())){
            /*Deleting all existing category preferences*/
            $this->deleteFromTable('category_preference', array('userId'), array($userId));
            foreach($user->getCategoryPrefs() as $category){
                $this->insertUpdateAll('category_preference', array($userId,$category));
            }
        }
        return $userId;
    }

    /**
     * Returns a userModel from the database based on email
     * @param String $email
     * @return $userModel
     */	
    public function getUserFromEmail($email){
    	$sql = "SELECT * from user where mail = (:usermail)";
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':usermail',$email);
        $stmt->execute();
        $userrow = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(count($userrow) > 0){ /* User with the inputed email exists */
			return array(true,$userrow[0], $this->getUserCategories($userrow[0]['userId']));
    	}
    	else { /* User with the inputed email cannot be found */
    		return array(false);
    	}
	}
	
	/**
	 * Returns a userModel from the database based on userId
	 * @param int $userId
	 * @return $userModel
	 */
    public function getUserFromId($userId){
    	$sql = "SELECT * from user where userId = (:userid)";
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':userid',$userId);
		$stmt->execute();
        $userrow = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(count($userrow) > 0){ /* User with the inputed email exists */
			return array(true,$userrow[0], $this->getUserCategories($userrow[0]['userId']));
    	}
    	else { /* User with the inputed email cannot be found */
    		return array(false);
    	}
    }
    
    /**
     * Get a users category preferences
     * @param int $userId
     * @return $row 	category preference array
     */
    public function getUserCategories($userId){
   		$sql = "SELECT group_concat(distinct categoryId) as categories
   		from category_preference 
   		where userId = (:userid)
   		group by userId";
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':userid',$userId);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return $row;
    }
    
    /**
     * Get a users email address from userId
     * @param int $userId
     * @return $rows
     */
    public function getMailFromId($userId){
    	$sql = "SELECT mail from user where userId = :userid";
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':userid', $userId);
		$stmt->execute();
    	$row = $stmt->fetch(PDO::FETCH_ASSOC);
    	return $row;
    }
    
    /**
     * Get number of users in database
     * @return $result
     */
    public function getNumberOfUsers(){
    	$sql = "SELECT COUNT(*) from user";
    	$stmt = $this->db->prepare($sql);
    	$stmt->execute();
    	$result = $stmt->fetch();
    	if($result){
    		//print_r($result[0]);
    		return $result[0];
    	}else{
    		return null;
    	}
    }
    
    /**
     * Returns the number of rated stories, EXCEPT this user
     * @param int $userId
     * @return $result
     */
    public function getNumberOfRatedStories($userId){
    	/*Returns the number of rated stories, EXCEPT this user*/
    	$sql = "SELECT COUNT(*) from story_state WHERE userId != (:userId) AND stateId = 5";
    	$stmt = $this->db->prepare($sql);
    	$stmt->bindParam(':userId', $userId);
    	$stmt->execute();
    	$result = $stmt->fetch();
    	//print_r($result[0])
    	if($result){
    		return $result[0];
    	}else{
    		return null;
    	}
    }
    
    /**
     * Returns the number of rated stories done by this user
     * @param int $userId
     * @return $result
     */
    public function getNumberOfRatedStoriesByThisUser($userId){
        /*Returns the number of rated stories done by this user */
        $sql = "SELECT COUNT(*) from story_state WHERE userId = (:userId) AND stateId = 5";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
        $result = $stmt->fetch();
        //print_r($result[0])
        if($result){
            return $result[0];
        }else{
            return null;
        }

    }
    /**
     * Returns the number of stories this user has rated 
     * that at least 10 other users also have rated
     * @param int $userId
     * @return $result
     */
    public function getNumRatedStoriesShared($userId){
        $sql = "SELECT COUNT(*) FROM collaborative_view AS userRatedStories
        INNER JOIN 
        (SELECT numericalId FROM collaborative_view GROUP BY numericalId HAVING count(userId) >= 10) 
        AS sotriesWithTenUserRatings 
        WHERE userId= (:userId) 
        AND userRatedStories.numericalId = sotriesWithTenUserRatings.numericalId";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
        $result = $stmt->fetch();
        if($result){
            return $result[0];
        }else{
            return null;
        }
    }
    
    /**
     * Sends a confirmation email to the given email.
     * @param boolean $updated
     * @param String $email
     */
    public function sendMail($updated, $email){
    	$subject = 'Velkommen som ny bruker';
    	$message = 'Hei, <br><br>
        Takk for at du har registrert deg og velkommen som ny bruker av Javisst. ' .
            'Vi bekrefter at det er opprettet en brukerprofil med bruker ' . $email .
            ' i vår database. <br><br> Logg inn med brukernavn <b>' . $email .
            '</b> for å lese historier om kulturarv anbefalt basert på dine preferanser.<br><br>
        Med vennlig hilsen <br>Javisst';
    	$altMessage = 'Hei,
        Takk for at du har registrert deg og velkommen som ny bruker av Javisst. ' .
            'Vi bekrefter at det er opprettet en brukerprofil med bruker ' . $email .
            ' i vår database. Logg inn med brukernavn ' . $email .
            ' for å lese historier om kulturarv anbefalt basert på dine preferanser.
        Med vennlig hilsen Javisst';
    
    	if ($updated) {
    		$subject = 'Bruker oppdatert';
    		$message = 'Hei, <br><br>Vi bekrefter at brukerprofil med bruker ' .
    				$email . ' har blitt oppdatert i vår database.<br><br>
            Du kan nå logge inn med brukernavn <b>' . $email .
                '</b> for å lese historier om kulturarv anbefalt basert på dine preferanser.
            <br><br>Med vennlig hilsen <br>Javisst';
    		$altMessage = 'Hei, Vi bekrefter at brukerprofil med bruker ' .
    				$email . ' har blitt oppdatert i vår database.
            Du kan nå logge inn med brukernavn ' . $email .
                ' for å lese historier om kulturarv anbefalt basert på dine preferanser.
            Med vennlig hilsen Javisst';
    	}
    
    	$mail = new PHPMailer;
    	$mail->CharSet = 'UTF-8';
    	$mail->isSMTP();                                      // Set mailer to use SMTP
    	$mail->Host = 'smtp.gmail.com';                       // Specify main and backup server
    	$mail->SMTPAuth = true;                               // Enable SMTP authentication
    	$mail->Username = 'javisstsintef@gmail.com';                   // SMTP username
    	$mail->Password = 'javisstatsintef';               // SMTP password
    	$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted
    	$mail->Port = 587;                                    //Set the SMTP port number - 587 for authenticated TLS
    	$mail->FromName = 'Javisst';     //Set who the message is to be sent from
    	$mail->AddReplyTo("no-reply@javisst.no","No Reply");
    	$mail->addAddress($email);  // Add a recipient
    	$mail->WordWrap = 50;                                 // Set word wrap to 50 characters
    	$mail->isHTML(true);                                  // Set email format to HTML
    
    	$mail->Subject = $subject;
    	$mail->Body    = $message;
    	$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
    	$mail->send();
    }
}
?>

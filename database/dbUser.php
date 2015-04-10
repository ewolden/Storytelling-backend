<?php
require_once 'dbHelper.php';
require_once '../models/userModel.php';

/**
* This class handles database communication related to users
*/

class dbUser extends dbHelper {
	
	/*Construct a new dbHelper-instance*/
	function __construct() {
		parent::__construct();
	}
	
	/** Adds or updates a user and chosen preferences to the database, does not allow duplicate email adresses in the DB, returns userId if user is added/changed, false if email exists**/
     public function updateUserInfo($user){
        $values = array();
        /** Find if the email address that the user tries to add/change is in the DB **/
        $sql = "SELECT COUNT(*) as count from user WHERE mail = (:mail)";
        $stmt = $this->db->prepare($sql);
		$mail = $user->getMail();
        $stmt->bindParam(':mail', $mail);
        $stmt->execute();
		
		$numberOfEmailsFound = $stmt->fetch(PDO::FETCH_ASSOC);
		if($numberOfEmailsFound['count'] == 0){ /** Checks if there are no users with that email address **/
			if($user->getUserId() == -1 && $user->getMail() == -1){ /** Create user without email **/
				$values = array(null,$user->getAgeGroup(),$user->getGender(),$user->getLocation());
			}
			else if($user->getUserId() == -1){ /** User is creating a new user with unique email address **/
				$values = array($user->getMail(),$user->getAgeGroup(),$user->getGender(),$user->getLocation());
			}
			else if($user->getUserId() != -1 && $user->getMail() == -1){ /**Update user who has not got an email registrated in db**/
				$values = array($user->getUserId(),null,$user->getAgeGroup(),$user->getGender(),$user->getLocation());
			}
			else { /** We are updating a user who have inputed a new unique email address **/
				$values = array($user->getUserId(),$user->getMail(),$user->getAgeGroup(),$user->getGender(),$user->getLocation());
			}
		} else{ /** There exists a user with the same email in the DB **/
			if($user->getUserId() == -1){ /** User is trying to create a new user and assign an existing email **/
				return false;
			}
			else{ /** User is either updating other fields or is chaning his email to an already chosen address **/
				$sql = "SELECT mail from user where userId = (:userid)";
				$stmt = $this->db->prepare($sql);
				//Setting the parameters
				$userid=$user->getUserId();
				//binding the parameters
				$stmt->bindParam(':userid', $userid);
				$stmt->execute();
        		if(strcmp($stmt->fetch(PDO::FETCH_ASSOC)['mail'],$user->getMail()) == 0){ /** Compares DB mail to user mail. The user is trying to change something other than his email **/
        			$values = array($user->getUserId(),$user->getMail(),$user->getAgeGroup(),$user->getGender(),$user->getLocation());
				} 
				else{ /** The user is trying to change from an email that is in the DB, to a new different email that already exists in the DB */
					return false;
				}
			}
		}
        $this->insertUpdateAll('user',$values);
        $userId = $this->db->lastInsertId();

        /*Deleting all existing category preferences*/
        $this->deleteFromTable('category_preference', array('userId'), array($user->getUserId()));

         /*Inserting category preferences*/
		if(!is_null($user->getCategoryPrefs())){
			foreach($user->getCategoryPrefs() as $category){
				$this->insertUpdateAll('category_preference', array($userId,$category)); 
			}
		}
        return $userId;
    }

    /** Returns a(1) userModel from the database based on email **/	
    public function getUserFromEmail($email){
    	$sql = "SELECT * from user where mail = (:usermail)";
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':usermail',$email);
        $stmt->execute();
        $userrow = $stmt->fetchAll();
        if(count($userrow) > 0){ /** User with the inputed email exists **/
			return array(true,$userrow[0], $this->getUserCategories($userrow[0]['userId']));
    	}
    	else { /** User with the inputed email cannot be found **/
    		return array(false);
    	}
	}


    /** Returns a(1) userModel from the database based on userId **/	
    public function getUserFromId($userId){
    	$sql = "SELECT * from user where userId = (:userid)";
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':userid',$userId);
		$stmt->execute();
        $userrow = $stmt->fetchAll();
        if(count($userrow) > 0){ /** User with the inputed email exists **/
			return array(true,$userrow[0], $this->getUserCategories($userrow[0]['userId']));
    	}
    	else { /** User with the inputed email cannot be found **/
    		return array(false);
    	}
    }
    public function getUserCategories($userId)
    {
   		$sql = "SELECT group_concat(distinct categoryName) as categories
   		from category,category_preference 
   		where userId = (:userid) AND category.categoryId = category_preference.categoryId
   		group by userId";
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':userid',$userId);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return $row;
    }

    public function getMailFromId($userId){
    	$sql = "SELECT mail from user where userId = :userid";
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':userid', $userId);
		$stmt->execute();
    	$row = $stmt->fetch(PDO::FETCH_ASSOC);
    	return $row;
    }
}
?>
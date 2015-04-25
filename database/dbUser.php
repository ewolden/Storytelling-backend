<?php
require_once (__DIR__.'/dbHelper.php');
require_once (__DIR__.'/../models/userModel.php');

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

        /*Deleting all existing category preferences*/
        $this->deleteFromTable('category_preference', array('userId'), array($userId));

         /*Inserting category preferences*/
		if(!is_null($user->getCategoryPrefs())){
			foreach($user->getCategoryPrefs() as $category){
				if(is_string($category)){
					$category = $this->getSelected('category', 'categoryId', 'categoryName', $category)[0]['categoryId'];
				}
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
        $userrow = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        $userrow = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(count($userrow) > 0){ /** User with the inputed email exists **/
			return array(true,$userrow[0], $this->getUserCategories($userrow[0]['userId']));
    	}
    	else { /** User with the inputed email cannot be found **/
    		return array(false);
    	}
    }
    public function getUserCategories($userId)
    {
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

    public function getMailFromId($userId){
    	$sql = "SELECT mail from user where userId = :userid";
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':userid', $userId);
		$stmt->execute();
    	$row = $stmt->fetch(PDO::FETCH_ASSOC);
    	return $row;
    }
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
    /* Returns the number of stories this user has rated that at least 10 other users also have rated */
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
}
?>

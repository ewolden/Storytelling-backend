<?php

/*Contributors: Kjersti Fagerholt, Roar Gj�vaag, Ragnhild Krogh, Espen Str�mjordet,
 Audun S�ther, Hanne Marie Trelease, Eivind Halm�y Wolden

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
*Script that intercepts and interprets angular http requests and perform the desired action
* @author Audun S�ther
* @author Kjersti Fagerholt
* @author Eivind Halm�y Wolden
* @author Hanne Marie Trelease
*/
require_once(__DIR__."/../models/storyModel.php");
require_once(__DIR__."/../models/userModel.php");
require_once(__DIR__."/../database/dbUser.php");
require_once(__DIR__."/../database/dbStory.php");
require_once(__DIR__."/../personalization/runRecommender.php");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header("Content-Type: application/json; charset=UTF-8");

$dbUser = new dbUser();
$dbStory = new dbStory();
$postdata = file_get_contents("php://input");
//$postdata = $_POST['data'];
$request = json_decode($postdata);
$type = $request->type;
switch ($type) {

	/**Returns all contents from a single story*/
	case "getStory":
	$storyModel = new storyModel();
	$storyModel->getFromDF($request->storyId);
    $data = $dbStory->fetchStory($request->storyId, $request->userId);
	$storyModel->fromDB($data);
	$dbStory->insertUpdateAll('story_state', array($request->storyId, $request->userId, 4));
	print_r (json_encode($storyModel->getAll()));
	break;
	
	/*This method is called when front end wants new recommendations. In such cases, the front end array
	 should be counted as empty in the database. */
	case "getStories":
	/*This methods empties the front end array for this user*/
	$dbStory->emptyFrontendArray($request->userId);
	$data = $dbStory->getRecommendedStories($request->userId);
	$returnArray = array();
	foreach ($data as $story) {
		$list = array(
			'id' => $story['storyId'],
			'title' => $story['title'],
			'description' => $story['introduction'],
			'false_recommend' => $story['false_recommend'],
			'explanation' => explode(",",$story['explanation']),
			'picture' => "",
			'thumbnail' => "",
			'categories' => "",
			'mediaType' => array(),
			'author' => $story['author'],
			'date' => "");
		if(array_key_exists('categories', $story))
			$list['categories'] = explode(",",$story['categories']);
		if(array_key_exists('mediaId', $story)){
			$medialist = explode(",", $story['mediaId']);
			if(in_array(1, $medialist)){
				array_push($list['mediaType'], "picture");
				$list['picture'] = "http://media31.dimu.no/media/image/H-DF/".$story['storyId']."/0?byIndex=true&height=400&width=400";
				$list['thumbnail'] = "http://api.digitaltmuseum.no/media?owner=H-DF&identifier=".$story['storyId']."&type=thumbnail&api.key=demo";
			}
			if(in_array(2, $medialist))
				array_push($list['mediaType'], "audio");
			if(in_array(3, $medialist))
				array_push($list['mediaType'], "video");

		}			
		array_push($returnArray, $list);
	}
	print_r(json_encode($returnArray));
	break;
	
	/** Recieves request from frotned, adds a new user to the database with autoincremented userId */
	case "addUser":
	$userModel = new userModel();
	$userModel->addUser(-1, $request->email);
	$userId = $dbUser->updateUserInfo($userModel);
	if($userId){ /* User sucessfully added, returns returns sucess message and newly assigned userId */
		print_r(json_encode(array('status' => "sucessfull",'userId' => $userId)));
	}
	else { /* User entered an email that is already in the DB, returns status failed */
		print_r(json_encode(array('status' => "failed")));
	}
	break;

	/** Updates a user with new values from frontend */
	case "updateUser":
	$userModel = new userModel();
	$userInfo = $dbUser->getUserFromId($request->userId);
	$userModel->addFromDB($userInfo);
	$userModel->addUserValues($request->email, $request->age_group, $request->gender, 
		$request->use_of_location, $request->category_preference);

	$userId = $dbUser->updateUserInfo($userModel);
	if($userId){/* User sucessfully updated, returns sucess message and userId */
		/* Running the recommender only if the update include categories to avoid running it 
		when the user sets gender and age.*/
		$output = $userId;
		if(!is_null($request->category_preference)){
			$startTime = microtime(true);
			$userModel->setUserId($userId);
			$recommend = new runRecommender($userModel);
			$prefTime = microtime(true)-$startTime;
			$output = $recommend->runRecommender();
			$endTime = microtime(true)-$startTime;
			$output .= "Preference value computation time: ".$prefTime;
			$output .= "\nTotal recommendation time: ".$endTime;
		}
		print_r(json_encode(array('status' => "successfull",'userId' => $output)));
	}
	else { /* User entered an email that is already in the DB, returns status failed */
		print_r(json_encode(array('status' => "failed")));
	}
	break;

/** Invoked when frontend is trying to retrive a user instance using email as identifier */
	case "getUserFromEmail":
	$userFromDB = $dbUser->getUserFromEmail($request->email);
	if($userFromDB[0]) { /* user exists returning status successfull and user instance */
		$userModel = new userModel();
		$userModel->addFromDB($userFromDB);
		print_r(json_encode(array('status' => "successfull", 'userModel' => $userModel->printAll())));
	}
	else { /* user does not exist, returning status failed*/
		print_r(json_encode(array('status' => "failed")));
	}
	break;

/** Invoked when frontend is trying to retrive a user instance using a userId as identifier */
	case "getUserFromId":
	$userFromDB = $dbUser->getUserFromId($request->userId);
	if($userFromDB[0]) { /* user exists returning status successfull and user instance */
		$userModel = new userModel();
		$userModel->addFromDB($userFromDB);
		print_r(json_encode(array('status' => "successfull", 'userModel' => $userModel->printAll())));
	}
	else { /* user does not exist, returning status failed*/
		print_r(json_encode(array('status' => "failed")));
	}
	break;

	/** Saves a users rating of a story. */
	case "rating":
	if($request->rating > 0){
		$updated = $dbStory->updateOneValue('stored_story', 'rating', $request->rating, array($request->userId, $request->storyId));
		if(!$updated)
			$updated = $dbStory->insertUpdateAll('stored_story', array($request->userId, $request->storyId, null, $request->rating, 0, 0,null,0));
		
		if($updated){
			$dbUser->insertUpdateAll('user_storytag', array($request->userId, $request->storyId, "Lest"));
			$dbStory->insertUpdateAll('story_state', array($request->storyId, $request->userId, 5));
		
		/*Run the recommender*/
			$userModel = new userModel();
			$userInfo = $dbUser->getUserFromId($request->userId);
			$userModel->addFromDB($userInfo);
			$recommend = new runRecommender($userModel);
			$recommend->runRecommender();
			print_r(json_encode(array('status' => "successfull")));
		} else {
			print_r(json_encode(array('status' => "failed")));
		}
	}else {
		$dbStory->insertUpdateAll('story_state', array($request->storyId, $request->userId, 6));
	}
	break;

	/** Add a new tag and connect it to the user, and the story*/
	case "addNewTag":
	$dbUser->insertUpdateAll('user_tag', array($request->userId, $request->tagName));
	$dbUser->insertUpdateAll('user_storytag', array($request->userId, $request->storyId, $request->tagName));
	break;

	/** Tag a story*/
	case "tagStory":
	if($request->tagName == "Les senere"){
		$dbStory->insertUpdateAll('story_state', array($request->storyId, $request->userId, 3));
	}
	$dbUser->insertUpdateAll('user_storytag', array($request->userId, $request->storyId, $request->tagName));
	break;

	/** Get all stories connected to a user and the tagName*/
	case "getList":
	$data = $dbStory->getStoryList($request->userId, $request->tagName);
	$returnArray = array();
	foreach($data as $story){
		$list = array(
			'id' => $story['storyId'],
			'title' => $story['title'],
			'description' => $story['introduction'],
			'false_recommend' => $story['false_recommend'],
			'explanation' => explode(",",$story['explanation']),
			'picture' => "",
			'thumbnail' => "",
			'categories' => "",
			'mediaType' => array(),
			'author' => $story['author'],
			'date' => "");
		if(array_key_exists('categories', $story))
			$list['categories'] = explode(",",$story['categories']);
		if(array_key_exists('mediaId', $story)){
			$medialist = explode(",", $story['mediaId']);
			if(in_array(1, $medialist)){
				array_push($list['mediaType'], "picture");
				$list['picture'] = "http://media31.dimu.no/media/image/H-DF/".$story['storyId']."/0?byIndex=true&height=400&width=400";
				$list['thumbnail'] = "http://api.digitaltmuseum.no/media?owner=H-DF&identifier=".$story['storyId']."&type=thumbnail&api.key=demo";
			}
			if(in_array(2, $medialist))
				array_push($list['mediaType'], "audio");
			if(in_array(3, $medialist))
				array_push($list['mediaType'], "video");
		}		
		array_push($returnArray, $list);
	}
	print_r(json_encode($returnArray));
	break;

	/** Get all tags connected to a user*/
	case "getAllLists":
	$data = $dbUser->getSelected('user_tag', 'tagName', array('userId'), array($request->userId));
	$returnArray = array();
	if(!is_null($data)){
		foreach($data as $tag){
			$list = array(
				'text' => $tag['tagName'],
				'checked' => ''
				);
			array_push($returnArray, $list);
		}
	}
	print_r(json_encode($returnArray));
	break;

	/** Get all tags connected to a story for a user*/
	case "getStoryTags":
	$data = $dbUser->getSelected('user_storytag', 'tagName', array('userId', 'storyId'), array($request->userId, $request->storyId));
	$returnArray = array();
	if(count($data) > 0){
		foreach($data as $tag){
			$list = array(
				'text' => $tag['tagName'],
				'checked' => true
				);
			array_push($returnArray, $list);
		}
	}
	print_r(json_encode($returnArray));
	break;

	/** Remove a tag connected to a story (remove from list)*/
	case "removeTagStory":
	$dbUser->deleteFromTable('user_storytag', array('userId', 'storyId', 'tagName'), array($request->userId, $request->storyId, $request->tagName));
	break;

	/** Remove a tag (list) altogether for a user, both the connection to the user and for all stories connected to the tag*/
	case "removeTag":
	$dbUser->deleteFromTable('user_storytag', array('userId', 'tagName'), array($request->userId, $request->tagName));
	$dbUser->deleteFromTable('user_tag', array('userId', 'tagName'), array($request->userId, $request->tagName));
	break;

	/** Set reject state in the database */
	case "rejectStory":
	$dbStory->insertUpdateAll('story_state', array($request->storyId, $request->userId, 2));
	break;
	
	/** Register users usage of app in the database */
	case "appUsage":
	$return = $dbStory->insertUpdateAll('user_usage', array($request->userId, $request->usageType));
	if($return){
		/* If the user is leaving the application, update recommendations in database so they are ready for next log on*/
		if($request->usageType == "Closed"){
			$dbStory->emptyFrontendArray($request->userId);
			$userModel = new userModel();
			$userInfo = $dbUser->getUserFromId($request->userId);
			$userModel->addFromDB($userInfo);
			$recommend = new runRecommender($userModel);
			$recommend->runRecommender();
		}
		print_r(json_encode(array('status' => "successfull")));
	}
	else {
		print_r(json_encode(array('status' => "failed")));
	}
	break;
	
	/** Set recommended state for story in database */ 
	case "recommendedStory":
	$dbStory->insertUpdateAll('story_state', array($request->storyId, $request->userId, 1));
	break;
	
	/** Get more stories after the 10 initial recommendations*/
	case "getMoreRecommendations":
	$userModel = new userModel();
	$userInfo = $dbUser->getUserFromId($request->userId);
	$userModel->addFromDB($userInfo);
	$recommend = new runRecommender($userModel);
	/*True means that we're avoiding returning stories already in the story-array at frontend*/
	$recommend->setAdd("true");
	$output = $recommend->runRecommender();
	
	$data = $dbStory->getRecommendedStories($request->userId);
	$returnArray = array();
	foreach ($data as $story) {
		$list = array(
			'id' => $story['storyId'],
			'title' => $story['title'],
			'description' => $story['introduction'],
			'false_recommend' => $story['false_recommend'],
			'explanation' => explode(",",$story['explanation']),
			'picture' => "",
			'thumbnail' => "",
			'categories' => "",
			'mediaType' => array(),
			'author' => $story['author'],
			'date' => "");
		if(array_key_exists('categories', $story))
			$list['categories'] = explode(",",$story['categories']);
		if(array_key_exists('mediaId', $story)){
			$medialist = explode(",", $story['mediaId']);
			if(in_array(1, $medialist)){
				array_push($list['mediaType'], "picture");
				$list['picture'] = "http://media31.dimu.no/media/image/H-DF/".$story['storyId']."/0?byIndex=true&height=400&width=400";
				$list['thumbnail'] = "http://api.digitaltmuseum.no/media?owner=H-DF&identifier=".$story['storyId']."&type=thumbnail&api.key=demo";
			}
			if(in_array(2, $medialist))
				array_push($list['mediaType'], "audio");
			if(in_array(3, $medialist))
				array_push($list['mediaType'], "video");

		}			
		array_push($returnArray, $list);
	}
	print_r(json_encode($returnArray));
	break;

	default: 
	echo "Unknown type";
	break;

}
$dbUser->close();
$dbStory->close();
?>
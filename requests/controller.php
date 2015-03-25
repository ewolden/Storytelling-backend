<?php
/**
*Script that intercepts and interprets angular http requests and perform the desired action
*/
require_once(__DIR__."/../models/storyModel.php");
require_once(__DIR__."/../models/userModel.php");
require_once(__DIR__."/../database/dbHelper.php");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header("Content-Type: application/json; charset=UTF-8");

$db = new DbHelper();
$postdata = file_get_contents("php://input");
//$postdata = $_POST['data'];
$request = json_decode($postdata);
$type = $request->type;
switch ($type) {

	case "getStory":
	$storyModel = new storyModel();
	$storyModel->getFromDF($request->storyId);
	$storyModel->getFromDB();	//Should use $request->userId
	$db->insertUpdateAll('story_state', array($request->storyId, $request->userId, 4));
	print_r (json_encode($storyModel->getAll()));
	break;

	case "getStories":
	$data = $db->getAllStories();
	$returnArray = array();
	foreach ($data as $story) {
		$list = array(
			'id' => $story['storyId'],
			'title' => $story['title'],
			'description' => $story['introduction'],
			'thumbnail' => "",
			'categories' => "",
			'author' => $story['author'],
			'date' => "");
		if(array_key_exists('categories', $story))
			$list['categories'] = explode(",",$story['categories']);
		if($story['mediaId'] == 1)
			$list['thumbnail'] = "http://api.digitaltmuseum.no/media?owner=H-DF&identifier=".$story['storyId']."&type=thumbnail&api.key=demo";
		array_push($returnArray, $list);
	}
	print_r(json_encode($returnArray));
	break;

/** Recieves request from frotned, adds a new user to the database with autoincremented userId **/
	case "addUser":
	$userModel = new userModel();
	$userModel->addUserValues(-1, $request->email, $request->age_group, $request->gender,
		$request->use_of_location, $request->category_preference);
	if($db->uptadeUserInfo($userModel)){ /** User sucessfully added, returns returns sucess message and newly assigned userId **/
		print_r(json_encode(array('status' => "sucessfull",'userId' => $db->uptadeUserInfo($userModel))));
	}
	else { /** User entered an email that is already in the DB, returns status failed **/
		print_r(json_encode(array('status' => "failed")));
	}
	break;

	case "updateUser":
	$userModel = new userModel();	
	$userModel->addUserValues($request->userId, $request->email, $request->age_group, $request->gender,
		$request->use_of_location, $request->category_preference);
	$db->uptadeUserInfo($userModel);
	if($db->uptadeUserInfo($userModel)){/** User sucessfully updated, returns sucess message and userId **/
		print_r(json_encode(array('status' => "successfull",'userId' => $db->uptadeUserInfo($userModel))));
	}
	else { /** User entered an email that is already in the DB, returns status failed **/
		print_r(json_encode(array('status' => "failed")));
	}
	break;

/** Invoked when frontend is trying to retrive a user instance using email as identifier **/
	case "getUserFromEmail":
	$userFromDB = $db->getUserFromEmail($request->email);

	$userModel = new userModel();
	$userModel->addFromDB($userFromDB);
	$userModel->json_print();
	break;

/** Invoked when frontend is trying to retrive a user instance using a userId as identifier **/
	case "getUserFromId":
	$userFromDB = $db->getUserFromId($request->userId);

	$userModel = new userModel();
	$userModel->addFromDB($userFromDB);
	$userModel->json_print();
	break;

	/**Saves a users rating of a story. A story is only marked as read if the user rates the story,
	if user does not rate the story will be recommended later*/
	case "rating":
	if($request->rating > 0){
		$db->updateOneValue('stored_story', 'rating', $request->rating, array($request->userId, $request->storyId));
		$db->insertUpdateAll('story_state', array($request->storyId, $request->userId, 5));	
	}else {
		$db->insertUpdateAll('story_state', array($request->storyId, $request->userId, 6));
	}
	break;

	/*Add a new tag and connect it to the user, and the story*/
	case "addNewTag":
	$db->insertUpdateAll('user_tag', array($request->userId, $request->tagName));
	$db->insertUpdateAll('user_storytag', array($request->userId, $request->storyId, $request->tagName));
	break;

	/*Tag a story*/
	case "tagStory":
	$db->insertUpdateAll('user_storytag', array($request->userId, $request->storyId, $request->tagName));
	break;

	/*Get all stories connected to a user and the tagName*/
	case "getList":
	$data = $db->getStoryList($request->userId, $request->tagName);
	$returnArray = array();
	foreach($data as $story){
		$list = array(
			'id' => $story['storyId'],
			'title' => $story['title'],
			'description' => $story['introduction'],
			'thumbnail' => "",
			'categories' => "",
			'author' => $story['author'],
			'date' => "");
		if(array_key_exists('categories', $story))
			$list['categories'] = explode(",",$story['categories']);
		if($story['mediaId'] == 1)
			$list['thumbnail'] = "http://api.digitaltmuseum.no/media?owner=H-DF&identifier=".$story['storyId']."&type=thumbnail&api.key=demo";
		array_push($returnArray, $list);
	}
	print_r(json_encode($returnArray));
	break;

	/*Get all tags connected to a user*/
	case "getAllLists":
	$data = $db->getAllSelected('user_tag', 'tagName', array('userId'), array($request->userId));
	$returnArray = array();
	foreach($data as $tag){
		$list = array(
			'text' => $tag['tagName'],
			'checked' => ''
			);
		array_push($returnArray, $list);
	}
	print_r(json_encode($returnArray));
	break;

	/*Get all tags connected to a story for a user*/
	case "getStoryTags":
	$data = $db->getAllSelected('user_storytag', 'tagName', array('userId', 'storyId'), array($request->userId, $request->storyId));
	$returnArray = array();
	foreach($data as $tag){
		$list = array(
			'text' => $tag['tagName'],
			'checked' => true
			);
		array_push($returnArray, $list);
	}
	print_r(json_encode($returnArray));
	break;

	/*Remove a tag connected to a story (remove from list)*/
	case "removeTagStory":
	$db->deleteFromTable('user_storytag', array('userId', 'storyId', 'tagName'), array($request->userId, $request->storyId, $request->tagName));
	break;

	/*Remove a tag (list) altogether for a user, both the connection to the user and for all stories connected to the tag*/
	case "removeTag":
	$db->deleteFromTable('user_storytag', array('userId', 'tagName'), array($request->userId, $request->tagName));
	$db->deleteFromTable('user_tag', array('userId', 'tagName'), array($request->userId, $request->tagName));
	break;

	default: 
	echo "Unknown type";
	break;

}
$db->close();
?>
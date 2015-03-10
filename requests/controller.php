<?php
/**
*Script that intercepts and interprets angular http requests and performe the desired action
*/
require_once(__DIR__."/../models/storyModel.php");
require_once(__DIR__."/../models/userModel.php");
require_once(__DIR__."/../database/dbHelper.php");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$db = new DbHelper();
$postdata = file_get_contents("php://input");
//$postdata = $_POST['data'];
$request = json_decode($postdata);
$type = $request->type;
if($type == "getStory"){
	$storyModel = new storyModel();
	$storyModel->getFromDF($request->storyId);
	print_r (json_encode($storyModel->getAll()));
}
if($type == "getStories"){
	$data = $db->getAllStories();
	$returnArray = array();
	foreach ($data as $story) {
		$list = array(
			'id' => $story['storyId'],
			'title' => $story['title'],
			'description' => $story['introduction'],
			'thumbnail' => "http://api.digitaltmuseum.no/media?owner=H-DF&identifier=".$story['storyId']."&type=thumbnail&api.key=demo",
			'categories' => "",
			'author' => $story['author'],
			'date' => "");
		if(array_key_exists('group_concat(distinct categoryName)', $story))
			$list['categories'] = explode(",",$story['group_concat(distinct categoryName)']);
		array_push($returnArray, $list);
	}
	print_r(json_encode($returnArray));
}
if($type == "addUser"){
	$userModel = new userModel();
	$userModel->setUserId($request->userId);
	$userModel->setMail($request->email);
	$userModel->setAgeGroup($request->age_group);
	$userModel->setGender($request->gender);
	$userModel->setLocation($request->use_of_location);
	$userModel->setCategoryPrefs($request->category_preference);
	$db->uptadeUserInfo($userModel);
}

if($type == "rating"){
	$db->updateOneValue('stored_story', 'rating', $request->rating, array($request->userId, $request->storyId));
}
/*Add a new tag and connect it to the user, and the story*/
if($type == "addNewTag"){
	$db->insertUpdateAll('user_tag', array($request->userId, $request->tagName));
	$db->insertUpdateAll('user_storytag', array($request->userId, $request->storyId, $request->tagName));
}
/*Tag a story*/
if($type == "tagStory"){
	$db->insertUpdateAll('user_storytag', array($request->userId, $request->storyId, $request->tagName));
}
/*Get all stories connected to a user and the tagName*/
if($type == "getList"){
	$returnArray = $db->getStoryList($request->userId, $request->tagName);
	print_r(json_encode($returnArray));
}
/*Get all tags connected to a user*/
if($type == "getAllLists"){
	$returnArray = $db->getAll('user_tag', 'tagName', array('userId'), array($request->userId));
	print_r(json_encode($returnArray));
}
/*Get all tags connected to a story by a user*/
if($type == "getStoryTags"){
	$returnArray = $db->getAll('user_storytag', 'tagName', array('userId', 'storyId'), array($request->userId, $request->storyId));
	print_r(json_encode($returnArray));
}

$db->close();
?>
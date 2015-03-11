<?php
/**
*Script that intercepts and interprets angular http requests and performe the desired action
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
			'thumbnail' => "",
			'categories' => "",
			'author' => $story['author'],
			'date' => "");
		if(array_key_exists('group_concat(distinct categoryName)', $story))
			$list['categories'] = explode(",",$story['group_concat(distinct categoryName)']);
		if($story['mediaId'] == 1)
			$list['thumbnail'] = "http://api.digitaltmuseum.no/media?owner=H-DF&identifier=".$story['storyId']."&type=thumbnail&api.key=demo";
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
		if(array_key_exists('group_concat(distinct categoryName)', $story))
			$list['categories'] = explode(",",$story['group_concat(distinct categoryName)']);
		if($story['mediaId'] == 1)
			$list['thumbnail'] = "http://api.digitaltmuseum.no/media?owner=H-DF&identifier=".$story['storyId']."&type=thumbnail&api.key=demo";
		array_push($returnArray, $list);
	}
	print_r(json_encode($returnArray));
}
/*Get all tags connected to a user*/
if($type == "getAllLists"){
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
}
/*Get all tags connected to a story for a user*/
if($type == "getStoryTags"){
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
}
/*Remove a tag connected to a story (remove from list)*/
if($type == "removeTagStory"){
	$db->deleteFromTable('user_storytag', array('userId', 'storyId', 'tagName'), array($request->userId, $request->storyId, $request->tagName));
}
/*Remove a tag (list) altogether for a user, both the connection to the user and for all stories connected to the tag*/
if($type == "removeTag"){
	$db->deleteFromTable('user_storytag', array('userId', 'tagName'), array($request->userId, $request->tagName));
	$db->deleteFromTable('user_tag', array('userId', 'tagName'), array($request->userId, $request->tagName));
}

$db->close();
?>
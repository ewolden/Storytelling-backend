<?php
/**
*Script that intercepts and interprets angular http requests and performe the desired action
*/
require_once(__DIR__."/../models/storyModel.php");
require_once(__DIR__."/../models/userModel.php");
require_once(__DIR__."/../database/dbHelper.php");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$postdata = file_get_contents("php://input");
//$postdata = $_POST['data'];
$request = json_decode($postdata);
$type = $request->type;

if($type == "getStory"){
	$storyModel = new storyModel;
	$storyModel->getFromDF($request->storyId);
	print_r (json_encode($storyModel->getAll()));
}

if($type == "getStories"){
	$db = new DbHelper();
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
	$userModel->setUserId($request->id);
	$userModel->setMail($request->email);
	$userModel->setAgeGroup($request->age_group);
	$userModel->setGender($request->gender);
	$userModel->setLocation($request->use_of_location);
	$userModel->setCategoryPrefs($request->category_preference);
	$db = new dbHelper();
	$db->uptadeUserInfo($userModel);
	$db->close();
}

?>
<?php
/**
*Script that intercepts and interprets angular http requests and performe the desired action
*/
require_once "database/dbHelper.php";
require_once "models/storyModel.php";
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
	$db = new DbHelper;
	echo json_encode($db->getAllStories());
}

?>
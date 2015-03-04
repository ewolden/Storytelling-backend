<?php
require_once('../models/userModel.php');
$userModel = new userModel();

$json_object = json_decode($_POST["userdata"]);

$userModel->setUserId($json_object->{'UserId'});
$userModel->setMail($json_object->{'email'});
$userModel->setAgeGroup($json_object->{'age_group'});
$userModel->setGender($json_object->{'gender'});
$userModel->setLocation($json_object->{'user_of_location'});
$userModel->setCategoryPrefs($json_object->{'category_preference'});
$userModel->setMediaPrefs($json_object->{'media_ranking'});

$userModel->uptadeUserInfo();
?>
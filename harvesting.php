<?php

/* 
Harvests stories from Digitalt fortalt.
Remember to set constants in the config.php - file
*/


include '/database/dbHelper.php';
//include '/models/storyModel.php';

$db = new dbHelper();

$db->addStoriesToDatabase();

$db->close();

?>
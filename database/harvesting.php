<?php

/* 
Harvests stories from Digitalt fortalt.
Remember to set constants in the config.php - file
*/
require_once 'dbHelper.php';

$db = new dbHelper();

$harvestTime = date('m/d/Y h:i:s');

$db->addStoriesToDatabase($harvestTime);

$db->close();

?>
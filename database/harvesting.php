<?php

/* 
Harvests stories from Digitalt fortalt.
Remember to set constants in the config.php - file
*/
require_once 'dbStory.php';

$dbStory = new dbStory();

$harvestTime = date('m/d/Y h:i:s');

$dbStory->addStoriesToDatabase($harvestTime);

$dbStory->close();

?>
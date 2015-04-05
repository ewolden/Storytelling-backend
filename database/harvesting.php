<?php

/* 
Harvests stories from Digitalt fortalt.
Remember to set constants in the config.php - file
*/
require_once 'dbStory.php';
require_once '../personalization/storySimilarities.php';

$dbStory = new dbStory();

$harvestTime = date('m/d/Y h:i:s');

$dbStory->addStoriesToDatabase($harvestTime);

$dbStory->close();

/*Compute similarities between the harvested stories*/
shell_exec('php ../personalization/storySimilarities.php');

?>
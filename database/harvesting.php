<?php

/* 
Harvests stories from Digitalt fortalt.
Remember to set constants in the config.php - file
*/
include 'dbHelper.php';

$db = new dbHelper();

$db->addStoriesToDatabase();

$db->close();

?>
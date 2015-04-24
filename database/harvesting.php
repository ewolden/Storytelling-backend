<?php

/* 
Harvests stories from Digitalt fortalt.
Remember to set constants in the config.php - file
*/
require_once (__DIR__.'/dbStory.php');

$dbStory = new dbStory();

$harvestTime = date('m/d/Y h:i:s');

$dbStory->addStoriesToDatabase($harvestTime);

/*Compute similarities between the harvested stories (copied from storySimilarities.php)*/
$storyArray = $dbStory->getSubcategoriesPerStory();
$dbStory->close();

/*Put the computed similarities in this file. Will be overwritten every time this script is run*/
$simFile = fopen(__DIR__.'/../personalization/similarities.csv', 'w');

/*Looping through to get all possible pairs of stories*/
for($x=0; $x<sizeof($storyArray); $x++){
	$firstArray = explode(',',$storyArray[$x]['subcategories']);
	$firstId = $storyArray[$x]['numericalId'];
	for($y=$x+1; $y<sizeof($storyArray); $y++){
		$secondArray = explode(',', $storyArray[$y]['subcategories']);
		$secondId = $storyArray[$y]['numericalId'];
		$sim = computeSimilarity($firstArray, $secondArray);
		$txt = $firstId.",".$secondId.",".$sim."\n";
		fwrite($simFile,$txt);
	}
}
fclose($simFile);

/*Compute the similarities between stories. Using cosine similarity for now*/
function computeSimilarity($storyOne, $storyTwo){
	$storyOneLength = sizeof($storyOne);
	$storyTwoLength = sizeof($storyTwo);
	$commonSubCategories = array_intersect($storyOne, $storyTwo);
	$numCommon = sizeof($commonSubCategories);
	$cosineSimilarity = $numCommon/(sqrt($storyOneLength)*sqrt($storyTwoLength));
	return $cosineSimilarity;
}

?>
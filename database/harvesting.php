<?php
/*Contributors: Kjersti Fagerholt, Roar Gjøvaag, Ragnhild Krogh, Espen Strømjordet,
 Audun Sæther, Hanne Marie Trelease, Eivind Halmøy Wolden

 "Copyright 2015 The TAG CLOUD/SINTEF project

 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at

 http://www.apache.org/licenses/LICENSE-2.0

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License."
 */

/**
 * Harvests stories from Digitalt fortalt.
 * Remember to set constants in the config.php - file
 * @author Audun Sæther
 * @author Kjersti Fagerholt
 * @author Eivind Halmøy Wolden
 * @author Hanne Marie Trelease
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
print_r(" ** Done writing the similarities file");

/**
 * Compute the similarities between stories. Using cosine similarity for now
 * @param unknown $storyOne
 * @param unknown $storyTwo
 * @return number similarity value
 */
function computeSimilarity($storyOne, $storyTwo){
	$storyOneLength = sizeof($storyOne);
	$storyTwoLength = sizeof($storyTwo);
	$commonSubCategories = array_intersect($storyOne, $storyTwo);
	$numCommon = sizeof($commonSubCategories);
	$cosineSimilarity = $numCommon/(sqrt($storyOneLength)*sqrt($storyTwoLength));
	return $cosineSimilarity;
}

?>
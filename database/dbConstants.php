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
 * This class define the tables in our database, and the mapping between categories and subcategories.
 * @author Audun Sæther
 * @author Kjersti Fagerholt
 * @author Eivind Halmøy Wolden
 * @author Hanne Marie Trelease
 */
class dbConstants {
	
	/*false = not auto incremented primary key
	* The first number is the number of primary keys in the table
	* It is assumed that the primary key columns are placed first in the table
	*/
	protected $tableColumns = array(
			'story' => array(1,false,'storyId','numericalId','title','author','date','institution','introduction', 'lastChangedTime'),
			'user' => array(1,true,'userId','mail','age_group','gender','use_of_location'),
			'subcategory' => array(1,false,'subcategoryId','subcategoryName'),
			'story_subcategory' => array(2,false,'storyId', 'subcategoryId'),
			'story_dftags' => array(2,false,'storyId', 'DFTagName'),
			'story_media' => array(2,false, 'storyId', 'mediaId'),
			'category_mapping' => array(2,false, 'categoryId', 'subcategoryId'),
			'category_preference' => array(2,false,'userId','categoryId'),
			'media_preference' => array(2,false,'userId','mediaId','ranking'),
			'user_tag' => array(2, false, 'userId', 'tagName'),
			'user_storytag' => array(3,false,'userId', 'storyId', 'tagName'),
			'stored_story' => array(2,false, 'userId', 'storyId', 'explanation', 'rating', 'false_recommend', 'type_of_recommendation','recommend_ranking', 'in_frontend_array'),
			'story_state' => array(1,true, 'recordedStateId', 'storyId', 'userId', 'stateId'),
			'user_usage' => array(1, true, 'usageId', 'userId', 'usageType'),
			'preference_value' => array(2,false,'userId', 'storyId', 'numericalId', 'preferenceValue')
			);
			
	/*The numbers 1-9 are the primary keys in the category table*/
	protected $categoryMapping = array(
			'art and design' => array(1,'bildekunst', 'design og formgjeving', 'film', 'fotografi', 'media', 'teater', 'dans'),
			'architecture' => array(2,'arkitektur'),
			'archeology' => array(3,'arkeologi og forminne'),
			'history' => array(4,'historie', 'historie og geografi', 'sprÃ¥khistorie', 'sjÃ¸fart og kystkultur','kulturminne'),
			'local traditions and food' => array(5,'bunader og folkedrakter', 'hordaland', 'kulturminne', 'kultur og samfunn', 'rallarvegen', 'tradisjonsmat og drikke', 'dans', 'sprÃ¥k', 'fiske og fiskeindustri', 'samer', 'musikk', 'sjÃ¸fart og kystkultur', 'fleirkultur og minoritetar'),
			'nature and adventure' => array(6,'fiske og fiskeindustri', 'naturhistorie', 'sport og friluftsliv', 'fiske og fiskeindustri'),
			'literature' => array(7,'teikneseriar', 'litteratur'),
			'music' => array(8,'musikk'),
			'science and technology' => array(9,'kjÃ¸retÃ¸y, bil og motor, veitransport', 'skip- og bÃ¥tbygging', 'teknikk, industri og bergverk', 'natur, teknikk og nÃ¦ring', 'media', 'fotografi', 'fiske og fiskeindustri'),
			);	
		
	/**
	 * Retrieves columns in table with name $tableName
	 * @param String $tableName
	 * @return columns names of columns in table
	 */
	function getTableColumns($tableName){
		if (!array_key_exists($tableName, $this->tableColumns)){
			return null;
		}
		return $this->tableColumns[$tableName];
	}
	
	/**
	 * Returns an array with the ID's of the categories which contain the given $subcategory
	 * @param unknown $subcategory
	 * @return $categories
	 */
	function getCategories($subcategory){
		$categories = array();
		foreach ($this->categoryMapping as $subCategoryArray){
			$key = in_array($subcategory, $subCategoryArray);
			if($key){
				array_push($categories, $subCategoryArray[0]);
			}
		}
		return $categories;
	}	
}

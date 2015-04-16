<?php

/**
* This class define the tables in our database, and the mapping between categories and subcategories.
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
			'stored_story' => array(2,false, 'userId', 'storyId', 'explanation', 'rating', 'false_recommend', 'type_of_recommendation'),
			'story_state' => array(1,true, 'recordedStateId', 'storyId', 'userId', 'stateId'),
			'user_usage' => array(1, true, 'usageId', 'userId'),
			'preference_value' => array(2,false,'userId', 'storyId', 'numericalId', 'preferenceValue')
			);
			
	/*The numbers 1-9 are the primary keys in the category table*/
	protected $categoryMapping = array(
			'art and design' => array(1,'bildekunst', 'design og formgjeving', 'film', 'fotografi', 'media', 'teater', 'dans'),
			'architecture' => array(2,'arkitektur'),
			'archeology' => array(3,'arkeologi og forminne'),
			'history' => array(4,'historie', 'historie og geografi', 'språkhistorie', 'sjøfart og kystkultur','kulturminne'),
			'local traditions and food' => array(5,'bunader og folkedrakter', 'hordaland', 'kulturminne', 'kultur og samfunn', 'rallarvegen', 'tradisjonsmat og drikke', 'dans', 'språk', 'fiske og fiskeindustri', 'samer', 'musikk', 'sjøfart og kystkultur', 'fleirkultur og minoritetar'),
			'nature and adventure' => array(6,'fiske og fiskeindustri', 'naturhistorie', 'sport og friluftsliv', 'fiske og fiskeindustri'),
			'literature' => array(7,'teikneseriar', 'litteratur'),
			'music' => array(8,'musikk'),
			'science and technology' => array(9,'kjøretøy, bil og motor, veitransport', 'skip- og båtbygging', 'teknikk, industri og bergverk', 'natur, teknikk og næring', 'media', 'fotografi', 'fiske og fiskeindustri'),
			);	
		
	/*Retrieves columns in table with name $tableName*/
	function getTableColumns($tableName){
		if (!array_key_exists($tableName, $this->tableColumns)){
			return null;
		}
		return $this->tableColumns[$tableName];
	}
	
	/*Returns an array with the ID's of the categories which contain the given $subcategory*/
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

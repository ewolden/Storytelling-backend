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

// Database setting constants [DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD]
require_once (__DIR__.'/config.php'); 
require_once (__DIR__.'/dbConstants.php');
header('Content-type: text/plain; charset=utf-8');

/**
 * Class to controll the database connection and provide some general methods
 * for updating, deleting and selecting from the database.
 * @author Audun Sæther
 * @author Kjersti Fagerholt
 * @author Eivind Halmøy Wolden
 * @author Hanne Marie Trelease
 */

class DbHelper extends dbConstants {

    protected $db;	
		
    /**
     * Constructs a new database connection
     */
    public function __construct() {
        $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8';

        try {
            $this->db = new PDO($dsn, DB_USERNAME, DB_PASSWORD, 
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        } catch (PDOException $e) {
            $response["status"] = "error";
            $response["message"] = 'Connection failed: ' . $e->getMessage();
            $response["data"] = null;
            print_r("Connection failed\n");
			print_r($response);
			print_r($e->getTraceAsString());
            exit;
        }
    }
	
    /**
     * Closes the database connection by destroying the PDO object
     */
	public function close(){
		$this->db = null;
	}

	/**
	 * Updates a single column in a table
	 * @param String $tableName	name of table to update
	 * @param String $insertColumn	name of column to update
	 * @param unknown $updateValue	new column value
	 * @param unknown $keyValues define which row to update, might be a string or an array, depending on the number of primary keys.
	 * @return boolean returns true if rows were updated, else false
	 */
	public function updateOneValue($tableName, $insertColumn, $updateValue, $keyValues){
		/*Get the columns in the table we are updating*/
		$tableColumns = $this->getTableColumns($tableName);
		
		/*Find the key columns in the table. Assumes that these columns is placed first in the table
		* and start from number 2 in $tableColumns
		* $tableColumns[0] is the number of primary keys in the table*/
		$keyColumns = array_slice($tableColumns, 2, $tableColumns[0]);
		$whereString = '';
		
		/*If $keyValues is an array we (probably) have a multiple-valued primary key (works with an array of one value as well)
		* We have to loop through the primary key columns to create placeholders.
		*/
		if (is_array($keyValues)){
			$whereString .= ''.$keyColumns[0].'=? ';
			for($x=1; $x<sizeof($keyColumns); $x++){
				$whereString .= 'AND '.$keyColumns[$x].'=? ';
			}	
			$values = array_merge(array($updateValue), $keyValues);
		}
		/*If $keyValues is not an array, we only have one where clause and 
		* we need to create an array for the values we are inserting in the query.*/
		else {
			$keyColumn = implode(',', $keyColumns); 
			$whereString .= ''.$keyColumn.'=? ';
			$values = array($updateValue, $keyValues);
		}
		$query = 'UPDATE '.$tableName.' SET '.$insertColumn.'=? WHERE '.$whereString.'';
		$stmt = $this->db->prepare($query);
		$stmt->execute($values);

		if($stmt->rowCount()<=0) return false;
		else return true;
	}
	
	/**
	 * Insert or update all fields in a table row in the database.
	 * Inserts all values in $valuesArray in table $tableName. 
	 * If the primary key already exists, it updates all other values.
	 * @param String $tableName
	 * @param array $valuesArray values to be updated or inserted. The number of values needs to match the number of columns in the table.
	 * @return boolean returns true if rows were inserted or updated, else false
	 */
    public function insertUpdateAll($tableName,$valuesArray) {
        $columnsArray = array_slice($this->getTableColumns($tableName),1);//Slice off the primary key number
		$cols = implode(",", $columnsArray);
		$cols = trim($cols,","); //Remove the comma before first attribute
				
		$update = array();
		$values = array();
		
		/*Checking if the primary key is auto incremented or not*/
		if($columnsArray[0] == false){
			$insert = '?,';
			/*The first $valuesArray is for the placeholders inside VALUES(), the sliced $valuesArray for the updating placeholders*/
			$values = array_merge($valuesArray, array_slice($valuesArray,1));
		}
		/*If the primary key is auto incremented*/
		else {
			/*If we are updating a row with primary key that exists
			$valuesArray+1 because $columnsArray[0] = the boolean value*/
			if (sizeof($columnsArray) == sizeof($valuesArray)+1){
				/*If we are updating,we need to remove the boolean true but not the primary key 
				* (we need the primary key to know which row to update)*/
				$cols = implode(",", array_slice($columnsArray,1));
				$insert = '?,';
				/*The $valuesArray include the primary key. We need this value in values(?,?,...,?),
				* but not in the updateString, so we have to slice it away*/
				$values = array_merge($valuesArray, array_slice($valuesArray, 1));
			}
			/*If we are inserting a new row*/
			else {
				/* If the primary key is auto incremented, we need to remove the boolean true and the primary key from the array */
				$cols = implode(",", array_slice($columnsArray,2));
				$insert = '';
				/* If the primary key is auto incremented, the parameter $valuesArray doesn't include a key, so we don't need to slice*/
				$values = array_merge($valuesArray, $valuesArray);
			}
		}
		/*Looping through the columns to create placeholders*/
		for ($x = 2; $x < sizeof($columnsArray); $x++){
			/*Creating placeholders for each value for inserting values*/
			$insert .= '?,';
			/*Creating plateholders for each value for updating, except for primary key*/
			$update[] = ''.$columnsArray[$x].'=?';
		}
		$insert = trim($insert,","); //Remove the extra comma at the end
		$updateString = implode(",", $update);
				
		$query = 'INSERT INTO '.$tableName.' ('.$cols.') VALUES ('.$insert.') ON DUPLICATE KEY UPDATE ';
		if(!empty($updateString)){
			$query .= ''.$updateString.'';
		}
		else {
			/*Just a meaningless operation to avoid primary key error*/
			$duplicatePrimary = ''.$columnsArray[1].'='.$columnsArray[1].''; //Means that we are not updating anything
			$query .= ''.$duplicatePrimary.''; 
		}
        $stmt = $this->db->prepare($query);
        $stmt->execute($values);
        
        if($stmt->rowCount()<=0) return false;
		else return true;
    }

	/* Get $selectColumns in $tableName based on $whereValues*/
    /**
     * Returns selected columns from a database table. Get $selectColumns in $tableName based on $whereValues
     * @param String $tableName
     * @param unknown $selectColumns names of columns to retrieve, can be array or single column name
     * @param unknown $whereColumns	names of columns for where statement, can be array or single column name
     * @param unknown $whereValues	values for where statement, can be array or single value string
     * @return multitype:|NULL returns 
     */
	public function getSelected($tableName, $selectColumns, $whereColumns, $whereValues){
		$values = array();
		if (is_array($selectColumns)){
			$selectColumns = implode(",", $selectColumns);
		}
		if(!is_null($whereValues)){
			list($where, $values) = $this->getWhereStringAndValuesArray($whereColumns, $whereValues);
			$query = "SELECT ".$selectColumns." FROM ".$tableName." WHERE ".$where."";
			$stmt = $this->db->prepare($query);
			$stmt->execute($values);
		}
		else{
			$query = "SELECT ".$selectColumns." FROM ".$tableName."";
			$stmt = $this->db->prepare($query);
			$stmt->execute();
		}
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if ($stmt->rowCount() > 0){
			return($rows);
		}
		else {
			return null;
		}
	}
	
	/**
	 * Constructs a where-string with placeholders and an values array
	 * @param unknown $whereColumns	names of columns for where statement, can be array or single column name
     * @param unknown $whereValues	values for where statement, can be array or single value string
	 * @return multitype:string Ambigous <unknown, multitype:unknown >
	 */
	public function getWhereStringAndValuesArray($whereColumns, $whereValues){
		$whereString = "";
		if (is_array($whereValues)){
			if (is_array($whereColumns)){
				$whereString .= ''.$whereColumns[0].'=? ';
				for($x=1; $x<sizeof($whereColumns); $x++){
					$whereString .= 'AND '.$whereColumns[$x].'=? ';
				}
			}
			else {
				$whereString .= ''.$whereColumns.'=? ';
			}
			$values = $whereValues;
		}
		else { 
			if (is_array($whereColumns)){
				$whereColumn = implode(",", $whereColumns);
				$whereString .= ''.$whereColumn.'=? ';
			}
			else {
				$whereString .= ''.$whereColumns.'=? ';
			}
			$values = array($whereValues);
		}	
		return array($whereString, $values);
	}
	
	/**
	 * Delete the rows in $tableName that match the where-clauses
	 * @param String $tableName
	 * @param unknown $whereColumns	names of columns for where statement, can be array or single column name
     * @param unknown $whereValues	values for where statement, can be array or single value string
	 */
	public function deleteFromTable($tableName, $whereColumns, $whereValues){
		list($where, $values) = $this->getWhereStringAndValuesArray($whereColumns, $whereValues);
		$query = "DELETE FROM ".$tableName." WHERE ".$where."";
		$stmt = $this->db->prepare($query);
		$stmt->execute($values);
	}
	
	/**
	 * Insert multiple sets of values in $tableName.
	 * $placeholderString should look like (?,?,?,?),(?,?,?,?),(?,?,?,?), etc
	 * $valuesString should be a string with all the values separated by commas
	 * @param String $tableName
	 * @param String $columns
	 * @param String $placeholderString
	 * @param String $valuesString should be a string with all the values separated by commas
	 */
	public function batchInsert($tableName, $columns, $placeholderString, $valuesString){
		$query = "INSERT INTO ".$tableName." (".$columns.") VALUES ".$placeholderString."";
		$stmt = $this->db->prepare($query);
		$stmt->execute(explode(',',$valuesString));
	}
	
}
//$db->getUserCategories(1);
//print_r('Running');
//$db->insertUpdateAll('category_preference', array(1,2));
//$db->getMailFromId('5');
//$newUser1 = New userModel('6', 'kjerstiii@gmail.com', '1', '1', '0');
//$db->updateUserInfo($newUser1);

//$db->insertUpdateAll('user_storytag', array(1, 'DF.1295', 'test'));
//$db->deleteFromTable('user_storytag', array('userId', 'tagName'), array(1, 'test'));
//$db->deleteFromTable('user_tag', array('userId', 'tagName'), array(1, 'test'));
?>

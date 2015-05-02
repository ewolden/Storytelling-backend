<?php
require_once '../../database/dbUser.php';
require_once '../../models/userModel.php';

class updateRatingsIntegrationTest extends PHPUnit_Framework_TestCase{

	public $dbUser;
	public function setUp(){
		$this->dbUser = new dbUser();
	}

	public function testAddRatings(){

		///////////CREATING NEW USER/////////
		
		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'addUser','email' => null));
 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
 
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postarray);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
 
		$response = curl_exec($ch);
		print_r("\n".$response);
		curl_close($ch);
		$data = json_decode($response,true);

		$id = $data['userId'];
		
		$resultBefore = $this->dbUser->getNumberOfRatedStoriesByThisUser($id);

		///////////RATINGS DONE BY NEW USER WITH $id/////////

		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'rating', 'storyId'=>'DF.5220', 'userId'=>$id, 'rating'=>1));
 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
 
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postarray);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
 
		$response = curl_exec($ch);
		print_r("\n".$response);
		curl_close($ch);
		
		$resultAfter = $this->dbUser->getNumberOfRatedStoriesByThisUser($id);

		//Check if number of rates done by this user is correct
		$this->assertEquals($resultBefore+1,$resultAfter);
		
	}

	public function testRejectStory(){
		///////////CREATING NEW USER/////////
		
		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'addUser','email' => null));
 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
 
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postarray);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
 
		$response = curl_exec($ch);
		curl_close($ch);
		$data = json_decode($response,true);

		$id = $data['userId'];
		
		///////////THE NEW USER REJECTS A STORY ////////

		$url = 'http://188.113.108.37/requests/controller.php';
		$postarray = json_encode(array('type'=>'rejectStory', 'userId'=>$id,'storyId'=>'DF.6081'));
 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
 
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postarray);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
 
		$response = curl_exec($ch);
		print_r("\n".$response);
		curl_close($ch);

		////TEST MORE HERE? DONT KNOW WHAT TO TEST//////
	}
}
?>	
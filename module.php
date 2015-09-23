<?php 
	function getMongoDbConnection(){
		$conn = new Mongo() or die('Error connecting to db');
		return $conn;
	
	}

	function checkIfLoginIsSet(){
		if(isset($_SESSION['userid']))
			return true;
		return false;
	}
	
	function redirect_url($url,$status_code=303){
		header("Location:".$url,true,$status_code);
		die();
	}
?>
<?php
	
	if(!isset($alt_path))
	{
		$alt_path = "";
	}
	
	require($alt_path.'LiteFrameModel.php');
	
	define('DB_SERVER', "");
	define('DB_USER', "");
	define('DB_PASS', "");
	define('DB_DATABASE', "");
	
	//Create the database object	
	$db = new LiteFrameModel(DB_SERVER, DB_DATABASE, DB_USER, DB_PASS);
	
	//Connect to the server
	$db->Connect();

?>
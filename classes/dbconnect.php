<?php
	
	if(!isset($alt_path))
	{
		$alt_path = "";
	}
	
	require($alt_path.'LiteFrameDB.php');
	
	//Create the database object	
	$db = new LiteFrameDB(DB_SERVER, DB_DATABASE, DB_USER, DB_PASS);
	
	//Connect to the server
	$db->Connect();
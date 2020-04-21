<?php

	session_start();
	
	require 'app_config.php';
		
	$obj = new LiteFrameImplementation($db);	
	$obj->AssembleTheme();

?>
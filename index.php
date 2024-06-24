<?php

	session_start();
	
	require 'environment.php';
	require 'app_config.php';
	
	$obj = new Implementation($db);
	
	/* Optional - This builds your website theme and renders the page if it exists */
	$obj->AssembleTheme();

?>
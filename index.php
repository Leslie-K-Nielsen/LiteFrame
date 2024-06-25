<?php

	session_start();
	
	require 'environment.php';
	require 'app_config.php';
	
	$obj = new Implementation($db);
	
	//Set environment variables to initialize the object
	$obj->SetEnvVars($env_vars);

	/* Optional - This builds your website theme and renders the page if it exists */
	$obj->AssembleTheme();

?>
<?php

	session_start();
	
	require 'environment.php';
	require 'app_config.php';
	
	$obj = new Implementation($db);
	
	//Set environment variables to initialize the object
	$obj->SetEnvVars($env_vars);

	//Grab any include level modifiers that may be in the calling dir
	$modifiers['include_level_modifier'] = (isset($add_include_levels)) ? $add_include_levels : false;
	$modifiers['theme_modifier'] = (isset($alternate_theme)) ? $alternate_theme : false;

	/* Optional - This builds your website theme and renders the page if it exists */
	$obj->AssembleTheme($modifiers);
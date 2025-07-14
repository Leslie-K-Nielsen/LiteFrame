<?php

	session_start();
	
	require '../environment.php';
	require '../app_config.php';
		    	
	$obj = new Implementation($db);	
	
	$obj->SetEnvVars($env_vars);
			
	$obj->SetSMTPVals($smtp_variables);
				
	if(isset($_POST['method']) && !empty($_POST['method']))
	{
        switch($_POST['method'])
		{
			case '':
				
				break;		
			default:				
				break;
		}
    }    
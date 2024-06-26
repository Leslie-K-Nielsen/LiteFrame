<?php 

/* Initialization */
	
$instances = array('dev','qa','production');
	
//Dev Instance - Local Machine
$this_instance = $instances[0];

/* SMTP Vars */

//This will be used by PHPMailer to authenticate SMTP
$smtp_variables = array(
    'host' => '',
    'username' => '',
    'password' => ''
);

/* Theme parts */
$theme_parts = array(
    'theme' => array(
        'header-parts' => array(
            'assets-header',
            'content-header',
            'top-navigation'
        ),
        'footer-parts' => array(
            'content-footer',
            'assets-footer'
        )
    ),
    'theme-2' => array(
        'header-parts' => array(
            'assets-header',
            'content-header',
            'aside-navigation'
        ),
        'footer-parts' => array(
            'assets-footer'
        )
    )
);

/* Init Environment Variables */
$env_vars = array(
    'theme_dir' => 'theme',
    'theme_parts' => $theme_parts  
);

/* Instance Dependant Variables */	
switch($this_instance)
{
    case 'dev':
        
        //Dev db vars	
        define('DB_SERVER', "localhost");
        define('DB_USER', "");
        define('DB_PASS', "");
        define('DB_DATABASE', "");
        
        break;
    case 'qa':
        
        //QA db vars	
        define('DB_SERVER', "localhost");
        define('DB_USER', "");
        define('DB_PASS', "");
        define('DB_DATABASE', "");
        
        break;
    case 'production':

        //Production db vars	
        define('DB_SERVER', "localhost");
        define('DB_USER', "");
        define('DB_PASS', "");
        define('DB_DATABASE', "");
        
        break;			
    default:
        break;
}

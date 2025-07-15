<?php 

	/* Application Pre-Assembly */

	require 'classes/dbconnect.php';
	require 'classes/LiteFrameCore.php';
	require 'classes/LFRequests.php';

	/* End Application Pre-Assembly */
	
	
	//--------

	
	/* Controllers */
	
	//Controller Assembler extends to Models
	require 'classes/Controllers/Controller_Assembler.php';

	/* End Controllers */


	//--------

	
	/* Models */

	
	//Model Assembler extends to Views
	require 'classes/Models/Model_Assembler.php';
	
	/* End  Models */

	//--------

	
	/* Views */
	
	require 'classes/Views/Parsers.php';
	
	/* End Views */
	
	
	//--------
	
	
	/* Application Assembly */
	
	require 'classes/Assembler.php';
	
	/* End Application Assembly */
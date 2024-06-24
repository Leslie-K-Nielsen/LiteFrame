<?php

	class LiteFrameCore
	{
	
		/* GLOBALS */

		//Bringing in database object for this class
		public $db_object;
		public $key_len;
		
		public $file_level;		
		public $dynamic_header_text;
		public $user_host;
		public $default_nav_uri;
		public $dev_uri;
				
		/* END OF GLOBALS */
		
		
		/* ------------------------------- */
		
		
		/* CONSTRUCTOR */
		
		function __construct($db)		
		{
			$this->db_object = $db;			
			$this->user_host = $_SERVER['HTTP_HOST'];
			$this->default_nav_uri = "home";
			$this->dev_uri = "";//name this after the directory of dev or sub folder
		}
		
		/* END OF CONSTRUCTOR */
		
		
		/* ------------------------------- */
		
	
		/* UTILITY */
	
		//Get users REAL ip address
		function GetRealIpAddr()
		{
			if (!empty($_SERVER['HTTP_CLIENT_IP']))//check ip from share internet
			{
				return $_SERVER['HTTP_CLIENT_IP'];
			}
			elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))//to check ip is pass from proxy
			{
				return $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
			else
			{
				return $_SERVER['REMOTE_ADDR'];
			}			
		}
		
		//Generated random key value for any purpose
		function GenerateKey($kl = 9)
		{
			$this->key_len = $kl;
			$vowels = 'aeiouyAEIOU';
			$consonants = 'bcdfghjklmnpqrstvwxzBCDFGHJKLMNPRSTVWXZ234567890@';
			$alt = time() % 2;
			$keyvalue = "";
			
			for ($i = 0; $i < $this->key_len; $i++) 
			{
				if ($alt == 1) 
				{
					$keyvalue.= $consonants[(rand() % strlen($consonants))];
					$alt = 0;
				} 
				else 
				{
					$keyvalue.= $vowels[(rand() % strlen($vowels))];
					$alt = 1;
				}
			}
				
			return $keyvalue;
		}
		
		function SysSetCookie($name, $value, $expires)
		{
			setcookie($name, $value, $expires);
		}
		
		function GetArrayValueCount($assoc_idx, $value, $array)
		{
			$num_needles = 0;
			
			foreach($array as $haystack)
			{
				if(isset($haystack[$assoc_idx]) && $haystack[$assoc_idx] == $value) $num_needles++;
			}
			
			return $num_needles;
		}
		
		function CheckForExistingSQL($table, $fields, $value = false)
		{
			if(!$value && is_array($fields))
			{
				$where = " WHERE ";
				$idx = 0;
				
				foreach($fields as $name => $value)
				{
					$where.= ($idx == 0) ? $name." = '".$value."'" : " AND ".$name." = '".$value."'";
					$idx++;
				}
				
				$sql = "SELECT * FROM ".$table.$where;
				$result = $this->db_object->QueryFirstRow($sql);	
				
			}
			else
			{
				$sql = "SELECT * FROM ".$table." WHERE ".$fields." = '".$value."'";
				$result = $this->db_object->QueryFirstRow($sql);	
			}
				
			return count($result);
		}
				
		function P($data)
		{
			echo "<pre>";
			print_r($data);
			echo "</pre>";
		}
		
		/* END UTILITY */
			
		/* ------------------------------- */			
		
		
		/* GET VALUES */
		
		function GetFileLevel()
		{
			return $this->file_level;
		}
		
		/* END OF GET VALUES */
		
		
		/* ------------------------------- */			
		
		
		/* DISPLAY GET VALUES */
		
		function DisplayFileLevel()
		{
			echo $this->GetFileLevel();
		}
		
		/* END OF DISPLAY GET VALUES */
		
		
		/* ------------------------------- */			
		
		
		/* CONTROLLER FUNCIONS */
		
		function ThemeHeader()
		{
			$pages = array('assets-header','content-header','main-navigation');
			
			for($p = 0; $p < count($pages); $p++)
			{
				if(file_exists($this->file_level.$pages[$p].".php"))
				{
					include $this->file_level.$pages[$p].".php";
				}
			}		
		}
		
		function ThemeFooter()
		{
			$pages = array('content-footer','assets-footer');
			
			for($p = 0; $p < count($pages); $p++)
			{
				if(file_exists($this->file_level.$pages[$p].".php"))
				{
					include $this->file_level.$pages[$p].".php";
				}
			}			
		}
		
		function LoadPage()
		{
			$request_uri = $this->FetchCleanRequestURI();			
						
			if($request_uri == '/')
			{
				include "home/content.php";				
			}
			else
			{
				include "content.php";				
			}			
		}
		
		function AssembleTheme()
		{
			$request_uri = $this->FetchCleanRequestURI();			
			
			if($request_uri == '/')
			{
				//Uncomment to lock down home directory
				//$this->EnforceAuthentication();
				$this->file_level = '';				
			}
			else
			{
				$this->file_level = '../';				
			}
						
			//Load theme and current page content
			$this->ThemeHeader();
			$this->LoadPage();
			$this->ThemeFooter();
		}		
		
		function ConditionalCSSAsset($uri, $asset, $cdn = false)
		{
			$clean_uri = str_ireplace('/','',$this->FetchCleanRequestURI());
			
			if(is_array($uri))
			{
				for($i = 0; $i < count($uri); $i++)
				{
					if($clean_uri == $uri[$i])
					{
						if($cdn)
						{
							echo "<link href=\"{$asset}\" id=\"theme\" rel=\"stylesheet\">";
						}
						else
						{
							echo "<link href=\"{$this->GetFileLevel()}{$asset}\" id=\"theme\" rel=\"stylesheet\">";	
						}
					}	
				}
			}
			else
			{
				if($clean_uri == $uri)
				{
					if($cdn)
					{
						echo "<link href=\"{$asset}\" id=\"theme\" rel=\"stylesheet\">";
					}
					else
					{
						echo "<link href=\"{$this->GetFileLevel()}{$asset}\" id=\"theme\" rel=\"stylesheet\">";	
					}
				}
			}
			
		}
		
		function DisplayConditionalCSSAsset($uri, $asset, $cdn = false)
		{
			echo $this->ConditionalCSSAsset($uri, $asset, $cdn);
		}

		function ConditionalScriptAsset($uri, $asset, $cdn)
		{
			$clean_uri = str_ireplace('/','',$this->FetchCleanRequestURI());
			
			if(is_array($uri))
			{
				for($i = 0; $i < count($uri); $i++)
				{
					if($clean_uri == $uri[$i])
					{
						if($cdn)
						{
							echo "<script src=\"{$asset}\"></script>";
						}
						else
						{
							echo "<script src=\"{$this->GetFileLevel()}{$asset}\"></script>";
						}
					}	
				}
			}
			else
			{
				if($clean_uri == $uri)
				{
					if($cdn)
					{
						echo "<script src=\"{$asset}\"></script>";	
					}
					else
					{
						echo "<script src=\"{$this->GetFileLevel()}{$asset}\"></script>";	
					}
				}
			}			
		}
		
		function DisplayConditionalScriptAsset($uri, $asset, $cdn = false)
		{
			echo $this->ConditionalScriptAsset($uri, $asset, $cdn);
		}
		
		function ConditionalInlineJSAsset()
		{
			$active_page = $this->FetchCleanRequestURI();			
			$active_page = str_replace('/','',$active_page);			
			$directory = ($active_page == '') ? 'home/' : '';			
			
			if(file_exists("{$directory}inlinejs.php"))
			{
				include "{$directory}inlinejs.php";
			}
		}
				
		function SetActiveMenuItem($page)
		{
			$active_page = $this->FetchCleanRequestURI();
			
			$active_page = str_replace('/','',$active_page);			
			
			$class_output = "";
			
			if(is_array($page))
			{
				for($i = 0; $i < count($page); $i++)
				{
					if($active_page == $page[$i])
					{
						$class_output = ' active';	
					}
				}
			}
			else if($active_page == $page)
			{
				$class_output = 'class="active"';
			}
			
			echo $class_output;
		}		
		
		function FetchCleanRequestURI()
		{
			$request_uri = $_SERVER['REQUEST_URI'];
			/*
			If you have more than one version and one is in a subdirectory, 
			uncomment the line below and replace [ALTDIRECTORY] with the name of the subdirectory the application lives in
			*/
			if(stristr($request_uri, '?'))
			{
				$request_uri = substr($request_uri, 0, strpos($request_uri, "?"));//Strip the query string if exists
			}		
			//$request_uri = str_replace('/[ALTDIRECTORY]','',$request_uri);			
			
			return $request_uri;
		}
		
		//SEO Funcitons
		function DisplayPageTitleTagString()
		{
			//"Page URI" => "Title Tag Text"
			$page_titles = array(
				"" => ""				
			);
			
			$request_uri = $this->FetchCleanRequestURI();						
						
			//Set default title tag text
			$title_tag_text = "";
			
			foreach($page_titles as $name => $value)
			{
				if($name == $request_uri) $title_tag_text = $value;
			}
			
			echo $title_tag_text;
		}
		
		function DisplayPageMetaDescription()
		{
			//"Page URI" => "Meta Description Text"
			$meta_descriptions = array(
				"" => ""
			);
			
			$request_uri = $this->FetchCleanRequestURI();
						
			//Set default meta description text
			$meta_desc_text = "";
			
			foreach($meta_descriptions as $name => $value)
			{
				if($name == $request_uri) $meta_desc_text = $value;
			}
			
			echo $meta_desc_text;			
		}		
		
		/* END CONTROLLER FUNCIONS */
		
		
		/* ------------------------------- */	
		
		function SaveFormSubmission()
		{
			$is_valid = true;
			$message = "";			
			
			if(isset($_POST['form_data']) && !empty($_POST['form_data']))
			{
				foreach($_POST['form_data'] as $item)
				{
					
					$key = $item['name'];
					$value = $item['value'];

					$key = explode("_", $key, 2);
					$datatype = $key[0];							
					$name = ($datatype == 'r') ? substr($item['name'],2) : $item['name'];							
							
					if($datatype == 'r' && $value == "")
					{
						$is_valid = '0';
						$message = "Please make sure you enter all required fields.";
					}
					else
					{
						//Save Submitted Form Data
					}
				}
			}
			
			return '{"is_valid":'.$is_valid.',"message":"'.$message.'"}';
		}
	}	
?>
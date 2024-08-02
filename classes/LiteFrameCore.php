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
		public $user_uri;
		
		public $instance;

		public $theme_parts;
		public $theme_dir;
		public $theme_dir_parts;

		public $dev_app_path;
		public $qa_app_path;
		public $production_app_path;

		/* END OF GLOBALS */
		
		
		/* ------------------------------- */
		
		
		/* CONSTRUCTOR */
		
		function __construct($db)		
		{
			$this->db_object = $db;			
			$this->user_host = $_SERVER['HTTP_HOST'];
			$this->user_uri = $_SERVER['REQUEST_URI'];
		}
		
		/* END OF CONSTRUCTOR */
		
		
		/* ------------------------------- */


		/* INIT ENVIRONMENT */

		public function SetEnvVars($env_vars)
		{
			$this->instance = $env_vars['instance'];
			
			//Theme assembly
			$this->theme_dir = $env_vars['theme_dir'];		
			$this->theme_parts = $env_vars['theme_parts'];	
			$this->theme_dir_parts = $env_vars['theme_parts'][$this->theme_dir];

			//For instances running in a subdirectory
			$this->dev_app_path = $env_vars['dev_app_path'];
			$this->qa_app_path = $env_vars['qa_app_path'];
			$this->production_app_path = $env_vars['production_app_path'];			
		}
		
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
		
		
		/* THEME CONTROLLER FUNCIONS */
		
		function ThemeMergeCallback($html)
		{
			$html = (str_replace("{{incfilelevel}}", $this->file_level, $html));

			//Set alternate theme name
			if(stristr($html, '{{active-theme-dir}}'))
			{
				$html = (str_replace("{{active-theme-dir}}", $this->theme_dir, $html));
			}

			//Set theme location uri 
			if(stristr($html, '{{theme-deployment-dir}}') && $this->theme_dir_parts['deployments'])
			{
				foreach($this->theme_dir_parts['deployments'] as $deployment)
				{
					echo "DEP {$deployment} URI {$this->user_uri} <br />";
					if(stristr($this->user_uri, $deployment))
					{
						$deployment_dir = '/' . $deployment . '/';
						
						$html = (str_replace("{{theme-deployment-dir}}", $deployment_dir, $html));
					}					
				}				
			}

			return $html;
		}

		function ThemeHeader()
		{
			$pages = $this->theme_dir_parts['header-parts'];
			
			$html = "";

			for($p = 0; $p < count($pages); $p++)
			{
				if(file_exists($this->file_level.$this->theme_dir.'/'.$pages[$p].".php"))
				{
					ob_start();

					include $this->file_level.$this->theme_dir.'/'.$pages[$p].".php";

					$html.= ob_get_clean();
				}
			}
			
			$html = $this->ThemeMergeCallback($html);

			return $html;
		}
		
		function ThemeFooter()
		{
			$pages = $this->theme_dir_parts['footer-parts'];

			$html = "";

			for($p = 0; $p < count($pages); $p++)
			{
				if(file_exists($this->file_level.$this->theme_dir.'/'.$pages[$p].".php"))
				{
					ob_start();

					include $this->file_level.$this->theme_dir.'/'.$pages[$p].".php";

					$html.= ob_get_clean();
				}
			}
			
			$html = $this->ThemeMergeCallback($html);

			return $html;
		}
		
		function LoadPage()
		{
			ob_start();

			$request_uri = $this->FetchCleanRequestURI();			
						
			if($request_uri == '/')
			{
				if(file_exists("home/content.php"))
				{
					include "home/content.php";
				}								
			}
			else
			{
				if(file_exists("content.php"))
				{
					include "content.php";
				}				
			}
			
			$html = ob_get_clean();
			
			return $html;
		}
		
		function AssembleTheme($modifiers)
		{
			$request_uri = $this->FetchCleanRequestURI();			
			
			if($request_uri == '/')
			{
				//Uncomment to lock down home directory
				$this->file_level = '';				
			}
			else
			{
				$this->file_level = '../';				
			}
						
			if($modifiers['include_level_modifier'])
			{
				for($m = 0; $m < $modifiers['include_level_modifier']; $m++)
				{
					$this->file_level.= '../';
				}
			}

			if($modifiers['theme_modifier'])
			{
				$this->theme_dir = $modifiers['theme_modifier'];		
				$this->theme_dir_parts = $this->theme_parts[$this->theme_dir];
			}

			//Load theme and current page content
			$page_output = $this->ThemeHeader();
			$page_output.= $this->LoadPage();
			$page_output.= $this->ThemeFooter();

			$this->Display($page_output);
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
			
			//Sets path for instances located in a subdirectory as part of the resolving domain or localhost
			switch($this->instance)
			{
				case 'dev':
					if(!empty($this->dev_app_path))
					{
						$request_uri = str_replace('/'.$this->dev_app_path,'',$request_uri);
					}
					break;
				case 'qa':
					if(!empty($this->qa_app_path))
					{
						$request_uri = str_replace('/'.$this->qa_app_path,'',$request_uri);
					}	
					break;
				case 'production':
					if(!empty($this->production_app_path))
					{
						$request_uri = str_replace('/'.$this->production_app_path,'',$request_uri);
					}
					break;			
				default:
					break;	
			}						
			
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

		function ImplodeAssociativeArray($array, $index, $glue, $surroundchar = "", $numeric_idx = false)
		{
			$output_string = "";

			if($numeric_idx)
			{
				if(isset($array[0]))
				{
					for($i = 0; $i < count($array); $i++)
					{
						$output_string.= $surroundchar . $array[$i] . $surroundchar . $glue;	
					}
	
					$output_string = rtrim($output_string, ",");
				}
			}
			else
			{
				if(isset($array[0]))
				{
					foreach($array as $row)
					{
						$output_string.= $surroundchar . $row[$index] . $surroundchar . $glue;	
					}
	
					$output_string = rtrim($output_string, ",");
				}
			}		

			return $output_string;
		}
	}	
?>
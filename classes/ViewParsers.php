<?php

	class ViewParsers extends LiteFrameCore
	{
		/* OUTPUT PARSING AND DISPLAY */
		
		//Simple display - abstracts native code
		function Display($value, $format = false)
		{
			if($format)
			{
				switch($format)
				{
					case 'standard-date':
						echo date("n/j/Y", $value);
						break;
					case 'standard-time':	
						echo date("g:i A", $value);
						break;
					case 'html_entity_decode':
						echo html_entity_decode($value);
						break;	
					case 'ucwords':
						echo ucwords($value);
						break;
					case 'jsonencode':
						echo json_encode($value);
						break;
					case 'jsondecode':
						echo json_decode($value, true);
						break;	
					default:
						break;
				}
			}
			else
			{
				echo $value;
			}
		}
		
		function FetchTemplate($location, $template_name)
		{
			return file_get_contents($this->GetFileLevel().'templates/'.$location.'/'.$template_name.'.php');
		}
		
		function ParseGeneralSelectObject($array, $markup, $selected_id)
		{
			$output = "";
			
			foreach($array as $row)
			{
				$tmp_str = str_ireplace("[label]", $row['name'], $markup);
				$tmp_str = str_ireplace("[id]", $row['value'], $tmp_str);
				
				if($selected_id == $row['value'])
				{
					$tmp_str = str_ireplace("[selected]", " selected", $tmp_str);
				}
				else
				{
					$tmp_str = str_ireplace("[selected]", "", $tmp_str);
				}
				
				$output.= $tmp_str;
			}
			
			return $output;
		}
				
		function DisplayGeneralSelectObject($array, $markup, $selected_id = false)
		{
			echo $this->ParseGeneralSelectObject($array, $markup, $selected_id);
		}
		
		function ParseGeneralCheckboxObject($array, $markup, $checked_ids)
		{
			$output = "";
			
			foreach($array as $row)
			{
				$tmp_str = str_ireplace("[label]", $row['name'], $markup);
				$tmp_str = str_ireplace("[id]", $row['value'], $tmp_str);
				
				
				if($checked_ids == $row['value'])
				{
					$tmp_str = str_ireplace("[checked]", " checked", $tmp_str);
				}
				else
				{
					$tmp_str = str_ireplace("[checked]", "", $tmp_str);
				}
				
				$output.= $tmp_str;
			}
			
			return $output;
		}
				
		function DisplayGeneralCheckboxObject($array, $markup, $checked_ids = false)
		{
			echo $this->ParseGeneralCheckboxObject($array, $markup, $checked_ids);
		}
		
		function ParseGeneralArrayList($array, $markup)
		{
			$output = "";
			
			for($i = 0; $i < count($array); $i++)
			{
				$tmp_str = str_ireplace("[label]", $array[$i], $markup);			
				
				$output.= $tmp_str;
			}
			
			return $output;
		}
		
		function DisplayGeneralArrayList($array, $markup)
		{
			echo $this->ParseGeneralArrayList($array, $markup);
		}

		function ParseGeneralTextboxObject($array, $markup)
		{
			$output = "";
			
			foreach($array as $row)
			{
				$tmp_str = str_ireplace("[name]", $row['name'], $markup);
				$tmp_str = str_ireplace("[value]", $row['value'], $tmp_str);
				$tmp_str = str_ireplace("[label]", $row['label'], $tmp_str);
				
				$output.= $tmp_str;
			}
			
			return $output;
		}
		
		function DisplayGeneralTextboxObject($array, $markup)
		{
			echo $this->ParseGeneralTextboxObject($array, $markup);
		}
		
		function ParseTableObject($array, $markup, $fields, $date_format, $custom_fields)
		{
			$output = "";
			
			if(!empty($array))
			{
				foreach($array as $row)
				{
					for($j = 0; $j < count($fields); $j++)
					{
						$idxlbl = "[".$fields[$j]."]";
						
						//Formatting
						if(stristr($fields[$j], 'date'))
						{
							$value = date($date_format, $row[$fields[$j]]);
						}				
						else
						{
							$value = $row[$fields[$j]];
						}					
						
						if(isset($custom_fields) && in_array($fields[$j], $custom_fields))
						{
							$value = $this->ParseCustomFields($fields[$j], $value);
						}
						
						if($j == 0)
						{
							$tmp_str = str_ireplace($idxlbl, $value, $markup);
						}
						else
						{
							$tmp_str = str_ireplace($idxlbl, $value, $tmp_str);
						}
					}
					
					$output.= $tmp_str;
				}				
			}
				
			return $output;
		}
		
		function ParseCustomFields($name, $value)
		{
			switch($name)
			{
				case 'source':
					return ($value == 'manual') ? "Manually Entered" : "Online Submission";
					break;		
				case 'show_review':
					return ($value) ? "Yes" : "No";
					break;							
				default:
					break;
			}			
		}
		
		function DisplayTableObject($array, $markup, $fields, $date_format = false, $custom_fields = array())
		{
			echo $this->ParseTableObject($array, $markup, $fields, $date_format, $custom_fields);					
		}
						
		function ParseGeneralRepeatingObject($array, $markup, $fields, $custom_fields, $checked)
		{
			$output = "";
			
			if(!empty($array))
			{
				foreach($array as $row)
				{
					for($j = 0; $j < count($fields); $j++)
					{
						$idxlbl = "[".$fields[$j]."]";
												
						if(isset($custom_fields) && in_array($fields[$j], $custom_fields))
						{
							$value = $this->ParseCustomFields($fields[$j], $value);
						}
						else
						{
							$value = $row[$fields[$j]];
						}
						
						if($j == 0)
						{
							$tmp_str = str_ireplace($idxlbl, $value, $markup);
						}
						else
						{
							$tmp_str = str_ireplace($idxlbl, $value, $tmp_str);
						}
					}
					
					//Handle selected
					if($checked && $checked == $row['id'])
					{
						$tmp_str = str_ireplace('[checked]', 'checked', $tmp_str);
					}
					else
					{
						$tmp_str = str_ireplace('[checked]', '', $tmp_str);
					}
					
					$output.= $tmp_str;
				}				
			}
				
			return $output;
		}
		
		function DisplayGeneralRepeatingObject($array, $markup, $fields, $custom_fields = array(), $selected_id = false)
		{
			echo $this->ParseGeneralRepeatingObject($array, $markup, $fields, $custom_fields, $selected_id);
		}			
		
		/* END OUTPUT PARSING AND DISPLAY */
		
		/* --------------- */
		
		/* PRE-PARSERS */
		
		// Select Object
		function PreParseForSelect($array, $fields)
		{
			$name_idx = $fields[0];
			$val_idx = $fields[1];
			$idx = 0;
			
			foreach($array as $row)
			{
				$results[$idx]['name'] = $row[$name_idx];
				$results[$idx]['value'] = $row[$val_idx]; 				
				$idx++;
			}
			
			return $results;
		}
		
		// Select Object
		function PreParseForTextbox($array, $fields)
		{
			$name_idx = $fields[0];
			$val_idx = $fields[1];
			$lbl_idx = $fields[2];
			$idx = 0;
			
			foreach($array as $row)
			{
				$results[$idx]['name'] = $row[$name_idx];
				$results[$idx]['value'] = (isset($row[$val_idx])) ? $row[$val_idx] : '';
				$results[$idx]['label'] = (isset($row[$lbl_idx])) ? $row[$lbl_idx] : '';
				$idx++;
			}
			
			return $results;
		}
		
		/* END PRE-PARSERS */
	}

?>
<?php 

	/* 
		LiteFrame by Leslie K. Nielsen			
		Rights to the original framework in all forms remains in place with a GNU Public license.
	*/

class LiteFrameDB
{
	
	/* CLASS DATA */
	
	public $server;
	public $db_name;
	public $db_username;
	public $db_password;
	
	//internal info
	public $error = "";
	public $errno = 0;

	public $connection = 0;
	public $query_id = 0;
	
	/* END DATA */
	
	/* INITIALIZATION & HOUSEKEEPING */
	
	function __construct($server, $db_name, $db_username, $db_password)
	{
		$this->server = $server;
		$this->db_name = $db_name; 
		$this->db_username = $db_username;
		$this->db_password = $db_password;
	}
	
	function Connect($new_link = false) 
	{
		$this->connection = mysqli_connect($this->server, $this->db_username, $this->db_password, $new_link);

		if (!$this->connection) 
		{
			$this->ThrowError("20022003");
		}

		if(!mysqli_select_db($this->connection, $this->db_name)) 
		{
			$this->ThrowError("20022003");
		}

		//Unset the data so it can't be dumped
		$this->server = '';
		$this->db_name = ''; 
		$this->db_username = '';
		$this->db_password = '';
	}
	
	function CloseConnection()
	{
		if(!@mysqli_close($this->connection))
		{
			$this->ThrowError("noclose");
		}	
	}
	
	function RealEscape($string)
	{
		return mysqli_real_escape_string($this->connection, $string);
	}
	
	/* BASIC DATABASE OPERATIONS */
	
	function Query($sql)
	{
		$this->query_id = mysqli_query($this->connection, $sql);
		
		if (!$this->query_id) 
		{
			//error
			return 0;
		}
		
		return $this->query_id;
	}
	
	function QueryFirstRow($sql)
	{
		$query_id = $this->Query($sql);
		$out = $this->FetchRecAssoc($query_id);
		//$this->free_result($query_id);
		
		return $out;
	}
	
	function FetchRecAssoc($sql = false) 
	{
		if (isset($sql)) 
		{
			$record = mysqli_fetch_assoc($sql);
		}
		else
		{
			$this->ThrowError("invalidid");
		}

		return $record;
	}

	function FetchAllAssoc($sql)
	{
		$query_id = $this->Query($sql);
		$assoc_output = array();
		
		while ($row = $this->FetchRecAssoc($query_id))
		{
			$assoc_output[] = $row;
		}

		//$this->free_result($query_id);
		return $assoc_output;	
	}

	/* ACTION QUERIES */
	
	//Insert into a table
	public function SQLInsert($table = false, $nvpair = false) 
	{
		if($table && $nvpair)
		{
			$insert_statment = "INSERT INTO ".$table." ";
			$vals = ""; 
			$cols = "";

			foreach($nvpair as $name => $value) 
			{
				$cols.="$name, ";
				
				if(strtoupper($value) == 'NULL') 
				{	
					$vals.="NULL, ";
				}
				else
				{		
					$vals.= "'".mysqli_real_escape_string($this->connection, $value)."', ";
				}	
			}

			$insert_statment .= "(". rtrim($cols, ', ') .") VALUES (". rtrim($vals, ', ') .");";
			//echo $insert_statment; //for debugging
			$result = $this->Query($insert_statment);
		
			if($result)
			{
				return $this->GetLastInsertId();				
			}
			else
			{		
				return false;	
			}	
		}
	}
	
	//Update values in a table
	public function SQLUpdate($table = false, $nvpair = false, $where = "1") 
	{
		$update_statement = "UPDATE ".$table." SET ";
		
		foreach($nvpair as $name => $value) 
		{
			if(strtolower($value)=='null')
			{
				$update_statement.= "$name = NULL, ";
			}	
			else
			{
				$update_statement.= "$name='".mysqli_real_escape_string($this->connection, $value)."', ";
			}
		}
		
		$update_statement = rtrim($update_statement, ', ') . ' WHERE '.$where.';';
		//echo $update_statement; //for dugugging
		return $this->Query($update_statement);
	}
	
	function SQLDeleteRecordSimple($table, $field, $value)
	{
		$sql = "DELETE FROM ".$table." WHERE ".$field." = '".$value."'";
		return $this->Query($sql);		
	}
	
	/* UTILITY */
	
	function GetLastInsertId()
	{
		return mysqli_insert_id($this->connection);
	}
	
	function NumRows()
	{
		if($this->query_id)
		{
			return mysqli_num_rows($this->query_id);
		}
		else
		{
			return 0;
		}
	}
		
	function AffectedRows()
	{
		return mysqli_affected_rows($this->query_id);
	}
	
	//Check error code and throw message back
	public function ThrowError($error_code)
	{
		if($this->connection>0)
		{
			$this->error=mysqli_error($this->connection);
			$this->errno=mysqli_errno($this->connection);
		}
		
		switch($error_code)
		{
			case '20022003':
				//Either no MySQL server running or Network connection has been refused.
				$message = '{"error_msg":"Either no MySQL server running or Network connection has been refused."}';	
				break;
			case 'noclose':
				//Can't close the connection.				
				$message = '{"error_msg":"The MySQL connection could not be closed at this time"}';	
				break;				
			case 'invalidid':
				//Bad id.				
				$message = '{"error_msg":"Invalid Id, could not execute."}';	
				break;				
			default:
				break;
		}
	}
}

?>
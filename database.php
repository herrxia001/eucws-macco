<?php

$root_db = "zvhjub8k_root";
$domain = "https://www.moda-macco.com/";

class myDatabase
{
	private $dbHost  		= "127.0.0.1:3306";
    private $dbUser  		= "zvhjub8k_admin";

    private $dbPassword   	= "moda-prato23#";
    private $dbName  		= "";  
	private $dbConnect 		= false;
	
	public function __construct($databaseName)
	{
		$this->dbName = $databaseName;
    }
	
	public function dbClose()
	{
		$sqlConnect = $this->dbConnect;
		$sqlConnect->close();
	}
		
	private function dbProcess($sqlQuery, $option)
	{
		if(!$this->dbConnect)
		{ 
			$mysqli = new mysqli($this->dbHost, $this->dbUser, $this->dbPassword, $this->dbName);
			if($mysqli->connect_errno)
			{  
				$errmsg = 'Error dbConnect: '.$mysqli->connect_error;
				writeLog($errmsg);
				return -1;
			}
			$mysqli->set_charset("utf8");
			$this->dbConnect = $mysqli;
		}
		
		$result = mysqli_query($this->dbConnect, $sqlQuery);
		if(!$result)
		{
			$sqlConnect = $this->dbConnect;
			$errmsg = 'Error dbQuery: '.$sqlConnect->error." SQL=".$sqlQuery;
			writeLog($errmsg);
			return -1;
		}
		// insert_id
		if($option)
		{
			 $last_id = mysqli_insert_id($this->dbConnect);
			 return $last_id;
		}
		
		return $result;
	}
	
	public function dbQuery($sqlQuery)
	{
		$result = $this->dbProcess($sqlQuery, 0);
		if($result  < 0)
			return -1;

		if($result->num_rows == 0)
			return 0;
		
		$data= array();
		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
			$data[]=$row;  

		return $data;
	}
	
	public function dbUpdate($sqlUpdate)
	{
		$result = $this->dbProcess($sqlUpdate, 0);
		if($result <= 0)
			return FALSE;
	   
		return TRUE;
	}

	public function dbInsert($sqlInsert)
	{
		$result = $this->dbProcess($sqlInsert, 0);
		if($result <= 0)
			return FALSE;
	   
		return TRUE;
	}
	
	public function dbInsertId($sqlInsert)
	{
		$last_id = $this->dbProcess($sqlInsert, 1);
		if($last_id <= 0)
			return 0;
	   
		return $last_id;
	}
}

function writeLog($log)
{
	/*if($_SESSION['uDb']=='')
		$myPath = "c:\\log\\".date('Ymd').".log";
	else
		$myPath = "c:\\log\\".$_SESSION['uDb']."/".date('Ymd').".log";
	$myfile = fopen($myPath, "a");
	if(!$myfile)
	{	printf($myPath);
		return false;
	}
	$myLog = date('Y-m-d H:i:s').": ".$log."\n"; 
	fwrite($myfile, $myLog);
	fclose($myfile);*/
	
	return true;
}

?>

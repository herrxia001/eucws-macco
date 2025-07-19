<?php

include_once 'database.php';

// User
function dbLogin($user, $password, $tabID = '')
{
	global $root_db;
	$thisDb = new myDatabase($root_db);
	$sqlQuery = "SELECT * FROM users WHERE u_name='".strtolower($user)."'";
    $userData = $thisDb->dbQuery($sqlQuery);

	if($userData <= 0)
		return FALSE;	
	if(!password_verify($password, $userData[0]['u_password']))
		return FALSE;

	if($tabID != ""){
		$sqlQuery = "UPDATE users SET tabID = '".$tabID."' WHERE u_id = '".$userData[0]['u_id']."'";
		$thisDb->dbQuery($sqlQuery);
		$_SESSION['tabID'] = $tabID;
	}

	$_SESSION['uId'] = $userData[0]['u_id']; 
	$_SESSION['cId'] = $userData[0]['c_id']; 
	$_SESSION['uName'] = $userData[0]['u_name']; 
	$_SESSION['uDb'] = $userData[0]['u_db']; 
	$_SESSION['uLanguage'] = $userData[0]['lan']; 
	$company = dbQueryCompany();
	$_SESSION['myCompany'] =  $company;
	$_SESSION['uRole'] = intval($userData[0]['role']); 
	$_SESSION['version'] = intval($company['version']);

	$_SESSION['printerName'] = $userData[0]['printerName']; 
	$_SESSION['paperWidth'] = $userData[0]['paperWidth']; 
	$_SESSION['paperHeight'] = $userData[0]['paperHeight']; 
	$_SESSION['codeWidth'] = $userData[0]['codeWidth']; 
	$_SESSION['codeHeight'] = $userData[0]['codeHeight']; 
	$_SESSION['fontSize'] = $userData[0]['fontSize']; 
	
	return TRUE;
}
	
function dbUpdatePassword($password)
{
	global $root_db;
	$thisDb = new myDatabase($root_db);
	$hashPwd = password_hash($password, PASSWORD_DEFAULT);
	$sqlUpdate = "UPDATE users SET u_password ='".$hashPwd."' WHERE u_id ='".$_SESSION['uId']."'";
	
	return $thisDb->dbUpdate($sqlUpdate);
}

function dbCheckUserExist($user)
{
	global $root_db;
	$thisDb = new myDatabase($root_db);
	$sqlQuery = "SELECT * from users WHERE u_name ='".$user."'";
	$thisQuery = $thisDb->dbQuery($sqlQuery);
	
	if($thisQuery==0)
		return FALSE;
	
	return TRUE;
}

function dbAddUser($user, $password)
{
	global $root_db;
	$thisDb = new myDatabase($root_db);
	$hashPwd = password_hash($password, PASSWORD_DEFAULT);
	$sqlInsert = "INSERT INTO users(c_id, u_name, u_password, u_db) VALUES('".$_SESSION['cId']."','".$user."','".$hashPwd."','".$_SESSION['uDb']."')";
	
	$thisQuery = $thisDb->dbUpdate($sqlInsert);
	
	return 0;
}

function dbQueryAllUsers()
{
	global $root_db;
	$thisDb = new myDatabase($root_db);
	$sqlQuery = "SELECT u_id, u_name, role FROM users WHERE c_id='".$_SESSION['cId']."'";
	$thisQuery = $thisDb->dbQuery($sqlQuery);
	
	return $thisQuery;	
}

// company table

function dbGetCompanyColumns()
{
	$thisColumns = array('c_name','address','post','city','country','tel','fax','mobile','email','whatsapp','tax_no','uid_no','iban','bic','tax','website','hrb','geschaeftsfuehrer');
	
	return $thisColumns;
}

function dbGetCompanyColumnNo()
{
	return 18;
}

function dbQueryCompany()
{
	global $root_db;
	$thisDb = new myDatabase($root_db);
	$sqlQuery = "SELECT * FROM company WHERE c_id ='".$_SESSION['cId']."'";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	
	if($thisQuery <= 0)
		return $thisQuery;
	
	return $thisQuery[0];
}

function dbUpdateCompany($company)
{
	global $root_db;
	$thisDb = new myDatabase($root_db);
	$thisColumns = dbGetCompanyColumns();
	$thisColumnNo = dbGetCompanyColumnNo();
	$sqlSet = $thisColumns[0]."='".$company[$thisColumns[0]]."'";
	for ($i=1; $i<$thisColumnNo; $i++)
		$sqlSet = $sqlSet.",".$thisColumns[$i]."='".$company[$thisColumns[$i]]."'";
	$sqlUpdate = "UPDATE company SET ".$sqlSet." WHERE c_id ='".$_SESSION['cId']."'";
	
	$result = $thisDb->dbUpdate($sqlUpdate);
	
	$_SESSION['myCompany'] = dbQueryCompany();
	
	return $result;
}

// inventory table

function dbGetInventoryColumns()
{
	$thisColumns = array('i_id','i_code','i_name','count','count_a','cost','price','t_id','s_id','comment','code1','code2','discount','time_created','time_updated','path','m_no','unit', 'position', 'color', 'status','season');
	
	return $thisColumns;
}

function dbGetInventoryColumnNo()
{
	return 22;
}

function dbGetInvCount()
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT count(*) as i_count, sum(count) as t_count, sum(cost*count) as c_count FROM inventory";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	
	return $thisQuery[0];
}

function dbGetInvMax()
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT count(*) AS max FROM inventory";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $thisQuery[0];
}

function dbGetAllInvs($sql)
{	
	$thisDb = new myDatabase($_SESSION['uDb']);	
	if($sql!=NULL && $sql != '')
	{
		$sqlQuery = $sql;
	}
	else
		$sqlQuery = "SELECT * FROM inventory";  
	$thisQuery = $thisDb->dbQuery($sqlQuery);
	
	return $thisQuery;
}

function dbGetAllInventories()
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT * FROM inventory ORDER BY i_code ASC";   
	$thisQuery = $thisDb->dbQuery($sqlQuery);
	
	return $thisQuery;
}

function dbGetInvs0()
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT * FROM inventory WHERE status='0' ORDER BY i_code ASC";   
	$thisQuery = $thisDb->dbQuery($sqlQuery);
	
	return $thisQuery;
}

function dbQueryAllInvCodes()
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT i_id, i_code, i_name, path, m_no FROM inventory";   
	$thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $thisQuery;
}

function dbQueryInventory($iId)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT * FROM inventory WHERE i_id ='".$iId."'";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	
	if($thisQuery <= 0)
		return $thisQuery;
	
	return $thisQuery[0];
}

function dbQueryInvByCode($code)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT * FROM inventory WHERE i_code ='".$code."'";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	return $thisQuery;
}

// Barcode
function dbQueryInvByCode1($code1)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT inv.*, variant.* FROM inventory AS inv, inv_variant AS variant WHERE variant.i_id=inv.i_id AND variant.barcode ='".$code1."'";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	if ($thisQuery <= 0) 
	{
		$sqlQuery = "SELECT * FROM inventory WHERE code1 ='".$code1."'";
		$thisQuery = $thisDb->dbQuery($sqlQuery);
	}
	$thisDb->dbClose();

	return $thisQuery;
}

function dbQueryInvVariants() {
	$thisDb = new myDatabase($_SESSION['uDb']);
//	$sqlQuery = "SELECT inv.*, variant.* FROM inventory AS inv, inv_variant AS variant WHERE variant.i_id=inv.i_id";
if($_SESSION['uId'] == 6){
	$sqlQuery = "SELECT inv.position,inv.comment AS hint,t.t_name AS t_name, inv.i_id,inv.discount, inv.i_code, inv.i_name, inv.count, inv.unit, inv.price, inv.path, inv.m_no, inv.code1, inv.cost,
			var.iv_id,var.variant, var.amount, var.barcode, var.m_no AS im_no, var.size  FROM inventory AS inv LEFT JOIN inv_variant AS var ON var.i_id=inv.i_id LEFT JOIN types t ON t.t_id = inv.t_id WHERE inv.status='0' AND inv.count > 0";
}else{
	$sqlQuery = "SELECT inv.position,inv.comment AS hint, t.t_name AS t_name, inv.i_id,inv.discount, inv.i_code, inv.i_name, inv.count, inv.unit, inv.price, inv.path, inv.m_no, inv.code1, inv.cost,
	var.iv_id,var.variant, var.amount, var.barcode, var.m_no AS im_no, var.size  FROM inventory AS inv LEFT JOIN inv_variant AS var ON var.i_id=inv.i_id  LEFT JOIN types t ON t.t_id = inv.t_id WHERE inv.status='0'";
}
//	$sqlQuery = "SELECT inv.i_code, inv.i_name, inv.count, inv.unit, inv.price, inv.path, inv.m_no, variant.* FROM inventory AS inv, inv_variant AS variant WHERE variant.i_id=inv.i_id";
    $thisQuery = $thisDb->dbQuery($sqlQuery);	
	$thisDb->dbClose();
	
	return $thisQuery;
}

Function dbGetInvId()
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT code_num FROM code_gen WHERE code_type ='i'";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	if($thisQuery <= 0)
		return 0;
	
	$thisNum = $thisQuery[0]['code_num'];	
	$newNum = intval($thisNum);
	$newNum++;
	$sqlUpdate = "UPDATE code_gen SET code_num = '".$newNum."' WHERE code_type ='i'";
    $thisQuery = $thisDb->dbUpdate($sqlUpdate);
	
	return $thisNum;
}

function dbAddInventory($inventory)
{
	if($inventory['i_id'] == '')
		return FALSE;
		
	$thisDb = new myDatabase($_SESSION['uDb']);
	$thisColumns = dbGetInventoryColumns();
	$thisColumnNo = dbGetInventoryColumnNo();
	$sqlCol = $thisColumns[0];
	for ($i=1; $i<$thisColumnNo; $i++)
		$sqlCol = $sqlCol.",".$thisColumns[$i];
	$sqlVal = "'".$inventory[$thisColumns[0]]."'";
	for ($i=1; $i<$thisColumnNo; $i++)
	{
		if($inventory[$thisColumns[$i]] == '')
			$sqlVal = $sqlVal.",NULL";
		else
			$sqlVal = $sqlVal.",'".$inventory[$thisColumns[$i]]."'";
	}
	$sqlInsert = "INSERT INTO inventory(".$sqlCol.") VALUES(".$sqlVal.")";
	$result = $thisDb->dbInsert($sqlInsert);
	
	// Add log to history
	$log = initInvLog($inventory, 0);
	dbAddInvLog($log);
	
	// If batch, add pur_item
	if($_SESSION['p_id'] != '')
	{
		$puritem = dbInitPurItem($inventory['i_id'], $inventory['count'], $inventory['cost']);
		dbAddPurItem($puritem);
	}
	
	$thisDb->dbClose();
	
	return $result;
}
// 2021-02-04: after adding variant, we can update 'count' and 'cost'
function dbUpdateInventory($inventory)
{
	$iId = $inventory['i_id'];
	if($iId == '')
		return FALSE;
	$thisDb = new myDatabase($_SESSION['uDb']);
	$thisColumns = dbGetInventoryColumns();
	$thisColumnNo = dbGetInventoryColumnNo();
	$sqlSet = $thisColumns[1]."='".$inventory[$thisColumns[1]]."'";
	for ($i=2; $i<$thisColumnNo; $i++)
	{
		if($thisColumns[$i] == 'time_created')
			continue;
		if($inventory[$thisColumns[$i]] == '')
			$sqlSet = $sqlSet.",".$thisColumns[$i]."=NULL";
		else
			$sqlSet = $sqlSet.",".$thisColumns[$i]."='".$inventory[$thisColumns[$i]]."'";
	}
	$sqlUpdate = "UPDATE inventory SET ".$sqlSet." WHERE i_id ='".$iId."'";
	$result = $thisDb->dbUpdate($sqlUpdate);
	
	$thisDb->dbClose();
	
	return $result;
}

function dbInvAddCount($iId, $countAdd, $costAdd)
{
	$timeUpdated = date('Y-m-d H:i:s');
	
	$thisDb = new myDatabase($_SESSION['uDb']);
	
	// Calculate the new cost
	$sqlQuery = "SELECT * FROM inventory WHERE i_id='".$iId."'";
	$result = $thisDb->dbQuery($sqlQuery);
	if($result <= 0)
		return FALSE;
	$cost = $result[0]['cost'];
	$count = $result[0]['count'];
	$newCost = ($cost*$count + $costAdd*$countAdd)/($count + $countAdd);
	$newCost = round($newCost, 2);
	$newCount = $count + $countAdd;
	
	// Save data for log
	$inv = array();
	$inv['i_id'] = $iId;
	$inv['count'] = $countAdd;
	$inv['cost'] = $costAdd;
	$inv['price'] = $result[0]['price'];
	
	// Update inventory
	$sqlUpdate = "UPDATE inventory SET count='".$newCount."', count_a='".$newCount."', cost='".$newCost."' WHERE i_id ='".$iId."'";
	$result = $thisDb->dbUpdate($sqlUpdate);	
	
	// Add log
	$log = initInvLog($inv, 1);
	dbAddInvLog($log);
	
	// If batch, add pur_item
	if($_SESSION['p_id'] != '')
	{
		$puritem = dbInitPurItem($iId, $countAdd, $costAdd);
		dbAddPurItem($puritem);
	}
	
	$thisDb->dbClose();
	
	// Return 
	$ret = array();
	$ret['i_id'] = $iId;
	$ret['count'] = $newCount;
	$ret['cost'] = $newCost;
	
	return $ret;
}

function dbInvEditCount($iId, $countEdit, $costEdit)
{
	$timeUpdated = date('Y-m-d H:i:s');
	
	$thisDb = new myDatabase($_SESSION['uDb']);

	// Save data for log
	$inv = array();
	$inv['i_id'] = $iId;
	$inv['count'] = $countEdit;
	$inv['cost'] = $costEdit;
	$inv['price'] = 0;
	
	// Update inventory
	$sqlUpdate = "UPDATE inventory SET count='".$countEdit."', count_a='".$countEdit."', cost='".$costEdit."' WHERE i_id ='".$iId."'";
	$result = $thisDb->dbUpdate($sqlUpdate);	
	
	// Add log
	$log = initInvLog($inv, 3);
	dbAddInvLog($log);
		
	$thisDb->dbClose();
	
	return TRUE;
}

// Init history values
function initInvLog($inv, $action)
{
	$invLog = array();
	
	$invLog['i_id'] = $inv['i_id'];
	$invLog['u_id'] = $_SESSION['uId'];
	$invLog['h_date'] = date('Y-m-d H:i:s');
	if($action >= 10)
	{
		$invLog['amount'] = 0 - $inv['count'];
		$invLog['amount_a'] = 0 - $inv['count'];
	}
	else
	{
		$invLog['amount'] = $inv['count'];
		$invLog['amount_a'] = $inv['count'];
	}		
	$invLog['cost'] = $inv['cost'];
	$invLog['price'] = $inv['price'];
	$invLog['source'] = $action;
	$invLog['src_id'] = $inv['src_id'];
	
	return $invLog;
}

// Add new entry to history
function dbAddInvLog($invLog)
{			
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlInsert = "INSERT INTO history(i_id,u_id,h_date,amount,amount_a,cost,price,source,src_id) 
				VALUES('".$invLog['i_id']."','".$invLog['u_id']."','".$invLog['h_date']
				."','".$invLog['amount']."','".$invLog['amount_a']."','".$invLog['cost']."','".$invLog['price']
				."','".$invLog['source']."','".$invLog['src_id']."')";

	$result = $thisDb->dbInsert($sqlInsert);
	$thisDb->dbClose();
	
	return $result;
}

function dbQueryInvLogByTime($iId, $timeFrom, $timeTo)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT * FROM history WHERE i_id='".$iId."' AND h_date>='".$timeFrom." 00:00:00' AND h_date<='".$timeTo." 23:59:59'";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $thisQuery;	
}

function dbQueryInvLog($iId)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT * FROM history WHERE i_id='".$iId."' ORDER BY h_date DESC";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $thisQuery;	
}

function dbGetImageFileName($iId, $path, $mno, $type)
{
	if($type == 0)
		$name = $path."/".$iId.'_'.$mno.".jpg";
	else
		$name = $path."/".$iId.'_'.$mno."_s.jpg";
	
	return $name;
}

function dbAddInvImage($iId, $imageNo)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	
	$sqlInsert = "INSERT INTO inv_img(i_id,m_no) VALUES('".$iId."','".$imageNo."')";
	$result = $thisDb->dbInsert($sqlInsert);
	$thisDb->dbClose();
	
	return $result;
}

function dbDelInvImage($iId, $imageNo)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT path FROM inventory WHERE i_id='".$iId."'";
	$sqlDel = "DELETE FROM inv_img WHERE i_id='".$iId."' AND m_no='".$imageNo."'";
	$result = $thisDb->dbUpdate($sqlDel);
	$thisDb->dbClose();
	
	return $$sqlQuery[0];
}

function dbGetInvImages($iId)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT * FROM inv_img WHERE i_id='".$iId."'";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $thisQuery;
}

function dbGetInvStatBySup()
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT s_id,count(*) as s_count FROM inventory GROUP BY s_id";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $thisQuery;
}

function dbGetInvStatByType()
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT t_id,count(*) as t_count FROM inventory GROUP BY t_id";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $thisQuery;
}

// Table 'types'

function dbQueryTypes()
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT * FROM types ORDER BY t_name ASC";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $thisQuery;	
}

// 2021-01-14 when add a new type, add a record in a_art
function dbAddType($tName)
{
	$thisDb = new myDatabase($_SESSION['uDb']);	
	$sqlInsert = "INSERT INTO types(t_name) VALUES('".$tName."')";
	$tId = $thisDb->dbInsertId($sqlInsert);
	if ($tId > 0)
	{
		$aCode = $tId;
		$sqlInsert = "INSERT INTO a_art(a_code, t_id, a_name) VALUES('".$aCode."','".$tId."','".$tName."')";
		$thisDb->dbInsert($sqlInsert);
	}
	
	$thisDb->dbClose();
	
	return $tId;
}

function dbUpdateType($tId, $tName)
{
	$thisDb = new myDatabase($_SESSION['uDb']);	
	$sqlUpdate = "UPDATE types SET t_name='".$tName."' WHERE t_id='".$tId."'";
	$result = $thisDb->dbUpdate($sqlUpdate);
	if ($result)
	{
		$sqlUpdate = "UPDATE a_art SET a_name='".$tName."' WHERE t_id='".$tId."'";
		$thisDb->dbUpdate($sqlUpdate);
	}
	$thisDb->dbClose();
	
	return $result;
}

function dbGetTypeIdByName($tName, $types)
{
	for ($i=0; $i<count($types); $i++)
	{
		if($tName == $types[$i]['t_name'])
			return $types[$i]['t_id'];
	}
	
	return 0;
}

function dbGetTypeNameById($tId, $types)
{
	for ($i=0; $i<count($types); $i++)
	{
		if($tId == $types[$i]['t_id'])
			return $types[$i]['t_name'];
	}
	
	return '';
}

// When work on an inventory and add a new type
function dbAddTypeQuick($tName)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlInsert = "INSERT INTO types(t_name) VALUES('".$tName."')";
	$result = $thisDb->dbInsertId($sqlInsert);
	$thisDb->dbClose();
	
	return $result;
}

// Table 'suppliers'

function dbGetSupColumns()
{
	$thisColumns = array('s_code','s_name','name1','tel','address','post','city','country','contact','email','whatsapp','wechat');
	
	return $thisColumns;
}

function dbGetSupColumnNo()
{
	return 12;
}

function dbQueryAllSuppliers()
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT * FROM suppliers ORDER BY s_name ASC";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $thisQuery;	
}

function dbQueryAllName(){
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT DISTINCT(i_name) FROM inventory WHERE i_name IS NOT NULL AND i_name != '' ORDER BY i_name ASC";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $thisQuery;
}

function dbQuerySupplier($sId)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT * FROM suppliers WHERE s_id ='".$sId."'";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	if($thisQuery <= 0)
		return $thisQuery;
	
	return $thisQuery[0];
}

function dbAddSupplier($sup)
{	
	$thisDb = new myDatabase($_SESSION['uDb']);
	$thisColumns = dbGetSupColumns();
	$thisColumnNo = dbGetSupColumnNo();
	$sqlCol = $thisColumns[0];
	for ($i=1; $i<$thisColumnNo; $i++)
		$sqlCol = $sqlCol.",".$thisColumns[$i];
	$sqlVal = "'".$sup[$thisColumns[0]]."'";
	for ($i=1; $i<$thisColumnNo; $i++)
		$sqlVal = $sqlVal.",'".$sup[$thisColumns[$i]]."'";
	$sqlInsert = "INSERT INTO suppliers(".$sqlCol.") VALUES(".$sqlVal.")";
	
	$result = $thisDb->dbInsertId($sqlInsert);
	$thisDb->dbClose();
	
	return $result;
}

function dbUpdateSupplier($sId, $sup)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$thisColumns = dbGetSupColumns();
	$thisColumnNo = dbGetSupColumnNo();
	$sqlSet = $thisColumns[0]."='".$sup[$thisColumns[0]]."'";
	for ($i=1; $i<$thisColumnNo; $i++)
		$sqlSet = $sqlSet.",".$thisColumns[$i]."='".$sup[$thisColumns[$i]]."'";
	$sqlUpdate = "UPDATE suppliers SET ".$sqlSet." WHERE s_id ='".$sId."'";

	$result = $thisDb->dbUpdate($sqlUpdate);
	$thisDb->dbClose();
	
	return $result;
}

function dbQuerySupsByColumn($col, $value)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT * FROM suppliers WHERE ".$col."='".$value."'";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();

	return $thisQuery;
}

function dbQuerySupsInvs() 
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT s_id, sum(count) AS count_total, sum(count*cost) AS value_total FROM inventory GROUP BY s_id";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();

	return $thisQuery;
}

function dbGetSupIdByName($sName, $sups)
{
	for ($i=0; $i<count($sups); $i++)
	{
		if($sName == $sups[$i]['s_name'])
			return $sups[$i]['s_id'];
	}
	
	return 0;
}

function dbGetSupNameById($sId, $sups)
{
	for ($i=0; $i<count($sups); $i++)
	{
		if($sId == $sups[$i]['s_id'])
			return $sups[$i]['s_name'];
	}
	
	return '';
}

// When work on an inventory and add a new supplier
function dbAddSupQuick($sName)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlInsert = "INSERT INTO suppliers(s_name) VALUES('".$sName."')";
	$result = $thisDb->dbInsertId($sqlInsert);
	$thisDb->dbClose();
	
	return $result;
}

// Order

function dbGetOrderUpdateColumns()
{
	$thisColumns = array('discount_rate','fee1','fee2','fee3','fee4','fee5',
							'count_sum', 'price_sum', 'total_sum', 'paid_sum', 'due',
							'pay_cash', 'pay_card', 'pay_bank', 'pay_check', 'pay_other', 'pay_paypal', 'profit', 'k_id', 'pay_vorkasse', 'status');
	
	return $thisColumns;
}

Function dbGetOrderId()
{	
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT code_num FROM code_gen WHERE code_type ='o'";
    $thisQuery = $thisDb->dbQuery($sqlQuery);	
	if($thisQuery <= 0)
	{
		$thisDb->dbClose();
		return thisQuery;
	}
	$thisNum = $thisQuery[0]['code_num'];
	$newNum = intval($thisNum);
	$newNum++;
	$sqlUpdate = "UPDATE code_gen SET code_num = '".$newNum."' WHERE code_type ='o'";
    $thisQuery = $thisDb->dbUpdate($sqlUpdate);
	$thisDb->dbClose();
	
	return $thisNum;
}

// Create order
function dbCreateOrder($oId)
{
	$thisDb = new myDatabase($_SESSION['uDb']);	
	$sqlInsert = "INSERT INTO orders(o_id, k_id, u_id, date) 
				VALUES('".$oId."','"."0"."','".$_SESSION['uId']."','".date('Y-m-d H:i:s')."')";
	$result = $thisDb->dbInsert($sqlInsert);
	$thisDb->dbClose();
	
	return $result;
}

function dbCreateOrderNew() {
	$thisDb = new myDatabase($_SESSION['uDb']);
	$oId = dbGetOrderId();
	if ($oId <= 0)
		return 0;
	$sqlInsert = "INSERT INTO orders(o_id, k_id, u_id, date) 
				VALUES('".$oId."','"."0"."','".$_SESSION['uId']."','".date('Y-m-d H:i:s')."')";
	$result = $thisDb->dbInsert($sqlInsert);
	$thisDb->dbClose();
	if ($result <= 0)
		return 0;
	
	return $oId;	
}

function dbCreaeteOrderFromMerge($order, $orderitems, $ordervariants, $oldorders) {
	$thisDb = new myDatabase($_SESSION['uDb']);
	$oId = dbGetOrderId();
	if ($oId <= 0)
		return -1;
	$sqlInsert = "INSERT INTO orders(o_id, k_id, u_id, date, count_sum, price_sum, total_sum, due, profit) VALUES('".
					$oId."','".$order['k_id']."','".$_SESSION['uId']."','".date('Y-m-d H:i:s')."','".
					$order['count_sum']."','".$order['price_sum']."','".$order['total_sum']."','".$order['due']."','".$order['profit']."')";
	$result = $thisDb->dbInsert($sqlInsert);
	if (!$result) {
		$thisDb->dbClose();
		return $result;
	}
writeLog("MERGE ORDER: ".$sqlInsert);	
	for ($i=0; $i<count($orderitems); $i++) {
		$sqlInsert = "INSERT INTO order_items(o_id, i_id, count, cost, price, unit) VALUES('".
						$oId."','".$orderitems[$i]['i_id']."','".$orderitems[$i]['count']."','".$orderitems[$i]['cost']."','".
						$orderitems[$i]['price']."','".$orderitems[$i]['unit']."')";
		$thisDb->dbInsert($sqlInsert);
	}
		
	for ($i=0; $i<count($ordervariants); $i++) {
		$sqlInsert = "INSERT INTO order_variant(o_id, i_id, iv_id, count) VALUES('".
						$oId."','".$ordervariants[$i]['i_id']."','".$ordervariants[$i]['iv_id']."','".$ordervariants[$i]['count']."')";
		$thisDb->dbInsert($sqlInsert);
	}
			
	for ($i=0; $i<count($oldorders); $i++) {

		$sql = "INSERT INTO orders_del SELECT * FROM orders WHERE o_id = '".$oldorders[$i]['o_id']."'";
		$thisDb->dbUpdate($sql);
		$sql = "INSERT INTO order_items_del SELECT * FROM order_items WHERE o_id = '".$oldorders[$i]['o_id']."'";
		$thisDb->dbUpdate($sql);
		$sql = "INSERT INTO order_variant_del SELECT * FROM order_variant WHERE o_id = '".$oldorders[$i]['o_id']."'";
		$thisDb->dbUpdate($sql);

		$sqlDel = "DELETE FROM orders WHERE o_id='".$oldorders[$i]['o_id']."'";
		$thisDb->dbUpdate($sqlDel);
	}
				
	$thisDb->dbClose();
	
	return TRUE;
}

function dbAddOrderItemOne($orderitem)
{ 
	if($orderitem->o_id == '' || $orderitem->i_id == '')
		return FALSE;
	
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlInsert = "INSERT INTO order_items(o_id, i_id, count, cost, price, unit, discount) VALUES('".
			$orderitem->o_id."','".$orderitem->i_id."','".$orderitem->count."','".$orderitem->cost."','".$orderitem->price."','".$orderitem->unit."','".$orderitem->discount."')";
	$result = $thisDb->dbInsert($sqlInsert);
	if ($result <= 0)
	{
		$thisDb->dbClose();
		return FALSE;
	}
	// Update inventory
	$sqlUpdate = "UPDATE inventory SET count=count-".$orderitem->count.", count_a=count_a-".$orderitem->count." WHERE i_id='".$orderitem->i_id."'";
	$thisDb->dbUpdate($sqlUpdate);		
	// Add log to history
	$in = array();
	$in['i_id'] = $orderitem->i_id;
	$in['count'] = $orderitem->count;
	$in['cost'] = $orderitem->cost;
	$in['price'] = $orderitem->price;
	$in['src_id'] = $orderitem->o_id;
	$log = initInvLog($in, 10);
	dbAddInvLog($log);
	
	$thisDb->dbClose();
	
	return $result;
}

function dbDelOrderItemOne($orderitem)
{ 
	if($orderitem->o_id == '' || $orderitem->i_id == '')
		return FALSE;
	
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlDel = "DELETE FROM order_items WHERE o_id='".$orderitem->o_id."' AND i_id='".$orderitem->i_id."'";
	$result = $thisDb->dbUpdate($sqlDel);
	if ($result <= 0)
	{
		$thisDb->dbClose();
		return FALSE;
	}
	// Update inventory
	$sqlUpdate = "UPDATE inventory SET count=count+".$orderitem->count.", count_a=count_a+".$orderitem->count." WHERE i_id='".$orderitem->i_id."'";
	$thisDb->dbUpdate($sqlUpdate);		
	// Add log to history
	$in = array();
	$in['i_id'] = $orderitem->i_id;
	$in['count'] = $orderitem->count;
	$in['cost'] = $orderitem->cost;
	$in['price'] = $orderitem->price;
	$in['src_id'] = $orderitem->o_id;
	$log = initInvLog($in, 2);
	dbAddInvLog($log);
	
	$thisDb->dbClose();
	
	return $result;
}

function dbUpdateOrderItemOne($orderitem, $option)
{ 
	if($orderitem->o_id == '' || $orderitem->i_id == '')
		return FALSE;
	
	$thisDb = new myDatabase($_SESSION['uDb']);
	if ($option == 1) {
		$sqlUpdate = "UPDATE order_items SET count=count+".$orderitem->count.", price='".$orderitem->price.
			"', discount='".$orderitem->discount."' WHERE o_id='".$orderitem->o_id."' AND i_id='".$orderitem->i_id."'";
		$result = $thisDb->dbUpdate($sqlUpdate);
		if ($result <= 0)
		{
			$thisDb->dbClose();
			return FALSE;
		}
		// Update inventory
		$sqlUpdate = "UPDATE inventory SET count=count-".$orderitem->count.", count_a=count_a-".$orderitem->count." WHERE i_id='".$orderitem->i_id."'";
		$thisDb->dbUpdate($sqlUpdate);		
		// Add log to history
		$in = array();
		$in['i_id'] = $orderitem->i_id;
		$in['count'] = $orderitem->count;
		$in['cost'] = $orderitem->cost;
		$in['price'] = $orderitem->price;
		$in['src_id'] = $orderitem->o_id;
		$log = initInvLog($in, 10);
		dbAddInvLog($log);
	} else {
		$sqlUpdate = "UPDATE order_items SET count=count-'".$orderitem->count."', price='".$orderitem->price.
			"', discount='".$orderitem->discount."' WHERE o_id='".$orderitem->o_id."' AND i_id='".$orderitem->i_id."'";
		$result = $thisDb->dbUpdate($sqlUpdate);
		if ($result <= 0)
		{
			$thisDb->dbClose();
			return FALSE;
		}
		// Update inventory
		$sqlUpdate = "UPDATE inventory SET count=count+".$orderitem->count.", count_a=count_a+".$orderitem->count." WHERE i_id='".$orderitem->i_id."'";
		$thisDb->dbUpdate($sqlUpdate);		
		// Add log to history
		$in = array();
		$in['i_id'] = $orderitem->i_id;
		$in['count'] = $orderitem->count;
		$in['cost'] = $orderitem->cost;
		$in['price'] = $orderitem->price;
		$in['src_id'] = $orderitem->o_id;
		$log = initInvLog($in, 2);
		dbAddInvLog($log);		
	}
	
	$thisDb->dbClose();
	
	return $result;
}

function dbDelOrder($order, $orderitems, $ordervariants)
{
	if($order == NULL || $order['o_id'] == '')
		return FALSE;

	$thisDb = new myDatabase($_SESSION['uDb']);

	$sql = "INSERT INTO orders_del SELECT * FROM orders WHERE o_id = '".$order['o_id']."'";
	$thisDb->dbUpdate($sql);
	$sql = "INSERT INTO order_items_del SELECT * FROM order_items WHERE o_id = '".$order['o_id']."'";
	$thisDb->dbUpdate($sql);
	$sql = "INSERT INTO order_variant_del SELECT * FROM order_variant WHERE o_id = '".$order['o_id']."'";
	$thisDb->dbUpdate($sql);

	// order_items and order_variant are deleted by DB trigger
	$sqlDel = "DELETE FROM orders WHERE o_id='".$order['o_id']."'";
	$result = $thisDb->dbUpdate($sqlDel);
	if ($result <= 0)
	{
		$thisDb->dbClose();
		return FALSE;
	}
writeLog("DELETE ORDER o_id=".$order['o_id']." k_id=".$order['k_id']." total=".$order['total_sum']);
	// Update inventory
	if($orderitems == NULL || count($orderitems) <= 0)
	{
		$thisDb->dbClose();
		return TRUE;
	}

	for ($i=0; $i<count($orderitems); $i++)
	{
		$sqlUpdate = "UPDATE inventory SET count=count+".$orderitems[$i]['count'].", count_a=count_a+".$orderitems[$i]['count']." WHERE i_id='".$orderitems[$i]['i_id']."'";
		$thisDb->dbUpdate($sqlUpdate);				
		// Add log to history
		$in = array();
		$in['i_id'] = $orderitems[$i]['i_id'];
		$in['count'] = $orderitems[$i]['count'];
		$in['cost'] = $orderitems[$i]['cost'];
		$in['price'] = $orderitems[$i]['price'];
		$in['src_id'] = $orderitems[$i]['o_id'];
		$log = initInvLog($in, 2);
		dbAddInvLog($log);
	}
	// Update inv_variant
	if($ordervariants == NULL || count($ordervariants) <= 0)
	{
		$thisDb->dbClose();
		return TRUE;
	}	
	for ($i=0; $i<count($ordervariants); $i++)
	{
		for ($j=0; $j<count($ordervariants[$i]); $j++)
		{
			$sqlUpdate = "UPDATE inv_variant SET amount=amount+".$ordervariants[$i][$j]['count']." WHERE iv_id='".$ordervariants[$i][$j]['iv_id']."'";
			$result = $thisDb->dbUpdate($sqlUpdate);
		}
	}
	
	$thisDb->dbClose();
	
	return $result;
}

function dbQueryOrders($timefrom, $timeto)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	if ($timefrom != NULL && $timeto != NULL)
		$sqlQuery = "SELECT orders.*, SUM(items.count*items.unit) AS s_count_sum, SUM(items.count*items.unit*items.cost) AS s_cost_sum, SUM(items.count*items.unit*((items.price*(100-items.discount))/100)*(100-items.discount))/100 AS s_price_sum
			FROM orders AS orders, order_items AS items
			WHERE orders.o_id=items.o_id AND orders.date>='".$timefrom." 00:00:00' AND orders.date<='".$timeto." 23:59:59'
			GROUP BY orders.o_id ORDER BY orders.date DESC";
	else
		$sqlQuery = "SELECT * FROM orders";
	$thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $thisQuery;
}

function dbUpdateOrder($order) {
	$oId = $order['o_id'];
	if(!$oId or $oId == '')
		return FALSE;	
	
	$thisDb = new myDatabase($_SESSION['uDb']);
	$thisColumns = dbGetOrderUpdateColumns();
	$sqlSet = $thisColumns[0]."='".$order[$thisColumns[0]]."'";
	for ($i=1; $i<count($thisColumns); $i++)
		$sqlSet = $sqlSet.",".$thisColumns[$i]."='".$order[$thisColumns[$i]]."'";
	$sqlUpdate = "UPDATE orders SET ".$sqlSet." WHERE o_id ='".$oId."'";	
	$result = $thisDb->dbUpdate($sqlUpdate);

	if($order['isPayed'] == "-1") $order['isPayed'] = 0;
	else if($order['isPayed'] == "") $order['isPayed'] = 1;
	if($order['isPayed'] == 0) $order['paidDatum'] = "";
	else $order['paidDatum'] = date("Y-m-d");
	$sqlUpdate = "UPDATE orders SET isPayed = '".$order['isPayed']."', paidDatum = '".$order['paidDatum']."' WHERE o_id ='".$oId."'"; 
	$result = $thisDb->dbUpdate($sqlUpdate);

	$thisDb->dbClose();
	
	return $result;
}

function dbGetAllOrders()
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT * FROM orders";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $thisQuery;
}

function dbGetOrderCount()
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT count(*) as o_count, sum(price_net) as o_value FROM orders";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $thisQuery[0];
}

function dbQueryOrderById($oId)
{	
	$thisDb = new myDatabase($_SESSION['uDb']);	
	$sqlQuery = "SELECT * FROM orders WHERE o_id ='".$oId."'";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	if($thisQuery <= 0)		
		return $thisQuery;
	
	return	$thisQuery[0];
}

function dbQueryOrderItems($oId)
{	
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT items.*, invs.i_code, invs.i_name, invs.path, invs.m_no, invs.count AS old_count,invs.comment AS hint, invs.position, invs.color
					FROM order_items AS items, inventory AS invs WHERE items.o_id='".$oId."' and invs.i_id=items.i_id";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $thisQuery;
}

// Table 'customer'
function dbGetCustomerCode()
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT code_num FROM code_gen WHERE code_type ='k'";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	
	if($thisQuery <= 0)
		return "";
	
	$thisNum = $thisQuery[0]['code_num'];	
	$newNum = intval($thisNum);
	$newNum++;
	$sqlUpdate = "UPDATE code_gen SET code_num = '".$newNum."' WHERE code_type ='k'";
    $thisQuery = $thisDb->dbUpdate($sqlUpdate);
	
	return $thisNum;
}

function dbGetCustomerCodeByPrefix_old($prefix)
{
	$prefix = strtoupper($prefix);
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT k_code FROM customer WHERE k_code LIKE '".$prefix."%' ORDER BY k_code ASC";
	$result = $thisDb->dbQuery($sqlQuery);
	if ($result <= 0)
		return dbGetCustomerCode();

	$max = 0;
	for ($i=0; $i<count($result); $i++)
	{
		$numstr = substr($result[$i]['k_code'], strlen($prefix));
		$num = intval($numstr);
		if ($num >= $max)
			$max = $num;
	}
	$max++;
	$codestr = $prefix.$max;
	
	return $codestr;
}


function dbGetCustomerCodeByPrefix($prefix)
{
	$prefix = strtoupper($prefix);
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT k_code FROM customer WHERE k_code LIKE '".$prefix."%' ORDER BY k_code ASC";
	$result = $thisDb->dbQuery($sqlQuery);
	if ($result <= 0)
		return dbGetCustomerCode();

	for ($i=0; $i<count($result); $i++){
		$arr_code[] = intval("1".substr($result[$i]['k_code'], strlen($prefix)));
	}
	asort($arr_code);

	for($max=1;;$max++){
		$max_tmp = intval("1".$max);
		$find_st = false;
        $i = 0;
		foreach ($arr_code AS $t){
			if($max_tmp == $t){
				$find_st = true;
				break;
			}
			if($max_tmp < $t) break;
 			$i++;
		}
		if($i == count($arr_code)) break;
        if($find_st == false) break;
	}
	$codestr = $prefix.substr($max_tmp,1);
	
	return $codestr;
}

function dbGetCusColumns()
{
	$thisColumns = array('k_code', 'k_name','name1','address','post','city','country','tel','contact','email','whatsapp','wechat','taxno','ustno','discount');
	
	return $thisColumns;
}

function dbGetCusColumnNo()
{
	return 15;
}

function dbQueryAllCustomers()
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT * FROM customer";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $thisQuery;	
}

function dbQueryCustOrders($kId, $timefrom, $timeto)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	if ($timefrom != NULL && $timeto != NULL)
		$sqlQuery = "SELECT * FROM orders WHERE k_id='".$kId."' AND date>='".$timefrom." 00:00:00' AND date<='".$timeto." 23:59:59'  ORDER BY date DESC";	
	else
		$sqlQuery = "SELECT * FROM orders WHERE k_id='".$kId."' ORDER BY date DESC";
	$thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $thisQuery;
}

function dbQueryCustOrdersItems($kId, $timefrom, $timeto)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	if ($timefrom != NULL && $timeto != NULL)
		$sqlQuery = "SELECT invs.i_code, invs.path, invs.m_no, orders.*, items.*
				FROM orders AS orders, order_items AS items, inventory AS invs
				WHERE k_id='".$kId."' AND orders.o_id=items.o_id AND items.i_id=invs.i_id 
				AND date>='".$timefrom." 00:00:00' AND date<='".$timeto." 23:59:59' ORDER BY items.o_id";	
	else
		$sqlQuery = "SELECT invs.i_code, invs.path, invs.m_no, items.*
				FROM orders AS orders, order_items AS items, inventory AS invs
				WHERE k_id='".$kId."' AND orders.o_id=items.o_id AND items.i_id=invs.i_id ORDER BY items.o_id";
	$thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $thisQuery;
}

function dbQueryCustOrderVariants($kId)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT variants.*, iv.variant, iv.m_no
				FROM orders AS orders, order_variant AS variants, inv_variant AS iv
				WHERE orders.k_id='".$kId."' AND orders.o_id=variants.o_id AND variants.iv_id=iv.iv_id ORDER BY variants.o_id";
	$thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $thisQuery;
}

function dbQueryCustomerById($kId)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT * FROM customer WHERE k_id ='".$kId."'";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	if($thisQuery <= 0)
		return $thisQuery;

	return $thisQuery[0];
}

function dbQueryCustsByColumn($col, $value)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT * FROM customer WHERE ".$col."='".$value."'";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();

	return $thisQuery;
}

function dbAddCustomer($cus)
{	
	$thisDb = new myDatabase($_SESSION['uDb']);
	$thisColumns = dbGetCusColumns();
	$thisColumnNo = dbGetCusColumnNo();
	$sqlCol = $thisColumns[0];
	for ($i=1; $i<$thisColumnNo; $i++)
		$sqlCol = $sqlCol.",".$thisColumns[$i];
	$sqlVal = "'".$cus[$thisColumns[0]]."'";
	for ($i=1; $i<$thisColumnNo; $i++)
	{
		if($cus[$thisColumns[$i]] == '')
			$sqlVal = $sqlVal.",NULL";
		else
			$sqlVal = $sqlVal.",'".addslashes($cus[$thisColumns[$i]])."'";
	}
	$sqlInsert = "INSERT INTO customer(".$sqlCol.") VALUES(".$sqlVal.")";
	$result = $thisDb->dbInsertId($sqlInsert);
	$thisDb->dbClose();
	
	return $result;
}

function dbUpdateCustomer($kId, $cus)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$thisColumns = dbGetCusColumns();
	$thisColumnNo = dbGetCusColumnNo();
	$sqlSet = $thisColumns[0]."='".$cus[$thisColumns[0]]."'";
	for ($i=1; $i<$thisColumnNo; $i++)
	{
		if($cus[$thisColumns[$i]] == '')
			$sqlSet = $sqlSet.",".$thisColumns[$i]."=NULL";
		else
			$sqlSet = $sqlSet.",".$thisColumns[$i]."='".addslashes($cus[$thisColumns[$i]])."'";
	}
	$sqlUpdate = "UPDATE customer SET ".$sqlSet." WHERE k_id ='".$kId."'";

	$result = $thisDb->dbUpdate($sqlUpdate);
	$thisDb->dbClose();
	
	return $kId;
}

function dbGetCustIdByName($kName, $custs)
{
	for ($i=0; $i<count($custs); $i++)
	{
		if($kName == $custs[$i]['k_name'])
			return $custs[$i]['k_id'];
	}
	
	return 0;
}

function dbGetCustNameById($kId, $custs)
{
	for ($i=0; $i<count($custs); $i++)
	{
		if($kId == $custs[$i]['k_id'])
			return $custs[$i]['k_name'];
	}
	
	return '';
}

function dbGetCustById($kId, $custs)
{
	for ($i=0; $i<count($custs); $i++)
	{
		if($kId == $custs[$i]['k_id'])
			return $custs[$i];
	}
	
	return 0;
}

// Purchase
function dbGetPurColumns()
{
	$thisColumns = array('p_id','sp_id','sp_date','p_date','s_id','note','u_id');
	
	return $thisColumns;
}

function dbGetPurColumnNo()
{
	return 7;
}

function dbGetPurItemsColumns()
{
	$thisColumns = array('p_id','i_id','count','cost');
	
	return $thisColumns;
}

function dbGetPurItemsColumnNo()
{
	return 4;
}

Function dbGetPurId()
{	
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT code_num FROM code_gen WHERE code_type ='p'";
    $thisQuery = $thisDb->dbQuery($sqlQuery);	
	if($thisQuery <= 0)
	{
		$thisDb->dbClose();
		return thisQuery;
	}
	$thisNum = $thisQuery[0]['code_num'];
	$newNum = intval($thisNum);
	$newNum++;
	$sqlUpdate = "UPDATE code_gen SET code_num = '".$newNum."' WHERE code_type ='p'";
    $thisQuery = $thisDb->dbUpdate($sqlUpdate);
	$thisDb->dbClose();
	
	return $thisNum;
}

// Add purchase
function dbAddPurchase($pur)
{
	if($pur['p_id'] == '')
		return FALSE;
	
	$pur['u_id'] = $_SESSION['uId'];
	$pur['p_date'] = date('Y-m-d H:i:s');

	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlInsert = "INSERT INTO purchase(p_id, u_id, p_date) VALUES('".$pur['p_id']."','". $pur['u_id']."','".$pur['p_date']."')";
	
	$result = $thisDb->dbInsert($sqlInsert);
	$thisDb->dbClose();
	
	return $result;
}

// Header purchase
function dbHeaderPurchase($pur)
{
	if($pur['p_id'] == '')
		return FALSE;

	$thisDb = new myDatabase($_SESSION['uDb']);

	if($pur['isPayed'] == "-1") $pur['isPayed'] = 0;
	else if($pur['isPayed'] == "") $pur['isPayed'] = 1;
	if($pur['isPayed'] == 0) $pur['paidDatum'] = "";
	else $pur['paidDatum'] = date("Y-m-d");

	$sqlUpdate = "UPDATE purchase SET isPayed='".$pur['isPayed']."', paidDatum='".$pur['paidDatum']."', p_code='".$pur['p_code']."', s_id='".$pur['s_id']."', note='".$pur['note']."', unpaid='".$pur['unpaid'].
					"' WHERE p_id='".$pur['p_id']."'";
	
	$result = $thisDb->dbUpdate($sqlUpdate);
	$thisDb->dbClose();
	
	return $result;
}

// Update purchase
function dbUpdatePurchase($pur)
{
	if($pur['p_id'] == '')
		return FALSE;

	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlUpdate = "UPDATE purchase SET count_sum='".$pur['count_sum']."', cost_sum='".$pur['cost_sum']."', discount='".$pur['discount']."',fee='".$pur['fee']."',total_sum='".$pur['total_sum']."' WHERE p_id='".$pur['p_id']."'";
	
	$result = $thisDb->dbUpdate($sqlUpdate);
	$thisDb->dbClose();
	
	return $result;
}

function dbAddPurItemOne($puritem)
{ 
	if($puritem->p_id == '' || $puritem->i_id == '')
		return FALSE;
	
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlInsert = "INSERT INTO pur_items(p_id, i_id, count, cost, price, old_count, old_cost, old_price, unit) VALUES('".
			$puritem->p_id."','".$puritem->i_id."','".$puritem->count."','".$puritem->cost."','".$puritem->price."','".
			$puritem->old_count."','".$puritem->old_cost."','".$puritem->old_price."','".$puritem->unit."')";
	$result = $thisDb->dbInsert($sqlInsert);
	if ($result <= 0)
	{
		$thisDb->dbClose();
		return FALSE;
	}
	// Update inventory
	$cost = $puritem->old_cost;
	$count = $puritem->old_count;
	$costAdd = $puritem->cost;
	$countAdd = $puritem->count;
	$newCost = ($cost*$count + $costAdd*$countAdd)/($count + $countAdd);
	$newCost = round($newCost, 2);
	$newCount = $count + $countAdd;
	$newPrice = $puritem->price;
	
	$sqlUpdate = "UPDATE inventory SET count='".$newCount."', count_a='".$newCount."', cost='".$newCost."', price='".$newPrice.
				"', time_updated='".date('Y-m-d H:i:s')."' WHERE i_id='".$puritem->i_id."'";
	$thisDb->dbUpdate($sqlUpdate);
	// Add log to history
	$in = array();
	$in['i_id'] = $puritem->i_id;
	$in['count'] = $puritem->count;
	$in['cost'] = $puritem->cost;
	$in['price'] = $puritem->price;
	$in['src_id'] = $puritem->p_id;
	$log = initInvLog($in, 1);
	dbAddInvLog($log);
	
	$thisDb->dbClose();
	
	return $result;
}

/* update count_sum and cost_sum. Used by inv_view after new product is created in purchase */
function dbUpdatePurSum($puritem)
{
	if($puritem->p_id == '')
		return FALSE;	
	$thisDb = new myDatabase($_SESSION['uDb']);
	$costSum = intval($puritem->real_count)*floatval($puritem->cost);
	$sqlUpdate = "UPDATE purchase SET count_sum=count_sum+".$puritem->real_count.", cost_sum=cost_sum+".$costSum." WHERE p_id='".$puritem->p_id."'";
	$result = $thisDb->dbUpdate($sqlUpdate);
	$thisDb->dbClose();
	
	return $result;
}

function dbUpdatePurItemOne($puritem, $option)
{ 
	if($puritem->p_id == '' || $puritem->i_id == '')
		return FALSE;
	
	$thisDb = new myDatabase($_SESSION['uDb']);
	if ($option == 1)
	{
		$sqlUpdate = "UPDATE pur_items SET count=count+".$puritem->count.", cost='".$puritem->cost."', price='".$puritem->price.
					"' WHERE p_id='".$puritem->p_id."' AND i_id='".$puritem->i_id."'";
		$result = $thisDb->dbUpdate($sqlUpdate);
		if ($result <= 0)
		{
			$thisDb->dbClose();
			return FALSE;
		}
		// Update inventory
		$sqlUpdate = "UPDATE inventory SET count=count+".$puritem->count.", count_a=count_a+".$puritem->count." WHERE i_id='".$puritem->i_id."'";
		$thisDb->dbUpdate($sqlUpdate);		
		// Add log to history
		$in = array();
		$in['i_id'] = $puritem->i_id;
		$in['count'] = $puritem->count;
		$in['cost'] = $puritem->cost;
		$in['price'] = $puritem->price;
		$in['src_id'] = $puritem->p_id;
		$log = initInvLog($in, 1);
		dbAddInvLog($log);
	}
	else
	{
		$sqlUpdate = "UPDATE pur_items SET count=count-".$puritem->count.", cost='".$puritem->cost."', price='".$puritem->price.
					"' WHERE p_id='".$puritem->p_id."' AND i_id='".$puritem->i_id."'";
		$result = $thisDb->dbUpdate($sqlUpdate);
		if ($result <= 0)
		{
			$thisDb->dbClose();
			return FALSE;
		}
		// Update inventory
		$sqlUpdate = "UPDATE inventory SET count=count-".$puritem->count.", count_a=count_a-".$puritem->count." WHERE i_id='".$puritem->i_id."'";
		$thisDb->dbUpdate($sqlUpdate);		
		// Add log to history
		$in = array();
		$in['i_id'] = $puritem->i_id;
		$in['count'] = $puritem->count;
		$in['cost'] = $puritem->cost;
		$in['price'] = $puritem->price;
		$in['src_id'] = $puritem->p_id;
		$log = initInvLog($in, 11);
		dbAddInvLog($log);		
	}
	
	$thisDb->dbClose();
	
	return $result;
}

function dbDelPurItemOne($puritem)
{ 
	if($puritem->p_id == '' || $puritem->i_id == '')
		return FALSE;
	
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlDel = "DELETE FROM pur_items WHERE p_id='".$puritem->p_id."' AND i_id='".$puritem->i_id."'";
	$result = $thisDb->dbUpdate($sqlDel);
	if ($result <= 0)
	{
		$thisDb->dbClose();
		return FALSE;
	}
	// Update inventory
	$sqlUpdate = "UPDATE inventory SET count=count-".$puritem->count.", count_a=count_a-".$puritem->count.
					", time_updated='".date('Y-m-d H:i:s')."' WHERE i_id='".$puritem->i_id."'";
	$thisDb->dbUpdate($sqlUpdate);		
	// Add log to history
	$in = array();
	$in['i_id'] = $puritem->i_id;
	$in['count'] = $puritem->count;
	$in['cost'] = $puritem->cost;
	$in['price'] = $puritem->price;
	$in['src_id'] = $puritem->p_id;
	$log = initInvLog($in, 11);
	dbAddInvLog($log);
	
	$thisDb->dbClose();
	
	return $result;
}
function dbPrintPur($pur)
{
	if($pur['p_id'] == '')
		return FALSE;
	
	$thisDb = new myDatabase($_SESSION['uDb']);

	// with variant //
	$sql = "SELECT pi.unit AS unit, pv.count AS count, i.i_code AS i_code, iv.variant AS variant, iv.barcode AS barcode FROM pur_variant pv LEFT JOIN inventory i ON (pv.i_id = i.i_id) LEFT JOIN inv_variant iv ON (pv.iv_id = iv.iv_id) LEFT JOIN pur_items pi ON (pv.i_id = pi.i_id AND pv.p_id = pi.p_id) WHERE pv.p_id = '".$pur['p_id']."'";
	$data = $thisDb->dbQuery($sql);
	foreach($data AS $arr){
		$label = $arr['i_code'];
		$label_2 = $arr['variant'];
		$code = $arr['barcode'];
		$amount = intval($arr['count']) * intval($arr['unit']);
		$sql = "INSERT INTO print (printerName, paperWidth, paperHeight, codeWidth, codeHeight, fontSize, label, label_2, code, amount, datum) VALUES ('".
			$_SESSION['printerName']."','".$_SESSION['paperWidth']."','".$_SESSION['paperHeight']."','".$_SESSION['codeWidth']."','".$_SESSION['codeHeight']."','".$_SESSION['fontSize']."','".$label."','".$label_2."','".$code."','".$amount."','".date("Y-m-d H:i:s")."')";
		$thisDb->dbQuery($sql);
	}
	// no variant //
	$sql = "SELECT pi.unit AS unit, pi.count AS count, i.i_code AS i_code, i.code1 AS barcode FROM pur_items pi LEFT JOIN inventory i ON (pi.i_id = i.i_id) WHERE pi.p_id = '".$pur['p_id']."' AND pi.i_id NOT IN (SELECT i_id FROM pur_variant WHERE p_id = '".$pur['p_id']."')";
	$data = $thisDb->dbQuery($sql);
	foreach($data AS $arr){
		$label = $arr['i_code'];
		$label_2 = "";
		$code = $arr['barcode'];
		$amount = intval($arr['count']) * intval($arr['unit']);
		$sql = "INSERT INTO print (printerName, paperWidth, paperHeight, codeWidth, codeHeight, fontSize, label, label_2, code, amount, datum) VALUES ('".
			$_SESSION['printerName']."','".$_SESSION['paperWidth']."','".$_SESSION['paperHeight']."','".$_SESSION['codeWidth']."','".$_SESSION['codeHeight']."','".$_SESSION['fontSize']."','".$label."','".$label_2."','".$code."','".$amount."','".date("Y-m-d H:i:s")."')";
		$thisDb->dbQuery($sql);
	}
	
	$thisDb->dbClose();
	return TRUE;
}

function dbPrintInv($i_code, $code, $amount, $variant)
{
	if($i_code == '')
		return FALSE;
	
	$thisDb = new myDatabase($_SESSION['uDb']);
	$label = $i_code;
	$label_2 = $variant;
	$sql = "INSERT INTO print (printerName, paperWidth, paperHeight, codeWidth, codeHeight, fontSize, label, label_2, code, amount, datum) VALUES ('".
		$_SESSION['printerName']."','".$_SESSION['paperWidth']."','".$_SESSION['paperHeight']."','".$_SESSION['codeWidth']."','".$_SESSION['codeHeight']."','".$_SESSION['fontSize']."','".$label."','".$label_2."','".$code."','".$amount."','".date("Y-m-d H:i:s")."')";
	$thisDb->dbQuery($sql);
		
	$thisDb->dbClose();
	return TRUE;
}

function dbDelPur($pur, $puritems, $purvariants)
{
	if($pur['p_id'] == '')
		return FALSE;
	
	$thisDb = new myDatabase($_SESSION['uDb']);
	// pur_items and pur_variant are deleted by DB trigger
	$sqlDel = "DELETE FROM purchase WHERE p_id='".$pur['p_id']."'";
	$result = $thisDb->dbUpdate($sqlDel);
writeLog("DELETE_PUR: s_id=".$pur['s_id']);
	if ($result <= 0)
	{
		$thisDb->dbClose();
		return FALSE;
	}
	// Update inventory
	if($puritems == NULL || count($puritems) <= 0)
	{
		$thisDb->dbClose();
		return TRUE;
	}	
	
	for ($i=0; $i<count($puritems); $i++)
	{
		$sqlUpdate = "UPDATE inventory SET count=count-".$puritems[$i]['count'].", count_a=count_a-".$puritems[$i]['count'].
						", cost='".$puritems[$i]['old_cost']."', price='".$puritems[$i]['old_price']."', time_updated='".date('Y-m-d H:i:s').
						"' WHERE i_id='".$puritems[$i]['i_id']."'";
		$thisDb->dbUpdate($sqlUpdate);	
		
		// Add log to history
		$in = array();
		$in['i_id'] = $puritems[$i]['i_id'];
		$in['count'] = $puritems[$i]['count'];
		$in['cost'] = $puritems[$i]['cost'];
		$in['price'] = $puritems[$i]['price'];
		$in['src_id'] = $puritems[$i]['p_id'];
		$log = initInvLog($in, 11);
		dbAddInvLog($log);
	}
	
	// Update inv_variant
	if($purvariants == NULL || count($purvariants) <= 0)
	{
		$thisDb->dbClose();
		return TRUE;
	}	
	for ($i=0; $i<count($purvariants); $i++)
	{
		for ($j=0; $j<count($purvariants[$i]); $j++)
		{
			$sqlUpdate = "UPDATE inv_variant SET amount=amount-".$purvariants[$i][$j]['count']." WHERE iv_id='".$purvariants[$i][$j]['iv_id']."'";
			$result = $thisDb->dbUpdate($sqlUpdate);
		}
	}
	
	$thisDb->dbClose();
	
	return $result;
}

function dbQueryPurs($timeFrom, $timeTo, $sId, $iCode) {
	$thisDb = new myDatabase($_SESSION['uDb']);
	
	$sql = "SELECT * FROM purchase";
	$sqlWhere = "";
	if ($timeFrom != NULL && $timeTo != NULL) {
		$sqlWhere = $sqlWhere."p_date>='".$timeFrom." 00:00:00' AND p_date<='".$timeTo." 23:59:59'";
	}
	if ($sId != NULL) {
		if ($sqlWhere != "")
			$sqlWhere = $sqlWhere." AND s_id='".$sId."'";
		else
			$sqlWhere = $sqlWhere." WHERE s_id='".$sId."'";
	}
	if ($iCode != NULL) {
		$sqlCode = "(SELECT items.p_id FROM pur_items AS items, inventory AS invs WHERE items.i_id=invs.i_id AND invs.i_code='".$iCode."')";
		if ($sqlWhere != "")
			$sqlWhere = $sqlWhere." AND p_id IN ".$sqlCode;
		else
			$sqlWhere = $sqlWhere." WHERE p_id IN ".$sqlCode;
	}
	if ($sqlWhere != "") {
		$sql = $sql." WHERE ".$sqlWhere;
	}
	$sql = $sql." ORDER BY p_date DESC";

	$result = $thisDb->dbQuery($sql);
	$thisDb->dbClose();
	
	return $result;
}

function dbQueryPurItems($timefrom, $timeto, $sId)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT invs.i_code, invs.path, invs.m_no, purs.*, items.* 
			FROM inventory AS invs, purchase AS purs, pur_items AS items
			WHERE purs.p_id=items.p_id AND items.i_id=invs.i_id
			AND p_date>='".$timefrom." 00:00:00' AND p_date<='".$timeto." 23:59:59' AND purs.s_id='".$sId."' ORDER BY p_date DESC";
	$thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $thisQuery;
}

function dbGetAllPurs()
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT * FROM purchase";   
	$thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $thisQuery;
}

function dbQueryPurBySpId($spId)
{	
	$thisDb = new myDatabase($_SESSION['uDb']);	
	$sqlQuery = "SELECT * FROM purchase WHERE sp_id ='".$spId."'";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	if($thisQuery <= 0)
		return $thisQuery;
	else
		return $thisQuery[0];
}

function dbQueryPurById($pId)
{
	$thisDb = new myDatabase($_SESSION['uDb']);	
	$sqlQuery = "SELECT * FROM purchase WHERE p_id ='".$pId."'";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	if($thisQuery <= 0)
		return $thisQuery;
	else
		return $thisQuery[0];
}

function dbQueryPurItemsById($pId)
{
	$thisDb = new myDatabase($_SESSION['uDb']);	
	$sqlQuery = "SELECT items.*, inv.i_code, inv.i_name, inv.unit, inv.path, inv.m_no, inv.code1, inv.position FROM pur_items AS items, inventory AS inv WHERE p_id='".$pId.
					"' AND inv.i_id = items.i_id";
	$thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();

	return $thisQuery;
}

/***********************************************************************************************
	Sales Report
/**********************************************************************************************/
// 2021-02-19:	support unit
function dbGetSalesReport($timefrom, $timeto)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT invs.*, sum(items.count) AS count_sum, sum(items.count*items.unit*((items.price*(100-items.discount))/100)*(1-orders.discount_rate/100)) AS price_sum
					FROM inventory AS invs, orders AS orders, order_items AS items 
					WHERE items.i_id=invs.i_id AND orders.o_id=items.o_id 
					AND orders.date>='".$timefrom." 00:00:00' AND orders.date<='".$timeto." 23:59:59' GROUP BY invs.i_id";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();

	return $thisQuery;
}

function dbGetSalesReportById($iId, $timefrom, $timeto)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT orders.k_id, sum(items.count) AS k_count_sum, sum(items.count*items.unit*((items.price*(100-items.discount))/100)*(1-orders.discount_rate/100)) AS k_price_sum 
					FROM orders AS orders, order_items AS items 
					WHERE orders.o_id=items.o_id AND items.i_id='".$iId.
					"' AND orders.date>='".$timefrom." 00:00:00' AND orders.date<='".$timeto." 23:59:59'
					GROUP BY orders.k_id ORDER BY k_price_sum DESC";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();

	return $thisQuery;
}
/*
function dbGetPurReportById($iId, $timefrom, $timeto)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT items.cost, items.unit, sum(items.count) AS count_sum, sum(items.count*items.unit*items.cost) AS cost_sum 
					FROM purchase AS purs, pur_items AS items 
					WHERE items.i_id='".$iId."' AND purs.p_id=items.p_id 
					AND purs.p_date>='".$timefrom." 00:00:00' AND purs.p_date<='".$timeto." 23:59:59'";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();

	return $thisQuery;
}
*/
function dbGetPurReportById($iId, $timefrom, $timeto)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT purs.p_date, items.cost, items.count, items.unit 
					FROM purchase AS purs, pur_items AS items 
					WHERE items.i_id='".$iId."' AND purs.p_id=items.p_id 
					AND purs.p_date>='".$timefrom." 00:00:00' AND purs.p_date<='".$timeto." 23:59:59'";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();

	return $thisQuery;
}

function dbGetSalesReportByCust($timefrom, $timeto)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT orders.k_id, SUM(items.count*items.unit) AS count_sum, SUM(items.count*items.unit*((items.price*(100-items.discount))/100)*(1-orders.discount_rate/100)) AS price_sum
			FROM orders AS orders, order_items AS items
			WHERE orders.o_id=items.o_id AND orders.date>='".$timefrom."  00:00:00' AND orders.date<='".$timeto." 23:59:59'
			GROUP BY orders.k_id";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();

	return $thisQuery;
}
// 2021-02-19:	support unit
function dbGetSalesDetailsByCust($kId, $timefrom, $timeto)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT invs.i_id, invs.i_code, invs.i_name, invs.path, invs.m_no, SUM(items.count*items.unit) AS count_sum, SUM(items.count*items.unit*((items.price*(100-items.discount))/100)*(1-orders.discount_rate/100)) AS price_sum 
					FROM orders AS orders, order_items AS items, inventory AS invs 
					WHERE orders.o_id=items.o_id AND items.i_id=invs.i_id
					AND orders.date>='".$timefrom." 00:00:00' AND orders.date<='".$timeto." 23:59:59' 
					AND orders.k_id='".$kId."' GROUP BY invs.i_id ORDER BY price_sum DESC";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();

	return $thisQuery;
}

function dbGetSalesReportBySup($timefrom, $timeto)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT s_id, SUM(count_sum) AS count_sum, SUM(cost_sum) AS cost_sum FROM purchase WHERE p_date>='".$timefrom." 00:00:00' AND p_date<='".$timeto." 23:59:59' GROUP BY s_id";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();

	return $thisQuery;
}
// 2021-02-19: support unit
function dbGetSalesReportBySupOrders($timefrom, $timeto)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT invs.s_id, SUM(items.count*items.unit) AS count_sum, SUM(items.count*items.unit*((items.price*(100-items.discount))/100)*(1-orders.discount_rate/100)) AS price_sum
					FROM inventory AS invs, orders AS orders, order_items AS items 
					WHERE items.i_id=invs.i_id AND orders.o_id=items.o_id 
					AND orders.date>='".$timefrom." 00:00:00' AND orders.date<='".$timeto." 23:59:59' GROUP BY invs.s_id";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();

	return $thisQuery;
}
// 2021-02-19: support unit
function dbGetSalesReportByType($timefrom, $timeto, $country = "")
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	if($country == "")
		$sqlQuery = "SELECT invs.t_id, sum(items.count*items.unit) AS count_sum, sum(items.count*items.unit*((items.price*(100-items.discount))/100)*(1-orders.discount_rate/100)) AS price_sum 
					FROM orders AS orders, order_items AS items, inventory AS invs 
					WHERE orders.o_id=items.o_id  AND invs.i_id=items.i_id
					AND orders.date>='".$timefrom." 00:00:00' AND orders.date<='".$timeto." 23:59:59' GROUP BY invs.t_id";
	else
		$sqlQuery = "SELECT invs.t_id, sum(items.count*items.unit) AS count_sum, sum(items.count*items.unit*((items.price*(100-items.discount))/100)*(1-orders.discount_rate/100)) AS price_sum 
					FROM orders AS orders, order_items AS items, inventory AS invs 
					WHERE orders.o_id=items.o_id  AND invs.i_id=items.i_id
					AND orders.date>='".$timefrom." 00:00:00' AND orders.date<='".$timeto." 23:59:59' AND k_id IN (SELECT k_id FROM customer WHERE country = '".$country."') GROUP BY invs.t_id";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();

	return $thisQuery;
}


function dbGetReportByType()
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT invs.t_id, sum(invs.count) AS tcount FROM inventory AS invs GROUP BY invs.t_id";
	$thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();

	return $thisQuery;
}

function dbGetSalesReportByPay($timefrom, $timeto)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT SUM(pay_cash) AS cash, SUM(pay_card) AS card, SUM(pay_bank) AS bank, SUM(pay_check) AS scheck, SUM(pay_other) AS other 
				FROM orders WHERE date>='".$timefrom." 00:00:00' AND date<='".$timeto." 23:59:59'";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();

	return $thisQuery;
}

/*********************************************************************************************************************************
	inv_variant 
	2021-02-01: new function: dbQueryVariant(), dbDeleteVariant()
**********************************************************************************************************************************/
// Add variant
function dbAddVariant($variant)
{
	if ($variant == NULL || $variant['i_id'] == NULL || $variant['i_id'] == "")
		return 0;
	
	$thisDb = new myDatabase($_SESSION['uDb']);
	if ($variant['m_no'] == NULL || $variant['m_no'] == "")
		$sqlInsert = "INSERT INTO inv_variant(i_id, variant, amount, barcode, m_no, size) 
			VALUES('".$variant['i_id']."','".$variant['variant']."','".$variant['amount']."','".$variant['barcode']."',NULL,'".$variant['size']."')";
	else
		$sqlInsert = "INSERT INTO inv_variant(i_id, variant, amount, barcode, m_no, size) 
			VALUES('".$variant['i_id']."','".$variant['variant']."','".$variant['amount']."','".$variant['barcode']."','".$variant['m_no']."','".$variant['size']."')";
	$result = $thisDb->dbInsertId($sqlInsert);
	$thisDb->dbClose();
	
	return $result;
}
// Update variant
function dbUpdateVariant($variant)
{
	if ($variant == NULL || $variant['iv_id'] == NULL || $variant['iv_id'] == "")
		return FALSE;
	
	$thisDb = new myDatabase($_SESSION['uDb']);
	if ($variant['m_no'] == NULL || $variant['m_no'] == "")
		$sqlUpdate = "UPDATE inv_variant SET variant='".$variant['variant']."', amount='".$variant['amount'].
			"', barcode='".$variant['barcode']."',size='".$variant['size']."', m_no=NULL WHERE iv_id='".$variant['iv_id']."'";
	else
		$sqlUpdate = "UPDATE inv_variant SET variant='".$variant['variant']."', amount='".$variant['amount'].
			"', barcode='".$variant['barcode']."',size='".$variant['size']."', m_no='".$variant['m_no']."' WHERE iv_id='".$variant['iv_id']."'";
	$result = $thisDb->dbUpdate($sqlUpdate);
	$thisDb->dbClose();
	
	return TRUE;
}
// Delete variant
function dbDeleteVariant($variant)
{
	if ($variant == NULL || $variant['iv_id'] == NULL || $variant['iv_id'] == "")
		return 0;
	
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlDelete = "DELETE FROM inv_variant WHERE iv_id='".$variant['iv_id']."'";
	$result = $thisDb->dbUpdate($sqlDelete);
	$thisDb->dbClose();
	
	return $result;
}
// Query variant
function dbQueryVariant($iId)
{
	if ($iId == NULL || $iId == "")
		return 0;
	
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT * FROM inv_variant WHERE i_id='".$iId."' ORDER BY variant ASC";
	$result = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $result;
}
/*********************************************************************************************************************************
	Purchase Variant
**********************************************************************************************************************************/
// Query pur_variant
function dbQueryPurVariantById($pId)
{
	$thisDb = new myDatabase($_SESSION['uDb']);	
	$sqlQuery = "SELECT pv.pv_id, pv.p_id, pv.count, vr.* FROM pur_variant AS pv, inv_variant AS vr
				WHERE vr.iv_id=pv.iv_id AND pv.p_id='".$pId."' ORDER BY i_id, variant ASC";
	$thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();

	return $thisQuery;
}
// Add pur_variant
function dbAddPurVariant($purvariant)
{ 
	if(count($purvariant) <= 0)
		return TRUE;
	
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlInsert = "INSERT INTO pur_variant(p_id, i_id, iv_id, count) VALUES";
	for ($i=0; $i<count($purvariant); $i++)
	{
		if ($purvariant[$i]['count'] == "")
			$vcount = "0";			
		else
			$vcount = $purvariant[$i]['count'];
		$sqlInsert = $sqlInsert."(";
		$sqlInsert = $sqlInsert."'".$purvariant[$i]['p_id']."','".$purvariant[$i]['i_id']."','".$purvariant[$i]['iv_id']."','".$vcount."'";
		if ($i < count($purvariant)-1) 
			$sqlInsert = $sqlInsert."),";
		else
			$sqlInsert = $sqlInsert.")";
	}
	$thisDb->dbInsert($sqlInsert);
	// Update inv_variant
	for ($i=0; $i<count($purvariant); $i++)
	{
		if ($purvariant[$i]['count'] == "")
			$vcount = "0";			
		else
			$vcount = $purvariant[$i]['count'];
		$sqlUpdate = "UPDATE inv_variant SET amount=amount+".$vcount." WHERE iv_id='".$purvariant[$i]['iv_id']."'";
		$thisDb->dbUpdate($sqlUpdate);
	}
	
	$thisDb->dbClose();
	
	return TRUE;
}
// Add one pur_variant
function dbAddPurVariantOne($purvariant)
{ 
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlInsert = "INSERT INTO pur_variant(p_id, i_id, iv_id, count) VALUES('".
					$purvariant->p_id."','".$purvariant->i_id."','".$purvariant->iv_id."','".$purvariant->count."')";
	$thisDb->dbInsert($sqlInsert);
	$thisDb->dbClose();
	
	return TRUE;
}
// Add pur_variant
function dbAddPurVariantFromInv($purvariant)
{ 
	if(count($purvariant) <= 0)
		return TRUE;
	
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlInsert = "INSERT INTO pur_variant(p_id, i_id, iv_id, count) VALUES";
	for ($i=0; $i<count($purvariant); $i++)
	{
		if ($purvariant[$i]['count'] == "")
			$vcount = "0";			
		else
			$vcount = $purvariant[$i]['count'];
		$sqlInsert = $sqlInsert."(";
		$sqlInsert = $sqlInsert."'".$purvariant[$i]['p_id']."','".$purvariant[$i]['i_id']."','".$purvariant[$i]['iv_id']."','".$vcount."'";
		if ($i < count($purvariant)-1) 
			$sqlInsert = $sqlInsert."),";
		else
			$sqlInsert = $sqlInsert.")";
	}
	$thisDb->dbInsert($sqlInsert);
	$thisDb->dbClose();
writeLog("ADD VARIANT FROM PUR: ".$purvariant[0]['i_id']);	
	return TRUE;
}
// Update pur_variant
function dbUpdatePurVariant($purvariant)
{ 
	if(count($purvariant) <= 0)
		return TRUE;
	
	$thisDb = new myDatabase($_SESSION['uDb']);
	for ($i=0; $i<count($purvariant); $i++)
	{
		if ($purvariant[$i]['count'] == "")
			$vcount = "0";			
		else
			$vcount = $purvariant[$i]['count'];	
		// update pur_variant		
		$sqlUpdate = "UPDATE pur_variant SET count='".$vcount.
			"' WHERE i_id='".$purvariant[$i]['i_id']."' AND p_id='".$purvariant[$i]['p_id']."' AND iv_id='".$purvariant[$i]['iv_id']."'";
		$thisDb->dbUpdate($sqlUpdate);
		// update inv_variant
		$sqlUpdate = "UPDATE inv_variant SET amount=amount+".$purvariant[$i]['count_diff']." WHERE iv_id='".$purvariant[$i]['iv_id']."';";
		$thisDb->dbUpdate($sqlUpdate);
	}
	$thisDb->dbClose();
	
	return TRUE;
}
// Delete pur_variant
function dbDeletePurVariant($purvariant)
{ 
	if(count($purvariant) <= 0)
		return TRUE;
	
	$thisDb = new myDatabase($_SESSION['uDb']);
	for ($i=0; $i<count($purvariant); $i++)
	{
		// delete pur_variant		
		$sqlDelete = "DELETE FROM pur_variant WHERE i_id='".$purvariant[$i]['i_id']."' AND p_id='".$purvariant[$i]['p_id']."'";
		$thisDb->dbUpdate($sqlDelete);
		// update inv_variant
		$sqlUpdate = "UPDATE inv_variant SET amount=amount-".$purvariant[$i]['count']." WHERE iv_id='".$purvariant[$i]['iv_id']."';";
		$thisDb->dbUpdate($sqlUpdate);
	}
	$thisDb->dbClose();
	
	return TRUE;
}
/*********************************************************************************************************************************
	Order Variant
**********************************************************************************************************************************/
// Query order_variant
function dbQueryOrderVariantById($oId)
{
	$thisDb = new myDatabase($_SESSION['uDb']);	
	$sqlQuery = "SELECT ov.ov_id, ov.o_id, ov.count, vr.* FROM order_variant AS ov, inv_variant AS vr
				WHERE vr.iv_id=ov.iv_id AND ov.o_id='".$oId."' ORDER BY i_id, variant ASC";
	$thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();

	return $thisQuery;
}
// Add order_variant
function dbAddOrderVariant($ordervariant)
{ 
	if(count($ordervariant) <= 0)
		return FALSE;
	
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlInsert = "INSERT INTO order_variant(o_id, i_id, iv_id, count) VALUES";
	for ($i=0; $i<count($ordervariant); $i++)
	{
		if ($ordervariant[$i]['count'] == "")
			$vcount = "0";			
		else
			$vcount = $ordervariant[$i]['count'];
		$sqlInsert = $sqlInsert."(";
		$sqlInsert = $sqlInsert."'".$ordervariant[$i]['o_id']."','".$ordervariant[$i]['i_id']."','".$ordervariant[$i]['iv_id']."','".$vcount."'";
		if ($i < count($ordervariant)-1) 
			$sqlInsert = $sqlInsert."),";
		else
			$sqlInsert = $sqlInsert.")";
	}
	$thisDb->dbInsert($sqlInsert);
	// Update inv_variant
	for ($i=0; $i<count($ordervariant); $i++)
	{
		if ($ordervariant[$i]['count'] == "")
			$vcount = "0";			
		else
			$vcount = $ordervariant[$i]['count'];
		$sqlUpdate = "UPDATE inv_variant SET amount=amount-".$vcount." WHERE iv_id='".$ordervariant[$i]['iv_id']."'";
		$thisDb->dbUpdate($sqlUpdate);
	}
	
	$thisDb->dbClose();
	
	return TRUE;
}
// Update order_variant
function dbUpdateOrderVariant($ordervariant)
{ 
	if(count($ordervariant) <= 0)
		return TRUE;
	
	$thisDb = new myDatabase($_SESSION['uDb']);
	for ($i=0; $i<count($ordervariant); $i++)
	{
		if ($ordervariant[$i]['count'] == "")
			$vcount = "0";			
		else
			$vcount = $ordervariant[$i]['count'];
		// update order_variant
		$sqlUpdate = "UPDATE order_variant SET count='".$vcount.
			"' WHERE i_id='".$ordervariant[$i]['i_id']."' AND o_id='".$ordervariant[$i]['o_id']."' AND iv_id='".$ordervariant[$i]['iv_id']."'";;
		$thisDb->dbUpdate($sqlUpdate);
		// update inv_variant
		$sqlUpdate = "UPDATE inv_variant SET amount=amount-".$ordervariant[$i]['count_diff']." WHERE iv_id='".$ordervariant[$i]['iv_id']."'";
		$thisDb->dbUpdate($sqlUpdate);
	}	
	$thisDb->dbClose();
	
	return TRUE;
}
// Delete order_variant
function dbDeleteOrderVariant($ordervariant)
{ 
	if(count($ordervariant) <= 0)
		return TRUE;
	
	$thisDb = new myDatabase($_SESSION['uDb']);
	for ($i=0; $i<count($ordervariant); $i++)
	{
		// delete order_variant		
		$sqlDelete = "DELETE FROM order_variant WHERE i_id='".$ordervariant[$i]['i_id']."' AND o_id='".$ordervariant[$i]['o_id']."'";
		$result = $thisDb->dbUpdate($sqlDelete);
		// update inv_variant
		$sqlUpdate = "UPDATE inv_variant SET amount=amount+".$ordervariant[$i]['count']." WHERE iv_id='".$ordervariant[$i]['iv_id']."'";
		$result = $thisDb->dbUpdate($sqlUpdate);
	}
	$thisDb->dbClose();
	
	return TRUE;
}
/*********************************************************************************************************************************
	UNITS
**********************************************************************************************************************************/
// Query all units
function dbQueryUnits()
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT * FROM units ORDER BY units ASC";
	$result = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $result;	
}
// Add new units
function dbAddUnits($units)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlInsert = "INSERT INTO units(units) VALUES('".$units."')";
	$result = $thisDb->dbInsert($sqlInsert);
	$thisDb->dbClose();
	
	return $result;	
}
// Delete units
function dbDelUnits($id)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlDelete = "DELETE FROM units WHERE ut_id='".$id."'";
	$result = $thisDb->dbUpdate($sqlDelete);
	$thisDb->dbClose();
	
	return $result;	
}
/*********************************************************************************************************************************
	VARIANTS (table: variants)
**********************************************************************************************************************************/
// Query all variants
function dbQueryVariants()
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT * FROM variants ORDER BY variant ASC";
	$result = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $result;	
}
// Add new variant
function dbAddVariants($variant)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlInsert = "INSERT INTO variants(variant) VALUES('".$variant."')";
	$result = $thisDb->dbInsert($sqlInsert);
	$thisDb->dbClose();
	
	return $result;	
}
// Delete variant
function dbDelVariants($id)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlDelete = "DELETE FROM variants WHERE va_id='".$id."'";
	$result = $thisDb->dbUpdate($sqlDelete);
	$thisDb->dbClose();
	
	return $result;	
}

/*********************************************************************************************************************************
	APP Products
**********************************************************************************************************************************/
function dbAppProductsQuery()
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT aps.*, invs.i_code, invs.count, invs.price, invs.path 
			FROM app_products AS aps, inventory AS invs 
			WHERE aps.i_id=invs.i_id ORDER BY time_created DESC";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $thisQuery;	
}

function dbAppProductQueryById($iId)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT aps.*, invs.i_code, invs.count, invs.price 
			FROM app_products AS aps, inventory AS invs 
			WHERE aps.i_id=invs.i_id AND aps.i_id='".$iId."'";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();

	return $thisQuery[0];	
}

function dbAppProductAdd($data, $images) 
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sql = "INSERT INTO app_products (i_id, note, ap_t_id, state, collections, old_price, time_created, time_updated, m_no, zero_sale) 
					VALUES ('".$data['i_id']."','".$data['note']."','".$data['ap_t_id']."','".$data['state']."','".$data['collections']."','".$data['old_price']."','".
							date('Y-m-d H:i:s')."','".date('Y-m-d H:i:s')."','".$data['m_no']."','".$data['zero_sale']."')";
	$result = $thisDb->dbInsert($sql);
	$thisDb->dbClose();
	
	dbAppUpdateImages($images);
	
	return $result;
}

function dbAppProductUpdate($product, $images)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlUpdate = "UPDATE app_products SET note='".$product['note']."', ap_t_id='".$product['ap_t_id']."', state='".$product['state'].
					"', collections='".$product['collections']."', old_price='".$product['old_price'].
					"', time_updated='".date('Y-m-d H:i:s')."', m_no='".$product['m_no']."', zero_sale='".$product['zero_sale']."' WHERE i_id='".$product['i_id']."'";
	$result = $thisDb->dbUpdate($sqlUpdate);
	
	dbAppUpdateImages($images);
	
	return $result;
}
/*********************************************************************************************************************************
	APP Images
**********************************************************************************************************************************/
function dbAppImagesQuery($iId)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT * FROM app_images WHERE i_id='".$iId."'";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();

	return $thisQuery;	
}

function dbAppUpdateImages($images) 
{
	if ($images == NULL || count($images) == 0)
		return FALSE;
		
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sql = "DELETE FROM app_images WHERE i_id='".$images[0]['i_id']."'";
	$thisDb->dbUpdate($sql);
	
	for ($i=0; $i<count($images); $i++) {
		$sql = "INSERT INTO app_images(i_id, m_no) VALUES('".$images[$i]['i_id']."','".$images[$i]['m_no']."')";
		$thisDb->dbInsert($sql);
	}
	
	$thisDb->dbClose();
	
	return TRUE;
}

/*********************************************************************************************************************************
	APP Types
**********************************************************************************************************************************/
function dbAppTypesQuery()
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT * FROM app_types ORDER BY t_name ASC";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $thisQuery;	
}
function dbAppTypeAdd($tName)
{
	$thisDb = new myDatabase($_SESSION['uDb']);	
	$sql = "INSERT INTO app_types(t_name) VALUES('".$tName."')";
	$tId = $thisDb->dbInsertId($sql);	
	$thisDb->dbClose();
	
	return $tId;
}
function dbAppTypeUpdate($tId, $tName)
{
	$thisDb = new myDatabase($_SESSION['uDb']);	
	$sql = "UPDATE app_types SET t_name='".$tName."' WHERE ap_t_id='".$tId."'";
	$result = $thisDb->dbUpdate($sql);	
	$thisDb->dbClose();
	
	return $result;
}
function dbAppTypeDelete($tId)
{
	$thisDb = new myDatabase($_SESSION['uDb']);	
	$sql = "DELETE FROM app_types WHERE ap_t_id='".$tId."'";
	$result = $thisDb->dbUpdate($sql);	
	$thisDb->dbClose();
	
	return $result;
}

/*********************************************************************************************************************************
	COMMON
**********************************************************************************************************************************/
function dbUpdateTableCol($table, $col, $value, $col1, $value1)
{
	$thisDb = new myDatabase($_SESSION['uDb']);

	$sqlUpdate = "UPDATE ".$table." SET ".$col."='".$value."' WHERE ".$col1."='".$value1."'";
	$result = $thisDb->dbUpdate($sqlUpdate);
	$thisDb->dbClose();

	return $result;
}

/*********************************************************************************************************************************
	APP Users
**********************************************************************************************************************************/
function dbAppUsersQuery()
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT * 
			FROM app_company 
			ORDER BY time_created ASC";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $thisQuery;	
}

function dbAppUsersUpdate($id, $company)
{	
	$kCode = dbGetCustomerCode();
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sql = "INSERT INTO customer(k_code, k_name, address, post, city, country, ustno, whatsapp, tel, email, contact)
			VALUES('".$kCode."','".$company['apc_name']."','".$company['address']."','".$company['post']."','".$company['city']."','".$company['country']."','".
			$company['taxno']."','".$company['whatsapp']."','".$company['cell']."','".$company['email']."','".$company['contact']."')";
	$kId = $thisDb->dbInsertId($sql);
	if ($kId <= 0) {
		return -1;
	}
	$token = "";
	if($company['status'] == 1) $token = uniqid();
	$sql = "UPDATE app_company 
			SET status='".$company['status']."', token = '".$token."', message='".$company['msg']."', time_updated='".date('Y-m-d H:i:s')."', k_id='".$kId.
			"' WHERE apc_id='".$id."'";
    $result = $thisDb->dbUpdate($sql);
	$thisDb->dbClose();
	
	return $result;	
}

/****update order payed */
function dbUpdateOrderGroupPay($o_id, $isPayed){
	$thisDb = new myDatabase($_SESSION['uDb']);
	if($isPayed == 0){
		$sql = "UPDATE orders SET isPayed = 0, paidDatum = '' WHERE o_id IN (".$o_id.")";
	}else{
		$sql = "UPDATE orders SET isPayed = 1, paidDatum = '".date("Y-m-d")."' WHERE o_id IN (".$o_id.")";
	}
	$result = $thisDb->dbUpdate($sql);
	$thisDb->dbClose();
	return $result;	
}

/****update order payed */
function dbUpdatePurGroupPay($pid, $isPayed){
	$thisDb = new myDatabase($_SESSION['uDb']);
	if($isPayed == 0){
		$sql = "UPDATE purchase SET isPayed = 0, paidDatum = '' WHERE p_id IN (".$pid.")";
	}else{
		$sql = "UPDATE purchase SET isPayed = 1, paidDatum = '".date("Y-m-d")."' WHERE p_id IN (".$pid.")";
	}
	$result = $thisDb->dbUpdate($sql);
	$thisDb->dbClose();
	return $result;	
}

/*********************************************************************************************************************************
	ORDER PRICE
**********************************************************************************************************************************/
function dbOrderPriceQuery($kId, $iId) {
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sql = "SELECT items.oi_id, items.price, orders.date FROM order_items AS items, orders 
			WHERE orders.o_id=items.o_id AND orders.k_id='".$kId."' AND items.i_id='".$iId."' ORDER BY orders.date DESC";
	$result = $thisDb->dbQuery($sql);
	$thisDb->dbClose();
	
	return $result;
}

function dbInvPriceQuery($iId) {
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sql = "SELECT * FROM inv_price WHERE i_id='".$iId."' ORDER BY ip_id";
	$result = $thisDb->dbQuery($sql);
	$thisDb->dbClose();
	
	return $result;
}

function dbInvPriceUpdate($iId, $price) {
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sql = "DELETE FROM inv_price WHERE i_id='".$iId."'";
	$thisDb->dbUpdate($sql);
	$sql = "INSERT INTO inv_price(i_id, ip_id, price, note) VALUES";
	for ($i=0; $i<count($price); $i++) {
		$sql = $sql."(";
		$sql = $sql."'".$price[$i]['i_id']."','".$price[$i]['ip_id']."','".$price[$i]['price']."','".$price[$i]['note']."'";
		$sql = $sql.")";
		if ($i < count($price)-1) 
			$sql = $sql.",";
	}
	$result = $thisDb->dbInsert($sql);
	$thisDb->dbClose();
	
	return $result;
}

/*********************************************************************************************************************************
	APP REPORTS
**********************************************************************************************************************************/
function dbAppRptGetSales($timefrom, $timeto) {
	$thisDb = new myDatabase($_SESSION['uDb']);	
	$sqlQuery = 
			"SELECT DATE(date) AS dateonly, SUM(total_sum) as value FROM orders
				WHERE status >= 10 AND total_sum>0 AND date>='".$timefrom." 00:00:00' AND date<='".$timeto." 23:59:59' 
				GROUP BY dateonly ORDER BY dateonly ASC";
	$result = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $result;
}

function dbAppRptGetUsers($timefrom, $timeto) {
	$thisDb = new myDatabase($_SESSION['uDb']);	
	$sqlQuery = 
			"SELECT DATE(time_created) AS dateonly, COUNT(apc_id) as value FROM app_company
				WHERE time_created>='".$timefrom." 00:00:00' AND time_created<='".$timeto." 23:59:59' 
				GROUP BY dateonly ORDER BY dateonly ASC";
	$result = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $result;
}

function dbAppRptGetProducts($timefrom, $timeto) {
	$thisDb = new myDatabase($_SESSION['uDb']);	
	$sqlQuery = 
			"SELECT b.i_id, i.i_code, i.path, i.m_no, 
				SUM(CASE WHEN b.apb_type = 0 THEN 1 ELSE 0 END) AS browse, 
				SUM(CASE WHEN b.apb_type = 1 THEN 1 ELSE 0 END) AS favorite, 
				SUM(CASE WHEN b.apb_type = 2 THEN 1 ELSE 0 END) AS cart, 
				SUM(CASE WHEN b.apb_type = 3 THEN 1 ELSE 0 END) AS buy 
				FROM app_behavior AS b, inventory AS i 
				WHERE b.i_id=i.i_id AND time>='".$timefrom." 00:00:00' AND time<='".$timeto." 23:59:59' 
				GROUP BY b.i_id";
	$result = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $result;
}

/*********************************************************************************************************************************
	TEST
**********************************************************************************************************************************/
function dbTestKunCun() {
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sql = "SELECT sku.sis_barcode, invs.i_code, sku.sis_name, sku.sku, sku.sis_var, iv.variant, iv.amount, sku.state
			FROM sis_sku AS sku, inventory AS invs, inv_variant AS iv
			WHERE sku.sis_barcode=iv.barcode AND iv.i_id=invs.i_id AND iv.amount<=5 AND sku.state=0";
	$result = $thisDb->dbQuery($sql);
	$thisDb->dbClose();
	
	return $result;	
}

function dbTestKunCunGM() {
	$thisDb = new myDatabase($_SESSION['uDb']);
	$result = array();
	
	$sql = "SELECT pv.i_id, pv.iv_id, pv.count FROM purchase AS p, pur_variant AS pv 
				WHERE p.s_id='32' AND p.p_id=pv.p_id";
	$result_p = $thisDb->dbQuery($sql);
	
	$sql = "SELECT sku.sis_barcode, invs.i_code, sku.sis_name, sku.sku, sku.sis_var, invs.i_id, iv.iv_id, iv.variant, iv.amount
			FROM sis_sku AS sku, inventory AS invs, inv_variant AS iv
			WHERE sku.sis_barcode=iv.barcode AND iv.i_id=invs.i_id AND sku.state=0";
	$result_s = $thisDb->dbQuery($sql);
	
	$thisDb->dbClose();

	for ($i=0; $i<count($result_s); $i++) {
		if (intval($result_s[$i]['amount']) <= 5) {
			array_push($result, $result_s[$i]);
			continue;
		} 
		for ($j=0; $j<count($result_p); $j++) {
			if ($result_s[$i]['iv_id'] == $result_p[$j]['iv_id']) {
				$count = intval($result_s[$i]['amount']) - intval($result_p[$j]['count']);
				$result_s[$i]['amount'] = strval($count);
				if ($count <= 5) {
					array_push($result, $result_s[$i]);
				}					
			}
		}
	}
		
	return $result;	
}


?>
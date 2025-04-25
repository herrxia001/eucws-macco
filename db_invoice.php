<?php
/* INVOICE ONLY */
include_once 'database.php';

// Get a new invoice id from a_in_no, then increase this number by +1
// This is mostly used when converting an order to invoice. (order.php via AJAX)
function dbGetInvoiceNo($rId) 
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$yr = date("Y");
	$sqlQuery = "SELECT * FROM a_in_no WHERE year ='".$yr."'";
    $result = $thisDb->dbQuery($sqlQuery);
	if($result <= 0)
	{
		$invoiceNo = "1";
		$sqlInsert = "INSERT INTO a_in_no (year, invoice_no) VALUES('".$yr."','".$invoiceNo."')";
		$thisDb->dbInsert($sqlInsert);
		
	} else
	{
		$invoiceNo = $result[0]['invoice_no'];
	}
writeLog("GET_IN invoice r_id=".$rId.", invoice_no=".$invoiceNo);
	// invoice_no+1
	$newNo = intval($invoiceNo);
	$newNo++;
	$sqlUpdate = "UPDATE a_in_no SET invoice_no = '".$newNo."' WHERE year ='".$yr."'";
    $thisDb->dbUpdate($sqlUpdate);	

	if($invoiceNo < 10) $invoiceNo = "000".$invoiceNo;
	else if($invoiceNo < 100) $invoiceNo = "00".$invoiceNo;
	else if($invoiceNo < 1000) $invoiceNo = "0".$invoiceNo;
	$invoiceNo = substr($yr,2,2).$invoiceNo;
	
	// update a_invoice
	$sqlUpdate = "UPDATE a_invoice SET status='1', invoice_no='".$invoiceNo."' WHERE r_id='".$rId."'";
	$thisDb->dbUpdate($sqlUpdate);
	$thisDb->dbClose();
	
	return $invoiceNo;
}

function dbGetInvoiceNo_2($rId) 
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$yr = date("Y");
	$sqlQuery = "SELECT * FROM a_in_no WHERE year ='".$yr."'";
    $result = $thisDb->dbQuery($sqlQuery);
	if($result <= 0)
	{
		$invoiceNo = "1";
		
	} else
	{
		$invoiceNo = $result[0]['invoice_no'];
	}
	if($invoiceNo < 10) $invoiceNo = "000".$invoiceNo;
	else if($invoiceNo < 100) $invoiceNo = "00".$invoiceNo;
	else if($invoiceNo < 1000) $invoiceNo = "0".$invoiceNo;
	$invoiceNo = substr($yr,2,2).$invoiceNo;
	
	return $invoiceNo;
}

// Check invoice_no
function dbCheckInvoiceNo($inNo, $yr)
{
	$thisDb = new myDatabase($_SESSION['uDb']);

	$sqlQuery = "SELECT * FROM a_invoice WHERE invoice_no='".$inNo."'"; 
	$result = $thisDb->dbQuery($sqlQuery);
	if ($result > 0) {
		for ($i=0; $i<count($result); $i++) {
			if (substr($result[$i]['date'], 0, 4) == $yr)
				return FALSE;
		}
	}

	$sqlQuery = "SELECT * FROM a_in_void WHERE invoice_no='".$inNo."'"; 
	$result = $thisDb->dbQuery($sqlQuery);
	if ($result > 0) {
		for ($i=0; $i<count($result); $i++) {
			if (substr($result[$i]['date'], 0, 4) == $yr)
				return FALSE;
		}
	}
	
	$sqlQuery = "SELECT * FROM a_in_no WHERE year ='".$yr."'";
	$result = $thisDb->dbQuery($sqlQuery);
	$thisNo = $result[0]['invoice_no'];

	/*if (stripos($_SESSION['uDb'],"emily") !== false || stripos($_SESSION['uDb'],"clva") !== false) {
		if (strlen($inNo) <= 5)
			return FALSE;
		$newNo = substr($inNo, 5);
		if (intval($newNo) >= intval($thisNo) || intval($newNo) <= 0)
			return FALSE;
		else
			return $inNo;
	}*/
	$prefix = substr($inNo, 0, 2);
	$inNo = substr($inNo, 2);
	if (intval($inNo) >= intval($thisNo) || intval($inNo) <= 0)
		return FALSE;
	$inNo = intval($inNo);
	if($inNo < 10) $inNo = "000".$inNo;
	else if($inNo < 100) $inNo = "00".$inNo;
	else if($inNo < 1000) $inNo = "0".$inNo;
	$inNo = $prefix.$inNo;	

	return $inNo;
}


// Query all invoices matching selected time period.
// This is mostly used in displaying invoice list. (aordmgt.php via AJAX)
function dbQueryInvoices($timefrom, $timeto, $kId)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	if ($timefrom != NULL && $timeto != NULL)
	{
		if ($kId != NULL && $kId != "")
			$sqlQuery = "SELECT * FROM a_invoice WHERE date>='".$timefrom." 00:00:00' AND date<='".$timeto." 23:59:59' AND k_id='".$kId."' ORDER BY date DESC";	
		else
			$sqlQuery = "SELECT * FROM a_invoice WHERE date>='".$timefrom." 00:00:00' AND date<='".$timeto." 23:59:59' ORDER BY date DESC";
	}
	else
		$sqlQuery = "SELECT * FROM a_invoice";
	$thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $thisQuery;
}

// Query invoice by r_id.
// This is mostly used when an invoice is in view/edit action. (ainvoice.php via AJAX)
function dbQueryInvoiceById($rId)
{	
	$thisDb = new myDatabase($_SESSION['uDb']);	
	$sqlQuery = "SELECT * FROM a_invoice WHERE r_id ='".$rId."'";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	if($thisQuery <= 0)		
		return $thisQuery;
	
	return	$thisQuery[0];
}



// Query invoice by country & date.
// This is mostly used when an invoice is in view/edit action. (ainvoice.php via AJAX)
function dbQueryInvoiceByCountryDate($timefrom, $timeto,$country, $kid="", $fType="")
{	
	$thisDb = new myDatabase($_SESSION['uDb']);	
	$where = "WHERE 1=1";
	if($fType != "") $where .= " AND r_id IN (SELECT a.r_id FROM a_in_items a LEFT JOIN inventory i ON (a.i_id = i.i_id) WHERE i.t_id = '".$fType."')";
	if($kid != "") $where .= " AND k_id = '".$kid."'";
	


	if ($timefrom != NULL && $timeto != NULL)
	{
		if ($country != NULL && $country != "")
			$sqlQuery = "SELECT * FROM a_invoice ".$where." AND date>='".$timefrom." 00:00:00' AND date<='".$timeto." 23:59:59' AND k_id IN ( SELECT k_id FROM customer WHERE country = '".$country."') ORDER BY date DESC";	
		else
			$sqlQuery = "SELECT * FROM a_invoice ".$where." AND date>='".$timefrom." 00:00:00' AND date<='".$timeto." 23:59:59' ORDER BY date DESC";
	}
	else{
		if ($country != NULL && $country != "")
			$sqlQuery = "SELECT * FROM a_invoice ".$where." AND k_id IN ( SELECT k_id FROM customer WHERE country = '".$country."') ORDER BY date DESC";
		else
			$sqlQuery = "SELECT * FROM a_invoice ".$where." ORDER BY date DESC";
	}
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return	$thisQuery;
}

// Query invoice by o_id. 
// This is mostly used to check if an order has been already converted to invoice. (order.php via AJAX)
function dbQueryInvoiceByOrder($oId)
{	
	$thisDb = new myDatabase($_SESSION['uDb']);	
	
	$sqlQuery = "SELECT * FROM a_invoice WHERE o_id ='".$oId."'";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	
	$sqlQuery1 = "SELECT * FROM a_refund WHERE o_id ='".$oId."'";
    $thisQuery1 = $thisDb->dbQuery($sqlQuery1);
	
	$sqlVoid = "SELECT * FROM a_in_void WHERE o_id ='".$oId."'";
    $resultVoid = $thisDb->dbQuery($sqlVoid);
	
	$thisDb->dbClose();
	
	if($resultVoid > 0)		
		return $resultVoid[0];
		
	if($thisQuery <= 0)		
		return $thisQuery;
		
	if($thisQuery > 0 && $thisQuery1 > 0)		
		return 0;
		
	return	$thisQuery[0];
}

// Query invoice by invoice_no. 
function dbQueryInvoiceByNo($iNo, $year)
{	
	$thisDb = new myDatabase($_SESSION['uDb']);	
	$sqlQuery = "SELECT * FROM a_invoice WHERE invoice_no ='".$iNo."'";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	if($thisQuery <= 0)		
		return $thisQuery;
	
	for ($i=0; $i<count($thisQuery); $i++) {
		$thisYear = substr($thisQuery[$i]['date'], 0, 4);
		if ($thisYear == $year)
			return $thisQuery[$i];
	}
	return 0;
}

// Query invoice by status. 
// This is mostly used to query invoice. (a_neword.php via AJAX)
function dbQueryInvoiceByStatus($status)
{	
	$thisDb = new myDatabase($_SESSION['uDb']);	
	$sqlQuery = "SELECT * FROM a_invoice WHERE status ='".$iNo."' ORDER BY invoice_no DESC";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $thisQuery;
}

// All columns in table 'a_invoice' for update
function dbGetInvoiceUpdateColumns()
{
	$thisColumns = array('discount_rate','fee1','fee2','fee3','fee4','fee5',
							'count_sum', 'price_sum', 'total_sum', 'paid_sum', 'due',
							'pay_cash', 'pay_card', 'pay_bank', 'pay_check', 'pay_other', 'pay_paypal', 'profit', 'k_id', 'tax_rate', 'net', 'pay_vorkasse');
	
	return $thisColumns;
}
function dbGetInvoiceUpdateColumnNo()
{
	return 22;
}

// Update invoice
// This is mostly used in ainvoice.php via AJAX
function dbUpdateInvoice($order)
{
	$rId = $order['r_id'];
	if(!$rId or $rId == '')
		return FALSE;	
	
	$thisDb = new myDatabase($_SESSION['uDb']);
	$thisColumns = dbGetInvoiceUpdateColumns();
	$thisColumnNo = dbGetInvoiceUpdateColumnNo();
	$sqlSet = $thisColumns[0]."='".$order[$thisColumns[0]]."'";
	for ($i=1; $i<$thisColumnNo; $i++)
		$sqlSet = $sqlSet.",".$thisColumns[$i]."='".$order[$thisColumns[$i]]."'";
	$sqlUpdate = "UPDATE a_invoice SET ".$sqlSet." WHERE r_id ='".$rId."'"; 
	
	$result = $thisDb->dbUpdate($sqlUpdate);

	if($order['isPayed'] == "") $order['isPayed'] = 0;
	if($order['isPayed'] == 0) $order['paidDatum'] = "";
	$sqlUpdate = "UPDATE a_invoice SET isPayed = '".$order['isPayed']."', paidDatum = '".$order['paidDatum']."' WHERE r_id ='".$rId."'"; 
	$result = $thisDb->dbUpdate($sqlUpdate);

	// update customer in orders //
	$sqlUpdate = "UPDATE orders SET k_id = '".$order['k_id']."' WHERE o_id IN (SELECT o_id FROM a_invoice WHERE r_id = '".$rId."' )";
	$thisDb->dbUpdate($sqlUpdate);
	$thisDb->dbClose();
	
	return $result;
}

// Query a_in_items by r_id.
// This is mostly used when an invoice is in view/edit action. (ainvoice.php via AJAX)
function dbQueryInvoiceItems($rId)
{	
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery1 = "SELECT items.*, invs.i_code, invs.i_name, invs.path, invs.m_no, invs.unit
					FROM a_in_items AS items, inventory AS invs WHERE items.r_id='".$rId."' and invs.i_id=items.i_id";
    $thisQuery1 = $thisDb->dbQuery($sqlQuery1);
	$sqlQuery2 = "SELECT items.*, arts.a_code, arts.a_name
					FROM a_in_items AS items, a_art AS arts WHERE items.r_id='".$rId."' and arts.a_id=items.a_id AND items.i_id='0'";
    $thisQuery2 = $thisDb->dbQuery($sqlQuery2);
	$thisDb->dbClose();
	
	if ($thisQuery1 > 0 && $thisQuery2 > 0)
		$result = array_merge($thisQuery1, $thisQuery2);
	else if ($thisQuery2 > 0)
		$result = $thisQuery2;
	else
		$result = $thisQuery1;
	
	return $result;
}

// Add new a_in_item
// This is mostly used in ainvoice.php via AJAX
function dbAddInvoiceItemOne($orderitem)
{ 
	if($orderitem['r_id'] == '')
		return FALSE;
	
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlInsert = "INSERT INTO a_in_items(r_id, a_id, ai_id, ai_code, count, cost, price, i_id, discount) VALUES('".
			$orderitem['r_id']."','".$orderitem['a_id']."','".$orderitem['ai_id']."','".$orderitem['ai_code']."','".$orderitem['count']."','".$orderitem['cost']."','".$orderitem['price']."','".$orderitem['i_id']."','".$orderitem['discount']."')";
	$result = $thisDb->dbInsert($sqlInsert);
	dbUpdateArtByAid($orderitem, 0, $thisDb);			
	$thisDb->dbClose();
	
	return $result;
}

// Update a record in a_in_items.
// This is mostly used in ainvoice.php via AJAX
function dbUpdateInvoiceItemOne($orderitem, $option)
{ 
	if($orderitem['r_id'] == '')
		return FALSE;

	$thisDb = new myDatabase($_SESSION['uDb']);
	if ($orderitem['i_id'] != "0" && $orderitem['i_id'] != "" && $orderitem['i_id'] != NULL)
	{
		if ($option == 1)
			$sqlUpdate = "UPDATE a_in_items SET count=count-".$orderitem['count'].", price='".$orderitem['price']."', note='".$orderitem['note'].
						"', discount='".$orderitem['discount']."' WHERE r_id='".$orderitem['r_id']."' AND i_id='".$orderitem['i_id']."'";
		else
			$sqlUpdate = "UPDATE a_in_items SET count=count+".$orderitem['count'].", price='".$orderitem['price']."', note='".$orderitem['note'].
						"', discount='".$orderitem['discount']."' WHERE r_id='".$orderitem['r_id']."' AND i_id='".$orderitem['i_id']."'";	
	}
	else
	{
		if ($option == 1)
			$sqlUpdate = "UPDATE a_in_items SET count=count-".$orderitem['count'].", price='".$orderitem['price'].
						"', discount='".$orderitem['discount']."' WHERE r_id='".$orderitem['r_id']."' AND ai_id='".$orderitem['ai_id']."'";
		else
			$sqlUpdate = "UPDATE a_in_items SET count=count+".$orderitem['count'].", price='".$orderitem['price'].
						"', discount='".$orderitem['discount']."' WHERE r_id='".$orderitem['r_id']."' AND ai_id='".$orderitem['ai_id']."'";	
	}
	$result = $thisDb->dbUpdate($sqlUpdate);
	dbUpdateArtByAid($orderitem, $option, $thisDb);
	$thisDb->dbClose();
	
	return $result;
}

// Delete one a_in_item
// This is mostly used in ainvoice.php via AJAX
function dbDelInvoiceItemOne($orderitem)
{ 
	if($orderitem['r_id'] == '')
		return FALSE;
	
	$thisDb = new myDatabase($_SESSION['uDb']);
	if ($orderitem['i_id'] != "0" && $orderitem['i_id'] != "" && $orderitem['i_id'] != NULL)
		$sqlDel = "DELETE FROM a_in_items WHERE r_id='".$orderitem['r_id']."' AND i_id='".$orderitem['i_id']."'";
	else
		$sqlDel = "DELETE FROM a_in_items WHERE r_id='".$orderitem['r_id']."' AND ai_id='".$orderitem['ai_id']."'";
	$result = $thisDb->dbUpdate($sqlDel);
	dbUpdateArtByAid($orderitem, 1, $thisDb);	
	$thisDb->dbClose();
	
	return $result;
}

// Convert order to invoice
// This is used in order.php via AJAX
function dbCreateInvoiceFromOrder($order, $orderitems)
{
	if ($order['o_id'] == "" || count($orderitems) <= 0)
		return FALSE;
	// Database actions
	$thisDb = new myDatabase($_SESSION['uDb']);
	// 2021/01/05 no invoice number when creating
	$invoiceNo = "0";
	// Create a new invoice
	$sqlInsert = 
			"INSERT INTO a_invoice(invoice_no, o_id, k_id, u_id, date, lieferdatum, 
				discount_rate, fee1, fee2, fee3, fee4, fee5, 
				count_sum, price_sum, total_sum, paid_sum, due, 
				pay_cash, pay_card, pay_bank, pay_check, pay_other, pay_paypal, pay_vorkasse) 
				VALUES('".$invoiceNo."','".
				$order['o_id']."','".$order['k_id']."','".$_SESSION['uId']."','".date('Y-m-d H:i:s')."','".date('Y-m-d H:i:s')."','".
				$order['discount_rate']."','".$order['fee1']."','".$order['fee2']."','".$order['fee3']."','".$order['fee4']."','".$order['fee5']."','".
				$order['count_sum']."','".$order['price_sum']."','".$order['total_sum']."','".$order['paid_sum']."','".$order['due']."','".
				$order['pay_cash']."','".$order['pay_card']."','".$order['pay_bank']."','".$order['pay_check']."','".$order['pay_other']."','".$order['pay_paypal']."','".$order['pay_vorkasse'].
				"')";
	$rId = $thisDb->dbInsertId($sqlInsert);
	if ($rId <= 0)
	{
		$thisDb->dbClose();
		return FALSE;
	}
	// Create a_in_items
	for ($i=0; $i<count($orderitems); $i++)
	{		
		$aId = dbUpdateArtByIid($orderitems[$i], 0, $thisDb);
		$sqlInsert = 
			"INSERT INTO a_in_items(r_id, i_id, a_id, count, cost, price, unit, discount)
			VALUES('".$rId."','".$orderitems[$i]['i_id']."','".$aId."','".
				$orderitems[$i]['count']."','".$orderitems[$i]['cost']."','".$orderitems[$i]['price']."','".$orderitems[$i]['unit']."','".$orderitems[$i]['discount']."')";
		$result = $thisDb->dbInsert($sqlInsert);				
	}

	$thisDb->dbClose();
	
	return TRUE;	
}

// Create new invoice
function dbCreateInvoice()
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	// 20210105 no invoice_no when creating
	$invoiceNo = "0";
	$sqlInsert = 
			"INSERT INTO a_invoice(invoice_no, u_id, date, lieferdatum, status) 
				VALUES('".$invoiceNo."','".$_SESSION['uId']."','".date('Y-m-d H:i:s')."','".date('Y-m-d H:i:s')."','0')";
	$rId = $thisDb->dbInsertId($sqlInsert);
	$thisDb->dbClose();
	
	return $rId;
}

// Update invoice status
function dbUpdateInvoiceStatus($rId, $status)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlUpdate = "UPDATE a_invoice SET status='".$status."' WHERE r_id='".$rId."'";
	$result = $thisDb->dbUpdate($sqlUpdate);
	$thisDb->dbClose();
	
	return $result;
}

// Update invoice date
function dbUpdateInvoiceDate($rId, $date, $lieferdate)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlUpdate = "UPDATE a_invoice SET date='".$date."', lieferdatum='".$lieferdate."' WHERE r_id='".$rId."'";
	$result = $thisDb->dbUpdate($sqlUpdate);
	$thisDb->dbClose();
	
	return $result;
}

// Update invoice_no
function dbUpdateInvoiceNo($rId, $invoiceNo)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlUpdate = "UPDATE a_invoice SET invoice_no='".$invoiceNo."' WHERE r_id='".$rId."'";
writeLog("UPD_IN invoice r_id=".$rId.", invoice_no=".$invoiceNo);
	$result = $thisDb->dbUpdate($sqlUpdate);
	$thisDb->dbClose();
	
	return $result;
}

function dbDelInvoice($order, $orderitems)
{
	if($order['r_id'] == '')
		return FALSE;
	
	$thisDb = new myDatabase($_SESSION['uDb']);
	// Delete a_invoice. a_in_items are deleted by DB trigger
	$sqlDel = "DELETE FROM a_invoice WHERE r_id='".$order['r_id']."'";
writeLog("DEL_IN invoice r_id=".$order['r_id'].", invoice_no=".$order['invoice_no']);
	$result = $thisDb->dbUpdate($sqlDel);
	if ($result <= 0)
	{
		$thisDb->dbClose();
		return FALSE;
	}

	// Update inventory
	if($orderitems == NULL || count($orderitems) <= 0)
	{
		$thisDb->dbClose();
		return TRUE;
	}	
	
	for ($i=0; $i<count($orderitems); $i++)
	{
		$result = dbUpdateArtByAid($orderitems[$i], 1, $thisDb);			
	}	
	$thisDb->dbClose();
	
	return TRUE;
}

// Query article by i_id
function dbQueryArtById($id)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT * FROM a_art WHERE a_id ='".$id."'";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();

	return $thisQuery;
}

// Query all articles
function dbQueryArticles()
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT * FROM a_art ORDER BY a_name ASC";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();

	return $thisQuery;
}

// Update article
function dbUpdateArticle($art)
{
	if ($art['a_id'] == "")
		return FALSE;
	
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlUpdate = "UPDATE a_art SET a_code='".$art['a_code']."', a_name='".$art['a_name']."', count='".$art['count']."', cost='".$art['cost']."' WHERE a_id='".$art['a_id']."'";
	$result = $thisDb->dbUpdate($sqlUpdate);
	$thisDb->dbClose();
	
	return $result;
}

// Update article by i_id
// 2021-02-18: added support for unit
function dbUpdateArtByIid($orderitem, $option, $thisDb)
{
	if ($thisDb == NULL)
		$thisDb = new myDatabase($_SESSION['uDb']);
	// Get t_id from inventory
	$iId = $orderitem['i_id'];
	$sqlQuery = "SELECT arts.a_id, invs.unit FROM a_art AS arts, inventory AS invs WHERE invs.t_id=arts.t_id AND invs.i_id='".$iId."'";
	$thisQuery = $thisDb->dbQuery($sqlQuery);
	if ($thisQuery <= 0)
		$aId = '0';
	else
		$aId = $thisQuery[0]['a_id'];
	
	if ($thisQuery[0]['unit'] == "1")
		$real_count = $orderitem['count'];
	else
		$real_count = intval($orderitem['count'])*intval($orderitem['unit']);
	if ($option != 0)
		$sqlUpdate = "UPDATE a_art SET count=count+".$real_count." WHERE a_id='".$aId."'";
	else
		$sqlUpdate = "UPDATE a_art SET count=count-".$real_count." WHERE a_id='".$aId."'";
	$result = $thisDb->dbUpdate($sqlUpdate);		
	
	return $aId;
}

// Update article by a_id
function dbUpdateArtByAid($orderitem, $option, $thisDb)
{
	if ($thisDb == NULL)
		$thisDb = new myDatabase($_SESSION['uDb']);
writeLog($orderitem['rf_id']);	
	$aId = $orderitem['a_id'];
	if ($orderitem['unit'] == "1")
		$real_count = $orderitem['count'];
	else
		$real_count = intval($orderitem['count'])*intval($orderitem['unit']);
writeLog($real_count);	
	if ($option != 0)
		$sqlUpdate = "UPDATE a_art SET count=count+".$real_count." WHERE a_id='".$aId."'";
	else
		$sqlUpdate = "UPDATE a_art SET count=count-".$real_count." WHERE a_id='".$aId."'";
	$result = $thisDb->dbUpdate($sqlUpdate);		
writeLog($sqlUpdate);	
	return $aId;
}

// Query sales
// 2021-02-19: support unit
function dbQueryArtSales($timefrom, $timeto, $option)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	if ($option != "0")
		$sqlQuery ="SELECT items.a_id, sum(items.count*items.unit) AS count, sum(((items.price*(100-items.discount))/100)*(1-inv.discount_rate/100)*items.unit*items.count) AS total  
				FROM a_invoice AS inv, a_in_items AS items
				WHERE inv.r_id=items.r_id AND date>'".$timefrom." 23:59:59' AND date<='".$timeto." 23:59:59'
				GROUP BY a_id ORDER BY a_id ASC";	
	else
		$sqlQuery ="SELECT items.a_id, sum(items.count*items.unit) AS count, sum(((items.price*(100-items.discount))/100)*(1-inv.discount_rate/100)*items.unit*items.count) AS total 
				FROM a_invoice AS inv, a_in_items AS items
				WHERE inv.r_id=items.r_id AND date>='".$timefrom." 00:00:00' AND date<='".$timeto." 23:59:59'
				GROUP BY a_id ORDER BY a_id ASC";	
	$thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $thisQuery;
}

// Query purchases
function dbQueryArtPurs($timefrom, $timeto, $option)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	if ($option != "0")
		$sqlQuery ="SELECT items.a_id, sum(items.count*items.unit) AS count, sum(items.cost*items.count*items.unit) AS total 
				FROM a_purs AS purs, a_pur_items AS items
				WHERE purs.f_id=items.f_id AND date>'".$timefrom." 23:59:59' AND date<='".$timeto." 23:59:59'
				GROUP BY a_id ORDER BY a_id ASC";	
	else
		$sqlQuery ="SELECT items.a_id, sum(items.count*items.unit) AS count, sum(items.cost*items.count*items.unit) AS total 
				FROM a_purs AS purs, a_pur_items AS items
				WHERE purs.f_id=items.f_id AND date>='".$timefrom." 00:00:00' AND date<='".$timeto." 23:59:59'
				GROUP BY a_id ORDER BY a_id ASC";
	$thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $thisQuery;
}

function dbQueryArtRefund($timefrom, $timeto, $option)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	if ($option != "0")
		$sqlQuery ="SELECT items.a_id, sum(items.count*items.unit) AS count, sum(((items.price*(100-items.discount))/100)*(1-inv.discount_rate/100)*items.unit*items.count) AS total
				FROM a_refund AS inv, a_rf_items AS items
				WHERE inv.rf_id=items.rf_id AND date>'".$timefrom." 23:59:59' AND date<='".$timeto." 23:59:59'
				GROUP BY a_id ORDER BY a_id ASC";	
	else
		$sqlQuery ="SELECT items.a_id, sum(items.count*items.unit) AS count, sum(((items.price*(100-items.discount))/100)*(1-inv.discount_rate/100)*items.unit*items.count) AS total
				FROM a_refund AS inv, a_rf_items AS items
				WHERE inv.rf_id=items.rf_id AND date>='".$timefrom." 00:00:00' AND date<='".$timeto." 23:59:59'
				GROUP BY a_id ORDER BY a_id ASC";	
	$thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $thisQuery;
}

/* PURCHASES */
// Query all purchases
function dbQueryPurInvoices($timefrom, $timeto, $sId, $sPay)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$where = "";
	if($sPay == 1) $where = " AND isPayed = 1";
	else if($sPay == 0) $where = " AND isPayed = 0";
	if ($timefrom != NULL && $timeto != NULL)
	{
		if ($sId != NULL && $sId != "")
			$sqlQuery = "SELECT * FROM a_purs WHERE date>='".$timefrom." 00:00:00' AND date<='".$timeto." 23:59:59' AND s_id='".$sId."'".$where." ORDER BY date DESC";	
		else
			$sqlQuery = "SELECT * FROM a_purs WHERE date>='".$timefrom." 00:00:00' AND date<='".$timeto." 23:59:59'".$where." ORDER BY date DESC";
	}
	else
		$sqlQuery = "SELECT * FROM a_purs WHERE 1=1".$where;
	$thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $thisQuery;
}

// Add new purchase invoice
function dbCreatePurInvoice($pur, $puritems)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlInsert = 
			"INSERT INTO a_purs(p_id, s_id, u_id, date, count_sum, cost_sum, total_sum, discount, fee, tax, payment, isPayed, paidDatum)
			VALUES('".$pur['p_id']."','".$pur['s_id']."','".$_SESSION['uId']."','".$pur['date'].
			"','".$pur['count_sum']."','".$pur['cost_sum']."','".$pur['total_sum'].
			"','".$pur['discount']."','".$pur['fee']."','".$pur['tax']."','".$pur['payment']."','".$pur['isPayed']."','".$pur['paidDatum']."')";				
	$fId = $thisDb->dbInsertId($sqlInsert);
	if ($fId <= 0)
	{
		$thisDb->dbClose();
		return FALSE;
	}

	for ($i=0; $i<count($puritems); $i++)
	{
		$sqlInsert = 
			"INSERT INTO a_pur_items(f_id, a_id, count, cost, unit)
			VALUES('".$fId."','".$puritems[$i]['a_id']."','".$puritems[$i]['count']."','".$puritems[$i]['cost']."','".$puritems[$i]['unit']."')";				
		$result = $thisDb->dbInsert($sqlInsert);
		dbAddArt($puritems[$i], $thisDb);
	}
	
	$thisDb->dbClose();
	
	return TRUE;
}

function dbAddArt($puritem, $thisDb)
{
	if ($thisDb == NULL)
		$thisDb = new myDatabase($_SESSION['uDb']);
	
	$sqlQuery = "SELECT * FROM a_art WHERE a_id='".$puritem['a_id']."'";
	$thisQuery = $thisDb->dbQuery($sqlQuery);
	if ($thisQuery <= 0)
		return FALSE;

	$cost = $thisQuery[0]['cost'];
	$count = $thisQuery[0]['count'];
	if (intval($cost) <= 0 || intval($count) <= 0)
		$newCost = $puritem['cost'];
	else
		$newCost = ($count*$cost + $puritem['real_count']*$puritem['cost'])/($count + $puritem['real_count']);

	$sqlUpdate = "UPDATE a_art SET count=count+".$puritem['real_count'].", cost='".$newCost."' WHERE a_id='".$puritem['a_id']."'";
	$result = $thisDb->dbUpdate($sqlUpdate);		
	
	return TRUE;
}

function dbDeletePurInvoice($fId)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	
	$sqlQuery = "SELECT * FROM a_pur_items WHERE f_id='".$fId."'";
	$puritems = $thisDb->dbQuery($sqlQuery);
	
	for ($i=0; $i<count($puritems); $i++)
	{	
		$sqlQuery = "SELECT * FROM a_art WHERE a_id='".$puritems[$i]['a_id']."'";
		$thisQuery = $thisDb->dbQuery($sqlQuery);
		if ($thisQuery <= 0)
			continue;
		$cost = $thisQuery[0]['cost'];
		$count = $thisQuery[0]['count'];
		$real_count = $puritems[$i]['count']*$puritems[$i]['unit'];
		if ($real_count == $count)
			$newCost = 0;
		else
			$newCost = ($count*$cost - $real_count*$puritems[$i]['cost'])/($count -$real_count);
		$sqlUpdate = "UPDATE a_art SET count=count-".$real_count.", cost='".$newCost."' WHERE a_id='".$puritems[$i]['a_id']."'";
		$thisDb->dbUpdate($sqlUpdate);
	}
	
	$sqlDelete = "DELETE FROM a_purs WHERE f_id='".$fId."'";
	$thisDb->dbUpdate($sqlDelete);
	
	$thisDb->dbClose();
	
	return TRUE;	
}

// Query a_pur_items
function dbQueryAPurItems($fId)
{
	$thisDb = new myDatabase($_SESSION['uDb']);	
	$sqlQuery = "SELECT * FROM a_pur_items WHERE f_id='".$fId."'";
	$thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $thisQuery;
	
}

/************************************************************************
	REFUND
************************************************************************/
function dbCreateRefund($order, $orderitems)
{
	$rfNo = dbGetRefundNo("", 0);	
	if (!$rfNo)
		return FALSE;
		
	$thisDb = new myDatabase($_SESSION['uDb']);				
	// Create a_refund
	$sqlInsert = 
			"INSERT INTO a_refund(refund_no, invoice_no, o_id, k_id, u_id, date, invoice_date, 
				discount_rate, fee1, fee2, fee3, fee4, fee5, tax_rate,
				count_sum, price_sum, total_sum, net, paid_sum, due, 
				pay_cash, pay_card, pay_bank, pay_check, pay_other, pay_paypal, pay_vorkasse) 
				VALUES('".$rfNo."','".$order['invoice_no']."','".
				$order['o_id']."','".$order['k_id']."','".$_SESSION['uId']."','".date('Y-m-d H:i:s')."','".$order['date']."','".
				$order['discount_rate']."','".$order['fee1']."','".$order['fee2']."','".$order['fee3']."','".$order['fee4']."','".$order['fee5']."','".$order['tax_rate']."','".
				$order['count_sum']."','".$order['price_sum']."','".$order['total_sum']."','".$order['net']."','".$order['paid_sum']."','".$order['due']."','".
				$order['pay_cash']."','".$order['pay_card']."','".$order['pay_bank']."','".$order['pay_check']."','".$order['pay_other']."','".$order['pay_paypal']."','".$order['pay_vorkasse'].
				"')";
	$rfId = $thisDb->dbInsertId($sqlInsert);
	if (!$rfId)
		return FALSE;
	// Create a_rf_items
	$sqlInsert = "INSERT INTO a_rf_items(rf_id, i_id, a_id, ai_id, ai_code, count, cost, price, unit, note, discount) VALUES";
	for ($i=0; $i<count($orderitems); $i++)
	{	
		$sql = "SELECT i.i_code, a.a_id FROM inventory as i, a_art as a WHERE i.i_id='".$orderitems[$i]['i_id']."' AND a.t_id=i.t_id";
		$iResult = $thisDb->dbQuery($sql);
		$sqlInsert = $sqlInsert.
			"('".$rfId."','"."0"."','".$iResult[0]['a_id']."','".$i."','".$iResult[0]['i_code']."','".
			$orderitems[$i]['count']."','".$orderitems[$i]['cost']."','".$orderitems[$i]['price']."','".
			$orderitems[$i]['unit']."','".$orderitems[$i]['note']."','".$orderitems[$i]['discount']."')";
		if ($i < count($orderitems) - 1)
			$sqlInsert = $sqlInsert.",";
		dbUpdateArtByAid($orderitems[$i], 1, $thisDb);
						
	}
	$result = $thisDb->dbInsert($sqlInsert);
	$thisDb->dbClose();
	
	return TRUE;
}

function dbQueryRefundById($rfId)
{
	$thisDb = new myDatabase($_SESSION['uDb']);	
	$sqlQuery = "SELECT * FROM a_refund WHERE rf_id ='".$rfId."'";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	if($thisQuery <= 0)		
		return $thisQuery;
	
	return	$thisQuery[0];
}

function dbQueryRefundItems($rfId)
{	
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT items.*, arts.a_code, arts.a_name
					FROM a_rf_items AS items, a_art AS arts WHERE items.rf_id='".$rfId."' and arts.a_id=items.a_id";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	if($thisQuery <= 0)		
		return $thisQuery;
	
	return $thisQuery;
}

function dbDelRefund($order, $orderitems)
{
	if($order['rf_id'] == '')
		return FALSE;
	$thisDb = new myDatabase($_SESSION['uDb']);	
	$sqlDel = "DELETE FROM a_refund WHERE rf_id ='".$order['rf_id']."'";
    $result = $thisDb->dbUpdate($sqlDel);	
	if($result <= 0) {
		$thisDb->dbClose();		
		return FALSE;
	}
	
	if($orderitems == NULL || count($orderitems) <= 0) {
		$thisDb->dbClose();
		return TRUE;
	}		
	for ($i=0; $i<count($orderitems); $i++) {
		$result = dbUpdateArtByAid($orderitems[$i], 0, $thisDb);			
	}	
	$thisDb->dbClose();
	
	return	TRUE;
}

function dbQueryRefunds($timefrom, $timeto)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT ref.*, cust.k_name FROM a_refund AS ref, customer AS cust WHERE ref.k_id=cust.k_id";
	if ($timefrom != NULL && $timeto != NULL)
		$sqlQuery = $sqlQuery." AND ref.date>='".$timefrom." 00:00:00' AND ref.date<='".$timeto." 23:59:59'";
	$sqlQuery = $sqlQuery."	ORDER BY ref.date DESC";	
	$thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $thisQuery;
}

// Create new refund
function dbCreateRefundNew()
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlInsert = 
			"INSERT INTO a_refund(refund_no, invoice_no, u_id, date) 
				VALUES('0', '0', '".$_SESSION['uId']."','".date('Y-m-d H:i:s')."')";
	$rId = $thisDb->dbInsertId($sqlInsert);
	$thisDb->dbClose();
	
	return $rId;
}
// Add new a_rf_items
function dbAddRefundItemOne($orderitem)
{ 
	if($orderitem['rf_id'] == '')
		return FALSE;	
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlInsert = "INSERT INTO a_rf_items(rf_id, a_id, ai_id, ai_code, count, cost, price, discount) VALUES('".
			$orderitem['rf_id']."','".$orderitem['a_id']."','".$orderitem['ai_id']."','".$orderitem['ai_code']."','".$orderitem['count']."','".$orderitem['cost']."','".$orderitem['price']."','".$orderitem['discount']."')";
	$result = $thisDb->dbInsert($sqlInsert);	
	dbUpdateArtByAid($orderitem, 1, $thisDb);			
	$thisDb->dbClose();
	
	return $result;
}
// Get refund_no
Function dbGetRefundNo($rfId, $option)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT code_num FROM code_gen WHERE code_type ='f'";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	if($thisQuery <= 0)
		return 0;
	
	$thisNum = $thisQuery[0]['code_num'];	
	$newNum = intval($thisNum);
	$newNum++;
	$sqlUpdate = "UPDATE code_gen SET code_num = '".$newNum."' WHERE code_type ='f'";
    $thisQuery = $thisDb->dbUpdate($sqlUpdate);
	
	// update a_refund
	if ($option) {
		$sqlUpdate = "UPDATE a_refund SET status='1', refund_no='".$thisNum."' WHERE rf_id='".$rfId."'";
		$thisDb->dbUpdate($sqlUpdate);
	}
	
	return $thisNum;
}
// Update r_rfund
function dbGetRefundUpdateColumns()
{
	$thisColumns = array('discount_rate','fee1','fee2','fee3','fee4','fee5',
							'count_sum', 'price_sum', 'total_sum', 'paid_sum', 'due',
							'pay_cash', 'pay_card', 'pay_bank', 'pay_check', 'pay_other', 'pay_paypal', 'profit', 'k_id', 'tax_rate', 'net', 'pay_vorkasse');
	
	return $thisColumns;
}
function dbGetRefundUpdateColumnNo()
{
	return 21;
}
function dbUpdateRefund($order)
{
	$rfId = $order['rf_id'];
	if(!$rfId or $rfId == '')
		return FALSE;	
	
	$thisDb = new myDatabase($_SESSION['uDb']);
	$thisColumns = dbGetRefundUpdateColumns();
	$thisColumnNo = count($thisColumns);
	$sqlSet = $thisColumns[0]."='".$order[$thisColumns[0]]."'";
	for ($i=1; $i<$thisColumnNo; $i++)
		$sqlSet = $sqlSet.",".$thisColumns[$i]."='".$order[$thisColumns[$i]]."'";
	$sqlUpdate = "UPDATE a_refund SET ".$sqlSet." WHERE rf_id ='".$rfId."'"; 
	
	$result = $thisDb->dbUpdate($sqlUpdate);
	$thisDb->dbClose();
	
	return $result;
}

function dbUpdateRefundItemOne($orderitem, $option)
{ 
	if($orderitem['rf_id'] == '')
		return FALSE;

	$thisDb = new myDatabase($_SESSION['uDb']);
	if ($orderitem['i_id'] != "0" && $orderitem['i_id'] != "" && $orderitem['i_id'] != NULL)
	{
		if ($option == 1)
			$sqlUpdate = "UPDATE a_rf_items SET count=count-".$orderitem['count'].", price='".$orderitem['price']."', note='".$orderitem['note'].
						"' WHERE rf_id='".$orderitem['rf_id']."' AND i_id='".$orderitem['i_id']."'";
		else
			$sqlUpdate = "UPDATE a_rf_items SET count=count+".$orderitem['count'].", price='".$orderitem['price']."', note='".$orderitem['note'].
						"' WHERE rf_id='".$orderitem['rf_id']."' AND i_id='".$orderitem['i_id']."'";	
	}
	else
	{
		if ($option == 1)
			$sqlUpdate = "UPDATE a_rf_items SET count=count-".$orderitem['count'].", price='".$orderitem['price']."', ai_code='".$orderitem['ai_code'].
						"' WHERE rf_id='".$orderitem['rf_id']."' AND ai_id='".$orderitem['ai_id']."'";
		else
			$sqlUpdate = "UPDATE a_rf_items SET count=count+".$orderitem['count'].", price='".$orderitem['price']."', ai_code='".$orderitem['ai_code'].
						"' WHERE rf_id='".$orderitem['rf_id']."' AND ai_id='".$orderitem['ai_id']."'";	
	}
	$result = $thisDb->dbUpdate($sqlUpdate);
	if ($option == 1)
		$option = 0;
	else
		$option = 1;
	dbUpdateArtByAid($orderitem, $option, $thisDb);
	$thisDb->dbClose();
	
	return $result;
}

function dbDelRefundItemOne($orderitem)
{ 
	if($orderitem['rf_id'] == '')
		return FALSE;
	
	$thisDb = new myDatabase($_SESSION['uDb']);
	if ($orderitem['i_id'] != "0" && $orderitem['i_id'] != "" && $orderitem['i_id'] != NULL)
		$sqlDel = "DELETE FROM a_rf_items WHERE rf_id='".$orderitem['rf_id']."' AND i_id='".$orderitem['i_id']."'";
	else
		$sqlDel = "DELETE FROM a_rf_items WHERE rf_id='".$orderitem['rf_id']."' AND ai_id='".$orderitem['ai_id']."'";
	$result = $thisDb->dbUpdate($sqlDel);
	dbUpdateArtByAid($orderitem, 0, $thisDb);	
	$thisDb->dbClose();
	
	return $result;
}

function dbQueryRefundByNo($rNo)
{	
	$thisDb = new myDatabase($_SESSION['uDb']);	
	$sqlQuery = "SELECT * FROM a_refund WHERE refund_no ='".$rNo."'";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	if($thisQuery <= 0)		
		return $thisQuery;
	
	return $thisQuery[0];
}

/************************************************************************
	VOID
************************************************************************/
function dbVoidInvoice($order, $orderitems) {
	if($order['r_id'] == '')
		return FALSE;
	
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlInsert = 
			"INSERT INTO a_in_void(r_id, invoice_no, o_id, k_id, u_id, date, lieferdatum, 
				discount_rate, discount, fee1, fee2, fee3, fee4, fee5, tax_rate,
				count_sum, price_sum, total_sum, net, paid_sum, due, 
				pay_cash, pay_card, pay_bank, pay_check, pay_other, pay_paypal, pay_vorkasse,
				note, profit, status, void_date) 
				VALUES('".$order['r_id']."','".$order['invoice_no']."','".
				$order['o_id']."','".$order['k_id']."','".$order['u_id']."','".$order['date']."','".$order['lieferdatum']."','".
				$order['discount_rate']."','".$order['discount']."','".$order['fee1']."','".$order['fee2']."','".$order['fee3']."','".$order['fee4']."','".$order['fee5']."','".$order['tax_rate']."','".
				$order['count_sum']."','".$order['price_sum']."','".$order['total_sum']."','".$order['net']."','".$order['paid_sum']."','".$order['due']."','".
				$order['pay_cash']."','".$order['pay_card']."','".$order['pay_bank']."','".$order['pay_check']."','".$order['pay_other']."','".$order['pay_paypal']."','".$order['pay_vorkasse']."','".
				$order['note']."','".$order['profit']."','".$order['status']."','".date('Y-m-d H:i:s')."')";
	$result = $thisDb->dbInsertId($sqlInsert);
	if ($result <= 0) {
		$thisDb->dbClose();
		return FALSE;
	}	
	// Create a_in_void_items
	$sqlInsert = "INSERT INTO a_in_void_items(r_id, i_id, a_id, ai_id, ai_code, count, cost, price, unit, note, discount) VALUES";
	for ($i=0; $i<count($orderitems); $i++) {			
		$orderitems[$i]['ai_id'] == "" ? $aiId = "0" :  $aiId = $orderitems[$i]['ai_id'];
		$sqlInsert = $sqlInsert.
			"('".$orderitems[$i]['r_id']."','".$orderitems[$i]['i_id']."','".$orderitems[$i]['a_id']."','".$aiId."','".$orderitems[$i]['ai_code']."','".
			$orderitems[$i]['count']."','".$orderitems[$i]['cost']."','".$orderitems[$i]['price']."','".
			$orderitems[$i]['unit']."','".$orderitems[$i]['note']."','".$orderitems[$i]['discount']."')";
		if ($i < count($orderitems) - 1)
			$sqlInsert = $sqlInsert.",";						
	}
	$result = $thisDb->dbInsert($sqlInsert);
	
	// Delete a_invoice. a_in_items are deleted by DB trigger
	$sqlDel = "DELETE FROM a_invoice WHERE r_id='".$order['r_id']."'";
writeLog("VOID r_id=".$order['r_id'].", invoice_no=".$order['invoice_no']);
	$result = $thisDb->dbUpdate($sqlDel);
	if ($result <= 0){
		$thisDb->dbClose();
		return FALSE;
	}	
	
	for ($i=0; $i<count($orderitems); $i++){
		$result = dbUpdateArtByAid($orderitems[$i], 1, $thisDb);			
	}	
	$thisDb->dbClose();
	
	return TRUE;
}

function voidToInvoice($order, $orderitems){
	if($order['r_id'] == '')
		return FALSE;
	
	$thisDb = new myDatabase($_SESSION['uDb']);

	$sqlInsert = 
			"INSERT INTO a_invoice SELECT r_id, invoice_no, o_id, k_id, u_id, date, lieferdatum, 
				discount_rate, discount, fee1, fee2, fee3, fee4, fee5, tax_rate,
				count_sum, price_sum, total_sum, net, paid_sum, due, 
				pay_cash, pay_card, pay_bank, pay_check, pay_other, pay_paypal, pay_vorkasse,
				note, profit, status FROM a_in_void WHERE r_id = '".$order['r_id']."'";
	$thisDb->dbInsert($sqlInsert);

	$sqlInsert = "INSERT INTO a_in_items(r_id, i_id, a_id, ai_id, ai_code, count, cost, price, unit, note, discount) SELECT r_id, i_id, a_id, ai_id, ai_code, count, cost, price, unit, note, discount FROM a_in_void_items WHERE r_id = '".$order['r_id']."'";
	$thisDb->dbInsert($sqlInsert);

	$sqlDel = "DELETE FROM a_in_void WHERE r_id='".$order['r_id']."'";
	$result = $thisDb->dbUpdate($sqlDel);
	if ($result <= 0){
		$thisDb->dbClose();
		return FALSE;
	}


	for ($i=0; $i<count($orderitems); $i++){
		$result = dbUpdateArtByAid($orderitems[$i], 0, $thisDb);			
	}	
	$thisDb->dbClose();
	return TRUE;
}

function dbQueryInvoiceVoid($timefrom, $timeto, $kId) {
	$thisDb = new myDatabase($_SESSION['uDb']);
	if ($timefrom != NULL && $timeto != NULL) {
		if ($kId != NULL && $kId != "")
			$sqlQuery = "SELECT * FROM a_in_void WHERE date>='".$timefrom." 00:00:00' AND date<='".$timeto." 23:59:59' AND k_id='".$kId."' ORDER BY date DESC";	
		else
			$sqlQuery = "SELECT * FROM a_in_void WHERE date>='".$timefrom." 00:00:00' AND date<='".$timeto." 23:59:59' ORDER BY date DESC";
	}
	else
		$sqlQuery = "SELECT * FROM a_in_void";
	$thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $thisQuery;
}

function dbQueryInvoiceVoidItems($rId) {	
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery1 = "SELECT items.*, invs.i_code, invs.i_name, invs.path, invs.m_no, invs.unit
					FROM a_in_void_items AS items, inventory AS invs WHERE items.r_id='".$rId."' and invs.i_id=items.i_id";
    $thisQuery1 = $thisDb->dbQuery($sqlQuery1);
	$sqlQuery2 = "SELECT items.*, arts.a_code, arts.a_name
					FROM a_in_void_items AS items, a_art AS arts WHERE items.r_id='".$rId."' and arts.a_id=items.a_id AND items.i_id='0'";
    $thisQuery2 = $thisDb->dbQuery($sqlQuery2);
	$thisDb->dbClose();
	
	if ($thisQuery1 > 0 && $thisQuery2 > 0)
		$result = array_merge($thisQuery1, $thisQuery2);
	else if ($thisQuery2 > 0)
		$result = $thisQuery2;
	else
		$result = $thisQuery1;
	
	return $result;
}

function dbQueryInvoiceVoidByNo($iNo, $year) {	
	$thisDb = new myDatabase($_SESSION['uDb']);	
	$sqlQuery = "SELECT * FROM a_in_void WHERE invoice_no ='".$iNo."'";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	if($thisQuery <= 0)		
		return $thisQuery;
	
	for ($i=0; $i<count($thisQuery); $i++) {
		$thisYear = substr($thisQuery[$i]['date'], 0, 4);
		if ($thisYear == $year)
			return $thisQuery[$i];
	}
	return 0;
}

function dbQueryInvoiceVoidById($rId) {	
	$thisDb = new myDatabase($_SESSION['uDb']);	
	$sqlQuery = "SELECT * FROM a_in_void WHERE r_id ='".$rId."'";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	if($thisQuery <= 0)		
		return $thisQuery;
	
	return	$thisQuery[0];
}

/************************************************************************
	NEW REPORTS
************************************************************************/
function dbRptQueryArts() {
	$thisDb = new myDatabase($_SESSION['uDb']);	
	
	$sql = "SELECT a_id, a_name, count, cost 
				FROM a_art ORDER By a_name ASC";
    $result = $thisDb->dbQuery($sql);
	$thisDb->dbClose();
	
	return	$result;
}

function dbRptQuerySales($timefrom, $timeto) {
	$thisDb = new myDatabase($_SESSION['uDb']);	
	
	$sql = "SELECT a.a_id, SUM(ai.count) AS count_sale, SUM(ai.count*ai.price) as total_sale  
			FROM a_art AS a, a_in_items AS ai, a_invoice AS an 
			WHERE a.a_id=ai.a_id AND ai.r_id=an.r_id AND an.date>='".$timefrom." 00:00:00' AND an.date<='".$timeto." 23:59:59' ".
			"GROUP BY a.a_id ORDER By a.a_id ASC";
    $result = $thisDb->dbQuery($sql);
	$thisDb->dbClose();
	
	return	$result;
}

function dbRptQueryPurs($timefrom, $timeto) {
	$thisDb = new myDatabase($_SESSION['uDb']);	
	
	$sql = "SELECT a.a_id, SUM(pi.count) AS count_pur, SUM(pi.count*pi.cost) as total_pur  
			FROM a_art AS a, a_pur_items AS pi, a_purs AS pur 
			WHERE a.a_id=pi.a_id AND pi.f_id=pur.f_id AND pur.date>='".$timefrom." 00:00:00' AND pur.date<='".$timeto." 23:59:59' ".
			"GROUP BY a.a_id ORDER By a.a_id ASC";
    $result = $thisDb->dbQuery($sql);
	$thisDb->dbClose();
	
	return	$result;
}

function dbRptQueryRefunds($timefrom, $timeto) {
	$thisDb = new myDatabase($_SESSION['uDb']);	
	
	$sql = "SELECT a.a_id, SUM(ri.count) AS count_rf, SUM(ri.count*((ri.price*(100-ri.discount))/100)) as total_rf  
			FROM a_art AS a, a_rf_items AS ri, a_refund AS rf 
			WHERE a.a_id=ri.a_id AND ri.rf_id=rf.rf_id AND rf.date>='".$timefrom." 00:00:00' AND rf.date<='".$timeto." 23:59:59' ".
			"GROUP BY a.a_id ORDER By a.a_id ASC";
    $result = $thisDb->dbQuery($sql);
	$thisDb->dbClose();
	
	return	$result;
}

function dbQueryArtHist($year, $month) {
	$thisDb = new myDatabase($_SESSION['uDb']);	
	
	$sql = "SELECT * FROM a_art_hist WHERE year='".$year."' AND month='".$month."' ORDER BY a_name ASC";
    $result = $thisDb->dbQuery($sql);
	$thisDb->dbClose();
	
	return	$result;
}

function dbQueryArtHistLast($year, $month) {
	$thisDb = new myDatabase($_SESSION['uDb']);	
	
	$sql = "SELECT a_id, count, dep_count FROM a_art_hist WHERE year='".$year."' AND month='".$month."'";
    $result = $thisDb->dbQuery($sql);
	$thisDb->dbClose();
	
	return	$result;
}

function dbQueryArtHistById($id, $year, $month)
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT * FROM a_art_hist WHERE a_id ='".$id."' AND year='".$year."' AND month = ".$month;
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();

	return $thisQuery;
}

function dbUpdateArticleHist($art)
{
	if ($art['a_id'] == "")
		return FALSE;
	
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT dep_count FROM a_art_hist WHERE a_id='".$art['a_id']."' AND year='".$art['year']."' AND month='".$art['month']."'";
	$thisQuery = $thisDb->dbQuery($sqlQuery);
	$old_dep_count = $thisQuery[0]['dep_count'];
	$diff = $art['dep_count'] - $old_dep_count;

	$sqlUpdate = "UPDATE a_art_hist SET dep_count='".$art['dep_count']."' WHERE a_id='".$art['a_id']."' AND year='".$art['year']."' AND month='".$art['month']."'";
	$result = $thisDb->dbUpdate($sqlUpdate);

	$sqlUpdate = "UPDATE a_art_hist SET count = count - ".$diff." WHERE a_id='".$art['a_id']."' AND ((year >= '".$art['year']."' AND month > '".$art['month']."') OR (year > '".$art['year']."' AND month = '".$art['month']."'))";
	$result = $thisDb->dbUpdate($sqlUpdate);

	$sqlUpdate = "UPDATE a_art SET count = count - ".$diff." WHERE a_id='".$art['a_id']."'";
	$result = $thisDb->dbUpdate($sqlUpdate);

	$thisDb->dbClose();
	
writeLog($sqlUpdate);
	
	return $result;
}


function dbGetReportByTypeInvoice()
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT a_id, sum(count) AS tcount FROM a_art GROUP BY a_id";
	$thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();

	return $thisQuery;
}


// 2021-02-19: support unit
function dbGetSalesReportByTypeInvoice($timefrom, $timeto, $country = "")
{
	$thisDb = new myDatabase($_SESSION['uDb']);

	if($country == "")
		$sqlQuery = "SELECT items.a_id, sum(items.count*items.unit) AS count_sum, sum(items.count*items.unit*((items.price*(100-items.discount))/100)) AS price_sum 
					FROM a_invoice AS orders, a_in_items AS items
					WHERE orders.r_id=items.r_id 
					AND orders.date>='".$timefrom." 00:00:00' AND orders.date<='".$timeto." 23:59:59' GROUP BY items.a_id";
	else
		$sqlQuery = "SELECT items.a_id, sum(items.count*items.unit) AS count_sum, sum(items.count*items.unit*((items.price*(100-items.discount))/100)) AS price_sum 
					FROM a_invoice AS orders, a_in_items AS items 
					WHERE orders.r_id=items.r_id 
					AND orders.date>='".$timefrom." 00:00:00' AND orders.date<='".$timeto." 23:59:59' AND k_id IN (SELECT k_id FROM customer WHERE country = '".$country."') GROUP BY items.a_id";

	/*if($country == "")
		$sqlQuery = "SELECT items.a_id, sum(items.count*items.unit) AS count_sum, sum(items.count*items.unit*items.price*(1-orders.discount_rate/100)) AS price_sum 
					FROM a_invoice AS orders, a_in_items AS items
					WHERE orders.r_id=items.r_id 
					AND orders.date>='".$timefrom." 00:00:00' AND orders.date<='".$timeto." 23:59:59' GROUP BY items.a_id";
	else
		$sqlQuery = "SELECT items.a_id, sum(items.count*items.unit) AS count_sum, sum(items.count*items.unit*items.price*(1-orders.discount_rate/100)) AS price_sum 
					FROM a_invoice AS orders, a_in_items AS items 
					WHERE orders.r_id=items.r_id 
					AND orders.date>='".$timefrom." 00:00:00' AND orders.date<='".$timeto." 23:59:59' AND k_id IN (SELECT k_id FROM customer WHERE country = '".$country."') GROUP BY items.a_id";*/
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();

	return $thisQuery;
}

// Table 'types'

function dbQueryTypesInvoice()
{
	$thisDb = new myDatabase($_SESSION['uDb']);
	$sqlQuery = "SELECT a_id, a_name FROM a_art ORDER BY a_name ASC";
    $thisQuery = $thisDb->dbQuery($sqlQuery);
	$thisDb->dbClose();
	
	return $thisQuery;	
}
?>
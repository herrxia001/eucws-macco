<?php
/* 	
	File:		getArts.php
	Purpose: 	Query all articles.
	Return: 	All articles.
*/
session_start();
if(!$_SESSION['uId'])
	header("Location:index.php");

include_once 'db_invoice.php';

$inv = dbQueryArticles();
// refresh current from last year nianbao //
if(is_array($inv)){
	$thisDb = new myDatabase($_SESSION['uDb']);
	for($i = 0; $i < count($inv); $i++){
		$a_id = $inv[$i]['a_id'];
		$sql = "SELECT * FROM a_art_hist WHERE a_id = '".$a_id."' AND month = 0 ORDER BY year DESC LIMIT 1";
		$result = $thisDb->dbQuery($sql);
		if($result <= 0){
			$count = 0;
			//$inv[$i]['count'] = "0";
			$year = date("Y") - 2;
		}else{
			$count = intval($result[0]['count']) - intval($result[0]['dep_count']);
			//$inv[$i]['count'] = $count;
			$year = $result[0]['year'];
		}


		$sql = "SELECT a.a_id, SUM(ai.count) AS count_sale, SUM(ai.count*ai.price) as total_sale
		FROM a_art AS a, a_in_items AS ai, a_invoice AS an 
		WHERE a.a_id = '".$a_id."' AND a.a_id=ai.a_id AND ai.r_id=an.r_id AND an.date>='".$year."-12-31 23:59:59'".
			"GROUP BY a.a_id ORDER By a.a_id ASC";
		$result_2 = $thisDb->dbQuery($sql);
		if($result_2 <= 0){
			//$inv[$i]['count'] = $count;
		}else{
			$count -= $result_2[0]['count_sale'];
		}

		



		$sql = "SELECT a.a_id, SUM(pi.count) AS count_pur, SUM(pi.count*pi.cost) as total_pur
		FROM a_art AS a, a_pur_items AS pi, a_purs AS pur 
		WHERE a.a_id = '".$a_id."' AND a.a_id=pi.a_id AND pi.f_id=pur.f_id AND pur.date>='".$year."-12-31 23:59:59'".
			"GROUP BY a.a_id ORDER By a.a_id ASC";
		$result_2 = $thisDb->dbQuery($sql);
		if($result_2 <= 0){
			//$inv[$i]['count'] = $count;
		}else{
			$count += $result_2[0]['count_pur'];
		}




		$sql = "SELECT a.a_id, SUM(ri.count) AS count_rf, SUM(ri.count*ri.price) as total_rf  
		FROM a_art AS a, a_rf_items AS ri, a_refund AS rf 
		WHERE a.a_id = '".$a_id."' AND a.a_id=ri.a_id AND ri.rf_id=rf.rf_id AND rf.date>='".$year."-12-31 23:59:59'".
			"GROUP BY a.a_id ORDER By a.a_id ASC";
		$result_2 = $thisDb->dbQuery($sql);
		if($result_2 <= 0){
			//$inv[$i]['count'] = $count;
		}else{
			$count -= $result_2[0]['count_rf'];
			//$inv[$i]['count'] = $count;
		}
		$inv[$i]['count'] = $count;
	}
}



if($inv < 0)
	echo json_encode("NO");
else		
	echo json_encode($inv);

?>

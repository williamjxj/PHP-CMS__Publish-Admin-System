<?
session_start();
ob_start(); //Turn on output buffering.

//echo "<pre>";print_r($_SESSION);echo "</pre>"; exit;
if (!isset($_SESSION['userid']) ) {
	header("Location: login.php"); exit;
}
require_once('config.php');
define("DELIMITER", ",");

$db = mysql_pconnect(HOST, USER, PASS) or die(mysql_error());
mysql_select_db(DB_NAME, $db);

if (isset($_POST['xls']) && isset($_POST['from'])) {
	header("Content-Type: application/csv");
	// header("Pragma: no-cache");
	// header("Expires: 0");

	switch(strtolower($_POST['from'])) {
		case "remittance":
			header("Content-Disposition: attachment; filename=Employer_Remittance_".date("Y-m-d").".csv");
			export_remittance_xls();
			break;
		case "contact_us":
			header("Content-Disposition: attachment; filename=Contact_us_".date("Y-m-d").".csv");
			export_contact_us_xls();
			break;
		case "card_request":
			header("Content-Disposition: inline; filename=Card_Request_".date("Y-m-d").".csv");
			export_card_request_xls();
			break;
		case "users_management":
			header("Content-Disposition: inline; filename=Users_Management_".date("Y-m-d").".csv");
			export_users_management_xls($conn);
			break;
		case "users_detail":
			header("Content-Disposition: inline; filename=Users_Detail_".date("Y-m-d").".csv");
			export_users_detail_xls();
			break;
		default:
			echo "Error Here...";
	}
}
exit;
/*
Array(
    [username] => Admin
    [userid] => 10
    [remit1_sql] =>  select e.division, e.name, e.email, if(e.payment_type!=' ', e.payment_type, 'CHQ') payment_type, e.phone, e.contacter, r.reporter, r.comment, r.file, r.count, r.download_time, r.date date, date_format(r.date, '%b-%d-%y %T') date1, r.deleted, 
(select size from rfiles rf where rf.name = r.file) size
from employers e , remittance r
where   e.division = r.division  and r.deleted='N'  order by DATE desc  limit  0, 20
    [remit1_rows] => 17
)*/
function export_remittance_xls() 
{
	if(! (isset($_SESSION['remit1_sql']) && $_SESSION['remit1_sql'])) die ("Session is null on ".__LINE__);
	$sql = preg_replace("/limit.*$/i", '', $_SESSION['remit1_sql']);
	echo "Devision".DELIMITER."Name".DELIMITER."Reporter".DELIMITER."Email".DELIMITER."Report Date".DELIMITER."Payment Type".DELIMITER."File".DELIMITER."Size".DELIMITER."Download Times".DELIMITER."Latest Download Time".DELIMITER."Comment\n";
	$s = mysql_query( $sql );
	while ($res = mysql_fetch_assoc($s)) {
		$t1 = '';
		if ($res['comment'] &&  $res['comment']!=' ') {
			$t1 = preg_replace("/,/", ';', $res['comment']);
			$t1 = preg_replace("/\r?\n/", '. ', $t1);
		}
		$t2 = str_replace(",", ';', $res['name']);
		$t3 = str_replace(",", ';', $res['file']);
		echo $res['division'].DELIMITER.$t2.DELIMITER.$res['reporter'].DELIMITER.$res['email'].DELIMITER.$res['date1'].DELIMITER.$res['payment_type'].DELIMITER.$t3.DELIMITER.$res['size'].DELIMITER.$res['count'].DELIMITER.(($res['download_time'])?$res['download_time']:'n/a').DELIMITER.$t1."\n";
	}
	mysql_free_result($s);
}

//foreach($res as $key=>$v) {}; while ($row = mysql_fetch_array($select_c, MYSQL_ASSOC)) { echo implode(",", $row)."\n"; }
function export_contact_us_xls() 
{
	//[contact_sql] =>  select * from contact where deleted='N'  order by createDATE desc  limit  0, 20
	if(! (isset($_SESSION['contact_sql']) && $_SESSION['contact_sql'])) die ("Session is null on ".__LINE__);
	$sql = preg_replace("/limit.*$/i", '', $_SESSION['contact_sql']);
	echo "Name".DELIMITER."Email".DELIMITER."Created".DELIMITER."CreatedBy".DELIMITER."Message".DELIMITER."Notes\n";
	$s = mysql_query( $sql );
	while ($res = mysql_fetch_assoc($s)) {
		$t1=''; $t2='';
		if ($res['message']) {
			$t1 = preg_replace("/\r?\n/", '. ', $res['message']);
			$t1 = preg_replace("/,/", '. ', $t1);
		}
		if ($res['reply']) {
			$t2 = preg_replace("/\r?\n/", '. ', $res['reply']);
			$t2 = preg_replace("/,/", '. ', $t2);
		}
		//$t3 = str_replace(array("\r", "\r\n", "\n"), '. ', $res['name']);
		$t3 = str_replace(",", ';', $res['name']);
		//echo "[" . $res['name'] . "], " . "[" . $t3 . "]\n";
		echo $t3.DELIMITER.$res['email'].DELIMITER.$res['createdate'].DELIMITER.$res['username'].DELIMITER.$t1.DELIMITER.$t2."\n";
	}
	mysql_free_result($s);
}
// [card_sql] =>  select * from id_card where deleted='N'  order by createDATE desc  limit  0, 20
function export_card_request_xls() 
{
	//[contact_sql] =>  select * from contact where deleted='N'  order by createDATE desc  limit  0, 20
	if(! (isset($_SESSION['card_sql']) && $_SESSION['card_sql'])) die ("Session is null on ".__LINE__);
	$sql = preg_replace("/limit.*$/i", '', $_SESSION['card_sql']);
	echo "GWL".DELIMITER."Username".DELIMITER."First Name".DELIMITER."Last Name".DELIMITER."Address".DELIMITER."City".DELIMITER."Province".DELIMITER."Postal".DELIMITER."Email".DELIMITER."Created\n";
	$s = mysql_query( $sql );
	while ($res = mysql_fetch_assoc($s)) {
		$t1 = $res['address1'].' '.$res['address2'];
		if ($t1 != ' ') {
			$t1 = preg_replace("/\r?\n/", '. ', $t1);
			$t1 = str_replace(",", '. ', $t1);
		}
		$t2=''; $t3=''; $t4='';
		if ($res['surname'])  $t2 = str_replace(",", '. ', $res['surname']);
		if ($res['given'])    $t3 = str_replace(",", '. ', $res['given']);
		if ($res['username']) $t4 = str_replace(",", '. ', $res['username']);
		echo $res['gwl'].DELIMITER.$t4.DELIMITER.$t3.DELIMITER.$t2.DELIMITER.$t1.DELIMITER.$res['city'].DELIMITER.$res['prov'].DELIMITER.$res['postal'].DELIMITER.$res['email'].DELIMITER.$res['createdate']."\n";
	}
	mysql_free_result($s);
}
/* users_sql
  SELECT u.uid, u.gwl, u.birthdate, username, passwd, a.qid1, a.qid2, answer1, answer2,
	(select question1 from questions where qid1 = a.qid1) question1,
	(select question2 from questions where qid2 = a.qid2) question2
	FROM users u left join (answers a)
	on ( a.uid = u.uid)  order by u.uid  limit  0, 20
*/
function export_users_management_xls()
{
	if(! (isset($_SESSION['users_sql']) && $_SESSION['users_sql'])) die ("Session is null on ".__LINE__);
	$sql = preg_replace("/limit.*$/i", '', $_SESSION['users_sql']);
	echo "GWL".DELIMITER."DOB".DELIMITER."Userame".DELIMITER."Password".DELIMITER."Question 1".DELIMITER."Answer 1".DELIMITER."Question 2".DELIMITER."Answer 2\n";
	$s = mysql_query( $sql );
	while ($res = mysql_fetch_assoc($s)) {
		$t0=''; $t1=''; $t2=''; $t3=''; $t4='';
		if ($res['question1'])	$t0 = str_replace(",", '. ', $res['question1']);
		if ($res['answer1'])	$t1 = str_replace(",", '. ', $res['answer1']);
		if ($res['question2'])	$t2 = str_replace(",", '. ', $res['question2']);
		if ($res['answer2'])	$t3 = str_replace(",", '. ', $res['answer2']);
		if ($res['username']) $t4 = str_replace(",", '. ', $res['username']);
		echo $res['gwl'].DELIMITER.$res['birthdate'].DELIMITER.$t4.DELIMITER.$res['passwd'].DELIMITER.$t0.DELIMITER.$t1.DELIMITER.$t2.DELIMITER.$t3."\n";
	}
	mysql_free_result($s);
}
//[users_sql2] => SELECT * FROM users 
function export_users_detail_xls()
{
	if(! (isset($_SESSION['users_sql2']) && $_SESSION['users_sql2'])) die ("Session is null on ".__LINE__);
	$sql = preg_replace("/limit.*$/i", '', $_SESSION['users_sql2']);
	echo "GWL".DELIMITER."Username".DELIMITER.'Password'.DELIMITER.'DOB'.DELIMITER."First Name".DELIMITER."Last Name".DELIMITER.'Employer'.DELIMITER."Address".DELIMITER."City".DELIMITER."Email".DELIMITER."Phone".DELIMITER."Beneficiary".DELIMITER."Relationship\n";
	$s = mysql_query( $sql );
	while ($res = mysql_fetch_assoc($s)) {
		$t1 = (($res['address1'])?$res['address1'].', ':'').(($res['address2'])?$res['address2'].', ':'').(($res['prov'])?$res['prov'].', ':'').$res['postalcode'];
		if ($t1 != ' ') {
			$t1 = preg_replace("/\r?\n/", '. ', $t1);
			$t1 = str_replace(",", '. ', $t1);
		}
		$t2=''; $t3=''; $t4='';
		if ($res['surname'])  $t2 = str_replace(",", '. ', $res['surname']);
		if ($res['given'])    $t3 = str_replace(",", '. ', $res['given']);
		if ($res['username']) $t4 = str_replace(",", '. ', $res['username']);
		echo $res['gwl'].DELIMITER.$t4.DELIMITER.$res['passwd'].DELIMITER.$res['birthdate'].DELIMITER.$t3.DELIMITER.$t2.DELIMITER.$res['employer'].DELIMITER.$t1.DELIMITER.$res['city'].DELIMITER.$res['email'].DELIMITER.$res['phone'].DELIMITER.$res['beneficiary'].DELIMITER.$res['relationship']."\n";
	}
	mysql_free_result($s);
}

?>

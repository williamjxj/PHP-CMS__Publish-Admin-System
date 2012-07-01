<?php
session_start();
/** 
 * Add TinyMCE application. - William Feb 14, 2011.
 */
if(! ((isset($_SESSION['userid']) && $_SESSION['userid'])) ) {
	echo "<script>if(self.opener){self.opener.location.href='login.php';} else{window.parent.location.href='login.php';}</script>"; exit;
}
include_once("config.php");
include_once("pams.php");
include_once("remittance.php");

@mysql_connect(HOST, USER, PASS) or die(mysql_error());
mysql_select_db(DB_NAME);

$remit = new Remittance() or die;

if(isset($_POST['download'])) {
	$_SESSION['download_files'] = $_POST['files'];
	$files = explode(';', $_SESSION['download_files']);
	foreach($files as $file) {
		if($file) {
		// You should use mysql_real_escape_string only before inserting the data into your table,
		// if( preg_match("/'/", $file)) $file = addslashes($file); no addslashes, no urldecode().
		// $remit->get_count_time($file);
			$file = mysql_real_escape_string($file);
			$remit->update_download_time($file);
		}
	}
	$query = preg_replace("/^.*from /is",  " from ", $_SESSION['remit1_sql']);
	$query = "select r.count, r.download_time ". $query;
	if ($query) {
		$res = mysql_query($query);
		while ($row = mysql_fetch_array($res)) {
			echo $row['count']. ','. $row['download_time'].';'; 
		}
	} 

}
else if(isset($_GET['search1'])) {
	$remit->search1();
}
else if(isset($_POST['search_form1'])) {
	$remit->parse1();
	$remit->list1();
}
elseif(isset($_POST['delete'])) {
	if($remit->delete_files($_POST['files'])) {
		echo 'Y';
	}
}
else if (isset($_GET['page1']) && isset($_GET['sort'])) {
	$remit->list1();
}
else if (isset($_GET['page1'])) {
	$remit->list1();
}
else if (isset($_GET['page2'])) {
	// print_r($_GET);echo "<br/>";
	$remit->list2();
}
elseif(isset($_POST['dfile'])) {
	$file = urldecode($_POST['dfile']);
	if( preg_match("/'/", $file)) $file = addslashes($file);
	$remit->update_download_time($file);
	$remit->get_count_time($file);
}
elseif(isset($_POST['comment']) && isset($_POST['rid'])) {
	if ($remit->update_comment($_POST['rid'], mysql_real_escape_string(trim($_POST['comment'])))) {
		echo $_SESSION['pams_user'];
	}
}
////////////////////////////////
else {
	//$remit->zip_files();
	$remit->reset_session();
	$remit->initial_page();
	echo "\n</body>\n</html>";
}

exit;
?>

<?php
session_start();
if(! ((isset($_SESSION['userid']) && $_SESSION['userid'])) ) {
	echo "<script>if(self.opener){self.opener.location.href='login.php';} else{window.location.href='login.php';}</script>"; exit;
}

require_once('config.php');
//echo "<pre>";print_r($_POST);print_r($_FILES);echo "</pre>";
// HTTP headers for no cache etc
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$targetDir = RESOURCES_DIR;

@set_time_limit(5 * 60);

$chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
$chunks = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 0;
$fileName = isset($_FILES['file']['name']) ? $_FILES['file']['name'] : (isset($_REQUEST["name"]) ? $_REQUEST["name"] : '');

// $fileName = preg_replace('/[^\w\._]+/', '', $fileName); // bug here.

if ($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
	$ext = strrpos($fileName, '.');
	$fileName_a = substr($fileName, 0, $ext);
	$fileName_b = substr($fileName, $ext);

	$count = 1;
	while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
		$count++;

	$fileName = $fileName_a . '_' . $count . $fileName_b;
}

if (!file_exists($targetDir))
	@mkdir($targetDir);

if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
	$contentType = $_SERVER["HTTP_CONTENT_TYPE"];

if (isset($_SERVER["CONTENT_TYPE"]))
	$contentType = $_SERVER["CONTENT_TYPE"];

if (strpos($contentType, "multipart") !== false) {
	if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
		// Open temp file
		$out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
		if ($out) {
			// Read binary input stream and append it to temp file
			$in = fopen($_FILES['file']['tmp_name'], "rb");

			if ($in) {
				while ($buff = fread($in, 4096))
					fwrite($out, $buff);
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
			fclose($in);
			fclose($out);
			@unlink($_FILES['file']['tmp_name']);
		} else
			die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
	} else
		die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
} else {
	// Open temp file
	$out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
	if ($out) {
		// Read binary input stream and append it to temp file
		$in = fopen("php://input", "rb");

		if ($in) {
			while ($buff = fread($in, 4096))
				fwrite($out, $buff);
		} else
			die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');

		fclose($in);
		fclose($out);
	} else
		die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
}

if (!file_exists(RESOURCES_DIR)) mkdir(RESOURCES_DIR) or die ("Permission not allowed");

// location maybe an string=P,M;
if( isset($_FILES) && is_array($_FILES) && count($_FILES)>0 ) {
	$db = mysql_pconnect(HOST, USER, PASS) or die(mysql_error());
	mysql_select_db(DB_NAME, $db);
	/* print_r($_POST);
	[location] => M
    [chunks] => 4
    [chunk] => 3
    [comment] => existing, ???
    [name] => p15o3k1do71m521vlornevad1kme1.pdf
    [title] => test: how about existing???
	*/
	foreach(explode(',', $_POST['location']) as $t) {
	  if (! $t) die ('{"jsonrpc" : "2.0", "result" : "Nothing uploaded."}');
	  if ($t == 'P') {
		if(! get_file($_FILES['file']['name'], $t)) 
		  	process_form_location_p();
		/* everytime, the chunks=4, chunk=0,1,2,3, so post 4 times totally.
		the first time is ok, the 1-3 will throw out the following message.*/
		else {
			if(isset($_POST['chunk']) && $_POST['chunk']==0) {
				$m = "file [" . $_FILES['file']['name'] . "]  already existed.";
				echo '{"jsonrpc" : "2.0", "result" : "' . $m . '"}';
			}
		}
	  }
	  else {
		  foreach(explode(',', $_POST['division']) as $division) {
			if(! get_file($_FILES['file']['name'], $t, $division)) 
				process_form_location_m($division);
			/*else {
				$m = "file [" . $_FILES['file']['name'] . "] in [" . $t . "] already existed in line [" . __LINE__ . ']';
				echo ('{"jsonrpc" : "2.0", "result" : "'.$m.'"}');
			}*/
		 }
	  }
	}
}

// Return JSON-RPC response
// die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
////////////////////////////////////////
function process_form_location_p()
{
	$h = array();
	$h['title'] = mysql_real_escape_string(trim($_POST['title']));
	$h['comment'] = $_POST['comment']?mysql_real_escape_string(trim($_POST['comment'])):'';
	$h['uploader'] = isset($_SESSION['pams_user']) ? $_SESSION['pams_user'] : 'admin';
	
	$h['file'] = mysql_real_escape_string($_FILES['file']['name']);
	$h['type'] = $_FILES['file']['type'];
	$h['size'] = (int)$_FILES['file']['size'];
	
	$query = "insert into myresources_new(file,type,size,path,title,comment,createdby, created, location, division) values(
	  '".$h['file']."', '".$h['type']."', ".$h['size'].", '".RESOURCES_DIR."', '".$h['title']."', '".$h['comment']."', '".$_SESSION['pams_user']."', now(), 'P', '')";
	if (! mysql_query($query)) {
		$m = "file [" . $h['file'] . "] insert failure: already existed in line [" . __LINE__ . ']';
		echo ('{"jsonrpc" : "2.0", "result" : "'.$m.'"}');		
	}
	return true;
}

function process_form_location_m($division)
{
	$h = array();
	$h['title'] = mysql_real_escape_string(trim($_POST['title']));
	$h['comment'] = $_POST['comment']?mysql_real_escape_string(trim($_POST['comment'])):'';
	$h['uploader'] = isset($_SESSION['pams_user']) ? $_SESSION['pams_user'] : 'admin';
	
	$h['file'] = mysql_real_escape_string($_FILES['file']['name']);
	$h['type'] = $_FILES['file']['type'];
	$h['size'] = (int)$_FILES['file']['size'];
	
	$query = "insert into myresources_new(file,type,size,path,title,comment,createdby, created, location, division) values(
	  '".$h['file']."', '".$h['type']."', ".$h['size'].", '".RESOURCES_DIR."', '".$h['title']."', '".$h['comment']."', '".$_SESSION['pams_user']."', now(), 'M', '".$division."')";
/*
echo $query."<br>\n"; the size is wrong.
insert into myresources_new(file,type,size,path,title,comment,createdby, created, location, division) values(
	  'pdf.pdf', 'application/pdf', 22918, './myresources_new/', 'pdf.pdf', '', 'Admin', now(), 'M', 'B')
insert into myresources_new(file,type,size,path,title,comment,createdby, created, location, division) values(
	  'pdf.pdf', 'application/pdf', 22918, './myresources/', 'pdf.pdf', '', 'Admin', now(), 'M', 'C')
*/
	if (! mysql_query($query)) {
		$m = "file [" . $_FILES['file']['name'] . "] insert failure: already existed in line [" . __LINE__ . ']';
		echo ('{"jsonrpc" : "2.0", "result" : "'.$m.'"}');
	}
	return true;
}

// division only available for location='M'.
function get_file($file, $location, $division='')
{
	$file = mysql_real_escape_string($file);
	if($location=='M')
		$sql = "select * from myresources_new where file='".$file."' and location='M' and active='Y' and division='".$division."'";
	else 
		$sql = "select * from myresources_new where file='".$file."' and location='P' and active='Y'";
	$res = mysql_query($sql);
	$total =  mysql_num_rows($res);
	mysql_free_result($res);
	if($total>0) return true;
	return false;
}
?>

<?php
session_start();
if(! ((isset($_SESSION['userid']) && $_SESSION['userid'])) ) {
	echo "<script>if(self.opener){self.opener.location.href='login.php';} else{window.location.href='login.php';}</script>"; exit;
}
require_once('config.php');
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$targetDir = COVERAGE_DIR;

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

if (!file_exists(COVERAGE_DIR)) mkdir(COVERAGE_DIR) or die ("Permission not allowed");

if( isset($_FILES) && is_array($_FILES) && count($_FILES)>0 ) {
	$db = mysql_pconnect(HOST, USER, PASS) or die(mysql_error());
	mysql_select_db(DB_NAME, $db);

	foreach(explode(',', $_POST['division']) as $division) {
		if (! $division) die ('{"jsonrpc" : "2.0", "result" : "Nothing uploaded."}');
		if(! get_file($_FILES['file']['name'], $division)) 
			process_form($division);
		else {
			if(isset($_POST['chunk']) && $_POST['chunk']==0) {
				$m = "file [" . $_FILES['file']['name'] . "]  already existed.";
				die ('{"jsonrpc" : "2.0", "result" : "' . $m . '"}');
			}
		}
	}
}

////////////////////////////////////////
function process_form($division)
{
	$h = array();
	$h['title'] = mysql_real_escape_string(trim($_POST['title']));
	$h['comment'] = $_POST['comment']?mysql_real_escape_string(trim($_POST['comment'])):'';
	$h['uploader'] = isset($_SESSION['pams_user']) ? $_SESSION['pams_user'] : 'admin';
	
	$h['file'] = mysql_real_escape_string($_FILES['file']['name']);
	$h['type'] = $_FILES['file']['type'];
	$h['size'] = (int)$_FILES['file']['size'];
	
	$query = "insert into mycoverage_new(file,type,size,path,title,comment,createdby, created, division) values(
	  '".$h['file']."', '".$h['type']."', ".$h['size'].", '".COVERAGE_DIR."', '".$h['title']."', '".$h['comment']."', '".$_SESSION['pams_user']."', now(), '".$division."')";
	// chunks=4,chunk=0,1,2,3. upload routines 4 times every time. and always give this warnings. remove.
	if (! mysql_query($query)) {
		$m = "file [" . $h['file'] . "] insert failure: already existed in line [" . __LINE__ . ']';
		echo ('{"jsonrpc" : "2.0", "result" : "'.$m.'"}');		
	}
	return true;
}

function get_file($file, $division)
{
	$file = mysql_real_escape_string($file);
	$sql = "select * from mycoverage_new where file='".$file."' and active='Y' and division='".$division."'";
	$res = mysql_query($sql);
	$total =  mysql_num_rows($res);
	mysql_free_result($res);
	if($total>0) return true;
	return false;
}
?>

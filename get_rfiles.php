<?php
session_start();
include_once("config.php");
@mysql_connect(HOST, USER, PASS) or die(mysql_error());
mysql_select_db(DB_NAME);

if(isset($_GET['file']) && isset($_GET['division'])) {
	get_file();
}
else if(isset($_GET['file']) && isset($_GET['routine'])) {
	get_monthly_update_csv_file();
}
else if(isset($_GET['payment'])) {
	get_file_by_payment();
}
else {
	if(isset($_SESSION['download_files']) && $_SESSION['download_files']) {
		create_zip_db_content();
	}
	else {
		// echo "<pre>"; print_r($_SESSION); echo "</pre>";
		echo 'No files to download';
	}
}
exit;

/////////////////////////////////

function get_file()
{
	$file = mysql_escape_string($_GET['file']);
	$division = mysql_escape_string($_GET['division']);
	$query = "select name, type, size, content from rfiles where division='" . $division . "' and name='" . $file . "'";		
	$res = mysql_query($query);
	$row = mysql_fetch_assoc($res);
	$name = preg_replace("/\s/", '_', $row['name']);
	
	header("Content-type:".$row['type']);
	header('Content-Disposition: attachment; filename='.$name.';');
	header("Content-length:".$row['size']);
	echo $row['content'];
	mysql_free_result($res);
	mysql_close();
}
// Not used.
function get_file_by_payment() 
{
	$payment = mysql_escape_string($_GET['payment']);
	$query = "select r.name, r.type, r.content from employers e, rfiles r where e.division=r.division and payment_type='" . $payment . "'";		
	$res = mysql_query($query);
	$row = mysql_fetch_assoc($res);
	$name = preg_replace("/\s/", '_', $row['name']);
	
	header("Content-type:". $row['type'],";");
	header('Content-Disposition: inline; filename='.$name.';');
	echo $row['content'];
	mysql_free_result($res);
	mysql_close();
}

/**
 * http://stackoverflow.com/questions/3024765/zip-multiple-database-pdf-blob-files
 * 
 * google by 'php zip database content'.
 Array (
    [gwl] => 800000008
    [username] => Jackley
    [uid] => 9
    [passwd] => 123123
    [beneficiary] => Heather Ackley
    [files] => Array (
            [0] => arrow.jpg
            [1] => payment.csv
            [2] => payment.csv
            [3] => arrow2.jpg
        )
    [remit1_sql] => select e.division, e.name, e.email, e.payment_type, e.phone, e.contacter, r.reporter, r.comment, r.file, r.date date, date_format(r.date, '%b-%d-%y %T') date1, rf.size
		from employers e , remittance r, rfiles rf
		where rf.division = r.division
		and e.division = r.division
		and (r.deleted is null or r.deleted='N')
    [remit1_rows] => 81
)
select distinct name, type, size, content from rfiles where name in ('arrow.jpg','payment.csv','payment.csv','arrow2.jpg')
$query = "select distinct name, type, size, content from rfiles where name in ('" . implode("','", $_SESSION['download_files']) . "');";
select distinct name, type, size, content from rfiles where name in ('payment_bc200_20110201044421.csv','payment_bc300_20110128055927.csv')
$t = substr($t, 0, strlen($t)-1);
 */
function create_zip_db_content() 
{
	// $ff = 'get_rfiles.php?file='.urlencode($row['file']).'&division='.urlencode($row['division']);
	$t = urldecode($_SESSION['download_files']);
	if( preg_match("/'/", $t)) $t = addslashes($t);
	$t = preg_replace("/;/", "','", $t);
	$t = "('".$t."')";

	$query = "select distinct name, type, size, content from rfiles where name in " . $t;
	$files = array();
	
	$result = mysql_query($query) or die('Error, query failed: ' . mysql_error().':<br>'.$query);
	/**
	 * Reading your data from the database
	 * Creating an array of filenames
	 * Write your BLOB data into that files
	 * Creating a ZIP of files that exist
	 */
	while(list($name, $type, $size, $content) = mysql_fetch_array($result)) {
		$name = DOWNLOAD_DIR.$name;
		$files[] = $name;
	
		// write the BLOB Content to the file
		if ( file_put_contents("$name", $content) === FALSE ) {
			die("Could not write File: " . $name . ": ".mysql_error());
		}
	}
    // a temp filename containing the current unix timestamp
    $a = isset($_SESSION['pams_user'])? $_SESSION['pams_user'] : 'temp';
    $zipfilename = DOWNLOAD_DIR1.$a.'_' . date('Ymdhis') . '.zip';

    $zip = new ZipArchive; 
    $zip->open($zipfilename, ZipArchive::CREATE) or die("Can't create zipfile at line " . __LINE__ ); 

    foreach ($files as $file) { 
      $zip->addFile($file, basename($file));
    } 
    $zip->close(); 

    header("Content-type: application/zip");
    header('Content-Disposition: attachment; filename='.basename($zipfilename));
    header('Content-Length: ' . filesize($zipfilename));
	/**
	 * php.net/readfile: int readfile ( string $filename [, bool $use_include_path = false [, resource $context ]] )
	 *
	 * Reads a file and writes it to the output buffer. 
	 * Returns the number of bytes read from the file. If an error occurs, FALSE is returned and unless the function was called as @readfile(), an error message is printed. 
	 */
    readfile($zipfilename);
    exit;
}


// Just for reference, not used.
/* http://davidwalsh.name/create-zip-php */
/* creates a compressed zip file */
function create_zip($files = array(),$destination = '',$overwrite = false) 
{
	//if the zip file already exists and overwrite is false, return false
	if(file_exists($destination) && !$overwrite) { return false; }
	//vars
	$valid_files = array();
	//if files were passed in...
	if(is_array($files)) {
		//cycle through each file
		foreach($files as $file) {
			//make sure the file exists
			if(file_exists($file)) {
				$valid_files[] = $file;
			}
		}
	}
	//if we have good files...
	if(count($valid_files)) {
		//create the archive
		$zip = new ZipArchive();
		if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
			return false;
		}
		//add the files
		foreach($valid_files as $file) {
			$zip->addFile($file,$file);
		}
		//echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
		//close the zip -- done!
		$zip->close();		
		//check to make sure the file exists
		return file_exists($destination);
	}
	else return false;
}
// soft link: ln -s /home/backup/DBs/cibp/pipe /home/pams/data
// so pams:./data/ reflects the .../pipe/files directly from get_csv.sh.
function get_monthly_update_csv_file()
{
	if(get_env()=='Windows')
		$path = './data/june_2011/';
	else
		$path = './data/pipe/';
	
	$file = $path . mysql_escape_string($_GET['file']);
    header("Content-Type: application/csv");

 if(strstr($_SERVER["HTTP_USER_AGENT"],"MSIE")==false) {
  header("Cache-Control: no-cache");
  header("Pragma: no-cache");
  header("Expires: 0");
 }
	header('Content-Disposition: attachment; filename='.$_GET['file'].';');
	echo file_get_contents($file);
}
function get_env() {
  if(isset($_SERVER['SERVER_SOFTWARE'])) {
	if(preg_match('/Win32/i', $_SERVER['SERVER_SOFTWARE'])) return 'Windows';
	return 'Unix';
  }
}
?>

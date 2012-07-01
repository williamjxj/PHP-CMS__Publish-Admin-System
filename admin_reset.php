<?php
session_start();
if(! ((isset($_SESSION['userid']) && $_SESSION['userid'])) ) {
	echo "<script>if(self.opener){self.opener.location.href='login.php';} else{window.parent.location.href='login.php';}</script>"; exit;
}
include_once("config.php");

mysql_pconnect(HOST, USER1, PASS1) or die(mysql_error());
mysql_select_db(DB_PAMS);

if(isset($_GET['js_enable'])) {
	enable_notice();
}
elseif(isset($_GET['js_disable'])) {
	disable_notice();
}
else {
	initial();
}
exit;

////////////////////////////////
function check_notice()
{
	$sql = "SELECT enable_upload FROM monthly_notice";
	$res = mysql_query($sql) or die (mysql_error());
	$row = mysql_fetch_row($res);
	return $row[0];
}
function enable_notice() 
{
	$sql = "UPDATE monthly_notice SET enable_upload='Y' WHERE enable_upload='N'";
	$res = mysql_query($sql) or die (mysql_error());
	echo $res;
}
function disable_notice() 
{
	$sql = "UPDATE monthly_notice SET enable_upload='N' WHERE enable_upload='Y'";
	$res = mysql_query($sql) or die (mysql_error());
	echo $res;
}
function initial()
{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Enable / Disable Data Processing</title>
<link href="css/style.css" rel="stylesheet" type="text/css" />
<link href="css/programs.css" rel="stylesheet" type="text/css" />
<style type="text/css">
#info {
	font-family: "Courier New", Courier, monospace;
	font-size: 14px;
	font-weight: bold;
	color: #FF0000;
	margin: 20px auto;
	padding: 40px;
	border: thin dotted #0000FF;
	width: 40%;
	display: none;
}
.mbutton {
	background:#fff url(images/new_fileUpload.png) no-repeat right center
}
</style>
<script language="javascript" type="text/javascript" src="js/jquery-1.5.1.min.js"></script>
<script language="javascript" type="text/javascript">
$(document).ready(function() {
  var url = '<?=$_SERVER['PHP_SELF'];?>';
  var s1 = '#enable_button'; 
  var s2 = '#disable_button';
  $(s1).click(function(e){
  	e.preventDefault();
	$.get(url, {'js_enable':1}, function(data) {
		if(data == 1) {
			$(s1).attr('disabled', true).css('cursor', 'default');
			$(s2).removeAttr('disabled').css('cursor', 'pointer');
			$('#info').html('The Month End Data Upload is ENABLED. go to "Monthly Data Upgrade" tab to continue.').show();
		}
	});
  });
  $(s2).click(function(e){
  	e.preventDefault();
	$.get(url, {'js_disable':1}, function(data) {
		if(data == 1) {
			$(s2).attr('disabled', true).css('cursor', 'default');
			$(s1).removeAttr('disabled').css('cursor', 'pointer');
			$('#info').html('The Month End Data Upload is DISABLED. To make it available, click "Enable Data Processing" button.').show();
		}
	});
  });
  $(s1).attr('title', $(s1).val());
  $(s2).attr('title', $(s2).val());
});
</script>
</head>
<body>
<div style="margin-top:30px; margin-left:50px">
<h3>Enable Data Processing/Disable Data Processing</h3>
<?php
	$r = check_notice();
	$n1 = ($r=='Y')?' disabled="disabled"':' style="cursor:pointer" ';
	$n2 = ($r=='N')?' disabled="disabled"':' style="cursor:pointer" ';
?>
<input type="button" id="enable_button" value="Enable Data Processing&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" <?=$n1;?>  class="mbutton" /> &nbsp;&nbsp;&nbsp;&nbsp;
<input type="button" id="disable_button" value="Disable Data Processing&nbsp;&nbsp;&nbsp;&nbsp;" <?=$n2;?> class="mbutton"  />
</div>
<div id="info" style="margin-left:50px"></div>
</body>
</html>
<?php
}
?>

<?php
session_start();

if(! isset($_SESSION['pams_user']) ) {
	header("Location: login.php");
	exit;
}

if(isset($_GET['logout'])) {
	session_unset();
	session_destroy();
	if(isset($_COOKIE['pams']) && is_array($_COOKIE['pams'])) {
		$time = time();
		if(isset($_SESSION['pams_user']) && isset($_SESSION['pams_pass'])) {		
			setcookie("pams[username]", $_SESSION['pams_user'], $time - 3600);
			setcookie("pams[password]", $_SESSION['pams_pass'], $time - 3600);
		}
	}
	header('Location:login.php');
	exit;
}
else {
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312">
<title>Construction Industry Benefits Plan Management System - PAMS</title>
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);
//-->
</script>
</head>
<frameset rows="80,*" cols="*" frameborder="NO" border="0" framespacing="0">
<frame src="header.php" name="topFrame" scrolling="NO" noresize >
<frameset cols="200,*" frameborder="NO" border="0" framespacing="0">
<frame src="left.php" name="leftFrame" scrolling="NO" noresize>
<frame src="main.php" name="mainFrame">
<noframes>
<body>
</body>
</noframes>
</html>
<?
}
?>

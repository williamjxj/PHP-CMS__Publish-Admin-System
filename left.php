<?php session_start();
if(! ((isset($_SESSION['userid']) && $_SESSION['userid'])) ) {
	echo "<script>if(self.opener){self.opener.location.href='login.php';} else{window.location.href='login.php';}</script>"; exit;
}?>
<base target="mainFrame">
<!--link href="css/style.css" rel="stylesheet" type="text/css" /-->
<style type="text/css" media="all">@import "css/c-css.php";</style>
<script language="javascript" type="text/javascript" src="js/jquery-1.5.1.min.js"></script>
<script language="javascript" type="text/javascript">
$(document).ready(function() {
  jQuery.set_path = function(str) {
	var t= $('h1',window.parent.frames[0].document.body);
	var path = 'Construction Industry Benefits Plan Management System - ' + str;
	t.html(path);
	return true;
  }
  $('ul.sideLeftNav li:eq(0)').addClass('active');
  $('ul.sideLeftNav li a').click(function(){
	$('ul.sideLeftNav li').removeAttr('class');
	$(this).parent().addClass('active');
  }).each(function() {
  	$(this).attr('title', $(this).text());
  });
});
</script>
</head><body class="bodyStyle">
<div class="sidebarBox">
  <div id="welcome">
    <script language="JavaScript">var date=new Date().toLocaleString(); document.writeln(date);</script>
    <?php if (isset($_SESSION['pams_user'])) { echo "Welcome, <strong>" . $_SESSION['pams_user'] . "</strong>!<br>";} ?>
  </div>
  <div id="sideBarLeft" class="resize_width100">
    <ul class="sideLeftNav">
      <li><a href="main.php" onClick="$.set_path($(this).text());">Employer Remittance</a></li>
      <li><a href="contact_us.php" onClick="$.set_path($(this).text());">Contact Us</a></li>
      <li><a href="card_request.php" onClick="$.set_path($(this).text());">ID Card Request</a></li>
      <!--li><a href="#">Import / Export</a></li> 
    <li><a href="#">Assets</a></li-->
      <li><a href="users.php" onClick="$.set_path($(this).text());">User Accounts</a></li>
      <li><a href="employers.php" onClick="$.set_path($(this).text());">Employers Information</a></li>
	  <li><a href="du.php" onClick="$.set_path($(this).text());">Monthly Data Upgrade</a></li>
	  <li><a href="admin_reset.php" onClick="$.set_path($(this).text());">Administration</a></li>
    </ul>
    <a href="javascript:void(0);" class="needHelp">Need Help?</a> </div>
</div>
</body>

<?php session_start();
if(! ((isset($_SESSION['userid']) && $_SESSION['userid'])) ) {
	echo "<script>if(self.opener){self.opener.location.href='login.php';} else{window.location.href='login.php';}</script>"; exit;
} ?>
<base target="mainFrame">
<!--<link href="css/style.css" rel="stylesheet" type="text/css" />-->
<style type="text/css" media="all">@import "css/c-css.php";</style>
<script language="javascript" type="text/javascript" src="js/jquery-1.5.1.min.js"></script>
<script language="javascript" type="text/javascript">
$(document).ready(function() {
  jQuery.set_path = function(str) {
	var t= $('h1',window.parent.frames[0].document.body);
	var path = 'Construction Industry Benefits Plan Management System - ' + str;
	t.html(path);
  }
  $('ul.sideLeftNav li:eq(0)').addClass('active');
  $('ul.sideLeftNav li a').click(function(){
	$('ul.sideLeftNav li').removeAttr('class');
	$(this).parent().addClass('active');
  });
});
</script>
</head><body class="bodyStyle">
<div class="sidebarBox">
  <div id="welcome">
    <script language="JavaScript">var date=new Date().toLocaleString(); document.writeln(date);</script>
    <?php	if (isset($_SESSION['pams_user'])) {echo "Welcome, <strong>" . $_SESSION['pams_user'] . "</strong>!<br>";	} ?>
  </div>
  <div id="sideBarLeft" class="resize_width100">
    <ul class="sideLeftNav">
      <li><a href="mydiscounts.php" onClick="$.set_path($(this).text());">My Discounts</a></li>
      <li><a href="mycoverage_new.php" onClick="$.set_path($(this).text());">My Coverage</a></li>
      <li><a href="mycoverage.php" onClick="$.set_path($(this).text());">My Coverage Old</a></li>
      <li><a href="downloads_new.php" onClick="$.set_path($(this).text());">Downloads</a></li>
      <li><a href="downloads.php" onClick="$.set_path($(this).text());">Downloads Old</a></li>
      <li><a href="faqs.php" onClick="$.set_path($(this).text());">FAQs</a></li>
    </ul>
    <a href="javascript:void(0);" class="needHelp">Need Help?</a> </div>
</div>
</body>

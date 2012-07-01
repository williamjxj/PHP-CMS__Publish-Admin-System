<?php session_start();
if(! ((isset($_SESSION['userid']) && $_SESSION['userid'])) ) {
	echo "<script>if(self.opener){self.opener.location.href='login.php';} else{window.location.href='login.php';}</script>"; exit;
}
?>
<base target="mainFrame">
<link href="css/style.css" rel="stylesheet" type="text/css" />
<script language="javascript" type="text/javascript" src="js/jquery-1.5.1.min.js"></script>
<script type="text/javascript" language="javascript">
$(document).ready(function(){
  jQuery.set_path = function(str) {
	var t= $('h1');
	var path = 'Construction Industry Benefits Plan Management System - ' + str;
	t.html(path);
  }
$('.arrowDown').click(function(){
	if($('#menuDrop').hasClass('activeDown')){
		$('#menuDrop').removeClass('activeDown');
		$('#menuDrop').addClass('nonactiveDown');
		$('#menuDrop').hide();				
	} else if($('#menuDrop').hasClass('nonactiveDown')) {
		$(this).find('#menuDrop').removeClass('nonactiveDown');
		$(this).find('#menuDrop').addClass('activeDown');
		$(this).find('#menuDrop').show();
	};
});		
$('#menuDrop').mouseleave(function(){
	if($('#menuDrop').hasClass('activeDown')){
		$(this).hide();
		$(this).removeClass('activeDown');
		$(this).addClass('nonactiveDown');
	};
});
$('#userNav select').change(function() {
	if($(this).val()=='Logout') {
		parent.document.location.href='login.php?logout=1';
		return false;
	}
});
$('div.link2:first').find('li').click(function() {
	if(/left.php/.test(window.parent.frames[1].location.pathname)) {
		return window.parent.frames[1].location.href='left1.php';
	}
	return 'javascript:void(0);';
});
$('div.link2:last').find('li').click(function() {
	if(/left1.php/.test(window.parent.frames[1].location.pathname)) {
		return window.parent.frames[1].location.href='left.php';
	}
	return 'javascript:void(0);';
});
});
</script>
<div id="upper">
  <div id="navLinks">
    <div class="badge"></div>
    <div class="link1"><a href="main.php" class="font12px textcolorGrey marR23px" onClick="$.set_path($(this).text());">Dashboard</a></div>
    <div class="link1"><a href="mydiscounts.php" class="font12px textcolorGrey marR23px" onClick="window.parent.frames[1].location.href='left1.php';$.set_path('My Discounts');">Edit Content</a></div>
    <div class="link1"><a href="main.php" class="font12px textcolorGrey marR23px" onClick="window.parent.frames[1].location.href='left.php';$.set_path('Employer Remittance');">Manage System</a></div>
    <!--<div class="link2">
      <div class="linkWrap"><a href="mydiscounts.php" class="font12px textcolorGrey" onClick="window.parent.frames[1].location.href='left1.php';$.set_path('My Discounts');">Edit Content</a>
       <div class="arrowDown marR23px">
          <div class="menuDropWrap">
            <div id="menuDrop" class="nonactiveDown">
              <div class="mTopbg"></div>
              <div class="mCenterbg">
                <ul>
                  <li><a href="mydiscounts.php" onClick="$.set_path($(this).text());">My Discounts</a></li>
                  <li><a href="mycoverage.php" onClick="$.set_path($(this).text());">My Coverage</a></li>
                  <li><a href="downloads.php" onClick="$.set_path($(this).text());">Downloads</a></li>
                  <li><a href="faqs.php" onClick="$.set_path($(this).text());">FAQs</a></li>
                </ul>
              </div>
              <div class="mBelowbg"></div>
            </div>
          </div>
        </div>
      </div>
    </div>-->
    <!--<div class="link2">
      <div class="linkWrap"><a href="main.php" class="font12px textcolorGrey" onClick="window.parent.frames[1].location.href='left.php';$.set_path('Employer Remittance');">Manage System</a>
        <div class="arrowDown marR23px">
          <div class="menuDropWrap">
            <div id="menuDrop" class="nonactiveDown">
              <div class="mTopbg"></div>
              <div class="mCenterbg">
                <ul>
                  <li><a href="main.php" onClick="$.set_path($(this).text());">Employer Remittance</a></li>
                  <li><a href="contact_us.php" onClick="$.set_path($(this).text());">Contact Us</a></li>
                  <li><a href="card_request.php" onClick="$.set_path($(this).text());">ID Card Request</a></li>
                  <li><a href="users.php" onClick="$.set_path($(this).text());">User Accounts</a></li>
                </ul>
              </div>
              <div class="mBelowbg"></div>
            </div>
          </div>
        </div>
      </div>
    </div>-->
  </div>
  <div id="userNav"> <span>
    <label>Login User:
    <b><?=$_SESSION['pams_user'];?></b> |
    </label>
    </span>
    <span>
    	<label><a href="javascript:void(0);" onclick="parent.document.location.href='login.php?logout=1'">Logout</a></label>
    </span>
    <!--    <select class="font12px textcolorGrey">
      <option>< ?=$_SESSION['pams_user'];?></option>
      <option>Administration</option>
      <option>Profile</option>
      <option>Logout</option>
    </select>-->
  </div>
</div>
<!--<div class="topLine"></div>
-->
<h1>Construction Industry Benefits Plan Management System - Employer Remittance</h1>

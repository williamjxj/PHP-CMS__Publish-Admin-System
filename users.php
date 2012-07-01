<?php
session_start();
// header("Location: login.php"); header() will open login.php in current window - main.
if(! ((isset($_SESSION['userid']) && $_SESSION['userid'])) ) {
	echo "<script>if(window.opener){window.opener.location.href='login.php';} else{window.parent.location.href='login.php';}</script>"; exit;
}
include_once("config.php");
include_once("pams.php");

class Users extends PamsBase
{
  var $url, $div1, $div2;
  function __construct()  {
    $this->url = $_SERVER['PHP_SELF'];
    $this->div1 = 'main1';
    $this->div2 = 'main2';
//	  (select login_time from login_info where gwl = u.gwl) latest_access
 	$this->sql1 = " SELECT u.uid, u.gwl, u.birthdate, u.username, u.passwd, a.qid1, a.qid2, answer1, answer2, u.employer,u.dcode,a.adate, li.login_time latest_access, li.count as counts,
	  (select question1 from questions where qid1 = a.qid1) question1,
	  (select question2 from questions where qid2 = a.qid2) question2
	  FROM users u left join (answers a)
	  on ( a.uid = u.uid) left join (login_info li) on  (u.uid=li.uid) ";
	$this->sql2 = "SELECT * FROM users ";
	$this->sql3 = "SELECT u.uid, u.gwl, u.birthdate, u.username, u.passwd, 
		a.qid1, a.qid2, answer1, answer2, 
		u.employer, u.dcode, a.adate, 
		(select question1 from questions where qid1 = a.qid1) question1, 
		(select question2 from questions where qid2 = a.qid2) question2, 
		(select login_time from login_info li where li.uid = u.uid) latest_access,
		(select count from login_info li where li.uid = u.uid) counts
		FROM users u, answers a
		WHERE a.uid = u.uid ";
  }
  
  function initial_page() {
    $_SESSION['users_sql'] = $this->sql1;
    $total_rows = $this->get_total_rows($_SESSION['users_sql']);
    // echo "total rows is: " . $total_rows . "<br/>\n";
    $_SESSION['users_rows'] = $total_rows < 1 ? 1 : $total_rows;

    $_SESSION['users_sql2'] = $this->sql2;
    $total_rows2 = $this->get_total_rows($_SESSION['users_sql2']);
    $_SESSION['users_rows2'] = $total_rows2 < 1 ? 1 : $total_rows2;
    ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>PAMS Admin Panel</title>
<link href="css/style.css" rel="stylesheet" type="text/css" />
<link href="css/cwcalendar.css" rel="stylesheet" type="text/css" />
<link href="css/programs.css" rel="stylesheet" type="text/css" />
<link href="SpryAssets/SpryTabbedPanels.css" rel="stylesheet" type="text/css" />
<script src="SpryAssets/SpryTabbedPanels.js" type="text/javascript"></script>
<script language="JavaScript" src="js/calendar.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript" src="js/jquery-1.5.1.min.js"></script>
<script language="javascript" type="text/javascript">
$(document).ready(function() {
	var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
	$('#tab8').bind('click', function(event) {
		 event.preventDefault();
		if(/Hide/.test(this.innerHTML)){
			parent.document.getElementsByTagName('FRAMESET').item(1).cols = '1,*';
			this.innerHTML='&nbsp;&nbsp;&nbsp;Show Left Menu';
			$(this).removeClass('move2').addClass('move1');
		} else {
			parent.document.getElementsByTagName('FRAMESET').item(1).cols = '200,*';
			this.innerHTML='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Hide Left Menu';
			$(this).removeClass('move1').addClass('move2');
		}
	});
	$('#title1').bind('click', function() {
		$('#title1').fadeOut(200);
		if($('#search1').html().length>0) {
			$('#search1').fadeIn(200);
			return false;
		}
		$('#search1').load('users.php?search1=1').fadeIn(200);
	});	
	$('#title2').bind('click', function() {
		$('#title2').fadeOut(200);
		if($('#search2').html().length>0) {
			$('#search2').fadeIn(200);
			return false;
		}
		$('#search2').load('users.php?search2=1').fadeIn(200);
	});
	$(window).resize(function(){
		var browserWidth = $(window).width();
		if(browserWidth <= 740){
			$('fieldset').addClass('fsWidth_fix');
		} else {
			$('fieldset').removeAttr('class');
		};
	});
});
</script>
</head>
<body>
<div id="programs">
  <div id="TabbedPanels1" class="TabbedPanels">
    <ul class="TabbedPanelsTabGroup">
      <li class="TabbedPanelsTab navName font16px textcolorGrey fontBold" tabindex="0">User Management</li>
      <li class="TabbedPanelsTab textcolorLPurple" tabindex="1">User Detail</li>
      <li class="TabbedPanelsTab textcolorGrey move2" tabindex="2" id="tab8">&nbsp;&nbsp;&nbsp;Hide Left Menu</li>
    </ul>
    <div class="TabbedPanelsContentGroup tabPanelWidth_ua">
      <div class="TabbedPanelsContent">
        <div>
          <div id="title1" class="formLegend">Click to Search Users List</div>
          <div id="search1"></div>
        </div>
        <div id="main1">
          <?php $this->list1();?>
        </div>
      </div>
      <div class="TabbedPanelsContent">
        <div>
          <div id="title2" class="formLegend">Click to Search Users Details List</div>
          <div id="search2"></div>
        </div>
        <div id="main2">
          <?php $this->list2();?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
  }


  function list1()
  {
    $page = isset($_GET['page1']) ? $_GET['page1'] : 1;
    $row_no = ((int)$page-1)*ROWS_PER_PAGE;

    if (isset($_SESSION['users_sql']) && $_SESSION['users_sql']) $query = $_SESSION['users_sql'];
    else $query = $this->sql1;

	// usually 'limit 20,20' is after 'sort by username desc', so remove 'limit' first.
    if (preg_match("/limit /i", $query)) {
      $query = preg_replace("/limit.*$/i", '', $query);
    }
	if (! preg_match("/order by/i", $query)) {
		$query .=  " order by u.uid ";
	}

    if (isset($_GET['sort'])) {
      $new_order = $_GET['sort'];
      if (eregi("order by", $query)) {
        $query = preg_replace("/order by.*$/i", " order by " . $new_order, $query);
      }
      else {
        $query .= " order by " . $new_order;
      }
    }

	// After adding 'order by', add 'limit' afterwards.
    $query .=  " limit  $row_no, ".ROWS_PER_PAGE;
	
	$_SESSION['users_sql'] = $query;
    
	$total_pages = ceil($_SESSION['users_rows']/ROWS_PER_PAGE);
    if ( $page > $total_pages ) $page = $total_pages;

    $sp_flag = $total_pages==1 ? true : false;
    $url = $this->url.'?page1='.$page;
    $url1 = $this->url.'?page1';
    $divid = $this->div1;
    ?>
<table class="table8 userTable" align="center">
  <caption class="cp1">
  User Account Management Display &nbsp;
  <?php if (!$sp_flag) echo "[Page ".$page.", Total ".$total_pages." pages/".$_SESSION['users_rows']." records]"; ?>
  </caption>
  <thead>
    <?php if (!$sp_flag) { ?>
    <tr>
      <td colspan="14" align="center" class="page"><?=$this->draw($url1,$total_pages,$page,$divid); ?></td>
    </tr>
    <?php } ?>
    <tr class="rowTitle">
      <th class="bdRW"><label>No.</label></th>
      <th class="bdRW"><label>GWL # </label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'gwl'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'gwl desc'}, function(data) {$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>DOB</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'birthdate'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'birthdate desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>Employer</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'employer'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'employer desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>Division</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'dcode'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'dcode desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>Username</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'username'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'username desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>Passwd</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'passwd'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'passwd desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>Question1</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'question1'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'question1 desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>Answer1</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'answer1'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'answer1 desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>Question2</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'question2'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'question2 desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>Answer2</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'answer2'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'answer2 desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>Registered</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'adate'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'adate desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>Latest</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'latest_access'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'latest_access desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>Total</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'counts'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'counts desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
    </tr>
  </thead>
  <tbody>
    <?php
  $result = mysql_query($query);
  // if (mysql_num_rows($result) == 0) {  return false; }
  while ($row = mysql_fetch_array($result)) {
    $bgcolor = $row_no%2==1 ? 'odd': 'even';
    $sno = 'span_'.$row_no;
    ?>
    <tr class="<?=$bgcolor;?>" align="right">
      <td align="left" class="bdR"><label>
        <?=++ $row_no; ?>
        </label></td>
      <td class="bdR"><label>
        <?=$row['gwl'];?>
        </label></td>
      <td class="bdR"><label>
        <?=$row['birthdate'];?>
        </label></td>
      <td class="bdR"><label>
        <?=htmlspecialchars($row['employer']);?>
        </label></td>
      <td class="bdR"><label>
        <?=htmlspecialchars($row['dcode']);?>
        </label></td>
      <td class="bdR"><label>
        <?=$row['username'];?>
        </label></td>
      <td class="bdR"><label>
        <?=$row['passwd'];?>
        </label></td>
      <td class="bdR"><label>
        <?=htmlspecialchars($row['question1']);?>
        </label></td>
      <td class="bdR"><label>
        <?=htmlspecialchars($row['answer1']);?>
        </label></td>
      <td class="bdR"><label>
        <?=htmlspecialchars($row['question2']);?>
        </label></td>
      <td class="bdR"><label>
        <?=htmlspecialchars($row['answer2']);?>
        </label></td>
      <td class="bdR"><label>
        <?=$row['adate'];?>
        </label></td>
      <td class="bdR"><label>
        <?=$row['latest_access'];?>
        </label></td>
      <td class="bdR"><label>
        <?=$row['counts'];?>
        </label></td>
    </tr>
    <?php
  }
  mysql_free_result($result);
  ?>
  </tbody>
  <?php if (!$sp_flag) { ?>
  <tfoot>
    <tr>
      <td colspan="14" align="center" class="page"><?=$this->draw($url1,$total_pages,$page,$divid); ?></td>
    </tr>
  </tfoot>
  <?php } ?>
</table>
<div align="center" style="margin-top:10px;">
  <form action="get_csv.php" method="post">
    <input type="hidden" name="from" value="users_management" />
    <input name="xls" type="submit" class="export" value="Export to CSV File" />
  </form>
</div>
<?php
  }
  
  function search1()
  {
    ?>
<form action="javascript:void(0);" id="form1" name="form1" method="post">
  <fieldset>
  <legend id="legend1"><span>User Account Management Form Search:</span></legend>
  <table border="0" cellspacing="0" cellpadding="4" class="tab_users">
    <tr>
      <td align="right" nowrap><label for="gwl">Gwl #:</label></td>
      <td><input class="formField" name="gwl" type="text" id="gwl"></td>
      <td align="right" nowrap><label for="dob1">DOB:</label></td>
      <td><a href="javascript: fPopCalendar('dob1')">
        <input class="formField" name="dob" id="dob1" type="text" value="YYYY-MM-DD" onFocus="this.select();" size="28" />
        <img src="images/cal2.jpg" width="14" height="14" alt="to date" border="0" /></a></td>
      <td rowspan="5"><div class="inputBtn">
          <div>
            <input type="submit" name="search_form1" value="" class="tabSearch">
            <span id="msg1" style="display: none;"><img name="search_users" src="images/spinner.gif" width="16" height="16" alt="search Users..." border="0" style="margin-top: 26px; margin-left: 26px;"></span></div>
          <input type="reset" name="reset" value="" class="tabReset" title="Reset">
        </div></td>
    </tr>
    <tr>
      <td align="right" nowrap><label for="username">Username:</label></td>
      <td><input class="formField" name="username" type="text" id="username"></td>
      <td align="right" nowrap><label for="passwd">Password:</label></td>
      <td><input class="formField" name="passwd" type="text" id="passwd" /></td>
    </tr>
<!--    <tr>
      <td align="right"><label for="question1">Question 1:</label></td>
      <td><select class="formPopup" id="question1" name="question1">
          < ?php $this->get_question(1); ?>
        </select></td>
      <td align="right"><label for="question2">Question 2:</label></td>
      <td><select class="formPopup" id="question2" name="question2">
          < ?php $this->get_question(2); ?>
        </select></td>
    </tr>
-->    <tr>
      <td align="right" nowrap="nowrap"><label>Answer 1:</label></td>
      <td><input name="q1" type="radio" value="N">
        <label>No Null</label>
        &nbsp;
        <input name="q1" type="radio" value="Y">
        <label>Null</label>
        &nbsp;
        <input name="q1" type="radio" value="A" checked="checked">
        <label>All</label></td>
      <td align="right" nowrap="nowrap"><label>Answer 2:</label></td>
      <td><input name="q2" type="radio" value="N">
        <label>Not Null</label>
        &nbsp;
        <input name="q2" type="radio" value="Y">
        <label>Null</label>
        &nbsp;
        <input name="q2" type="radio" value="A" checked="checked">
        <label>All</label></td>
    </tr>
    <!--<tr>
      <td align="right" nowrap><label for="answer1">Answer 1:</label></td>
      <td><input class="formField" name="answer1" type="text" id="answer1"></td>
      <td align="right" nowrap><label for="answer2">Answer 2:</label></td>
      <td><input class="formField" name="answer2" type="text" id="answer2" /></td>
    </tr>-->
    <tr>
      <td align="right" nowrap><label for="division">Employer:</label></td>
      <td><input class="formField" name="employer" type="text" id="employer"></td>
      <td align="right"><label>Division:</label></td>
      <td><input type="checkbox" name="division" value="A" />
        <label>A</label>
        &nbsp;
        <input type="checkbox" name="division" value="B" />
        <label>B</label>
        &nbsp;
        <input type="checkbox" name="division" value="C" />
        <label>C</label>
        &nbsp;
        <input type="checkbox" name="division" value="" checked="checked" />
        <label>All</label>
      </td>
    </tr>
  </table>
  </fieldset>
</form>
<script language="javascript" type="text/javascript">
$(document).ready(function() {
    $('#form1').submit(function() {
		var dn =  $('#form1 input[name=division][type=checkbox]:checked').map(function(){ return $(this).val(); }).get().join(","); 
		var data = $('#form1').serialize() + '&dn='+dn+'&search_form1=1';
        $.ajax({
            type: "POST",
            url: 'users.php',
            data: data,
			beforeSend: function() {
				$('#form1 input:submit').hide();
				$('#msg1').show();
			},
            success: function(data) {
                if(data) {
                    $('#main1').html(data).show(200);
                } else {
                    alert('Error Here.');
                }
				$('#form1 input:submit').show();
				$('#msg1').hide();
                return false;
            }
        });
	});
	$('#legend1').bind('click', function() {
		$('#title1').fadeIn(200);
		$('#search1').fadeOut(200);
	});
	$('input.btn-remit').hover(function(){
		$(this).removeClass('btn-norm');
		$(this).addClass('btn-hover');
	}, function() {
		$(this).removeClass('btn-hover');
		$(this).addClass('btn-norm');
	});
	$('input.btn-reset').hover(function(){
		$(this).removeClass('btn-norm');
		$(this).addClass('btn-hover');
	}, function() {
		$(this).removeClass('btn-hover');
		$(this).addClass('btn-norm');
	});
});
</script>
<?php
  }
  function parse1()
  {
    $h['gwl'] = trim($_POST['gwl']);
    $h['dob'] = preg_match("/YYYY-MM-DD/i",$_POST['dob']) ? '' : trim($_POST['dob']);
    $h['user'] = trim($_POST['username']);
    $h['passwd'] = $_POST['passwd'];
    $h['q1'] =  $_POST['q1'];
    $h['q2'] =  $_POST['q2'];

    $sql = $this->sql1;
	if ($h['q1']=='N' || $h['q2']=='N') $sql = $this->sql3;
	
    if ($h['q1']=='N') {
      $sql .= " and answer1 is not null ";
    }
    elseif($h['q1']=='Y') {
      $sql .= " and answer1 is null ";
    }
    if ($h['q2']=='N') {
      $sql .= " and answer2 is not null ";
    }
    elseif($h['q2']=='Y') {
      $sql .= " and answer2 is null ";
    }
    if ($h['gwl']) {
      $sql .= " and u.gwl like '%" . $h['gwl'] . "%' ";
    }
    if($h['dob']) {
      $sql .= " and date(u.birthdate) = '" . $h['dob']. "' ";
    }
    if($h['user']) {
      $sql .= " and u.username like '%" . $h['user'] . "%' ";
    }
    if($h['passwd']) {
      $sql .= " and u.passwd ='" . $h['passwd'] . "' ";
    }/*
    if($h['question1']) $sql .= " and qid1 = '" . $h['q1'] . "' ";
    if($h['question2']) $sql .= " and qid2 = '" . $h['q2'] . "' ";
    if($h['a1']) {
      $sql .= "  answer1 like '%" . $h['a1'] . "%' ";
    }
    if($h['a2']) {
      $sql .= "  answer2 like '%" . $h['a2'] . "%' ";
    }    */
    if(isset($_POST['dn']) && !empty($_POST['dn'])) {
		$t = preg_replace("/,,/", ",", $_POST['dn']);
		$t = preg_replace("/,$/", "", $t);
		$t = preg_replace("/,/", "','", $t);
		$sql .= " and dcode in ('" . $t . "') ";
	}
	if (! preg_match("/WHERE/", $sql))
    	$sql = $this->str_replace_once(" and ", " WHERE ", $sql);
	//echo $sql . "\n";
    $_SESSION['users_sql'] = $sql;
    $total_rows = $this->get_total_rows($_SESSION['users_sql']);
    $_SESSION['users_rows'] = $total_rows < 1 ? 1 : $total_rows;
}

  function list2()
  {
    $page = isset($_GET['page2']) ? $_GET['page2'] : 1;
    $row_no = ((int)$page-1)*ROWS_PER_PAGE;

    if (isset($_SESSION['users_sql2']) && $_SESSION['users_sql2']) {
      $query = $_SESSION['users_sql2'];
    }
    else {
      $query = $this->sql2;
      $_SESSION['users_sql2'] = $query;
    }

    if (isset($_GET['sort'])) {
      $new_order = $_GET['sort'];
      if (eregi("order by", $query)) {
        $query = preg_replace("/order by.*$/i", " order by " . $new_order, $query);
      }
      else {
        $query .= " order by " . $new_order;
      }
    }
    if (preg_match("/limit /i", $query)) {
      $query = preg_replace("/limit.*$/i", " limit $row_no, ".ROWS_PER_PAGE, $query);
    }
    else {
      $query .=  " limit  $row_no, ".ROWS_PER_PAGE;
    }
    $total_pages = ceil($_SESSION['users_rows2']/ROWS_PER_PAGE);
    if ( $page > $total_pages ) $page = $total_pages;

    $sp_flag = $total_pages==1 ? true : false;
    $url = $this->url.'?page2='.$page;
    $url1 = $this->url.'?page2';
    $divid = $this->div2;
    ?>
<table class="table8 userTable" align="center">
  <caption class="cp1">
  User Account Management Display &nbsp;
  <?php if (!$sp_flag) echo "[Page ".$page.", Total ".$total_pages." pages/".$_SESSION['users_rows2']." records]"; ?>
  </caption>
  <thead>
    <?php if (!$sp_flag) { ?>
    <tr>
      <td colspan="15" align="center" class="page"><?=$this->draw($url1,$total_pages,$page,$divid); ?></td>
    </tr>
    <?php } ?>
    <tr class="rowTitle">
      <th class="bdRW"><label>No.</label></th>
      <th class="bdRW"><label>Gwl # </label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'gwl'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'gwl desc'}, function(data) {$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>Username</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'username'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'username desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>Passwd</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'passwd'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'passwd desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>DOB</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'birthdate'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'birthdate desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>Employer</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'employer'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'employer desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>Division</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'dcode'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'dcode desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>first_name</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'given'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'given desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>Last_name</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'surname'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'surname desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>Address</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'address1'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'address1 desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>City</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'city'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'city desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>Email</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'email'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'email desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>Phone</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'phone'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'phone desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>Beneficiary</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'beneficiary'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'beneficiary desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>Relationship</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'relationship'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'relationship desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
    </tr>
  </thead>
  <tbody>
    <?php
  $result = mysql_query($query);
  // if (mysql_num_rows($result) == 0) {  return false; }
  while ($row = mysql_fetch_array($result)) {
    $bgcolor = $row_no%2==1 ? 'odd': 'even';
    $sno = 'span_'.$row_no;
    $address = (($row['address1'])?$row['address1'].', ':'').(($row['address2'])?$row['address2'].', ':'').(($row['prov'])?$row['prov'].', ':'').$row['postalcode'];
    ?>
    <tr class="<?=$bgcolor;?>" align="right">
      <td align="left" class="bdR"><label>
        <?=++ $row_no; ?>
        </label></td>
      <td class="bdR"><label>
        <?=$row['gwl'];?>
        </label></td>
      <td class="bdR"><label>
        <?=$row['username'];?>
        </label></td>
      <td class="bdR"><label>
        <?=$row['passwd'];?>
        </label></td>
      <td class="bdR"><label>
        <?=$row['birthdate'];?>
        </label></td>
      <td class="bdR"><label>
        <?=htmlspecialchars($row['employer']);?>
        </label></td>
      <td class="bdR"><label>
        <?=htmlspecialchars($row['dcode']);?>
        </label></td>
      <td class="bdR"><label>
        <?=htmlspecialchars($row['given']);?>
        </label></td>
      <td class="bdR"><label>
        <?=htmlspecialchars($row['surname']);?>
        </label></td>
      <td class="bdR"><label>
        <?=htmlspecialchars($address);?>
        </label></td>
      <td class="bdR"><label>
        <?=$row['city'];?>
        </label></td>
      <td style="text-transform: lowercase" class="bdR"><label> <a
        href="mailto:<?=strtolower($row['email']);?>" title="<?=strtolower($row['email']);?>">
        <?=$this->email_abbr($row['email']);?>
        </a></label></td>
      <td class="bdR"><label>
        <?=$row['phone'];?>
        </label></td>
      <td><label>
        <?=htmlspecialchars($row['beneficiary']);?>
        </label></td>
      <td class="bdR"><label>
        <?=$row['relationship'];?>
        </label>
      </td>
    </tr>
    <?php
  }
  mysql_free_result($result);
  ?>
  </tbody>
  <?php if (!$sp_flag) { ?>
  <tfoot>
    <tr>
      <td colspan="15" align="center" class="page"><?=$this->draw($url1,$total_pages,$page,$divid); ?></td>
    </tr>
  </tfoot>
  <?php } ?>
</table>
<div align="center" style="margin-top:10px;">
  <form action="get_csv.php" method="post">
    <input type="hidden" name="from" value="users_detail" />
    <input name="xls" type="submit" value="Export to CSV File" class="export" />
  </form>
</div>
<?php
  }

  function search2()
  {
    ?>
<div id="title2" class="formLegend" style="display: none">Click to Search User Account Management Form</div>
<form action="javascript:void(0);" id="form2" name="form2" method="post">
  <fieldset>
  <legend id="legend2"><span>User Account Management Form Search:</span></legend>
  <table border="0" cellspacing="0" cellpadding="4" class="tab_users">
    <tr>
      <td align="right" nowrap><label for="gwl2">Gwl #:</label></td>
      <td><input class="formField" name="gwl" type="text" id="gwl2"></td>
      <td align="right" nowrap><label for="dob2">DOB:</label></td>
      <td><a href="javascript: fPopCalendar('dob2')">
        <input class="formField" name="dob" id="dob2" type="text" value="YYYY-MM-DD" onFocus="this.select();" size="28" />
        <img src="images/cal2.jpg" width="14" height="14" alt="to date" border="0" /></a> </td>
      <td rowspan="5"><div class="inputBtn">
          <div>
            <input type="submit" name="search_form2" value="" class="tabSearch">
            &nbsp;<span id="msg2" style="display: none;"><img name="search_User" src="images/spinner.gif" width="16" height="16" alt="search User..." border="0" style="margin-top: 26px; margin-left: 26px;"></span></div>
          <input type="reset" name="reset" value="" class="tabReset" title="Reset">
        </div></td>
    </tr>
    <tr>
      <td align="right" nowrap><label for="username2">Username:</label></td>
      <td><input class="formField" name="username" type="text" id="username2"></td>
      <td align="right" nowrap><label for="passwd2">Password:</label></td>
      <td><input class="formField" name="passwd" type="text" id="passwd2" /></td>
    </tr>
    <tr>
      <td align="right"><label for="given">First Name:</label></td>
      <td><input class="formField" name="given" type="text" id="given"></td>
      <td align="right"><label for="surname">Last Name:</label></td>
      <td><input class="formField" name="surname" type="text" id="surname"></td>
    </tr>
    <tr>
      <td align="right" nowrap="nowrap"><label>Email:</label></td>
      <td><input name="email" type="radio" value="" checked="checked">
        <label>All</label>
        &nbsp;
        <input name="email" type="radio" value="Y">
        <label>Existing</label>
        &nbsp;
        <input name="email" type="radio" value="N">
        <label>No Email</label></td>
      <td align="right" nowrap="nowrap"><label>Phone:</label></td>
      <td><input name="phone" type="radio" value="" checked="checked">
        <label>All</label>
        &nbsp;
        <input name="phone" type="radio" value="Y">
        <label>Existing</label>
        &nbsp;
        <input name="phone" type="radio" value="N">
        <label>No Phone</label></td>
    </tr>
    <tr>
      <td align="right" nowrap><label for="division">Employer:</label></td>
      <td><input class="formField" name="employer" type="text" id="employer"></td>
      <td align="right"><label>Division:</label></td>
      <td><input type="checkbox" name="division" value="A" />
        <label>A</label>
        &nbsp;
        <input type="checkbox" name="division" value="B" />
        <label>B</label>
        &nbsp;
        <input type="checkbox" name="division" value="C" />
        <label>C</label>
        &nbsp;
        <input type="checkbox" name="division" value="" checked="checked" />
        <label>All</label>
      </td>
    </tr>
  </table>
  </fieldset>
</form>
<script language="javascript" type="text/javascript">
$(document).ready(function() {
    $('#form2').submit(function() {
		var dn =  $('#form2 input[name=division][type=checkbox]:checked').map(function(){ return $(this).val(); }).get().join(","); 
		var data = $('#form2').serialize() + '&dn='+dn+'&search_form2=1';
        $.ajax({
            type: "POST",
            url: 'users.php',
            data: data,
            success: function(data) {
                if(data) {
                    $('#main2').html(data).show(200);
                } else {
                    alert('Error Here.');
                }
                return false;
            }
        });
	});
	$('#legend2').bind('click', function() {
		$('#title2').fadeIn(500);
		$('#form2').fadeOut(500);
	});

});
</script>
<?php
  }
  function parse2()
  {
    $h['gwl'] = trim($_POST['gwl']);
    $h['dob'] = preg_match("/YYYY-MM-DD/i",$_POST['dob']) ? '' : trim($_POST['dob']);
    $h['username'] = $_POST['username'];
    $h['passwd'] = $_POST['passwd'];
    $h['given'] = trim($_POST['given']);
    $h['surname'] = trim($_POST['surname']);
    $h['email'] = $_POST['email'];
    $h['phone'] = $_POST['phone'];

    $sql = $this->sql2;

    if ($h['gwl']) {
      $sql .= " and gwl like '%" . $h['gwl'] . "%' ";
    }

    if($h['dob']) {
      $sql .= " and date(birthdate) = '" . $h['dob']. "' ";
    }
    if($h['username']) {
      $sql .= " and username like '%" . $h['username'] . "%' ";
    }
    if($h['passwd']) {
      $sql .= " and passwd ='" . $h['passwd'] . "' ";
    }
    if($h['given']) {
      $sql .= " and given like '%" . $h['given'] . "%' ";
    }
    if($h['surname']) {
      $sql .= " and surname  like '%" . $h['surname'] . "%' ";
    }
    if($h['email']) {
      if ($h['email']=='Y') {
        $sql .= " and (email is not null and email!=' ' and email!='') ";
      }
      else {
        $sql .= " and (email is null or email=' ' or email='') ";
      }
    }
    if($h['phone']) {
      if ($h['phone']=='Y') {
        $sql .= " and (phone is not null and phone!=' ' and phone!='') ";
      }
      else {
        $sql .= " and (phone is null or phone=' ' or phone='') ";
      }
    }
    if(isset($_POST['dn']) && !empty($_POST['dn'])) {
		$t = preg_replace("/,,/", ",", $_POST['dn']);
		$t = preg_replace("/,$/", "", $t);
		$t = preg_replace("/,/", "','", $t);
		$sql .= " and dcode in ('" . $t . "') ";
	}

    $sql = $this->str_replace_once(" and ", " where ", $sql);
	// echo $sql . "\n";
	
    $_SESSION['users_sql2'] = $sql;
    $total_rows = $this->get_total_rows($_SESSION['users_sql2']);
    $_SESSION['users_rows2'] = $total_rows < 1 ? 1 : $total_rows;
}

	function reset_session() {
		if (isset($_SESSION['users_sql'])) unset($_SESSION['users_sql']);
		if (isset($_SESSION['users_rows'])) unset($_SESSION['users_rows']);
	}

}

/////////////////////////////////////////////

@mysql_connect(HOST, USER, PASS) or die(mysql_error());
mysql_select_db(DB_NAME);
$users = new Users() or die;

if(isset($_GET['search1'])) {
	$users->search1();
}
else if(isset($_POST['search_form1'])) {
	$users->parse1();
	$users->list1();
}
elseif(isset($_GET['search2'])) {
	$users->search2();
}
else if(isset($_POST['search_form2'])) {
	$users->parse2();
	$users->list2();
}
else if (isset($_GET['page1']) && isset($_GET['sort'])) {
	$users->list1();
}
else if (isset($_GET['page1'])) {
	$users->list1();
}
else if (isset($_GET['page2']) && isset($_GET['sort'])) {
	$users->list2();
}
else if (isset($_GET['page2'])) {
	$users->list2();
}
////////////////////////////////
else {
	//$users->zip_files();
	$users->reset_session();
	$users->initial_page();
	echo "\n</body>\n</html>";
}
exit;
?>

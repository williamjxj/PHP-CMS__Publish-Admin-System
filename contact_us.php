<?php
session_start();
if(! ((isset($_SESSION['userid']) && $_SESSION['userid'])) ) {
	echo "<script>if(self.opener){self.opener.location.href='login.php';} else{window.parent.location.href='login.php';}</script>"; exit;
}
include_once("config.php");
include_once("pams.php");

class ContactUs extends PamsBase
{
  var $url, $div1, $div2;
  function __construct()  {
    $this->url = $_SERVER['PHP_SELF'];
    $this->div1 = 'main1';
 	$this->sql1 = " select * from contact ";
	$this->sql2 = "update contact set deleted='Y' where cid in (";
  }
  
  function initial_page() {
    $_SESSION['contact_sql'] = $this->sql1 . " where deleted='N' ";
    $total_rows = $this->get_total_rows($_SESSION['contact_sql']);
    // echo "total rows is: " . $total_rows . "<br/>\n";
    $_SESSION['contact_rows'] = $total_rows < 1 ? 1 : $total_rows;
    ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>PAMS Admin Panel</title>
<link href="css/style.css" rel="stylesheet" type="text/css" />
<link href="css/cwcalendar.css" rel="stylesheet" type="text/css" />
<link href="css/my.css" rel="stylesheet" type="text/css" />
<link href="css/programs.css" rel="stylesheet" type="text/css" />
<link href="SpryAssets/SpryTabbedPanels.css" rel="stylesheet" type="text/css" />
<script src="SpryAssets/SpryTabbedPanels.js" type="text/javascript"></script>
<script language="JavaScript" src="js/calendar.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript" src="js/jquery-1.5.1.min.js"></script>
<script language="javascript" type="text/javascript" src="js/contact_us.min.js"></script>
</head>
<body>
<div id="programs">
<div id="TabbedPanels1" class="TabbedPanels">
  <ul class="TabbedPanelsTabGroup">
    <li class="TabbedPanelsTab navName font16px textcolorGrey fontBold" tabindex="0">Contact Form</li>
    <!--<li class="TabbedPanelsTab textcolorLPurple" tabindex="1">Tab 2</li>-->
    <li class="TabbedPanelsTab textcolorGrey move2" tabindex="2" id="tab8">&nbsp;&nbsp;&nbsp;Hide Left Menu</li>
  </ul>
  <div class="TabbedPanelsContentGroup tabPanelWidth_cu">
    <div class="TabbedPanelsContent">
      <div>
        <div id="title1" class="formLegend">Click to Search Contact List</div>
        <div id="search1"></div>
      </div>
      <div id="main1">
        <?php $this->list1();?>
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

    if (isset($_SESSION['contact_sql']) && $_SESSION['contact_sql']) $query = $_SESSION['contact_sql'];
    else $query = $this->sql1;

    if (preg_match("/limit /i", $query)) {
      $query = preg_replace("/limit.*$/i", '', $query);
    }
	if (! preg_match("/order by/i", $query)) {
		$query .=  " order by createDATE desc ";
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

    $query .=  " limit  $row_no, ".ROWS_PER_PAGE;
	$_SESSION['contact_sql'] = $query;
	
    $total_pages = ceil($_SESSION['contact_rows']/ROWS_PER_PAGE);
    if ( $page > $total_pages ) $page = $total_pages;

    $sp_flag = $total_pages==1 ? true : false;
    $url = $this->url.'?page1='.$page;
    $url1 = $this->url.'?page1';
    $divid = $this->div1;
    ?>
<table class="table8" align="center">
  <caption class="cp1">
  Contact Form Display &nbsp;
  <?php if (!$sp_flag) echo "[Page ".$page.", Total ".$total_pages." pages/".$_SESSION['contact_rows']." records]"; ?>
  </caption>
  <thead>
    <?php if (!$sp_flag) { ?>
    <tr>
      <td colspan="8" align="center" class="page"><?=$this->draw($url1,$total_pages,$page,$divid); ?></td>
    </tr>
    <?php } ?>
    <tr class="rowTitle">
      <th class="bdRW" ><label>No.</label></th>
      <th style="text-align:center; width:4%;max-width:4%;"><img id="img_delete" style="vertical-align:middle;" src="images/delete.png" width="16" height="16" border="0" title="remove records" />
        <div id="div1_checked"></div></th>
      <th class="bdRW" style="width:8%"><label>Name</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'name'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'name desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
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
      <th class="bdRW" style="width:25%"><label>Message</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'message'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'message desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW" style="width:25%"><label>Notes</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'reply'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'reply desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>Time</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'createdate'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'createdate desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>Login User</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'username'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'username desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
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
   ?>
    <tr class="<?=$bgcolor;?>" align="right">
      <td align="left" class="bdR"><label>
        <?=++ $row_no; ?>
        </label></td>
      <td class="bdR" style="width:4%"><?php
	  if($row['deleted']=='Y') {
      	echo '<input name="deleted" type="checkbox" value="'.$row['cid'].'" checked="checked" readonly="readonly" title="deleted already" />';
	  }
	  else {
      	echo '<input name="deleted" type="checkbox" value="'.$row['cid'].'" />';
	  }
	  ?>
      </td>
      <td class="bdR"><label>
        <?=htmlspecialchars($row['name']);?>
        </label></td>
      <td style="text-transform: lowercase" class="bdR"><label> <a
        href="mailto:<?=strtolower($row['email']);?>" title="<?=strtolower($row['email']);?>">
        <?=$this->email_abbr($row['email']);?>
        </a></label></td>
      <td class="bdR"><textarea name="message" class="contactText" readonly="readonly"><?=htmlspecialchars($row['message']);?>
</textarea></td>
      <td class="bdR"><form method="post" action="<?=$this->url;?>" id="f_<?=$row['cid'];?>"><textarea name="reply"  class="contactText" onchange="Contact.reply('f_<?=$row['cid'];?>')"><?=$row['reply'];?></textarea><input type="hidden" name="cid" value="<?=$row['cid'];?>" /></form>
<!--
	// The blur event is sent to an element when it loses focus. 
	// The change event is sent to an element when its value changes. This event is limited to <input> elements, <textarea> boxes and <select> elements. For select boxes, checkboxes, and radio buttons, 
	// the event is fired immediately when the user makes a selection with the mouse, but for the other element types the event is deferred until the element loses focus.
	// 'blur' not work well, 'change' event is better.
	/* $('#'+id+' textarea').change(function(event) {
		event.preventDefault();
		alert(typeof($('#'+id+' textarea').get(0).name)+','+event.target.nodeName); // object/string, TEXTAREA
		alert(id+','+$('#'+id).find('textarea:eq(0)').val()+','+$(event.target.nodeName).val());
		$('#'+id).post(Contact.url, $("").serialize());
	}); -->
      </td>
      <td class="bdR"><label>
        <?=$row['createdate'];?>
        </label></td>
      <td><label>
        <?=$row['username']?$row['username']:'N/A';?>
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
      <td colspan="8" align="center" class="page"><?=$this->draw($url1,$total_pages,$page,$divid); ?></td>
    </tr>
  </tfoot>
  <?php }?>
</table>
<div align="center" style="margin-top:10px;">
 <form action="get_csv.php" method="post">
  <input type="hidden" name="from" value="contact_us" />
  <input name="xls" type="submit" value="Export to CSV File" class="export" />
 </form>
</div>
<script language="javascript" type="text/javascript">
	Contact.count_deleted();
	$("input[name='deleted']:checkbox").bind('click', Contact.count_deleted);
	Contact.update_deleted();
</script>
<?php
  }

  function search1()
  {
    ?>
<form action="javascript:void(0);" id="form1" name="form1" method="post">
  <fieldset>
  <legend id="legend1"><span>Contact Form Search:</span></legend>
  <table border="0" cellspacing="0" cellpadding="4" class="tab_contactUs">
    <tr>
      <td align="right" nowrap><label for="name">Subscriber Name:</label></td>
      <td><input class="formField" name="name" type="text" id="name" /></td>
      <td align="right" nowrap="nowrap"><label for="username">Login Username:</label></td>
      <td><input class="formField" name="username" type="text" id="username" /></td>
      <td rowspan="4"><div class="inputBtn">
        <div><input type="submit" name="search_form1" value="" class="tabSearch"><span id="msg1" style="display: none;"><img name="search_download" src="images/spinner.gif" width="16" height="16" alt="search Remittance..." border="0" style="margin-top: 26px; margin-left: 26px;"></span></div><input type="reset" name="reset" value="" class="tabReset" title="Reset">
    </div></td>
    </tr>
    <tr>
      <td align="right" nowrap="nowrap"><label for="email">Subscriber Email:</label></td>
      <td><input class="formField" name="email" type="text" id="email"></td>
      <td align="right" nowrap><label for="message">Message:</label></td>
      <td><input class="formField" name="message" type="text" id="message"></td>
    </tr>
    <tr>
      <td align="right" nowrap="nowrap"><label>Reply:</label></td>
      <td><input name="reply" type="radio" value="" checked="checked">
        <label>All</label>
        &nbsp;
        <input name="reply" type="radio" value="Y">
        <label>Yes</label>
        &nbsp;
        <input name="reply" type="radio" value="N">
        <label>No</label></td>
      <td align="right" nowrap="nowrap"><label>Deleted:</label></td>
      <td><input name="deleted" type="radio" value="N" checked="checked">
        <label>No</label>
        &nbsp;
        <input name="deleted" type="radio" value="Y">
        <label>Yes</label>
        &nbsp;
        <input name="deleted" type="radio" value="">
        <label>All</label></td>
    </tr>
    <tr>
      <td align="right"><label for="date1">Date from:</label></td>
      <td><a href="javascript: fPopCalendar('date1')">
        <input class="formField" type="text" name="date1" id="date1"  value="YYYY-MM-DD" onFocus="this.select();" size="28" />
        <img src="images/cal2.jpg" width="14" height="14" alt="from date" border="0"></a></td>
      <td align="right"><label for="date2">Date to:</label></td>
      <td><a href="javascript: fPopCalendar('date2')">
        <input class="formField" name="date2" id="date2" type="text" value="YYYY-MM-DD" onFocus="this.select();" size="28" />
        <img src="images/cal2.jpg" width="14" height="14" alt="to date" border="0" /> </a></td>
    </tr>
  </table>
  </fieldset>
</form>
<script language="javascript" type="text/javascript">
$(document).ready(function() {
    $('#form1').submit(function() {
       var data = $('#form1').serialize() + '&search_form1=1';
        $.ajax({
            type: "POST",
            url: Contact.url,
            data: data,
			beforeSend: function() {
				$('input:submit').hide();
				$('#msg1').show();
			},
            success: function(data) {
                if(data) {
                    $('#main1').html(data).show(200);
                } else {
                    alert('Error Here.');
                }
 				$('input:submit').show();
				$('#msg1').hide();
               return false;
            }
        });
	});
	$('#legend1').bind('click', function() {
		$('#title1').fadeIn(100);
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
    // $h['cid'] = $_POST['cid'];
    $h['name'] = mysql_real_escape_string(trim($_POST['name']));
    $h['username'] = mysql_real_escape_string(trim($_POST['username']));
    $h['email'] = $_POST['email'];
    $h['message'] = mysql_real_escape_string($_POST['message']);
    $h['reply'] = mysql_real_escape_string($_POST['reply']);
    $h['deleted'] = $_POST['deleted'];
    $h['date1'] = $this->get_date(trim($_POST['date1']));
    $h['date2'] = $this->get_date(trim($_POST['date2']));
    $sql = $this->sql1;
    if($h['name']) {
      $sql .= " and name like '%" . $h['name'] . "%' ";
    }
    if($h['username']) {
      $sql .= " and username like '%" . $h['username'] . "%' ";
    }
    if($h['email']) {
      if ($h['email']=='Y') {
        $sql .= " and (email is not null or email!=' ') ";
      }
      else {
        $sql .= " and (email is null or email=' ') ";
      }
    }
    if ($h['deleted']) {
      if ($h['deleted']=='Y') {
	    $sql .= " and deleted = 'Y' ";
      }
      else {
	    $sql .= " and deleted = 'N' ";
      }
    }
    if($h['message']) {
      $sql .= " and message like '%" . $h['message'] . "%' ";
    }
    if($h['reply']) {
      if ($h['reply']=='Y') {
        $sql .= " and (reply is not null or reply!=' ') ";
      }
      else {
        $sql .= " and (reply is null or reply=' ') ";
      }
    }
    if($h['date1'] && $h['date2']) {
      $sql.=" and (createdate between '". $h['date1']. "' and '".$h['date2']."') ";
    }
    else if($h['date1']) {
      $sql .= " and date(createdate) = '" . $h['date1'] . "'";
    }
    else if($h['date2']) {
      $sql .= " and date(createdate) = '" .  $h['date2'] . "'";
    }
    $sql = $this->str_replace_once(" and ", " where ", $sql);
    $_SESSION['contact_sql'] = $sql;
    $total_rows = $this->get_total_rows($_SESSION['contact_sql']);
    $_SESSION['contact_rows'] = $total_rows < 1 ? 1 : $total_rows;
  }

	function reset_session() {
		if (isset($_SESSION['contact_sql'])) unset($_SESSION['contact_sql']);
		if (isset($_SESSION['contact_rows'])) unset($_SESSION['contact_rows']);
	}
	function delete_records($cids)
	{
		$query = $this->sql2.$cids.')';
		print_r($query);		
	    mysql_query($query) or die('Error, update deleted is failed'.mysql_error().":".$query);

		$dblink = mysql_connect(HOST, USER1, PASS1) or die(mysql_error());
		mysql_select_db(DB_PAMS, $dblink);
		$query = "insert into actions(uid, username, action) values(" . 
			$_SESSION['userid'] . ",'" .
			$_SESSION['pams_user'] . 
			"', 'Delete contact table: ".$cids."')";
	    mysql_query($query, $dblink) or die('Error, insert actions table failure: '.mysql_error().":".$query);
		mysql_close($dblink);
		
		return true;
	}
	function update_contact($type) {
		$query1 = "update contact set type='".$type."', download_time=current_timestamp where cid='".$cid."'";
		mysql_query($query1) or die("Can't update contactus column at line ".__LINE__.":".mysql_error());
	}
	function update_note($cid, $note) {
		$username = isset($_SESSION['pams_user']) ? mysql_real_escape_string($_SESSION['pams_user']) : '';
		$query = "update contact set reply='".$note."', username='".$username."' where cid=".$cid;
		mysql_query($query) or die("Can't update contactus column at line ".__LINE__.': '.mysql_error());
		return true;
	}

}
/////////////////////////////////////////////

@mysql_connect(HOST, USER, PASS) or die(mysql_error());
mysql_select_db(DB_NAME);

$contact = new ContactUs() or die;

if(isset($_POST['search_form1'])) {
	$contact->parse1();
	$contact->list1();
}
else if (isset($_GET['search1'])) {
	$contact->search1();
}
else if (isset($_GET['page1']) && isset($_GET['sort'])) {
	$contact->list1();
}
else if (isset($_GET['page1'])) {
	$contact->list1();
}
elseif(isset($_POST['delete'])) {
	if($contact->delete_records($_POST['cids'])) {
		echo 'Y';
	}
}
elseif(isset($_POST['reply']) && isset($_POST['cid'])) {
	if ($contact->update_note($_POST['cid'], mysql_real_escape_string(trim($_POST['reply'])))) {
		echo $_SESSION['pams_user'];
	}
}
else {
	$contact->reset_session();
	$contact->initial_page();
	echo "\n</body>\n</html>";
}
exit;
?>

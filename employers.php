<?php
session_start();
if(! ((isset($_SESSION['userid']) && $_SESSION['userid'])) ) {
	echo "<script>if(self.opener){self.opener.location.href='login.php';} else{window.parent.location.href='login.php';}</script>"; exit;
}
include_once("config.php");
include_once("pams.php");

class Employers extends PamsBase
{
  var $url, $div1, $div2;
  function __construct()  {
    $this->url = $_SERVER['PHP_SELF'];
    $this->div1 = 'main1';
    $this->div2 = 'main2';

 	$this->sql1 = " SELECT * from employers ";
	$this->sql2 = "SELECT * FROM employers ";
  }
  
  function initial_page() {
    $_SESSION['empl_sql'] = $this->sql1;
    $total_rows = $this->get_total_rows($_SESSION['empl_sql']);
    // echo "total rows is: " . $total_rows . "<br/>\n";
    $_SESSION['empl_rows'] = $total_rows < 1 ? 1 : $total_rows;

    $_SESSION['empl_sql2'] = $this->sql2;
    $total_rows2 = $this->get_total_rows($_SESSION['empl_sql2']);
    // echo "total rows is: " . $total_rows . "<br/>\n";
    $_SESSION['empl_rows2'] = $total_rows2 < 1 ? 1 : $total_rows2;
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
		$('#title1').fadeOut(100);
		if($('#search1').html().length>0) {
			$('#search1').fadeIn(100);
			return false;
		}
		$('#search1').load('employers.php?search1=1').fadeIn(200);
	});
	$('#title2').bind('click', function() {
		$('#title2').fadeOut(200);
		if($('#search2').html().length>0) {
			$('#search2').fadeIn(200);
			return false;
		}
		$('#search2').load('employers.php?search2=1').fadeIn(200);
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
      <li class="TabbedPanelsTab navName font16px textcolorGrey fontBold" tabindex="0">Employer Management</li>
      <li class="TabbedPanelsTab textcolorGrey move2" tabindex="2" id="tab8">&nbsp;&nbsp;&nbsp;Hide Left Menu</li>
    </ul>
    <div class="TabbedPanelsContentGroup tabPanelWidth_ua">
      <div class="TabbedPanelsContent">
        <div>
          <div id="title1" class="formLegend">Click to Search Employers List</div>
          <div id="search1"></div>
        </div>
        <div id="main1">
          <?php $this->list1();?>
        </div>
      </div>
      <div class="TabbedPanelsContent">
        <div>
          <div id="title2" class="formLegend">Click to Search Employers List</div>
          <div id="search2"></div>
        </div>
        <div id="main2"></div>
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

    if (isset($_SESSION['empl_sql']) && $_SESSION['empl_sql']) $query = $_SESSION['empl_sql'];
    else $query = $this->sql1;

	// usually 'limit 20,20' is after 'sort by username desc', so remove 'limit' first.
    if (preg_match("/limit /i", $query)) {
      $query = preg_replace("/limit.*$/i", '', $query);
    }
	if (! preg_match("/order by/i", $query)) {
		$query .=  " order by EDATE desc ";
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
    $_SESSION['empl_sql'] = $query;

    $total_pages = ceil($_SESSION['empl_rows']/ROWS_PER_PAGE);
    if ( $page > $total_pages ) $page = $total_pages;

    $sp_flag = $total_pages==1 ? true : false;
    $url = $this->url.'?page1='.$page;
    $url1 = $this->url.'?page1';
    $divid = $this->div1;
    ?>
<table class="table8 userTable" align="center">
  <caption class="cp1">
  Employers Submission Form Display &nbsp;
  <?php if (!$sp_flag) echo "[Page ".$page.", Total ".$total_pages." pages/".$_SESSION['empl_rows']." records]"; ?>
  </caption>
  <thead>
    <?php if (!$sp_flag) { ?>
    <tr>
      <td colspan="13" align="center" class="page"><?=$this->draw($url1,$total_pages,$page,$divid); ?></td>
    </tr>
    <?php } ?>
    <tr class="rowTitle">
      <th class="bdRW"><label>No.</label></th>
      <th class="bdRW"><label>Employer</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'division'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'division desc'}, function(data) {$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>Division</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'dcode'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'dcode desc'}, function(data) {$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>Name</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'name'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'name desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>Type</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'payment_type'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'payment_type desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>Contacter</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'contacter'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'contacter desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>Address</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'address'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'address desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
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
      <th class="bdRW"><label>Province</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'Province'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'Province desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>Postal</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'postal'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'postal desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
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
      <th class="bdRW"><label>Fax</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'fax'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$.get('<?=$url;?>', {sort:'fax desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
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
        <?=$row['division'];?>
        </label></td>
      <td class="bdR"><label>
        <?=$row['dcode'];?>
        </label></td>
      <td class="bdR"><label>
        <?=htmlspecialchars($row['name']);?>
        </label></td>
      <td class="bdR"><label>
        <?
if (preg_match("/^\s+$/",$row['payment_type']) || ($row['payment_type']=='')) {
	echo 'CHQ';
} else {
	echo $row['payment_type'];
}?>
        </label></td>
      <td class="bdR"><label>
        <?=htmlspecialchars($row['contacter']);?>
        </label></td>
      <td class="bdR"><label>
        <?=htmlspecialchars($row['address']);?>
        </label></td>
      <td class="bdR"><label>
        <?=htmlspecialchars($row['city']);?>
        </label></td>
      <td class="bdR"><label>
        <?=$row['province'];?>
        </label></td>
      <td class="bdR"><label>
        <?=$row['postal'];?>
        </label></td>
      <td style="text-transform: lowercase" class="bdR"><label> <a
        href="mailto:<?=strtolower($row['email']);?>" title="<?=strtolower($row['email']);?>">
        <?=$this->email_abbr($row['email']);?>
        </a></label></td>
      <td><label>
        <?=$row['phone'];?>
        </label></td>
      <td class="bdR"><label>
        <?=$row['fax'];?>
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
      <td colspan="13" align="center" class="page"><?=$this->draw($url1,$total_pages,$page,$divid); ?></td>
    </tr>
  </tfoot>
  <?php } ?>
</table>
<?php
  }

  function search1()
  {
    ?>
<form action="javascript:void(0);" id="form1" name="form1" method="post">
  <fieldset>
  <legend id="legend1"><span>Employers Submission Form Search:</span></legend>
  <table border="0" cellspacing="0" cellpadding="4" class="tab_users">
    <tr>
      <td align="right" nowrap="nowrap"><label for="name">Employer Name:</label></td>
      <td><input class="formField" name="name" type="text" id="name" /></td>
      <td align="right" nowrap><label for="employer">Employer Code:</label></td>
      <td><input class="formField" name="employer" type="text" id="employer"></td>
      <td rowspan="5"><div class="inputBtn">
          <div>
            <input type="submit" name="search_form1" value="" class="tabSearch">
            <span id="msg1" style="display: none;"><img name="search_employers" src="images/spinner.gif" width="16" height="16" alt="search Employers..." border="0" style="margin-top: 26px; margin-left: 26px;"></span></div>
          <input type="reset" name="reset" value="" class="tabReset" title="Reset">
        </div></td>
    </tr>
    <tr>
      <td align="right"><label for="payment_type">Payment Type:</label></td>
      <td><select class="formPopup" id="payment_type" name="payment_type">
          <?php $this->get_payment_type(); ?>
        </select></td>
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
        <label>No Existing</label></td>
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
            url: 'employers.php',
            data: data,
            success: function(data) {
                if(data) {
                    $('#main1').html(data).show(200);
                } else {
                    alert('Error Here.');
                }
                return false;
            }
        });
	});
	$('#legend1').bind('click', function() {
		$('#title1').fadeIn(500);
		$('#form1').fadeOut(500);
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
    $h['employer'] = trim($_POST['employer']);
    $h['name'] = trim($_POST['name']);
    // $h['contacter'] = trim($_POST['contacter']);
    $h['email'] = trim($_POST['email']);
    $h['phone'] = trim($_POST['phone']);
    $h['payment_type'] = trim($_POST['payment_type']);

    $sql = $this->sql1;

    if($h['email']) {
      if ($h['email']=='Y') {
        $sql .= " and (email is not null or email!=' ') ";
      }
      else {
        $sql .= " and (email is null or email=' ') ";
      }
    }
    if($h['phone']) {
      if ($h['phone']=='Y') {
        $sql .= " and (phone is not null or phone!=' ') ";
      }
      else {
        $sql .= " and (phone is null or phone=' ') ";
      }
    }
    if($h['payment_type']) {
	    if($h['payment_type']=='CHQ')
			$sql .= " and (payment_type='CHQ' or payment_type=' ' or payment_type='')";
		else 
			$sql .= " and payment_type = '" . $h['payment_type'] . "' ";
    }
    if($h['employer']) {
      $sql .= " and division like '%" . $h['employer'] . "%' ";
    }
    if($h['name']) {
      $sql .= " and name like '%" . $h['name'] . "%' ";
    }
    if(isset($_POST['dn']) && !empty($_POST['dn'])) {
		$t = preg_replace("/,,/", ",", $_POST['dn']);
		$t = preg_replace("/,$/", "", $t);
		$t = preg_replace("/,/", "','", $t);
		$sql .= " and dcode in ('" . $t . "') ";
	}

    $sql = $this->str_replace_once(" and ", " where ", $sql);
    $_SESSION['empl_sql'] = $sql;
    $total_rows = $this->get_total_rows($_SESSION['empl_sql']);
    $_SESSION['empl_rows'] = $total_rows < 1 ? 1 : $total_rows;
}

  function list2() {}
  function search2() {}
  function parse2()  {}

  function reset_session() {
	if (isset($_SESSION['empl_sql'])) unset($_SESSION['empl_sql']);
	if (isset($_SESSION['empl_rows'])) unset($_SESSION['empl_rows']);
  }
}

/////////////////////////////////////////////

@mysql_connect(HOST, USER, PASS) or die(mysql_error());
mysql_select_db(DB_NAME);
$empl = new Employers() or die;

if(isset($_GET['search1'])) {
	$empl->search1();
}
elseif(isset($_POST['search_form1'])) {
	$empl->parse1();
	$empl->list1();
}
elseif(isset($_GET['list2'])) {
	$empl->list2();
}
elseif(isset($_GET['search2'])) {
	$empl->search2();
}
elseif(isset($_POST['search_form2'])) {
	$empl->parse2();
	$empl->list2();
}
else if (isset($_GET['page1']) && isset($_GET['sort'])) {
	$empl->list1();
}
else if (isset($_GET['page1'])) {
	$empl->list1();
}
else if (isset($_GET['page2']) && isset($_GET['sort'])) {
	$empl->list2();
}
else if (isset($_GET['page2'])) {
	print_r($_GET);echo "<br/>";
	$empl->list2();
}
////////////////////////////////
else {
	$empl->reset_session();
	$empl->initial_page();
	echo "\n</body>\n</html>";
}
exit;
?>

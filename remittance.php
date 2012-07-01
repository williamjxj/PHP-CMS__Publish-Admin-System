<?php
//http://craigsworks.com/projects/qtip/docs/

class Remittance extends PamsBase
{
  var $url, $div1, $div2;
  function __construct()  {
    $this->url = $_SERVER['PHP_SELF'];
    $this->div1 = 'main1';
    $this->div2 = 'main2';

     //order by r.date desc, group by r.division
 	$this->sql1 = " select e.division, e.name, e.email, e.payment_type, e.phone, e.contacter, r.reporter, r.comment, r.file, r.count, r.download_time, r.date date, date_format(r.date, '%b-%d-%y %T') date1, r.deleted, r.rid, 
(select size from rfiles rf where rf.name = r.file) size
from employers e , remittance r
where   e.division = r.division ";
	$this->sql2 = "update remittance set deleted='Y' where deleted='N' and file in (";
  }
  
  function initial_page() {
    $_SESSION['remit1_sql'] = $this->sql1 . " and r.deleted='N' ";
    $total_rows = $this->get_total_rows($_SESSION['remit1_sql']);
    // echo "total rows is: " . $total_rows . "<br/>\n";
    $_SESSION['remit1_rows'] = $total_rows < 1 ? 1 : $total_rows;
    ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>PAMS Admin Panel</title>
<link href="css/style.css" rel="stylesheet" type="text/css" />
<link href="css/my.css" rel="stylesheet" type="text/css" />
<link href="css/programs.css" rel="stylesheet" type="text/css" />
<link href="css/cwcalendar.css" rel="stylesheet" type="text/css" />
<link href="SpryAssets/SpryTabbedPanels.css" rel="stylesheet" type="text/css" />
<script src="SpryAssets/SpryTabbedPanels.js" type="text/javascript"></script>
<script language="JavaScript" src="js/calendar.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript" src="js/jquery-1.5.1.min.js"></script>
<script language="javascript" type="text/javascript" src="js/remit.min.js"></script>
</head>
<body>
<div id="programs">
  <div id="TabbedPanels1" class="TabbedPanels">
    <ul class="TabbedPanelsTabGroup">
      <li class="TabbedPanelsTab navName font16px textcolorGrey fontBold" tabindex="0">Employer Remittance Submission Form</li>
      <!--<li class="TabbedPanelsTab textcolorLPurple" tabindex="1">Tab 2</li>-->
      <li class="TabbedPanelsTab textcolorGrey move2" tabindex="2" id="tab8">&nbsp;&nbsp;&nbsp;Hide Left Menu</li>
    </ul>
    <div class="TabbedPanelsContentGroup tabPanelWidth_rm">
      <div class="TabbedPanelsContent">
        <div>
          <div id="title1" class="formLegend" style="display:none;">Click to Search Remittance List</div>
          <div id="search1">
            <?php $this->search1();?>
          </div>
        </div>
        <div id="main1">
          <?php $this->list1();?>
        </div>
        <div id="main2"></div>
      </div>
      <!--div class="TabbedPanelsContent">
      <div id="search2">
        < ?php $this->search2(); ?>
      </div>
    </div-->
    </div>
  </div>
</div>
<?php
  }

  // for US, Canada: search and advanced search.
  function reset_session() {
    if (isset($_SESSION['remit1_sql'])) unset($_SESSION['remit1_sql']);
    if (isset($_SESSION['remit1_rows'])) unset($_SESSION['remit1_rows']);
    if (isset($_SESSION['remit1_state'])) unset($_SESSION['remit1_state']);
  }

  function list1()
  {
    $page = isset($_GET['page1']) ? $_GET['page1'] : 1;
    $row_no = ((int)$page-1)*ROWS_PER_PAGE;

    if (isset($_SESSION['remit1_sql']) && $_SESSION['remit1_sql']) $query = $_SESSION['remit1_sql'];
    else $query = $this->sql1;

    if (preg_match("/limit /i", $query)) {
      $query = preg_replace("/limit.*$/i", '', $query);
    }
	if (! preg_match("/order by/i", $query)) {
		$query .=  " order by DATE desc ";
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
	$_SESSION['remit1_sql'] = $query;

    $total_pages = ceil($_SESSION['remit1_rows']/ROWS_PER_PAGE);
    if ( $page > $total_pages ) $page = $total_pages;

    $sp_flag = $total_pages==1 ? true : false;
    $url = $this->url.'?page1='.$page;
    $url1 = $this->url.'?page1';
    $divid = $this->div1;
    // echo $query; //echo $total_pages . ", " . $page; echo "<pre>"; print_r($_SESSION); echo "</pre>\n";  
    ?>
<table class="table8" align="center">
  <caption class="cp1">
  Employer Remittance Submission Form Display &nbsp;
  <?php if (!$sp_flag) echo "[Page ".$page.", Total ".$total_pages." pages/".$_SESSION['remit1_rows']." records]"; ?>
  </caption>
  <thead>
    <?php if (!$sp_flag) { ?>
    <tr>
      <td colspan="13" align="center" class="page"><?=$this->draw($url1,$total_pages,$page,$divid); ?></td>
    </tr>
    <?php } ?>
    <tr class="rowTitle">
      <th class="bdRW"><label>No.</label></th>
      <th style="text-align:center; width:10%;max-width:10%;" class="bdRW"><a href="javascript:void(0);"><img id="img_delete" style="vertical-align:middle;" src="images/delete.png" width="16" height="16" border="0" title="remove files" /></a>
        <div id="div1_checked">delete</div>&nbsp;<input type="checkbox" value="All" id="checkall1" /><label>CheckAll</label></th>
      <th class="bdRW"><label>Division # </label>
        <br />
        <a href="javascript:void(0);"
        onClick="$(this).find('img').attr('src', 'images/wait.gif').show();$.get('<?=$url;?>', {sort:'division'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$(this).find('img').attr('src', 'images/wait.gif').show();$.get('<?=$url;?>', {sort:'division desc'}, function(data) {$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>Name</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$(this).find('img').attr('src', 'images/wait.gif').show();$.get('<?=$url;?>', {sort:'name'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$(this).find('img').attr('src', 'images/wait.gif').show();$.get('<?=$url;?>', {sort:'name desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>SubmittedBy</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$(this).find('img').attr('src', 'images/wait.gif').show();$.get('<?=$url;?>', {sort:'reporter'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$(this).find('img').attr('src', 'images/wait.gif').show();$.get('<?=$url;?>', {sort:'reporter desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW tabComment_remit" title="Double Click to edit this column"><label>Comment</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$(this).find('img').attr('src', 'images/wait.gif').show();$.get('<?=$url;?>', {sort:'comment'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$(this).find('img').attr('src', 'images/wait.gif').show();$.get('<?=$url;?>', {sort:'comment desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>Email</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$(this).find('img').attr('src', 'images/wait.gif').show();$.get('<?=$url;?>', {sort:'email'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$(this).find('img').attr('src', 'images/wait.gif').show();$.get('<?=$url;?>', {sort:'email desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>Post Timestamp</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$(this).find('img').attr('src', 'images/wait.gif').show();$.get('<?=$url;?>', {sort:'date'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$(this).find('img').attr('src', 'images/wait.gif').show();$.get('<?=$url;?>', {sort:'date desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW tabType_remit"><label>Type</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$(this).find('img').attr('src', 'images/wait.gif').show();$.get('<?=$url;?>', {sort:'payment_type'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$(this).find('img').attr('src', 'images/wait.gif').show();$.get('<?=$url;?>', {sort:'payment_type desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>File</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$(this).find('img').attr('src', 'images/wait.gif').show();$.get('<?=$url;?>', {sort:'File'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$(this).find('img').attr('src', 'images/wait.gif').show();$.get('<?=$url;?>', {sort:'File desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th class="bdRW"><label>Size</label>
        <br />
        <a href="javascript:void(0);"
        onClick="$(this).find('img').attr('src', 'images/wait.gif').show();$.get('<?=$url;?>', {sort:'size'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT1;?>
        </a>&nbsp;<a href="javascript:void(0);"
        onClick="$(this).find('img').attr('src', 'images/wait.gif').show();$.get('<?=$url;?>', {sort:'size desc'}, function(data){$('#<?=$divid;?>').hide().html(data).fadeIn(200); });">
        <?=SORT2;?>
        </a></th>
      <th style="text-align:center; width:10%;max-width:10%" class="bdRW"><a href="javascript:void(0);"><img id="img_download" style="vertical-align:middle;" src="images/download.png" width="16" height="16" border="0" title="download files" /></a>
        <div id="div_checked">download</div>&nbsp;<input type="checkbox" value="All" id="checkall2" /><label>CheckAll</label></th>
      <th class="bdRW"><label>Download<br />
        timestamp</label></th>
    </tr>
  </thead>
  <tbody>
    <?php
  $result = mysql_query($query);
  // if (mysql_num_rows($result) == 0) {  return false; }
  while ($row = mysql_fetch_array($result)) {
    $bgcolor = $row_no%2==1 ? 'odd': 'even';
    $ff = 'get_rfiles.php?file='.urlencode($row['file']).'&division='.urlencode($row['division']);
    $sno = 'span_'.$row_no;
    $rc = '<label>'.$row['count'].',&nbsp;'.(($row['download_time'])?$row['download_time']:'n/a').'</label>';
	$file_escape = addslashes($row['file']);
    ?>
    <tr class="<?=$bgcolor;?>" align="right">
      <td align="left" class="bdR"><label>
        <?=++ $row_no; ?>
        </label></td>
      <td class="bdR"><?php
	  if($row['deleted']=='Y') {
      	echo '<input name="deleted" type="checkbox" value="'.$row['file'].'" checked="checked" readonly="readonly" title="deleted already" />';
	  }
	  else {
      	echo '<input name="deleted" type="checkbox" value="'.$row['file'].'" />';
	  }
	  ?>
      </td>
      <td class="bdR"><label><a href="<?=$this->url;?>" title="<?=$row['division'];?>" target="_blank" style="text-decoration: underline;">
        <?=$row['division'];?>
        </a></label></td>
      <td class="bdR"><label>
        <?=htmlspecialchars($row['name']);?>
        </label></td>
      <td class="bdR"><label>
        <?=htmlspecialchars($row['reporter']);?>
        </label></td>
      <td class="bdR"><form method="post" action="<?=$this->url;?>" id="f_<?=$row['rid'];?>">
          <textarea name="comment" class="contactText" readonly="readonly" ondblclick="$(this).removeAttr('readonly');" onchange="Remit.reply('<?=$row['rid'];?>')"><?=$row['comment'];?></textarea>
          <input type="hidden" name="division" value="<?=$row['division'];?>" />
          <input type="hidden" name="rid" value="<?=$row['rid'];?>" />
        </form></td>
      <td style="text-transform: lowercase" class="bdR"><label> <a
        href="mailto:<?=strtolower($row['email']);?>" title="<?=strtolower($row['email']);?>">
        <?=$this->email_abbr($row['email']);?>
        </a></label></td>
      <td class="bdR"><label>
        <?=$row['date1'];?>
        </label></td>
      <td class="bdR"><label>
<?
if (preg_match("/^\s+$/",$row['payment_type']) || ($row['payment_type']=='')) {
    echo 'CHQ';
} else {
    echo $row['payment_type'];
}?>

        </label></td>
      <td class="bdR leftA"><label> <a href="<?=$ff;?>" target="i_view" onclick="Remit.update_download_time('<?=urlencode($row['file']);?>', '<?=$sno;?>')" title="<?=$row['file'];?>" style="text-decoration: underline;">
        <?=htmlspecialchars($row['file']);?>
        </a> </label></td>
      <td class="bdR"><label>
        <?=$row['size'];?>
        </label></td>
      <td class="bdR"><input name="download" type="checkbox" value="<?=$row['file'];?>" /></td>
      <td class="bdR"><span class="count_time" id="<?=$sno;?>">
        <?=$rc;?>
        </span> </td>
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
<div align="center" style="margin-top:10px;">
 <form action="get_csv.php" method="post">
  <input type="hidden" name="from" value="remittance" />
  <input name="xls" type="submit" value="Export to CSV File" class="export" />
 </form>
</div>
<iframe id="i_view" name="i_view" src="about:blank" frameborder="0" style="width:0px;height:0px;border:0px solid #fff;"></iframe>
<script language="javascript" type="text/javascript">
  //Remit.count_deleted();
  //Remit.count_download();
  $("input[name='deleted']:checkbox").bind('click', Remit.count_deleted);
  $("input[name='download']:checkbox").click(Remit.count_download);
  Remit.update_download_all();
  Remit.update_deleted();
  $('#checkall1').click(function(e){
	// e.preventDefault();	-> not work,coz the checkbox can't be selected.
	// alert(e.target.nodeName); -> input
	var t = (e.target);
	var n;
	if($(t).is(':checked')) {
	  $("input[name='deleted']:visible").attr('checked', 'checked');
	  n = $("input[name='deleted']:visible:checked").length;
	  $('#div1_checked').text(n+" checked!");
	  // if(! $('#img_delete').parent().is('a')) $('#img_delete').wrap('<a href="javascript:void(0);"></a>');
	}
	else {
	  $("input[name='deleted']:visible").removeAttr('checked');
	  $('#div1_checked').text('delete');
	  // if($('#img_delete').parent().is('a')) $("#img_delete").unwrap();
	}
  });
  $('#checkall2').click(function(e){
	var t = (e.target);	var n=0;
	if($(t).is(':checked')) {
	  $("input[name='download']:visible").attr('checked', 'checked');
	  n = $("input[name='download']:visible:checked").length;
	  $('#div_checked').text(n+" checked!");
	}
	else {
	  $("input[name='download']:visible").removeAttr('checked');
	  $('#div_checked').text('download');
	}
  });
</script>
<?php
  }

  function search1()
  {
    ?>
<form action="javascript:void(0);" id="form1" name="form1" method="post">
  <fieldset>
  <legend id="legend1"><span>Employer Remittance Submission Form Search:</span></legend>
  <table border="0" cellspacing="0" cellpadding="4" class="tab_remittance">
    <tr>
      <td align="right" nowrap><label for="division">Division #:</label></td>
      <td><input class="formField" name="division" type="text" id="division"></td>
      <td align="right" nowrap><label for="name">Employer Name:</label></td>
      <td><input class="formField" name="name" type="text" id="name" /></td>
      <td rowspan="4"><div class="inputBtn">
          <div>
            <input type="submit" name="search_form1" value="" class="tabSearch">
            <span id="msg1" style="display: none;"><img name="search_download" src="images/spinner.gif" width="16" height="16" alt="search Remittance..." border="0" style="margin-top: 26px; margin-left: 26px;"></span></div>
          <input type="reset" name="reset" value="" class="tabReset" title="Reset">
        </div></td>
    </tr>
    <tr>
      <td align="right"><label for="state">Payment Type:</label></td>
      <td><select class="formPopup" id="payment_type" name="payment_type">
          <?php $this->get_payment_type(); ?>
        </select></td>
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
      <td align="right" nowrap="nowrap"><label>Files deleted:</label></td>
      <td><input name="fdelete" type="radio" value="N" checked="checked">
        <label>No</label>
        &nbsp;
        <input name="fdelete" type="radio" value="Y">
        <label>Yes</label>
        &nbsp;
        <input name="fdelete" type="radio" value="A">
        <label>All</label></td>
      <td align="right" nowrap="nowrap"><label>Files not yet downloaded:</label></td>
      <td><input name="fdownload" type="radio" value="A" checked="checked">
        <label>All</label>
        &nbsp;
        <input name="fdownload" type="radio" value="Y">
        <label>Yes</label>
        &nbsp;
        <input name="fdownload" type="radio" value="N">
        <label>No</label></td>
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
            url: Remit.url,
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

  function search2() {
  	echo "Under-construction.";
  }

  function parse1()
  {
    $h['m1'] = isset($_POST['fdelete'])?trim($_POST['fdelete']):'N';
    $h['m8'] = isset($_POST['fdownload']) ? $_POST['fdownload']:'Y';

    $h['m3'] = isset($_POST['email']) ? trim($_POST['email']):'';
    $h['m4'] = isset($_POST['payment_type']) ? $_POST['payment_type']:'';
    $h['m5'] = isset($_POST['division']) ? trim($_POST['division']):'';
    $h['m6'] = isset($_POST['name']) ? trim($_POST['name']):'';
    $h['m9'] = isset($_POST['date1']) ? $this->get_date(trim($_POST['date1'])):'';
    $h['m10'] = isset($_POST['date2']) ? $this->get_date(trim($_POST['date2'])):'';
    $h['m11'] = isset($_POST['file']) ? $_POST['file']:'';

    $sql = $this->sql1;
    if ($h['m1']=='N') {
      $sql .= " and deleted = 'N' ";
    }
    elseif($h['m1']=='Y') {
      $sql .= " and deleted = 'Y' ";
    }

    if($h['m3']) {
      if ($h['m3']=='Y') {
        $sql .= " and (email is not null or email!=' ') ";
      }
      else {
        $sql .= " and (email is null or email=' ') ";
      }
    }
    if($h['m4']) {
      $sql .= " and payment_type = '" . $h['m4'] . "' ";
    }
    if($h['m5']) {
      $sql .= " and e.division like '" . $h['m5'] . "%' ";
    }
    if($h['m6']) {
      $sql .= " and name like '%" . $h['m6'] . "%' ";
    }
    if($h['m8']=='N') {
      $sql .= " and count > 0 ";
    }
    elseif($h['m8']=='Y') {
      $sql .= " and count = 0 ";
    }

    if($h['m9'] && $h['m10']) {
      $sql.=" and (r.date between '". $h['m9']. "' and '".$h['m10']."') ";
    }
    else if($h['m9']) {
      $sql .= " and date(r.date) = '" . $h['m9'] . "'";
    }
    else if($h['m10']) {
      $sql .= " and date(r.date) = '" .  $h['m10'] . "'";
    }
    if($h['m11']) {
      if ($h['m11']=='Y') {
        $sql .= " and file is not null ";
      }
      else {
        $sql .= " and file is null ";
      }
    }
    // $sql = str_replace_once(" and ", " where ", $sql);
    $_SESSION['remit1_sql'] = $sql;
    $total_rows = $this->get_total_rows($_SESSION['remit1_sql']);
    $_SESSION['remit1_rows'] = $total_rows < 1 ? 1 : $total_rows;
  }

	function delete_files($files)
	{
		$files = mysql_real_escape_string($files);
		$t = preg_replace("/;/", "','", $files);
		$t = "'".urldecode($t)."');";
		//$t = substr($t, 0, strlen($t)-2);
		$query = $this->sql2.$t;
	    mysql_query($query) or die('Error, update deleted is failed'.mysql_error().":".$query);
		return true;
	}
	function update_comment($rid, $comment) {
		$username = isset($_SESSION['pams_user']) ? mysql_real_escape_string($_SESSION['pams_user']) : '';
		//$query = "update remittance set comment='".$comment."', updatedby='".$username."', updated=now() where division='".$division."' and deleted='N'";
		$query = "update remittance set comment='".$comment."', updatedby='".$username."', updated=now() where rid=".$rid;
		mysql_query($query) or die("Can't update Remittance column at line ".__LINE__.': '.mysql_error());
		return true;
	}
	function update_download_time($filename) 
	{
		$query1 = "update remittance set count=count+1, download_time=current_timestamp where file='".$filename."'";
		mysql_query($query1) or die("Can't update remittance download_time column at line ".__LINE__.": ".mysql_error());
		
		$query2 = "insert into rtimes value('".$filename."', NULL)";
		mysql_query($query2) or die("Can't insert rtimes at line ".__LINE__.": ".mysql_error());
	}
	function get_count_time($filename) 
	{
		$query = "select count, download_time from remittance where file='".$filename."'";
		$res = mysql_query($query);
		$row = mysql_fetch_assoc($res);
		echo '<label>'.$row['count']. ', &nbsp;'. $row['download_time'].'</label>';
	}

}
?>

<?php
//alter table `monthly_upgrade` add column active varchar(1) default 'Y'

session_start();
/*
if(! ((isset($_SESSION['userid']) && $_SESSION['userid'])) ) {
	echo "<script>if(self.opener){self.opener.location.href='login.php';} else{window.parent.location.href='login.php';}</script>"; exit;
}
*/
include_once("config.php");
$du = new DataUpdateClass() or die();

if($du->get_env()=='Windows')
	define('PATH', '.\\data\\june_2011\\');
else
	define('PATH', './data/');

if(isset($_POST['notes']) || isset($_POST['id'])) {
	$sql = "update monthly_upgrade set notes='".mysql_real_escape_string(trim($_POST['notes']))."' WHERE active='Y'  AND id=".$_POST['id'];
	mysql_select_db(DB_PAMS, $du->pams);
	mysql_query($sql, $du->pams) or die (mysql_error());
	//echo htmlspecialchars($_POST['notes']); <textarea> don't need it.
	$data = $du->get_upgrade_record($_POST['id']);
	echo json_encode($data);
}
elseif(isset($_POST['new_notes'])) {
	mysql_select_db(DB_PAMS, $du->pams);
	$sql = "insert into monthly_upgrade(uid, username, notes) values(".$_SESSION['userid'].", '".$_SESSION['pams_user']."', '".mysql_real_escape_string(trim($_POST['new_notes']))."')";
	mysql_query($sql, $du->pams) or die (mysql_error());
	echo $du->display_list( $du->get_trails() );
}
// ps -ef | grep bash:  /bin/bash /home/backup/DBs/monthly/getCSV.bash
elseif(isset($_GET['js_upload'])) {
	// async: don't need result, just call getCSV.bash to run background.
	// exec("nohup /home/backup/DBs/monthly/getCSV.bash >/dev/null 2>&1 &");
	exec("/home/backup/DBs/monthly/run.bash >/dev/null 2>&1");
}
elseif(isset($_GET['js_process'])) {
	// sync: need result immediately.
	// system("kill -9 `ps -ef|grep sleep|grep -v grep|awk '{print $2}'`", $retval);
	system("/home/backup/DBs/monthly/process.bash >/dev/null 2>&1", $retval);
	if ($retval == 0) echo $du->errors['4'];
	else echo $du->errors['5'];
}
elseif(isset($_GET['js_cancel'])) {
	// need return variable, result immediately.
	// $ret = shell_exec("kill -9 `ps -ef|grep getCSV|grep -v grep|awk '{print $2}'`");
	system("kill -9 `ps -ef|grep getCSV|grep -v grep|grep -v vi|awk '{print $2}'`", $retval);
	// if ($retval == 0) echo $du->errors['2']; else echo $du->errors['3'];
	echo $retval;
}
elseif(isset($_GET['js_delete'])) {
	mysql_select_db(DB_PAMS, $du->pams);
	$sql = "UPDATE monthly_upgrade set active='N' WHERE id=".$_GET['id'];
	mysql_query($sql, $du->pams) or die (mysql_error());
	echo $du->errors['1'];
}
elseif(isset($_GET['js_file_records'])) {
	$files = $du->get_csv_files();
	$du->get_file_records($files);
}
elseif(isset($_GET['js_table_records'])) {
	list($num_rows, $info) = $du->get_records();
	$du->get_table_records($num_rows, $info);	
}
elseif(isset($_GET['js_monthly_records'])) {
  $du->update_monthly_records();
}
else {
	list($num_rows, $info) = $du->get_records();
	$files = $du->get_csv_files();
	$rows = $du->get_trails();
	// echo "<pre>"; print_r($info); echo "</pre>"; echo "<pre>"; print_r($rows); echo "</pre>";
	$du->initial($num_rows, $info, $files, $rows);
}

exit;
//////////////////////////////////////////////////

class DataUpdateClass
{
  var $url, $cibp, $pams, $num_rows, $info, $files, $rows, $errors;
  function __construct() {
	$this->url = $_SERVER['PHP_SELF'];
	// The sequence is import, by default, which DBNAME is selected? cibp.
	$this->pams = $this->connect_pams();
	$this->cibp = $this->connect_cibp();
	$this->errors = $this->errors();
  }
  function connect_cibp() {
	$cibp = mysql_pconnect(HOST, USER, PASS) or die(mysql_error());
	mysql_select_db(DB_NAME, $cibp);
	return $cibp;
  }
  function connect_pams() {
	$pams = mysql_pconnect(HOST, USER1, PASS1) or die(mysql_error());
	// mysql_select_db(DB_PAMS, $pams);
	return $pams;
  }
  function errors() {
  	return array(
		'1' => 'Delete Successfully.',
		'2' => 'Successfully kill the process.',
		'3' => 'No such process.',
		'4' => 'Month end data has been successfully processed.<br>Please enable the month end data process.',
		'5' => 'Month end data process failure.',
	);
  }
function initial($num_rows, $info, $files, $rows) 
{
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="css/style.css" rel="stylesheet" type="text/css" />
<link href="css/programs.css" rel="stylesheet" type="text/css" />
<style type="text/css">
.textarea1 {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
	color: #686868;
	width: 100%;
	height: 116px;
}
input:focus, textarea:focus, select:focus {
	background-color: lightyellow;
}
#info {
	font-family: "Courier New", Courier, monospace;
	font-size: 14px;
	font-weight: bold;
	color: #FF0000;
	margin: 20px auto;
	padding: 4px;
	border: thin dotted #0000FF;
	width: 60%;
	display: none;
}
#trails {
	margin-top: 20px;
}
input.mbutton {
    font-size: 13px;
    color: #FFF;
    background: url(../images/new_btn-create-admin-user.png) no-repeat;
    background-position: 0 0;
    width: 126px;
    height: 24px;
    border: 0;
}
input:hover.mbutton {
    background-position: 0 -24px;
}
</style>
<script language="javascript" type="text/javascript" src="js/jquery-1.5.1.min.js"></script>
<script language="javascript" type="text/javascript">
$(document).ready(function() {
  var url = '<?=$_SERVER['PHP_SELF'];?>';
  $('.tabrecords table tr:even').css('background-color','#f5f5f5');
  //$('#records').find('input:button').each(function() {	$(this).css({'cursor':'pointer'}); });
  //$('#records input:button').css('cursor','pointer');

  if( ($('#span_upload').text()=='Y') && ($('#span_status').text()!='1')) {
	$('#button1').removeAttr('disabled').css('cursor', 'pointer');
  }
  else {
	$('#button1, #button3').attr('disabled', true).css('cursor', 'default');
	// $('#button3').removeAttr('disabled').css('cursor', 'pointer');
  }
  if($('#span_process').text()=='Y') {
	$('#button2').removeAttr('disabled').css('cursor', 'pointer');
	$('#button1, #button3').attr('disabled', true).css({'cursor':'default','display':'none'});
  }
  else {
	$('#button2').attr('disabled', true).css('cursor', 'default');
  }
  
  $('#button1').click(function(event) {
	event.preventDefault();
	var button = $(this);
	if(confirm("Are you sure to upload the monthly data? It will take tens of minutes and will run at background.")) {
	  $.ajax({
			type: 'get',
			url: url,
			data: {'js_upload':1},
			success: function() {
				var msg1 = "\n<p>The upload process may take tens of minutes to 1 hour or even more, depending on network traffic.<br />";
				msg1 += " If finished, a remindering box will be present on the page, and (optional) an email will be sent to notice.<br />";
				msg1 += " Please be patient for the processing.</p>\n";
				$('#info').html(msg1).show();
				$('<img/>').attr({src:"images/wait.gif",border:"0",width:"16",height:"16"}).insertAfter(button).show('fast');
				$(button).attr({'disabled':true}).css({'cursor':'default', 'display':'none'});
				$('#button3').attr('disabled', true).css({'cursor':'default', 'display':'none'});
				$('#button2').removeAttr('disabled').css('cursor', 'pointer');
			}
	  });
	}
	return false;
  });  
  $('#button2').click(function(event) {
	event.preventDefault();
	var button = $(event.target);
	if(confirm("Are you going to process the monthly data to current Database? This will replace current data in Database.")) {
		$.ajax({
			type: 'get',
			url: url,
			data: {'js_process':1},
			beforeSend: function() {
				$('<img/>').attr({src:"images/wait.gif",border:"0",width:"16",height:"16"}).insertAfter(button).show('fast');
			},
			success: function(data) {
				$('#div_list').load(url+'?js_monthly_records=1');
				$('#info').html(data).show();
				$(button).next('img').empty().remove();
				$(button).attr({'disabled':true}).css({'cursor':'default', 'display':'none'});
				$('#button3, #button4').css({'cursor':'default', 'display':'none'});
				// $('#div_list').load(url+'?js_monthly_records=1');
				// $('#info').html('Please enable the month end data process').show();
			}
		});
	}
	return false;
  });
  $('#button3').click(function(event) {
	event.preventDefault();
	var button = $(event.target);
	if(confirm("Are you going to stop current processing?")) {
	  $.ajax({
		type: 'get',
		url: url,
		data: {'js_cancel':1},
		beforeSend: function() {
			$('<img/>').attr({src:"images/wait.gif",border:"0",width:"16",height:"16"}).insertAfter('#button3').show('fast');
		},
		success: function(data) {
			var msg3 = '';
			if(data == 0) msg3 = "Successfully stop the process.";
			else msg3 = "This process is currently not running, so nothing to do.";
			$('#info').html(msg3).show();
			$(button).next('img').empty().remove();
			// $(button).attr('disabled', true).css('cursor', 'default');
			$('#button1').removeAttr('disabled').css('cursor', 'pointer');
			if($('#button1').next('img').length) $('#button1').next('img').empty().remove();
		}
	  });
	}
	return false;
  });
  $('#button4').click(function(event) {
	event.preventDefault();
	$('#div_file_records').load(url+'?js_file_records=1');
  });

  $('#display_form_add').click(function() {
 	if($('#form_add').is(':visible')) {
		$('#form_add').hide();
		return;
	}
	$('#form_add').show();
	if($('#new_notes').length)$('#new_notes').focus();
  });
  $('#form_add').submit(function(e) {
  	e.preventDefault();
	$form_add = $(e.target);
	$.ajax({
		type: $(this).attr('method'),
		url: $(this).attr('action'),
		data: $(this).serialize(),
		beforeSend: function() {
			$('input:submit', $form_add).hide();
			$('<img/>').attr({id:'img_add',src:"images/wait.gif",border:"0",width:"16",height:"16"}).appendTo($form_add).show('fast');
		},
		success: function(data) {
			if(data) {
				$('#div_list').html(data).show(200);
			}
			$form_add.hide();
			$('input:submit', $form_add).show();
			if($('#img_add').length) $('#img_add').empty().remove();
		}
	});		
  });
  jQuery.fn.notes = function(id) {
	var notes = $('#'+id).val();
	var t = $(this).closest('tr');
	$.ajax({
		type: 'post',
		url: url,
		data: 'id='+id+'&notes='+notes,
		dataType: 'json',
		success: function(data) {
			$(t).find('td:eq(1)').text(data.username);
			$(t).find('td:eq(2)').text(data.created);		
			$('#'+id).val(data.notes);
		}
	});
	return false;
  }
  jQuery.fn.delete_notes = function(id) {
	if(confirm("Are you sure to delete this record [id="+id+"] ?")) {
	  	var t = $(this).closest('tr');
		$.get(url, {id: id, 'js_delete': 1}, function(data) {
			$(t).hide('slow');
		});
	}
	return false;
  }
});
</script>
</head>
<body>
<? $row = $this->get_notice();?>
<span id="span_upload" style="display:none"><?=$row['enable_upload'];?></span><span id="span_process" style="display:none"><?=$row['enable_process'];?></span>
<? $count = $this->get_upload_status();?>
<span id="span_status" style="display:none"><?=$count;?></span>
<div id="records">
  <table border="0" cellpadding="2" cellspacing="4" style="font-size:14px">
    <tr>
      <td class="tabrecords bdR2" id="div_table_records"><? $this->get_table_records($num_rows, $info); ?></td>
      <td>&nbsp;</td>
      <td class="origfiles" id="div_file_records"><? $this->get_file_records($files); ?></td>
    </tr>
  </table>
  <iframe id="iframe" name="iframe" src="about:blank" frameborder="0" style="width:0;height:0;border:0px solid #fff;"></iframe>
  <input type="button" id="button1" value="Upload" class="mbutton1" title="Upload Original Oracle Data"/>
  <input type="button" id="button3" value="Cancel Upload" class="mbutton1" title="Terminate the Upload Process" />
  <input type="button" id="button4" value="Refresh Upload Status" class="mbutton1" title="Reresh File Records" />
  <input type="button" id="button2" value="Processing" class="mbutton1" disabled="disabled" title="Start to Process uploaded Data" />
</div>
<div id="info" align="center"></div>
<div id="trails" align="center">
  <h3>Processing History<a href="javascript:void(0);" id="display_form_add" style="float:right; margin-right:100px"><img src="images/note_add.png" width="15" height="15" title="Add New Notes" /></a></h3>
  <form id="form_add" style="display:none" action="<?=$_SERVER['PHP_SELF'];?>" method="post">
    <table width="60%" border="0" cellpadding="0" cellspacing="0">
      <caption>
      Add New Notes
      </caption>
      <tr>
        <td><textarea id="new_notes" name="new_notes" class="textarea1"></textarea>
        </td>
      </tr>
      <tr>
        <td align="center"><input type="submit" name="submit_add" id="submit_add" value="Submit" class="submit" />
          <input type="button" name="Close" value="Close" class="close" onClick="this.form.style.display='none';" />
        </td>
      </tr>
    </table>
  </form>
  <div id="div_list" align="center">
    <?php $this->display_list($rows); ?>
  </div>
</div>
</body>
</html>
<?php
}

function display_list($rows) {
?>
<table width="88%" border="0" cellpadding="0" cellspacing="0">
  <thead>
    <tr>
      <th class="bdRW">No.</th>
      <th class="bdRW">Who</th>
      <th class="bdRW">When</th>
      <th style="width:70%" class="bdRW">notess</th>
      <th class="bdRW">Option</th>
    </tr>
  </thead>
  <tbody>
    <?php
  $row_no = 1;
  foreach ($rows as $row) {
    $bgcolor = ($row_no%2==1) ? 'odd': 'even';
	$cid = $row['id'];
    ?>
    <tr>
      <td align="right" class="bdR"><?=$row_no ++;?></td>
      <td align="right" class="bdR"><?=$row['username'];?></td>
      <td align="right" class="bdR"><?=$row['created'];?></td>
      <td align="center" class="bdR"><textarea name="notes" id="<?=$cid;?>" class="textarea1" readonly="readonly" onDblClick="$(this).removeAttr('readonly').css('border-width','2px');" onChange="$(this).notes('<?=$cid;?>')"><?=$row['notes'];?>
</textarea></td>
      <td align="right" class="bdR"><a href="javascript:void(0);" onClick="$(this).delete_notes('<?=$row['id'];?>');"><img src="images/pams-delete-16px.png" title="Delete" /></a></td>
    </tr>
    <? } ?>
  </tbody>
</table>
<?
}

function get_upgrade_record($id)
{
	mysql_select_db(DB_PAMS, $this->pams);
	$res = mysql_query("SELECT * FROM monthly_upgrade WHERE id=".$id, $this->pams);
	$row = mysql_fetch_assoc($res);
	mysql_free_result($res);
	return $row;
}

function get_notice()
{
	mysql_select_db(DB_PAMS, $this->pams);
	$res = mysql_query("SELECT * FROM monthly_notice", $this->pams);
	$row = mysql_fetch_assoc($res);
	mysql_free_result($res);
	return $row;
}

function get_records() 
{
	// mysql_select_db(DB_NAME, $this->cibp);
	$query1 = array(
	  "select count(*) from benefits",
	  "select count(*) from dependents",
	  "select count(*) from employers",
	  "select count(*) from users"
	);
	// use $row['Update_time']
	$query2 = array(
		"SHOW TABLE STATUS FROM cibp WHERE name = 'dependents'", 
		"SHOW TABLE STATUS FROM cibp WHERE name = 'dependents'",
		"SHOW TABLE STATUS FROM cibp WHERE name = 'employers'",
		"SHOW TABLE STATUS FROM cibp WHERE name = 'users'"
	);
	$num_rows = array();
	foreach ($query1 as $sql) {
		$result = mysql_query($sql, $this->cibp);
		//$num_rows[] = mysql_num_rows($result);
		$num_rows[] = mysql_fetch_row($result);
		mysql_free_result($result);
	}
	foreach ($query2 as $sql) {
		$result = mysql_query($sql, $this->cibp);
		//$num_rows[] = mysql_num_rows($result);
		$info[] = mysql_fetch_assoc($result);
		mysql_free_result($result);
	}
	return array($num_rows, $info);
}

function get_trails()
{
	$rows = array();
	$sql = "select * from monthly_upgrade WHERE active='Y' order by created desc";
	$result = mysql_query($sql, $this->pams);
	while ( $row = mysql_fetch_assoc($result)) {
		$rows[] = $row;
	}
	mysql_free_result($result);
	return $rows;
}
function get_csv_files() {
	$files = array();
	if (is_dir(PATH)) {
		$dir_res = opendir(PATH);
		while ($file=readdir($dir_res)) {
			if (preg_match("/\.csv$/", $file))  {
				$files[$file] = $this->get_file_stat($file);
				//$files[] = array($file => get_file_stat($file));
			}
		}
	}
	// echo "<pre>";print_r($files);echo "</pre>";
	return $files;
}
// http://php.net/manual/en/function.stat.php
function get_file_stat_old($file)
{
	$ss = @stat(PATH.$file);
	if(!$ss) return false; //couldnt stat file
	return array(	
		'accessed'=>@date('Y-m-d H:i:s',$ss['atime']),
		'modified'=>@date('Y-m-d H:i:s',$ss['mtime']),
		'created'=>@date('Y-m-d H:i:s',$ss['ctime'])
	);
}

function get_file_stat($file)
{
	mysql_select_db(DB_PAMS, $this->pams);
	$query = "SELECT * FROM monthly_records WHERE file='".$file."' ORDER BY created desc";
	$result = mysql_query($query, $this->pams);
	$row = mysql_fetch_assoc($result);
	mysql_free_result($result);
	return $row;
}

//Apache/2.2.14 (Win32) DAV/2 mod_ssl/2.2.14 OpenSSL/0.9.8l mod_autoindex_color PHP/5.3.1 mod_apreq2-20090110/2.7.1 mod_perl/2.0.4 Perl/v5.10.1
function get_env() {
  if(isset($_SERVER['SERVER_SOFTWARE'])) {
	if(preg_match('/Win32/i', $_SERVER['SERVER_SOFTWARE'])) return 'Windows';
	return 'Unix';
  }
}

function get_table_records($num_rows, $info)
{
?>
<table border="0" cellpadding="4" cellspacing="4">
  <caption>
  <strong>Last Update</strong>
  </caption>
  <thead>
    <tr>
      <th class="bdRW">CIBP Tables</th>
      <th>Records</th>
      <th>Latest Updated</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td class="bdR">Members</td>
      <td align="right"><?=$num_rows[3][0];?></td>
      <td align="right"><?=$info[3]['Update_time'];?></td>
    </tr>
    <tr>
      <td class="bdR">Employers</td>
      <td align="right"><?=$num_rows[2][0];?></td>
      <td align="right"><?=$info[2]['Update_time'];?></td>
    </tr>
    <tr>
      <td class="bdR">Benefits (hours bank)</td>
      <td align="right"><?=$num_rows[0][0];?></td>
      <td align="right"><?=$info[0]['Update_time'];?></td>
    </tr>
    <tr>
      <td class="bdR">Dependents</td>
      <td align="right"><?=$num_rows[1][0];?></td>
      <td align="right"><?=$info[1]['Update_time'];?></td>
    </tr>
  </tbody>
</table>
<?php
}
function get_file_records($files) 
{
?>
<table border="0" cellpadding="4" cellspacing="4">
  <caption>
  <strong>Current Upload Status</strong>
  </caption>
  <thead>
    <tr>
      <th>Original Files</th>
      <th>Start</th>
      <th>End</th>
      <th>Total</th>
      <th>Result</th>
    </tr>
  </thead>
  <tbody>
    <? $num = 0;
	foreach ($files as $k=>$v) { ?>
    <tr>
      <td><a href="get_rfiles.php?file=<?=urlencode($k).'&routine='.$num;?>" target="iframe">
        <?=$k;?>
        </a></td>
      <td><?=$v['start'];?></td>
      <td><?=$v['end'];?></td>
      <td><?=$v['total'];?></td>
      <td><?php $t = $v['result'];
			switch($t) {
				case 'Y':
					echo 'Done' . '&nbsp;<img src="images/done.gif" width="16" height="16" border="0" />';
					break;
				case 'N':
					echo 'Not Process&nbsp;<img src="images/delete.png" width="16" height="16" border="0" />';
					break;
				case 'P':
					echo 'Processing...'.'&nbsp;<img src="images/processing.gif" width="20" height="20" border="0" />';
					break;
				default:
					echo 'Not Process&nbsp;<img src="images/logo_sales.png" width="20" height="20" border="0" />';
			}
?></td>
    </tr>
    <?
				$num ++;
			}
			?>
  </tbody>
</table>
<?
  }
  
  function update_monthly_records()
  {
  	if(! isset($_SESSION['userid'])) {
		$_SESSION['userid'] = 2;
		$_SESSION['pams_user'] = 'Admin';
	}
	
	$str = "Filename\t\t\t\t\t\t\tStart At:\t\t\tEnd At:\t\tTotal Seconds\tResult\n\n";
  	mysql_select_db(DB_PAMS, $this->pams);
	$sql = "SELECT file, start, end, total, result FROM monthly_records";
	$res = mysql_query($sql, $this->pams);
	while ($row = mysql_fetch_assoc($res)) {
		$str .= implode(",\t", $row);
		$str .= "\n";
	}
	$sql = "INSERT INTO monthly_upgrade(uid, username, notes) values(".$_SESSION['userid'].", '".$_SESSION['pams_user']."', '".mysql_real_escape_string($str)."')";
	mysql_query($sql, $this->pams) or die (mysql_error());

	$sql = "DELETE FROM monthly_records";
	mysql_query($sql, $this->pams) or die (mysql_error());
	
	echo $this->display_list( $this->get_trails() );
  }

  function get_upload_status()
  {
	mysql_select_db(DB_PAMS, $this->pams);
	$query = "SELECT count(*) FROM monthly_records WHERE result='P'";
	$result = mysql_query($query, $this->pams);
	$row = mysql_fetch_row($result);
	mysql_free_result($result);
	return $row[0];
  }

}
?>

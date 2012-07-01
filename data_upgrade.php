<?php
//alter table `monthly_upgrade` add column active varchar(1) default 'Y'

session_start();
if(! ((isset($_SESSION['userid']) && $_SESSION['userid'])) ) {
	echo "<script>if(self.opener){self.opener.location.href='login.php';} else{window.parent.location.href='login.php';}</script>"; exit;
}
include_once("config.php");
if(get_env()=='Windows')
	define('PATH', '.\\data\\june_2011\\');
else
	define('PATH', './data/pipe/');

$db = mysql_pconnect(HOST, USER1, PASS1) or die(mysql_error());
mysql_select_db(DB_PAMS, $db);


if(isset($_POST['notes']) || isset($_POST['id'])) {
	$sql = "update monthly_upgrade set notes='".mysql_real_escape_string(trim($_POST['notes']))."' WHERE active='Y'  AND id=".$_POST['id'];
	mysql_query($sql, $db) or die (mysql_error());
	//echo htmlspecialchars($_POST['notes']); <textarea> don't need it.
	echo $_POST['notes'];
	exit;
}
elseif(isset($_POST['js_process'])) {
	exec("ls -l /home/cibp/");
	// exec("nohup /home/ >/dev/null 2>&1 &");
	//$result = 'Done';
	//$sql = "insert into monthly_upgrade(uid, username, result) values(".$_SESSION['userid'].", '".$_SESSION['pams_user']."', '".$result."')";
	//mysql_query($sql, $db) or die (mysql_error());
	//echo htmlspecialchars($_POST['notes']); <textarea> don't need it.
	//echo display_list(get_trails());
}
elseif(isset($_POST['new_comment'])) {
	$sql = "insert into monthly_upgrade(uid, username, notes) values(".$_SESSION['userid'].", '".$_SESSION['pams_user']."', '".mysql_real_escape_string(trim($_POST['new_comment']))."')";
	mysql_query($sql, $db) or die (mysql_error());
	echo display_list(get_trails());
}
elseif(isset($_GET['js_delete'])) {
	$sql = "UPDATE monthly_upgrade set active='N' WHERE id=".$_GET['id'];
	mysql_query($sql, $db) or die (mysql_error());
	echo "Delete Successfully.";
	//echo display_list(get_trails());
}
else {
	list($num_rows, $info) = get_records();	
	// echo "<pre>"; print_r($info); echo "</pre>";
	$files = get_csv_files();
	$rows = get_trails();
	// echo "<pre>"; print_r($rows); echo "</pre>";
	initial($num_rows, $info, $files, $rows);
}

exit;
//////////////////////////////////////////////////

function initial($num_rows, $info, $files, $rows) 
{
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="css/style.css" rel="stylesheet" type="text/css" />
<link href="css/programs.css" rel="stylesheet" type="text/css" />
<style>
.textarea1 {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 11px;
	color: #686868;
	width: 100%;
	height: 100px;
	max-height:150px;
}
input:focus, textarea:focus, select:focus {
	background-color: lightyellow;
}
</style>
<script language="javascript" type="text/javascript" src="js/jquery-1.5.1.min.js"></script>
<script language="javascript" type="text/javascript">
$(document).ready(function() {
  $('.tabrecords table tr:even').css('background-color','#f5f5f5');
  $('#form_process input[type=button]:eq(0)').click(function(event) {
	event.preventDefault();
	var du = this.form;
	if(confirm("Are you going to process the montly data transferation?")) {
		$.ajax({
				type: $(du).attr('method'),
				url: $(du).attr('action'),
				data: {'js_process':1},
				success: function(data) {
					$('#trails').html(data).fadeIn(200);
				}
		});
	}
	return false;
  });
  $('#add_id').click(function() {
 	if($('#form_add').is(':visible')) {
		$('#form_add').hide();
		return;
	}
	$('#form_add').show();
  });
  $('#form_add').submit(function(e) {
  	e.preventDefault();
	$.ajax({
		type: $(this).attr('method'),
		url: $(this).attr('action'),
		data: $(this).serialize(),
		beforeSend: function() {
			$('#submit_add').hide();
			$('#img_add').show();
		},
		success: function(data) {
			if(data) {
				$('#div_list').html(data).show(200);
			} else {
				alert('Error Here.');
			}
			$('#form_add').hide();
			$('#submit_add').show();
			$('#img_add').hide();
		}
	});		
  });
  jQuery.fn.comment = function(fid) {
	var content = $('#'+fid).val();
	$.ajax({
		type: 'post',
		data: 'id='+fid+'&notes='+content,
		success: function(data) {
			$('#'+fid).val(data);
		}
	});
	return false;
  }
  jQuery.fn.delete_comment = function(id) {
  	var t = $(this).closest('tr');
	if(confirm("Are you sure to delete this record?")) {
		$.get('<?=$_SERVER['PHP_SELF'];?>', {id: id, 'js_delete': 1}, function(data) {
			$(t).hide('slow');
		});
	}
	return false;
  }
  $('#form_process').find('input:button').each(function() {
  	$(this).css({'cursor':'pointer'});
  });
});
</script>
</head>
<body>
<div id="records">
  <table border="0" cellpadding="0" cellspacing="0" style="font:14px Verdana, Arial, Helvetica, sans-serif;">
    <tr>
      <td class="tabrecords bdR2"><table border="0" cellpadding="0" cellspacing="0">
          <thead>
            <tr>
              <th class="bdRW">CIBP Tables</th>
              <th>Records</th>
              <th>Latest Updated</th>
            </tr>
          </thead>
          <tbody>
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
            <tr>
              <td class="bdR">Employers</td>
              <td align="right"><?=$num_rows[2][0];?></td>
              <td align="right"><?=$info[2]['Update_time'];?></td>
            </tr>
            <tr>
              <td class="bdR">Users (Members)</td>
              <td align="right"><?=$num_rows[3][0];?></td>
              <td align="right"><?=$info[3]['Update_time'];?></td>
            </tr>
          </tbody>
        </table></td>
      <td class="origfiles"><table border="0" cellpadding="0" cellspacing="0">
          <thead>
            <tr>
              <th>Original Files</th>
              <th>Upload Time</th>
              <th>Status</th>
              <th>Generated</th>
            </tr>
          </thead>
          <tbody>
            <? $num = 0;
			foreach ($files as $k=>$v) { ?>
            <tr>
              <td><a href="get_rfiles.php?file=<?=urlencode($k).'&routine='.$num;?>" target="iframe">
                <?=$k;?>
                </a></td>
              <td><?=$v['accessed'];?></td>
              <td><?=$v['modified'];?></td>
              <td><?=$v['created'];?></td>
            </tr>
            <?
				$num ++;
			}
			?>
          </tbody>
        </table></td>
    </tr>
  </table>
  <iframe id="iframe" name="iframe" src="about:blank" frameborder="0" style="width:0;height:0;border:0px solid #fff;"></iframe>
  <form method="post" action="<?=$_SERVER['PHP_SELF'];?>" id="form_process">
    <input type="button" value="Upload1&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" style="background:#fff url(images/new_fileUpload.png) no-repeat right center" />
    <input type="button" value="Upload2&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" style="background:#fff url(images/new_fileUpload.png) no-repeat right center" />
    <input type="button" value="Processing&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" style="background:#fff url(images/new_fileUpload.png) no-repeat right center;" disabled="disabled" />
    <img src="images/wait.gif" width="16" height="16" style="display:none" />
    <input type="button" value="Cancel&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" style="background:#fff url(images/icon-cancel.jpg) no-repeat right center" />
  </form>
</div>
<div id="trails" align="center">
  <h3>Trails of Processing <a href="javascript:void(0);" id="add_id" style="float:right"><img src="images/note_add.png" width="15" height="15" title="Add New Comment" /></a></h3>
  <form id="form_add" style="display:none" action="<?=$_SERVER['PHP_SELF'];?>" method="post">
    <table width="90%" border="0" cellpadding="0" cellspacing="0">
      <caption>
      Add a new comment
      </caption>
      <tr>
        <td><textarea id="new_comment" name="new_comment" class="textarea1"></textarea>
        </td>
      </tr>
      <tr>
        <td align="center"><input type="submit" name="submit_add" id="submit_add" value="Submit" class="submit" />
          <img id="img_add" src="images/wait.gif" height="16" width="16" style="display:none" />
          <input type="button" name="Close" value="Close" class="close" onClick="this.form.style.display='none';" />
        </td>
      </tr>
    </table>
  </form>
  <div id="div_list" align="center">
    <?php display_list($rows); ?>
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
      <th class="bdRW">Result</th>
      <th>Comments</th>
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
      <td align="right" class="bdR"><?=$row['result'];?>
        <img src="images/done.gif" /></td>
      <td align="center"><textarea name="notes" id="<?=$cid;?>" class="mduComment" readonly="readonly" onDblClick="$(this).removeAttr('readonly').css('border-width','2px');" onChange="$(this).comment('<?=$cid;?>')"><?=$row['notes'];?>
</textarea></td>
      <td align="right"><a href="javascript:void(0);" onClick="$(this).delete_comment('<?=$row['id'];?>');"><img src="images/pams-delete-16px.png" title="Delete" /></a></td>
    </tr>
    <? } ?>
  </tbody>
</table>
<?
}

function get_records() 
{
	$link = mysql_pconnect(HOST, USER, PASS) or die(mysql_error());
	mysql_select_db(DB_NAME, $link);
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
		$result = mysql_query($sql, $link);
		//$num_rows[] = mysql_num_rows($result);
		$num_rows[] = mysql_fetch_row($result);
		mysql_free_result($result);
	}
	foreach ($query2 as $sql) {
		$result = mysql_query($sql, $link);
		//$num_rows[] = mysql_num_rows($result);
		$info[] = mysql_fetch_assoc($result);
		mysql_free_result($result);
	}
	return array($num_rows, $info);
}

function get_trails()
{
	$db = mysql_pconnect(HOST, USER1, PASS1) or die(mysql_error());
	mysql_select_db(DB_PAMS, $db);
	$rows = array();
	$sql = "select * from monthly_upgrade WHERE active='Y' order by created desc";
	$result = mysql_query($sql, $db);
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
			if ($file=='..' || $file=='.') continue;
			$files[$file] = get_file_stat($file);
			//$files[] = array($file => get_file_stat($file));
		}
	}
	//echo "<pre>";print_r($files);echo "</pre>";
	return $files;
}
// http://php.net/manual/en/function.stat.php
function get_file_stat($file)
{
	$ss = @stat(PATH.$file);
	if(!$ss) return false; //couldnt stat file
	return array(	
		/*'mtime'=>$ss['mtime'], //Time of last modification
		'atime'=>$ss['atime'], //Time of last access.
		'ctime'=>$ss['ctime'], //Time of last status change*/
		'accessed'=>@date('Y M D H:i:s',$ss['atime']),
		'modified'=>@date('Y M D H:i:s',$ss['mtime']),
		'created'=>@date('Y M D H:i:s',$ss['ctime'])
	);
}

//Apache/2.2.14 (Win32) DAV/2 mod_ssl/2.2.14 OpenSSL/0.9.8l mod_autoindex_color PHP/5.3.1 mod_apreq2-20090110/2.7.1 mod_perl/2.0.4 Perl/v5.10.1
function get_env() {
  if(isset($_SERVER['SERVER_SOFTWARE'])) {
	if(preg_match('/Win32/i', $_SERVER['SERVER_SOFTWARE'])) return 'Windows';
	return 'Unix';
  }
}
?>

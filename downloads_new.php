<?php
/**
 * http://www.plupload.com/, http://www.plupload.com/plupload/docs/api/index.html#class_plupload.Uploader.html
 */
session_start();
if(! ((isset($_SESSION['userid']) && $_SESSION['userid'])) ) {
	echo "<script>if(self.opener){self.opener.location.href='login.php';} else{window.parent.location.href='login.php';}</script>"; exit;
}
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
include_once('config.php');
include_once("pams1.php");

////////////////////////////

class Plupload extends PamsBase
{
  var $url, $cibp, $pams, $div1, $div2, $sql;
  function __construct() {
	$this->url = $_SERVER['PHP_SELF'];
	$this->cibp = $this->connect_cibp();
	//$this->pams = $this->connect_pams();
    $this->div1 = 'main1';
    $this->div2 = 'main2';
	$this->sql = "select * from myresources_new where active='Y' ";
  }
  function connect_cibp() {
	$db = mysql_pconnect(HOST, USER, PASS) or die(mysql_error());
	mysql_select_db(DB_NAME, $db);
	return $db;
  }
  function connect_pams() {
	$db = mysql_pconnect(HOST, USER1, PASS1) or die(mysql_error());
	mysql_select_db(DB_PAMS, $db);
	return $db;
  }

  function init() {
    $_SESSION['download_sql'] = $this->sql;
    $total_rows = $this->get_total_rows($_SESSION['download_sql']);
    $_SESSION['download_rows'] = $total_rows < 1 ? 1 : $total_rows;
?>
<style type="text/css">
@import url(css/plupload.queue.css);
</style>
<!--<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script type="text/javascript">
	google.load("jquery", "1.4.2");
</script>
<script language="javascript" type="text/javascript" src="http://bp.yahooapis.com/2.4.21/browserplus-min.js"></script>
-->
<script language="javascript" type="text/javascript" src="js/jquery-1.5.1.min.js"></script>
<script language="javascript" type="text/javascript" src="plupload/js/gears_init.js"></script>
<script language="javascript" type="text/javascript" src="js/browserplus-min.js"></script>
<script language="javascript" type="text/javascript" src="plupload/js/plupload.full.min.js"></script>
<script language="javascript" type="text/javascript" src="plupload/js/jquery.plupload.queue.min.js"></script>
<link href="css/cwcalendar.css" rel="stylesheet" type="text/css" />
<link type="text/css" rel="stylesheet" href="css/style.css" />
<link type="text/css" rel="stylesheet" href="css/my.css"  />
<!--<link type="text/css" rel="stylesheet" href="css/programs.css" />-->
<style type="text/css" media="all">
@import "css/c-css.php";
</style>
<script src="SpryAssets/SpryTabbedPanels.js" type="text/javascript"></script>
<link href="SpryAssets/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script language="JavaScript" src="js/calendar.js" type="text/javascript"></script>
<div id="programs">
  <div id="TabbedPanels1" class="TabbedPanels">
    <ul class="TabbedPanelsTabGroup">
      <li class="TabbedPanelsTab" tabindex="0" id="tab1">List & Update</li>
      <li class="TabbedPanelsTab" tabindex="0" id="tab2">Add</li>
      <li class="TabbedPanelsTab textcolorGrey move2" tabindex="2" id="tab8">&nbsp;&nbsp;&nbsp;Hide Left Menu</li>
      <li onclick="window.open('help.html', 'help','height=260,width=600,scrollbars=1,resizable=1');">&nbsp;&nbsp;<img src="images/help.png" border="0" width="16px" height="16" alt="Help" class="iconHelp" /></li>
    </ul>
    <div class="TabbedPanelsContentGroup tabPanelWidth_dl">
      <div class="TabbedPanelsContent">
        <div>
          <div id="title1" class="formLegend">Click to Search Downloads List</div>
          <div id="search1"></div>
        </div>
        <div id="main1">
          <?php $this->get_list();?>
        </div>
      </div>
      <div class="TabbedPanelsContent">
        <div id="main2">
          <? $this->get_form();?>
        </div>
      </div>
    </div>
  </div>
</div>
<script language="javascript" type="text/javascript">
$(function() {
  var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
  $('<div id="livetip"></div>').hide().appendTo('body');
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
	$('#search1').load('<?=$this->url;?>?search1=1').fadeIn(200);
  });	
});
</script>
<?
  }

  function get_list() 
  {
    $page = isset($_GET['page1']) ? $_GET['page1'] : 1;
    $row_no = ((int)$page-1)*ROWS_PER_PAGE;

    if (isset($_SESSION['download_sql']) && $_SESSION['download_sql']) $query = $_SESSION['download_sql'];
    else $query = $this->sql;

    if (preg_match("/limit /i", $query)) {
      $query = preg_replace("/limit.*$/i", '', $query);
    }
	if (! preg_match("/order by/i", $query)) {
		$query .=  " order by ID desc ";
	}
	//  SELECT * FROM `myresources_new` WHERE 1 order by id desc limit 1,10; just replace 'order by id desc', keep 'limit 0,10' unchanged.    
    if (isset($_GET['sort'])) {
      $new_order = $_GET['sort'];
      if (eregi("order by", $query)) {
		if (eregi("limit", $query)) {
	        $query = preg_replace("/order by.* (?=limit|$)/i", " order by " . $new_order, $query);
		}
		else $query = preg_replace("/order by.*$/i", " order by " . $new_order, $query);
      }
      else {
        $query .= " order by " . $new_order;
      }
    }

 	if (!eregi("limit", $query)) $query .=  " limit ".$row_no.", ".ROWS_PER_PAGE;
	$_SESSION['download_sql'] = $query;
    $total_pages = ceil($_SESSION['download_rows']/ROWS_PER_PAGE);
    if ( $page > $total_pages ) $page = $total_pages;

    $sp_flag = $total_pages==1 ? true : false;
    $url = $this->url.'?page1='.$page;
    $url1 = $this->url.'?page1';
    $divid = $this->div1;
	?>
<table class="table8" align="center">
  <caption class="cp1">
  MyCIBP File Upload Management - Downloads &nbsp;
  <?php if (!$sp_flag) echo "[Page ".$page.", Total ".$total_pages." pages/".$_SESSION['download_rows']." records]"; ?>
  </caption>
  <thead>
    <?php if (!$sp_flag) { ?>
    <tr>
      <td colspan="12" align="center" class="page"><?=$this->draw($url1,$total_pages,$page,$divid); ?></td>
    </tr>
    <?php } ?>
    <tr class="rowTitle">
      <th>No.</th>
      <th class="bdRW"><label>Title</label></th>
      <th class="bdRW">Location</th>
      <th class="bdRW">Division</th>
      <th class="bdRW">File</th>
      <th class="bdRW">Type</th>
      <!--      <th class="bdRW">Size</th>
-->
      <th class="bdRW"><label>Comment</label></th>
      <th class="bdRW">CreatedBy</th>
      <th class="bdRW">Created</th>
      <th class="bdRW">UpdatedBy</th>
      <th class="bdRW">Updated</th>
      <th><label>Options</label></th>
    </tr>
  </thead>
  <tbody>
    <?php
  $result = mysql_query($query, $this->cibp);
  // if (mysql_num_rows($result) == 0) {  return false; }
  while ($row = mysql_fetch_array($result)) {
    $bgcolor = $row_no%2==1 ? 'odd': 'even';
	$updated = '&nbsp;';
	if ($row['updated'] && !preg_match("/0000-00-00/", $row['updated'])) {
		$updated = $row['updated'];
	}
    ?>
    <tr class="<?=$bgcolor;?>" align="right">
      <td align="left" class="bdR"><label>
        <?=++ $row_no; ?>
        </label></td>
      <td class="bdR"><form method="post" action="<?=$this->url;?>" id="t_<?=$row['id'];?>">
          <!-- onchange="$(this).title('t_< ?=$row['id'];?>')" -->
          <!--<input name="title" readonly="readonly" class="dlTitle" ondblclick="$(this).removeAttr('readonly').css('border-width','2px');" value="< ?=htmlspecialchars($row['title']);?>" />-->
          <textarea name="title" readonly="readonly" class="dlTitle" ondblclick="$(this).removeAttr('readonly').css('border-width','2px');" onchange="$(this).title('t_<?=$row['id'];?>')"><?=htmlspecialchars($row['title']);?>
</textarea>
          <input type="hidden" name="id" value="<?=$row['id'];?>" />
        </form></td>
      <td class="bdR"><label>
        <? if($row['location']=='P') {
          echo 'Public';
        } elseif($row['location']=='M') {
		  echo 'Members';
        } else {
		  echo 'N/A';
        };?>
        </label></td>
      <td class="bdR"><label>
        <?=$row['division'];?>
        </label></td>
      <td class="bdR"><label><a href="<?=RESOURCES_DIR.$row['file'];?>" title="<?=$row['file'];?>" target="_blank">
        <?=htmlspecialchars($row['file']);?>
        </a></label></td>
      <td class="bdR"><label>
        <?=$this->get_ftype(htmlspecialchars($row['type']));?>
        </label></td>
      <!--td class="bdR"><label>
        < ?=$row['size'];?>
        </label></td-->
      <td class="bdR"><form method="post" action="<?=$this->url;?>" id="fd_<?=$row['id'];?>" style="margin:0;padding:0;">
          <textarea name="notes" class="dlNotes" readonly="readonly" ondblclick="$(this).removeAttr('readonly').css('border-width','2px');" onchange="$(this).comment('fd_<?=$row['id'];?>')"><?=htmlspecialchars($row['comment']);?>
</textarea>
          <input type="hidden" name="id" value="<?=$row['id'];?>" />
        </form></td>
      <td class="bdR"><label>
        <?=htmlspecialchars($row['createdby']);?>
        </label></td>
      <td class="bdR"><label>
        <?=$row['created'];?>
        </label></td>
      <td class="bdR"><label>
        <?=$row['updatedby']?$row['updatedby']:'N/A';?>
        </label></td>
      <td class="bdR"><label>
        <?=$updated;?>
        </label></td>
      <td class="optbtn"><label><a href="<?=RESOURCES_DIR.$row['file'];?>" title="View <?=$row['file'];?>" target="_blank" class="preview"></a></label>
        &nbsp;
        <label><a href="<?=$this->url;?>?id=<?=$row['id'];?>&action=delete" class="delete" title="Delete"></a></label>
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
      <td colspan="12" align="center" class="page"><?=$this->draw($url1,$total_pages,$page,$divid); ?></td>
    </tr>
  </tfoot>
  <?php } ?>
</table>
<script language="javascript" type="text/javascript">
var url = '<?=$this->url;?>';
var divid = "<?=$this->div1;?>";
var ary = new Array();
jQuery.fn.sortBy= function(sort_column) {
	var t = $(this);
	var page = '<?=$page;?>';
	var t1 = t.find('img').attr('src');
	var t2 = t.find('img');
	t.find('img').attr('src', 'images/wait.gif').show();
	$.get(url, {page1:page,sort:sort_column}, function(data) {
	  $('#'+divid).html(data);
	  	if (/up/.test(t1)) t2.attr('src', 'images/up-arrow.png').show();
		else t2.attr('src', 'images/down-arrow.png').show();
	});
}
jQuery.fn.comment = function(fid) {
	var form = $('#'+fid);
	$.ajax({
		type: form.attr('method'),
		url: form.attr('action'),
		data: form.serialize(), 
		dataType: 'json',
		success: function(data) {
			form.parent().parent().find('td:eq(9) label').text(data.updatedby);
			form.parent().parent().find('td:eq(10) label').text(data.updated);
		}
	});
	return false;
}
jQuery.fn.title = function(tid) {
	var form = $('#'+tid);
	$.ajax({
		type: form.attr('method'),
		url: form.attr('action'),
		data: form.serialize(), 
		dataType: 'json',
		success: function(data) {
			form.closest('tr').find('td:eq(9) label').text(data.updatedby);
			form.closest('tr').find('td:eq(10) label').text(data.updated);
		}
	});
	return false;
}
/* $('.table8 form input[name="title"]').bind('change', function(event) {
	//console.log(event);
	event.preventDefault();
	var form = $(event.target).closest('form');
	if (event.keyCode == 13) { return false; }
	// alert(this.form.id.value);
	// var form = this.form;
	// var id = $(this.form.id).val();
	// console.log($(this.form).attr('id')+','+$(this.form.id).val());return false;
	$.ajax({
		type: $(form).attr('method'),
		url: $(form).attr('action'),
		data: $(form).serialize(), 
		dataType: 'json',
		success: function(data) {
			$(form).closest('tr').find('td:eq(9) label').text(data.updatedby);
			$(form).closest('tr').find('td:eq(10) label').text(data.updated);
		}
	});
	return false;
}); */
$('tr.rowTitle th:gt(0)').not(':last').each(function() {
	var t1 = $(this);
	var t2 = t1.text();
	t2 = jQuery.trim(t2);
	var t3 = t2 + ' desc';
	var t = '<div class="sortBox"><a href="javascript:void(0);" ' +
		'onClick="$(this).sortBy(\''+t2+'\')" class="upSort">' +
		'<img src="images/up-arrow.png" border="0" width="11" height="5" alt="up">' +
		'</a>' +
		'<a href="javascript:void(0);" ' +
		'onClick="$(this).sortBy(\''+t3+'\')" class="downSort">' +
		'<img src="images/down-arrow.png" border="0" width="11" height="5" alt="down">' +
		'</a></div>';
	$(t).appendTo(t1);
});
$("tr td a.delete").click(function(event) {
	event.preventDefault();
	var $a = $(this);
	var name = $a.parent().parent().parent().find('td:eq(4)').text();
	name = jQuery.trim(name);
	if( confirm('Are you sure to delete the file [' + name + ']?')) {
		var t = $(this).attr('href');
		$.get(url+t, function(data) {
			alert('Successfully delete the file [' + name + '].');
			$a.parent().parent().parent().fadeOut(200);
		});
	}
	return false;
});
var tipTitle = 'Double Click to edit this column -- [';
$('.table8 th:eq(1) label, .table8 th:eq(6) label').bind('mouseover', function(event) {
  $(this).css('text-decoration','underline');
  $('#livetip').css({top: event.pageY+2,left: event.pageX+2}).html('<div>' + tipTitle + $(this).text() + '], move mouse out to finish edit.</div>').show();
}).bind('mouseout', function(event) {
  $(this).css('text-decoration','none');
  $('#livetip').hide();
});
</script>
<?php
  }

  function get_form() {
  ?>
<div>
  <form method="post" action="javascript:void();" id="form_uploader">
    <fieldset>
    <div class="marB7px"> <span id="span_location"  style="background:yellow;border:3px red solid">
      <label>Where (default is Member's page):</label>
      <input type="checkbox" name="location" value="P" />
      <label>Public</label>
      &nbsp;
      <input type="checkbox" name="location" id="location_m" value="M" checked="checked" />
      <label>Members</label>
      </span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <span id="span_division" style="background:yellow;border:3px red solid">
      <label>Division: </label>
      <input type="checkbox" name="division" value="A" checked="checked" />
      <label>A</label>
      &nbsp;
      <input type="checkbox" name="division" value="B" />
      <label>B</label>
      &nbsp;
      <input type="checkbox" name="division" value="C" />
      <label>C</label>
      </span> (
      <label><span style="color:#FF0000">*Required Field.</span></label>
      ) </div>
    <div>
      <label for="title">Title:&nbsp;&nbsp;(<span style="color:#FF0000">*Required Field. This can be adjusted from the <strong>List & Update</strong> tab.</span>)</label>
    </div>
    <div>
      <input type="text" id="title" name="title" size="100"  class="inputTitle"/>
    </div>
    <div>
      <label for="comment">Comment:</label>
    </div>
    <div>
      <textarea name="comment" id="comment" class="textPlupload note1" rows="4" cols="80"></textarea>
    </div>
    <div id="uploader" class="uploaderStyle">
      <p>You browser doesn't have Flash, Silverlight, Gears, BrowserPlus or HTML5 support.</p>
    </div>
    </fieldset>
  </form>
</div>
<script type="text/javascript" language="javascript">
//$('#title').focus();
$(function() {
	$('#location_m').change(function() {
		//$('#span_division').children('input').each(function(){ $(this).removeAttr('checked');	});
		if ($('#location_m').is(':checked')) {
			//$('#span_division').children('input:first').attr('checked','checked');
			$('#span_division').css({background:"yellow", border:"3px red solid"}).show();
		}
		else
			$('#span_division').hide();
	});
	/* alert($("#uploader").find("a.plupload_start").attr('class'));return false;
	$("#uploader a.plupload_start").live('click', function(e){
		alert('cccccccccccc');
		e.preventDefault();
		return false;
	});*/
	$("#uploader").pluploadQueue({
		runtimes : 'gears,flash,silverlight,browserplus,html5',
		url : 'upload_download_new.php',
		max_file_size : '10mb',
		chunk_size : '1mb',
		unique_names : true,
		// Resize images on clientside if we can
		resize : {width : 320, height : 240, quality : 90},
		multipart_params: { 'title':'','comment':'','location':'','division':'' },
		// Specify what files to browse for
		filters : [
			{title : "PDF files", extensions : "pdf"},
			{title : "HTML files", extensions : "html,htm,xml"},
			{title : "Plain files", extensions : "txt,bak"},
			{title : "Excel files", extensions : "csv,xls,xlsx"},
			{title : "Image files", extensions : "jpg,gif,png"}
		],
		// Flash settings
		flash_swf_url : 'plupload/js/plupload.flash.swf',
		// Silverlight settings
		silverlight_xap_url : 'plupload/js/plupload.silverlight.xap',
		init : {
		  //StateChanged : function(up) {	return false; },
		  // FilesAdded : function(up, files) // Fires while the user selects files to upload.
		  BeforeUpload : function(up, files) { // Fires when just before a file is uploaded. This event enables you to override settings on the uploader instance before the file is uploaded.
		  /// <field name="state" type="Number">Current state of the total uploading progress. This one can either be plupload.STARTED or plupload.STOPPED. These states are controlled by the stop/start methods. The default value is STOPPED.</field>
		  	//up.state = plupload.STOPPED;	//up.stop(); 	  
		  	if ($("#form_uploader input[name='location']:checked").size() == 0) {
				alert("Please assign at least 1 Location checkbox.");
				up.stop(); return false;
			}
			if ($("#location_m").is(':checked') && $("#form_uploader input[name='division']:checked").size() == 0) {
				alert("Please assign at least 1 Division checkbox.");
				up.stop(); return false;
			}
			var v = $("#title").val();
			if (v=='' || /^\s+$/.test(v)) {
				alert("Please assign title for the download."); //up.state=1: STOPPED
				$('#title').focus();
				up.stop(); return false;
			}
			up.settings.multipart_params.location = $('#form_uploader input[name=location][type=checkbox]:checked').map(function(){ return $(this).val(); }).get().join(",");
			up.settings.multipart_params.division = $("#form_uploader input[name=division][type=checkbox]:checked").map(function(){ return $(this).val(); }).get().join(',');
			up.settings.multipart_params.title = $("#title").val();
			up.settings.multipart_params.comment = $("#comment").val();
		  	//up.state = plupload.STARTED;  up.trigger("StateChanged"); //up.start(); 		  
		  },
		  FileUploaded : function(up, files, info) { // Fires while a file is successfully uploaded.
		  	// alert('in fileuploaded ' + up.state);
		    // the response is returned as a string. deserialize it from JSON to a native object to access its properties:
		  	// console.log(files); alert(files.name); uploaded filename.
			var obj = eval('(' + info.response + ')');
			if(/existed/.test(obj.result)) {
				alert(obj.result);
				up.stop(); return false;
			}
			else if(/nothing/.test(obj.result)) {  //upload.php: line 102.
				alert(obj.result);
				up.stop(); return false;
			}
			else $.post('<?=$this->url;?>',  { file: files.name, action: 'resource' });
		  },
		  // Fires when file chunk is uploaded. Called when a file chunk has finished uploading.
		  //ChunkUploaded: function(up,file,info) {console.log(file); console.log(info); },
		  UploadComplete: function(up,files) { // Fires when all files in a queue are uploaded.
		  	// alert('in uploadcomplete ' + up.state);
			$('#tab2').removeClass('TabbedPanelsTabSelected');
			$('#tab1').addClass('TabbedPanelsTabSelected');
			$('#main2').closest('.TabbedPanelsContent').hide();
			$('#main1').closest('.TabbedPanelsContent').show();
			$('#main1').load('<?=$this->url;?>?page1=1');
		  }
		}
	});
	/* Never come here.
	$('#form_uploader').submit(function(e) { alert('Am I alive? - form_uploader'); e.preventDefault(); });
	$('#uploader').click(function(e) { alert('Am I alive - uploader?'); e.preventDefault(); }); */
});
</script>
<?php
  }

  function update_resources_file($file) {
	$content = file_get_contents(RESOURCES_DIR."$file");
	$content = mysql_real_escape_string($content);
	$size = filesize(RESOURCES_DIR."$file");
	$query = "update myresources_new set size=".$size.", content='".$content."' where file = '".$file."'";
	if (! mysql_query($query, $this->cibp)) {
		die("Could not update myresource at " . __LINE__ . ': '.mysql_error());
	}
  }
  function delete($id) {
	//$query = "update myresources_new set active='N', updatedby='".$_SESSION['pams_user']."' where id=".$id;
	$query = "delete from myresources_new where id=".$id;
    if(! mysql_query($query, $this->cibp)) {
        die ("Could not delete myresources_new id=".$id." at [".__LINE__."]: ".mysql_error());
    }
	return true;
  }
  
  function get_updated_notes($id) {
	$query = "SELECT updated,updatedby,created,createdby FROM myresources_new where active='Y' and id=" . $id;
	$res = mysql_query($query, $this->cibp);
	$row = mysql_fetch_assoc($res);
	mysql_free_result($res);
	return $row;
  }
  function update_notes($id, $notes) {
	$username = isset($_SESSION['pams_user']) ? mysql_real_escape_string($_SESSION['pams_user']) : '';
	$query = "update myresources_new set comment='".$notes."', updatedby='".$username."' where id=".$id;
	mysql_query($query, $this->cibp) or die("Can't update myresources_new column at line ".__LINE__.': '.mysql_error());
	$row = $this->get_updated_notes($id);
	$ary = array();
	$ary['id'] = $id;
	$ary['updated'] = $row['updated'];
	$ary['updatedby'] = $row['updatedby'];
	$ary['created'] = $row['created'];
	$ary['createdby'] = $row['createdby'];
	$encodedArray = array_map("utf8_encode", $ary);
	return $encodedArray;
  }
  function update_title($id, $title) {
	$username = isset($_SESSION['pams_user']) ? mysql_real_escape_string($_SESSION['pams_user']) : '';
	$query = "update myresources_new set title='".$title."', updatedby='".$username."' where id=".$id;
	mysql_query($query, $this->cibp) or die("Can't update myresources_new column at line ".__LINE__.': '.mysql_error());
	$row = $this->get_updated_notes($id);
	$ary = array();
	$ary['id'] = $id;
	$ary['updated'] = $row['updated'];
	$ary['updatedby'] = $row['updatedby'];
	$ary['created'] = $row['created'];
	$ary['createdby'] = $row['createdby'];
	$encodedArray = array_map("utf8_encode", $ary);
	return $encodedArray;
  }

  function search1() {
?>
<form action="<?=$this->url;?>" id="form1" method="post">
  <fieldset>
  <legend id="legend1"><span>Downloads Files Search:</span></legend>
  <table border="0" cellspacing="0" cellpadding="4" class="tab_downloads">
    <tr>
      <td align="right"><label for="title2">Title:</label></td>
      <td><input class="formField" name="title" type="text" id="title2"></td>
      <td align="right"><label for="file">File:</label></td>
      <td><input class="formField" name="file" type="text" id="file" /></td>
      <td rowspan="4"><div class="inputBtn">
          <div>
            <input type="submit" name="search_form1" value="" class="tabSearch">
            <span id="msg1" style="display: none;"><img name="search_download" src="images/spinner.gif" width="16" height="16" alt="search Remittance..." border="0" style="margin-top: 26px; margin-left: 26px;"></span></div>
          <input type="reset" name="reset" value="" class="tabReset" title="Reset">
        </div></td>
    </tr>
    <tr>
      <!--     <td align="right"><label for="type">Type:</label></td>
      <td><select class="formPopup" id="type" name="type">
          < ?php $this->get_type(); ?>
        </select></td>-->
      <td align="right"><label>Location:</label></td>
      <td><input type="radio" name="loc" value="P" />
        <label>Public</label>
        &nbsp;
        <input type="radio" name="loc" value="M" />
        <label>Member</label>
        &nbsp;
        <input type="radio" name="loc" value="A" checked="checked" />
        <label>All</label></td>
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
      <td align="right"><label for="date1">Created Date from:</label></td>
      <td><a href="javascript: fPopCalendar('date1')">
        <input class="formField" type="text" name="date1" id="date1"  value="YYYY-MM-DD" onFocus="this.select();" size="28" />
        <img src="images/cal2.jpg" width="14" height="14" alt="from date" border="0"></a></td>
      <td align="right"><label for="date2">Created Date to:</label></td>
      <td><a href="javascript: fPopCalendar('date2')">
        <input class="formField" name="date2" id="date2" type="text" value="YYYY-MM-DD" onFocus="this.select();" size="28" />
        <img src="images/cal2.jpg" width="14" height="14" alt="to date" border="0" /></a></td>
    </tr>
    <tr>
      <td align="right"><label for="date3">Updated Date from:</label></td>
      <td><a href="javascript: fPopCalendar('date3')">
        <input class="formField" type="text" name="date3" id="date3"  value="YYYY-MM-DD" onFocus="this.select();" size="28" />
        <img src="images/cal2.jpg" width="14" height="14" alt="from date" border="0"></a></td>
      <td align="right"><label for="date4">Updated Date to:</label></td>
      <td><a href="javascript: fPopCalendar('date4')">
        <input class="formField" name="date4" id="date4" type="text" value="YYYY-MM-DD" onFocus="this.select();" size="28" />
        <img src="images/cal2.jpg" width="14" height="14" alt="to date" border="0" /></a></td>
    </tr>
  </table>
  </fieldset>
</form>
<script language="javascript" type="text/javascript">
$('#form1').submit(function(e) {
   e.preventDefault();
   var dn =  $('#form1 input[name=division][type=checkbox]:checked').map(function(){ return $(this).val(); }).get().join(","); 
   var data = $('#form1').serialize() + '&dn='+dn+'&search_form1=1';
	$.ajax({
		type: $(this).attr('method'),
		url: $(this).attr('action'),
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
</script>
<?php
  }
  
  function parse1()
  {
    $h['m1'] = isset($_POST['title']) ? trim($_POST['title']):'';
    $h['m2'] = isset($_POST['file'])  ? trim($_POST['file']):'';
    $h['m3'] = isset($_POST['type']) ? trim($_POST['type']):'';
    $h['m4'] = isset($_POST['date1']) ? $this->get_date(trim($_POST['date1'])):'';
    $h['m5'] = isset($_POST['date2']) ? $this->get_date(trim($_POST['date2'])):'';
    $h['m6'] = isset($_POST['date3']) ? $this->get_date(trim($_POST['date3'])):'';
    $h['m7'] = isset($_POST['date4']) ? $this->get_date(trim($_POST['date4'])):'';
	$h['m8'] = isset($_POST['loc']) ? $_POST['loc'] : '';

    $sql = $this->sql;
    if($h['m1']) {
      $sql .= " and title like '%" . $h['m1'] . "%' ";
    }
    if($h['m2']) {
      $sql .= " and file like '" . $h['m2'] . "%' ";
    }
    if($h['m3']) {
      $sql .= " and type = '" . $h['m3'] . "' ";
    }
    if($h['m4'] && $h['m5']) {
      $sql.=" and (date(created) between '". $h['m4']. "' and '".$h['m5']."') ";
    }
    else if($h['m4']) {
      $sql .= " and date(created) = '" . $h['m4'] . "'";
    }
    else if($h['m5']) {
      $sql .= " and date(created) = '" .  $h['m5'] . "'";
    }
    if($h['m6'] && $h['m7']) {
      $sql.=" and (date(updated) between '". $h['m6']. "' and '".$h['m7']."') ";
    }
    else if($h['m6']) {
      $sql .= " and date(updated) = '" . $h['m6'] . "'";
    }
    else if($h['m7']) {
      $sql .= " and date(updated) = '" .  $h['m7'] . "'";
    }
    if($h['m8']!='A') {
      $sql .= " and location = '" . $h['m8'] . "' ";
    }
    if(isset($_POST['dn']) && !empty($_POST['dn'])) {
		$t = preg_replace("/,,/", ",", $_POST['dn']);
		$t = preg_replace("/,$/", "", $t);
		$t = preg_replace("/,/", "','", $t);
		$sql .= " and division in ('" . $t . "') ";
	}
    $_SESSION['download_sql'] = $sql;
    $total_rows = $this->get_total_rows($_SESSION['download_sql']);
    $_SESSION['download_rows'] = $total_rows < 1 ? 1 : $total_rows;
  }

  function get_type() {
	$query = "SELECT distinct(type) FROM myresources_new where active='Y' order by type";
	echo "<option value=''> ----- Select ---- </option>\n";
	$result = mysql_query($query, $this->cibp);
    while ($res = mysql_fetch_row($result)) {
      echo "\t<option value='" . $res[0] . "'>".htmlspecialchars($res[0])."</option>\n";
    }
    mysql_free_result($result);
  }

  function get_ftype($type)
  {
	$ftype = substr($type, strpos($type, '/')+1);
	$ftype = strtolower($ftype);
	$ret = '';
	switch($ftype) {
	case 'pdf':
		$ret = '<img src="images/icons/icon-pdf.png" border="0" width="16" height="16" title="PDF" />';
		break;
	case 'html':
		$ret = '<img src="images/icons/icon-htm.png" border="0" width="16" height="16" title="HTML" />';
		break;
	case 'htm':
		$ret = '<img src="images/icons/icon-html.png" border="0" width="16" height="16" title="HTM" />';
		break;
	case 'xls':
		$ret = '<img src="images/icons/icon-xls.png" border="0" width="16" height="16" title="XLS" />';
		break;
	case 'xlsx':
		$ret = '<img src="images/icons/icon-xlsx.png" border="0" width="16" height="16" title="XLSX" />';
		break;
	case 'csv':
		$ret = '<img src="images/icons/icon-csv.png" border="0" width="16" height="16" title="CSV" />';
		break;
	case 'txt':
		$ret = '<img src="images/icons/icon-txt.png" border="0" width="16" height="16" title="TXT" />';
		break;
	case 'xml':
		$ret = '<img src="images/icons/icon-xml.png" border="0" width="16" height="16" title="XML" />';
		break;
	default:
		$ret = '<img src="images/icons/icon-bak.png" border="0" width="16" height="16" title="BAK" />';
	}
	return $ret;
  }
}

/////////////////////////////////////////
$uploader = new Plupload();
if(isset($_REQUEST['action'])) {
	switch($_REQUEST['action']) {
	case 'add':
		$uploader->add();
		break;
	case 'edit':
		$ret = $uploader->edit();
		echo json_encode($ret);
		break;
	case 'delete':
		if($uploader->delete($_GET['id'])) {
			echo "Successfully delete the record (id=".$_GET['id'].").";
		}
		break;		
	case 'resource':
		if (isset($_POST['file'])) {
			$uploader->update_resources_file(mysql_real_escape_string($_POST['file']));
		}
		break;
	default:
		echo "Error, should not come here: "; print_r($_REQUEST);
		break;
	}
}
elseif(isset($_POST['notes']) && isset($_POST['id'])) {
	$ret = $uploader->update_notes($_POST['id'], mysql_real_escape_string(trim($_POST['notes'])));
	echo json_encode($ret);
}
elseif(isset($_POST['title']) && isset($_POST['id'])) {
	$ret = $uploader->update_title($_POST['id'], mysql_real_escape_string(trim($_POST['title'])));
	echo json_encode($ret);
}
elseif (isset($_POST['name'])) {
	print_r($_REQUEST);
}
elseif(isset($_GET['search1'])) {
	$uploader->search1();
}
else if(isset($_POST['search_form1'])) {
	$uploader->parse1();
	$uploader->get_list();
}
else if (isset($_GET['page1']) && isset($_GET['sort'])) {
	$uploader->get_list();
}
else if (isset($_GET['page1'])) {
	$uploader->get_list();
}
else {
	$uploader->init();
	//$uploader->get_form();
}
?>

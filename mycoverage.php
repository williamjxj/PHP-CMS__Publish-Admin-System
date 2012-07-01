<?php
/* try{} throw{}
  error_reporting(E_ALL);
  ini_set("display_errors", 1);
 * LEAD: list, edit, add, delete
 * SPS: search, pagination, sortable.
 spend 1 hour: h.content is null. caused by '., json_encode(utf8), array_map("utf8_encode", $content);
 */
session_start();
if(! ((isset($_SESSION['userid']) && $_SESSION['userid'])) ) {
	echo "<script>if(self.opener){self.opener.location.href='login.php';} else{window.location.href='login.php';}</script>"; exit;
}
include_once('config.php');
include_once("pams1.php");

///////////////////////////
class MyCoverage  extends PamsBase
{
  var $url, $cibp, $pams, $div1, $div2, $div3, $sql;
  function __construct() {
	$this->url = $_SERVER['PHP_SELF'];
	$this->connect_cibp();
	//$this->connect_pams();
    $this->div1 = 'main1';
    $this->div2 = 'main2';
    $this->div3 = 'main3';
	$this->sql = "select * from mycoverage where active='Y' order by id ";
  }
  function connect_cibp() {
	$db = mysql_pconnect(HOST, USER, PASS) or die(mysql_error());
	mysql_select_db(DB_NAME, $db);
	$this->cibp = $db;
  }
  function connect_pams() {
	$db = mysql_pconnect(HOST, USER1, PASS1) or die(mysql_error());
	mysql_select_db(DB_PAMS, $db);
	$this->pams = $db;
  }

  function init() {
      $_SESSION['coverage_sql'] = $this->sql;
    $total_rows = $this->get_total_rows($_SESSION['coverage_sql']);
    // echo "total rows is: " . $total_rows . "<br/>\n";
    $_SESSION['coverage_rows'] = $total_rows < 1 ? 1 : $total_rows;

?>
<link type="text/css" rel="stylesheet" href="css/style.css" />
<link type="text/css" rel="stylesheet" href="css/my.css"  />
<!--<link type="text/css" rel="stylesheet" href="css/programs.css" />-->
<style type="text/css" media="all">@import "css/c-css.php";</style>
<script language="javascript" type="text/javascript" src="js/jquery-1.5.1.min.js"></script>
<script language="javascript" type="text/javascript" src="tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script src="SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
<link href="SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css">
<script src="SpryAssets/SpryValidationTextarea.js" type="text/javascript"></script>
<link href="SpryAssets/SpryValidationTextarea.css" rel="stylesheet" type="text/css">
<script language="javascript" type="text/javascript">
$(document).ready(function() {
 // to make object and function persistance, by register them as jQuery object and function.
 jQuery.init_tinyMCE = function(WYSIWYG) {
  return tinyMCE.init({
	mode : "exact",
	elements: WYSIWYG,
	theme : "advanced",
	plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
	// Theme options
	theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
	theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
	theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
	theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_statusbar_location : "bottom",
	theme_advanced_resizing : true,
	content_css : "css/content.css",
	template_external_list_url : "tinymce/examples/lists/template_list.js",
	external_link_list_url : "tinymce/examples/lists/link_list.js",
	external_image_list_url : "tinymce/examples/lists/image_list.js",
	media_external_list_url : "tinymce/examples/lists/media_list.js"
  });
 };
 jQuery.fn.validate_form = function(form) {
	if(form.notes.value=='' || /^\s+$/.test(form.notes.value)) {
		alert("Please input a notes");
		form.notes.focus();
		return false;
	}
	var ed = tinyMCE.get('elm2');
	if(ed) {
		var c = ed.getContent();
		if(c=='' || /^\s+$/.test(c)) {
			alert("Please input content");
			return false;
		}
	}
	return true;
 };
 // Where to init tinyMCE? should be here, if put inside 'change' event, error: undefined tinyMCE.get('content');
 $.init_tinyMCE('elm2');
 // Safe way to put event 'change' here immediately after init_tinyMCE, so tinyMCE.get('elm2') works.
 $('#select_file').bind('change', function(e) {
	e.preventDefault();
	if (!$('#f_form').is(':visible')) {
		$('#f_form').show('fast');
	}
	var ed = tinyMCE.get('elm2');
	// if($('#elm2').length) { alert('exists'); console.log($('#elm2'));}
	if(ed) { ed.setProgressState(1)};
	$.ajax({
		type: 'get',
		url: '<?=$this->url;?>',
		data: 'js_list=1&mcid='+$(this).val(),
		dataType: 'json',
		success: function(data){
			if(ed) {
				ed.setProgressState(0);
				ed.setContent(data.content);
			}
			else {
				tinyMCE.get('elm2').setContent(data.content);
			}
			$('#notes').val(data.notes);
			$('#id').val(data.id);
			$('#input_file').val(data.file).show('fast').attr('readonly',true);
		}
	})
 });
});
</script>
<script src="SpryAssets/SpryTabbedPanels.js" type="text/javascript"></script>
<link href="SpryAssets/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<link href="css/cwcalendar.css" rel="stylesheet" type="text/css" />
<script language="JavaScript" src="js/calendar.js" type="text/javascript"></script>
<div id="programs">
<div id="TabbedPanels1" class="TabbedPanels">
  <ul class="TabbedPanelsTabGroup">
    <li class="TabbedPanelsTab" tabindex="0" id="tab1">List</li>
    <li class="TabbedPanelsTab" tabindex="1" id="tab2">Update</li>
    <li class="TabbedPanelsTab" tabindex="2" id="tab3">Add</li>
    <li class="TabbedPanelsTab textcolorGrey move2" tabindex="3" id="tab8">&nbsp;&nbsp;&nbsp;Hide Left Menu</li>
    <li onclick="window.open('help.html', 'help','height=260,width=600,scrollbars=1,resizable=1');">&nbsp;&nbsp;<img src="images/help.png" border="0" width="16px" height="16" alt="Help" class="iconHelp"/></li>
  </ul>
  <div class="TabbedPanelsContentGroup tabPanelWidth_mc">
    <div class="TabbedPanelsContent">
      <div>
        <div id="title1" class="formLegend">Click to Search MyCoverage List</div>
        <div id="search1"></div>
      </div>
      <div id="main1">
        <?php $this->get_list();?>
      </div>
    </div>
    <div class="TabbedPanelsContent">
      <div id="main2">
        <? $this->get_form_2();?>
      </div>
    </div>
    <div class="TabbedPanelsContent">
      <div id="main3"> 
        <?=$this->get_form_3();?>
      </div>
    </div>
  </div>
</div>
</div>
<script language="javascript" type="text/javascript">
$(function() {
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
	$('#search1').load('<?=$this->url;?>?search1=1').fadeIn(200);
  });
  $('#tab3').click(function() {
  	if($('#elm3').length) {
		var ed=tinyMCE.get('elm3');
		if(!ed) { jQuery.init_tinyMCE('elm3');} //typeof(ed)==undefined?
	}
	else {
		alert('where is elm3? can not initialize.');
	}
  });	
});
</script>
<?php
 }
 
 function get_form_2() {
 ?>
<select id="select_file" style="width:300px;max-width:450px;" onsubmit="return $(this).validate_form(this);">  
  <option value=""> ---- Please select a file to edit ---- </option>
  <?php $this->get_select_list();?>
</select><label for="input_file">&nbsp;&nbsp;File:</label><input type="text" id="input_file" size="90" style="display:none;" />
<form id="f_form" method="post" action="<?=$this->url;?>" target="iframe" style="display:none;">
  <label for="notes"> Notes:</label>
  <br />
  <textarea rows="10" style="width:100%" name="notes" id="notes"></textarea>
  <br />
  <label for="elm2"> HTML:</label>
  <br />
  <textarea name="elm2" id="elm2" style="width: 100%; height: 80%"></textarea>
  <div>
    <!-- Some integration calls -->
    <a href="javascript:;" onmousedown="tinyMCE.get('elm2').show();">[Show]</a> <a href="javascript:;" onmousedown="tinyMCE.get('elm2').hide();">[Hide]</a> <a href="javascript:;" onmousedown="tinyMCE.get('elm2').execCommand('Bold');">[Bold]</a> <a href="javascript:;" onmousedown="alert(tinyMCE.get('elm2').getContent());">[Get contents]</a> <a href="javascript:;" onmousedown="alert(tinyMCE.get('elm2').selection.getContent());">[Get selected HTML]</a> <a href="javascript:;" onmousedown="alert(tinyMCE.get('elm2').selection.getContent({format : 'text'}));">[Get selected text]</a> <a href="javascript:;" onmousedown="alert(tinyMCE.get('elm2').selection.getNode().nodeName);">[Get selected element]</a> <a href="javascript:;" onmousedown="tinyMCE.execCommand('mceInsertContent',false,'<b>Hello world!!</b>');">[Insert HTML]</a> <a href="javascript:;" onmousedown="tinyMCE.execCommand('mceReplaceContent',false,'<b>{$selection}</b>');">[Replace selection]</a> </div>
  <br />
  <input type="submit" value="Save" name="submit" class="save" />
  <input type="reset" value="Reset" class="reset" />
  <input type="button" value="Cancel" class="cancel" />
  <input type="hidden" id="id" name="id" />
</form>
<iframe id="iframe" name="iframe" src="about:blank" frameborder="0" style="width:0;height:0;border:0px solid #fff;"></iframe>
<?
 }

 function get_form_3() {
 ?>
<form id="f_form_3" method="post" action="<?=$this->url;?>" target="iframe3">
  <label for="input_title_3"> Title: </label>
  &nbsp;<span id="sprytextfield1">
  <input type="text" id="input_title_3" name="input_title_3" size="90" class="inputTitle" />
  <span class="textfieldRequiredMsg">A value is required.</span></span><span style="display:none">
  <label for="input_file_3">&nbsp;&nbsp;File: </label>
  <input type="text" id="input_file_3" name="input_file_3" size="90" />
  </span> <br />
  <label for="notes"> Notes:</label>
  <br />
  <span id="sprytextarea1">
  <textarea rows="10" class="note1" name="input_notes_3" id="input_notes_3"></textarea>
  <span class="textareaRequiredMsg">A value is required.</span></span><br />
  <label for="elm3"> HTML:</label>
  <br />
  <textarea name="elm3" id="elm3" style="width: 100%; height: 80%"></textarea>
  <div> <a href="javascript:;" onMouseDown="tinyMCE.get('elm3').show();">[Show]</a> <a href="javascript:;" onMouseDown="tinyMCE.get('elm3').hide();">[Hide]</a> <a href="javascript:;" onMouseDown="tinyMCE.get('elm3').execCommand('Bold');">[Bold]</a> <a href="javascript:;" onMouseDown="alert(tinyMCE.get('elm3').getContent());">[Get contents]</a> <a href="javascript:;" onMouseDown="alert(tinyMCE.get('elm3').selection.getContent());">[Get selected HTML]</a> <a href="javascript:;" onMouseDown="alert(tinyMCE.get('elm3').selection.getContent({format : 'text'}));">[Get selected text]</a> <a href="javascript:;" onMouseDown="alert(tinyMCE.get('elm3').selection.getNode().nodeName);">[Get selected element]</a> <a href="javascript:;" onMouseDown="tinyMCE.execCommand('mceInsertContent',false,'<b>Hello world!!</b>');">[Insert HTML]</a> <a href="javascript:;" onMouseDown="tinyMCE.execCommand('mceReplaceContent',false,'<b>{$selection}</b>');">[Replace selection]</a> </div>
  <br />
  <input type="submit" value="Save" name="submit" class="save" />
  <input type="reset" value="Reset" class="reset" />
</form>
<iframe id="iframe3" name="iframe3" src="about:blank" frameborder="0" style="width:0;height:0;border:0px solid #fff;"></iframe>
<script language="javascript" type="text/javascript">
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var sprytextarea1 = new Spry.Widget.ValidationTextarea("sprytextarea1");
$('#input_title_3').change(function() {
	var t = $('#input_title_3').val();
	t = t.replace(/\s+/g, '_').replace(/\'/g,'').replace(/\"/g,'').replace(/,/g,'').replace(/\./g,'') + '.html';
	$('#input_file_3').val(t.toLowerCase());
	$('#input_file_3').parent().show('fast');
});
</script>
<?
  }

  function get_select_list() {
	$query = "SELECT id, title FROM mycoverage where active='Y'";
	$result = mysql_query($query, $this->cibp);
    while ($res = mysql_fetch_row($result)) {
      echo "\t<option value=\"" . $res[0] . "\">$res[1]</option>\n";
    }
    mysql_free_result($result);
  }

  //Regarding encoding issues, if you make sure the PHP files containing your strings are encoded in UTF-8, you shouldn't need to call utf8_encode.
  function get_table_file($mcid) {
  	$ary = array();	
  	$query = "SELECT FILE, NOTES FROM mycoverage where id=".$mcid;
	$res = mysql_query($query, $this->cibp);
	$row = mysql_fetch_assoc($res);
	$tmp = MYCOVERAGE_DIR . $row['FILE']; //echo $tmp;
	$fp      = fopen($tmp, 'r');
	$content = fread($fp, filesize($tmp)); 
	$ary['content'] = $content; 
	fclose($fp);
	mysql_free_result($res);
	$ary['id'] = $mcid;
	$ary['file'] = $row['FILE'];
	$ary['notes'] = $row['NOTES'];
	$ary['content'] = $content; // "'\"&<>)(*&^%$#@!~}{:\"? ><,./';l[]";
	$encodedArray = array_map("utf8_encode", $ary);
	return $encodedArray;
  }
  function get_table($id) {
	$query = "SELECT * FROM mycoverage where active='Y' and id=" . $id;
	$res = mysql_query($query, $this->cibp);
	$row = mysql_fetch_assoc($res);
	mysql_free_result($res);
	return $row;
  }
  function get_filename($id) {
	$query = "SELECT file FROM mycoverage where id=" . $id;
	$res = mysql_query($query, $this->cibp);
	$row = mysql_fetch_assoc($res);
	mysql_free_result($res);
	return $row['file'];
  }
  function  edit()
  {
  	$content = trim($_POST['elm2']);
  	$notes = mysql_real_escape_string(trim($_POST['notes']));
	$id = $_POST['id'];
	$query = "update mycoverage set notes='".$notes."', updatedby='".$_SESSION['pams_user']."' where id=".$id;
    if(! mysql_query($query, $this->cibp)) {
       die ("Could not update mycoverage at [".__LINE__.']: '.mysql_error());
    }
	$file = $this->get_filename($id);
  	$tmp = MYCOVERAGE_DIR . $file;
	$fp      = fopen($tmp, 'w') or die ('array_map');
	if(! fwrite($fp, $content)) {
		die ('not tinyMCE.getContent, it is json_encode.');
	};
	fclose($fp);
	$row = $this->get_table($id);
	$ary = array();
	$ary['id'] = $id;
	$ary['notes'] = $row['notes'];
	$ary['content'] = $content;

	$encodedArray = array_map("utf8_encode", $ary);
	return $encodedArray;
  }
  function add() {
	$title = mysql_real_escape_string(trim($_POST['input_title_3']));
	$file = mysql_real_escape_string(trim($_POST['input_file_3']));
	$notes = mysql_real_escape_string(trim($_POST['input_notes_3']));
	$content = trim($_POST['elm3']);
	$query = "insert into mycoverage (title, file, notes, createdby, created)
		values('".$title."', '".$file."', '".$notes."', '" . $_SESSION['pams_user'] . "', now())";
	if(! mysql_query($query, $this->cibp)) {
		die ("Could not add mycoverage at [".__LINE__.']: '.mysql_error());
	}
	$id = mysql_insert_id();
  	$tmp = MYCOVERAGE_DIR . $file;
	$fp      = fopen($tmp, 'w') or die ('array_map');
	if(! fwrite($fp, $content)) {
		die ('not tinyMCE.getContent, it is json_encode.');
	};
	fclose($fp);
	$row = $this->get_table($id);
	$ary = array();
	$ary['id'] = $id;
	$ary['title'] = $row['title'];
	$ary['notes'] = $row['notes'];
	$ary['content'] = $content;
	$encodedArray = array_map("utf8_encode", $ary);
	return $encodedArray;
  }
  function delete($id) {
	$query = "update mycoverage set active='N', updatedby='".$_SESSION['pams_user']."' where id=".$id;
    if(! mysql_query($query, $this->cibp)) {
        die ("Could not delete mycoverage id=".$id." at [".__LINE__."]: ".mysql_error());
    }
	return true;
  }
  
  function get_list() 
  {
    $page = isset($_GET['page1']) ? $_GET['page1'] : 1;
    $row_no = ((int)$page-1)*ROWS_PER_PAGE;

    if (isset($_SESSION['coverage_sql']) && $_SESSION['coverage_sql']) $query = $_SESSION['coverage_sql'];
    else $query = $this->sql;

    if (preg_match("/limit /i", $query)) {
      $query = preg_replace("/limit.*$/i", '', $query);
    }
	if (! preg_match("/order by/i", $query)) {
		$query .=  " order by ID desc ";
	}
	//  SELECT * FROM `myresources` WHERE 1 order by id desc limit 1,10; just replace 'order by id desc', keep 'limit 0,10' unchanged.
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
	$_SESSION['coverage_sql'] = $query;

    $total_pages = ceil($_SESSION['coverage_rows']/ROWS_PER_PAGE);
    if ( $page > $total_pages ) $page = $total_pages;

    $sp_flag = $total_pages==1 ? true : false;
    $url = $this->url.'?page1='.$page;
    $url1 = $this->url.'?page1';
    $divid = $this->div1;

?>
<table class="table8" align="center">
  <caption class="cp1">
  MyCoverage List &nbsp;
  <?php if (!$sp_flag) echo "[Page ".$page.", Total ".$total_pages." pages/".$_SESSION['coverage_rows']." records]"; ?>
  </caption>
  <thead>
    <?php if (!$sp_flag) { ?>
    <tr>
      <td colspan="9" align="center" class="page"><?=$this->draw($url1,$total_pages,$page,$divid); ?></td>
    </tr>
    <?php } ?>
    <tr class="rowTitle">
      <th class="bdRW">No.</th>
      <th class="bdRW">Title </th>
      <th class="bdRW">File </th>
      <th class="bdRW tabNotes_mc">Notes </th>
      <th class="bdRW">CreatedBy </th>
      <th class="bdRW">Created </th>
      <th class="bdRW">UpdatedBy </th>
      <th class="bdRW">Updated</th>
      <th class="tabOptions_mc">Options</th>
    </tr>
  </thead>
  <tbody>
    <?php
  $result = mysql_query($query);
  // if (mysql_num_rows($result) == 0) {  return false; }
  while ($row = mysql_fetch_array($result)) {
    $bgcolor = $row_no%2==1 ? 'odd': 'even';
	$updated = '&nbsp;';
	if ($row['updated'] && !preg_match("/0000-00-00/", $row['updated'])) {
		$updated = $row['updated'];
	}
	$fname = htmlspecialchars($row['file']);
    ?>
    <tr class="<?=$bgcolor;?>" align="right">
      <td align="left" class="bdR"><label>
        <?=++ $row_no; ?>
        </label></td>
      <td class="bdR"><label>
        <?=htmlspecialchars($row['title']);?>
        </label></td>
      <td class="bdR"><label><a href="<?=MYCOVERAGE_DIR.$row['file'];?>" title="<?=$fname;?>" target="_blank">
        <?=$fname;?>
        </a></label></td>
      <td class="bdR"><form method="post" action="<?=$this->url;?>" id="fc_<?=$row['id'];?>">
          <textarea name="notes" class="mcNotes" onchange="$(this).comment('fc_<?=$row['id'];?>')"><?=htmlspecialchars($row['notes']);?></textarea>
          <input type="hidden" name="id" value="<?=$row['id'];?>" />
        </form></td>
      <td class="bdR"><label>
        <?=$row['createdby']?htmlspecialchars($row['createdby']):'N/A';?>
        </label></td>
      <td class="bdR"><label>
        <?=$row['created'];?>
        </label></td>
      <td class="bdR"><label>
        <?=$row['updatedby']?htmlspecialchars($row['updatedby']):'N/A';?>
        </label></td>
      <td class="bdR"><label>
        <?=$updated;?>
        </label></td>
      <td class="optbtn"><label><a href="<?=MYCOVERAGE_DIR.$fname;?>" title="Preview <?=$fname;?>" target="_blank" class="preview"></a></label>
        &nbsp;
        <label><a href="javascript:void(0);" onclick="edit_form2('<?=$row['id'];?>')" title="Edit <?=$fname;?>" class="edit"></a></label>
        &nbsp;
        <label><a href="<?=$this->url;?>?id=<?=$row['id'];?>&action=delete" class="delete" title="Delete <?=$fname;?>"></a></label>
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
      <td colspan="9" align="center" class="page"><?=$this->draw($url1,$total_pages,$page,$divid); ?></td>
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
jQuery.fn.comment = function(fid) {
	$.ajax({
		type: $('#'+fid).attr('method'),
		url: $('#'+fid).attr('action'),
		data: $('#'+fid).serialize(), 
		dataType: 'json',
		success: function(data) {
			$('#'+fid).parent().parent().find('td:eq(6) label').text(data.updatedby);
			$('#'+fid).parent().parent().find('td:eq(7) label').text(data.updated);
		}
	});
	return false;	
}
$("tr td a.delete").click(function(event) {
	event.preventDefault();
	var $a = $(this);
	var name = $a.parent().parent().parent().find('td:eq(2)').text();
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
function edit_form2(id) {
	$('#'+id).closest('tr').css({'background-color':'yellow','font-size':'24px'}); //not work?
	$.ajax({
		type: 'get',
		url: '<?=$this->url;?>',
		data: 'js_list=1&mcid='+id,
		dataType: 'json',
		success: function(data){
			$('#tab1').removeClass('TabbedPanelsTabSelected');
			$('#tab2').addClass('TabbedPanelsTabSelected');
			$('#main2').closest('.TabbedPanelsContent').show();
			$('#main1').closest('.TabbedPanelsContent').hide();
			if (!$('#f_form').is(':visible')) $('#f_form').show('fast');
			if (!$('#input_file').is(':visible')) {
				$('#input_file').show('fast');
			}
			$("#select_file option[value='"+data.id+"']").attr('selected', 'selected');
			tinyMCE.get('elm2').setContent(data.content);
			$('#notes').val(data.notes);
			$('#id').val(data.id);
			$('#input_file').val(data.file).attr('readonly',true);			
		}
	})
}
</script>
<?php
 }
 
 function update_notes($id, $notes) {
	$username = isset($_SESSION['pams_user']) ? mysql_real_escape_string($_SESSION['pams_user']) : '';
	$query = "update mycoverage set notes='".$notes."', updatedby='".$username."' where id=".$id;
	mysql_query($query, $this->cibp) or die("Can't update mycoverage column at line ".__LINE__.': '.mysql_error());
	$row = $this->get_table($id);
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
  <legend id="legend1"><span>MyCoverage Files Search:</span></legend>
  <table border="0" cellspacing="0" cellpadding="4"  class="tab_myCoverage">
      <tr>
        <td align="right"><label for="title2">Title:</label></td>
        <td><input class="formField" name="title" type="text" id="title2"></td>
        <td align="right"><label for="file">File:</label></td>
        <td><input class="formField" name="file" type="text" id="file" /></td>
        <td rowspan="3">
        	<div class="inputBtn">
            	<div><input type="submit" name="search_form1" value="" class="tabSearch"><span id="msg1" style="display: none;"><img name="search_download" src="images/spinner.gif" width="16" height="16" alt="search Remittance..." border="0" style="margin-top: 26px; margin-left: 26px;"></span></div>
                <input type="reset" name="reset" value="" class="tabReset" title="Reset">
            </div>
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
   var data = $('#form1').serialize() + '&search_form1=1';
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
      $sql.=" and (created between '". $h['m4']. "' and '".$h['m5']."') ";
    }
    else if($h['m4']) {
      $sql .= " and created = '" . $h['m4'] . "'";
    }
    else if($h['m5']) {
      $sql .= " and created = '" .  $h['m5'] . "'";
    }
    if($h['m6'] && $h['m7']) {
      $sql.=" and (updated between '". $h['m6']. "' and '".$h['m7']."') ";
    }
    else if($h['m6']) {
      $sql .= " and updated = '" . $h['m6'] . "'";
    }
    else if($h['m7']) {
      $sql .= " and updated = '" .  $h['m7'] . "'";
    }

    // $sql = str_replace_once(" and ", " where ", $sql);
    $_SESSION['coverage_sql'] = $sql;
    $total_rows = $this->get_total_rows($_SESSION['coverage_sql']);
    $_SESSION['coverage_rows'] = $total_rows < 1 ? 1 : $total_rows;
  }


}
/////////////////////////////////
$mc = new MyCoverage();

if(isset($_REQUEST['action'])) {
	switch($_REQUEST['action']) {
	case 'add':
		$mc->add();
		break;
	case 'edit':
		$ret = $mc->edit();
		echo json_encode($ret);
		//echo "<pre>";print_r($_POST);echo "</pre>";
		break;
	case 'delete':
		if($mc->delete($_GET['id'])) {
			echo "Successfully delete the record (id=".$_GET['id'].").";
		}
		break;		
	default:
		echo "Error, should not come here: "; print_r($_REQUEST);
		break;
	}
}
elseif(isset($_POST['submit']) && isset($_POST['elm3'])) {
	$ret = $mc->add();
	echo json_encode($ret);
}
elseif(isset($_POST['submit'])) {
	$ret = $mc->edit();
	echo json_encode($ret);
}
elseif(isset($_POST['notes']) && isset($_POST['id'])) {
	$ret = $mc->update_notes($_POST['id'], mysql_real_escape_string(trim($_POST['notes'])));
	echo json_encode($ret);
}
// when select a title, get content and setContent in textarea.
elseif(isset($_GET['js_list'])) {
	$ret = $mc->get_table_file($_GET['mcid']);
	echo json_encode($ret);
}
elseif(isset($_GET['search1'])) {
	$mc->search1();
}
else if(isset($_POST['search_form1'])) {
	$mc->parse1();
	$mc->get_list();
}
else if (isset($_GET['page1']) && isset($_GET['sort'])) {
	$mc->get_list();
}
else if (isset($_GET['page1'])) {
	$mc->get_list();
}

else {
	$mc->init();
	//$mc->get_form_2();
}
?>

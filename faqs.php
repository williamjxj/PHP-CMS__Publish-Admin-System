<?php
/**
 * WYSIWYG: tinyMCE
 * http://api.jquery.com/live/
 * seperate Members and Public to 2 tabs.
 */
session_start();
if(! ((isset($_SESSION['userid']) && $_SESSION['userid'])) ) {
	echo "<script>if(self.opener){self.opener.location.href='login.php';} else{window.location.href='login.php';}</script>"; exit;
}
include_once('config.php');
define('WEIGHT', 0); // header section: WEIGHT=0 to seperate from othes.
$faq = new FAQS();
// $_POST or $_GET.
if(isset($_REQUEST['action'])) {
	switch($_REQUEST['action']) {
	case 'add':
		$new_record = $faq->add(); // $_POST;
		echo json_encode($new_record);
		break;
	case 'edit':
		$edit_record = $faq->edit(); // $_POST;
		echo json_encode($edit_record);
		break;
	case 'delete':
		if($faq->delete($_GET['id'])) {
			echo "Successfully delete the record (id=".$_GET['id'].").";
		}
		break;
	default:
		echo "Error, should not come here: "; print_r($_REQUEST);
		break;
	}
}
elseif(isset($_GET['add_form'])) {
	$faq->get_newform();
}
elseif(isset($_GET['add_list'])) {
	$faq->lists($_GET['add_list']);
}
elseif(isset($_GET['sort_ids'])) {
	$faq->change_sort();
}
elseif(isset($_GET['refresh'])) {
	$faq->lists($_GET['refresh']);
}
else {
	$faq->init();
}
exit;

/////////////////////////////////////////////
class FAQS
{
  var $url, $cibp, $pams, $div1,$div2,$div3,$sql1,$sql2,$sql3,$sql4;
  function __construct() {
	$this->url = $_SERVER['PHP_SELF'];
	$this->cibp = $this->connect_cibp();
	//$this->pams = $this->connect_pams(); not work.
	$this->div1 = "main1";
	$this->div2 = "main2";
	$this->div3 = "main3";
	$this->sql1 = "SELECT * FROM faqs where weight=".WEIGHT." and active='Y' and location='M' ";
	$this->sql2 = "SELECT * FROM faqs where weight!=".WEIGHT." and active='Y' and location='M' order by weight, updated desc, created desc ";
	$this->sql3 = "SELECT * FROM faqs where weight=".WEIGHT." and active='Y' and location='P' ";
	$this->sql4 = "SELECT * FROM faqs where weight!=".WEIGHT." and active='Y' and location='P' order by weight, updated desc, created desc ";
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
?>
<link type="text/css" rel="stylesheet" href="css/style.css" />
<!--<link type="text/css" rel="stylesheet" href="css/my.css"  />
<link type="text/css" rel="stylesheet" href="css/programs.css" />-->
<style type="text/css" media="all">
@import "css/c-css.php";
</style>
<script language="javascript" type="text/javascript" src="js/jquery-1.5.1.min.js"></script>
<link rel="stylesheet" href="jquery-ui-1.8.12.custom/development-bundle/themes/base/jquery.ui.all.css" />
<script language="javascript" type="text/javascript" src="jquery-ui-1.8.12.custom/development-bundle/ui/jquery.ui.core.js"></script>
<script language="javascript" type="text/javascript" src="jquery-ui-1.8.12.custom/development-bundle/ui/jquery.ui.widget.js"></script>
<script language="javascript" type="text/javascript" src="jquery-ui-1.8.12.custom/development-bundle/ui/jquery.ui.mouse.js"></script>
<script language="javascript" type="text/javascript" src="jquery-ui-1.8.12.custom/development-bundle/ui/jquery.ui.sortable.js"></script>
<!--<link rel="stylesheet" href="jquery-ui-1.8.12.custom/development-bundle/demos/demos.css" />-->
<script language="javascript" type="text/javascript" src="tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script src="SpryAssets/SpryTabbedPanels.js" type="text/javascript"></script>
<link href="SpryAssets/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<style type="text/css">
.odd {	background-color: #e5e5e5; }
.even {	background-color: #eee; }
.hover { background-color:#d4ffcf; }
</style>
<div id="programs">
  <div id="TabbedPanels1" class="TabbedPanels">
    <ul class="TabbedPanelsTabGroup">
      <li class="TabbedPanelsTab" tabindex="0" id="tab1">Members List</li>
      <li class="TabbedPanelsTab" tabindex="1" id="tab2">Public List</li>
      <li class="TabbedPanelsTab addNew" tabindex="2" id="tab3">Add</li>
      <li class="TabbedPanelsTab textcolorGrey move2" tabindex="3" id="tab8">&nbsp;&nbsp;&nbsp;Hide Left Menu</li>
      <li onclick="window.open('help.html', 'help','height=260,width=600,scrollbars=1,resizable=1');">&nbsp;&nbsp;<img src="images/help.png" border="0" width="16px" height="16" alt="Help" class="iconHelp" /></li>
    </ul>
    <div class="TabbedPanelsContentGroup">
      <div class="TabbedPanelsContent">
        <div id="main1">
          <?php $this->lists('M');?>
        </div>
      </div>
      <div class="TabbedPanelsContent">
        <div id="main2"></div>
      </div>
      <div class="TabbedPanelsContent">
        <div id="main3"> </div>
      </div>
    </div>
  </div>
</div>
<script language="javascript" type="text/javascript">
var url = '<?=$this->url;?>';
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
$(function() {
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
  $.delete_item = function(id) {
	$.get(url, {'id': id, 'action': 'delete'}, function(data) {
		$('#li_'+id).fadeOut(1000);
		alert(data);
	});
  };
  /*$.trim = function(str) {
	return str.replace(/^\s+|\s+$/g, '').toUpperCase();
  };*/
  $.validate_form = function(form) {
	if($('#location').length) {
	 if(($('#f_new input[id="location"]:checked').map(function(){ return $(this).val();})).get().length==0){
		alert("Please select Members or Public checkbox");
		return false;
	 }
	}
	if(form.title.value==''  || /^\s+$/.test(form.title.value)) {
		alert("Please input title");
		form.title.focus();
		return false;
	}
	/*if(form.content.value=='' || /^\s+$/.test(form.content.value)) {
		alert("Please input content");
		return false;
	}*/
	return true;
  };
  $('#tab2').click(function(e) {
  	e.preventDefault();
	if ($('#main2').length && $('#main2').html().length>0) {
		$('#main2').show('fast'); return false;
	}
	$('#main2').load(url+'?add_list=p');
  });
  $('#tab3').click(function(e) {
  	e.preventDefault();
	if ($('#f_new').length>0) {
		$('#f_new').show('fast'); return false;
	}
	$('#main3').load(url+'?add_form=1');
  });
  $('ol form, #f_new').live('submit', function(event) {
	event.preventDefault();
	var $f = $(this);
	if (! $.validate_form(this)) return false;
	var cid = $f.find('textarea').attr('id');
	var action = 'edit';
	var locs = '';
	// data: 'title='+$('#title').val()+'&tinymce='+tinyMCE.get('elm1').getContent(),
	var data = $(this).serialize()+'&action=edit';
	if (cid=='content') {
		action = 'add';
		locs=$('#f_new input[id="location"]:checked').map(function(){ return $(this).val(); }).get().join(",");
		data = $(this).serialize()+'&action=add&locs='+locs;
	}
	var ed = tinyMCE.get(cid);
	ed.setProgressState(1);
	$.ajax({
		type: "POST",
		url: $(this).attr('action'),
		dataType: 'json',
		data: data,
		success: function(data) {
			ed.setProgressState(0);
			var title = data.title;
			title = jQuery.trim(title);
			var content = data.content;
			var created = data.created;
			var createdby = data.createdby;
			var updated = data.updated;
			var updatedby = data.updatedby;
			var id = data.id;
			var mdiv = 'm_' + id;
			if(action=='edit') {
				var divid = '#div_'+id;
				$(divid).find('.title').html(title);
				$(divid).find('blockquote[class="box"]').html(content);
				$(divid).show('fast');
				$f.hide();
				var succMessage = 'Successfully Update for ['+title+'], created by: [ '+createdby+' ], on [ '+created+' ]';
				if(/0000-00-00/.test(updated)) {
					succMessage += '.';
				}
				else {
					succMessage += ', updated by [ '+updatedby+' ], on [ '+updated+' ].';
				}
				if($('#'+mdiv).length) {
					$('#'+mdiv).html(succMessage).fadeIn('fast').fadeOut(5000);
				}
				else {
					$('<div></div>')
					.attr('id', mdiv)
					.addClass('verify')
					.html(succMessage)
					.insertBefore($f)
					.fadeIn('fast').fadeOut(5000);
				}
			}
			else if(action=='add') {
				if($('#f_new').length) {
					$('#f_new input[type=text]').val('');
					tinyMCE.get('content').setContent('');
				}
				// locs: [P,M], [P], [M]; location1:[P], location2:[M]
				if(/M/.test(locs)) $('#main1').load(url+'?refresh=M');
				if(/P/.test(locs)) $('#main2').load(url+'?refresh=P');
				if (/M/.test(locs)) {
					$('#tab3').removeClass('TabbedPanelsTabSelected');
					$('#tab2').removeClass('TabbedPanelsTabSelected');
					$('#tab1').addClass('TabbedPanelsTabSelected');
					$('#main3').closest('.TabbedPanelsContent').hide();
					$('#main2').closest('.TabbedPanelsContent').hide();
					$('#main1').closest('.TabbedPanelsContent').show();
				}
				else {
					$('#tab3').removeClass('TabbedPanelsTabSelected');
					$('#tab1').removeClass('TabbedPanelsTabSelected');
					$('#tab2').addClass('TabbedPanelsTabSelected');
					$('#main3').closest('.TabbedPanelsContent').hide();
					$('#main1').closest('.TabbedPanelsContent').hide();
					$('#main2').closest('.TabbedPanelsContent').show();
				}
			}
		}
	});
	return false;
  });
});
</script>
<?
  }
  function get_newform() {
?>
<form method="post" action="<?=$this->url;?>" id="f_new" > <!--works: onsubmit="return $.validate_form(this);" -->
  <div class="marB7px">
    <label for="location">Where (default is Member's page):</label>
    <input type="checkbox" name="location1" id="location" value="P" />
    <label>Public</label>
    <input type="checkbox" name="location2"  id="location" value="M" checked="checked" />
    <label>Members</label>
  </div>
  <label>Title:</label>
  <input type="text" name="title" id="title" size="90" class="inputTitle" />
  <label>Content:</label>
  <textarea name="content" id="content" rows="6" cols="80"></textarea>
  <input type="submit" value="Add" class="add marT7px" />
  <input type="reset" value="Reset" class="reset marT7px" />
<!--  <input type="button" value="Close" class="close" onclick="$(this.form.id).hide();" />
--></form>
<script language="javascript" type="text/javascript">
tinyMCE.init({
	theme : "advanced",
	mode: "exact",
	elements : 'content',
	theme_advanced_toolbar_location : "top",
	theme_advanced_buttons1 : "bold,italic,underline,strikethrough,separator,justifyleft,justifycenter,justifyright,justifyfull,formatselect,bullist,numlist,outdent,indent",
	theme_advanced_buttons2 : "link,unlink,anchor,image,separator,undo,redo,cleanup,code,separator,sub,sup,charmap",
	theme_advanced_buttons3 : "",
	height:"150px",
	width:"100%"
});
</script>
<?php
  }

  function get_header($query) {
	$res = mysql_query($query, $this->cibp) or die(mysql_error());
	$row = mysql_fetch_assoc($res);
	mysql_free_result($res);
	return $row;
  }	
  function get_list($query)
  {
	$res = mysql_query($query, $this->cibp);
	$records = array();
	while($row = mysql_fetch_assoc($res)) {
		array_push($records, $row);
	}
	mysql_free_result($res);
	return $records;
  }
  function lists($location) {
  	$location = strtolower($location);
  	if ($location=='m') {
		$title = $this->get_header($this->sql1);
		$records = $this->get_list($this->sql2);
	}
	else {
		$title = $this->get_header($this->sql3);
		$records = $this->get_list($this->sql4);
	}
	$id = $this->get_titleid_by_weight($location);
?>
<div class="demo">
  <?php $form_id = $location.'-header';?>
  <form id="<?=$form_id;?>" method="post" action="<?=$this->url;?>"  style="display:none;">
    <input type="text" name="title" value="<?=htmlspecialchars($title['title']);?>" size="100" />
    <span class="span1">
    <input type="submit" value="" class="f_submit" />
    </span>
    <span><a href="javascript:void(0);" onclick="$('#<?=$form_id;?>').hide();$('#<?=$location;?>-reminder').show('fast');" class="hide" title="Hide"></a></span>
    <p class="box">
      <textarea name="content" cols="90"><?=$title['content'];?></textarea>
    </p>
    <input type="hidden" name="id" value="<?=$id;?>">
    </input>
  </form>
  <div class="span1"> 
    <a id="<?=$location;?>-reminder" href="javascript:void(0);" onclick="$(this).hide();$('#<?=$form_id;?>').show('fast');" class="addNew" title="<?=htmlspecialchars($title['title']);?>">Show/Edit Title</a>&nbsp;
    <a href="javascript:void(0);" id="<?=$location;?>-hideshow" class="hideShow">Show Contents</a>
  </div>
  <ol id="<?=$location;?>-sortable">
    <?
  foreach ($records as $r) {
  	$rid = $r['id'];
?>
    <li id="li_<?=$rid;?>" class="ui-state-default">
      <div class="uiwrap"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span></div>
      <div id="div_<?=$rid;?>" class="listWrap">
        <span class="title"><?=htmlspecialchars($r['title']);?></span>
        <span class="span1">
          <a href="javascript:void(0);" class="edit" name="edit" title="edit <?=$r['id'];?>"></a>&nbsp;
          <a href="javascript:void(0);" class="delete" name="delete" title="delete <?=$r['id'];?>" onclick="if(confirm('Do you want to delete the record?')){$.delete_item('<?=$rid;?>')};"></a>
        </span>
        <blockquote class="box">
          <?=$r['content'];?>
        </blockquote>
      </div>
    </li>
    <?
  }
?>
  </ol>
</div>
<script language="javascript" type="text/javascript">
var url = '<?=$this->url;?>';
var header = '#<?=$location;?>-header';
var ol = '#<?=$location;?>-sortable';
var hideshow = '#<?=$location;?>-hideshow';
$(document).ready(function() {
  $( ol ).sortable( {
	update: function(event, ui) { 
		var ary = new Array();
		$(ol+">li").each(function(index) {
			ary[index] = parseInt(this.id.replace(/li_/,''));
		});
		$.get('<?=$this->url;?>', {sort_ids: ary.join(',')} );
	}
  });
  $(ol+'>li:odd').addClass('odd');
  $(ol+'>li:even').addClass('even');
  $(ol+'>li>div>span[class="title"]').hover(function() {
	$(this).addClass('hover');
	$(this).css({'cursor':'pointer','text-decoration':'underline'});
	}, function() {
	$(this).removeClass('hover');
	$(this).css('text-decoration','none');
  });
  if ( $.browser.msie ) {
	  $('#programs>ol>li').addClass('addPad');
	  $('form input.close').click(function(){
		$(ol+'>li').addClass('addPad').removeClass('padT5px').removeClass('padB5px');
		$(ol+'>li div.listWrap_off').addClass('listWrap').removeClass('listWrap_off');
	  });	  
  }/*
  $('<div id="livetip"></div>').hide().appendTo('body');
  var tipTitle = 'Drag and move to change display sequence';
  $('ol#sortable>li>div>span.title').bind('mouseover', function(event) {
	var $link = $(this);
	$('#livetip').css({top: event.pageY + 12,left: event.pageX + 12})
	  .html('<div>' + tipTitle + '</div>').show();
  }).live('mouseout', function(event) {
	$('#livetip').hide();
  });*/

  // $("ol li span a[title='edit']").live('click', function(event) {
  //$("ol#sortable>li>div>span a[title='edit']").live('click', function(event) {
  $(ol+">li>div>span[class='title'],"+ol+">li>div>span a[name='edit']").bind('click', function(event) {
   
    /* Ryan added: for IE */
	if ( $.browser.msie ) {													 
		$(ol + ' > li').addClass('padT5px padB5px').removeClass('addPad');
		$(ol + ' > li div.listWrap').addClass('listWrap_off').removeClass('listWrap');
	}
	/* end of IE */
	event.preventDefault();
	var $div = $(event.target).closest('div');
	var id = $div.attr('id').substr(4);
	var title = $div.children(':first').text(); 
	title = jQuery.trim(title);
	var content = $div.find('blockquote.box').html();
	if( $('#f_'+id).length>0 && (!$('#f_'+id).is(':visible'))) {
		if($('#div_'+id).length) $('#div_'+id).hide('fast');
		$('#f_'+id).show('fast');
		return false;
	}
	var cid = 'c_' + id;
	var tf = '<form method="post" action="'+url+'" id="f_'+id+'" name="f_'+id+'">'+"\n";
    tf += '  <input type="text" name="title" value="' + title + '" size="90" class="title" />'+"<br />\n";
    tf += '  <textarea name="content" id="' + cid + '" rows="6" cols="80">' + content + '</textarea>'+"<br />\n";
    tf += '  <input type="submit" value="Update" class="update" />'+"&nbsp;\n";
    tf += '  <input type="reset" value="Reset" class="reset" />' +"&nbsp;\n";
    tf += '  <input type="button" value="Close" class="close" onclick="$(\'#div_'+id+'\').show();$(this.form).hide();" />'+"\n";
    tf += '  <input type="hidden" name="id" value="' + id + '" />'+"\n";
    tf += '</form>'+"\n";
	$div.hide();
	$div.closest('li').append(tf);
	tinyMCE.init({
		theme : "advanced",
		mode: "exact",
		elements : cid,
		theme_advanced_toolbar_location : "top",
		theme_advanced_buttons1 : "bold,italic,underline,strikethrough,separator,justifyleft,justifycenter,justifyright,justifyfull,formatselect,bullist,numlist,outdent,indent",
		theme_advanced_buttons2 : "link,unlink,anchor,image,separator,undo,redo,cleanup,code,separator,sub,sup,charmap",
		theme_advanced_buttons3 : "",
		height:"150px",
		width:"100%"
	});
  });
  $('input[type="button"][value^="Close"]').click(function() {
	$(this).closest('form').get(0).style.display='None';
	$(this).closest('form').hide();
	if($('#'+$div).length>0 && $('#'+$div).is(':visible')) $('#'+$div).hide();
  });
  $(header).submit(function(e) {
	var t = $(this).attr('id');
	e.preventDefault();
	if(! $.validate_form(this)) return false;
	//var ed = tinyMCE.get("f_content");
	//ed.setProgressState(1);
	$.ajax({
		type: $(this).attr('method'),
		url: $(this).attr('action'),
		dataType: 'json',
		data: $(this).serialize()+'&action=edit',
		success: function(data) {
			//ed.setProgressState(0);
			var title = data.title;
			title = jQuery.trim(title);
			var content = data.content;
			var created = data.created;
			var createdby = data.createdby;
			var updated = data.updated;
			var updatedby = data.updatedby;
			var id = data.id;
			$("input[name='title']", $(this)).html(title);
			$("textarea[name='content']", $(this)).html(content);
			var mdiv = t+"_1";
			var succMessage = 'Successfully Update header ['+title+'], updated by: [ '+updatedby+' ], on [ '+updated+' ]';
			if($('#'+mdiv).length) {
				$('#'+mdiv).html(succMessage).fadeIn('fast').fadeOut(5000);
			}
			else {
				$('<div></div>')
				.attr('id', mdiv)
				.addClass('verify')
				.html(succMessage)
				.insertBefore($(this))
				.fadeIn('fast').fadeOut(5000);
			}
		}
	});
	return false;
  });
  $('#m-sortable li').addClass('marB1px');
  $(hideshow).bind('click', function(e){
  	//e.preventDefault();
	var hs = $(e.target);
	var t = hs.parent().next('ol');
	if(/Hide/.test(hs.text())) {
		$('li', $(t)).find('blockquote').hide();
		hs.text('Show Contents');
		$('li', $(t)).removeClass('marB10px').addClass('marB1px');
	}
	else {
		$('li', $(t)).find('blockquote').show();
		hs.text('Hide Contents');
		$('li', $(t)).removeClass('marB1px').addClass('marB10px');
	}
  });
});
</script>
<?
	}

  function get_current_id($id) {
	$query = "SELECT * FROM faqs where active='Y' and id=" . $id;
	$res = mysql_query($query, $this->cibp);
	$row = mysql_fetch_assoc($res);
	mysql_free_result($res);
	return $row;
  }  
  function edit()
  {
	$title = mysql_real_escape_string(trim($_POST['title']));
	$content = mysql_real_escape_string(trim($_POST['content']));
	$id = $_POST['id'];	
    $query = "update faqs set title = '".$title."', content = '".$content."', updatedby='".$_SESSION['pams_user']."' where id=".$id;
      if(! mysql_query($query, $this->cibp)) {
        die ("Could not update faqs at [".__LINE__.']: '.mysql_error());
      }
	  return $this->get_current_id($id);
  }
  function get_max_weight() {
	$sql = "select max(weight) from faqs where active='Y' and location='".$location."'";
	$res = mysql_query($sql, $this->cibp);
	$row = mysql_fetch_array($res);
	mysql_free_result($res);
	return $row[0];
  }
  function add()
  {
	$title = mysql_real_escape_string(trim($_POST['title']));
	$content = mysql_real_escape_string(trim($_POST['content']));
	$location = explode(',', $_POST['locs']);
	if(is_array($location) && count($location)>1) {
		foreach($location as $t) {
			$max = $this->get_max_weight($t);
			$weight = $max ? (++$max) : 100;
			$query = "insert into faqs (title, content, weight, createdby, created,location)
				values('".$title."', '".$content."', ".$weight.", '" . $_SESSION['pams_user'] . "', now(), '".$t."')";
			if(! mysql_query($query, $this->cibp)) {
				die ("Could not add faqs at [".__LINE__.']: '.mysql_error());
			}
		}
	}
	else {
		$max = $this->get_max_weight($location[0]);
		$weight = $max ? (++$max) : 100;
		$query = "insert into faqs (title, content, weight, createdby, created,location)
			values('".$title."', '".$content."', ".$weight.", '" . $_SESSION['pams_user'] . "', now(), '".$location[0]."')";
		if(! mysql_query($query, $this->cibp)) {
			die ("Could not add faqs at [".__LINE__.']: '.mysql_error());
		}
	}
	return $this->get_current_id(mysql_insert_id());
  }
  function delete($id) {
    // $query = "delete from faqs where id=".$id; // and id!=1
	$query = "update faqs set active='N', updatedby='".$_SESSION['pams_user']."' where id=".$id;
    if(! mysql_query($query, $this->cibp)) {
        die ("Could not delete faqs id=".$id." at [".__LINE__."]: ".mysql_error());
    }
	return true;
  }
  function change_sort() {
  	$ary = array();
  	$ary = explode(',', $_GET['sort_ids']);
	$count = 1;
	foreach($ary as $id) {
		$sql = "update faqs set weight=".$count.", updatedby='".$_SESSION['pams_user']."' where id=" . $id;
		mysql_query($sql,  $this->cibp) or die(mysql_error());
		$count ++;
	}
  }
  function get_titleid_by_weight($location) {
      $query = "select id from faqs where weight=".WEIGHT." and location='".$location."'";
      $res = mysql_query($query, $this->cibp);
	  $row = mysql_fetch_row($res);
	  mysql_free_result($res);
	  return $row['0'];
  }

}
?>

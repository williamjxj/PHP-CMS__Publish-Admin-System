<?php
class PamsBase
{
  var $url, $divid; 
  function __construct()  {
    $this->url = $_SERVER['PHP_SELF'];
  }
  function get_question($flag=1)
  {
	if ($flag==1) {
		$sql = "SELECT qid1, question1 FROM questions";
	}
	else if ($flag==2) {
		$sql = "SELECT qid2, question2 FROM questions";
	}
	echo "\n<option value=''> -------- </option>\n";
	$res = mysql_query($sql);
	while ($row = mysql_fetch_array($res, MYSQL_NUM)) {
		echo "\t" . '<option value="' . $row[0] . '">' . $row[1] . '</option>' . "\n";
	}
	mysql_free_result($res);
  }
  function draw($url, $total_pages, $current_page, $divId)
  {
    if( $current_page > 1 ) {
      printf( "<a href='javascript:void(0);' onClick=\"\$.get('$url=%d', function(data){\$('#%s').hide().html(data).fadeIn(300);}); \">[Start]</a> \n" , 1, $divId);
      printf( "<a href='javascript:void(0);' onClick=\"\$.get('$url=%d', function(data){\$('#%s').hide().html(data).fadeIn(300);}); \">[Prev]</a> \n" , ($current_page-1), $divId);
    }
    for( $i = ($current_page-5); $i <= $current_page+5; $i++ ) {
      if ($i < 1) continue;
      if ( $i > $total_pages ) break;

      if ( $i != $current_page ) {
        printf( "<a href='javascript:void(0);' onClick=\"\$.get('$url=%d', function(data){\$('#%s').hide().html(data).fadeIn(300);}); \">%d</a> \n" , $i, $divId, $i);
      } else {
        printf("<strong>%d</strong> \n",$i);
      }
    }
    if ( $current_page < $total_pages ) {
      printf( "<a href='javascript:void(0);' onClick=\"\$.get('$url=%d', function(data){\$('#%s').hide().html(data).fadeIn(300);});\">[Next]</a> \n" , ($current_page+1), $divId);
      printf( "<a href='javascript:void(0);' onClick=\"\$.get('$url=%d', function(data){\$('#%s').hide().html(data).fadeIn(300);});\">[End]</a> \n" , $total_pages, $divId);
    }
  }
  function get_total_rows($sql) {
    $res = mysql_query($sql);
    return mysql_num_rows($res);
  }
  function cutstr($str, $length, $ellipsis='') {
    $cut=(array)explode("\n\n",wordwrap($str,$length,"\n\n"));
    return $cut[0].((strlen($cut[1])<strlen($str))?$ellipsis:'...');
  }
  function escape($str) {
    $order   = array("\r\n", "\n", "\r");
    $replace = '<br />';
    $newstr = str_replace($order, $replace, $str);
    if (preg_match("/\'/", $newstr)) $newstr = preg_replace("/\'/", "\\'", $newstr);
    if (preg_match("/\"/", $newstr)) $newstr = preg_replace("/\"/", "&quot;", $newstr);
    return $newstr;
  }
  function get_date($date) {
	return preg_match("/YYYY-MM-DD/i", $date) ? '' : $date;
  }
  function str_replace_once($search, $replace, $subject)
  {
	if(($pos = strpos($subject, $search)) !== false) {
		$ret = substr($subject, 0, $pos).$replace.substr($subject, $pos + strlen($search));
	} else {
		$ret = $subject;
	}
	return($ret);
  }
  function web_full($subject) {
	if (! preg_match("/^http/", $subject)) {
		$subject = 'http://'.$subject;
	}
	return $subject;
  }
  function web_abbr($subject) {
	$pos = strripos($subject, '.');
	$h1 = strtolower(substr($subject, 0, $pos));
	$f1 = strtolower(substr($subject, $pos+1)); //csv,xls etc
	$ret = '';
	switch($f1) {
		case 'csv':
			$ret = '<img src="images/page_excel.png" width="16" height="16" border="0" alt="download csv file ' . $subject .'" />';
			break;
		case 'xls';
			$ret = '<img src="images/page_excel.png" width="16" height="16" border="0" alt="download xls file ' . $subject .'" />';
			break;
		default:
			$ret = '<img src="images/page_excel.png" width="16" height="16" border="0" alt="download file ' . $subject .'" />';
			break;
	}
	return ((strlen($h1)>FILE_LEN) ? substr($h1, 0, FILE_LEN-1).'&hellip;'.$f1 : $subject ).'&nbsp;'.$ret;
  }
  function email_abbr($subject) {
	if(! $subject) return '';
	return (strlen($subject)>15) ? substr($subject, 0, 14).'..' : $subject;
  }	
  function create_download_dir() {
	$cur_dir = dirname(__FILE__);
	if (! is_dir(DOWNLOAD_DIR)) {
		mkdir(DOWNLOAD_DIR, 0775) or die("Can't create directory ".DOWNLOAD_DIR);
	}
  }

}
?> 

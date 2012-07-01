<?php
/**
 * c-datcom_cibp's tables incmpatible: utf8_general_ci, utf8_unicode_ci 
 * http://dev.mysql.com/doc/refman/5.5/en/charset-unicode-sets.html
 * For any Unicode character set, operations performed using the _general_ci collation are faster than those for the _unicode_ci collation. 
 * For example, comparisons for the utf8_general_ci collation are faster, but slightly less correct, than comparisons for utf8_unicode_ci. 
 * The reason for this is that utf8_unicode_ci supports mappings such as expansions; that is, when one character compares as equal to combinations of other characters. 
 * For example, in German and some other languages “ß” is equal to “ss”. utf8_unicode_ci also supports contractions and ignorable characters. 
 * utf8_general_ci is a legacy collation that does not support expansions, contractions, or ignorable characters. It can make only one-to-one comparisons between characters.
 * 
 * In summary, unicode_ci adheres more strictly to the official Unicode collation algorithms because it respects the rules for expanding characters and the like. 
 * general_ci is faster, because it looks at every character in isolation.
 * 
 */
class PamsBase
{
  var $url, $divid; 
  function __construct()  {
    $this->url = $_SERVER['PHP_SELF'];
  }
  function get_payment_type() {
    $sql = "SELECT distinct payment_type FROM employers order by payment_type";
    $result = mysql_query($sql) or die(mysql_error());
    echo "\t<option value=''> --------------- </option>\n";
    while ($res = mysql_fetch_row($result)) {
      echo "\t<option value=\"" . $res[0] . "\">$res[0]</option>\n";
    }
    mysql_free_result($result);
  }
  function get_total_rows($sql) {
    $res = mysql_query($sql);
    return mysql_num_rows($res);
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
			$subject = 'https://'.$subject;
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
		return (strlen($subject)>15) ? substr($subject, 0, 14).'&hellip;' : $subject;
	}
	function cutstr($str, $length, $ellipsis='') {
		$cut=(array)explode("\n\n",wordwrap($str,$length,"\n\n"));
		return $cut[0].((strlen($cut[1])<strlen($str))?$ellipsis:'...');
	}
  
}
?> 

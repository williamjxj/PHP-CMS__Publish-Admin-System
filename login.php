<?php
session_start();
include_once('config.php');
//echo "<pre>";print_r($_SERVER); echo "</pre>";

mysql_pconnect(HOST, USER1, PASS1) or die(mysql_error());
mysql_select_db(DB_PAMS);
$login = new Login();

if (isset($_POST['username']) && isset($_POST['password'])) {
	echo $login->get_userpass(); 
}
elseif(isset($_GET['logout'])) {
	$login->update_login_info();
	session_unset();
	session_destroy();
	if(isset($_COOKIE['pams'])) {
		$time = time();
		if(isset($_SESSION['pams_user']) && isset($_SESSION['pams_pass'])) {		
			setcookie("pams[username]", $_SESSION['pams_user'], $time - 3600);
			setcookie("pams[password]", $_SESSION['pams_pass'], $time - 3600);
		}
	}
	$login->initial();
	echo "</body>\n</html>\n";
	exit;
}
else {
	$login->initial();
	echo "</body>\n</html>\n";
}
exit;

//////////////////
class Login
{
	var $url;
	function __construct()  {
		$this->url = $_SERVER['PHP_SELF'];
	}

	function connect_cibp_admin() {
		$db = mysql_pconnect(HOST, USER1, PASS1) or die(mysql_error());
		mysql_select_db(DB_PAMS, $db);
		return $db;
	}
	function connect_ufcw_admin() {
		$db = mysql_pconnect(UFCW_ADMIN_HOST, UFCW_ADMIN_USER, UFCW_ADMIN_PWD) or die(mysql_error());
		mysql_select_db(UFCW_ADMIN_DB, $db);
		return $db;
	}

	function initial($page=0) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>PAMS Admin Panel - User Login</title>
<link href="css/style.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="css/validationEngine.jquery.css" />
<style type="text/css" media="all">@import "css/c-css.php";</style>
<style>
h1 {
	font-size: 26px;
	font-weight: bold;
	color: #686868;
	margin: 0 0 0 10px;
	padding: 0;
}
</style>
<!--https doesn't work.
<script language="javascript" type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
--><script language="javascript" type="text/javascript" src="js/jquery-1.5.1.min.js"></script>
<script language="javascript" type="text/javascript" src="js/cookie.js"></script>
<script language="javascript" type="text/javascript" src="js/jquery.validationEngine-en.js"></script>
<script language="javascript" type="text/javascript" src="js/jquery.validationEngine.js"></script>
<script language="javascript" type="text/javascript">
$(document).ready(function() {
	var url =  '<?=$this->url;?>';
	$("#loginForm").validationEngine();
	$("#loginForm #login").click(function(event){
		event.preventDefault();
		if ($("#loginForm").validationEngine({returnIsValid:true})) {
			$.ajax({
				type: "POST",
				url: url,
				data: $(this.form).serialize(),
				success: function(data) {
					data = parseInt(data);
					switch(data) {
						case 1:
							//document.location.href='http://www.mycibp.ca/pams/index.php';
							document.location.href='index.php';
							break;
						case 2:
							document.location.href="http://localhost/ufcw/ufcw_admin/admin.php";
							break;
						case 0:
						default:
							if ($('#div1').length>0) {
								$('#div1').show();
							} else {
								$('<div id="div1">').insertAfter('#loginForm');
								$('#div1').addClass("notice w270px posRel loginTop").html("No such user, Please try again: " + data);
								$('#username').select().focus();	
							}
							break;
					}
					return false;
				}
			});
		}
		return false;
	});
	if( $.cookie("pams[username]") && $.cookie("pams[password]") ) {
		$('#username').val($.cookie("pams[username]"));
		$('#password').val($.cookie("pams[password]"));	
		$('input[name=rememberme]').attr('checked', true);
	}
	else {
		$('input[name=rememberme]').attr('checked', false);
	}
	$('#username').select().focus();	
});
</script>
</head>
<body>
<div id="upper">
  <div id="navLinks">
    <div class="badge"></div>
  </div>
</div>
<div class="topLine"></div>
<br/>
<br/>
<br/>
<br/>
<h1 align="center">Construction Industry Benefits Plan</h1>
<br/>
<br/>
<div id="loginbox">
  <form id="loginForm" action="javascript:void(0);" method="post">
    <div class="user">
      <label class="userTitle" for="username">Username:</label>
      <input class="validate[required,length[4,20]] userInput" id="username" name="username" type="text"   onfocus="this.select();" />
    </div>
    <div class="pass">
      <label class="passTitle" for="password">Password:</label>
      <input class="validate[required,length[4,20]] passInput" type="password" id="password" name="password" />
    </div>
    <div class="rememfor">
      <label class="remember" for="rememberme">
        <input id="rememberme" name="rememberme" type="checkbox" value="" class="checkbox" />
        Remember</label>
    </div>
    <input type="submit" id="login" value="Login" class="login bgpos" />
  </form>
</div>
<?
	}
	
	function get_userpass()
	{
		$username = mysql_real_escape_string(trim($_POST['username']));
		$password = mysql_real_escape_string($_POST['password']);
		$rememberme = isset($_POST['rememberme']) ? true : false;

		if( $this->search_cibp($username, $password, $rememberme) ) {
			return CIBP_USER;
		}
		elseif( $this->search_ufcw($username, $password) ) {
			return UFCW_USER;
		}
		else {
			return NO_USER;
		}
	}
	
	function search_cibp($username, $password, $rememberme)
	{
		$db = $this->connect_cibp_admin();
		$query = "SELECT * FROM users WHERE  username='".$username."' AND password='".$password."'";
		$res = mysql_query($query);
		// echo $query;
		$total =  mysql_num_rows($res);
		if ($total>0) {
			$username = ucfirst(strtolower($username));
			if($rememberme) {
				$expire = time() + 1728000; // Expire in 20 days		
				setcookie('pams[username]', $username, $expire);
				setcookie('pams[password]', $password, $expire);
			}
			else {
				setcookie('pams[username]', null);
				setcookie('pams[password]', null);
			}			
			$row = mysql_fetch_assoc($res);
			$_SESSION['pams_user'] = $username;
			$_SESSION['userid'] = $row['uid'];
			$_SESSION['pams_pass'] = $password;
			
			$this->insert_login_info($username, $row['uid']);
			return true;
			//$this->get_projects($row['uid']);
		}
		return false;
	}

	//header("Location: http://www.ufcwpensionplan.com/ufcw_admin/"); header("Location: http://localhost/ufcw/ufcw_admin/admin.php");exit;
	function search_ufcw($username, $password) 
	{
		$db = $this->connect_ufcw_admin();
		$query = "SELECT * FROM `users` WHERE username = '$username' AND pwd = '$password'";
		$res = mysql_query($query);
		$total =  mysql_num_rows($res);
		if ($total>0) {
			$row = mysql_fetch_assoc($res);
			$_SESSION['userid'] = $row['userid'];
			$session = $this->ufcw_sessionid();
			$sql = "update users SET session = '$session', expired  = NOW() + INTERVAL 5 HOUR WHERE userid = '$row[userid]'";
			// echo $sql; --update users SET session = 'apbrknue8q3jdhiebfh3jqbbk3', expired  = NOW() + INTERVAL 5 HOUR WHERE userid = '2'
			mysql_query($sql) or die(mysql_error());
			mysql_close($db);
			return true;
		}
		return false;
	}

  function insert_login_info($username, $uid)  {
    $ip = $this->getRealIpAddr();
	$browser = $this->get_browser();
	$session = session_id();
    $query = "insert into login_info(uid,ip,browser,username,session,count,login_time,logout,logout_time, expired)
      values(".$uid.", '".$ip."', '".$browser."', '".$username."', '".$session."', count+1, NULL, 'N', '', NOW() + INTERVAL 5 HOUR)
      on duplicate key update
      count = count+1,login_time = NULL,
	  expired = NOW() + INTERVAL 5 HOUR,
	  session = '".$session."', 
      logout='N', logout_time=''";
      if(! mysql_query($query)) {
        die ("Could not add login_info information: ".mysql_error());
      }
	  return true;
  }

	function ufcw_sessionid() {
	  return session_id();
	  return rand(1,99)*time(); //bigint instead of varchar(32);
	}
  
  function update_login_info() {
    //$query = "update login_info set logout='Y', logout_time=NULL where uid=".$uid;
	$query = "update login_info set logout='Y', logout_time=NULL where session='".session_id()."'";
	//echo "<pre>";print_r($_SESSION);echo $query; echo session_id(); echo "</pre>";
    if(! mysql_query($query)) {
        die ("Could not update login_info information: ".mysql_error());
    }
  }

  function getRealIpAddr() {
    if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
    {
      $ip=$_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
    {
      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else {
      $ip=$_SERVER['REMOTE_ADDR'];
    }
    return $ip;
  }
  
  //http://php.net/manual/en/function.get-browser.php
  function get_browser() {
	return $_SERVER["HTTP_USER_AGENT"]; //Mozilla/5.0 (Windows; U; Windows NT 6.1; en-GB; rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13
  }
  
	function get_projects($uid)
	{
		$query = "select up.*, p.name, p.url from users_projects up, projects p where uid=".$uid . " and up.pid=p.pid and p.active='Y'";
		$res = mysql_query($query);
		$howmany = mysql_num_rows($res);				
		if ($howmany==0) {
			$error = "You need to associate with this user with a certain project.";
			echo $error;
		}
		elseif ($howmany==1) {
			$row = mysql_fetch_assoc($res);
			echo "<pre>"; print_r($row); echo "</pre>";
		}
		else {
			$ary = array();
			while ($rows = mysql_fetch_assoc($res)) {
				$ary[] = $rows;
			}
			echo "<pre>"; print_r($ary); echo "</pre>";
		}
	}

}
?>

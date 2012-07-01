<?php
// grant all on cibp.* to pams@localhost identified by '!@#$%^&*()ZCBM';

if (preg_match("/^(192\.168|127\.)/", $_SERVER['REMOTE_ADDR']) || preg_match("/::1/", $_SERVER['REMOTE_ADDR'])) { 
define("HOST", "localhost");
define("USER", "pams");
define("PASS", "!@#$%^&*()ZCBM");
define("DB_NAME", "cibp");
define("USER1", "pams");
define("PASS1", "!@#$%^&*()ZCBM");
define("DB_PAMS", "pams");
define("DOWNLOAD_DIR", "./download/");
define("DOWNLOAD_DIR1", "./download/");
define("SSL_PAMS", "http://localhost/pams/");
define("MYCOVERAGE_DIR", 'resources/pdfhtml/'); //not use anymore
define("RESOURCES_DIR", './myresources/');
define("COVERAGE_DIR",  './mycoverages/');
}
// cdat.com
elseif(($_SERVER['SERVER_ADDR'] == '74.53.88.82') || ($_SERVER['SERVER_NAME']=='cdat.com') ) {
define("HOST", "localhost");
define("USER", "cdatcom_cibp");
define("PASS", "!#%&(24680zxcvbnm");
define("DB_NAME", "cdatcom_cibp");
define("USER1", "cdatcom_pams");
define("PASS1", "!#%&(24680zxcvbnm");
define("DB_PAMS", "cdatcom_pams");
define("DOWNLOAD_DIR", "./download/");
define("DOWNLOAD_DIR1", "./download/");
define("MYCOVERAGE_DIR", './resources/pdfhtml/');
define("RESOURCES_DIR", './myresources/');
define("COVERAGE_DIR",  "./mycoverages/");
}
else {
define("HOST", "localhost");
define("USER", "cdatcom_cibp");
define("PASS", "!#%&(24680zxcvbnm");
define("DB_NAME", "cibp");
define("USER1", "cdatcom_pams");
define("PASS1", "!@#$%^&*()ZCBM");
define("DB_PAMS", "pams");
//define("DOWNLOAD_DIR", "./download/");
//define("DOWNLOAD_DIR1", "./pams_download/");
define("DOWNLOAD_DIR", "/usr/tmp/");
define("DOWNLOAD_DIR1", "/usr/tmp/pams_download/");
define("SSL_PAMS", "https://mycibp.ca/pams/");
//define("MYCOVERAGE_DIR", '/usr/tmp/resources/pdfhtml/');
//define("RESOURCES_DIR", '/usr/tmp/resources/');
define("MYCOVERAGE_DIR", './resources/pdfhtml/');
define("RESOURCES_DIR", './myresources/');
define("COVERAGE_DIR",  './mycoverages/');
}

define("ROWS_PER_PAGE", 20);
define("SORT1", '<img src="images/up.png" border="0" width="13" height="11" alt="up">');
define("SORT2", '<img src="images/down.png" border="0" width="13" height="11" alt="down">');

define("FILE_LEN", 12);
define("LOGIN", "login.php");
/////////////////////////////////////
//$config['dsn2'] = 'mysqli://ufcw_admin:ufcw_admin_pwd@localhost/ufcw_new';
if(($_SERVER['SERVER_ADDR'] == '74.53.88.82') || ($_SERVER['SERVER_NAME']=='cdat.com') ) {
define("UFCW_ADMIN_HOST", "localhost");
define("UFCW_ADMIN_USER", "cdatcom_ufcw");
define("UFCW_ADMIN_PWD", "!#%&(24680zxcvbnm");
define("UFCW_ADMIN_DB", "cdatcom_ufcw");
}
else {
define("UFCW_ADMIN_HOST", "localhost");
define("UFCW_ADMIN_USER", "ufcw_admin");
define("UFCW_ADMIN_PWD", "ufcw_admin_pwd");
define("UFCW_ADMIN_DB", "ufcw_new");
}

define("NO_USER", 0);
define("CIBP_USER", 1);
define("UFCW_USER", 2);
define("OTHER_USER", 3);
?>

<?php



if ($_SERVER['SERVER_NAME'] == 'localhost') {
    define('URL', 'http://localhost/sigob/');
    define('PASSWORD', "");
    define('USER', 'root');
  }else {
    define('URL', 'https://gitcom-ve.com/sigob/');
    define('PASSWORD', "JH6$.GnJA6eL");
    define('USER', 'sigob_user');
  }




define('HOST', 'localhost');
define('DB', 'sigob');
define('CHARSET', 'utf8mb4');

?>
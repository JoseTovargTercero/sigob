<?php



if ($_SERVER['SERVER_NAME'] == 'localhost') {
    define('URL', 'http://localhost/sigob/');
    define('PASSWORD', "");
    define('USER', 'root');
} elseif ($_SERVER['SERVER_NAME'] == 'gitcom-ve.com') {
    define('URL', 'https://gitcom-ve.com/sigob/');
    define('PASSWORD', "JH6$.GnJA6eL");
    define('USER', 'sigob_user');
} else {
    $url = 'http://' . $_SERVER['SERVER_NAME'] . '/sigob/';
    define('URL', $url);
    define('PASSWORD', "");
    define('USER', 'root');
}






define('HOST', 'localhost');
define('DB', 'sigob');
define('CHARSET', 'utf8mb4');

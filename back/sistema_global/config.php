<?php



if ($_SERVER['SERVER_NAME'] == 'localhost') {
    define('HOST', 'localhost');
    define('DB', 'sigob');
    define('CHARSET', 'utf8mb4');
    define('URL', 'http://localhost/sigob/');
    define('PASSWORD', "");
    define('USER', 'root');
} elseif ($_SERVER['SERVER_NAME'] == 'gitcom-ve.com') {
    define('HOST', 'sigob.net');
define('DB', 'sigobnet_sigob');
define('CHARSET', 'utf8mb4');
    define('URL', 'https://gitcom-ve.com/sigob/');
    define('PASSWORD', "JH6$.GnJA6eL");
    define('USER', 'sigob_user');
} else {
    $url = 'http://' . $_SERVER['SERVER_NAME'] . '/';
    define('HOST', 'sigob.net');
    define('DB', 'sigobnet_sigob');
    define('CHARSET', 'utf8mb4');
    define('URL', $url);
    define('PASSWORD', "]n^VmqjqCD1k");
    define('USER', 'sigobnet_userroot');
}









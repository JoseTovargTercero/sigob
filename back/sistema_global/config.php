<?php

if ($_SERVER['SERVER_NAME'] == 'localhost') {
    define('HOST', 'localhost');
    define('DB', 'sigob');
    define('URL', 'http://localhost/sigob/');
    define('PASSWORD', "");
    define('USER', 'root');
} elseif ($_SERVER['SERVER_NAME'] == 'sigob.net') {
    $url = 'http://' . $_SERVER['SERVER_NAME'] . '/';
    define('HOST', 'sigob.net');
    define('DB', 'sigobnet_sigob');
    define('URL', $url);
    define('PASSWORD', "]n^VmqjqCD1k");
    define('USER', 'sigobnet_userroot');
} 

define('CHARSET', 'utf8mb4');
define('REMOTE_HOST', '192.99.85.240');

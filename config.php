<?php
require_once "misc.php";
date_default_timezone_set('Europe/Moscow');
$DBServer = 'localhost';
$DBUser = 'root';
$DBPass = '';
$DBName = 'asteriskcdrdb';
$DBAstName = 'asterisk';
$DBTable = 'queuelog';

define('RECPATH', "/var/spool/asterisk/monitor/");

$connection = new mysqli($DBServer, $DBUser, $DBPass, $DBName);
$connection->set_charset('utf8');

// check connection
if ($connection->connect_error) {
    trigger_error('Database connection failed: ' . $connection->connect_error, E_USER_ERROR);
}

$confpbx = new mysqli($DBServer, $DBUser, $DBPass, $DBName);
$confpbx->set_charset('utf8');


// Credentials for AMI (for the realtime tab to work)
// See /etc/asterisk/manager.conf

$manager_host = "127.0.0.1";
$manager_user = "admin";
$manager_secret = "12345";


//AJAM for realtime. For use: webenable=yes; mini-http enable; 

$config['urlraw'] = 'http://127.0.0.1:8088/asterisk/rawman';
$config['admin'] = 'admin';
$config['secret'] = '123456';
$config['authtype'] = 'plaintext';
$config['cookiefile'] = null;
$config['debug'] = false;


// Available languages "en", "ru"
$language = "ru";

require_once "lang/$language.php";

$page_rows = '100';
//$midb = conecta_db($DBServer, $DBUser, $DBPass, $DBName);
$self = $_SERVER['PHP_SELF'];

$DB_DEBUG = false;

session_start();
header('content-type: text/html; charset: utf-8');

?>

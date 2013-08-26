<?php
date_default_timezone_set("Europe/Brussels");
require_once "config.php";
require_once "fn.php";
//require_once "mongo.php";
require_once "class/DB.php";
require_once "class/Backup.php";
require_once "class/Restore.php";
require_once "class/Tool.php";
$Tool = new Tool();
$Tool->exec("backup");


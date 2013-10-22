<?php
date_default_timezone_set("Europe/Brussels");

define("ROOT_PATH", dirname(__FILE__) . "/");
require_once ROOT_PATH."config.php";
require_once ROOT_PATH."fn.php";
require_once ROOT_PATH."class/DB.php";
require_once ROOT_PATH."class/Backup.php";
require_once ROOT_PATH."class/Restore.php";
require_once ROOT_PATH."class/Tool.php";
$Tool = new Tool();
$Tool->exec("backup");


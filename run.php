<?php
date_default_timezone_set("Europe/Brussels");
require_once "config.php";
require_once "fn.php";
require_once "mongo.php";
require_once "model/Backup.php";
require_once "model/Restore.php";
require_once "model/Tool.php";
$Backup = new Tool();
$Backup->exec("backup");


<?php
date_default_timezone_set("Europe/Brussels");
require_once "mongo.php";
$Backup = new Backup();
$Backup->exec();

class Backup {
    private $date = null;
    private $db = null;
    private $path = null;
    private $dumppath = null;

    const DUMPPATH = '/root/Dropbox/dump/';
    const TMPDIR = '/var/dniwebkp/tmp/';

    public function __construct() {

        if (!is_dir(self::DUMPPATH)) {
            mkdir(self::DUMPPATH);
        }
        mongo_set_db("backup");
        $this->date = date("d_m_Y");
    }

    public function exec() {
        $dbs = $this->getDBs();
        foreach ($dbs as $db) {
            $this->selectDB($db);
            $this->dump();
        }
    }

    public function setDB($db, $bkpallowed = true) {
        mongo_update("dbs", array("_id" => $db, "allowed" => (boolean) $bkpallowed), array("_id" => $db));
    }

    private function selectDB($db) {
        $this->db = $db;
        $this->dumppath = self::DUMPPATH . $this->db . "/";
        if (!is_dir($this->dumppath)) {
            mkdir($this->dumppath);
        }
        $this->path = $this->dumppath . 'dump_' . $this->db . "_" . $this->date . ".tar.gz";
    }

    private function getDBs() {
        $databases = array();
        $dbs = mongo_find("dbs", array("allowed" => true));
        foreach ($dbs as $db) {
            $databases[] = $db["_id"];
        }
        return $databases;
    }

    private function dump() {
        $archive = $this->db . "_" . $this->date . ".tar.gz";
        $dump = ' dump/' . $this->db;
        shell_exec('mongodump --db ' . $this->db);
        shell_exec('tar -zcvf ' . $archive . $dump);
        shell_exec('rm -rf ' . $dump);
        shell_exec('mv ' . $archive . ' ' . $this->path);
        $this->log();
    }

    private function log() {
        $action = array();
        $action['collection'] = "dumphistory";
        $action['condition'] = array('_id' => $this->db);
        $action['update'] = array('$push' => array("dates" => $this->date));
        mongo_update($action['collection'], $action['update'], $action['condition']);
    }
}

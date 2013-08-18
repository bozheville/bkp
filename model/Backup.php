<?php
/**
 * Created by JetBrains PhpStorm.
 * User: bozheville
 * Date: 8/17/13
 * Time: 5:19 PM
 * To change this template use File | Settings | File Templates.
 */

class Backup {
    private $date = null;
    private $timestamp = null;
    private $db = null;
    private $path = null;
    private $dumppath = null;

    public function __construct() {
        if (!is_dir(DUMPPATH)) {
            mkdir(DUMPPATH);
        }
        mongo_set_db("backup");
        $this->date = date("d_m_Y");
        $this->timestamp = time();
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
        $this->dumppath = DUMPPATH . $this->db . "/";
        if (!is_dir($this->dumppath)) {
            mkdir($this->dumppath);
        }
        $this->path = $this->dumppath . 'dump_' . $this->db . "_" . $this->date . "_" . $this->timestamp . ".tar.gz";
    }

    private function getDBs() {
        $databases = array();
        $dbs = mongo_find("dbs", array("allowed" => true));
        foreach ($dbs as $db) {
            if ($this->canBackup($db)) {
                $this->removeOldDumps($db["_id"], $db["autoremove"]["days"], $db["autoremove"]["count"]);
                $databases[] = $db["_id"];
            }
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

    private function canBackup($db) {
        $timestamp = explode("-", date("i-H-j-n-Y-w"));
        $keys = array("mm", "hh", "d", "m", "y", "dd");
        $ts = array();
        foreach ($keys as $key) {
            $ts[$key] = array_shift($timestamp);
        }
        $ts["dd"] = $ts["dd"] == 0 ? 7 : $ts["dd"];
        $matched = true;
        $rules = $db["rules"];
        foreach ($ts as $key => $val) {
            $rules[$key] = empty($rules[$key]) ? "*" : $rules[$key];
            $is_star = $rules[$key] == "*";
            if (!in_array($val, explode(",", $rules[$key])) && empty($is_star)) {
                $matched = false;
            }
        }
        return $matched;
    }

    private function removeOldDumps($db, $days, $maxcount) {
        $path=DUMPPATH . $db;
        $maxSeconds = $days * 24 * 60 * 60;
        if ($handle = opendir($path)) {
            while (false !== ($entry = readdir($handle))) {
                $entry = str_replace(".tar.gz", "", $entry);
                $entry = explode("_", $entry);
                if ($entry[1] == $db) {
                    if (time() - (int) $entry[5] > $maxSeconds) {
                        $entry = implode("_",$entry) . ".tar.gz";
                        unlink($path .$entry);
//                        p("Removed: " . $path .$entry);
                    }
                }
            }
            closedir($handle);
        }
//        if ($handle = opendir($path)) {
//            while (false !== ($entry = readdir($handle))) {
//                $entry = str_replace(".tar.gz", "", $entry);
//                $entry = explode("_", $entry);
//                if ($entry[1] == $db) {
//
//                }
//            }
//            closedir($handle);
//        }
//        die();
    }
}
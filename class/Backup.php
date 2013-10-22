<?php
class Backup {
    private $date = null;
    private $timestamp = null;
    private $db = null;

    public function __construct() {
        if (!is_dir(DUMPPATH)) {
            mkdir(DUMPPATH);
        }
        $this->db = new DB(DBNAME);
        $this->date = date("Y_m_d");
        $this->timestamp = time();
    }

    public function exec() {
        $dbs = $this->getDBs();
        foreach ($dbs as $db) {
            $this->dump($db);
        }
    }

    public function setDB($db, $bkpallowed = true) {
        $this->db->update("dbs", array("_id" => $db, "allowed" => (boolean) $bkpallowed), array("_id" => $db));
    }

    private function getDumpFileName($db, $fd = false) {
        $dumppath = DUMPPATH . $db . "/";
        if (!is_dir($dumppath)) mkdir($dumppath);
        $dumpFileName = ($fd ? "fd_" : "") . $dumppath . 'dump_' . $db . "_" . $this->date . "_" . $this->timestamp . ".tar.gz";
        return $dumpFileName;
    }

    private function getDBs() {
        $databases = array();
        $dbs = $this->db->find("dbs", array("allowed" => true));
        foreach ($dbs as $db) {
            if ($this->canBackup($db)) {
                $this->removeOldDumps($db["_id"], $db["autoremove"]["days"], $db["autoremove"]["count"]);
                $databases[] = $db["_id"];
            }
        }
        return $databases;
    }

    private function dump($db, $fd = false) {
        $tmpDumpPath = 'dump/' . $db;
        $compressedDump = $db . ".tar.gz";
        $exec = array();
        $exec[] = "cd " . ROOT_PATH;
        $exec[] = 'mongodump --db ' . $db;
        $exec[] = 'tar -zcvf ' . $compressedDump . " " . $tmpDumpPath;
        $exec[] = 'rm -rf ' . $tmpDumpPath;
        $exec[] = 'mv ' . $compressedDump . ' ' . $this->getDumpFileName($db, $fd);
        shell_exec(implode("; ", $exec));
        $this->log($db);
    }

    private function log($db) {
        $this->db->update("dumphistory", array('$push' => array("dates" => date("d.m.Y h:i:s"))), array('_id' => $db));
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

    private function removeOldDumps($db, $days = 1, $maxcount = 1) {
        $dumps = array();
        if ($handle = opendir(DUMPPATH . $db)) {
            while (false !== ($entryfile = readdir($handle))) {
                if (preg_match('#\.tar\.gz$#', $entryfile)) {
                    $ts = preg_replace('#^\S+_([0-9]+)\.tar\.gz$#', "$1", $entryfile);
                    $dumps[$ts] = $entryfile;
                }
            }
            closedir($handle);
            ksort($dumps);
            foreach ($dumps as $ts => $dump) {
                if ((time() - $ts) > ($days * 24 * 60 * 60)) {
                    unlink(DUMPPATH . $db."/" . $dump);
                    unset($dumps[$ts]);
                }
            }
            while (count($dumps) > $maxcount-1) {
                unlink(DUMPPATH . $db ."/". array_shift($dumps));
            }
        }
    }
}
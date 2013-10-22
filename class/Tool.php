<?php
class Tool {
    private $Backup;
    private $Restore;

    public function __construct() {
        $this->Backup = new Backup();
        $this->Restore = new Restore();
    }

    public function exec($type) {
        switch ($type) {
            case "backup":
                $this->Backup->exec();
                break;
            case "restore":
                $this->Restore->exec();
                break;
        }
    }
}
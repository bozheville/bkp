<?php
/**
 * Created by JetBrains PhpStorm.
 * User: bozheville
 * Date: 8/17/13
 * Time: 5:19 PM
 * To change this template use File | Settings | File Templates.
 */

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
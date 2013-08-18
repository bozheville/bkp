<?php
/**
 * Created by JetBrains PhpStorm.
 * User: bozheville
 * Date: 8/17/13
 * Time: 5:20 PM
 * To change this template use File | Settings | File Templates.
 */

function p($val = "") {
    print_r($val);
    if (!is_array($val)) {
        print_r("\n");
    }
}

function pd($val = "") {
    p($val);
    die();
}
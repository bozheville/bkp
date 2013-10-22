<?php
function p($val = "") {
    print_r($val);
    print_r("\n");
}

function pd($val = "") {
    p($val);
    die();
}
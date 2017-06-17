#!/usr/bin/php
<?php

require_once(__DIR__ . '/../../../include/functions.inc.php');

$noc = new \Detain\MyAdminSoftaculous\SOFT_NOC(SOFTACULOUS_USERNAME, SOFTACULOUS_PASSWORD);

print_r($noc->licenses('', $_SERVER['argv'][1]));

//$GLOBALS['tf']->session->destroy();

#!/usr/bin/php
<?php

require_once(__DIR__ . '/../../../include/functions.inc.php');

$noc = new SOFT_NOC(SOFTACULOUS_USERNAME, SOFTACULOUS_PASSWORD);

print_r($noc->cancel($_SERVER['argv'][1]));
print_r($noc->response);

//$GLOBALS['tf']->session->destroy();

#!/usr/bin/php
<?php

require_once(__DIR__.'/../../../include/functions.inc.php');

$noc = new \Detain\MyAdminSoftaculous\SOFT_NOC(SOFTACULOUS_USERNAME, SOFTACULOUS_PASSWORD);

print_r($noc->invoicedetails($_SERVER['argv'][1]));
print_r($noc->response);

//$GLOBALS['tf']->session->destroy();

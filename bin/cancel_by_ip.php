#!/usr/bin/env php
<?php

require_once __DIR__.'/../../../../include/functions.inc.php';

$noc = new \Detain\MyAdminSoftaculous\SoftaculousNOC(SOFTACULOUS_USERNAME, SOFTACULOUS_PASSWORD);

$noc->cancel('', $_SERVER['argv'][1]);
print_r($noc->response);

//$GLOBALS['tf']->session->destroy();

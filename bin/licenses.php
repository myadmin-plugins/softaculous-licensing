#!/usr/bin/env php
<?php

require_once __DIR__.'/../../../../include/functions.inc.php';

$noc = new \Detain\MyAdminSoftaculous\SoftaculousNOC(SOFTACULOUS_USERNAME, SOFTACULOUS_PASSWORD);

print_r($noc->licenses());

//$GLOBALS['tf']->session->destroy();

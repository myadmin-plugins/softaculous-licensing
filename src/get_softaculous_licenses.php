<?php
/**
 * Softaculous Related Functionality
 * Last Changed: $LastChangedDate: 2015-09-23 14:50:01 -0400 (Wed, 23 Sep 2015) $
 * @author detain
 * @version $Revision: 15402 $
 * @copyright 2017
 * @package MyAdmin-Softaculous-Licensing
 * @category Licenses
 */

/**
 * @param string $ipAddress
 * @return false|array
 */
function get_softaculous_licenses($ipAddress = '') {
	$noc = new \Detain\MyAdminSoftaculous\SOFT_NOC(SOFTACULOUS_USERNAME, SOFTACULOUS_PASSWORD);
	return $noc->licenses('', $ipAddress);
}


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
 * @param string $ip
 * @return array|bool
 */
function get_webuzo_licenses($ip = '') {
	$noc = new \Detain\MyAdminSoftaculous\SOFT_NOC(WEBUZO_USERNAME, WEBUZO_PASSWORD);
	return $noc->webuzo_licenses('', $ip);
}

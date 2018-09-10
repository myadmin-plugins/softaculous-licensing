<?php
/**
 * Softaculous Related Functionality
 * @author Joe Huss <detain@interserver.net>
 * @copyright 2018
 * @package MyAdmin-Softaculous-Licensing
 * @category Licenses
 */

/**
 * @param string $ipAddress
 * @return FALSE|array
 */
function get_softaculous_licenses($ipAddress = '')
{
	$noc = new \Detain\MyAdminSoftaculous\SoftaculousNOC(SOFTACULOUS_USERNAME, SOFTACULOUS_PASSWORD);
	return $noc->licenses('', $ipAddress);
}

<?php
/**
 * Softaculous Related Functionality
 * @author Joe Huss <detain@interserver.net>
 * @copyright 2019
 * @package MyAdmin-Softaculous-Licensing
 * @category Licenses
 */

/**
 * activate_softaculous()
 *
 * @param mixed $ipAddress
 * @param mixed $field
 * @param mixed $email
 * @return boolean
 */
function activate_softaculous($ipAddress, $field, $email)
{
	myadmin_log('softaculous', 'info', "activating softaculous({$ipAddress}, {$field}, {$email})", __LINE__, __FILE__);
	try {
		$noc = new \Detain\MyAdminSoftaculous\SoftaculousNOC(SOFTACULOUS_USERNAME, SOFTACULOUS_PASSWORD);
		// Buy / renew a License
		$matches = $noc->licenses('', $ipAddress);
		$need = true;
		if ($matches['num_results'] > 0) {
			myadmin_log('softaculous', 'info', "Found Existing Softaculous licenses on {$ipAddress}, scanning them", __LINE__, __FILE__);
			foreach ($matches['licenses'] as $lid => $ldata) {
				if ($ldata['type'] == $field) {
					myadmin_log('softaculous', 'info', 'Found matching license type, skipping creating a new one', __LINE__, __FILE__);
					$need = false;
				} else {
					myadmin_log('softaculous', 'info', "Found different softaculous license type {$ldata['type']}, canceling {$lid}", __LINE__, __FILE__);
					$noc->cancel($ldata['license']);
				}
			}
		}
		if ($need == true) {
			$response = $noc->buy($ipAddress, '1M', $field, $email, 1);
			$output = json_encode($response, JSON_PRETTY_PRINT);
			myadmin_log('softaculous', 'info', 'Softaculous order output '.str_replace("\n", '', $output), __LINE__, __FILE__);
		}
	} catch (Exception $e) {
		myadmin_log('softaculous', 'info', 'Canceling Caught exception: '.$e->getMessage(), __LINE__, __FILE__);
		return false;
	}
	return true;
}

<?php
/**
 * Softaculous Related Functionality
 * @author Joe Huss <detain@interserver.net>
 * @copyright 2019
 * @package MyAdmin-Softaculous-Licensing
 * @category Licenses
 */

/**
 * activate_webuzo()
 *
 * @param mixed $ipAddress
 * @param mixed $field
 * @param mixed $email
 * @return boolean
 */
function activate_webuzo($ipAddress, $field = '', $email = '')
{
	myadmin_log('softaculous', 'info', "activating webuzo({$ipAddress}, {$field}, {$email})", __LINE__, __FILE__);
	try {
		$noc = new \Detain\MyAdminSoftaculous\SoftaculousNOC(WEBUZO_USERNAME, WEBUZO_PASSWORD);
		// Buy / renew a License
		$matches = $noc->webuzoLicenses('', $ipAddress);
		$need = true;
		if ($matches['num_results'] > 0) {
			myadmin_log('softaculous', 'info', "Found Existing webuzo licenses on {$ipAddress}, scanning them", __LINE__, __FILE__);
			foreach ($matches['licenses'] as $lid => $ldata) {
				if ($ldata['type'] == $field) {
					myadmin_log('softaculous', 'info', 'Found matching license type, skipping creating a new one', __LINE__, __FILE__);
					$need = false;
				} else {
					myadmin_log('softaculous', 'info', "Found different webuzo license type {$ldata['type']}, canceling {$lid}", __LINE__, __FILE__);
					$noc->webuzoCancel($ldata['license']);
				}
			}
		}
		if ($need == true) {
			$response = $noc->webuzoBuy($ipAddress, '1M', $field, $email, 1);
			if ($response === false) {
				myadmin_log('softaculous', 'error', "webuzo->buy({$ipAddress}, 1M, {$field}, {$email}) failed with error".json_encode($noc->error), __LINE__, __FILE__);
			//$output = $noc->error;
			} else {
				myadmin_log('softaculous', 'info', 'webuzo order output '.json_encode($response), __LINE__, __FILE__);
			}
		}
	} catch (Exception $e) {
		myadmin_log('softaculous', 'info', 'Canceling Caught exception: '.$e->getMessage(), __LINE__, __FILE__);
		return false;
	}
	return true;
}

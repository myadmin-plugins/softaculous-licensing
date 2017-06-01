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
 * activate_softaculous()
 *
 * @param mixed $ip
 * @param mixed $field
 * @param mixed $email
 * @return void
 */
function activate_softaculous($ip, $field, $email) {
	myadmin_log('softaculous', 'info', "activating softaculous({$ip}, {$field}, {$email})", __LINE__, __FILE__);
	try {
		$noc = new \Detain\MyAdminSoftaculous\SOFT_NOC(SOFTACULOUS_USERNAME, SOFTACULOUS_PASSWORD);
		// Buy / renew a License
		$matches = $noc->licenses('', $ip);
		$need = true;
		if ($matches['num_results'] > 0) {
			myadmin_log('softaculous', 'info', "Found Existing Softaculous licenses on {$ip}, scanning them", __LINE__, __FILE__);
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
			$response = $noc->buy($ip, '1M', $field, $email, 1);
			$output = print_r($response, true);
			myadmin_log('softaculous', 'info', 'Softaculous order output ' . str_replace("\n", '', $output), __LINE__, __FILE__);
		}
	} catch (Exception $e) {
		myadmin_log('softaculous', 'info', 'Canceling Caught exception: '.$e->getMessage(), __LINE__, __FILE__);
		return false;
	}
	return true;
}

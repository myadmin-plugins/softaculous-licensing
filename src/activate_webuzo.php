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
 * activate_webuzo()
 *
 * @param mixed $ip
 * @param mixed $field
 * @param mixed $email
 * @return void
 */
function activate_webuzo($ip, $field = '', $email = '') {
	myadmin_log('softaculous', 'info', "activating webuzo({$ip}, {$field}, {$email})", __LINE__, __FILE__);
	try {
		$noc = new \Detain\MyAdminSoftaculous\SOFT_NOC(WEBUZO_USERNAME, WEBUZO_PASSWORD);
		// Buy / renew a License
		$matches = $noc->webuzo_licenses('', $ip);
		$need = TRUE;
		if ($matches['num_results'] > 0) {
			myadmin_log('softaculous', 'info', "Found Existing webuzo licenses on {$ip}, scanning them", __LINE__, __FILE__);
			foreach ($matches['licenses'] as $lid => $ldata) {
				if ($ldata['type'] == $field) {
					myadmin_log('softaculous', 'info', 'Found matching license type, skipping creating a new one', __LINE__, __FILE__);
					$need = FALSE;
				} else {
					myadmin_log('softaculous', 'info', "Found different webuzo license type {$ldata['type']}, canceling {$lid}", __LINE__, __FILE__);
					$noc->webuzo_cancel($ldata['license']);
				}
			}
		}
		if ($need == TRUE) {
			$response = $noc->webuzo_buy($ip, '1M', $field, $email, 1);
			if ($response === FALSE) {
				myadmin_log('softaculous', 'error', "webuzo->buy({$ip}, 1M, {$field}, {$email}) failed with error".json_encode($noc->error), __LINE__, __FILE__);
				$output = $noc->error;
			} else
				myadmin_log('softaculous', 'info', 'webuzo order output '.json_encode($response), __LINE__, __FILE__);
		}
	} catch (Exception $e) {
		myadmin_log('softaculous', 'info', 'Canceling Caught exception: '.$e->getMessage(), __LINE__, __FILE__);
		return FALSE;
	}
	return TRUE;
}

<?php
/**
 * Softaculous Related Functionality
 * Last Changed: $LastChangedDate: 2015-09-23 14:50:01 -0400 (Wed, 23 Sep 2015) $
 * @author detain
 * @copyright 2017
 * @package MyAdmin-Softaculous-Licensing
 * @category Licenses
 */

/**
 * @param $ipAddress
 * @return bool
 */
function deactivate_webuzo($ipAddress) {
	myadmin_log('softaculous', 'info', "deactivating webuzo({$ipAddress})", __LINE__, __FILE__);
	try {
		$noc = new \Detain\MyAdminSoftaculous\SoftaculousNOC(WEBUZO_USERNAME, WEBUZO_PASSWORD);
		// Buy / renew a License
		$matches = $noc->webuzoLicenses('', $ipAddress);
		if ($matches['num_results'] > 0) {
			foreach ($matches['licenses'] as $lid => $ldata) {
				myadmin_log('softaculous', 'info', "canceling webuzo license {$lid}", __LINE__, __FILE__);
				myadmin_log('softaculous', 'info', "noc->cancelWithRefund('{$ldata['license']}','') = ".json_encode($noc->cancelWithRefund($ldata['license'])), __LINE__, __FILE__);
				myadmin_log('softaculous', 'info', 'noc response '.json_encode($noc->response), __LINE__, __FILE__);
			}
		}
		//myadmin_log('softaculous', 'info', "noc->cancel('','$ipAddress') = " . json_encode($noc->cancel('', $ipAddress)), __LINE__, __FILE__);
		//myadmin_log('softaculous', 'info', "noc response " . json_encode($noc->response), __LINE__, __FILE__);
	} catch (Exception $e) {
		myadmin_log('softaculous', 'info', 'Canceling Caught exception: '.$e->getMessage(), __LINE__, __FILE__);
		return FALSE;
	}
	return TRUE;
}


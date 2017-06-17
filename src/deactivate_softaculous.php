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
 * @param $ip
 * @return bool
 */
function deactivate_softaculous($ip) {
	myadmin_log('softaculous', 'info', 'deactivating softaculous($ip)', __LINE__, __FILE__);
	try {
		$noc = new \Detain\MyAdminSoftaculous\SOFT_NOC(SOFTACULOUS_USERNAME, SOFTACULOUS_PASSWORD);
		// Buy / renew a License
		$matches = $noc->licenses('', $ip);
		$need = true;
		if ($matches['num_results'] > 0) {
			foreach ($matches['licenses'] as $lid => $ldata) {
				myadmin_log('softaculous', 'info', "canceling softaculous license {$lid}", __LINE__, __FILE__);
				myadmin_log('softaculous', 'info', "noc->cancel_with_refund('{$ldata['license']}','') = ".json_encode($noc->cancel_with_refund($ldata['license'])), __LINE__, __FILE__);
				myadmin_log('softaculous', 'info', 'noc response '.json_encode($noc->response), __LINE__, __FILE__);
			}
		}
		//myadmin_log('softaculous', 'info', "noc->cancel('','$ip') = " . json_encode($noc->cancel('', $ip)), __LINE__, __FILE__);
		//myadmin_log('softaculous', 'info', "noc response " . json_encode($noc->response), __LINE__, __FILE__);
	} catch (Exception $e) {
		myadmin_log('softaculous', 'info', 'Canceling Caught exception: '.$e->getMessage(), __LINE__, __FILE__);
		return false;
	}
	return true;
}

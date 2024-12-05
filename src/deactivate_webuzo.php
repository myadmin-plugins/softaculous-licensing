<?php
/**
 * Softaculous Related Functionality
 * @author Joe Huss <detain@interserver.net>
 * @copyright 2025
 * @package MyAdmin-Softaculous-Licensing
 * @category Licenses
 */

/**
 * @param $ipAddress
 * @return bool
 */
function deactivate_webuzo($ipAddress)
{
    myadmin_log('softaculous', 'info', "deactivating webuzo({$ipAddress})", __LINE__, __FILE__);
    try {
        $noc = new \Detain\MyAdminSoftaculous\SoftaculousNOC(WEBUZO_USERNAME, WEBUZO_PASSWORD);
        // Buy / renew a License
        $matches = $noc->webuzo_licenses('', $ipAddress);
        if ($matches['num_results'] > 0) {
            foreach ($matches['licenses'] as $lid => $ldata) {
                myadmin_log('softaculous', 'info', "canceling webuzo license {$lid}", __LINE__, __FILE__);
                myadmin_log('softaculous', 'info', "noc->refund_and_cancel('{$ldata['license']}','') = ".json_encode($noc->refund_and_cancel($ldata['license'], '', 1)), __LINE__, __FILE__);
                myadmin_log('softaculous', 'info', 'noc response '.json_encode($noc->response), __LINE__, __FILE__);
            }
        }
        //myadmin_log('softaculous', 'info', "noc->cancel('','{$ipAddress}') = " . json_encode($noc->cancel('', $ipAddress)), __LINE__, __FILE__);
        //myadmin_log('softaculous', 'info', "noc response " . json_encode($noc->response), __LINE__, __FILE__);
    } catch (Exception $e) {
        myadmin_log('softaculous', 'info', 'Canceling Caught exception: '.$e->getMessage(), __LINE__, __FILE__);
        return false;
    }
    return true;
}

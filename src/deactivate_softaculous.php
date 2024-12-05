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
function deactivate_softaculous($ipAddress)
{
    myadmin_log('softaculous', 'info', 'deactivating softaculous($ipAddress)', __LINE__, __FILE__);
    try {
        $noc = new \Detain\MyAdminSoftaculous\SoftaculousNOC(SOFTACULOUS_USERNAME, SOFTACULOUS_PASSWORD);
        // Buy / renew a License
        $matches = $noc->licenses('', $ipAddress);
        if ($matches['num_results'] > 0) {
            foreach ($matches['licenses'] as $lid => $ldata) {
                myadmin_log('softaculous', 'info', "canceling softaculous license {$lid}", __LINE__, __FILE__);
                $response = $noc->refund_and_cancel($ldata['license'], '', 1);
                myadmin_log('softaculous', 'info', "noc->refund_and_cancel('{$ldata['license']}','', 1) = ".json_encode($response), __LINE__, __FILE__);
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

<?php
/**
 * Softaculous Related Functionality
 * @author Joe Huss <detain@interserver.net>
 * @copyright 2025
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
        //$noc->json = 1;
        // Buy / renew a License
        $matches = $noc->webuzo_licenses('', $ipAddress);
        if ($matches['num_results'] > 0) {
            myadmin_log('softaculous', 'info', "Found Existing webuzo licenses on {$ipAddress}, scanning them", __LINE__, __FILE__);
            foreach ($matches['licenses'] as $lid => $ldata) {
                return $ldata['license'];
                if ($ldata['type'] == $field) {
                    myadmin_log('softaculous', 'info', 'Found matching license type, skipping creating a new one', __LINE__, __FILE__);
                    $need = false;
                    return $ldata['license'];
                } else {
                    myadmin_log('softaculous', 'info', "Found different webuzo license type {$ldata['type']}, canceling {$lid}", __LINE__, __FILE__);
                    $noc->webuzo_cancel($ldata['license']);
                }
            }
        }
        $response = $noc->webuzo_buy($ipAddress, '1M', $field, $email, 1);
        // Check for any error
        if (empty($noc->error)) {
            myadmin_log('myadmin', 'debug', 'webuzoBuy SUCCRESS!', __LINE__, __FILE__);
        // Everything went perfect
        } else {
            // Dump the error
            myadmin_log('myadmin', 'debug', 'webuzoBuy error:'.print_r($noc->error, true), __LINE__, __FILE__);
        }
        if ($response === false) {
            myadmin_log('softaculous', 'error', "webuzo->buy({$ipAddress}, 1M, {$field}, {$email}) failed with error".json_encode($noc->error), __LINE__, __FILE__);
        //$output = $noc->error;
        } else {
            myadmin_log('softaculous', 'info', 'webuzo order output '.json_encode($response), __LINE__, __FILE__);
        }
        return $response['license'];
    } catch (Exception $e) {
        myadmin_log('softaculous', 'info', 'Canceling Caught exception: '.$e->getMessage(), __LINE__, __FILE__);
        return false;
    }
}

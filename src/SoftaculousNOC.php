<?php
/**
 * Softaculous Related Functionality
 * @author Joe Huss <detain@interserver.net>
 * @copyright 2020
 * @package MyAdmin-Softaculous-Licensing
 * @category Licenses
 */

namespace Detain\MyAdminSoftaculous;

/**
 * Class SoftaculousNOC
 *
 * @package Detain\MyAdminSoftaculous
 */
class SoftaculousNOC
{
    private $nocname = 'username';
    private $nocpass = 'password';
    public $softaculous = 'https://www.softaculous.com/noc';
    public $error = [];
    public $params = [];
    public $response = [];
    public $post = [];
    public $raw_response;
    public $json = 0;

    /**
     * SoftaculousNOC constructor.
     *
     * @param string $nocname
     * @param string $nocpass
     * @param string $url
     * @param int $json
     */
    public function __construct($nocname = '', $nocpass = '', $url = '', $json = 0)
    {
        if (!empty($nocname)) {
            $this->nocname = $nocname;
        }

        if (!empty($nocpass)) {
            $this->nocpass = $nocpass;
        }

        if (!empty($url)) {
            $this->softaculous = $url;
        }

        if (!empty($json)) {
            $this->json = 1;
        }
    }

    /**
     * Handles the API curl Call, parsing the response and storing it
     * @return FALSE|array FALSE if there was an error (setting  $this->error or returning the response)
     */
    public function req()
    {
        $url = $this->softaculous.'?';

        foreach ($this->params as $k => $v) {
            $url .= '&'.$k.'='.rawurlencode($v);
        }

        if (!empty($this->json)) {
            $url .= '&json=1';
        }
        //echo $url.'<br>';

        // Set the curl parameters.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        // Turn off the server and peer verification (TrustManager Concept).
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);


        $this->post = ['nocname' => $this->nocname,
                'nocpass' => $this->nocpass];

        $this->post = http_build_query($this->post);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->post);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // Get response from the server.
        $this->raw_response = curl_exec($ch);

        if (!$this->raw_response) {
            $this->error[] = 'There was some error in connecting to Softaculous. This may be because of no internet connectivity at your end. '.curl_error($ch);
            return false;
        }

        // Extract the response details.
        $this->response = $this->APIunserialize($this->raw_response);

        if (empty($this->response['error'])) {
            unset($this->response['error']);
            return $this->response;
        } else {
            $this->error = array_merge($this->error, $this->response['error']);
            return false;
        }
    }

    /**
    * Decodes response as per JSON or serialize
    *
    * @param string $response
    * @return array the undecoded object
    */
    public function APIunserialize($response)
    {
        if (!empty($this->json)) {
            return json_decode($response, true);
        }

        return unserialize($response);
    }

    /**
     * Buy or Renew a License
     *
     * @param mixed $ip The IP of the license to be Purchased or Renewed
     * @param string $toadd Time to extend. Valid extension e.g. '1M' will extend by one months  '8M' will extend by eight months  '1Y' will extend by One year
     * @param mixed $servertype 1 for Dedicated and 2 for VPS
     * @param mixed $authemail When a new license is purchased an Authorisation email is required to verify the owner of the License or for reminders when the license is expiring. This is not required in case of renewals
     * @param integer $autorenew To be renewed Automatically before expiry. Values - 1 for TRUE   0 (i.e. any empty value) or 2 for FALSE   Emails will be sent when renewed.
     * @param integer $buy_sitepad  If you want to renew SitePad with Softaculous bundle at 40% dicount for SitePad.  Values 1 to purchase SitePad bundle 0 if you do not want to purchase SitePad bundle
     * @return FALSE|array
     */
    public function buy($ip, $toadd, $servertype, $authemail, $autorenew, $buy_sitepad = 0)
    {
        $this->params['ca'] = 'softaculous_buy';
        $this->params['purchase'] = 1;
        $this->params['ips'] = $ip;
        $this->params['toadd'] = $toadd;
        $this->params['servertype'] = $servertype;
        $this->params['authemail'] = $authemail;
        $this->params['autorenew'] = $autorenew;
        $this->params['buy_sitepad'] = $buy_sitepad;
        return $this->req();
    }

    /**
     * reverses a transaction
     * NOTE: A refund can be claimed only within 7 days of buying/renewing the license
     *
     * @param mixed $actid The Action ID for which you want to claim refund
     * @return FALSE|array
     */
    public function refund($actid)
    {
        $this->params['ca'] = 'softaculous_refund';
        $this->params['actid'] = $actid;
        return $this->req();
    }

    /**
     * gets a list of licenses
     *
     * NOTE: $key, $ipAddress, $expiry, $start, $len (i.e. All Paras) are Optional
     *  When nothing is specified a list of all your license will be returned.
     *
     * @param string $key (Optional) The License KEY to get the details of that particular License
     * @param string $ip (Optional) The Primary IP of a License to get the details of that particular License
     * @param string $expiry (Optional) To get a List of License that are expiring. Valid Options - 1 , 2 , 3 . Explanation is as follows: $expiry = 1; (All Expired License in your account)     $expiry = 2; (Expiring in 7 Days)  $expiry = 3; (Expiring in 15 Days)
     * @param int $start (Optional) The starting key to return from. e.g. If the result is 500 licenses and you wanted only from the 100th one then specify 99
     * @param int $len (Optional) The length to return from the start. e.g. If the result is 500 licenses and you wanted only from the 200 items after the 100th one then specify $start = 99 and $len = 200
     * @param string $email (Optional) The authorised email of the user for which  you want to get the list of licenses.
     * @return FALSE|array
     */
    public function licenses($key = '', $ip = '', $expiry = '', $start = 0, $len = 1000000, $email = '')
    {
        $this->params['ca'] = 'softaculous';
        $this->params['lickey'] = $key;
        $this->params['ips'] = $ip;
        $this->params['expiry'] = $expiry;
        $this->params['start'] = $start;
        $this->params['len'] = $len;
        $this->params['email'] = $email;
        return $this->req();
    }

    /**
     * remove license and its auto renewal
     * NOTE: 1) Either of $ipAddress, $key needs to be specified
     *	2) A cancel will not be allowed if you have a license expiring after MORE than a MONTH.
     *	3) Also a refund is not made when you cancel a license. You must first claim the refund using the refund() API
     *
     * @param string $key (Optional) The License KEY
     * @param string $ip (Optional) The Primary IP of the License
     * @param int $force (optional) Set this as 1 if you want to forcefully cancel the license without claiming a refund
     * @return FALSE|array
     */
    public function cancel($key = '', $ip = '', $force = 0)
    {
        $this->params['ca'] = 'softaculous_cancel';
        $this->params['lickey'] = $key;
        $this->params['licip'] = $ip;
        $this->params['cancel_license'] = 1;
        if (!empty($force)) {
            $this->params['force'] = 1;
        }
        return $this->req();
    }

    /**
     * refund license and then remove license and its auto renewal
     * NOTE: 1) Either of $ipAddress, $key needs to be specified
     *	2) A cancel will not be allowed if you have a license expiring after MORE than a MONTH.
     *	3) We will try to refund you if the license is purchased less than 7 days ago. And then we will cancel the license.
     *
     * @param string $key (Optional) The License KEY
     * @param string $ipAddress (Optional) The Primary IP of the License
     * @param int $force (optional) Set this as 1 if you want to forcefully cancel the license without claiming a refund if a refund is not eligible
     * @return bool|mixed
     */
    public function refund_and_cancel($key = '', $ip = '', $force = 0)
    {
        if (!empty($ip)) {
            // Search for a license
            $lic = $this->licenses('', $ip);
            // No licenes with this IP
            if (empty($lic['licenses'])) {
                $this->error[] = 'No Licenses found.';
                return false;
            }
            $my_lic = current(current($lic));
            $key = $my_lic['license'];
        }

        // No key to search for the logs or to cancel
        if (empty($key)) {
            $this->error[] = 'Please provide a License Key or a Valid IP.';
            return false;
        }

        // Lets get the logs
        $logs = $this->licenselogs($key);

        // Did we get any logs ?
        if (!empty($logs['actions'])) {
            foreach ($logs['actions'] as $k => $v) {
                // Is it a valid transaction ?
                if (($v['action'] != 'renew' && $v['action'] != 'new') || !empty($v['refunded'])) {
                    continue;
                }
                // Is it purchased within last 7 days
                if ((time() - $v['time'])/(24*60*60) < 7) {
                    $this->refund($v['actid']);
                }
            }
        }

        // Cancel the license
        return $this->cancel($key, '', $force);
    }

    /**
     * Edit the IPs of a License
     * NOTE: Either of $ipAddress, $key needs to be specified
     *
     * @param int $lid The License ID (NOT the license key) e.g. lid could be 1000
     * @param string|array $ips The list of IPs of the same VPS / Server. The first IP you enter will be the primary IP Address of the License. You can enter up to a maximum of 8 IP Address per license.
     * @return FALSE|array
     */
    public function editips($lid, $ips)
    {
        $this->params['ca'] = 'softaculous_showlicense';
        $this->params['lid'] = $lid;
        $this->params['ips[]'] = $ips;
        $this->params['editlicense'] = 1;
        return $this->req();
    }

    /**
     * Action Logs of a License
     * NOTE: The logs are returned in DESCENDING ORDER, meaning the latest logs will be return first.
     *
     * @param string $key The License KEY
     * @param int $limit The number of action logs to be retrieved
     * @param string $ipAddress The License IP
     * @return FALSE|array
     */
    public function licenselogs($key, $limit = 0, $ip = '')
    {
        $this->params['ca'] = 'softaculous_licenselogs';
        $this->params['key'] = $key;
        $this->params['licip'] = $ip;
        if (!empty($limit)) {
            $this->params['limit'] = $limit;
        }
        return $this->req();
    }

    /**
     * List the Auto Renewing Licenses
     * NOTE: $key, $ip, $start, $len (i.e. All Params) are Optional When nothing is specified
     *  a list of all your licenses under auto renewals will be returned.
     *
     * @param string $key (Optional) The License KEY to get the details of that particular License
     * @param string $ip (Optional) The Primary IP of a License to get the details of  that particular License
     * @param int $start (Optional) The starting key to return from. e.g. If the result is 500 licenses and you wanted only from the 100th one then specify 99
     * @param int $len (Optional) The length to return from the start. e.g. If the result is 500 licenses and you wanted only from the 200 items after the 100th one then specify $start = 99 and $len = 200
     * @return FALSE|array
     */
    public function autorenewals($key = '', $ip = '', $start = 0, $len = 1000000)
    {
        $this->params['ca'] = 'softaculous_renewals';
        $this->params['lickey'] = $key;
        $this->params['ips'] = $ip;
        $this->params['start'] = $start;
        $this->params['len'] = $len;
        return $this->req();
    }

    /**
     * Add Auto Renewals
     *
     * @param string $key The License KEY to get the details of that particular License
     * @return FALSE|array
     */
    public function addautorenewal($key = '')
    {
        $this->params['ca'] = 'softaculous_renewals';
        $this->params['addrenewal'] = 1;
        $this->params['lickey'] = $key;
        return $this->req();
    }

    /**
     * Remove Auto Renewals
     *
     * @param string $key The License KEY to get the details of that particular License
     * @return FALSE|array
     */
    public function removeautorenewal($key = '')
    {
        $this->params['ca'] = 'softaculous_renewals';
        $this->params['cancelrenewal'] = 1;
        $this->params['lickey'] = $key;
        return $this->req();
    }


    ////////////////////
    // WEBUZO Functions
    ////////////////////

    /**
     * To Buy or Renew a License
     *
     * @param string $ip The IP of the license to be Purchased or Renewed
     * @param string $toadd Time to extend. Valid extension e.g.  '1M' will extend by one months     '8M' will extend by eight months     '1Y' will extend by One year
     * @param int $servertype 1 for Dedicated and 2 for VPS
     * @param string $authemail When a new license is purchased an Authorisation email is required to verify the owner of the License or for reminders when the license is expiring. This is not required in case of renewals
     * @param integer $autorenew To be renewed Automatically before expiry. Values - 1 for TRUE    0 (i.e. any empty value) or 2 for FALSE     Emails will be sent when renewed.
     * @return FALSE|array
     */
    public function webuzo_buy($ip, $toadd, $servertype, $authemail, $autorenew)
    {
        $this->params['ca'] = 'webuzo_buy';
        $this->params['purchase'] = 1;
        $this->params['ips'] = $ip;
        $this->params['toadd'] = $toadd;
        $this->params['servertype'] = $servertype;
        $this->params['authemail'] = $authemail;
        $this->params['autorenew'] = $autorenew;
        return $this->req();
    }

    /**
     * reverses a transaction
     * NOTE: A refund can be claimed only within 7 days of buying/renewing the license
     *
     * @param string $actid The Action ID for which you want to claim refund
     * @return FALSE|array
     */
    public function webuzo_refund($actid)
    {
        $this->params['ca'] = 'webuzo_refund';
        $this->params['actid'] = $actid;
        return $this->req();
    }

    /**
     * gets a list of licenses
     * NOTE: $key, $ip, $expiry, $start, $len (i.e. All Paras) are Optional When nothing is specified a list of all your license will be returned.
     *
     * @param string $key (Optional) The License KEY to get the details of that particular License
     * @param string $ip (Optional) The Primary IP of a License to get the details of that particular License
     * @param string $expiry (Optional) To get a List of License that are expiring. Valid Options - 1 , 2 , 3 . Explanation is as follows:  $expiry = 1; (All Expired License in your account)    $expiry = 2; (Expiring in 7 Days)   $expiry = 3; (Expiring in 15 Days)
     * @param int $start (Optional) The starting key to return from. e.g. If the result is 500 licenses and you wanted only from  the 100th one then specify 99
     * @param int $len (Optional) The length to return from the start. e.g. If the result is 500 licenses and you wanted only from the 200 items after the 100th one then specify $start = 99 and $len = 200
     * @param string $email (Optional) The authorised email of the user for which you want to get the list of licenses.
     * @return FALSE|array
     */
    public function webuzo_licenses($key = '', $ip = '', $expiry = '', $start = 0, $len = 1000000, $email = '')
    {
        $this->params['ca'] = 'webuzo';
        $this->params['lickey'] = $key;
        $this->params['ips'] = $ip;
        $this->params['expiry'] = $expiry;
        $this->params['start'] = $start;
        $this->params['len'] = $len;
        $this->params['email'] = $email;
        return $this->req();
    }

    /**
     * remove license and its auto renewal
     * NOTE: 1) Either of $ip, $key needs to be specified
     * 	2) A cancel will not be allowed if you have a license expiring after MORE than a MONTH.
     * 	3) Also a refund is not made when you cancel a license. You must first claim the refund using the refund() API
     *
     * @param string $key (Optional) The License KEY
     * @param string $ip (Optional) The Primary IP of the License
     * @param int $force (Optional) Set this as 1 if you want to forcefully cancel the license without claiming a refund
     * @return FALSE|array
     */
    public function webuzo_cancel($key = '', $ip = '', $force = 0)
    {
        $this->params['ca'] = 'webuzo_cancel';
        $this->params['lickey'] = $key;
        $this->params['licip'] = $ip;
        $this->params['cancel_license'] = 1;
        if (!empty($force)) {
            $this->params['force'] = 1;
        }
        return $this->req();
    }

    /**
     * refund webuzo license and then remove webuzo license and its auto renewal
     * NOTE: 1) Either of $ip, $key needs to be specified
     *	2) A cancel will not be allowed if you have a license expiring after MORE than a MONTH.
     * 	3) We will try to refund you if the license is purchased less than 7 days ago. And then we will cancel the license.
     *
     * @param string $key (Optional) The License KEY
     * @param string $ip (Optional) The Primary IP of the License
     * @param int $force (Optional) Set this as 1 if you want to forcefully cancel the license without claiming a refund if a refund is not eligible
     * @return bool|mixed
     */
    public function webuzo_refund_and_cancel($key = '', $ip = '', $force = 0)
    {
        if (!empty($ip)) {
            // Search for a license
            $lic = $this->webuzo_licenses('', $ip);
            // No licenes with this IP
            if (empty($lic['licenses'])) {
                $this->error[] = 'No Licenses found.';
                return false;
            }
            $my_lic = current(current($lic));
            $key = $my_lic['license'];
        }

        // No key to search for the logs or to cancel
        if (empty($key)) {
            $this->error[] = 'Please provide a License Key or a Valid IP.';
            return false;
        }

        // Lets get the logs
        $logs = $this->webuzo_licenselogs($key);

        // Did we get any logs ?
        if (!empty($logs['actions'])) {
            foreach ($logs['actions'] as $k => $v) {
                // Is it a valid transaction ?
                if (($v['action'] != 'renew' && $v['action'] != 'new') || !empty($v['refunded'])) {
                    continue;
                }
                // Is it purchased within last 7 days
                if ((time() - $v['time'])/(24*60*60) < 7) {
                    $this->webuzo_refund($v['actid']);
                }
            }
        }

        // Cancel the license
        return $this->webuzo_cancel($key, '', $force);
    }

    /**
     * Edit the IPs of a License
     * NOTE: Either of $ip, $key needs to be specified
     *
     * @param $lid The License ID (NOT the license key) e.g. lid could be 1000
     * @param $ips The IP (SINGLE IP ONLY) of the VPS / Server. Unlike Softaculous only one IP is allowed here
     * @return FALSE|array
     */
    public function webuzo_editips($lid, $ips)
    {
        $this->params['ca'] = 'webuzo_showlicense';
        $this->params['lid'] = $lid;
        $this->params['ips'] = $ips;
        $this->params['editlicense'] = 1;
        return $this->req();
    }

    /**
     * Action Logs of a License
     * NOTE: The logs are returned in DESCENDING ORDER, meaning the latest logs will be return first.
     *
     * @param string $key The License KEY
     * @param int $limit The number of action logs to be retrieved
     * @param string $ip The License IP
     * @return FALSE|array
     */
    public function webuzo_licenselogs($key, $limit = 0, $ip = '')
    {
        $this->params['ca'] = 'webuzo_licenselogs';
        $this->params['key'] = $key;
        $this->params['licip'] = $ip;
        if (!empty($limit)) {
            $this->params['limit'] = $limit;
        }
        return $this->req();
    }

    /**
     * List the Auto Renewing Licenses
     * NOTE: $key, $ip, $start, $len (i.e. All Params) are Optional When nothing is specified
     * a list of all your licenses under auto renewals will be returned.
     *
     * @param string $key (Optional) The License KEY to get the details of that particular License
     * @param string $ip (Optional) The Primary IP of a License to get the details of that particular License
     * @param int $start (Optional) The starting key to return from. e.g. If the result is 500 licenses and you wanted only from the 100th one then specify 99
     * @param int $len (Optional) The length to return from the start. e.g. If the result is 500 licenses and you wanted only from the 200 items after the 100th one then specify $start = 99 and $len = 200
     * @return FALSE|array
     */
    public function webuzo_autorenewals($key = '', $ip = '', $start = 0, $len = 1000000)
    {
        $this->params['ca'] = 'webuzo_renewals';
        $this->params['lickey'] = $key;
        $this->params['ips'] = $ip;
        $this->params['start'] = $start;
        $this->params['len'] = $len;
        return $this->req();
    }

    /**
     * Add Auto Renewals
     *
     * @param string $key The License KEY that has to be added to Auto Renewal
     * @return FALSE|array
     */
    public function webuzo_addautorenewal($key = '')
    {
        $this->params['ca'] = 'webuzo_renewals';
        $this->params['addrenewal'] = 1;
        $this->params['lickey'] = $key;
        return $this->req();
    }

    /**
     * Remove Auto Renewals\
     *
     * @param string $key The License KEY that has to be removed from Auto Renewal
     * @return FALSE|array
     */
    public function webuzo_removeautorenewal($key = '')
    {
        $this->params['ca'] = 'webuzo_renewals';
        $this->params['cancelrenewal'] = 1;
        $this->params['lickey'] = $key;
        return $this->req();
    }

    /**
     * Webuzo Trial
     *
     * @param $ip The IP that has to be licensed with a TRIAL License
     * @param $servertype Whether its a VPS or a Dedicated Server License
     * @return FALSE|array
     */
    public function webuzotrial($ip, $servertype)
    {
        $this->params['ca'] = 'webuzotrial';
        $this->params['ips'] = $ip;
        $this->params['type'] = $servertype;
        $this->params['gettrial'] = 1;
        return $this->req();
    }

    //////////////////////////
    // Virtualizor Functions
    //////////////////////////

    /**
     * To Buy or Renew a Virtualizor License
     *
     * @param string $ip = The IP of the license to be Purchased or Renewed
     * @param string $toadd Time to extend. Valid extension e.g.  - '1M' will extend by one months - '8M' will extend by eight months - '1Y' will extend by One year
     * @param int $autorenew To be renewed Automatically before expiry.  Values - 1 for TRUE    0 for FALSE.
     * @return FALSE|array
     */
    public function virt_buy($ip, $toadd, $autorenew)
    {
        $this->params['ca'] = 'virtualizor_buy';
        $this->params['purchase'] = 1;
        $this->params['ips'] = $ip;
        $this->params['toadd'] = $toadd;
        $this->params['autorenew'] = $autorenew;
        return $this->req();
    }

    /**
     * reverses a Virtualizor transaction
     * NOTE: A refund can be claimed only within 7 days of buying/renewing the license
     *
     * @param $actid The Action ID for which you want to claim refund
     * @return FALSE|array
     */
    public function virt_refund($actid)
    {
        $this->params['ca'] = 'virtualizor_refund';
        $this->params['actid'] = $actid;
        return $this->req();
    }

    /**
     * gets a list of Virtualizor licenses
     * NOTE: $key, $ip, $expiry, $start, $len (i.e. All Paras) are Optional When nothing
     * is specified a list of all your license will be returned.
     *
     * @param string $key (Optional) The License KEY to get the details of that particular License
     * @param string $ip (Optional) The Primary IP of a License to get the details of that particular License
     * @param string $expiry (Optional) To get a List of License that are expiring. Valid Options - 1 , 2 , 3 . Explanation is as follows: $expiry = 1; (All Expired License in your account) $expiry = 2; (Expiring in 7 Days) $expiry = 3; (Expiring in 15 Days)
     * @param int $start
     * @param int $len (Optional) The length to return from the start. e.g. If the result is 500 licenses and you wanted only from the 200 items after the 100th one then specify $start = 99 and $len = 200
     * @param string $email
     * @return FALSE|array
     */
    public function virt_licenses($key = '', $ip = '', $expiry = '', $start = 0, $len = 1000000, $email = '')
    {
        $this->params['ca'] = 'virtualizor';
        $this->params['lickey'] = $key;
        $this->params['ips'] = $ip;
        $this->params['expiry'] = $expiry;
        $this->params['start'] = $start;
        $this->params['len'] = $len;
        $this->params['email'] = $email;
        return $this->req();
    }

    /**
     * remove Virtualizor license and its auto renewwed
     * NOTE: 1) $key needs to be specified
     *       2) A cancel will not be allowed if you have a license expiring
     *	      <br> &after MORE than a MONTH.
     *       3) Also a refund is not made when you cancel a license. You must first claim the refund using the refund() API
     *
     * @param string $key The License KEY
     * @return FALSE|array
     */
    public function virt_remove($key, $force = 0)
    {
        $this->params['ca'] = 'virtualizor_cancel';
        $this->params['lickey'] = $key;
        $this->params['cancel_license'] = 1;
        if (!empty($force)) {
            $this->params['force'] = 1;
        }
        return $this->req();
    }

    /**
     * NOTE: 1) Either of $ip, $key needs to be specified
     *		 2) A cancel will not be allowed if you have a license expiring
     *				after MORE than a MONTH.
     *		 3) We will try to refund you if the license is purchased less than 7 days ago. And then we will cancel the license.
     * refund virtualizor license and then remove virtualizor license and its auto renewal
     * @param string $key (Optional) The License KEY
     * @param string $ip (Optional) The Primary IP of the License
     * @param int $force (Optional) Set this as 1 if you want to forcefully remove the license without claiming a refund if a refund is not eligible
     * @return bool|mixed
     */
    public function virt_refund_and_cancel($key = '', $ip = '', $force = 0)
    {
        if (!empty($ip)) {
            // Search for a license
            $lic = $this->virt_licenses('', $ip);
            // No licenes with this IP
            if (empty($lic['licenses'])) {
                $this->error[] = 'No Licenses found.';
                return false;
            }
            $my_lic = current(current($lic));
            $key = $my_lic['license'];
        }

        // No key to search for the logs or to cancel
        if (empty($key)) {
            $this->error[] = 'Please provide a License Key or a Valid IP.';
            return false;
        }

        // Lets get the logs
        $logs = $this->virt_licenselogs($key);

        // Did we get any logs ?
        if (!empty($logs['actions'])) {
            foreach ($logs['actions'] as $k => $v) {
                // Is it a valid transaction ?
                if (($v['action'] != 'renew' && $v['action'] != 'new') || !empty($v['refunded'])) {
                    continue;
                }
                // Is it purchased within last 7 days
                if ((time() - $v['time'])/(24*60*60) < 7) {
                    $this->virt_refund($v['actid']);
                }
            }
        }

        // Cancel the license
        return $this->virt_remove($key, $force);
    }

    /**
     * Edit the IPs of a Virtualizor License
     * NOTE: Either of $ip, $key needs to be specified
     *
     * @param $lid The License ID (NOT the license key) e.g. lid could be 1000
     * @param $ips The NEW IP of the server
     * @return FALSE|array
     */
    public function virt_editips($lid, $ips)
    {
        $this->params['ca'] = 'virtualizor_showlicense';
        $this->params['lid'] = $lid;
        $this->params['ips'] = $ips;
        $this->params['editlicense'] = 1;
        return $this->req();
    }

    /**
     * Action Logs of a Virtualizor License
     *		NOTE: The logs are returned in DESCENDING ORDER, meaning the latest logs will be return first.
     *
     * @param $key The License KEY
     * @param int $limit The number of action logs to be retrieved
     * @param string $ip The License IP
     * @return FALSE|array
     */
    public function virt_licenselogs($key, $limit = 0, $ip = '')
    {
        $this->params['ca'] = 'virtualizor_licenselogs';
        $this->params['key'] = $key;
        $this->params['licip'] = $ip;
        if (!empty($limit)) {
            $this->params['limit'] = $limit;
        }
        return $this->req();
    }

    /**
     * List the Auto Renewing Virtualizor Licenses
     * NOTE: $key, $ip, $start, $len (i.e. All Params) are Optional When nothing is
     * 		specified a list of all your licenses under auto renewals will be returned.
     *
     * @param string $key (Optional) The License KEY to get the details of that particular License
     * @param string $ip (Optional) The Primary IP of a License to get the details of that particular License
     * @param int $start (Optional) The starting key to return from. e.g. If the result is 500 licenses and you wanted only from the 100th one then specify 99
     * @param int $len (Optional) The length to return from the start. e.g. If the result is 500 licenses and you wanted only from the 200 items after the 100th one then specify $start = 99 and $len = 200
     * @return FALSE|array
     */
    public function virt_renewals($key = '', $ip = '', $start = 0, $len = 1000000)
    {
        $this->params['ca'] = 'virtualizor_renewals';
        $this->params['lickey'] = $key;
        $this->params['ips'] = $ip;
        $this->params['start'] = $start;
        $this->params['len'] = $len;
        return $this->req();
    }

    /**
     * Add Virtualizor Auto Renewals
     *
     * @param string $key The License KEY that has to be added to Auto Renewal
     * @return FALSE|array
     */
    public function virt_addautorenewal($key = '')
    {
        $this->params['ca'] = 'virtualizor_renewals';
        $this->params['addrenewal'] = 1;
        $this->params['lickey'] = $key;
        return $this->req();
    }

    /**
     * Remove Virtualizor Auto Renewals
     *
     * @param string $key The License KEY that has to be removed from Auto Renewal
     * @return FALSE|array
     */
    public function virt_removeautorenewal($key = '')
    {
        $this->params['ca'] = 'virtualizor_renewals';
        $this->params['cancelrenewal'] = 1;
        $this->params['lickey'] = $key;
        return $this->req();
    }


    ////////////////////
    // SiteMush Functions
    ////////////////////

    /**
     * To Buy or Renew a License
     *
     * @param string $ip The IP of the license to be Purchased or Renewed
     * @param string $toadd Time to extend. Valid extension e.g. - '1M' will extend by one months - '3M' will extend by three months - '6M' will extend by six months - '9M' will extend by nine months - '1Y' will extend by One year - '2Y' will extend by Two year - '3Y' will extend by Three year
     * @param int $autorenew To be renewed Automatically before expiry. Values - 1 for TRUE   0 (i.e. any empty value) or 2 for FALSE    Emails will be sent when renewed.
     * @return FALSE|array
     */
    public function sitemush_buy($ip, $toadd, $autorenew)
    {
        $this->params['ca'] = 'sitemush_buy';
        $this->params['purchase'] = 1;
        $this->params['ips'] = $ip;
        $this->params['toadd'] = $toadd;
        $this->params['autorenew'] = $autorenew;
        return $this->req();
    }

    /**
     * reverses a transaction
     * NOTE: A refund can be claimed only within 7 days of buying/renewing the license
     *
     * @param mixed $actid The Action ID for which you want to claim refund
     * @return array|FALSE
     */
    public function sitemush_refund($actid)
    {
        $this->params['ca'] = 'sitemush_refund';
        $this->params['actid'] = $actid;
        return $this->req();
    }

    /**
     * gets a list of SiteMush licenses
     * NOTE: $key, $ip, $expiry, $start, $len (i.e. All Paras) are Optional When
     * nothing is specified a list of all your license will be returned.
     *
     * @param string $key       (Optional) The License KEY to get the details of that particular License
     * @param string $ip (Optional) The Primary IP of a License to get the details of that particular License
     * @param string $expiry    (Optional) To get a List of License that are expiring. Valid Options - 1 , 2 , 3 . Explanation is as follows:  $expiry = 1; (All Expired License in your account)   $expiry = 2; (Expiring in 7 Days)  $expiry = 3; (Expiring in 15 Days)
     * @param int    $start     (Optional) The starting key to return from. e.g. If the result is 500 licenses and you wanted only from the 100th one then specify 99
     * @param int    $len       (Optional) The length to return from the start. e.g.  If the result is 500 licenses and you wanted only from the 200 items after the 100th one then specify  $start = 99 and $len = 200
     * @param string $email     (Optional) The authorised email of the user for which you want to get the list of licenses.
     * @return array|FALSE
     */
    public function sitemush_licenses($key = '', $ip = '', $expiry = '', $start = 0, $len = 1000000, $email = '')
    {
        $this->params['ca'] = 'sitemush';
        $this->params['lickey'] = $key;
        $this->params['ips'] = $ip;
        $this->params['expiry'] = $expiry;
        $this->params['start'] = $start;
        $this->params['len'] = $len;
        $this->params['email'] = $email;
        return $this->req();
    }

    /**
     * remove SiteMush license and its auto renewal
     * NOTE: 1) $key needs to be specified
     *              2) A cancel will not be allowed if you have a license expiring after MORE than a MONTH.
     *              3) Also a refund is not made when you cancel a license. You must first claim the refund using the sitemushRefund() API
     *
     * @param string $key The License KEY
     * @return array|FALSE
     */
    public function sitemush_remove($key, $force = 0)
    {
        $this->params['ca'] = 'sitemush_cancel';
        $this->params['lickey'] = $key;
        $this->params['cancel_license'] = 1;
        if (!empty($force)) {
            $this->params['force'] = 1;
        }
        return $this->req();
    }

    /**
     * refund SiteMush license and then remove SiteMush license and its auto renewal
     * NOTE: 1) Either of $ip, $key needs to be specified
     *              2) A cancel will not be allowed if you have a license expiring after MORE than a MONTH.
     *              3) We will try to refund you if the license is purchased less than 7 days ago. And then we will cancel the license.
     *
     * @param string $key       (Optional) The License KEY
     * @param string $ip (Optional) The Primary IP of the License
     * @param int $force (Optional) Set this as 1 if you want to forcefully remove the license without claiming a refund if a refund is not eligible
     * @return array|bool|FALSE
     */
    public function sitemush_refund_and_cancel($key = '', $ip = '', $force = 0)
    {
        if (!empty($ip)) {
            // Search for a license
            $lic = $this->sitemush_licenses('', $ip);
            // No licenes with this IP
            if (empty($lic['licenses'])) {
                $this->error[] = 'No Licenses found.';
                return false;
            }
            $my_lic = current(current($lic));
            $key = $my_lic['license'];
        }

        // No key to search for the logs or to cancel
        if (empty($key)) {
            $this->error[] = 'Please provide a License Key or a Valid IP.';
            return false;
        }

        // Lets get the logs
        $logs = $this->sitemush_licenselogs($key);

        // Did we get any logs ?
        if (!empty($logs['actions'])) {
            foreach ($logs['actions'] as $k => $v) {
                // Is it a valid transaction ?
                if (($v['action'] != 'renew' && $v['action'] != 'new') || !empty($v['refunded'])) {
                    continue;
                }
                // Is it purchased within last 7 days
                if ((time() - $v['time'])/(24*60*60) < 7) {
                    $this->sitemush_refund($v['actid']);
                }
            }
        }

        // Cancel the license
        return $this->sitemush_remove($key, $force);
    }

    /**
     * Edit the IPs of a SiteMush License
     * NOTE: Either of $ip, $key needs to be specified
     *
     * @param string|int   $lid The License ID (NOT the license key) e.g. lid could be 1000
     * @param string|array $ips The NEW IP of the server
     * @return array|FALSE
     */
    public function sitemush_editips($lid, $ips)
    {
        $this->params['ca'] = 'sitemush_showlicense';
        $this->params['lid'] = $lid;
        $this->params['ips'] = $ips;
        $this->params['editlicense'] = 1;
        return $this->req();
    }

    /**
     * Action Logs of a SiteMush License
     * NOTE: The logs are returned in DESCENDING ORDER, meaning the latest logs will be return first.
     *
     * @param string $key       The License KEY
     * @param int    $limit     The number of action logs to be retrieved
     * @param string $ip The License IP
     * @return array|FALSE
     */
    public function sitemush_licenselogs($key, $limit = 0, $ip = '')
    {
        $this->params['ca'] = 'sitemush_licenselogs';
        $this->params['key'] = $key;
        $this->params['licip'] = $ip;
        if (!empty($limit)) {
            $this->params['limit'] = $limit;
        }
        return $this->req();
    }

    /**
     * List the Auto Renewing SiteMush Licenses
     * NOTE: $key, $ipAddress, $start, $len (i.e. All Params) are Optional When nothing
     * is specified a list of all your licenses under auto renewals will be returned.
     *
     * @param string $key       (Optional) The License KEY to get the details of that particular License
     * @param string $ip (Optional) The Primary IP of a License to get the details of that particular License
     * @param int    $start     (Optional) The starting key to return from. e.g. If the result is 500 licenses and you wanted only from  the 100th one then specify 99
     * @param int    $len       (Optional) The length to return from the start. e.g. If the result is 500 licenses and you wanted only from  the 200 items after the 100th one then specify $start = 99 and $len = 200
     * @return array|FALSE
     */
    public function sitemush_renewals($key = '', $ip = '', $start = 0, $len = 1000000)
    {
        $this->params['ca'] = 'sitemush_renewals';
        $this->params['lickey'] = $key;
        $this->params['ips'] = $ip;
        $this->params['start'] = $start;
        $this->params['len'] = $len;
        return $this->req();
    }

    /**
     * Add SiteMush Auto Renewals
     *
     * @param string $key The License KEY that has to be added toAuto Renewal
     * @return array|FALSE
     */
    public function sitemush_addautorenewal($key = '')
    {
        $this->params['ca'] = 'sitemush_renewals';
        $this->params['addrenewal'] = 1;
        $this->params['lickey'] = $key;
        return $this->req();
    }

    /**
     * Remove SiteMush Auto Renewals
     *
     * @param string $key The License KEY that has to be removed from Auto Renewal
     * @return array|FALSE
     */
    public function sitemush_removeautorenewal($key = '')
    {
        $this->params['ca'] = 'sitemush_renewals';
        $this->params['cancelrenewal'] = 1;
        $this->params['lickey'] = $key;
        return $this->req();
    }

    /**
     * Details of an invoice
     *
     * @param int $invoid The Invoice ID the details of which you want to see. If nothing is specified i.e. invoid = 0 then all unbilled transactions for the current month will be returned
     * @return FALSE|array
     */
    public function invoicedetails($invoid = 0)
    {
        $this->params['ca'] = 'invoicedetails';
        $this->params['invoid'] = $invoid;
        return $this->req();
    }

    /**
     * displays the response
     *
     * @param mixed $response the response from an a function/api command
     * @return void
     */
    public function r($r)
    {
        if (empty($r)) {
            $r = $this->error;
        }
        myadmin_log('licenses', 'info', '<pre>'.json_encode($response).'</pre>', __LINE__, __FILE__);
    }
}

// Converts an Array to XML
class ArrayToXML
{
    /**
     * The main function for converting to an XML document.
     * Pass in a multi dimensional array and this recrusively loops through and builds up an XML document.
     *
     * @param array $data
     * @param string $rootNodeName - what you want the root node to be - defaultsto data.
     * @param SimpleXMLElement $xml - should only be used recursively
     * @return string XML
     */
    // Array to XML
    public function toXML($data, $rootNodeName = 'ResultSet', $xml=null)
    {

        // turn off compatibility mode as simple xml throws a wobbly if you don't.
        if (ini_get('zend.ze1_compatibility_mode') == 1) {
            ini_set('zend.ze1_compatibility_mode', 0);
        }
        if (is_null($xml)) { //$xml = simplexml_load_string( "" );
            $xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$rootNodeName />");
        }

        // loop through the data passed in.
        foreach ($data as $key => $value) {
            $numeric = false;

            // no numeric keys in our xml please!
            if (is_numeric($key)) {
                $numeric = 1;
                $key = $rootNodeName;
            }

            // delete any char not allowed in XML element names
            $key = preg_replace('/[^a-z0-9\-\_\.\:]/i', '', $key);

            // if there is another array found recrusively call this function
            if (is_array($value)) {
                $node = ArrayToXML::isAssoc($value) || $numeric ? $xml->addChild($key) : $xml;

                // recrusive call.
                if ($numeric) {
                    $key = 'anon';
                }
                ArrayToXML::toXml($value, $key, $node);
            } else {

                // add single node.
                $value = htmlentities($value);
                $xml->addChild($key, $value);
            }
        }

        // pass back as XML
        return $xml->asXML();

        // if you want the XML to be formatted, use the below instead to return the XML
        //$doc = new DOMDocument('1.0');
        //$doc->preserveWhiteSpace = false;
        //$doc->loadXML( $xml->asXML() );
        //$doc->formatOutput = true;
        //return $doc->saveXML();
    }


    /**
     * Convert an XML document to a multi dimensional array
     * Pass in an XML document (or SimpleXMLElement object) and this recrusively loops through and builds a representative array
     *
     * @param string $xml - XML document - can optionally be a SimpleXMLElement object
     * @return array ARRAY
     */
    // XML to Array
    public function toArray($xml)
    {
        if (is_string($xml)) {
            $xml = new SimpleXMLElement($xml);
        }
        $children = $xml->children();
        if (!$children) {
            return (string) $xml;
        }
        $arr = [];
        foreach ($children as $key => $node) {
            $node = ArrayToXML::toArray($node);

            // support for 'anon' non-associative arrays
            if ($key == 'anon') {
                $key = count($arr);
            }

            // if the node is already set, put it into an array
            if (isset($arr[$key])) {
                if (!is_array($arr[$key]) || $arr[$key][0] == null) {
                    $arr[$key] = [ $arr[$key] ];
                }
                $arr[$key][] = $node;
            } else {
                $arr[$key] = $node;
            }
        }
        return $arr;
    }

    // determine if a variable is an associative array
    public function isAssoc($array)
    {
        return (is_array($array) && 0 !== count(array_diff_key($array, array_keys(array_keys($array)))));
    }
}

/**
 * Converts array to JSON
 *
 * @package      softaculous
 * @subpackage   json
 * @author       Pulkit Gupta
 * @param        array $arr An array that needs to be converted to JSON
 * @return       string The JSON string
 * @since     	 3.9
 */
function array2json($arr)
{
    if (function_exists('json_encode')) {
        return json_encode($arr);
    } //Lastest versions of PHP already has this functionality.
    $parts = [];
    $is_list = false;

    //Find out if the given array is a numerical array
    $keys = array_keys($arr);
    $max_length = count($arr)-1;
    if (($keys[0] == 0) and ($keys[$max_length] == $max_length)) {//See if the first key is 0 and last key is length - 1
        $is_list = true;
        for ($i=0; $i<count($keys); $i++) { //See if each key correspondes to its position
            if ($i != $keys[$i]) { //A key fails at position check.
                $is_list = false; //It is an associative array.
                break;
            }
        }
    }

    foreach ($arr as $key=>$value) {
        if (is_array($value)) { //Custom handling for arrays
            if ($is_list) {
                $parts[] = array2json($value);
            } /* :RECURSION: */
            else {
                $parts[] = '"' . $key . '":' . array2json($value);
            } /* :RECURSION: */
        } else {
            $str = '';
            if (!$is_list) {
                $str = '"' . $key . '":';
            }

            //Custom handling for multiple data types
            if (is_numeric($value)) {
                $str .= $value;
            } //Numbers
            elseif ($value === false) {
                $str .= 'false';
            } //The booleans
            elseif ($value === true) {
                $str .= 'true';
            } else {
                $str .= '"' . addslashes($value) . '"';
            } //All other things
            // :TODO: Is there any more datatype we should be in the lookout for? (Object?)

            $parts[] = $str;
        }
    }
    $json = implode(',', $parts);

    if ($is_list) {
        return '[' . $json . ']';
    }//Return numerical JSON
    return '{' . $json . '}';//Return associative JSON
}

/*

////////////////////////
// SOFTACULOUS Examples
////////////////////////

// Initiate the class with your NOC Account Credentials
$noc = new SoftaculousNOC('username', 'password');

// Buy / renew a License
$noc->r($noc->buy('174.37.113.98', '1M', 1, 'test@test.com', 1));

// Refund a Transaction
$noc->r($noc->refund(100));

// Refund a Transaction and then cancel license
$noc->r($noc->refund_and_cancel('88888-88888-88888-88888-88888'));

// Refund a Transaction and then cancel license by IP
$noc->r($noc->refund_and_cancel('', '198.198.198.198'));

// Get me all my licenses
$noc->r($noc->licenses());
// Search for a license by IP
$noc->r($noc->licenses('', '198.198.198.198'));
// Search for a license by KEY
$noc->r($noc->licenses('88888-88888-88888-88888-88888'));
// All Expired Licenses
$noc->r($noc->licenses('', '', 1));
// Expiring in next 7 Days
$noc->r($noc->licenses('', '', 2));
// Expiring in next 15 Days
$noc->r($noc->licenses('', '', 3));

// Cancel a License
$noc->r($noc->cancel('88888-88888-88888-88888-88888')); // Cancel by License Key
$noc->r($noc->cancel('', '198.198.198.198')); // Cancel by IP

// EDIT IP of a License
$noc->r($noc->editips(1000, '198.198.198.198')); // LID and new IP Address

// Get the Action/Activity Logs of a License
$noc->r($noc->licenselogs('88888-88888-88888-88888-88888'));

// Get the Action/Activity Logs of a License by IP
$noc->r($noc->licenselogs('', 0, '198.198.198.198'));

// Get me all auto renewing Licenses
$noc->r($noc->autorenewals());

// Start auto renewing a license
$noc->r($noc->addautorenewal('88888-88888-88888-88888-88888'));

// Stop auto renewing a license
$noc->r($noc->removeautorenewal('88888-88888-88888-88888-88888'));

*/

/*

////////////////////
// WEBUZO Examples
////////////////////

// Initiate the class with your NOC Account Credentials
$noc = new SoftaculousNOC('username', 'password');

// Buy / renew a License
$noc->r($noc->webuzo_buy('174.37.113.98', '1M', 1, 'test@test.com', 1));

// Refund a Transaction
$noc->r($noc->webuzo_refund(100));

// Refund a Transaction and then cancel webuzo license
$noc->r($noc->webuzo_refund_and_cancel('88888-88888-88888-88888-88888'));

// Refund a Transaction and then cancel webuzo license by IP
$noc->r($noc->webuzo_refund_and_cancel('', '198.198.198.198'));

// Get me all my licenses
$noc->r($noc->webuzo_licenses());
// Search for a license by IP
$noc->r($noc->webuzo_licenses('', '198.198.198.198'));
// Search for a license by KEY
$noc->r($noc->webuzo_licenses('webuzo-88888-88888-88888-88888'));
// All Expired Licenses
$noc->r($noc->webuzo_licenses('', '', 1));
// Expiring in next 7 Days
$noc->r($noc->webuzo_licenses('', '', 2));
// Expiring in next 15 Days
$noc->r($noc->webuzo_licenses('', '', 3));

// Cancel a License
$noc->r($noc->webuzo_cancel('webuzo-88888-88888-88888-88888')); // Cancel by License Key
$noc->r($noc->webuzo_cancel('', '198.198.198.198')); // Cancel by IP

// EDIT IP of a License
$noc->r($noc->webuzo_editips(1000, '198.198.198.198')); // LID and new IP Address

// Get the Action/Activity Logs of a License
$noc->r($noc->webuzo_licenselogs('webuzo-88888-88888-88888-88888'));

// Get the Action/Activity Logs of a License by IP
$noc->r($noc->webuzo_licenselogs('', 0, '198.198.198.198'));

// Get me all auto renewing Licenses
$noc->r($noc->webuzo_autorenewals());

// Start auto renewing a license
$noc->r($noc->webuzo_addautorenewal('webuzo-88888-88888-88888-88888'));

// Stop auto renewing a license
$noc->r($noc->webuzo_removeautorenewal('webuzo-88888-88888-88888-88888'));

// Get a Trial license
$noc->r($noc->webuzotrial('198.198.198.198', 1));

*/

/*
////////////////////////
// VIRTUALIZOR Examples
////////////////////////

// Buy / renew a License
$noc->r($noc->virt_buy('198.198.198.198', '1M', 1));

// Refund a Transaction
$noc->r($noc->virt_refund(100));

// Refund a Transaction and then cancel Virtualizor license
$noc->r($noc->virt_refund_and_cancel('88888-88888-88888-88888-88888'));

// Refund a Transaction and then cancel Virtualizor license by IP
$noc->r($noc->virt_refund_and_cancel('', '198.198.198.198'));

// Get me all my licenses
$noc->r($noc->virt_licenses());
// Search for a license by IP
$noc->r($noc->virt_licenses('', '198.198.198.198'));
// Search for a license by KEY
$noc->r($noc->virt_licenses('88888-88888-88888-88888-88888'));
// All Expired Licenses
$noc->r($noc->virt_licenses('', '', 1));
// Expiring in next 7 Days
$noc->r($noc->virt_licenses('', '', 2));
// Expiring in next 15 Days
$noc->r($noc->virt_licenses('', '', 3));

// Cancel a License
$noc->r($noc->virt_remove('88888-88888-88888-88888-88888')); // Remove by License Key

// Edit the IP of a license
$noc->r($noc->virt_editips(1, '111.111.111.111'));

// Get the Action/Activity Logs of a License
$noc->r($noc->virt_licenselogs('88888-88888-88888-88888-88888'));

// Get the Action/Activity Logs of a License by IP
$noc->r($noc->virt_licenselogs('', 0, '111.111.111.111'));

// Get me all auto renewing Licenses
$noc->r($noc->virt_renewals());

// Start auto renewing a license
$noc->r($noc->virt_addautorenewal('88888-88888-88888-88888-88888'));

// Stop auto renewing a license
$noc->r($noc->virt_removeautorenewal('88888-88888-88888-88888-88888'));

*/

/*

////////////////////////
// SITEMUSH Examples
////////////////////////

// Initiate the class with your NOC Account Credentials
$noc = new SoftaculousNOC('username', 'password');

// Buy / renew a License
$noc->r($noc->sitemush_buy('188.188.188.188', '1M', 1));

// Refund a Transaction
$noc->r($noc->sitemush_refund(100));

// Refund a transaction and then cancel the license
$noc->r($noc->sitemush_refund_and_cancel('SMUSH-88888-88888-88888-88888'));

// Refund a transaction and then cancel the license by IP
$noc->r($noc->sitemush_refund_and_cancel('', '198.198.198.198'));

// Get me all my licenses
$noc->r($noc->sitemush_licenses());
// Search for a license by IP
$noc->r($noc->sitemush_licenses('', '198.198.198.198'));
// Search for a license by KEY
$noc->r($noc->sitemush_licenses('SMUSH-88888-88888-88888-88888'));
// Search licenes by email
$noc->r($noc->sitemush_licenses('', '', '', '', '', 'a@a.com'));
// All Expired Licenses
$noc->r($noc->sitemush_licenses('', '', 1));
// Expiring in next 7 Days
$noc->r($noc->sitemush_licenses('', '', 2));
// Expiring in next 15 Days
$noc->r($noc->sitemush_licenses('', '', 3));

// Cancel a License
$noc->r($noc->sitemush_remove('SMUSH-88888-88888-88888-88888')); // Cancel by License Key

// EDIT IP of a License
$noc->r($noc->sitemush_editips(1000, '198.198.198.198')); // LID and new IP Address

// Get the Action/Activity Logs of a License
$noc->r($noc->sitemush_licenselogs('SMUSH-88888-88888-88888-88888'));

// Get the Action/Activity Logs of a License by IP
$noc->r($noc->sitemush_licenselogs('', 0, '188.188.188.188'));

// Get me all auto renewing Licenses
$noc->r($noc->sitemush_renewals());

// Start auto renewing a license
$noc->r($noc->sitemush_addautorenewal('SMUSH-88888-88888-88888-88888'));

// Stop auto renewing a license
$noc->r($noc->sitemush_removeautorenewal('SMUSH-88888-88888-88888-88888'));

*/

/*

////////////////////
// INVOICE Details
////////////////////

// Get all transactions of a Invoice
$noc->r($noc->invoicedetails(100));
// Get all unbilled transactions for the current month
$noc->r($noc->invoicedetails());

*/

/*

//////////////////
// Convert output
//////////////////

// You can convert the output to XML
$result = $noc->licenses();
echo ArrayToXML::toXML($result);

// You can also convert the data to Jason
$result = $noc->licenses();
echo array2json($result);

*/

/*

////////////////////
// ERROR Handling
////////////////////

// After any query, the class variable 'error' will be filled up IF there was an error

$noc = new SoftaculousNOC('username', 'password');

// Buy / renew a License
$result = $noc->webuzo_buy('174.37.113.98', '1M', 1, 'test@test.com', 1);

// Check for any error
if(empty($noc->error)){
    // Everything went perfect
}else{
    // Dump the error
    print_r($noc->error);
}

*/

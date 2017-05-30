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

namespace Detain\MyAdminSoftaculous;

//use Detain\Softaculous\Softaculous;
use Symfony\Component\EventDispatcher\GenericEvent;

/*
 * $noc = new SOFT_NOC(SOFTACULOUS_USERNAME, SOFTACULOUS_PASSWORD);
 * // Buy / renew a License
 * $noc->r($noc->buy('174.37.113.98', '1M', 1, 'test@test.com', 1));
 * // Refund a Transaction
 * $noc->r($noc->refund(100));
 * // Get me all my licenses
 * $noc->r($noc->licenses());
 * // Search for a license by IP
 * $noc->r($noc->licenses('', '198.198.198.198'));
 * // Search for a license by KEY
 * $noc->r($noc->licenses('88888-88888-88888-88888-88888'));
 * // All Expired Licenses
 * $noc->r($noc->licenses('', '', 1));
 * // Expiring in next 7 Days
 * $noc->r($noc->licenses('', '', 2));
 * // Expiring in next 15 Days
 * $noc->r($noc->licenses('', '', 3));
 * // Get all transactions of a Invoice
 * $noc->r($noc->invoicedetails(100));
 * // Get all unbilled transactions for the current month
 * $noc->r($noc->invoicedetails());
 * // Cancel a License
 * $noc->r($noc->cancel('88888-88888-88888-88888-88888')); // Cancel by License Key
 * $noc->r($noc->cancel('', '198.198.198.198')); // Cancel by IP
 * // EDIT IP of a License
 * $noc->r($noc->editips(1000, '198.198.198.198')); // LID and new IP Address
 * // Get the Action/Activity Logs of a License
 * $noc->r($noc->licenselogs('88888-88888-88888-88888-88888'));
 */

class Plugin {

	public function __construct() {
	}

	public static function Activate(GenericEvent $event) {
		$license = $event->getSubject();
		if ($event['category'] == SERVICE_TYPES_SOFTACULOUS) {
			myadmin_log('licenses', 'info', 'Softaculous Activation', __LINE__, __FILE__);
			function_requirements('activate_softaculous');
			activate_softaculous($license->get_ip(), $event['field1'], $event['email']);
			$event->stopPropagation();
		}
	}

	public static function Deactivate(GenericEvent $event) {
		$license = $event->getSubject();
		if ($event['category'] == SERVICE_TYPES_SOFTACULOUS) {
			myadmin_log('licenses', 'info', 'Softaculous Deactivation', __LINE__, __FILE__);
			function_requirements('deactivate_softaculous');
			deactivate_softaculous($license->get_ip());
			$event->stopPropagation();
		}
	}

	public static function ChangeIp(GenericEvent $event) {
		if ($event['category'] == SERVICE_TYPES_SOFTACULOUS) {
			$license = $event->getSubject();
			$settings = get_module_settings('licenses');
			myadmin_log('licenses', 'info', "IP Change - (OLD:".$license->get_ip().") (NEW:{$event['newip']})", __LINE__, __FILE__);
			function_requirements('get_softaculous_licenses');
			function_requirements('class.SOFT_NOC');
			$data = get_softaculous_licenses($license->get_ip());
			$lid = array_keys($data['licenses']);
			$lid = $lid[0];
			$noc = new \SOFT_NOC(SOFTACULOUS_USERNAME, SOFTACULOUS_PASSWORD);
			if ($noc->editips($lid[0], $event['newip']) !== false) {
				$GLOBALS['tf']->history->add($settings['TABLE'], 'change_ip', $event['newip'], $license->get_ip());
				$license->set_ip($event['newip'])->save();
				$return['status'] = 'ok';
				$return['status_text'] = 'The IP Address has been changed.';
			} else {
				$return['status'] = 'error';
				$return['status_text'] = 'Error occurred during deactivation.';
			}
			$event->stopPropagation();
		}
	}

	public static function Menu(GenericEvent $event) {
		// will be executed when the licenses.settings event is dispatched
		$menu = $event->getSubject();
		$module = 'licenses';
		if ($GLOBALS['tf']->ima == 'admin') {
			$menu->add_link($module.'api', 'choice=none.softaculous_list', 'whm/createacct.gif', 'List all Softaculous Licenses');
			$menu->add_link($module.'api', 'choice=none.webuzo_list', 'whm/createacct.gif', 'List all Webuzo Licenses');
		}
	}

	public static function Requirements(GenericEvent $event) {
		// will be executed when the licenses.loader event is dispatched
		$loader = $event->getSubject();
		$loader->add_requirement('class.SOFT_NOC', '/licenses/SOFT_NOC.php');
		$loader->add_requirement('activate_softaculous', '/../vendor/detain/myadmin-softaculous-licensing/src/activate_softaculous.php');
		$loader->add_requirement('activate_webuzo', '/../vendor/detain/myadmin-softaculous-licensing/src/activate_webuzo.php');
		$loader->add_requirement('softaculous_list', '/../vendor/detain/myadmin-softaculous-licensing/src/softaculous_list.php');
		$loader->add_requirement('webuzo_list', '/../vendor/detain/myadmin-softaculous-licensing/src/webuzo_list.php');
		$loader->add_requirement('deactivate_softaculous', '/../vendor/detain/myadmin-softaculous-licensing/src/deactivate_softaculous.php');
		$loader->add_requirement('deactivate_webuzo', '/../vendor/detain/myadmin-softaculous-licensing/src/deactivate_webuzo.php');
		$loader->add_requirement('get_softaculous_licenses', '/../vendor/detain/myadmin-softaculous-licensing/src/get_softaculous_licenses.php');
		$loader->add_requirement('get_webuzo_licenses', '/../vendor/detain/myadmin-softaculous-licensing/src/get_webuzo_licenses.php');
	}

	public static function Settings(GenericEvent $event) {
		// will be executed when the licenses.settings event is dispatched
		$settings = $event->getSubject();
		$settings->add_text_setting('apisettings', 'softaculous_username', 'Softaculous Username:', 'Softaculous Username', $settings->get_setting('SOFTACULOUS_USERNAME'));
		$settings->add_text_setting('apisettings', 'softaculous_password', 'Softaculous Password:', 'Softaculous Password', $settings->get_setting('SOFTACULOUS_PASSWORD'));
		$settings->add_text_setting('apisettings', 'webuzo_username', 'Webuzo Username:', 'Webuzo Username', $settings->get_setting('WEBUZO_USERNAME'));
		$settings->add_text_setting('apisettings', 'webuzo_password', 'Webuzo Password:', 'Webuzo Password', $settings->get_setting('WEBUZO_PASSWORD'));
		$settings->add_dropdown_setting('stock', 'outofstock_licenses_softaculous', 'Out Of Stock Softaculous Licenses', 'Enable/Disable Sales Of This Type', $settings->get_setting('OUTOFSTOCK_LICENSES_SOFTACULOUS'), array('0', '1'), array('No', 'Yes', ));
	}

}

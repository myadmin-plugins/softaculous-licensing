<?php
/**
 * Softaculous Related Functionality
 *
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
 * $noc = new \Detain\MyAdminSoftaculous\SOFT_NOC(SOFTACULOUS_USERNAME, SOFTACULOUS_PASSWORD);
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

	public static $name = 'Softaculous Licensing';
	public static $description = 'Allows selling of Softaculous Server and VPS License Types.  More info at http://softaculous.com/';
	public static $help = 'Softaculous is a great Auto Installer having 175 great scripts and we are still adding more. Softaculous is ideal for Web Hosting companies and it could give a significant boost to your sales. These scripts cover most of the uses a customer could ever have. We have covered a wide array of Categories so that everyone could find the required script one would need to power their Web Site. The best part is we keep on adding new scripts which we know will satisfy the needs of a User.';
	public static $module = 'licenses';
	public static $type = 'service';


	public function __construct() {
	}

	public static function getHooks() {
		return [
			'licenses.settings' => [__CLASS__, 'getSettings'],
			'licenses.activate' => [__CLASS__, 'Activate'],
			'licenses.deactivate' => [__CLASS__, 'Deactivate'],
			'licenses.change_ip' => [__CLASS__, 'ChangeIp'],
			'function.requirements' => [__CLASS__, 'getRequirements'],
			'ui.menu' => [__CLASS__, 'Menu'],
		];
	}

	public static function Activate(GenericEvent $event) {
		$license = $event->getSubject();
		if ($event['category'] == SERVICE_TYPES_SOFTACULOUS) {
			myadmin_log('licenses', 'info', 'Softaculous Activation', __LINE__, __FILE__);
			function_requirements('activate_softaculous');
			activate_softaculous($license->get_ip(), $event['field1'], $event['email']);
			$event->stopPropagation();
		} elseif ($event['category'] == SERVICE_TYPES_WEBUZO) {
			myadmin_log('licenses', 'info', 'Webuzo Activation', __LINE__, __FILE__);
			function_requirements('activate_webuzo');
			activate_webuzo($license->get_ip(), $event['field1'], $event['email']);
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
		} elseif ($event['category'] == SERVICE_TYPES_WEBUZO) {
			myadmin_log('licenses', 'info', 'Webuzo Deactivation', __LINE__, __FILE__);
			function_requirements('deactivate_webuzo');
			deactivate_webuzo($license->get_ip());
			$event->stopPropagation();
		}
	}

	public static function ChangeIp(GenericEvent $event) {
		if ($event['category'] == SERVICE_TYPES_SOFTACULOUS) {
			$license = $event->getSubject();
			$settings = get_module_settings('licenses');
			myadmin_log('licenses', 'info', "IP Change - (OLD:".$license->get_ip().") (NEW:{$event['newip']})", __LINE__, __FILE__);
			function_requirements('get_softaculous_licenses');
			$data = get_softaculous_licenses($license->get_ip());
			$lid = array_keys($data['licenses']);
			$lid = $lid[0];
			$noc = new \Detain\MyAdminSoftaculous\SOFT_NOC(SOFTACULOUS_USERNAME, SOFTACULOUS_PASSWORD);
			if ($noc->editips($lid[0], $event['newip']) !== FALSE) {
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
		$menu = $event->getSubject();
		$module = 'licenses';
		if ($GLOBALS['tf']->ima == 'admin') {
			$menu->add_link($module.'api', 'choice=none.softaculous_list', 'whm/createacct.gif', 'List all Softaculous Licenses');
			$menu->add_link($module.'api', 'choice=none.webuzo_list', 'whm/createacct.gif', 'List all Webuzo Licenses');
		}
	}

	public static function getRequirements(GenericEvent $event) {
		$loader = $event->getSubject();
		$loader->add_requirement('activate_softaculous', '/../vendor/detain/myadmin-softaculous-licensing/src/activate_softaculous.php');
		$loader->add_requirement('activate_webuzo', '/../vendor/detain/myadmin-softaculous-licensing/src/activate_webuzo.php');
		$loader->add_requirement('softaculous_list', '/../vendor/detain/myadmin-softaculous-licensing/src/softaculous_list.php');
		$loader->add_requirement('webuzo_list', '/../vendor/detain/myadmin-softaculous-licensing/src/webuzo_list.php');
		$loader->add_requirement('deactivate_softaculous', '/../vendor/detain/myadmin-softaculous-licensing/src/deactivate_softaculous.php');
		$loader->add_requirement('deactivate_webuzo', '/../vendor/detain/myadmin-softaculous-licensing/src/deactivate_webuzo.php');
		$loader->add_requirement('get_softaculous_licenses', '/../vendor/detain/myadmin-softaculous-licensing/src/get_softaculous_licenses.php');
		$loader->add_requirement('get_webuzo_licenses', '/../vendor/detain/myadmin-softaculous-licensing/src/get_webuzo_licenses.php');
	}

	public static function getSettings(GenericEvent $event) {
		// will be executed when the licenses.settings event is dispatched
		$settings = $event->getSubject();
		$settings->add_text_setting('licenses', 'Softaculous', 'softaculous_username', 'Softaculous Username:', 'Softaculous Username', $settings->get_setting('SOFTACULOUS_USERNAME'));
		$settings->add_text_setting('licenses', 'Softaculous', 'softaculous_password', 'Softaculous Password:', 'Softaculous Password', $settings->get_setting('SOFTACULOUS_PASSWORD'));
		$settings->add_text_setting('licenses', 'Softaculous', 'webuzo_username', 'Webuzo Username:', 'Webuzo Username', $settings->get_setting('WEBUZO_USERNAME'));
		$settings->add_text_setting('licenses', 'Softaculous', 'webuzo_password', 'Webuzo Password:', 'Webuzo Password', $settings->get_setting('WEBUZO_PASSWORD'));
		$settings->add_dropdown_setting('licenses', 'Softaculous', 'outofstock_licenses_softaculous', 'Out Of Stock Softaculous Licenses', 'Enable/Disable Sales Of This Type', $settings->get_setting('OUTOFSTOCK_LICENSES_SOFTACULOUS'), array('0', '1'), array('No', 'Yes',));
	}

}

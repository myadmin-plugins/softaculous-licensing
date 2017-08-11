<?php
/**
 * Softaculous Related Functionality
 *
 * @author Joe Huss <detain@interserver.net>
 * @copyright 2017
 * @package MyAdmin-Softaculous-Licensing
 * @category Licenses
 */

namespace Detain\MyAdminSoftaculous;

//use Detain\Softaculous\Softaculous;
use Symfony\Component\EventDispatcher\GenericEvent;

/*
 * $noc = new \Detain\MyAdminSoftaculous\SoftaculousNOC(SOFTACULOUS_USERNAME, SOFTACULOUS_PASSWORD);
 * // Buy / renew a License
 * $noc->displayResponse($noc->buy('174.37.113.98', '1M', 1, 'test@test.com', 1));
 * // Refund a Transaction
 * $noc->displayResponse($noc->refund(100));
 * // Get me all my licenses
 * $noc->displayResponse($noc->licenses());
 * // Search for a license by IP
 * $noc->displayResponse($noc->licenses('', '198.198.198.198'));
 * // Search for a license by KEY
 * $noc->displayResponse($noc->licenses('88888-88888-88888-88888-88888'));
 * // All Expired Licenses
 * $noc->displayResponse($noc->licenses('', '', 1));
 * // Expiring in next 7 Days
 * $noc->displayResponse($noc->licenses('', '', 2));
 * // Expiring in next 15 Days
 * $noc->displayResponse($noc->licenses('', '', 3));
 * // Get all transactions of a Invoice
 * $noc->displayResponse($noc->invoicedetails(100));
 * // Get all unbilled transactions for the current month
 * $noc->displayResponse($noc->invoicedetails());
 * // Cancel a License
 * $noc->displayResponse($noc->cancel('88888-88888-88888-88888-88888')); // Cancel by License Key
 * $noc->displayResponse($noc->cancel('', '198.198.198.198')); // Cancel by IP
 * // EDIT IP of a License
 * $noc->displayResponse($noc->editips(1000, '198.198.198.198')); // LID and new IP Address
 * // Get the Action/Activity Logs of a License
 * $noc->displayResponse($noc->licenselogs('88888-88888-88888-88888-88888'));
 */

/**
 * Class Plugin
 *
 * @package Detain\MyAdminSoftaculous
 */
class Plugin {

	public static $name = 'Softaculous Licensing';
	public static $description = 'Allows selling of Softaculous Server and VPS License Types.  More info at http://softaculous.com/';
	public static $help = 'Softaculous is a great Auto Installer having 175 great scripts and we are still adding more. Softaculous is ideal for Web Hosting companies and it could give a significant boost to your sales. These scripts cover most of the uses a customer could ever have. We have covered a wide array of Categories so that everyone could find the required script one would need to power their Web Site. The best part is we keep on adding new scripts which we know will satisfy the needs of a User.';
	public static $module = 'licenses';
	public static $type = 'service';

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
	}

	/**
	 * @return array
	 */
	public static function getHooks() {
		return [
			self::$module.'.settings' => [__CLASS__, 'getSettings'],
			self::$module.'.activate' => [__CLASS__, 'getActivate'],
			self::$module.'.reactivate' => [__CLASS__, 'getActivate'],
			self::$module.'.deactivate' => [__CLASS__, 'getDeactivate'],
			self::$module.'.deactivate_ip' => [__CLASS__, 'getDeactivate'],
			self::$module.'.change_ip' => [__CLASS__, 'getChangeIp'],
			'function.requirements' => [__CLASS__, 'getRequirements'],
			'ui.menu' => [__CLASS__, 'getMenu']
		];
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
	public static function getActivate(GenericEvent $event) {
		$serviceClass = $event->getSubject();
		if ($event['category'] == get_service_define('SOFTACULOUS')) {
			myadmin_log(self::$module, 'info', 'Softaculous Activation', __LINE__, __FILE__);
			function_requirements('activate_softaculous');
			activate_softaculous($serviceClass->getIp(), $event['field1'], $event['email']);
			$event->stopPropagation();
		} elseif ($event['category'] == get_service_define('WEBUZO')) {
			myadmin_log(self::$module, 'info', 'Webuzo Activation', __LINE__, __FILE__);
			function_requirements('activate_webuzo');
			activate_webuzo($serviceClass->getIp(), $event['field1'], $event['email']);
			$event->stopPropagation();
		}
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
	public static function getDeactivate(GenericEvent $event) {
		$serviceClass = $event->getSubject();
		if ($event['category'] == get_service_define('SOFTACULOUS')) {
			myadmin_log(self::$module, 'info', 'Softaculous Deactivation', __LINE__, __FILE__);
			function_requirements('deactivate_softaculous');
			deactivate_softaculous($serviceClass->getIp());
			$event->stopPropagation();
		} elseif ($event['category'] == get_service_define('WEBUZO')) {
			myadmin_log(self::$module, 'info', 'Webuzo Deactivation', __LINE__, __FILE__);
			function_requirements('deactivate_webuzo');
			deactivate_webuzo($serviceClass->getIp());
			$event->stopPropagation();
		}
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
	public static function getChangeIp(GenericEvent $event) {
		if ($event['category'] == get_service_define('SOFTACULOUS')) {
			$serviceClass = $event->getSubject();
			$settings = get_module_settings(self::$module);
			myadmin_log(self::$module, 'info', 'IP Change - (OLD:'.$serviceClass->getIp().") (NEW:{$event['newip']})", __LINE__, __FILE__);
			function_requirements('get_softaculous_licenses');
			$data = get_softaculous_licenses($serviceClass->getIp());
			$lid = array_keys($data[self::$module]);
			$lid = $lid[0];
			$noc = new \Detain\MyAdminSoftaculous\SoftaculousNOC(SOFTACULOUS_USERNAME, SOFTACULOUS_PASSWORD);
			if ($noc->editips($lid[0], $event['newip']) !== FALSE) {
				$GLOBALS['tf']->history->add($settings['TABLE'], 'change_ip', $event['newip'], $serviceClass->getIp());
				$serviceClass->set_ip($event['newip'])->save();
				$return['status'] = 'ok';
				$return['status_text'] = 'The IP Address has been changed.';
			} else {
				$return['status'] = 'error';
				$return['status_text'] = 'Error occurred during deactivation.';
			}
			$event->stopPropagation();
		}
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
	public static function getMenu(GenericEvent $event) {
		$menu = $event->getSubject();
		if ($GLOBALS['tf']->ima == 'admin') {
			$menu->add_link(self::$module.'api', 'choice=none.softaculous_list', 'whm/createacct.gif', 'List all Softaculous Licenses');
			$menu->add_link(self::$module.'api', 'choice=none.webuzo_list', 'whm/createacct.gif', 'List all Webuzo Licenses');
		}
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
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

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
	public static function getSettings(GenericEvent $event) {
		$settings = $event->getSubject();
		$settings->add_text_setting(self::$module, 'Softaculous', 'softaculous_username', 'Softaculous Username:', 'Softaculous Username', $settings->get_setting('SOFTACULOUS_USERNAME'));
		$settings->add_text_setting(self::$module, 'Softaculous', 'softaculous_password', 'Softaculous Password:', 'Softaculous Password', $settings->get_setting('SOFTACULOUS_PASSWORD'));
		$settings->add_text_setting(self::$module, 'Softaculous', 'webuzo_username', 'Webuzo Username:', 'Webuzo Username', $settings->get_setting('WEBUZO_USERNAME'));
		$settings->add_text_setting(self::$module, 'Softaculous', 'webuzo_password', 'Webuzo Password:', 'Webuzo Password', $settings->get_setting('WEBUZO_PASSWORD'));
		$settings->add_dropdown_setting(self::$module, 'Softaculous', 'outofstock_licenses_softaculous', 'Out Of Stock Softaculous Licenses', 'Enable/Disable Sales Of This Type', $settings->get_setting('OUTOFSTOCK_LICENSES_SOFTACULOUS'), ['0', '1'], ['No', 'Yes']);
	}

}

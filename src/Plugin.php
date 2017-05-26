<?php

namespace Detain\MyAdminSoftaculous;

//use Detain\Softaculous\Softaculous;
use Symfony\Component\EventDispatcher\GenericEvent;

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
			function_requirements('class.softaculous');
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
			$menu->add_link($module, 'choice=none.reusable_softaculous', 'icons/database_warning_48.png', 'ReUsable Softaculous Licenses');
			$menu->add_link($module, 'choice=none.softaculous_list', 'icons/database_warning_48.png', 'Softaculous Licenses Breakdown');
			$menu->add_link('licensesapi', 'choice=none.softaculous_licenses_list', 'whm/createacct.gif', 'List all Softaculous Licenses');
		}
	}

	public static function Requirements(GenericEvent $event) {
		// will be executed when the licenses.loader event is dispatched
		$loader = $event->getSubject();
		$loader->add_requirement('crud_softaculous_list', '/../vendor/detain/crud/src/crud/crud_softaculous_list.php');
		$loader->add_requirement('crud_reusable_softaculous', '/../vendor/detain/crud/src/crud/crud_reusable_softaculous.php');
		$loader->add_requirement('get_softaculous_licenses', '/licenses/softaculous.functions.inc.php');
		$loader->add_requirement('get_softaculous_list', '/licenses/softaculous.functions.inc.php');
		$loader->add_requirement('softaculous_licenses_list', '/licenses/softaculous.functions.inc.php');
		$loader->add_requirement('softaculous_list', '/licenses/softaculous.functions.inc.php');
		$loader->add_requirement('get_available_softaculous', '/licenses/softaculous.functions.inc.php');
		$loader->add_requirement('activate_softaculous', '/licenses/softaculous.functions.inc.php');
		$loader->add_requirement('get_reusable_softaculous', '/licenses/softaculous.functions.inc.php');
		$loader->add_requirement('reusable_softaculous', '/licenses/softaculous.functions.inc.php');
		$loader->add_requirement('class.softaculous', '/../vendor/detain/softaculous/class.softaculous.inc.php');
		$loader->add_requirement('vps_add_softaculous', '/vps/addons/vps_add_softaculous.php');
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

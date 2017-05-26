<?php
/* TODO:
 - service type, category, and services  adding
 - dealing with the SERVICE_TYPES_softaculous define
 - add way to call/hook into install/uninstall
*/
return [
	'name' => 'Softaculous Licensing',
	'description' => 'Allows selling of Softaculous Server and VPS License Types.  More info at http://softaculous.com/',
	'help' => 'Softaculous is a great Auto Installer having 175 great scripts and we are still adding more. Softaculous is ideal for Web Hosting companies and it could give a significant boost to your sales. These scripts cover most of the uses a customer could ever have. We have covered a wide array of Categories so that everyone could find the required script one would need to power their Web Site. The best part is we keep on adding new scripts which we know will satisfy the needs of a User.',
	'module' => 'licenses',
	'author' => 'detain@interserver.net',
	'home' => 'https://github.com/detain/myadmin-softaculous-licensing',
	'repo' => 'https://github.com/detain/myadmin-softaculous-licensing',
	'version' => '1.0.0',
	'type' => 'licenses',
	'hooks' => [
		'licenses.settings' => ['Detain\MyAdminSoftaculous\Plugin', 'Settings'],
		'licenses.activate' => ['Detain\MyAdminSoftaculous\Plugin', 'Activate'],
		'licenses.deactivate' => ['Detain\MyAdminSoftaculous\Plugin', 'Deactivate'],
		'licenses.change_ip' => ['Detain\MyAdminSoftaculous\Plugin', 'ChangeIp'],
		/* 'function.requirements' => ['Detain\MyAdminSoftaculous\Plugin', 'Requirements'],
		'ui.menu' => ['Detain\MyAdminSoftaculous\Plugin', 'Menu'] */
	],
];

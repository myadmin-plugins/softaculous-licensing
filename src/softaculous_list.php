<?php
/**
 * Softaculous Related Functionality
 * @author Joe Huss <detain@interserver.net>
 * @copyright 2019
 * @package MyAdmin-Softaculous-Licensing
 * @category Licenses
 */

function softaculous_list()
{
	if ($GLOBALS['tf']->ima == 'admin') {
		$table = new \TFTable;
		$table->set_title('Softaculous License List');
		$header = false;
		function_requirements('get_softaculous_licenses');
		$licenses = get_softaculous_licenses();
		$licensesValues = array_values($licenses['licenses']);
		foreach ($licensesValues as $data) {
			if (!$header) {
				$dataKeys = array_keys($data);
				foreach ($dataKeys as $field) {
					$table->add_field(ucwords(str_replace('_', ' ', $field)));
				}
				$table->add_row();
				$header = true;
			}
			$dataValues = array_values($data);
			foreach ($dataValues as $field) {
				$table->add_field($field);
			}
			$table->add_row();
		}
		add_output($table->get_table());
	}
	//add_output('<div style="text-align: left;"><pre>'.var_export(get_softaculous_licenses(), TRUE).'</pre></div>');
}

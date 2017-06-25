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

function webuzo_list() {
	if ($GLOBALS['tf']->ima == 'admin') {
		$table = new TFTable;
		$table->set_title('webuzo License List');
		$header = FALSE;
		function_requirements('get_webuzoLicenses');
		$licenses = get_webuzoLicenses();
		foreach ($licenses['licenses'] as $lid => $data) {
			if (!$header) {
				foreach (array_keys($data) as $field)
					$table->add_field(ucwords(str_replace('_', ' ', $field)));
				$table->add_row();
				$header = TRUE;
			}
			foreach ($data as $key => $field)
				$table->add_field($field);
			$table->add_row();
		}
		add_output($table->get_table());
	}
	//add_output('<div style="text-align: left;"><pre>'.var_export(get_softaculous_licenses(), TRUE).'</pre></div>');
}

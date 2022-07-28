<?php
/**
 * Softaculous Related Functionality
 * @author Joe Huss <detain@interserver.net>
 * @copyright 2019
 * @package MyAdmin-Softaculous-Licensing
 * @category Licenses
 */

function webuzo_list()
{
    if ($GLOBALS['tf']->ima == 'admin') {
        $table = new \TFTable();
        $table->set_title('webuzo License List');
        $header = false;
        $noc = new \Detain\MyAdminSoftaculous\SoftaculousNOC(WEBUZO_USERNAME, WEBUZO_PASSWORD);
        $licenses =  $noc->webuzo_licenses('', $ipAddress);
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
}

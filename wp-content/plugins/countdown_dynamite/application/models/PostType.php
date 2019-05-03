<?php
require_once UCD_APPLICATION_PATH . '/models/AbstractModel.php';

class Ucd_Model_PostType extends Ucd_Model_AbstractModel
{
    public function getList($labelKey = 'singular_name')
    {
        $result = array();

        $types = get_post_types(array(
            'show_ui' => TRUE,
            'show_in_nav_menus' => TRUE,
        ), 'objects');

        foreach ($types as $key=>$info) {
            $result[$key] = $info->labels->$labelKey;
        }

        return $result;
    }
}
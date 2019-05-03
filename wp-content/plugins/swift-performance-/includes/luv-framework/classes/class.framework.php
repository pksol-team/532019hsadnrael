<?php

class Luv_Framework {

      public static function fields($context, $args){
            include_once apply_filters('luv_framework_classes_dir', LUV_FRAMEWORK_PATH . 'classes/') . 'class.fields.php';

            // Meta fields
            if (strtolower($context) == 'meta'){
                  include_once apply_filters('luv_framework_classes_dir', LUV_FRAMEWORK_PATH . 'classes/') . 'class.meta-fields.php';
                  return new Luv_Framework_Meta_Fields($args);
            }

            // Option fields
            if (strtolower($context) == 'option'){
                  include_once apply_filters('luv_framework_classes_dir', LUV_FRAMEWORK_PATH . 'classes/') . 'class.option-fields.php';
                  return new Luv_Framework_Option_Fields($args);
            }
      }

      /**
       * Extend array
       * @param $values working array
       * @param $default default values
       */
      public static function extend($values, $default){
            foreach ($default as $key => $value){
                  if (!isset($values[$key])){
                        $values[$key] = $value;
                  }
            }

            return $values;
      }

      public static function sanitize_file_name($filename){
            return sanitize_title(preg_replace('~\.+~','.',$filename));
      }

}

?>

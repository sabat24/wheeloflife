<?php defined('SYSPATH') or die('No direct script access.');
 
class javascript extends Kohana_Core {
    static protected $vars = array();
    static protected $to_vars = array();
    static protected $header_js = '';
    static protected $jquery_ready = '';

    static public function add_var($var, $value) {
        self::$vars[$var] = $value;
    }
    
    static public function add_to_var($var, $value) {
        self::$to_vars[$var] = $value;
    }

    static public function render_vars($print = FALSE) {
        $output = '';
        foreach (self::$vars as $var => $value) {
            $output .= 'var ' . $var . ' = ' . $value . ';';
        }
        
        foreach (self::$to_vars as $var => $value) {
            $output .=  $var . ' = ' . $value . ';';
        }

        if ($print) {
            echo $output;
        }
        return $output;
    }
    
    static public function add_header_js($code) {
        self::$header_js.=$code;
    }
    
    static public function render_header_js($print = FALSE) {
        $output = self::$header_js;

        if ($print) {
            echo $output;
        }
        return $output;
    }
    
    static public function add_jquery_ready($code) {
        self::$jquery_ready.=$code;
    }
    
    static public function render_jquery_ready() {
        $output = self::$jquery_ready;
        if ( ! empty($output)) {
            $output = '$(function() {'.$output.'});';
        }
        return $output;
    }
    
    public static function array_to_js($arr, $name, $excluded_keys = array()) {
        Javascript::add_var($name, '{}');
        self::_array_to_js($arr, $name, $excluded_keys);
    }
    
   private static function _array_to_js($arr, $name, $excluded_keys = array()) {
        foreach($arr as $key => $value) {
            if (in_array($key, $excluded_keys)) continue;
            
            if (is_array($value)) {
                $new_name = $name.'["'.$key.'"]';
                self::add_to_var($new_name, '{}');
                self::_array_to_js($value, $new_name, $excluded_keys);
                continue;
            }
            if ( ! is_int($value)) {
                $value = "'".$value."'";
            }
            self::add_to_var($name.'["'.$key.'"]', $value);
            //echo $name.'["'.$key.'"]'.'='.$value.'<br />';
        }
    }
}
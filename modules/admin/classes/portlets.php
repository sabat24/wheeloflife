<?php defined('SYSPATH') or die('No direct script access.');

class Portlets {
    
    private static $portlets = array();
    
    private static function _get_config() {
        return array (
            'minmax' => TRUE, // mozliwosc 
            'title' => '', // tytul
            'id' => '', // id-holder - pole wymagane
            'icon' => '', // nazwa ikony
            'view' => '', // sciezka do widoku
        );
    }
    
    public static function render ($id, $title, $options = array()) {
        $options = self::_prepare_portlet_options($id, $title, $options);
        return View::factory('blocks/admin/portlet_holder', $options)->render();
    }
    
    public static function render_all() {
        $portlets = self::$portlets;
        $html = '';
        foreach ($portlets as $options) {
            $html .= View::factory('blocks/admin/portlet_holder', $options)->render();
        }
        return $html;
    }
    
    public static function add_to_render($id, $title, $options = array()) {
        $options = self::_prepare_portlet_options($id, $title, $options);
        if (isset(self::$portlets[$options['id']])) {
            throw new Kohana_Exception('Portlet with ID ['.$options['id'].'] already exists');
        }
        self::$portlets[$options['id']] = $options;
    }
    
    private static function _prepare_portlet_options($id, $title, $options) {
        $options = array_merge(self::_get_config(), array('id' => $id, 'title' => $title), $options);
        if (empty($options['id'])) {
            throw new Kohana_Exception('The Portlet ID option is empty.');
        }
        if (empty($options['icon'])) {
            $options['icon'] = $options['id'];
        }
        return $options;
    }
}
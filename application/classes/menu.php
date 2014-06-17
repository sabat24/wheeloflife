<?php
class Menu {
    private $_a_main_menu = array();
    
    private $_menu_selected = FALSE;
    
    private $_a2;
    
    public function __construct() {
        $this->_a2 = A2::instance('a2');
        $this->_prepareMenu();
    }
    
    private function _prepareMenu() {
        $this->_add_main_menu(__('Home'), 'static', 'index',  array ());
        $this->_add_main_menu(__('About'), 'static', 'about',  array ());
        $this->_add_main_menu(__('Contact'), 'static', 'contact',  array ());
    }

    private function _add_main_menu($title, $controller, $action = NULL, $params = array()) {
        $class = Arr::get($params, 'class', 'default');
        $route = Arr::get($params, 'route', 'static');
        $selected = Arr::get($params, 'selected', FALSE);
        if ( $this->_a2->allowed($controller, 'read')) {
            if ($action == 'index') {
                $this->_a_main_menu[$controller][$action] = array ('title' => $title, 'url' => URL::site(Route::get($route)->uri(array('controller' => $controller))), 'class' => $class, 'selected' => $selected);
            } else {
                $this->_a_main_menu[$controller][$action] = array ('title' => $title, 'url' => URL::site(Route::get($route)->uri(array('controller' => $controller, 'action' => $action))), 'class' => $class, 'selected' => $selected);
            }
        }
    }
    
    public function add_main_menu($title, $controller, $action = NULL, $params = array()) {
        $this->_add_main_menu($title, $controller, $action, $params);
    }
    
    public function select_menu_by_index($controller, $action) {
        if (isset($this->_a_main_menu[$controller][$action])) {
            $this->_a_main_menu[$controller][$action]['selected'] = TRUE;
            $this->_menu_selected = TRUE;
            return $this->_a_main_menu[$controller][$action]['title'];
        }
    }
    
    public function get_menu() {
        $menu = array();
        $menu['main_menu'] = $this->_a_main_menu;
        return $menu;
    }
}
?>
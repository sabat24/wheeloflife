<?php
class Admin_Menu {
    
    
    private $_a_main_menu = array();
    private $_a_hidden_menu = array();
    private $_a_sub_menu = array();
    private $_a_hidden_menu_col = 0;
    
    private $_menu_selected = FALSE;
    private $_submenu_selected = FALSE;
    
    private $_a2;
    
    public function __construct() {
        $this->_a2 = A2::instance('a2');
        $this->_prepareMenu();
    }
    
    private function _prepareMenu() {
        $this->_add_main_menu(__('Dashboard'), 'dashboard', NULL,  array ('class' => 'dashboard'));
        $this->_add_main_menu(__('Users'), 'users', NULL,  array ('class' => 'users'));
        $this->_add_main_menu(__('Charts'), 'charts', NULL,  array ('class' => 'reports'));
        $this->_add_main_menu(__('Admins'), 'admins', NULL,  array ('class' => 'users'));
    }

    private function _add_main_menu($title, $controller, $action = NULL, $params = array()) {
        $class = Arr::get($params, 'class', 'default');
        $selected = Arr::get($params, 'selected', FALSE);
        if ( $this->_a2->allowed($controller, 'read')) {
            $this->_a_main_menu[$controller] = array ('title' => $title, 'url' => URL::site(Route::get('admin')->uri(array('controller' => $controller, 'action' => $action))), 'class' => $class, 'selected' => $selected);
        }
    }
    
    public function add_sub_menu($item, $action = FALSE, $limit = 9, $sub_limit = 4) {
        $item['href'] = $item['url'];
        if (count($this->_a_sub_menu) >= $limit) {
            if ( ! isset($this->_a_hidden_menu[$this->_a_hidden_menu_col])) {
                $this->_a_hidden_menu[$this->a_hidden_menu_col] = array();
            } elseif (count($this->_a_hidden_menu[$this->_a_hidden_menu_col]) >= $sub_limit) {
                $this->_a_hidden_menu_col++;
            }
            $this->_a_hidden_menu[$this->_a_hidden_menu_col][] = $item;
        } else {
            if ($action === FALSE) {
                $this->_a_sub_menu[] = $item;
            } else {
                $this->_a_sub_menu[$action] = $item;
            }
            if ($item['selected'] === TRUE) {
                $this->_submenu_selected = TRUE;
            }
        }
    }
    
    public function select_menu_by_index($menu = FALSE, $submenu = FALSE) {
        if ($menu !== FALSE) {
            if (isset($this->_a_main_menu[$menu])) {
                $this->_a_main_menu[$menu]['selected'] = TRUE;
                $this->_menu_selected = TRUE;
                return $this->_a_main_menu[$menu]['title'];
            }
        }
        
        if ($submenu !== FALSE) {
            if (isset($this->_a_sub_menu[$submenu])) {
                $this->_a_sub_menu[$submenu]['selected'] = TRUE;
                $this->_submenu_selected = TRUE;
                return $this->_a_sub_menu[$submenu]['title'];
                
            }
        }
    }
    
    public function get_menu() {
        $menu = array();
        $menu['main_menu'] = $this->_a_main_menu;
        $menu['sub_menu'] = $this->_a_sub_menu;
        $menu['hidden_menu'] = $this->_a_hidden_menu;
        return $menu;
    }
}
?>
<?php
defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Dashboard extends Controller_Admin_Template_Default {
    
    public function before() {
        parent::before();
        if ($this->request->is_ajax()) {
        
        } else {
            if ($this->auto_render) {
                $route = Route::get('admin');
                //$controller = $this->request->controller();
                $this->menu->add_sub_menu(array('title' => __('Main site'), 'url' => URL::site($route->uri()), 'selected' => FALSE), 'index');
            }
        }
    }
    
    public function action_index() {
        $this->content = 'welcome';
    }
    
    
}
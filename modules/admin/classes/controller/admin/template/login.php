<?php
defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Template_Login extends Controller_Base {
    public function before(){
        // Run anything that need ot run before this.
        
        parent::before();
        if ( ! $this->request->is_ajax()) {
            if ($this->auto_render) {
                $this->template = View::factory('templates/admin/login');
                $this->template->styles = array();
                $this->template->scripts = array();
                $this->template->content = '';
            }
        }
        
        
    }

    /**
    * Fill in default values for our properties before rendering the output.
    */
    public function after(){
        if ($this->request->is_ajax()) {

            if ($this->auto_render) {
                $messages = View::factory('blocks/admin/hint')->render();
                $this->response['messages'] = $messages;
                echo json_encode($this->response);
            } else {
                echo $this->response;
            }
        } else { 
            if ($this->auto_render) {
                
                $styles = array('assets/modules/admin/css/login.css' => 'screen');
                $scripts = array (
                    'assets/modules/admin/js/login.js',
                    'assets/js/admin/jquery.history.js',
                    'assets/js/vendor/jquery.min.js',
                );
        
        
                // Add defaults to template variables.
                $this->template->styles = (array_merge($this->template->styles, $styles));
                $this->template->scripts = array_reverse(array_merge($this->template->scripts, $scripts));
                $this->template->content = $this->content;
                // Run anything that needs to run after this.
                parent::after();
            }
        }
    }
}
?>
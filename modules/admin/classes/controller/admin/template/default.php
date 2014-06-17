<?php
defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Template_Default extends Controller_Base {
    public $template = 'templates/admin/default';
    
    public $user;
    protected $menu;
    

    /**
    * Initialize properties before running the controller methods (actions),
    * so they are available to our action.
    */
    public function before(){

        // Run anything that need ot run before this.
        parent::before();
        
        
        /*
        if ($this->user['u_id'] == -1 && $this->request->controller() != 'auth' && $this->request->is_ajax() == FALSE && $this->request->action() != 'validate_fields') {
            $this->request->redirect('admin/login/');
        }
        */
        
        if ($this->request->is_ajax()) {
            if ($this->auto_render) {
                $this->response = array();
            } else {
                $this->response = '';
            }
        } else {
            if ($this->auto_render) {
                $this->menu = new Admin_Menu($this->a2);

                // Initialize empty values
                $this->template->head_title = ''; // title displayed to user
                $this->template->title = ''; // title tag
                $this->template->header = '';
                $this->template->content = '';
                $this->template->footer = '';
                $this->template->styles = array();
                $this->template->scripts = array();
                $this->template->options = array();
                
                if ( ! $this->a2->allowed($this->request->controller(), 'read')) {
                    Hint::set(Hint::ERROR, __('You haven\'t got access to this part of service'));
                    $redirect = array ('url' => URL::site(Route::get('admin/auth')->uri(array('action' => 'logout'))));
                    $this->_ses->set('redirect', $redirect);
                    $this->redirect = TRUE;
                    return;
                }
            }
        }
    }

    /**
    * Fill in default values for our properties before rendering the output.
    */
    public function after(){
        
        if ($this->request->is_ajax()) {
            
            if ($this->auto_render) {
                if ( ! isset($this->response['redirect'])) {
                    $messages = View::factory('blocks/admin/hint')->render();
                    $this->response['messages'] = $messages;
                }
                echo json_encode($this->response);
            } else {
                echo $this->response;
            }
        } else {
            if ($this->auto_render) {
                if ($this->redirect === TRUE) {
                    parent::after();
                    return;
                }
                // Define defaults
                $styles = array (
                    'assets/modules/admin/css/960.css' => 'screen',
                    //'assets/modules/admin/css/reset.css' => 'screen',
                    'assets/modules/admin/css/text.css' => 'screen',
                    'assets/modules/admin/css/blue.css' => 'screen',
                    'assets/modules/admin/css/smoothness/jquery-ui-1.9.2.custom.css' => 'screen',
                );
            
                // na top strony idzie to, co na dole
                
                $scripts = array(
                    'assets/modules/admin/js/functions.js',
                    'assets/modules/admin/js/autocomplete.js',
                    //'assets/js/admin/jquery.dialogextend.1_0_1.js',
                    'assets/js/admin/jquery.history.js',
                    //'assets/js/admin/jquery.timers.js',
                    //'assets/js/admin/jquery.timeago.pl.js',
                    'assets/js/admin/jquery.livequery.js',
                    //'assets/js/admin/jquery.timeago.js',
                    'assets/js/translations.js',
                    'assets/js/admin/jquery-ui-1.9.2.custom.min.js',
                    'assets/modules/filter/js/jquery.ui.datepicker-gb.js',
                    'assets/js/vendor/jquery.min.js',
                    
                );
                
                
                //if ( ! isset($this->template->options['turn_off_menu'])) {
                    $this->template->head_title = $this->menu->select_menu_by_index($this->request->controller());
                    $this->template->title = $this->menu->select_menu_by_index(FALSE, $this->request->action());
                    View::set_global('title', $this->template->head_title.' - '.$this->template->title);
                    
                    $menues = $this->menu->get_menu();
                    $blocks = array();
                    $blocks['menu'] = View::factory('blocks/admin/menu', $menues)->render();
                    $blocks['submenu'] = View::factory('blocks/admin/submenu', $menues)->render();
                    $blocks['hiddenmenu']='';
                    
                    $this->template->header = View::factory('pages/admin/header', $blocks)->set('user', $this->user);
                    
                    
                //}
                
                if ($this->content instanceof View) {
                    
                    $this->template->content = $this->content;
                } else {
                    $view = View::factory('pages/admin/default')
                        ->set('content', $this->content);
                    $this->template->content = $view;
                }
                //if ( ! isset($this->template->options['turn_off_menu'])) {
                    $this->template->footer = View::factory('pages/admin/footer');
                //}

                // Add defaults to template variables.
                $this->template->styles = (array_merge($this->template->styles, $styles));
                $this->template->scripts = array_reverse(array_merge($this->template->scripts, $scripts));
            }
            // Run anything that needs to run after this.
            parent::after();
        }
        
    }
}
?>
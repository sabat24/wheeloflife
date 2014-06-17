<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Default extends Controller_Template {

    public $template = 'pages/default';
    public $variables = array();
    public $content = '';
    
    protected $menu;
    protected $default_view_action;
    protected $_ses;
    protected $a2;

    public function before() {
      parent::before();
        
        $this->_ses = Session::instance('database');
        $model_user = Model::factory('Admin_User'); 

        if ( ($user = $this->_ses->get('user', FALSE)) === FALSE) {
            $u_id = $model_user->check_Cookie();
            if ($u_id !== FALSE) {
                $user = $moodel_user->create_user_by_id($u_id);
            } else {
                $user = $model_user->create_user_by_id(-1);
            }
            
            $this->_ses->set('user', $user);
            $this->user = $user;
            unset($user);
        } else {
            $this->user = $user;
        }
        
        $this->a2 = A2::instance('a2');
        if ( ! $this->request->is_ajax()) {
            if ($this->auto_render) {
                $this->menu = new Menu($this->a2);
                // inicjacja zmiennych
                $this->template->title = '';
                $this->template->description = '';
                $this->template->content = '';
                $this->template->styles = array();
                $this->template->scripts = array();
                $this->template->menu_index = '';
                // domyslny plik widoku dla kontrolera
                // moze byc nadpisywany z wewnatrz klasy kontrolera
                $this->default_view_action = 'index';
                $this->template->user = $this->user;
            }
        }
    }
    
    public function after() {
        
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
                
                $styles = array (
                    'assets/default/css/bootstrap.min.css' => 'screen',
                    'assets/default/css/bootstrap-theme.min.css' => 'screen',
                    'assets/default/css/main.css' => 'screen',
                );

                $scripts = array(
                    'assets/js/vendor/jquery.min.js',
                    'assets/default/js/vendor/bootstrap.min.js',
                    'assets/js/admin/jquery.history.js',
                    'assets/default/js/main.js',
                );

                $this->template->styles = array_merge ($styles, $this->template->styles);
                $this->template->scripts = array_merge ($scripts, $this->template->scripts);
            
                $this->template->variables = array (
                    'controller' => $this->request->controller(),
                    'action' => $this->request->action(),
                );
                
                $this->template->head_title = $this->menu->select_menu_by_index($this->request->controller(), $this->request->action());
                
                if ($this->user['u_id'] != -1) {
                    $this->menu->add_main_menu(__('Your account'), 'usercp', NULL, array('route' => 'usercp'));
                }
                $this->template->menu = $this->menu->get_menu();

                if (empty($this->content)) {
                    if (file_exists(APPPATH.'views/pages/'.$this->request->controller().'/'.$this->request->action().EXT)) {
                        $view = 'pages/'.$this->request->controller().'/'.$this->request->action();
                    } else if (file_exists(APPPATH.'views/pages/'.$this->request->controller().'/'.$this->default_view_action.EXT)) {
                        $view = 'pages/'.$this->request->controller().'/'.$this->default_view_action;
                    } else {
                        $view = FALSE;
                    }

                    $this->content = $view === FALSE ? '' : View::factory($view, $this->variables);
                }
                $this->template->content = $this->content;
                
            }

            parent::after();
        }
        
    }
    
}
?>
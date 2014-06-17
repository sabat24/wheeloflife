<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Main Controller
 *
 */
class Controller_Base extends Controller_Template {

    public $template;
    protected $redirect = FALSE;
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
	}
	
	public function after() {
        
        if ($this->auto_render) {
            $global_fields = $this->_ses->get('global_fields', array());
            $deleted = 0;
            $controller = $this->request->controller();
            foreach($global_fields as $global_field) {
                $delete = true;
                if (in_array('*', $global_field['exclude_delete_from'])) {
                    $delete = false;
                } elseif (in_array(strtolower($controller.'/*'), $global_field['exclude_delete_from'])) {
                    $delete = false;
                } elseif (in_array(strtolower($controller.'/'.Request::current()->action()), $global_field['exclude_delete_from'])) {
                    $delete = false;
                }

                if ($delete === true) {
                    $this->_ses->delete($global_field['field']);
                    unset($global_fields[$global_field['field']]);
                    $deleted++;
                }
            }
        
            if ($deleted > 0) {
                $this->_ses->set('global_fields', $global_fields);
            }
            
            if ($redirect = $this->_ses->get('redirect')) {
                if ( $this->redirect === FALSE && (empty($redirect['controllers']) || ! in_array($this->request->controller(), $redirect['controllers']) || ! in_array($this->request->action(), $redirect['actions']))) {
                    $this->_ses->delete('redirect');
                }
            }
            
            if ($this->redirect !== FALSE) {
                $this->_ses->delete('redirect');
                if (is_null($redirect)) {
                    // default redirection if none is set
                    $this->request->redirect(URL::site(Route::get('admin')->uri()));
                }
                if ( ! empty($redirect['url'])) {
                    $url = URL::base(TRUE, FALSE).str_replace(URL::base(TRUE, FALSE), '', $this->request->url());
                    if ($url == $redirect['url']) {
                        $redirect['url'] = '';
                    }
                }
                
                if (empty($redirect['url'])) {
                    if ($controller != 'auth') {
                        $redirect['url'] = 'admin/'.$controller;
                    } else {
                        $redirect['url'] = URL::site(Route::get('admin')->uri());
                    }
                }
                
                $this->request->redirect($redirect['url']);
            }
		}
		
		parent::after();
	}

}


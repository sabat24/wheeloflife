<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Auth extends Controller_Admin_Template_Login {

    public function before() {
        parent::before();
        $this->content = '';
    }
    
    public function after() {
        $this->template->content = $this->content;
        parent::after();
    }
    
    public function action_login() {
        $model_user = Model::factory('Admin_User'); 
        if ( ($user = $this->_ses->get('user', FALSE)) !== FALSE) {
        
            if ($user['u_id'] != -1) {
                $this->request->redirect(URL::site(Route::get('admin/auth')->uri(array('action' => 'logout'))));
                return;
            }
        }
        
        do if ( ($token = $this->request->param('id', FALSE)) !== FALSE) {
            if ( ($u_id = $model_user->verify_login_token($token)) === FALSE) {
                break;
            }

            $user = $model_user->create_user_by_id($u_id);
            $this->_ses->set('user', $user);
            //$this->request->redirect('admin/users/edit/'.$u_id);
            return;
            
        } while(FALSE);
        
        $data = array();
        $this->template->title = __('Login');
        $this->content = View::factory('pages/admin/login')
            ->bind('data', $data_filtered)
            ->set('forgot', FALSE);
        
        
        if (HTTP_Request::POST == $this->request->method()) {
            if ($this->_login() === FALSE) return FALSE;
            $this->redirect = TRUE;
            return;
        } else {
            $redirect = $this->_ses->get('redirect');
            
            if ( ! $redirect) {
                $referer = URL::get_referer();
                if ( ! empty($referer)) {
                    $redirect = array('url' => $referer, 'controllers' => array($this->request->controller()), 'actions' => array($this->request->action()));
                    $this->_ses->set('redirect', $redirect);
                }
            }
        }
    }
    
    public function action_login2() {
        if (HTTP_Request::POST == $this->request->method()) {
            if ($this->_login() === TRUE) {
                $this->response = array(
                        'status' => 'ok',
                        'callback' => array (
                            'name' => 'login_response',
                        ),
                    );
            } else {
                $this->response = array(
                        'status' => 'error',
                        'callback' => array (
                            'name' => 'login_response',
                        ),
                    );
            }
        }
    }
    
    private function _login() {
        $data_filtered = arr::extract($this->request->post(), array('username', 'password', 'remember'));
            $data_filtered = Filtering::base_filtering($data_filtered);    
            
            $model_user = new Model_Admin_User();
            $validation = $model_user->validate_login($data_filtered);
            if ( ! $validation->check()){
                Hint::set(Hint::ERROR, $validation->errors('validation'));
                $data_filtered['password'] = '';
                return FALSE;
            }
            
            $login = $model_user->login($data_filtered['username'], $data_filtered['password']);

            if ($login['result'] === FALSE) {
                Hint::set(Hint::ERROR, $login['error']);
                $data_filtered['password'] = '';
                return FALSE;
            }
            
            if ( ! empty($data_filtered['remember'])) {
                $Cookie = md5( md5($data_filtered['password']) . md5($data_filtered['username'])).':'.$login['id'];
                Cookie::set('remember', $Cookie, 1728000); // 20 days
            }
            $this->_ses->delete('chart_token');
            $user = $model_user->create_user_by_id($login['id']);
            
            $this->_ses->set('user', $user);
        return TRUE;
    }
    
    public function action_forgot_password() {
        if (HTTP_Request::POST == $this->request->method()) {
            $data_filtered = arr::extract($this->request->post(), array('username'));
            $data_filtered = Filtering::factory($data_filtered)    
            ->filter('username', 'Filtering::sanitize')
            ->get();
            
            $model_users = new Model_Admin_User();
            if ( ($user = $model_users->get_user_by_login($data_filtered['username'])) === FALSE) {
                Hint::set(Hint::NOTICE, 'Jeśli wprowadzone dane są poprawne, otrzymasz wkrótce na swoją skrzynkę dalsze instrukcje postępowania.');
                $this->request->redirect('admin/login/');
            }
            
            $model_users->user_forgot_password($user);
            Hint::set(Hint::NOTICE, 'Jeśli wprowadzone dane są poprawne, otrzymasz wkrótce na swoją skrzynkę dalsze instrukcje postępowania.');
            $this->request->redirect('admin/login/');
        }
        $this->template->title = __('Forgot password');
        $this->content = View::factory('pages/admin/login')
            ->bind('data', $data_filtered)
            ->set('forgot', TRUE);
    }
    
    public function action_logout() {
        $this->_ses->destroy();
        Cookie::delete('remember');
        if ($this->request->is_ajax()) {
            $this->response = array(
                        'status' => 'ok',
                        'callback' => array (
                            'name' => 'logout_response',
                        ),
                    );
        } else {
            $this->request->redirect(URL::site(Route::get('admin/auth')->uri(array('action' => 'login'))));
        }
    }
    
    
}
?>
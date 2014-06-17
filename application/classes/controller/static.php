<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Static extends Controller_Default {
    
    public $variables = array();
    
   
    public function action_index() {
        $this->template->title = 'Wheel of Life';
        $this->template->description = '';
        $this->template->menu_index = $this->request->action();
        $tokens = $this->_ses->get('chart_token', array());
        if ( ($token = end($tokens)) === FALSE) {
            $token = '';
        };

        if ( count($tokens) >= Kohana::$config->load('main.max_charts_per_session') && $this->user['u_id'] == -1) {
            $this->request->redirect(URL::site(Route::get('static')->uri(array('action' => 'chart', 'id' => $token))));
            return;
        }
        
        
        $model_charts = new Model_Charts();
        $chart = $model_charts->get_empty_chart();
        $this->template->scripts = array (
            'assets/modules/admin/js/functions_validation.js',
        );
        $this->template->scripts = array_merge($this->template->scripts, $chart->getScripts());
        
        Javascript::add_jquery_ready($chart->render('main_chart'));
        Javascript::add_var('chart_token', '""');
        $pagination = '';
        if ($this->user['u_id'] == -1) {
            $forms = new Forms(new Model_Admin_Users(), 'users/create_account', TRUE, TRUE);
            $forms
                ->add_input('user_email')->add_input('user_name')
                ->set_submit_name(__('Send'));
            
            $form_html = $forms->generate('create_account', 'front', URL::site(Route::get('static')->uri(array('action' => 'create_account'))));
            $chart_data = FALSE;
            
            if (count($tokens > 0)) {
                $pagination = $model_charts->get_chart_pagination_by_tokens($tokens);
            }
            
        } else {
            $form_html = FALSE;
            $model_admin_charts = new Model_Admin_Charts();
            $chart_data = $model_admin_charts->get_last_users_chart($this->user['u_id'], FALSE);
        }
        
        $this->content = View::factory('pages/chart/chart_index')->set('form_html', $form_html)->set('user_data', $this->user)->set('chart_data', $chart_data)->set('pagination', $pagination);
    }
    
    public function action_submit_chart() {
        if (HTTP_Request::POST == $this->request->method()) {
            $tokens = $this->_ses->get('chart_token', array());
            
            if ( count($tokens) >= Kohana::$config->load('main.max_charts_per_session')) {
                Hint::set(Hint::ERROR, __('You can\'t submit more charts.'));
                $this->response = array(
                    'status' => 'error',
                );
                return;
            }
            $post = Filtering::force_int($this->request->post());
            $model_charts = new Model_Charts();
            $user_id = $this->user['u_id'] == -1 ? 0 : $this->user['u_id'];

            if (($token = $model_charts->submit_chart($post, $user_id)) === FALSE) {
                $this->response = array(
                    'status' => 'error',
                );
                return;
            }
            $model_charts = new Model_Charts();
            if ($this->user['u_id'] != -1) {
                $pagination = $model_charts->get_chart_pagination($this->user['u_id'], $token);
            } else {
                $tokens[] = $token;
                $pagination = $model_charts->get_chart_pagination_by_tokens($tokens, $token);
            }
            
            $this->response = array(
                    'status' => 'ok',
                    'callback' => array (
                        'name' => 'submit_chart_response',
                        'params' => array (
                            'url' => Route::get('static')->uri(array('action' => 'chart', 'id' => $token)),
                            'token' => $token,
                            'pagination' => $pagination,
                        ),
                    ),
                );
            
            
        }
    }
    
    public function action_chart() {
        if ( $this->request->is_ajax()) {
            return $this->_action_chart_ajax();
        }
        $this->template->title = 'Wheel of Life - Chart view';
        $this->template->description = '';
        if ( ($token = $this->request->param('id', FALSE)) === FALSE) {
            throw new HTTP_Exception_404;
        }
        
        $ses_tokens = $this->_ses->get('chart_token', array());

        $model_charts = new Model_Charts();
        if ( ($charts = $model_charts->get_chart_by_token($token)) === FALSE) {
            if ( ($key = array_search($token, $ses_tokens)) !== FALSE) {
                unset($ses_tokens[$key]);
                $ses_tokens = array_merge($ses_tokens);
                $this->_ses->set('chart_token', $ses_tokens);
            }
            throw new HTTP_Exception_404;
        }
        
        if ($charts['chart_data']['c_public'] == 0 && ! in_array($token, $ses_tokens) && $this->user['u_id'] != $charts['chart_data']['u_id']) {
            throw new HTTP_Exception_404;
        }
        
        Javascript::add_jquery_ready($charts['chart']->render('main_chart'));
        Javascript::add_var('chart_token', '"'.$token.'"');
        
        $charts['chart_data']['_can_submit'] = count($ses_tokens) < Kohana::$config->load('main.max_charts_per_session');

        // new chart - create account
        if ($this->user['u_id'] == -1 && in_array($token, $ses_tokens)) {

            $forms = new Forms(new Model_Admin_Users(), 'users/create_account', TRUE, TRUE);
            $forms
                ->add_input('user_email')->add_input('user_name')
                ->set_submit_name(__('Send'));
            
            $form_html = $forms->generate('create_account', 'front', URL::site(Route::get('static')->uri(array('action' => 'create_account'))));
        
            $this->template->scripts = array (
                'assets/modules/admin/js/functions_validation.js',
            );
            
            $pagination = $model_charts->get_chart_pagination_by_tokens($ses_tokens, $token);

            $this->content = View::factory('pages/chart/chart_submit')
                ->set('form_html', $form_html)
                ->set('chart_data', $charts['chart_data'])
                ->set('pagination', $pagination)
                ;
        // own chart
        } elseif ($this->user['u_id'] == $charts['chart_data']['u_id']) {
            $pagination = $model_charts->get_chart_pagination($this->user['u_id'], $token);
            $this->content = View::factory('pages/chart/chart_change')->set('chart_data', $charts['chart_data'])->set('pagination', $pagination);
        // just show chart
        } else {
            if ($charts['chart_data']['u_id'] == 0) {
                $pagination = '';
            } else {
                $pagination = $model_charts->get_chart_pagination($charts['chart_data']['u_id'], $token, TRUE);
            }
            $this->content = View::factory('pages/chart/chart_show')->set('pagination', $pagination);
        }
        
        $this->template->scripts = array_merge($this->template->scripts, $charts['chart']->getScripts());
    }
    
    
    private function _action_chart_ajax() {
        if ( ($token = $this->request->param('id', FALSE)) === FALSE) {
            Hint::set(Hint::ERROR, __('There is no chart token.'));
            $this->response = array(
                    'status' => 'error',
                    'callback' => array (
                        'name' => 'get_chart_response',
                    ),
                );
                return;
        }
        
        if ( ($chart_id = Arr::get($this->request->post(), 'chart_id', FALSE)) === FALSE) {
            Hint::set(Hint::ERROR, __('There is no chart ID param.'));
            $this->response = array(
                    'status' => 'error',
                    'callback' => array (
                        'name' => 'get_chart_response',
                    ),
                );
                return;
        }
        
        
        $model_admin_charts = new Model_Admin_Charts();
        if ( ($chart_data = $model_admin_charts->get_chart_by_token($token)) === FALSE) {
            Hint::set(Hint::ERROR, __('There is no chart with following token: :token'), array(':token' => $token));
            $this->response = array(
                    'status' => 'error',
                    'callback' => array (
                        'name' => 'get_chart_response',
                    ),
                );
                return;
        }
        
        if ($chart_data['c_public'] == 0 && $this->user['u_id'] != $chart_data['u_id'] && ! in_array($token, $this->_ses->get('chart_token', array()))) {
            Hint::set(Hint::ERROR, __('Current chart is private.'));
            $this->response = array(
                    'status' => 'error',
                    'callback' => array (
                        'name' => 'get_chart_response',
                    ),
                );
                return;
        }
        
        $evaluation = $model_admin_charts->get_chart_evaluation($chart_data['c_id']);
        $series_data = array();
        foreach($evaluation as $item) {
            $series_data[] = (int) $item['e_value'];
        }
        
        $model_charts = new Model_Charts();
        if ($chart_data['u_id'] == 0) {
            $tokens = $this->_ses->get('chart_token', array());
            $pagination = $model_charts->get_chart_pagination_by_tokens($tokens, $token);
        } else {
            $pagination = $model_charts->get_chart_pagination($chart_data['u_id'], $token, $chart_data['u_id'] != $this->user['u_id']);
        }
        
        $this->response = array(
                    'status' => 'ok',
                    'callback' => array (
                        'name' => 'get_chart_response',
                        'params' => array (
                            'chart' => array(
                                'data' => $series_data,
                                'subtitle' => array (
                                    'text' => Date::local_format_date($chart_data['c_date_created']),
                                ),
                            ),
                            'pagination' => $pagination,
                            'chart_id' => $chart_id,
                            'url' => Route::get('static')->uri(array('action' => 'chart', 'id' => $token)),
                            'token' => $token,
                            'chart_public' => $chart_data['c_public'],
                        ),
                    ),
                );
        return TRUE;
    }
    
    public function action_create_account() {
        if (HTTP_Request::POST == $this->request->method()) {
            $tokens = $this->_ses->get('chart_token', array());
            $token = end($tokens);
            if ($token === FALSE) {
                Hint::set(Hint::ERROR, __('There is no chart token.'));
                $this->response = array(
                    'status' => 'error',
                    'callback' => array (
                        'name' => 'create_account_response',
                    ),
                );
                return;
            }
            $model_users = new Model_Admin_Users();
            $forms = new Forms($model_users, 'users/create_account', TRUE, TRUE);
            $forms
                ->add_input('user_email')->add_input('user_name')
                ->add_input('user_admin', 0, 1)->add_input('user_date_created', Date::local_format_datetime(), 1)->add_input('user_deleted', 0, 1) 
                ;
            $response = $forms->validate($this->request->post(), 'create_account');
      
            // ignore soft errors, which are used for ajax communication purposes only
            if ($response['status'] == 'ok' || ($response['status'] == 'error' && count($response['errors']) == 0)) {
                $post_filtered = $forms->get_post_filtered();
                if ($model_users->create_account($post_filtered, $tokens) === FALSE) {
                    $this->response = array(
                        'status' => 'error',
                        'callback' => array (
                            'name' => 'create_account_response',
                        ),
                    );
                } else {
                    $model_user = new Model_Admin_User();
                    $login = $model_user->login($post_filtered['user_email'], $post_filtered['password']);

                    if ($login['result'] === FALSE) {
                        Hint::set(Hint::ERROR, __('Your account was created, but you hasn\'t been logged in.'));
                        $this->response = array(
                            'status' => 'error',
                            'callback' => array (
                                'name' => 'create_account_response',
                            ),
                        );
                        return;
                    }
            
                    $this->_ses->delete('chart_token');
                    $user = $model_user->create_user_by_id($post_filtered['u_id']);
            
                    $this->_ses->set('user', $user);
                    
                    $this->response = array(
                        'status' => 'ok',
                        'callback' => array (
                            'name' => 'create_account_response',
                        ),
                    );
                }
            } else {
                $this->response = array(
                        'status' => 'error',
                        'callback' => array (
                            'name' => 'create_account_response',
                        ),
                    );
            }
        }
    }
    
    public function action_change_chart_visibility() {
        if (HTTP_Request::POST == $this->request->method()) {
            $post = Arr::extract($this->request->post(), array('token'), FALSE);
            $post = Filtering::base_filtering($post);
            $token = $post['token'];
            if ( $token === FALSE) {
                Hint::set(Hint::ERROR, __('There is no chart token.'));
                $this->response = array(
                    'status' => 'error',
                    'callback' => array (
                        'name' => 'change_chart_visibility_response',
                    ),
                );
                return;
            }
        
            $model_admin_charts = new Model_Admin_Charts();
            if ( ($chart_data = $model_admin_charts->get_chart_by_token($token)) === FALSE) {
                Hint::set(Hint::ERROR, __('There is no chart with following token: :token'), array(':token' => $token));
                    $this->response = array(
                        'status' => 'error',
                        'callback' => array (
                            'name' => 'change_chart_visibility_response',
                        ),
                    );
                return;
            }
        
            if ( ! in_array($token, $this->_ses->get('chart_token', array())) && $this->user['u_id'] != $chart_data['u_id']) {
                Hint::set(Hint::ERROR, __('You are not allowed to do that.'));
                    $this->response = array(
                        'status' => 'error',
                        'callback' => array (
                            'name' => 'change_chart_visibility_response',
                        ),
                    );
                return;
            }
        
            $model_charts = new Model_Charts();
            if ( ($new_visibility = $model_charts->change_chart_visibility($chart_data['c_id'], $chart_data['c_public'])) === FALSE) {
                Hint::set(Hint::ERROR, __('Error during operation.'));
                    $this->response = array(
                        'status' => 'error',
                        'callback' => array (
                            'name' => 'change_chart_visibility_response',
                        ),
                    );
                return;
            }
        
            $this->response = array(
                    'status' => 'ok',
                    'callback' => array (
                        'name' => 'change_chart_visibility_response',
                        'params' => array (
                            'public' => $new_visibility,
                        ),
                    ),
                );
        }
        
    }
    
    
}?>
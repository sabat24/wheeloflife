<?php
defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Users extends Controller_Admin_Template_Default {
    
    public function before() {
        parent::before();
        
        if ($this->request->is_ajax()) {
            
        } else {
            if ($this->auto_render) {
                $route = Route::get('admin');
                $controller = $this->request->controller();
                $this->menu->add_sub_menu(array('title' => __('List'), 'url' => URL::site($route->uri(array('controller' => $controller))), 'selected' => FALSE), 'index');
                $this->menu->add_sub_menu(array('title' => __('Add user'), 'url' => URL::site($route->uri(array('controller' => $controller, 'action' => 'add_user'))), 'selected' => FALSE, 'class' => 'save-filter'), 'add_user');
            }
        }
    }
    
    public function after() {
        if ($this->request->is_ajax()) {
        
        } else {
            if ($this->auto_render) {

            }
        }
        parent::after();
    }

    public function action_index() {
        $model_users = new Model_Admin_Users();
        
        if ( $this->request->is_ajax()) {
            $this->action_get_users();
            return;
        }

        $default_sorting = array('id', 'asc');
        
        $list_header = $model_users->get_header_list();
        $filter = new Filter($model_users);
        $filter->set_default_sorting($default_sorting);
        
        $filter->set_filter($this->_ses->get('filter', FALSE));
        
        $filter->set_letter_filter_state(TRUE);
        if ($this->request->param('id', FALSE) == 'letter') {
            $filter->set_letter_filter($this->request->param('id2', FALSE));
        }
        $model_users->set_filter_sql_params($filter);
        
        $filter->set_sorting($list_header);
        
        
        $filter->render_to_js();
        
        $filter->add_input('user_name')->add_input('user_email')->add_date('date_created', __('User date created from - to'));
        
        $this->template->scripts = array (
            //'assets/vendor/js/msdropdown/jquery.dd.js',
            //'assets/js/admin/functions_validation.js',
            //'assets/js/admin/functions_forms.js',
            'assets/modules/admin/js/users.js',
        );
        /*
        $this->template->styles = array (
            'assets/vendor/css/msdropdown/dd.css' => 'screen',
        );
        */
        
        // FILTER
        $filter_view = $filter->generate('users', 'users-list');
        Portlets::add_to_render('user-filter', __('Search user'), array('icon' => 'users', 'view' => $filter_view));
        
        
        // view
        $view_block = $model_users->get_users_list($filter);
        
        $view = View::factory('pages/admin/users/users_list_table', $view_block)
            ->set('list_header', $list_header)
        ;
        Portlets::add_to_render('users-list', __('Users'), array(
            'icon' => 'users',
            'view' => $view,
            'callback_url' => Route::get('admin')->uri(array('controller' => 'users', 'action' => 'get_users')),
            'chk_selected_total' => count(Session::instance()->get('chk_user', array())),
        ));
        $this->content = View::factory('pages/admin/users/default');
    }
    
     public function action_get_users() {
        $post = Filtering::base_filtering($this->request->post());
        $model_users = new Model_Admin_Users();
        $filter = $model_users->get_filter_ajax($post);
        $block_view = $model_users->get_users_list($filter);

        $this->response = array (
            'status' => 'ok',
            'html' => $block_view['view']->render(),
            'pagination' => $block_view['pagination']->render(),
            'callback' => array (
                    'name' => 'set_user_list',
                ),
        );

    }
    
    public function action_add_user() {
        if ( ! $this->a2->allowed('users', 'add')) {
            $this->request->redirect(URL::site(Route::get('admin/auth')->uri(array('action' => 'login'))));
        }
        $redirect = $this->_ses->get('redirect');
        if ( ! $redirect) {
            $url = URL::get_referer();
            if (empty($url)) {
                $url = URL::site(Route::get('admin')->uri(array('controller' => $this->request->controller())));
            }
            $redirect = array('url' => $url, 'controllers' => array($this->request->controller()), 'actions' => array($this->request->action()));
            $this->_ses->set('redirect', $redirect);
        }
        
        $model_users = new Model_Admin_Users();
        
        $forms = new Forms($model_users, 'users/add_user');
        $forms
            ->add_input('user_email')->add_input('user_name')->add_password()
            ->add_input('user_admin', 0, 1)->add_input('user_date_created', Date::local_format_datetime(), 1)->add_input('user_deleted', 0, 1) 
            ->set_redirect($redirect['url'])
            ->set_submit_name(__('Add user'));

        if (HTTP_Request::POST == $this->request->method()) {
            $response = $forms->validate($this->request->post(), 'add_user');
      
            // ignore soft errors, which are used for ajax communication purposes only
            if ($response['status'] == 'ok' || ($response['status'] == 'error' && count($response['errors']) == 0)) {
                $post_filtered = $forms->get_post_filtered();
                if ($model_users->save_user($post_filtered) === FALSE) {
                    Hint::set(Hint::ERROR, __('New user hasn\'t been added.'));
                } else {
                    Hint::set(Hint::SUCCESS, __('New user was added.'));
                    $this->redirect = TRUE;
                    return;
                }
                
            } else {
                // errors are inside forms class
            }
        }
        
        $form_html = $forms->generate('add_user');
        
        $this->template->scripts = array (
            'assets/modules/admin/js/functions_validation.js',
        );
        Portlets::add_to_render('user-add', __('Add user'), array('icon' => 'users', 'view' => $form_html));
        $this->content = View::factory('pages/admin/users/default');
    }
    
    public function action_user_edit() {
        

        if ( ! $this->a2->allowed('users', 'edit')) {
            $this->request->redirect(URL::site(Route::get('admin/auth')->uri(array('action' => 'login'))));
        }
        $get_filtered = array('u_id' => intval($this->request->param('id', 0)));
        $redirect = $this->_ses->get('redirect');
        if ( ! $redirect) {
            $url = URL::get_referer();
            if (empty($url)) {
                $url = URL::site(Route::get('admin')->uri(array('controller' => $this->request->controller())));
            }
            $redirect = array('url' => $url, 'controllers' => array($this->request->controller()), 'actions' => array($this->request->action()));
            $this->_ses->set('redirect', $redirect);
        }
        
        $model_users = new Model_Admin_Users();
        if ( ($user = $model_users->get_user_by_id($get_filtered['u_id'])) === FALSE) {
            Hint::set(Hint::ERROR, __('There is no user with following ID: :id', array(':id' => $get_filtered['u_id'])));            
            $this->redirect = TRUE;
            return;
        }
        $forms = new Forms($model_users, 'users/edit_user', TRUE);
        $forms
            ->add_input('user_email', '', 0, array('rel' => 'user_id'))->add_input('user_name')->add_password()
            ->add_hidden('user_id', $user['u_id'])
            ->set_redirect($redirect['url'])
            ->set_submit_name(__('Edit user'));
        
        $db_fields = $model_users->get_user_dbfields();
        $forms->set_default_values_from_db($user, $db_fields);
        
        
        if (HTTP_Request::POST == $this->request->method()) {
            $response = $forms->validate($this->request->post(), 'edit_user');
      
            // ignore soft errors, which are used for ajax communication purposes only
            if ($response['status'] == 'ok' || ($response['status'] == 'error' && count($response['errors']) == 0)) {
                $post_filtered = $forms->get_modified_fields('user_id');
                if (empty($post_filtered)) {
                    Hint::set(Hint::NOTICE, __('No changes to save.'));
                    $this->redirect = TRUE;
                    return;
                }

                if ($model_users->save_user($post_filtered) === FALSE) {
                    Hint::set(Hint::ERROR, __('Data haven\'t been saved.'));
                } else {
                    Hint::set(Hint::SUCCESS, __('User data were saved.'));
                    $this->redirect = TRUE;
                    return;
                }
                
            } else {
                // errors are inside forms class
            }
        }
        $form_html = $forms->generate('edit_user');
        
        $this->template->scripts = array (
            'assets/modules/admin/js/functions_validation.js',
        );
        $this->menu->add_sub_menu(array('title' => __('Edit user'), 'url' => URL::site(Route::get('admin')->uri(array('controller' => $this->request->controller(), 'action' => $this->request->action(), 'id' => $get_filtered['u_id']))), 'selected' => TRUE), 'edit_user');
        
        Portlets::add_to_render('user-edit', __('Edit user'), array('icon' => 'users', 'view' => $form_html));
        $this->content = View::factory('pages/admin/users/default');
    }
    
    public function action_user_remove() {
        if ( ! $this->a2->allowed('users', 'delete')) {
            if ( ! $this->request->is_ajax()) {
                $this->request->redirect(URL::site(Route::get('admin/auth')->uri(array('action' => 'login'))));
            } else {
                Hint::set(Hint::ERROR, __('Access forbiden.'));
                $this->response = array (
                    'status' => 'error',
                );
                return;
            }
        }
        $get_filtered = array('u_id' => intval($this->request->param('id', 0)));
        if ( ! $this->request->is_ajax()) {
            $redirect = $this->_ses->get('redirect');
            if ( ! $redirect) {
                $url = URL::get_referer();
                if (empty($url)) {
                    $url = URL::site(Route::get('admin')->uri(array('controller' => $this->request->controller())));
                }
                $redirect = array('url' => $url, 'controllers' => array($this->request->controller()), 'actions' => array($this->request->action()));
                $this->_ses->set('redirect', $redirect);
            }
        }
        
        $model_users = new Model_Admin_Users();
        if ( ($user = $model_users->get_user_by_id($get_filtered['u_id'])) === FALSE) {
            Hint::set(Hint::ERROR, __('There is no user with following ID: :id', array(':id' => $get_filtered['u_id'])));
            if ( ! $this->request->is_ajax()) {
                $this->redirect = TRUE;
            } else {
                $this->response = array (
                    'status' => 'error',
                );
            }
            return;
        }
        
        if ($model_users->remove_user($get_filtered['u_id']) !== FALSE ) {
            Hint::set(Hint::SUCCESS, __('User was removed.'));
            $this->response = array(
                'status' => 'ok',
                'callback' => array (
                    'name' => 'user_load_data',
                    'url' => Route::get('admin')->uri(array('controller' => 'users', 'action' => 'get_users')),
                ),
            );
            return;
        } else {
            Hint::set(Hint::ERROR, __('User hasn\'t benn removed.'));
            $this->response = array('status' => 'error');
            return;
        }
    }
    
    public function action_ac_search() {
        $this->auto_render = FALSE;
        if ( ($field = $this->request->param('id', FALSE)) === FALSE) {
            $this->response = json_encode(array('ac' => '')); 
            return;
        };
        
        $post_filtered = Filtering::base_filtering($this->request->post());

        if ( ($term = Arr::get($post_filtered, 'term', FALSE)) === FALSE) {
            $this->response = json_encode(array('ac' => '')); 
            return;
        }
        
        $ac_search_filter = Arr::path($post_filtered, 'filter.ac_search', array());
        $model_users = Model::factory('Admin_Users');
        $results = $model_users->get_ac_search($field, $term, $ac_search_filter);
        $response = array();
        foreach($results as $item) {
            switch($field) {
                case 'user_name':
                    $response[] = array('id' => $item['u_id'], 'label' => $item['u_name'], 'value' => $item['u_name']);
                break;
                case 'user_email':
                    $response[] = array('id' => $item['u_id'], 'label' => $item['u_email'].' ('.$item['u_name'].')', 'value' => $item['u_email']);
                break;
            }
        }
        $this->response = json_encode(array (
            'ac' => $response,
            'callback' => array (
                'name' => 'user_load_data',
                'url' => Route::get('admin')->uri(array('controller' => 'users', 'action' => 'get_users')),
            ),
        ));
    }
}
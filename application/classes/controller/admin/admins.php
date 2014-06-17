<?php
defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Admins extends Controller_Admin_Template_Default {
    
    public function before() {
        parent::before();
        
        if ($this->request->is_ajax()) {
            
        } else {
            if ($this->auto_render) {
                $route = Route::get('admin');
                $controller = $this->request->controller();
                $this->menu->add_sub_menu(array('title' => __('List'), 'url' => URL::site($route->uri(array('controller' => $controller))), 'selected' => FALSE), 'index');
                $this->menu->add_sub_menu(array('title' => __('Add admin'), 'url' => URL::site($route->uri(array('controller' => $controller, 'action' => 'add_admin'))), 'selected' => FALSE, 'class' => 'save-filter'), 'add_admin');
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
            $this->action_get_admins();
            return;
        }

        $default_sorting = array('id', 'asc');
        
        $list_header = $model_users->get_header_list('admins');
        $filter = new Filter($model_users);
        $filter->set_default_sorting($default_sorting);
        
        $filter->set_filter($this->_ses->get('filter', FALSE));
        
        $model_users->set_filter_sql_params($filter);
        
        $filter->set_sorting($list_header);
        
        
        $filter->render_to_js();
        
        $filter->add_input('user_name')->add_input('user_email')->add_date('date_created', __('Admin date created from - to'));
        
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
        $filter_view = $filter->generate('admins', 'admins-list');
        Portlets::add_to_render('admin-filter', __('Search admin'), array('icon' => 'users', 'view' => $filter_view));
        
        
        // view
        $view_block = $model_users->get_admins_list($filter);
        
        $view = View::factory('pages/admin/admins/admins_list_table', $view_block)
            ->set('list_header', $list_header)
        ;
        Portlets::add_to_render('admins-list', __('Admins'), array(
            'icon' => 'users',
            'view' => $view,
            'callback_url' => Route::get('admin')->uri(array('controller' => 'admins', 'action' => 'get_admins')),
        ));
        $this->content = View::factory('pages/admin/admins/default');
    }
    
     public function action_get_admins() {
        $post = Filtering::base_filtering($this->request->post());
        $model_users = new Model_Admin_Users();
        $filter = $model_users->get_filter_ajax($post);
        $block_view = $model_users->get_admins_list($filter);

        $this->response = array (
            'status' => 'ok',
            'html' => $block_view['view']->render(),
            'pagination' => $block_view['pagination']->render(),
            'callback' => array (
                    'name' => 'set_admin_list',
                ),
        );

    }
    
    public function action_add_admin() {
        if ( ! $this->a2->allowed('admins', 'add')) {
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
            ->add_input('user_admin', 1, 1)->add_input('user_date_created', Date::local_format_datetime(), 1)->add_input('user_deleted', 0, 1) 
            ->set_redirect($redirect['url'])
            ->set_submit_name(__('Add admin'));

        if (HTTP_Request::POST == $this->request->method()) {
            $response = $forms->validate($this->request->post(), 'add_user');
      
            // ignore soft errors, which are used for ajax communication purposes only
            if ($response['status'] == 'ok' || ($response['status'] == 'error' && count($response['errors']) == 0)) {
                $post_filtered = $forms->get_post_filtered();
                if ($model_users->save_user($post_filtered) === FALSE) {
                    Hint::set(Hint::ERROR, __('New admin hasn\'t been added.'));
                } else {
                    Hint::set(Hint::SUCCESS, __('New admin was added.'));
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
        Portlets::add_to_render('admin-add', __('Add admin'), array('icon' => 'users', 'view' => $form_html));
        $this->content = View::factory('pages/admin/admins/default');
    }
    
    public function action_admin_edit() {
        

        if ( ! $this->a2->allowed('admins', 'edit')) {
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
        if ( ($user = $model_users->get_admin_by_id($get_filtered['u_id'])) === FALSE) {
            Hint::set(Hint::ERROR, __('There is no admin with following ID: :id', array(':id' => $get_filtered['u_id'])));            
            $this->redirect = TRUE;
            return;
        }
        $forms = new Forms($model_users, 'users/edit_user', TRUE);
        $forms
            ->add_input('user_email', '', 0, array('rel' => 'user_id'))->add_input('user_name')->add_password()
            ->add_hidden('user_id', $user['u_id'])
            ->set_redirect($redirect['url'])
            ->set_submit_name(__('Edit admin'));
        
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
                    Hint::set(Hint::SUCCESS, __('Admin data were saved.'));
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
        $this->menu->add_sub_menu(array('title' => __('Edit admin'), 'url' => URL::site(Route::get('admin')->uri(array('controller' => $this->request->controller(), 'action' => $this->request->action(), 'id' => $get_filtered['u_id']))), 'selected' => TRUE), 'edit_admin');
        
        Portlets::add_to_render('admin-edit', __('Edit user'), array('icon' => 'users', 'view' => $form_html));
        $this->content = View::factory('pages/admin/admins/default');
    }
    
    public function action_admin_remove() {
        if ( ! $this->a2->allowed('admins', 'delete')) {
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
        
        if ($get_filtered['u_id'] == $this->user['u_id']) {
            Hint::set(Hint::ERROR, __('You can\'t remove yourself.'));
            $this->redirect = TRUE;
            return;
        }
        
        $model_users = new Model_Admin_Users();
        if ( ($user = $model_users->get_admin_by_id($get_filtered['u_id'])) === FALSE) {
            Hint::set(Hint::ERROR, __('There is no admin with following ID: :id', array(':id' => $get_filtered['u_id'])));
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
            Hint::set(Hint::SUCCESS, __('Admin was removed.'));
            $this->response = array(
                'status' => 'ok',
                'callback' => array (
                    'name' => 'admin_load_data',
                    'url' => Route::get('admin')->uri(array('controller' => 'users', 'action' => 'get_admins')),
                ),
            );
            return;
        } else {
            Hint::set(Hint::ERROR, __('Admin hasn\'t been removed.'));
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
        $ac_search_filter['user_admin'] = 1;
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
                'name' => 'admin_load_data',
                'url' => Route::get('admin')->uri(array('controller' => 'users', 'action' => 'get_users')),
            ),
        ));
    }
}
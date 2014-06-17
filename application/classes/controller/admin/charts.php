<?php
defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Charts extends Controller_Admin_Template_Default {
    
    public function before() {
        parent::before();
        
        if ( ! $this->a2->allowed('charts', 'read')) {
            $this->request->redirect(URL::site(Route::get('admin/auth')->uri(array('action' => 'login'))));
        }
        
        if ($this->request->is_ajax()) {
            
        } else {
            if ($this->auto_render) {
                $route = Route::get('admin');
                $controller = $this->request->controller();
                $this->menu->add_sub_menu(array('title' => __('List'), 'url' => URL::site($route->uri(array('controller' => $controller))), 'selected' => FALSE), 'index');
                $this->menu->add_sub_menu(array('title' => __('Categories'), 'url' => URL::site($route->uri(array('controller' => $controller, 'action' => 'chart_categories'))), 'selected' => FALSE), 'chart_categories');
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
        
        if ( $this->request->is_ajax()) {
            $this->action_get_charts();
            return;
        }
        $model_charts = new Model_Admin_Charts();
        $default_sorting = array('id', 'asc');
        
        $list_header = $model_charts->get_header_list();
        $filter = new Filter($model_charts);
        $filter->set_default_sorting($default_sorting);
        
        $filter->set_filter($this->_ses->get('filter', FALSE));
        
        $model_charts->set_filter_sql_params($filter);
        
        $filter->set_sorting($list_header);
        
        
        $filter->render_to_js();
        
        $filter->add_input('user_name')->add_label(__('User\'s name'))->add_input('user_email')->add_label(__('User\'s email'))->add_date('date_created', __('Chart date created from - to'));
        
        $this->template->scripts = array (
            //'assets/vendor/js/msdropdown/jquery.dd.js',
            //'assets/js/admin/functions_validation.js',
            //'assets/js/admin/functions_forms.js',
            'assets/js/admin/pages/charts.js',
        );
        /*
        $this->template->styles = array (
            'assets/vendor/css/msdropdown/dd.css' => 'screen',
        );
        */
        
        // FILTER
        $filter_view = $filter->generate('charts', 'charts-list');
        Portlets::add_to_render('chart-filter', __('Search chart'), array('icon' => 'charts', 'view' => $filter_view));
        
        
        // view
        $view_block = $model_charts->get_charts_list($filter);
        
        $view = View::factory('pages/admin/charts/charts_list_table', $view_block)
            ->set('list_header', $list_header)
        ;
        Portlets::add_to_render('charts-list', __('Charts'), array(
            'icon' => 'charts',
            'view' => $view,
            'callback_url' => Route::get('admin')->uri(array('controller' => 'charts', 'action' => 'get_charts')),
            'chk_selected_total' => count(Session::instance()->get('chk_chart', array())),
        ));
        $this->content = View::factory('pages/admin/charts/default');
    }
    
     public function action_get_charts() {
        $post = Filtering::base_filtering($this->request->post());
        $model_admin_charts = new Model_Admin_Charts();
        $filter = $model_admin_charts->get_filter_ajax($post);
        $block_view = $model_admin_charts->get_charts_list($filter);

        $this->response = array (
            'status' => 'ok',
            'html' => $block_view['view']->render(),
            'pagination' => $block_view['pagination']->render(),
            'callback' => array (
                    'name' => 'set_chart_list',
                ),
        );

    }
    
    public function action_chart_categories() {
        if ( ! $this->a2->allowed('charts_categories', 'read')) {
            $this->request->redirect(URL::site(Route::get('admin/auth')->uri(array('action' => 'login'))));
        }
        $this->menu->add_sub_menu(array('title' => __('Add category'), 'url' => URL::site(Route::get('admin')->uri(array('controller' => $this->request->controller(), 'action' => 'chart_add_category'))), 'selected' => FALSE), 'chart_add_category');
        $model_charts = new Model_Admin_Charts();
		$chart_categories = $model_charts->get_chart_categories_sorted();

		
		$this->template->scripts = array('assets/js/admin/pages/charts.js');
        
        $view = View::factory('pages/admin/charts/chart_categories')
            ->set('chart_categories', $chart_categories)
        ;
        
        Portlets::add_to_render('chart-categories', __('Chart categories'), array('icon' => 'charts', 'view' => $view));
        $this->content = View::factory('pages/admin/charts/default');
        
    }
    
    public function action_chart_add_category() {
        if ( ! $this->a2->allowed('charts_categories', 'add')) {
            $this->request->redirect(URL::site(Route::get('admin/auth')->uri(array('action' => 'login'))));
        }
        $this->menu->add_sub_menu(array('title' => __('Add category'), 'url' => URL::site(Route::get('admin')->uri(array('controller' => $this->request->controller(), 'action' => 'chart_add_category'))), 'selected' => FALSE), 'chart_add_category');
        
        $redirect = $this->_ses->get('redirect');
        if ( ! $redirect) {
            $url = URL::get_referer();
            if (empty($url)) {
                $url = URL::site(Route::get('admin')->uri(array('controller' => $this->request->controller())));
            }
            $redirect = array('url' => $url, 'controllers' => array($this->request->controller()), 'actions' => array($this->request->action()));
            $this->_ses->set('redirect', $redirect);
        }
        
        $model_charts = new Model_Admin_Charts();
        
        $chart_categories = $model_charts->get_chart_categories_sorted_for_select();
        
        
        $forms = new Forms($model_charts, 'charts/chart_add_category');
        $forms
            ->add_input('chart_category_name')->add_select('chart_category_order', $chart_categories, -1)
            ->add_input('chart_category_deleted', 0, 1) 
            ->set_redirect($redirect['url'])
            ->set_submit_name(__('Add chart category'));

        if (HTTP_Request::POST == $this->request->method()) {
            $response = $forms->validate($this->request->post(), 'chart_add_category');
      
            // ignore soft errors, which are used for ajax communication purposes only
            if ($response['status'] == 'ok' || ($response['status'] == 'error' && count($response['errors']) == 0)) {
                $post_filtered = $forms->get_post_filtered();
                if ($model_charts->save_chart_category($post_filtered) === FALSE) {
                    Hint::set(Hint::ERROR, __('New chart category hasn\'t been added.'));
                } else {
                    Hint::set(Hint::SUCCESS, __('New chart category was added.'));
                    $this->redirect = TRUE;
                    return;
                }
            } else {
                // errors are inside forms class
            }
        }
        
        $form_html = $forms->generate('chart_add_category');
        
        $this->template->scripts = array (
            'assets/modules/admin/js/functions_validation.js',
        );
        Portlets::add_to_render('chart-add-category', __('Add chart category'), array('icon' => 'charts', 'view' => $form_html));
        $this->content = View::factory('pages/admin/charts/default');
        
    }
    
    public function action_chart_edit_category() {
        if ( ! $this->a2->allowed('chart_categories', 'edit')) {
            $this->request->redirect(URL::site(Route::get('admin/auth')->uri(array('action' => 'login'))));
        }
        $get_filtered = array('cc_id' => intval($this->request->param('id', 0)));
        $redirect = $this->_ses->get('redirect');
        if ( ! $redirect) {
            $url = URL::get_referer();
            if (empty($url)) {
                $url = URL::site(Route::get('admin')->uri(array('controller' => $this->request->controller())));
            }
            $redirect = array('url' => $url, 'controllers' => array($this->request->controller()), 'actions' => array($this->request->action()));
            $this->_ses->set('redirect', $redirect);
        }
        $this->menu->add_sub_menu(array('title' => __('Edit category'), 'url' => URL::site(Route::get('admin')->uri(array('controller' => $this->request->controller(), 'action' => 'chart_edit_category', 'id' => $get_filtered['cc_id']))), 'selected' => FALSE), 'chart_edit_category');
        
        $model_charts = new Model_Admin_Charts();
        if ( ($chart_category = $model_charts->get_chart_category_by_id($get_filtered['cc_id'])) === FALSE) {
            Hint::set(Hint::ERROR, __('There is no chart category with following ID: :id', array(':id' => $get_filtered['cc_id'])));            
            $this->redirect = TRUE;
            return;
        }
        
        $chart_category['cc_order']--;
        $chart_categories = $model_charts->get_chart_categories_sorted_for_select('first');
        unset($chart_categories[$get_filtered['cc_id']]);
        $forms = new Forms($model_charts, 'charts/chart_edit_category', TRUE);
        
        
        $forms
            ->add_input('chart_category_name')->add_select('chart_category_order', $chart_categories) 
            ->set_redirect($redirect['url'])
            ->set_submit_name(__('Edit chart category'));
        
        $db_fields = $model_charts->get_chart_category_dbfields();
        $forms->set_default_values_from_db($chart_category, $db_fields);

        if (HTTP_Request::POST == $this->request->method()) {
            $response = $forms->validate($this->request->post(), 'chart_edit_category');
      
            // ignore soft errors, which are used for ajax communication purposes only
            if ($response['status'] == 'ok' || ($response['status'] == 'error' && count($response['errors']) == 0)) {
                $post_filtered = $forms->get_modified_fields();
                
                if (empty($post_filtered)) {
                    Hint::set(Hint::NOTICE, __('No changes to save.'));
                    $this->redirect = TRUE;
                    return;
                }
                $post_filtered['chart_category_id'] = $get_filtered['cc_id'];
                if ($model_charts->save_chart_category($post_filtered) === FALSE) {
                    Hint::set(Hint::ERROR, __('Category hasn\'t been saved.'));
                } else {
                    Hint::set(Hint::SUCCESS, __('Category was saved.'));
                    $this->redirect = TRUE;
                    return;
                }
                
            } else {
                // errors are inside forms class
            }
        }
        $form_html = $forms->generate('chart_edit_category');
        
        $this->template->scripts = array (
            'assets/modules/admin/js/functions_validation.js',
        );
        Portlets::add_to_render('chart-edit-category', __('Edit chart category'), array('icon' => 'charts', 'view' => $form_html));
        $this->content = View::factory('pages/admin/charts/default');
    }
    
    public function action_chart_save_categories_order() {
        if (HTTP_Request::POST == $this->request->method()) {
            $post = Filtering::base_filtering($this->request->post());
            
            $model_charts = new Model_Admin_Charts();
            if ($model_charts->save_chart_categories_order($post['new_order']) === FALSE) {
                Hint::set(Hint::ERROR, __('New order hasn\'t been saved.'));
                $this->response = array('status' => 'error');
            } else {
                Hint::set(Hint::SUCCESS, __('Order was saved.'));
                $this->response = array('status' => 'ok');
            }
		}
    }
    
    public function action_chart_remove_category() {
        if ( ! $this->a2->allowed('chart_categories', 'delete')) {
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
        $get_filtered = array('cc_id' => intval($this->request->param('id', 0)));
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
        
        $model_charts = new Model_Admin_Charts();
        if ( ($chart_category = $model_charts->get_chart_category_by_id($get_filtered['cc_id'])) === FALSE) {
            Hint::set(Hint::ERROR, __('There is no chart category with following ID: :id', array(':id' => $get_filtered['cc_id'])));
            if ( ! $this->request->is_ajax()) {
                $this->redirect = TRUE;
            } else {
                $this->response = array (
                    'status' => 'error',
                );
            }
            return;
        }

        if ($model_charts->remove_chart_category($get_filtered['cc_id'], $chart_category['cc_order']) !== FALSE ) {
            Hint::set(Hint::SUCCESS, __('Chart category was removed.'));
            $this->redirect = TRUE;
            return;
        } else {
            Hint::set(Hint::ERROR, __('Chart category hasn\'t benn removed.'));
            $this->redirect = TRUE;
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
                'name' => 'chart_load_data',
                'url' => Route::get('admin')->uri(array('controller' => 'charts', 'action' => 'get_charts')),
            ),
        ));
    }
    
    public function action_uncheck_all_charts() {
        $redirect = $this->_ses->get('redirect');
        if ( ! $redirect) {
            $redirect = array('url' => URL::get_referer(), 'controllers' => array($this->request->controller()), 'actions' => array($this->request->action()));
            $this->_ses->set('redirect', $redirect);
        }
        Admin_Functions::delete_field('chk_chart');
        Session::instance()->delete('chk_chart');
        $this->redirect = TRUE;
        return;
    }
    
    public function action_export_to_excel() {
        $model_charts = new Model_Admin_Charts();
        $filter = new Filter($model_charts);
        $filter->set_filter($this->_ses->get('filter', FALSE));
        $model_charts->set_filter_sql_params($filter);
        $model_charts->export_to_excel($filter->get_sql_params());
    }
}
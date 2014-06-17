<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Ajax extends Controller_Admin_Template_Default {
    
    public $response;
    
    public function before() {
        ignore_user_abort(true);
        set_time_limit(0);

        if (!$this->request->is_ajax()) { 
            //die('Not ajax request!');
        }
        parent::before();
    }
    
    public function after() {
        parent::after();
    }
    
    public function action_validate_field() {
        if (HTTP_Request::POST == $this->request->method()) {
            if ( ($url_model = $this->request->param('id', false)) === false) {
                $this->response = array('status' => 'error', 'errors' => 'Brak przypisanego modelu');
                return false;
            }
            $url_submodel = $this->request->param('id2', false);
            
            $options = arr::extract($this->request->post(), array('options'));
            $options = $options['options'];
            $field = array($options['name'] => $options['value']);

            $validation_model = Model::factory('Admin_'.$url_model);

            $rules = $validation_model->get_rule($field, $url_submodel);

            $validated = $this->_add_rules($field, $rules);
            if ($validated->check()) {
                $this->response = array('status' => 'ok');
            } else {
                $errors = $validated->errors('validation2');
                $this->response = array('status' => 'error', 'errors' => $errors);
            }
        }
    }
    
    public function action_validate_fields() {
        if (HTTP_Request::POST == $this->request->method()) {
            
            if ( ($url_model = $this->request->param('id', false)) === false) {
                $this->response = array('status' => 'error', 'errors' => 'Brak przypisanego modelu');
                return false;
            }
            
            $url_submodel = $this->request->param('id2', false);
            $options = arr::extract($this->request->post(), array('options'));
            $options = $options['options'];
            $fields = array();
            foreach($options['fields'] as $field) {
                $fields[$field[0]] = $field[1];
            }

            $current_field = Arr::get($options, 'current_field', FALSE);
            $model_validator = Model::factory('Admin_Validator');
            $this->response = $model_validator->validate($fields, $url_model, $url_submodel, TRUE, $current_field);
            if ( ! empty($this->response['soft_errors'])) {
                Hint::set(Hint::NOTICE, __('Some less important errors appeared. You may omit that information and send again your form.'));
            }
        }
    }
    
    public function action_save_filter() {
        if (HTTP_Request::POST == $this->request->method()) {
            $filter = Filtering::base_filtering($this->request->post());
            Session::instance('database')->set('filter', serialize($filter));
            $field = array();
            $field['exclude_delete_from'] = array('*');
            $field['field'] = 'filter';
            Admin_Functions::add_field_to_globals($field);
            $this->response = array('status' => 'ok');
        }
    }
    
    public function action_toogle_checkboxes() {
        if (HTTP_Request::POST != $this->request->method()) {
            $this->response = array('status' => 'error', 'errors' => 'Błąd przy wywołaniu');
            return;
        }
        $post = Arr::extract($this->request->post(), array('checkboxes'));
        $post_filtered = Filtering::base_filtering($post);
        if (empty($post_filtered['checkboxes'])) {
            $this->response = array('status' => 'error', 'errors' => 'Brak parametrów do przetworzenia');
            return;
        }

        foreach($post_filtered['checkboxes'] as $checkbox) {
            $response = Admin_Functions::toogle_checkbox($checkbox);
        }

        $this->response = array ('status' => 'ok', 'chk_selected_total' => $response);
    }
    
    public function action_toogle_checkbox() {
        $post = Arr::extract($this->request->post(), array('name', 'value', 'rel'));
        $response = Admin_Functions::toogle_checkbox($post);
        if ($response === FALSE) {
            $this->response = array('status' => 'error', 'errors' => 'Błąd przy zaznaczaniu / odznaczaniu');
            return;
        }
        $this->response = array ('status' => 'ok', 'chk_selected_total' => $response); 
    }
    
    public function action_update_timer() {
        $this->auto_render = FALSE;
    }
    
    public function action_get_many_online_status() {
        if (HTTP_Request::POST != $this->request->method()) {
            $this->auto_render = FALSE;
            return FALSE;
        }
        $post = Arr::extract($this->request->post(), array('id'));
        if (empty($post['id'])) return TRUE;
        $model_users = new Model_Admin_Users();
        $statusses = $model_users->get_many_online_status($post['id']);
        if (empty($statusses)) return TRUE;
        $this->response = array('status' => 'ok', 'statusses' => $statusses);
    }

}
?>
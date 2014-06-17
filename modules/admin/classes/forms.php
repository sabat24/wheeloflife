<?php defined('SYSPATH') or die('No direct script access.');
 
class Forms {
    const FIELD_INPUT = 1;
    const FIELD_PASSWORD = 2;
    const FIELD_TEXT = 3;
    const FIELD_HIDDEN = 4;
    const FIELD_SELECT = 5;
    
    private $_model;
    private $_validation_rule;
    private $_ajax_validation;
    private $_ajax;
    
    private $_redirect_url = '';
    private $_submit_name = 'Submit';
    
    private $_fields;
    private $_default_values;
    private $_hidden;
    
    
    
    
    
    private $_post_filtered;
    
    public function __construct($model, $validation_rule, $ajax_validation = TRUE, $ajax = FALSE) {
        $this->_model = $model;
        $this->_validation_rule = $validation_rule;
        $this->_ajax_validation = $ajax_validation;
        $this->_ajax = $ajax;
    }
    
    private function _add_field($name, $value, $type, $hidden, $attributes, $options = NULL) {
        if (isset($this->_fields[$name])) {
            throw new Kohana_Exception('Forms: field with name ['.$name.'] already exists in collection');
        }
        $this->_fields[$name] = array (
            'type' => $type,
            'value' => $value,
            'attributes' => $attributes,
            'required' => FALSE,
        );
        
        if ( ! is_null($options)) {
            $this->_fields[$name]['options'] = $options;
        }
        
        $this->_default_values[$name] = $value;
        $this->_hidden[$name] = $hidden;
        return $this;
    }
    
    public function add_input($name, $value = '', $hidden = 0, $attributes = array()) {
        $this->_add_field($name, $value, self::FIELD_INPUT, $hidden, $attributes);
        return $this;
    }
    
    public function add_password() {
        $this   ->_add_field('password', '', self::FIELD_PASSWORD, 0, array())
                ->_add_field('password_retype', '', self::FIELD_PASSWORD, 0, array('rel' => 'password'));
        return $this;
    }
    
    public function add_hidden($name, $value) {
        $this->_add_field($name, $value, self::FIELD_HIDDEN, 0, array('type' => 'hidden'));
        return $this;
    }
    
    public function add_select($name, $options, $selected = NULL, $hidden = 0, $attributes = array()) {
        $this->_add_field($name, $selected, self::FIELD_SELECT, $hidden, $attributes, $options);
        return $this;
    }
    
    
    private function _get_fields_name() {
        return array_keys($this->_fields);
    }
    
    private function _get_hidden() {
        return $this->_hidden;
    }
    
    private function _get_default_values() {
        return $this->_default_values;
    }
    
    private function _set_post_filtered($post) {
        $this->_post_filtered = $post;
    }
    
    public function get_post_filtered() {
        return $this->_post_filtered;
    }
    
    public function set_redirect($redirect_url) {
        $this->_redirect_url = $redirect_url;
        return $this;
    }
    
    public function set_submit_name($name) {
        $this->_submit_name = $name;
        return $this;
    }
    
    public function set_default_values_from_db($arr, $db_fields) {
          
        $fields = $this->_get_fields_name();
        foreach ($fields as $field) {
            if (isset($db_fields[$field]) && isset($arr[$db_fields[$field]])) {
                $this->_default_values[$field] = $arr[$db_fields[$field]];
                $this->_fields[$field]['value'] = $arr[$db_fields[$field]];
            }
        }
    }
    
    // after validation processed
    public function get_modified_fields($include_fields = FALSE) {
        $post_filtered = $this->get_post_filtered();
        $modified_fields = array_diff($post_filtered, $this->_get_default_values());
        if ( ! empty($modified_fields) && $include_fields !== FALSE) {
            $modified_fields[$include_fields] = Arr::get($modified_fields, $include_fields, $post_filtered[$include_fields]);
        }
        return $modified_fields;
    }
    
    private function _prepare_fields() {
        list($model_name, $submodel_name) = explode('/', $this->_validation_rule);
        $rules = $this->_model->get_rules($this->_get_default_values(), $submodel_name);
        $labels = $this->_model->get_labels($submodel_name);
        
        $fields_name = $this->_get_fields_name();
        foreach ($fields_name as $field_name) {
            if (isset($rules[$field_name])) {
                $field_rules = $rules[$field_name];
                foreach ($field_rules as $rule) {
                    switch ($rule[0]) {
                        case 'not_empty':
                            $this->_fields[$field_name]['required'] = TRUE;
                        break;
                        case 'max_length':
                            $this->_fields[$field_name]['max_length'] = $rule[1][1];
                        break;
                    }
                }
            }
            
            if (isset($labels[$field_name])) {
                $this->_fields[$field_name]['label'] = $labels[$field_name];
            }
            
            if (isset($this->_hidden[$field_name])) {
                $this->_fields[$field_name]['hidden'] = $this->_hidden[$field_name];
            }
        }
    }
    

    public function generate($name, $template = 'default', $action = '') {
        $this->_prepare_fields();
        
        
        
        $options = array (
            'name' => $name,
            'validation_rule' => $this->_validation_rule,
            'ajax_validation' => $this->_ajax_validation,
            'ajax' => $this->_ajax,
            'fields' => $this->_fields,
            'submit_name' => $this->_submit_name,
            'redirect_url' => $this->_redirect_url,
            'action' => $action,
        );
        
        $html = View::factory('blocks/forms/admin/'.$template, $options);
        return $html;
    }
    
    public function validate($post, $submodel) {
        $post = Arr::extract($post, array_keys($this->_get_hidden(), 0));
        $post_filtered = $this->_model->filter($post);
        foreach ($post_filtered as $field_name => $value) {
            $this->_fields[$field_name]['value'] = $value;
        }

        $response = $this->_model->validate($post_filtered, $submodel);
        $post_filtered = array_merge($post_filtered, Arr::extract($this->_get_default_values(), array_keys(array_diff_key($this->_get_default_values(), $post_filtered))));
        $this->_set_post_filtered($post_filtered);
        if ($response['status'] == 'error') {
            foreach ($response['errors'] as $field_name => $error) {
                if (isset($this->_fields[$field_name])) {
                    $this->_fields[$field_name]['error'] = $error;
                }
            }
        }
        return $response;
    }
    
    
    
}
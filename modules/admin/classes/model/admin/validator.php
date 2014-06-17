<?php
defined('SYSPATH') or die('No direct script access.');

class Model_Admin_Validator extends Model {
    
    public function validate($fields, $model_name, $action_name, $admin_mode = TRUE, $current_field = FALSE) {
        if ($admin_mode === TRUE) {
            $validation_model = Model::factory('Admin_'.$model_name);
        } else {
            if (is_object($model_name)) {
                $validation_model = $model_name;
            } else {
                $validation_model = Model::factory($model_name);
            }
        }
        
        $rules = $validation_model->get_rules($fields, $action_name);
        
        $labels = $validation_model->get_labels($action_name);
        $optional_fields = $validation_model->get_optional_fields($action_name);
        if (method_exists($validation_model, 'get_special_error_messages')) {
            $special_error_messages = $validation_model->get_special_error_messages($action_name);
        }
        // data should be filtered, instead of situation when they are come from ajax form
        if ($admin_mode === TRUE) {
            $fields = $validation_model->filter($fields);
        }
        
        $validated = $this->_add_rules($fields, $rules, $labels, $optional_fields, $current_field);
            
        if ($validated->check()) {
            $response = array('status' => 'ok');
        } else {
            $errors = $validated->errors();

            $message_errors = $validated->errors('validation2');
            $message_full_errors = $validated->errors('validation');
            $message_soft_errors = array();
            $soft_error_fields = $validation_model->get_soft_error_fields($action_name);
            foreach($errors as $field => $field_errors) {
                if (preg_match('/([a-z_]+)(_[0-9]+$)/i', $field, $r)) {
                    $global_field = $r[1];
                } else {
                    $global_field = $field;
                }
                
                if (isset($special_error_messages[$global_field])) {
                    $message_errors[$field] = $message_full_errors[$field] = $special_error_messages[$global_field]['error'];
                    if ($special_error_messages[$global_field]['show_as_message']) {
                        Hint::set(Hint::ERROR, $message_full_errors[$field]);
                    }
                }
                
                if (isset($soft_error_fields[$global_field]) && in_array($field_errors[0], $soft_error_fields[$global_field])) {
                    $message_soft_errors[$field] = $message_errors[$field];
                    unset($message_full_errors[$field]);
                    unset($message_errors[$field]);
                }
            }
            $response = array('status' => 'error', 'errors' => $message_errors, 'soft_errors' => $message_soft_errors, 'full_errors' => $message_full_errors);
        }
        
        return $response;
    }
    
    private function _add_rules($fields, $rules, $labels = array(), $optional_fields = array(), $current_field = FALSE) {
        $fields_to_validate = array();

        foreach($fields as $key => $val) {
            if (preg_match('/([a-z_]+)(_[0-9]+$)/i', $key, $r)) {
                if ($current_field !== FALSE && $current_field != $key) continue;
                if (isset($rules[$r[1]])) {
                    $fields_to_validate[$key] = $val;
                }
            } else {
                if ($current_field !== FALSE && $current_field != $key && $key != 'password') continue;
                if (isset($rules[$key])) {
                    $fields_to_validate[$key] = $val;
                }
            }
        }
        $validation = Validation::factory($fields_to_validate);
        foreach($fields_to_validate as $key => $val) {
            if (isset($optional_fields[$key]) && empty($val)) {
                
            } else {
                if (preg_match('/([a-z_]+)(_[0-9]+$)/i', $key, $r)) {
                    $validation->rules($key, $rules[$r[1]]);
                    if (isset($labels[$r[1]])) {
                        $validation->label($key, $labels[$r[1]]);
                    }
                } else {
                    $validation->rules($key, $rules[$key]);
                    if (isset($labels[$key])) {
                        $validation->label($key, $labels[$key]);
                    }
                }
            }
        }
        
        return $validation;
    }
}
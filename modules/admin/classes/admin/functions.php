<?php
class Admin_Functions {
    public static function add_field_to_globals($data) {
        $global_fields = Session::instance('database')->get('global_fields', array());
        if (isset($global_fields[$data['field']])) return true;
        $global_fields[$data['field']] = $data;
        Session::instance('database')->set('global_fields', $global_fields);
    }
    
    public static function delete_field($field) {
        $global_fields = Session::instance('database')->get('global_fields', array());
        Session::instance('database')->delete($field);
        unset($global_fields[$field]);
        Session::instance('database')->set('global_fields', $global_fields);
    }
    
    public static function run_task ($action, $params = array()) {
        
        if (!empty($params)) {
            $params_string = implode(',', $params);
            $params_hash = md5($params_string.','.Kohana::$config->load('task.hash_salt'));
            $params_hashed = $params_string.','.$params_hash;
        }
        
        if ( ($host = parse_url(url::site(FALSE, TRUE), PHP_URL_HOST)) === false) {
            $host = url::site(FALSE, TRUE);
        }
        
        
        $socketcon = fsockopen($host, 80, $errno, $errstr, 30);
        if ($socketcon) {   
            $socketdata = "GET ".url::site(FALSE, TRUE)."admin/task/".$action."/".(isset($params_hashed) ? $params_hashed : '')." HTTP 1.1\r\nHost: ".$host."\r\nConnection: Close\r\n\r\n";      
            fwrite($socketcon, $socketdata); 
            fclose($socketcon);
        }
    }
    /*
    name - nazwa checkboxa w formacie chk_invoice_1 (chk_nazwa_id)
    value - 1 jesli zaznaczony, 0 jesli nie
    rel - invoices/* - czyli maska dla ewentualnego kasowania pol
    */
    
    public static function toogle_checkbox($post) {
        if ( ! isset($post['name']) || ! isset($post['value']) || ! isset($post['rel'])) return FALSE;
        if (preg_match('/([a-z_]+)_(\d+)$/', $post['name'], $r)) {
            $field_name = $r[1];
            $id = $r[2];
        }
        $checkboxes_selected = Session::instance()->get($field_name, array());
        
        if ($post['value'] == 1) {
            return self::_add_checkbox($checkboxes_selected, $field_name, $id, $post['rel']);
        } else {
            return self::_remove_checkbox($checkboxes_selected, $field_name, $id);
        }
    }
    
    public static function _add_checkbox($checkboxes_selected, $field_name, $id, $rel) {
        if ( ! in_array($id, $checkboxes_selected)) {
            $checkboxes_selected[] = $id;
            Session::instance()->set($field_name, $checkboxes_selected);
        }
        
        $field = array();
        $field['exclude_delete_from'] = array($rel);
        $field['field'] = $field_name;
        self::add_field_to_globals($field);
        return count($checkboxes_selected);
    }
    
    public static function _remove_checkbox($checkboxes_selected, $field_name, $id) {
        if ( ($key = array_search($id, $checkboxes_selected)) !== FALSE) {
            unset($checkboxes_selected[$key]);
            Session::instance()->set($field_name, $checkboxes_selected);
        }
        return count($checkboxes_selected);
    }
    
    
}


?>
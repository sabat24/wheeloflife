<?php
defined('SYSPATH') or die('No direct script access.');

class Model_Admin_Users extends Model_Database {
    
    public function get_header_list($type = 'users') {
        $arr = array (
            'check-all-users' => array (
                'type' => 'checkbox',
                'title' => '',
                'field' => 'toogle-all-checkboxes',
                'width' => array(20, 20),
            ),
            'id' => array (
                'type' => 'sort_column',
                'title' => __('ID'),
                'sort' => 'inactive',
                'field' => 'id',
                'width' => array(34, 34),
            ),
            'email' => array (
                'type' => 'sort_column',
                'title' => __('E-mail address'),
                'field' => 'email',
                'sort' => 'inactive',
                'width' => array(90),
            ),
            'name' => array (
                'type' => 'sort_column',
                'title' => __('Name'),
                'sort' => 'inactive',
                'field' => 'name',
                'width' => array(90),
            ),
            'date_created' => array (
                'type' => 'column',
                'title' => __('Date created'),
                'field' => 'date_created',
                'width' => array(90),
            ),
            'action' => array (
                'type' => 'column',
                'title' => __('Action'),
                'field' => 'action',
                'width' => array(70,70),
            ),
        );
        
        if ($type == 'admins') {
            unset($arr['check-all-users']);
        }
        return $arr;
    }
    
    public function get_users_list($filter) {
        $sql_params = $filter->get_sql_params();

        $_filter = $filter->get_filter();
        
        $total_users = $this->get_total_users(Arr::get($sql_params, 'where', array()));
        $config = array(
            'total_items'    => $total_users,
            'items_per_page' => 20,
        );
        
        if (isset($_filter['page_offset'])) {
            $config['current_page'] = array('source' => 'query_string', 'key' => 'page', 'page' => intval($_filter['page_offset']));
        }
        
        $pagination = Pagination::factory($config);
        $users = $this->get_users($filter->get_sorting(), $pagination->items_per_page, $pagination->offset, Arr::get($sql_params, 'where', array()));
        $view = View::factory('pages/admin/users/users_list_rows')
            ->set('users', $users);

        return array (
            'view' => $view,
            'pagination' => $pagination,
        );
    }
    
    public function get_admins_list($filter) {
        $sql_params = $filter->get_sql_params();

        $_filter = $filter->get_filter();
        
        $total_users = $this->get_total_admins(Arr::get($sql_params, 'where', array()));
        $config = array(
            'total_items'    => $total_users,
            'items_per_page' => 20,
        );
        
        if (isset($_filter['page_offset'])) {
            $config['current_page'] = array('source' => 'query_string', 'key' => 'page', 'page' => intval($_filter['page_offset']));
        }
        
        $pagination = Pagination::factory($config);
        $users = $this->get_admins($filter->get_sorting(), $pagination->items_per_page, $pagination->offset, Arr::get($sql_params, 'where', array()));
        $view = View::factory('pages/admin/admins/admins_list_rows')
            ->set('users', $users);

        return array (
            'view' => $view,
            'pagination' => $pagination,
        );
    }
    
    public function get_users($sorting, $limit, $offset, $where = array()) {
        $params = array();
        if ( ! empty($sorting)) {
            $sort_fields = array('id' => 'u.u_id', 'email' => 'u.u_email', 'name' => 'u.u_name');
            if (isset($sort_fields[$sorting[0]])) {
                $sort_field = $sort_fields[$sorting[0]];
                $params['order_by'] = array($sort_field, $sorting[1]);
            }
        }

        $params['select'] = 'u.u_id, u.u_email, u.u_name, u.u_date_created';
        $params['from'] = array ('u' => 'users');
        $params['where'] = $where;
        $params['where'][] = array ('u.u_admin', '=', 0);
        $params['where'][] = array ('u.u_deleted', '=', 0);

        if ($limit !== FALSE && $offset !== FALSE) {
            $params['limit'] = array($limit, $offset);
        }

        return Database::get_params($params);
    }
    
    public function get_admins($sorting, $limit, $offset, $where = array()) {
        $params = array();
        if ( ! empty($sorting)) {
            $sort_fields = array('id' => 'u.u_id', 'email' => 'u.u_email', 'name' => 'u.u_name');
            if (isset($sort_fields[$sorting[0]])) {
                $sort_field = $sort_fields[$sorting[0]];
                $params['order_by'] = array($sort_field, $sorting[1]);
            }
        }

        $params['select'] = 'u.u_id, u.u_email, u.u_name, u.u_date_created';
        $params['from'] = array ('u' => 'users');
        $params['where'] = $where;
        $params['where'][] = array ('u.u_admin', '=', 1);
        $params['where'][] = array ('u.u_deleted', '=', 0);

        if ($limit !== FALSE && $offset !== FALSE) {
            $params['limit'] = array($limit, $offset);
        }

        return Database::get_params($params);
    }
    
    public function get_ac_search($field, $term, $ac_search_filter) {
        $params = array(
            'select' => 'u.u_id, u.u_email, u.u_name',
            'from' => array ('u' => 'users'),
            'limit' => array (20, 0),
        );
        
        switch($field) {
            case 'user_name':
                $params['where'] = array (
                    array('u.u_admin', '=', 0),
                    array('u.u_name', 'LIKE', '%'.$term.'%'),
                );
                $params['order_by'] = array('u.u_name');
            break;
            case 'user_email':
                $params['where'] = array (
                    array('u.u_admin', '=', 0),
                    array('u.u_email', 'LIKE', '%'.$term.'%'),
                );
                $params['order_by'] = array('u.u_email');
            break;
        }
        
        if (isset($ac_search_filter['user_admin'])) {
            $params['where'][] = array('u.u_admin', '=', $ac_search_filter['user_admin']);
        }
        
        $params['where'][] = array('u.u_deleted', '=', 0);
        return Database::get_params($params);        
    }
    
    public function get_filter_ajax($post) {
        $default_sorting = array('id', 'asc');
        $list_header = $this->get_header_list();
        
        $filter = new Filter();
        $filter->set_default_sorting($default_sorting);
        $filter->set_filter_ajax($post);
        $filter->set_sorting($list_header);
        $this->set_filter_sql_params($filter);
        
        return $filter;
    }
    
    public function set_filter_sql_params(&$filter) {
        $_filter = $filter->get_filter();

        $params = array();
        if (isset($_filter['letter_filter'])) {
            $params['where'][] = array('u.u_name', 'LIKE', $_filter['letter_filter'].'%');
        }
        
        $filter_search = array_merge_recursive(Arr::get($_filter, 'search', array()), Arr::get($_filter, 'static', array()));

        if ( ! empty($filter_search)) {
            foreach($filter_search as $key => $value) {
                switch($key) {
                    case 'user_email':
                    case 'user_name':
                        $params['where'][] = array('u.u_id', '=', $value['id']);
                    break;
                    case 'date_created_from':
                    if ( ($timestamp = Date::string_date_to_timestamp($value)) === FALSE) {
                        continue;
                    }
                    $params['where'][] = array('u.u_date_created', '>=', date('Y-m-d', $timestamp));
                break;
                case 'date_created_to':
                    if ( ($timestamp = Date::string_date_to_timestamp($value)) === FALSE) {
                        continue;
                    }
                    $params['where'][] = array('u.u_date_created', '<=', date('Y-m-d', $timestamp));
                break;
                }
            }
        }
        
        $filter->set_sql_params($params);
    }
    
    
    public function get_total_users($where = array()) {
        $wherep[] = array ('u.u_admin', '=', 0);
        $where[] = array ('u.u_deleted', '=', 0);
        $params = array (
            'select' => 'COUNT(*) as total_users',
            'from' => array ('u' => 'users'),
            'where' => $where,
        );
        return Database::get_params_value($params, 'total_users');
    }
    
    public function get_total_admins($where = array()) {
        $wherep[] = array ('u.u_admin', '=', 1);
        $where[] = array ('u.u_deleted', '=', 0);
        $params = array (
            'select' => 'COUNT(*) as total_users',
            'from' => array ('u' => 'users'),
            'where' => $where,
        );
        return Database::get_params_value($params, 'total_users');
    }
    
    public function get_user_by_login($login) {
        $login = Database::prepare_data_to_sql($login);
        $params['where'] = array('(u.u_email = '.$login.' OR u.u_phone = '.$login.')');
        $result = $this->get_users_params($params);
        return (count($result) == 0) ? FALSE : $result[0];
    }
    
    public function get_user_by_id($u_id) {
        $where = array (
            array('u.u_id', '=', $u_id),
            array('u.u_deleted', '=', 0),
        );
        $result = $this->get_users(FALSE, FALSE, FALSE, $where);
        return ($result === FALSE) ? FALSE : current($result);
    }
    
    public function get_admin_by_id($u_id) {
        $where = array (
            array('u.u_id', '=', $u_id),
            array('u.u_deleted', '=', 0),
        );
        
        $result = $this->get_admins(FALSE, FALSE, FALSE, $where);
        return ($result === FALSE) ? FALSE : current($result);
    }

    
    public function get_user_by_email($email) {
        $params['where'] = array (
            array('ue.ue_address', '=', $email)
        );
        $result = $this->get_users_params($params);
        return (count($result) == 0) ? FALSE : current($result);
    }
    

    
    
    
    public function get_user_dbfields() {
        return array(
            'user_admin' => 'u_admin',
            'user_email' => 'u_email',
            'password' => 'u_password',
            'user_name' => 'u_name',
            'user_date_created' => 'u_date_created',
            'user_deleted' => 'u_deleted'
        );
    }
    
    public function get_user_dbfields_flipped() {
        return array_flip($this->get_user_dbfields());
    }
    
    public function save_user($arr) {
        $db_fields = $this->get_user_dbfields();

        if ( ! empty($arr['password'])) {
            $bcrypt = new Bcrypt(15);
            $arr['password'] = $bcrypt->hash($arr['password']);
        } else {
            unset($arr['password']);
        }
            
        if (isset($arr['user_id'])) {
            return $this->_update_user($arr, $db_fields, array(array('u_id', '=', $arr['user_id'])));
        } else {
            return $this->_add_user($arr, $db_fields);
        }
    }
    
    private function _update_user($arr, $db_fields, $where) {
        try {
            return Database::update_data('users', $arr, $db_fields, $where);
        } catch (Exception $e) {
            throw new Kohana_Exception($e);
            return FALSE;
        }
    } 
    
    private function _add_user($arr, $db_fields) {
        try {
            return Database::insert_data('users', $arr, $db_fields);
        } catch (Exception $e) {
            throw new Kohana_Exception($e);
            return FALSE;
        }
    }
    
    public function remove_user($u_id) {
        $arr = array (
            'user_id' => $u_id,
            'user_deleted' => 1,
        );
        return $this->save_user($arr);
    }
    
    
    public function send_confirmation_email($data) {
        $hash = md5($data['email'].time().Kohana::$config->load('task.hash_salt'));
                
        $data_to_insert = array (
            'email' => $data['email'],
            'hash' => $hash,
            'u_id' => $data['u_id'],
            'expired_date' => date('Y-m-d H:i:s', time() + 86400),
        );
        Database::insert_data('email_hash', $data_to_insert);
        $params = array( array('{LINK}', '<a href="'.URL::site(Route::get('page')->uri(array('controller' => 'rejestracja', 'action' => 'email', 'id' => $hash)), TRUE).'" title="Weryfikacja adresu">'.URL::site(Route::get('page')->uri(array('controller' => 'rejestracja', 'action' => 'email', 'id' => $hash)), TRUE).'</a>'));
                
        $recipient = array('to_email' => $data['email'], 'u_id' => $data['u_id']);
        if ( ($emails_id = Model::factory('Admin_EmailTemplates')->create_email_from_template('register_email', $recipient, $params)) === FALSE) {
            Hint::set(Hint::ERROR, Messages::message('email_sending_error'));
            return FALSE;
        }
        return TRUE;
    }
    
    public function user_forgot_password($user) {
        $sql = 'DELETE FROM forgot_passwords WHERE u_id = '.$user['u_id'];
        $this->_db->query(Database::DELETE, $sql);
        $token = md5(md5(time().mt_rand()));
        $arr = array('u_id' => $user['u_id'], 'fp_code' => $token, 'fp_created_date' => time());
        $fields_set = Database::prepare_data_to_insert($arr, FALSE);
                
        $sql = 'INSERT INTO forgot_passwords ('.implode(', ', array_keys($fields_set)).') VALUES ('.implode(', ', $fields_set).')';
        $result = $this->_db->query(Database::INSERT, $sql);
        $params = array ( array ('{LINK}', '<a href="'.url::base(TRUE, FALSE).'admin/login/'.$token.'" title="Jednorazowe logowanie">Jednorazowe logowanie</a>'));   
        if ( ($emails_id = $this->create_emails($user, 'forgotten_password', $params)) === FALSE) {
            return FALSE;
        }
        
        //Admin_Functions::run_task('send_emails', $emails_id);
        RunCli::run_task('send_emails');
        return TRUE;     
    }
    
    public function create_account(&$post, $tokens) {
        $post['password'] = $this->generate_password(12);
        if ( ($u_id = $this->save_user($post)) === FALSE) return FALSE;
        $post['u_id'] = $u_id;
        $model_admin_charts = new Model_Admin_Charts();
        $charts_data = array();
        foreach($tokens as $token) {
            if ( ($chart_data = $model_admin_charts->get_chart_by_token($token)) === FALSE) {
                Hint::set(Hint::NOTICE, __('There is no chart with following token: :token'), array(':token' => $token));
                continue;
            }
            $chart = array (
                'chart_id' => $chart_data['c_id'],
                'user_id' =>  $u_id,
            );
        
            if ($model_admin_charts->save_chart($chart) === FALSE) return FALSE;
            $charts_data[] = $chart_data;
        }
        
        if (empty($charts_data)) {
            Hint::set(Hint::NOTICE, __('There are no charts to save.'));
            return FALSE;
        }
        
        $model_mailer = new Model_Mailer();
        $email_data = array (
            'e_from_email' => '',
            'e_from_name' => '',
            'e_to_email' => $post['user_email'],
            'e_to_name' => $post['user_name'],
            'e_subject' => __(Kohana::$config->load('mailer.create_account_subject')),
            'e_message' => View::factory('email/create_account')->set('user_data', $post)->set('charts_data', $charts_data)->render(),
            'e_attachment_path' => '',
            'e_created_date' => time()
        );
        if ($model_mailer->add_emails_to_db(array($email_data)) === FALSE) {
            Hint::set(Hint::NOTICE, __('Your account was created, but e-mail with password wasn\'t sent'));
            return TRUE;
        }
        RunCli::run_task('send_emails');
        Hint::set(Hint::SUCCESS, __('Your account was created and e-mail with your temporary password was send to the: :address<br />You will be redirected within 5 seconds.', array(':address' => HTML::chars($post['user_email']))));
        return TRUE;
    }
    
    public function generate_password ($length = 12) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $password = substr( str_shuffle( $chars ), 0, $length );
        return $password;
    }
    
    public function  create_emails($recipient, $t_type, $params) {
        
        
    }
    
    public function verify_login_token($token) {
        $token = Filtering::sanitize($token);
        $token = Database::prepare_data_to_sql($token);
        $sql = 'SELECT u.u_id FROM forgot_passwords fp, users u WHERE fp.fp_code = '.$token.' AND fp.u_id = u.u_id';
        if ( ($u_id = $this->_db->query(Database::SELECT, $sql, FALSE)->get('u_id', FALSE)) === FALSE) {
            return FALSE;
        }
        $sql = 'DELETE FROM forgot_passwords WHERE u_id = '.$u_id;
        $result = $this->_db->query(Database::DELETE, $sql);
        return $u_id;
    }
    
    
    public function filter($arr) {
        return Filtering::base_filtering($arr);
    }
    
    public function validation_unique_email($value, $u_id = -1, $field_name = '', $fields = array()) {
        $value = Filtering::sanitize($value);
        $params = array (
            'select' => 'COUNT(*) as total',
            'from' => array ('u' => 'users'),
            'where' => array (
                array ('u.u_email', '=', $value),
                array ('u.u_deleted', '=', 0),
            ),
        );
        if ($u_id != -1) {
            $params['where'][] = array ('u.u_id', '<>', $u_id);
        }
        $result = Database::get_params_value($params, 'total');
        return $result == 0 ? TRUE : FALSE;
    }
    

    
    private function _get_rules($submodel) {
        switch ($submodel) {
            case 'create_account':
                $rules = array (
                    'user_name' => array(array('not_empty'), array('max_length', array(':value', 30)) ),
                    'user_email' => array(array('not_empty'), array('email'), array('max_length', array(':value', 50)), array(array($this, 'validation_unique_email'), array(':value', -1) )),
                );
            break;
            default:
                $rules = array (
                    'user_name' => array(array('not_empty'), array('max_length', array(':value', 30)) ),
                    'password' => array(array('not_empty')),
                    'password_retype' => array(array('matches', array(':validation', ':field', 'password'))),
                );
            break;
        }
        return $rules;
    }
    
    public function get_labels($submodel) {
        switch ($submodel) {
            case 'create_account':
                $labels = array (
                    'user_email' => __('E-mail Address'),
                    'user_name' => __('Your Name'),
                );
            break;
            default:
                $labels = array (
                    'user_email' => __('E-mail Address'),
                    'user_name' => __('Name'),
                    'password' => __('Password'),
                    'password_retype' => __('Password retype'),
                );
            break;
        }
        return $labels;
    }
    
    public function get_optional_fields($submodel = '') {
        $optional_fields = array();
        return $optional_fields;
    }
    
    public function get_soft_error_fields($submodel) {
        switch ($submodel) {
            default:
                $soft_error_fields = array (
                    'user_email' => array ('email_domain'),
                );
            break;
        }
        return $soft_error_fields;
    }
    
    public function get_special_error_messages($submodel) {
        switch ($submodel) {
            default:
                return array (
                );
            break;
        }
    }
    
    public function get_rules($fields, $submodel) {
        $model_users = Model::factory('Admin_Users');

        $rules = $this->_get_rules($submodel);

        if ($submodel !== FALSE) {
            switch($submodel) {
                case 'edit_user':
                    if (isset($fields['password']) && empty($fields['password'])) {
                        unset($rules['password']);
                        unset($rules['password_retype']);
                    }
                    if (isset($fields['user_id'])) {
                        $rules['user_email'] = array(array('not_empty'), array('email'), array('email_domain'), array('max_length', array(':value', 50)), array(array($model_users, 'validation_unique_email'), array(':value', $fields['user_id'], ':field', $fields) ));
                    } else {
                        $rules['user_email'] = array(array('not_empty'), array('email'), array('email_domain'), array('max_length', array(':value', 50)), array(array($model_users, 'validation_unique_email') ));
                    }
                break;
                case 'add_user':
                    $rules['user_email'] = array(array('not_empty'), array('email'), array('email_domain'), array('max_length', array(':value', 50)), array(array($model_users, 'validation_unique_email'), array(':value', -1, ':field', $fields) ));
                break;
            }
            
        }
        return $rules;
    }
    
    public function validate($fields, $type) {
        $model_validator = Model::factory('Admin_Validator');
        $response = $model_validator->validate($fields, 'Users', $type);
        return $response;
    }
}
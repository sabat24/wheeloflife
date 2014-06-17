<?php
defined('SYSPATH') or die('No direct script access.');

class Model_Admin_User extends Model_Database implements Acl_Role_Interface {
    
   	public function get_role_id() {
        $role_id = ($user = Session::instance('database')->get('user', 'guest')) ? Arr::get($user, 'role', 'guest') : 'guest';
        return $role_id;
    }
   
    public function get_user_by_id($id){
        $id = (int) $id;
        $params = array (
            'select' => array (
                'u' => array ('u_id', 'u_admin', 'u_email', 'u_name'),
            ),
            'from' => array ('u' => 'users'),
            'where' => array (
                array ('u.u_id', '=', $id),
                array ('u.u_deleted', '=', 0),
            )
        );
         
        $result = Database::get_params($params);
        return empty($result) ? FALSE : current($result);
    }
    
       
    public function create_user_by_id($id){
        if ($id == -1) {
            // example of i18l message
            return array('u_id' => -1, 'u_admin' => 0, 'u_name' => Messages::message('Guest', FALSE, 'auth', ''), 'u_email' => '', 'role' => 'guest');
        } else {
            if ( ($result = $this->get_user_by_id($id)) === FALSE) {
                return $this->create_user_by_id (-1);
            } else {
                switch ($result['u_admin']) {
                    case 0:
                        $result['role'] = 'user';
                    break;
                    case 1:
                        $result['role'] = 'admin';
                    break;
                }
                return $result;
            }
        }
    }
    
    public function check_Cookie() {
        if( ($Cookie = Cookie::get('remember', false)) !== false ) {
            $tmp = explode(':', $Cookie);
            $u_id = intval($tmp[1]);
            $password = $tmp[0];
            
            $db_user = $this->get_user_by_id($u_id);
            if(count($db_user) == 0) return FALSE;
            if($db_user[0]['u_active'] == 0) return FALSE;
            
            $check_pass = md5( $db_user[0]['u_password'] . md5($db_user[0]['u_last_login']) );
        
            if($check_pass == $password){
                $expire = time() + 1728000; // 20 dni
                $Cookie = md5( $db_user[0]['u_password'] . md5($db_user[0]['u_last_login']) ) . ':' . $db_user[0]['u_id'];
                Cookie::set('remember', $Cookie, 1728000); // 20 dni
                return $u_id;
            } else {
                return false;
            }
        }
        return false;
    }
    
    public function login($username, $password) {
        $result = $this->_login($username, $password);
        return $result;
    }
    
    private function _login($username, $password) {
        try {
            $params = array (
                'select' => array (
                    'u' => array ('u_id', 'u_password'),
                ),
                'from' => array ('u' => 'users'),
                'where' => array (
                    array ('u.u_email', '=', $username),
                    array ('u.u_deleted', '=', 0),
                )
            );
        
            $result = Database::get_params($params);
            if (empty($result)) {
                return array (
                    'result' => FALSE,
                    'error' => __('There is no user with that username or password'),
                );
            }
            
            $result = current($result);
            $bcrypt = new Bcrypt(15);
            if ($bcrypt->verify($password, $result['u_password']) === TRUE) {
                return array (
                    'result' => TRUE,
                    'id' => (int) $result['u_id'],
                );
            } else {
                return array (
                    'result' => FALSE,
                    'error' => __('There is no user with that username or password'),
                );
            }
        } catch (Exception $e) {
            throw new Kohana_Exception($e);
        }
    }

    public function validate_login($arr){
        return Validation::factory($arr)
            ->rules('username', array(array('not_empty')))
            ->rule('password', 'not_empty')
            ->labels(array('username' => __('Username'), 'password' => __('Password')));
    }
    
}
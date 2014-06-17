<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Usercp extends Controller_Default {
    
    public function before() {
        parent::before();
        if ( ! $this->a2->allowed('usercp', 'read')) {
            $this->request->redirect(URL::site(Route::get('static')->uri()));
        }
        $this->template->title = __('User Control Panel');
    }
    
    public function action_index() {
        $model_users = new Model_Admin_Users();
        if ( ($user = $model_users->get_user_by_id($this->user['u_id'])) === FALSE) {
            Hint::set(Hint::ERROR, __('There is no user with following ID: :id', array(':id' => $this->user['u_id'])));            
            return;
        }
        $forms = new Forms($model_users, 'users/edit_user', TRUE);
        $forms
            ->add_input('user_email', '', 2)->add_input('user_name')->add_password()
            ->add_hidden('user_id', $this->user['u_id'])
            ->set_submit_name(__('Edit your data'));
        
        $db_fields = $model_users->get_user_dbfields();
        $forms->set_default_values_from_db($user, $db_fields);
        
        if (HTTP_Request::POST == $this->request->method()) {
            $response = $forms->validate($this->request->post(), 'edit_user');
      
            // ignore soft errors, which are used for ajax communication purposes only
            if ($response['status'] == 'ok' || ($response['status'] == 'error' && count($response['errors']) == 0)) {
                $post_filtered = $forms->get_modified_fields('user_id');
                if (empty($post_filtered)) {
                    Hint::set(Hint::NOTICE, __('No changes to save.'));
                    $this->request->redirect(URL::site(Route::get('usercp')->uri()));
                    return;
                }

                if ($model_users->save_user($post_filtered) === FALSE) {
                    Hint::set(Hint::ERROR, __('Data haven\'t been saved.'));
                } else {
                    Hint::set(Hint::SUCCESS, __('User data were saved.'));
                    $this->request->redirect(URL::site(Route::get('usercp')->uri()));
                    return;
                }
                
            } else {
                // errors are inside forms class
            }
        }
        
        $form_html = $forms->generate('edit_user', 'front');
        
        $this->template->scripts = array (
            'assets/modules/admin/js/functions_validation.js',
        );
        $model_admin_charts = new Model_Admin_Charts();
        $chart_data = $model_admin_charts->get_last_users_chart($this->user['u_id'], FALSE);
        $this->content = View::factory('pages/usercp/index')->set('form_html', $form_html)->set('chart_data', $chart_data);
    }
}?>
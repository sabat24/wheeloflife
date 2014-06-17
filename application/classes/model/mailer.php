<?php
defined('SYSPATH') or die('No direct script access.');

class Model_Mailer extends Model_Database {
    public function get_all_emails_to_send() {
        $params = array (
            'select' => '*',
            'from' => 'emails_to_send',
            'limit' => array (100, 0),
        );
        return Database::get_params($params);
    }   
    
    public function delete_sent_emails($e_ids) {
        if (empty($e_ids)) return FALSE;
        
        //$sql = 'INSERT INTO emails_sent (e_id, u_id, e_from_email, e_from_name, e_to_email, e_to_name, e_subject, e_message, e_attachment_path, e_created_date, e_sent_date) SELECT e_id, u_id, e_from_email, e_from_name, e_to_email, e_to_name, e_subject, e_message, e_attachment_path, e_created_date, now() FROM emails_to_send WHERE e_id IN ('.implode(',', $e_ids).')';
        //$this->_db->query(Database::INSERT, $sql);
        
        $sql = 'DELETE FROM emails_to_send WHERE e_id IN ('.implode(',', $e_ids).')';
        DB::query(Database::DELETE, $sql)->execute();

        return TRUE;
    }
    
    public function add_emails_to_db($emails) {
        foreach ($emails as $email) {
            $emails_id[] = Database::insert_data('emails_to_send', $email);
        }
        return $emails_id;
    }
}
<?php
defined('SYSPATH') or die('No direct script access.');

class Model_CronTask extends Kohana_Model {
    public function check_for_emails_to_send() {
        //$microtime_start = microtime(true);
        $model_mailer = new Model_Mailer();
        $emails = $model_mailer->get_all_emails_to_send();
        
        $sent = array();
        foreach ($emails as $email) {
            //if (microtime(true) - $microtime_start >= 25) break;
            if (Mailer::factory('Mails')->send_email($email)->send()) {
                $sent[] = $email['e_id'];
            }
        }
        $model_mailer->delete_sent_emails($sent);
    }
}
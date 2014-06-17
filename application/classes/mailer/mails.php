<?php
class Mailer_Mails extends Mailer {
    // Who is sending the mail
    //protected $from         = "users@domain.com";
    // Content type of the email
    //protected $content_type = 'text/html';

    public function send_email($email) {
        $this->from_name = Kohana::$config->load('mailer.from_name');
        $this->from_email = Kohana::$config->load('mailer.from_email');
        // jesli w configu ustawimy jakis adres e-mail, to cala poczta bedzie kierowana na niego
        if ( ($to_email = Kohana::$config->load('mailer.to_email')) !== FALSE) {
            $this->to_name = $email['e_to_name'].' - '.$email['e_to_email'];
            $this->to_email = $to_email;
        } else {
            $this->to_name = $email['e_to_name'];
            $this->to_email = $email['e_to_email'];
        }
        $this->subject = $email['e_subject'];
        $this->body    = $email['e_message'];
        
        if ( ! empty($email['e_from_name'])) {
            //$this->reply_to_name = $email['e_from_name'];
        }
        if ( ! empty($email['e_from_email'])) {
            //$this->reply_to_email = $email['e_from_email'];
        }
        if ( ! empty($email['e_attachment_path'])) {
            $this->attachment_path = array($email['e_attachment_path'], $email['e_subject'].'_'.$email['e_id'].Files::get_file_extension($email['e_attachment_path']));
        }

        return $this;
    }
}
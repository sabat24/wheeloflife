<?php defined('SYSPATH') or die('No direct script access.');

abstract class Mailer {
        protected $from_name;
        protected $from_email;
        protected $to_name;
        protected $to_email;
        protected $cc;
        protected $bcc;
        protected $subject;
        protected $body;
        protected $reply_to_email;
        protected $reply_to_name;
        protected $sent_on;
        protected $content_type = 'text/html';
        protected $headers;
        protected $attachment_path;

        protected $template;
        
        
        public static function factory($name) {       
            $class = 'Mailer_'.$name;
            return new $class();
        }

        public function __construct() {
            return $this;
        }

        public function send($save = FALSE) {
            static $email = null;
            if ( ! $email) {
                require Kohana::find_file('vendor/Swift-4.2.0', 'lib/swift_required','php');
            }
            $email = Email::factory()
                ->subject($this->subject)
                ->to($this->to_email, $this->to_name)
                ->from($this->from_email, $this->from_name);
            if (isset($this->reply_to_email)) {
                if (isset($this->reply_to_name)) {
                    $email->reply_to($this->reply_to_email, $this->reply_to_name);
                } else {
                    $email->reply_to($this->reply_to_email);
                }
            }
            $email->message($this->body, $this->content_type);
            if (isset($this->attachment_path)) {
                $email->attach_file($this->attachment_path[0], $this->attachment_path[1]);
            }
            return $email->send();
        }

        protected function save($to, $subject, $body, $headers) {

        }
    }
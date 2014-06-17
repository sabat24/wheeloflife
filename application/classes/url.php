<?php defined('SYSPATH') or die('No direct script access.');
    class URL extends Kohana_URL {
        public static function get_referer(){
            $referer = Request::initial()->referrer();
            if (strpos($referer, URL::base(FALSE,TRUE)) === FALSE) {
                return '';
            } else {
                return $referer;
            }
        }
    }
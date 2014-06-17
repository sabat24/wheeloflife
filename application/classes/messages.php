<?php defined('SYSPATH') OR die('No direct access allowed.');
class Messages {
    public static function message($key, $params = FALSE, $controller = FALSE, $action = FALSE) {
        $directory = Request::current()->directory();
        if (empty($directory)) {
            $directory = 'pages';
        }

        if ($action === FALSE) {
            $n_action = Request::current()->action();
        } elseif (empty($action)) {
            $n_action = 'global';
        } else {
            $n_action = $action;
        }
        
        if ($controller === FALSE) {
            $n_controller = Request::current()->controller();
        } elseif (empty($controller)) {
            $n_controller = 'global';
        } else {
            $n_controller = $controller;
        }
        
        
        
        
        $text = Kohana::message($directory.'/'.$n_controller, $n_action.'.'.$key);
        $text = ($params === FALSE) ? __($text) : __($text, $params);
        return $text;
    }
}
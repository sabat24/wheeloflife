<?php defined('SYSPATH') or die('No direct script access.');

Route::set('admin/auth', 'admin/<action>(/<id>)', array('action' => 'log(in|out)|forgot_password|login2'))
    ->defaults(array(
        'directory'  => 'admin',
        'controller' => 'auth',
        'action' => 'login',
));

Route::set('admin', 'admin(/<controller>(/<action>(/<id>(/<id2>(/<id3>)))))', array('id' => '[^/.;?\n]++'))
    ->defaults(array(
        'directory'  => 'admin',
        'controller' => 'dashboard',
        'action'     => 'index',
));
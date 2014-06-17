<?php defined('SYSPATH') or die('No direct script access.');

return array(
    'native' => array(
        'name' => 'ses_name',
        'lifetime' => 43200,
    ),
    'Cookie' => array(
        'name' => 'cook_name',
        'encrypted' => TRUE,
        'lifetime' => 43200,
    ),
    'database' => array(
        'name' => 'wol',
        'encrypted' => TRUE,
        'lifetime' => 900,
        'group' => 'default',
        'table' => 'sessions',
        'columns' => array(
            'session_id'  => 'session_id',
            'user_id'     => 'user_id',
            'last_active' => 'last_active',
            'contents'    => 'contents'
        ),
        'gc' => 500,
    ),
);
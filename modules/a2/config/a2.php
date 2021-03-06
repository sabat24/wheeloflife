<?php

return array(

	/**
	 * Throws an exception when authorization fails.
	 */
	'exception' => FALSE,

	/**
	 * Exception class to throw when authorization fails (eg 'HTTP_Exception_401')
	 */
	'exception_type' => 'a2_exception',

	/*
	 * The ACL Roles (String IDs are fine, use of ACL_Role_Interface objects also possible)
	 * Use: ROLE => PARENT(S) (make sure parent is defined as role itself before you use it as a parent)
	 */
	'roles' => array
	(
		// ADD YOUR OWN ROLES HERE
		'guest' => NULL,
        'user'	=>	'guest',
		'admin' =>  'user',
	),

	/*
	 * The name of the guest role 
	 * Used when no user is logged in.
	 */
	'guest_role' => 'guest',

	/*
	 * The ACL Resources (String IDs are fine, use of ACL_Resource_Interface objects also possible)
	 * Use: ROLE => PARENT (make sure parent is defined as resource itself before you use it as a parent)
	 */
	'resources' => array
	(
		// ADD YOUR OWN RESOURCES HERE
		'users'	=>	NULL,
		'dashboard' => NULL,
		'static' => NULL, // public 
		'usercp' => 'static', // user admin panel
	),

	/*
	 * The ACL Rules (Again, string IDs are fine, use of ACL_Role/Resource_Interface objects also possible)
	 * Split in allow rules and deny rules, one sub-array per rule:
	     array( ROLES, RESOURCES, PRIVILEGES, ASSERTION)
	 *
	 * Assertions are defined as follows :
			array(CLASS_NAME,$argument) // (only assertion objects that support (at most) 1 argument are supported
			                            //  if you need to give your assertion object several arguments, use an array)
	 */
	'rules' => array
	(
		'allow' => array
		(
			/*
			 * ADD YOUR OWN ALLOW RULES HERE 
			 *
			'ruleName1' => array(
				'role'      => 'guest',
				'resource'  => 'blog',
				'privilege' => 'read'
			),
			'ruleName2' => array(
				'role'      => 'admin'
			),
			'ruleName3' => array(
				'role'      => array('user','manager'),
				'resource'  => 'blog',
				'privilege' => array('delete','edit')
			)
			 */
			 'admin' => array (
			     'role'  =>  'admin',
			    
			),
			'static' => array (
                'role'  => 'guest',
                'resource' => 'static',
            ),
            'usercp' => array (
                'role' => 'user',
                'resource' => 'usercp',
            ),
			

			
			 
		),
		'deny' => array
		(
			'usercp' => array (
                'role' => 'guest',
                'resource' => 'usercp',
			),
            // ADD YOUR OWN DENY RULES HERE
			
		)
	)
);
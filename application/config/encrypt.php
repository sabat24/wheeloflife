<?php defined('SYSPATH') or die('No direct script access.');

return array(

	'default' => array(
		/**
		 * The following options must be set:
		 *
		 * string   key     secret passphrase
		 * integer  mode    encryption mode, one of MCRYPT_MODE_*
		 * integer  cipher  encryption cipher, one of the Mcrpyt cipher constants
		 */
		'key' => 'jfw89ejtgfgfnlfvjn9e8rgfji',
        'cipher' => MCRYPT_RIJNDAEL_128,
		'mode'   => MCRYPT_MODE_NOFB,
	),
	
	'blowfish' => array(
        'key'    => 'gdfgergerg5eyruyh4yjm09fj0s9fmn09sfjsd09fsnd0f9s',
        'cipher' => MCRYPT_BLOWFISH,
        'mode'   => MCRYPT_MODE_ECB,
    ),

);

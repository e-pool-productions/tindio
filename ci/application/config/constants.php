<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('URL', 'http://localhost/tindio/ci/');
define('IMG', URL.'img/');
define('CSS', URL.'css/');
define('JS', URL.'js/');
define('MEDIA', URL.'media/');

define('STATUS_UNASSIGNED'		, 0);
define('STATUS_PRE_PRODUCTION'	, 1);
define('STATUS_IN_PROGRESS'		, 2);
define('STATUS_FOR_APPROVAL'	, 3);
define('STATUS_FINISHED'		, 4);

define('LOGTYPE_NEW_PROJECT'	, 0);
define('LOGTYPE_NEW_USER'		, 1);
define('LOGTYPE_DELETE_PROJECT'	, 2);
define('LOGTYPE_DELETE_USER'	, 3);
define('LOGTYPE_FINISH_PROJECT' , 4);

define('VIDEO',		0);
define('AUDIO',		1);
define('3D_MODEL',	2);
define('IMAGE', 	3);
define('LINK',		4);
define('OTHER',		5);

define('FORMATS',	'JPEG, PNG, GIF, PDF, BMP, OBJ, MP4, OGG, OGV, MP3, WEBM');
/* End of file constants.php */
/* Location: ./application/config/constants.php */
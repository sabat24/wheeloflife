<?php defined('SYSPATH') or die('No direct script access.'); ?>

2014-06-01 15:58:56 --- ERROR: ErrorException [ 4 ]: parse error ~ APPPATH\views\pages\chart\chart_change.php [ 30 ]
2014-06-01 15:58:56 --- STRACE: ErrorException [ 4 ]: parse error ~ APPPATH\views\pages\chart\chart_change.php [ 30 ]
--
#0 [internal function]: Kohana_Core::shutdown_handler()
#1 {main}
2014-06-01 17:40:37 --- ERROR: Kohana_Exception [ 0 ]: Database_Exception [ 1066 ]: Not unique table/alias: 'u' [ SELECT c.c_id, c.u_id, c.c_date_created, COALESCE(u.u_name, "Anonymous") as u_name, GROUP_CONCAT(e.cc_id SEPARATOR ",") as cc_id, GROUP_CONCAT(e.e_value SEPARATOR ",") as e_value FROM charts c LEFT JOIN users u ON (u.u_id = c.u_id) LEFT JOIN evaluations_data e ON (e.c_id = c.c_id) JOIN users u ON (u.u_id = c.u_id) WHERE c.c_deleted = 0 AND u.u_id = '6' GROUP BY c.c_id ORDER BY c.u_id ASC, c.c_id ASC ] ~ MODPATH\database\classes\kohana\database\mysql.php [ 194 ] ~ APPPATH\classes\database.php [ 158 ]
2014-06-01 17:40:37 --- STRACE: Kohana_Exception [ 0 ]: Database_Exception [ 1066 ]: Not unique table/alias: 'u' [ SELECT c.c_id, c.u_id, c.c_date_created, COALESCE(u.u_name, "Anonymous") as u_name, GROUP_CONCAT(e.cc_id SEPARATOR ",") as cc_id, GROUP_CONCAT(e.e_value SEPARATOR ",") as e_value FROM charts c LEFT JOIN users u ON (u.u_id = c.u_id) LEFT JOIN evaluations_data e ON (e.c_id = c.c_id) JOIN users u ON (u.u_id = c.u_id) WHERE c.c_deleted = 0 AND u.u_id = '6' GROUP BY c.c_id ORDER BY c.u_id ASC, c.c_id ASC ] ~ MODPATH\database\classes\kohana\database\mysql.php [ 194 ] ~ APPPATH\classes\database.php [ 158 ]
--
#0 D:\wamp\www\wheel\application\classes\model\admin\charts.php(513): Database::get_params(Array)
#1 D:\wamp\www\wheel\application\classes\controller\admin\charts.php(375): Model_Admin_Charts->export_to_excel(Array)
#2 [internal function]: Controller_Admin_Charts->action_export_to_excel()
#3 D:\wamp\www\wheel\system\classes\kohana\request\client\internal.php(118): ReflectionMethod->invoke(Object(Controller_Admin_Charts))
#4 D:\wamp\www\wheel\system\classes\kohana\request\client.php(64): Kohana_Request_Client_Internal->execute_request(Object(Request))
#5 D:\wamp\www\wheel\system\classes\kohana\request.php(1138): Kohana_Request_Client->execute(Object(Request))
#6 D:\wamp\www\wheel\index.php(119): Kohana_Request->execute()
#7 {main}
2014-06-01 20:17:03 --- ERROR: HTTP_Exception_404 [ 404 ]: Unable to find a route to match the URI: favicon.ico ~ SYSPATH\classes\kohana\request.php [ 1126 ]
2014-06-01 20:17:03 --- STRACE: HTTP_Exception_404 [ 404 ]: Unable to find a route to match the URI: favicon.ico ~ SYSPATH\classes\kohana\request.php [ 1126 ]
--
#0 D:\wamp\www\wheel\index.php(118): Kohana_Request->execute()
#1 {main}
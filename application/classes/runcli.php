<?php defined('SYSPATH') OR die('No direct access allowed.');
class RunCli {
    public static function run_task($task_name, $params = FALSE) {
        if (Kohana::$environment == Kohana::DEVELOPMENT) {
            $bgproc = popen('php '.DOCROOT.'index.php crontask '.$task_name.' >> '.DOCROOT.'bg.log', 'w');
        } else {
            $bgproc = popen('/usr/bin/php '.DOCROOT.'index.php crontask '.$task_name.' >> '.DOCROOT.'bg.log', 'w');
        }
        if ($bgproc === FALSE) {
            Kohana::$log->add(Log::ERROR, 'Nie można otworzyć procesu: /usr/bin/php '.DOCROOT.'index.php crontask '.$task_name.' >> /dev/null');
            die();
        } else {
            // send params through stdin pipe to bgrnd process:
            if ($params !== FALSE) {
                $params_to_send = serialize($params);
                fwrite($bgproc, $params_to_send);
            }
            pclose($bgproc);
        }
        
       $string = '/usr/bin/php '.DOCROOT.'index.php crontask '.$task_name.' '.($params !== FALSE ? $params_to_send : '');
       Kohana::$log->add(Log::NOTICE, $string);
    }
}
<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Help task to display general instructons and list all tasks
 *
 * @package    Kohana
 * @category   Helpers
 * @author     Kohana Team
 * @copyright  (c) 2009-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Task_Crontask extends Minion_Task
{
    protected function _execute(array $params) {
        if ( ! isset($params[1])) return FALSE;
        switch($params[1]) {
            // co godzine 55 po kazdej
            case 'send_emails':
                $model_crontask = Model::factory('CronTask');
                $model_crontask->check_for_emails_to_send();
            break;
        }
        
	}
}
<?php defined('SYSPATH') or die('No direct script access.');
 
class Date extends Kohana_Date {

    public static function descriptive_date($time){
        $months = self::get_months_genitive();
         
        if (is_numeric($time))
            $time = (int)$time;
        else
            $time = strtotime($time);
         
        $d = time() - $time;
        $desc = (string)null;
     
        if ($d > 0 and $d <= 60) {
            $desc = 'chwilę temu';
        } elseif ($d > 60 and $d <= 600) {
            $desc = 'kilka minut temu';
        } elseif ($d > 600 and $d <= 3300) {
            $desc = floor($d/60).' minut temu';
        } elseif ($d > 3300 and $d <= 3900) {
            $desc = 'około godziny temu';
        } elseif ($d > 3900 and $d <= 5400) {
            $desc = 'ponad godzinę temu';
        } elseif ($d > 5400 and $d <= 6900) {
            $desc = 'niecałe dwie godziny temu';
        } elseif ($d > 6900 and $d <= 7500) {
            $desc = 'około dwóch godzin temu';
        } elseif ($d > 7500 and $d <= 86400 and (int)date('j') === (int)date('j',$time)) {
            $desc = 'dzisiaj, o '.date('H:i',$time);
        } elseif ($d > 7500 and $d <= 172800 and
        (                                                              
            (int)date('j') === ((int)date('j',$time) + 1) or            // czy sprawdzam mamy już kolejny dzień
            (                                                           // sprawdzam, czy nie mamy już nowego miesiąca
                (int)date('j') === 1 and                                // sprawdzam dzień
                (int)date('n',$time) === (int)date('n',$time)+1         // sprawdzam miesiąc
            )
        )
        ) {
            $desc = 'wczoraj, o '.date('H:i',$time);
        } elseif ($d > 172800 and $d <= 345600) {
            $desc = floor($d/86400).' dni temu';
        } elseif ($d > 345600 and (int)date('Y') === (int)date('Y',$time)) {
            $desc = date('d',$time).' '.$months[(int)date('n',$time)];
        } elseif ($d > 345600 and (int)date('Y') === ((int)date('Y',$time) + 1)) {
            $desc = date('d',$time).' '.$months[((int)date('n',$time)-1)].' '.date('Y',$time);
        }
        return $desc;
    }
    
    // for placeholder
    public static function get_local_date_format() {
        return 'd.m.YYYY';
    }
    
    // for JS and regexp validation
    public static function get_datepicker_date_format() {
        return 'd.mm.yy';
    }
    
    public static function string_date_to_timestamp($date) {
        if ( ($regex = self::get_date_regex()) === FALSE) return FALSE;
        if (preg_match('%'.$regex['rule'].'%', $date , $r)) {
            $timestamp = strtotime($r[$regex['positions']['y']].'-'.$r[$regex['positions']['m']].'-'.$r[$regex['positions']['d']]);
            return $timestamp == 0 ? FALSE : $timestamp;
        } else {
            return FALSE;
        }
    }
    
    public static function get_date_regex() {
        $date_format = self::get_datepicker_date_format();
        if (preg_match('/([\W])/', $date_format , $r)) {
            $regex_rule = array();
            $date_parts = explode($r[1], $date_format);
            // position's to get the Y-m-d format
            $date_positions = array();
            foreach($date_parts as $position => $date_part) {
                switch($date_part) {
                    case 'd':
                        $regex_rule[] = '([0-9]{1,2})';
                        $date_positions['d'] = $position + 1;
                    break;
                    case 'dd':
                        $regex_rule[] = '([0-9]{2})';
                        $date_positions['d'] = $position + 1;
                    break;
                    case 'm':
                        $regex_rule[] = '([0-9]{1,2})';
                        $date_positions['m'] = $position + 1;
                    break;
                    case 'mm':
                        $regex_rule[] = '([0-9]{2})';
                        $date_positions['m'] = $position + 1;
                    break;
                    case 'y':
                        $regex_rule[] = '([0-9]{2})';
                        $date_positions['y'] = $position + 1;
                    break;
                    case 'yy':
                        $regex_rule[] = '([0-9]{4})';
                        $date_positions['y'] = $position + 1;
                    break;
                }
            }
            return array(
                'rule' => implode('\\'.$r[1], $regex_rule),
                'positions' => $date_positions,
            );
	    } else {
	       return FALSE;
	    }
    }
    
    // for display purposes
    public static function local_format_date($date) {
        $time = strtotime($date);
        return date('Y-m-d', $time);
    }
    
    public static function local_format_datetime($date = FALSE) {
        if ($date === FALSE) {
            $time = time();
        } elseif ( ! is_numeric($date)) {
            $time = strtotime($date);
        }
        return date('Y-m-d H:i:s', $time);
    }
}
<?php
defined('SYSPATH') or die('No direct script access.');

class Model_Charts extends Model_Database {

    public function get_empty_chart() {
        static $chart = null;
        static $model_charts = null;
        if ( ! $chart) {
            require_once(Kohana::find_file('vendor', 'highchart/Highchart'));
        }
        
        if ( ! $model_charts) {
            $model_charts = new Model_Admin_Charts();
        }
        
        $chart_categories = array_merge($model_charts->get_chart_categories_sorted_for_select('none'));
        $chart_categories = array_map('HTML::chars', $chart_categories);
        
        $chart = new Highchart(null, null, TRUE);
        $chart->credits->enabled = false;
        $chart->chart = array (
            'renderTo' => 'main_chart',
            'polar' => true,
            'type' => 'line',
            'events' => array (
                'click' => new HighchartJsExpr('function(e) {return chart_click(this, e);}'),
            ),
        );
        $chart->title = array (
            'text' => __('Your Wheel of Life chart'),
            'x' => -80,
        );
        
        $chart->pane = array (
            'size' => '80%',
        );
        
        $chart->xAxis = array (
            'categories' => $chart_categories,
            'tickmarkPlacement' => 'on',
            'lineWidth' => 0,
        );
        
        $chart->yAxis = array (
            'lineWidth' => 0,
	        'min' => 1,
            'max' => 10,
            'tickInterval' => 1,
	    );
	    
	    $chart->tooltip = array (
	    	'shared' => true,
	        'pointFormat' => new HighchartJsExpr('\'<span style="color:{series.color}">{series.name}: <b>{point.y:,.0f}</b><br/>\''),
	    );
	    
	    $chart->legend = array (
	        'align' => 'right',
	        'verticalAlign' => 'top',
	        'y' => 70,
	        'layout' => 'vertical',
	    );
	    
	    $chart->plotOptions = array (
            'series' => array (
                'lineWidth' => 1,
            ),
        );
        
        $chart->series = array (
	        array (
                'type' => 'line',
                'name' => 'Anonymous',
                'data' => array_fill(0, count($chart_categories), null),
                'pointPlacement' => 'on'
            ),
	    );
	    
	    return $chart;
    }
    
    public function get_chart_pagination($u_id, $current_page, $public_only = FALSE) {
        $model_admin_charts = new Model_Admin_Charts();
        if ($public_only === FALSE) {
            $pagination_charts = $model_admin_charts->get_pagination_charts($u_id);
        } else {
            $pagination_charts = $model_admin_charts->get_pagination_charts_public($u_id);
        }
        
        $pagination_arr = new PaginationArr();
        $pagination = $pagination_arr->get_date_pagination($pagination_charts, $current_page, URL::site(Route::get('static')->uri(array('action' => 'chart', 'id' => '{PG}'))), 'main_chart');
        return $pagination['html'];
    }
    
    public function get_chart_pagination_by_tokens($tokens, $current_page = FALSE) {
        $model_admin_charts = new Model_Admin_Charts();
        
        $pagination_charts = $model_admin_charts->get_pagination_charts_by_tokens($tokens);
        
        if (count($tokens) != $pagination_charts) {
            $pagination_charts_tmp = array();
            foreach($pagination_charts as $pagination_chart) {
                $pagination_charts_tmp[] = $pagination_chart[0];
            }
            $empty_tokens = array_diff($tokens, $pagination_charts_tmp);
            if ( ! empty($empty_tokens)) {
                Session::instance('database')->set('chart_token', $pagination_charts_tmp);
            }
        }
          
        $pagination_arr = new PaginationArr();
        $pagination = $pagination_arr->get_date_pagination($pagination_charts, $current_page, URL::site(Route::get('static')->uri(array('action' => 'chart', 'id' => '{PG}'))), 'main_chart');
        return $pagination['html'];
    }
    
    
    
    
    public function submit_chart($post, $user_id = 0) {
        $model_charts = new Model_Admin_Charts();
        $chart_categories = $model_charts->get_chart_categories_sorted();
        if (count($post) != count($chart_categories)) {
            Hint::set(Hint::ERROR, __('The count of chart categories and data sent doesn\'t match'));
            return FALSE;
        }
        $evaluation_data_insert = array();
        foreach($post as $data) {
            if ( ! isset($chart_categories[($data[0] + 1)])) {
                Hint::set(Hint::ERROR, __('There is no such chart category index: :order', array(':order' => $data[0])));
                return FALSE;
            }
            if ( $data[1] > 10 || $data[1] < 1) {
                Hint::set(Hint::ERROR, __('Following data: :data from the :category chart category is out of range', array(':data' => $data[1], ':category' => $chart_categories[($data[0] + 1)]['cc_name'])));
                return FALSE;
            }
            $evaluation_data_insert[] = array($chart_categories[($data[0] + 1)]['cc_id'], $data[1]);
        }
        
        if ( ($token = $this->generate_chart_token()) === FALSE) {
            Hint::set(Hint::ERROR, __('Can\'t generate token for this chart.'));
            return FALSE;
        }
        try {
            DB::query('NULL', 'BEGIN')->execute();
            $chart_data_insert = array (
                'u_id' => $user_id,
                'c_public' => 0,
                'c_hash' => $token,
                'c_date_created' => date('Y-m-d H:m:s'),
                'c_deleted' => 0
            );
            $c_id = Database::insert_data('charts', $chart_data_insert);
            
            foreach ($evaluation_data_insert as $key => $val) {
                $val = array_reverse($val);
                $val[] = $c_id;
                $evaluation_data_insert[$key] = array_reverse($val);
            }
            Database::insert_data_many('evaluations_data', $evaluation_data_insert, array('c_id', 'cc_id', 'e_value'));
            DB::query('NULL', 'COMMIT')->execute();
            $tokens = Session::instance('database')->get('chart_token', array());
            $tokens[] = $token; 
            
            Session::instance('database')->set('chart_token', $tokens);
            Hint::set(Hint::SUCCESS, __('Your chart was saved.'));
            return $token;
             
		} catch (Exception $e) {
            DB::query('NULL', 'ROLLBACK')->execute();
            throw new Kohana_Exception($e);
            return FALSE;
        }
    }
    
    public function generate_chart_token($length = 10) {
        try {
            $i = 0;
            do {
                $token = bin2hex(mcrypt_create_iv($length, MCRYPT_DEV_RANDOM));
                $params = array (
                    'select' => 'c_id',
                    'from' => 'charts',
                    'where' => array (
                        array('c_hash', '=', $token),
                        array('c_deleted', '=', 0),
                    )
                );
                $result = Database::get_params_row($params);
                $i++;
            } while ($result == FALSE && $i <= 10);
            return $result === FALSE ? $token : FALSE;
        } catch (Exception $e) {
            throw new Kohana_Exception($e);
            return FALSE;
        }
    }
    
    public function get_chart_by_token($token) {
        $model_charts = new Model_Admin_Charts();
        if ( ($chart_data = $model_charts->get_chart_by_token($token)) === FALSE) return FALSE;
        if ( ($chart_evaluation = $model_charts->get_chart_evaluation($chart_data['c_id'])) === FALSE) return FALSE;
        
        $series_data = array();
        foreach($chart_evaluation as $item) {
            $series_data[] = $item['e_value'];
        }
        
        $chart = $this->get_empty_chart();
        $chart->series = array (
	        array (
                'type' => 'area',
                'name' => $chart_data['u_name'],
                'data' => $series_data,
                'pointPlacement' => 'on'
            ),
	    );
	    
	    $chart->subtitle = array (
	       'text' => Date::local_format_date($chart_data['c_date_created']),
            'x' => -80,
	    );
	    
	    return array(
            'chart' => $chart,
            'chart_data' => $chart_data,
        );
    }
    
    public function change_chart_visibility($c_id, $c_public) {
        $model_admin_charts = new Model_Admin_Charts();
        $new_c_public = $c_public == 0 ? 1 : 0;
        $arr = array (
            'chart_id' => $c_id,
            'chart_public' => $new_c_public,
        );
        if ($model_admin_charts->save_chart($arr) === FALSE) return FALSE;
        return $new_c_public;
        
        
    }
}
<?php
defined('SYSPATH') or die('No direct script access.');

class Model_Admin_Charts extends Model_Database {
    
    public function get_header_list() {
        return array (
            'check-all-charts' => array (
                'type' => 'checkbox',
                'title' => '',
                'field' => 'toogle-all-checkboxes',
                'width' => array(20, 20),
            ),
            'id' => array (
                'type' => 'sort_column',
                'title' => __('ID'),
                'sort' => 'inactive',
                'field' => 'id',
                'width' => array(34, 34),
            ),
            'user' => array (
                'type' => 'sort_column',
                'title' => __('User'),
                'field' => 'user',
                'sort' => 'inactive',
                'width' => array(120),
            ),
            'public' => array (
                'type' => 'column',
                'title' => __('Is public?'),
                'field' => 'public',
                'width' => array(30),
            ),
            'date_created' => array (
                'type' => 'column',
                'title' => __('Date created'),
                'field' => 'date_created',
                'width' => array(90),
            ),
            'action' => array (
                'type' => 'column',
                'title' => __('Action'),
                'field' => 'action',
                'width' => array(70,70),
            ),
        );
    }
    
    public function get_charts_list($filter) {
        $sql_params = $filter->get_sql_params();

        $_filter = $filter->get_filter();
        
        $total_charts = $this->get_total_charts($sql_params);
        $config = array(
            'total_items'    => $total_charts,
            'items_per_page' => 20,
        );
        
        if (isset($_filter['page_offset'])) {
            $config['current_page'] = array('source' => 'query_string', 'key' => 'page', 'page' => intval($_filter['page_offset']));
        }
        
        $pagination = Pagination::factory($config);
        $charts = $this->get_charts($filter->get_sorting(), $pagination->items_per_page, $pagination->offset, Arr::get($sql_params, 'where', array()));
        $view = View::factory('pages/admin/charts/charts_list_rows')
            ->set('charts', $charts)
            ->set('model_charts', $this)
            ;

        return array (
            'view' => $view,
            'pagination' => $pagination,
        );
    }
    
    public function get_charts($sorting, $limit, $offset, $where = array()) {
        $params = array();
        if ( ! empty($sorting)) {
            $sort_fields = array('id' => 'c.c_id', 'user' => 'u.u_name');
            if (isset($sort_fields[$sorting[0]])) {
                $sort_field = $sort_fields[$sorting[0]];
                $params['order_by'] = array($sort_field, $sorting[1]);
            }
        }

        $params['select'] = 'c.c_id, c.u_id, c.c_public, c.c_hash, c.c_date_created, COALESCE(u.u_name, "'.__('Anonymous').'") as u_name';
        $params['from'] = array ('c' => 'charts');
        $params['join']['users'] = 'LEFT JOIN users u ON (u.u_id = c.u_id AND u.u_deleted = 0)';
        $params['where'] = array_merge_recursive(
            $where, array (
                array ('c.c_deleted', '=', 0),
            )
        );

        if ($limit !== FALSE && $offset !== FALSE) {
            $params['limit'] = array($limit, $offset);
        }

        return Database::get_params($params);
    }
    
    public function get_chart_by_token($token) {
        $params = array (
            'select' => 'c.c_id, c.u_id, c.c_public, c.c_hash, c.c_date_created, COALESCE(u.u_name, "'.__('Anonymous').'") as u_name',
            'from' => array ('c' => 'charts'),
            'join' => array ('LEFT JOIN users u ON (u.u_id = c.u_id)'),
            'where' => array (
                array ('c.c_hash', '=', $token),
                array ('c.c_deleted', '=', 0),
            ),
        );
        return Database::get_params_row($params);
    }
    
    public function get_chart_evaluation($c_id) {
        $params = array (
            'select' => 'e.e_value, cc.cc_order',
            'from' => array('e' => 'evaluations_data'),
            'join' => array('JOIN chart_categories cc ON (cc.cc_id = e.cc_id)'),
            'where' => array (
                array('e.c_id', '=', $c_id),
            ),
        );
        $evaluation =  Database::get_params($params);
        return empty($evaluation) ? FALSE : $evaluation;
    }
    
    public function get_ac_search($field, $term, $ac_search_filter) {
        $params = array(
            'select' => 'u.u_id, u.u_email, u.u_name',
            'from' => array ('u' => 'users'),
            'limit' => array (20, 0),
        );
        
        switch($field) {
            case 'user_name':
                $params['where'] = array (
                    array('u.u_name', 'LIKE', '%'.$term.'%'),
                );
                $params['order_by'] = array('u.u_name');
            break;
            case 'user_email':
                $params['where'] = array (
                    array('u.u_email', 'LIKE', '%'.$term.'%'),
                );
                $params['order_by'] = array('u.u_email');
            break;
        }
        
        $params['where'][] = array('u.u_deleted', '=', 0);
        return Database::get_params($params);        
    }
    
    public function get_filter_ajax($post) {
        $default_sorting = array('id', 'asc');
        $list_header = $this->get_header_list();
        
        $filter = new Filter();
        $filter->set_default_sorting($default_sorting);
        $filter->set_filter_ajax($post);
        $filter->set_sorting($list_header);
        $this->set_filter_sql_params($filter);
        
        return $filter;
    }
    
    public function set_filter_sql_params(&$filter) {
        $_filter = $filter->get_filter();

        $params = array();
        if (isset($_filter['letter_filter'])) {
            $params['join']['user'] = 'JOIN users u ON (u.u_id = c.u_id)';
            $params['where'][] = array('u.u_name', 'LIKE', $_filter['letter_filter'].'%');
        }
        
        $filter_search = array_merge_recursive(Arr::get($_filter, 'search', array()), Arr::get($_filter, 'static', array()));

        if ( ! empty($filter_search)) {
            foreach($filter_search as $key => $value) {
                switch($key) {
                    case 'user_email':
                    case 'user_name':
                        $params['where'][] = array('u.u_id', '=', $value['id']);
                        $params['join']['user'] = 'JOIN users u ON (u.u_id = c.u_id)';
                    break;
                    case 'date_created_from':
                    if ( ($timestamp = Date::string_date_to_timestamp($value)) === FALSE) {
                        continue;
                    }
                    $params['where'][] = array('c.c_date_created', '>=', date('Y-m-d', $timestamp));
                    
                break;
                case 'date_created_to':
                    if ( ($timestamp = Date::string_date_to_timestamp($value)) === FALSE) {
                        continue;
                    }
                    $params['where'][] = array('c.c_date_created', '<=', date('Y-m-d', $timestamp));
                break;
                }
            }
        }
        
        $filter->set_sql_params($params);
    }

    public function get_total_user_charts($u_id) {
        return $this->get_total_charts(array(array('c.u_id', '=', $u_id)));
    }
    
    private function _get_pagination_charts($params) {
        $charts = Database::get_params($params);
        $pagination_charts = array();
        foreach($charts as $key => $chart) {
            $pagination_charts[] = array($chart['c_hash'], Date::local_format_date($chart['c_date_created']));
        }
        return $pagination_charts;
    }
    
    public function get_pagination_charts($u_id) {
        $params = array (
            'select' => 'c_hash, c_date_created',
            'from' => 'charts',
            'where' => array (
                array ('u_id', '=', $u_id),
                array ('c_deleted', '=', 0),
            ),
            'order_by' => array('c_id', 'DESC'),
        );
        return $this->_get_pagination_charts($params);
    }
    
    public function get_pagination_charts_by_tokens($tokens) {
        $params = array (
            'select' => 'c_hash, c_date_created',
            'from' => 'charts',
            'where' => array (
                'c_hash IN ("'.implode('","', $tokens).'")',
                array ('c_deleted', '=', 0),
            ),
            'order_by' => array('c_id', 'DESC'),
        );
        return $this->_get_pagination_charts($params);
    }
    
    public function get_pagination_charts_public($u_id) {
        $params = array (
            'select' => 'c_hash, c_date_created',
            'from' => 'charts',
            'where' => array (
                array ('u_id', '=', $u_id),
                array ('c_public', '=', 1),
                array ('c_deleted', '=', 0),
            ),
            'order_by' => array('c_id', 'DESC'),
        );
        return $this->_get_pagination_charts($params);
    }
    
    public function get_last_users_chart($u_id, $public_only = FALSE) {
        $params = array (
            'select' => 'c.c_id, c.u_id, c.c_public, c.c_hash, c.c_date_created',
            'from' => array ('c' => 'charts'),
            'order_by' => array('c.c_id', 'DESC'),
            'limit' => array (1, 0),
        );
        if ($public_only === FALSE) {
            $params['where'] = array (
                array ('c.u_id', '=', $u_id),
                array ('c.c_deleted', '=', 0),
            );
        } else {
            $params['where'] = array (
                array ('c.u_id', '=', $u_id),
                array ('c.c_public', '=', 1),
                array ('c.c_deleted', '=', 0),
            );
        }
        return Database::get_params_row($params);
    }
    
    public function get_total_charts($params) {
        
        $cur_params = array (
            'select' => 'COUNT(*) as total_charts',
            'from' => array ('c' => 'charts'),
        );
        
        $cur_params = array_merge_recursive($cur_params, $params);
        $cur_params['where'][] = array ('c.c_deleted', '=', 0);

        return Database::get_params_value($cur_params, 'total_charts');
    }
    
    public function get_chart_dbfields() {
        return array(
            'user_id' => 'u_id',
            'chart_public' => 'c_public',
            'chart_deleted' => 'c_deleted'
        );
    }
    
    public function save_chart($arr) {
        $db_fields = $this->get_chart_dbfields();

        if (isset($arr['chart_id'])) {
            return $this->_update_chart($arr, $db_fields, array(array('c_id', '=', $arr['chart_id'])));
        } else {
            return $this->_add_chart($arr, $db_fields);
        }
    }
    
    private function _update_chart($arr, $db_fields, $where) {
        try {
            return Database::update_data('charts', $arr, $db_fields, $where);
        } catch (Exception $e) {
            throw new Kohana_Exception($e);
            return FALSE;
        }
    } 
    
    private function _add_chart($arr, $db_fields) {
        try {
            return Database::insert_data('charts', $arr, $db_fields);
        } catch (Exception $e) {
            throw new Kohana_Exception($e);
            return FALSE;
        }
    }
    
    public function get_chart_categories_sorted() {
        $params = array (
            'select' => 'cc.cc_id, cc.cc_order, cc.cc_name',
            'from' => array ('cc' => 'chart_categories'),
            'where' => array (
                array ('cc.cc_deleted', '=', 0),
            ),
            'order_by' => array('cc.cc_order'),
        );
        return Database::get_params($params, 'cc_order');
    }
    
    public function get_chart_categories_sorted_for_select($mode = 'last') {
        $params = array (
            'select' => 'cc.cc_id, cc.cc_name',
            'from' => array ('cc' => 'chart_categories'),
            'where' => array (
                array ('cc.cc_deleted', '=', 0),
            ),
            'order_by' => array('cc.cc_order'),
        );
        $chart_categories = Database::get_params($params, 'cc_id', 'cc_name');
        switch ($mode) {
            case 'last':
                $chart_categories['-1'] = __('Last');
            break;
            case 'first':
                $chart_categories = array_reverse($chart_categories, TRUE);
                $chart_categories['0'] = __('First');
                $chart_categories = array_reverse($chart_categories, TRUE);
            break;
        }
        return $chart_categories;
    }
    
    public function get_chart_category_by_id($cc_id) {
        try {
            $params = array (
                'select' => 'cc.cc_id, cc.cc_order, cc.cc_name',
                'from' => array ('cc' => 'chart_categories'),
                'where' => array (
                    array ('cc.cc_id', '=', $cc_id),
                    array ('cc.cc_deleted', '=', 0),
                )
            );
            return Database::get_params_row($params);
        } catch (Exception $e) {
            throw new Kohana_Exception($e);
            return FALSE;
        }
    }
    
    public function get_chart_category_dbfields() {
        return array(
            'chart_category_name' => 'cc_name',
            'chart_category_order' => 'cc_order',
            'chart_category_deleted' => 'cc_deleted',
        );
    }
    
    public function save_chart_category($arr) {
        $db_fields = $this->get_chart_category_dbfields();
        if (isset($arr['chart_category_id'])) {
            return $this->_update_chart_category($arr, $db_fields, array(array('cc_id', '=', $arr['chart_category_id'])));
        } else {
            return $this->_add_chart_category($arr, $db_fields);
        }
    }
    
    private function _update_chart_category($arr, $db_fields, $where) {
        if (isset($arr['chart_category_order'])) {
            if ( ($current_chart_category = $this->get_chart_category_by_id($arr['chart_category_id'])) === FALSE) {
                throw new Kohana_Exception('There is no chart category with following ID: '.$arr['chart_category_id']);
                return FALSE;
            } 
            if ($arr['chart_category_order'] == 0) {
                $arr['chart_category_order'] = 1;
            } else {
                if ( ($chart_category = $this->get_chart_category_by_id($arr['chart_category_order'])) === FALSE) {
                    throw new Kohana_Exception('There is no chart category with following ID: '.$arr['chart_category_order']);
                    return FALSE;
                }
                if ($current_chart_category['cc_order'] > $arr['chart_category_order']) {
                    $arr['chart_category_order'] = $chart_category['cc_order'] + 1;
                } else {
                    $arr['chart_category_order'] = $chart_category['cc_order'];
                }
            }
            try {
                if ($current_chart_category['cc_order'] > $arr['chart_category_order']) {
                    $sql = 'UPDATE chart_categories SET cc_order = cc_order + 1 WHERE cc_deleted = 0 AND cc_order >= '.$arr['chart_category_order'].' AND cc_order <= '.$current_chart_category['cc_order'];
                } else {
                    $sql = 'UPDATE chart_categories SET cc_order = cc_order - 1 WHERE cc_deleted = 0 AND cc_order <= '.$arr['chart_category_order'].' AND cc_order >= '.$current_chart_category['cc_order'];
                }

                DB::query(Database::UPDATE, $sql)->execute();
            } catch (Exception $e) {
                throw new Kohana_Exception($e);
                return FALSE;
            }
        }

        return Database::update_data('chart_categories', $arr, $db_fields, $where);
        
    } 
    
    private function _add_chart_category($arr, $db_fields) {
        if ($arr['chart_category_order'] == -1) {
            $chart_categories = $this->get_chart_categories_sorted();
            $last_chart_category = end($chart_categories);
            $new_order = Arr::get($last_chart_category, 'cc_order', 0) + 1;
        } else {
            if ( ($chart_category = $this->get_chart_category_by_id($arr['chart_category_order'])) === FALSE) {
                throw new Kohana_Exception('There is no chart category with following ID: '.$arr['chart_category_order']);
                return FALSE;
            }
            $new_order = $chart_category['cc_order'] + 1;
            try {
                $sql = 'UPDATE chart_categories SET cc_order = cc_order + 1 WHERE cc_deleted = 0 AND cc_order >= '.$new_order;
                DB::query(Database::UPDATE, $sql)->execute();
            } catch (Exception $e) {
                throw new Kohana_Exception($e);
                return FALSE;
            }
        }
        
        $arr['chart_category_order'] = $new_order;
        
        
        return Database::insert_data('chart_categories', $arr, $db_fields);
    }
    
    public function remove_chart_category($cc_id, $cc_order) {
         try {
            $sql = 'UPDATE chart_categories SET cc_order = cc_order - 1 WHERE cc_deleted = 0 AND cc_order > '.$cc_order;
            DB::query(Database::UPDATE, $sql)->execute();
        } catch (Exception $e) {
            throw new Kohana_Exception($e);
            return FALSE;
        }

        $arr = array (
            'chart_category_id' => $cc_id,
            'chart_category_deleted' => 1,
        );
        return $this->save_chart_category($arr);
    }
    
    public function save_chart_categories_order($new_order) {
        $new_order_arr = explode(',', $new_order);
        $new_order_arr = array_unique(Filtering::force_int($new_order_arr));
        
        $chart_categories = $this->get_chart_categories_sorted();
        $check = array_intersect(array_keys($chart_categories), $new_order_arr);
        
        if (count($check) != count($new_order_arr)) {
            throw new Kohana_Exception('Chart categories order can\'t be saved');
            return FALSE;
        }
        
        foreach ($new_order_arr as $order => $cc_id) {
            $items[] = array ($cc_id, ($order + 1));
        }
        
        return Database::multiple_update('chart_categories', array('cc_id', 'cc_order'), $items);
    }
    
    public function export_to_excel($filter_sql_params) {
        $params = array (
            'select' => 'c.c_id, c.u_id, c.c_date_created, COALESCE(u.u_name, "'.__('Anonymous').'") as u_name, GROUP_CONCAT(e.cc_id SEPARATOR ",") as cc_id, GROUP_CONCAT(e.e_value SEPARATOR ",") as e_value',
            'from' => array ('c' => 'charts'),
            'join' => array(
                'user' => 'LEFT JOIN users u ON (u.u_id = c.u_id)',
                'evaluations_data' => 'LEFT JOIN evaluations_data e ON (e.c_id = c.c_id)',
            ),
            'where' => array (
                array('c.c_deleted', '=', 0),
            ),
            'group_by' => 'c.c_id',
            'order_by' => array('c.u_id ASC, c.c_id'),
        );
        

        $params = Arr::merge($params, $filter_sql_params);
        $charts = Database::get_params($params);
        
        $chart_categories = $this->get_chart_categories_sorted_for_select('none');
        

        $rows = array();
        $columns = array('A' => '', 'B' => '', 'C' => '', 'D' => '', 'E' => '', 'F' => '', 'G' => '', 'H' => '', 'I' => '', 'J' => '', 'K' => '', 'L' => '', 'M' => '', 'N' => '', 'O' => '', 'P' => '', 'Q' => '', 'R' => '', 'S' => '', 'T' => '', 'U' => '', 'V' => '', 'W' => '', 'X' => '', 'Y' => '');
        $categories_columns = array_slice($columns, 4, count($chart_categories), TRUE);
        $categories_columns_keys = array_keys($categories_columns);
        // headers
        $tmp_columns = $columns;
        $tmp_columns['A'] = 'Date';
        $tmp_columns['B'] = 'User id';
        $tmp_columns['C'] = 'First name';
        $tmp_columns['D'] = 'Registered';
        $i = 0;
        foreach($chart_categories as $c_name) {
            $tmp_columns[$categories_columns_keys[$i]] = $c_name;
            $i++;
        }
        $rows[] = $tmp_columns;
        
        foreach($charts as $chart) {
            $tmp_columns = $columns;
            $tmp_columns['A'] = date('Y-m-d', strtotime($chart['c_date_created']));
            $tmp_columns['B'] = $chart['u_id'] == 0 ? '' : $chart['u_id'];
            $tmp_columns['C'] = $chart['u_name'];
            $tmp_columns['D'] = $chart['u_id'] == 0 ? 'n' : 'y';
            
            $i = 0;
            $cc_arr = explode(',', $chart['cc_id']);
            $evaluations = explode(',', $chart['e_value']);
            foreach($categories_columns as $column => $val) {
                $tmp_columns[$column] = isset($chart_categories[$cc_arr[$i]]) ? $evaluations[$i] : '-';
                $i++;
            }
            $rows[] = $tmp_columns;
        }
        
        
        require_once(Kohana::find_file('vendor', 'phpexcel/PHPExcel'));
        $obj_PHPExcel = new PHPExcel();
        $obj_PHPExcel->getProperties()->setCreator("Wheel of Life")
							 ->setLastModifiedBy("Wheel of Life")
							 ->setTitle("Charts summary")
							 ->setSubject("Charts summary")
							 ->setDescription("Charts summary")
							 ->setKeywords("charts, summary");
        
        //$column_type = array('A' => PHPExcel_Cell_DataType::TYPE_STRING, 'B' => PHPExcel_Cell_DataType::TYPE_STRING, 'C' => PHPExcel_Cell_DataType::TYPE_STRING, 'D' => PHPExcel_Cell_DataType::TYPE_STRING, 'E' => PHPExcel_Cell_DataType::TYPE_STRING, 'F' => PHPExcel_Cell_DataType::TYPE_STRING, 'G' => PHPExcel_Cell_DataType::TYPE_STRING, 'H' => PHPExcel_Cell_DataType::TYPE_STRING, 'I' => PHPExcel_Cell_DataType::TYPE_STRING, 'J' => PHPExcel_Cell_DataType::TYPE_STRING, 'K' => PHPExcel_Cell_DataType::TYPE_STRING, 'L' => PHPExcel_Cell_DataType::TYPE_STRING, 'M' => PHPExcel_Cell_DataType::TYPE_STRING, 'N' => PHPExcel_Cell_DataType::TYPE_STRING, 'O' => PHPExcel_Cell_DataType::TYPE_STRING, 'P' => PHPExcel_Cell_DataType::TYPE_STRING, 'Q' => PHPExcel_Cell_DataType::TYPE_STRING, 'R' => PHPExcel_Cell_DataType::TYPE_STRING, 'S' => PHPExcel_Cell_DataType::TYPE_STRING, 'T' => PHPExcel_Cell_DataType::TYPE_STRING, 'U' => PHPExcel_Cell_DataType::TYPE_STRING, 'V' => PHPExcel_Cell_DataType::TYPE_STRING, 'W' => PHPExcel_Cell_DataType::TYPE_STRING, 'X' => PHPExcel_Cell_DataType::TYPE_STRING, 'Y' => PHPExcel_Cell_DataType::TYPE_STRING);
        
        foreach($rows as $row_no => $row) {
            foreach($row as $column => $value) {
                if (empty($value)) continue;
                $obj_PHPExcel->getActiveSheet()->setCellValueExplicit($column.($row_no + 1), $value, PHPExcel_Cell_DefaultValueBinder::dataTypeForValue($value));
            }
        }
        
        $obj_PHPExcel->getActiveSheet()->setTitle('summary');
        $obj_PHPExcel->setActiveSheetIndex(0);
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="summary.xls"');
        header('Cache-Control: max-age=0');

        $obj_Writer = PHPExcel_IOFactory::createWriter($obj_PHPExcel, 'Excel5');
        $obj_Writer->save('php://output');
        exit;
        die();
        
    }
    
    
    
    public function get_chart_public_status($status = FALSE) {
        $statuses = array (__('No'), __('Yes'));
        return $status === FALSE ? $statusues : Arr::get($statuses, $status, $statuses);
    }
   
    
    
    public function filter($arr) {
        return Filtering::base_filtering($arr);
    }
    
    
    
    private function _get_rules($submodel) {
        switch ($submodel) {
            case 'chart_add_category':
                $rules = array (
                    'chart_category_name' => array(array('not_empty'), array('max_length', array(':value', 45)) ),
                    'chart_category_order' => array(array('in_array', array(':value', array_keys($this->get_chart_categories_sorted_for_select())))),
                );
            break;
            case 'chart_edit_category':
                $rules = array (
                    'chart_category_name' => array(array('not_empty'), array('max_length', array(':value', 45)) ),
                    'chart_category_order' => array(array('in_array', array(':value', array_keys($this->get_chart_categories_sorted_for_select('first'))))),
                );
            break;
            
        }
        return $rules;
    }
    
    public function get_labels($submodel) {
        switch ($submodel) {
            case 'chart_add_category':
            case 'chart_edit_category':
                $labels = array (
                    'chart_category_name' => __('Chart category name'),
                    'chart_category_order' => __('Chart category order (after)'),
                );
            break;
            default:
                $labels = array (
                    'user_email' => __('E-mail address'),
                    'user_name' => __('User name'),
                );
            break;
        }
        return $labels;
    }
    
    public function get_optional_fields($submodel = '') {
        $optional_fields = array();
        return $optional_fields;
    }
    
    public function get_soft_error_fields($submodel) {
        switch ($submodel) {
            default:
                $soft_error_fields = array (
                  
                );
            break;
        }
        return $soft_error_fields;
    }
    
    public function get_special_error_messages($submodel) {
        switch ($submodel) {
            default:
                return array (
                );
            break;
        }
    }
    
    public function get_rules($fields, $submodel) {
        

        $rules = $this->_get_rules($submodel);
        return $rules;
    }
    
    public function validate($fields, $type) {
        $model_validator = Model::factory('Admin_Validator');
        $response = $model_validator->validate($fields, 'Charts', $type);
        return $response;
    }
}
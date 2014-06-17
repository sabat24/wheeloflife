<?php defined('SYSPATH') or die('No direct script access.');

abstract class Database extends Kohana_Database {
    public static function prepare_data_to_sql($value) {
        
        if (is_array($value) OR is_object($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = Database::prepare_data_to_sql($val);
            }
        } else {
            if (is_null($value)) return $value;
            $value = Database::instance()->quote($value);
        }
        return $value;
    }
    
    public static function prepare_data_to_update($arr, $db_fields = FALSE) {
        $arr = self::prepare_data_to_sql($arr);
        $fields_set = array();
        foreach($arr as $field => $value) {
            if ($db_fields === FALSE) {
                $fields_set[$field] = $field.' = '.$value;
            } else {
                if (isset($db_fields[$field]) && ! is_null($value)) {
                    $fields_set[$field] = $db_fields[$field].' = '.$value;
                }
            }
        }
        return $fields_set;
    }
    
    public static function prepare_data_to_insert($arr, $db_fields = FALSE) {
        $arr = self::prepare_data_to_sql($arr);
        $fields_set = array();
        foreach($arr as $field => $value) {
            if ($db_fields === FALSE) {
                $fields_set[$field] = $value;
            } elseif (isset($db_fields[$field]) && ! is_null($value)) {
                $fields_set[$db_fields[$field]] = $value;
            }
        }
        return $fields_set;
    }
    
    public static function get_sql_from_params ($params) {
        $sql = '';
        if (isset($params['select'])) {
            if (is_array($params['select'])) {
                $select = array();
                foreach ($params['select'] as $alias => $fields) {
                    if (is_array($fields)) {
                        if (is_numeric($alias)) {
                            $select = array_merge($select, $fields);
                        } else {
                            foreach($fields as $field) {
                                $select[] = $alias.'.'.$field;
                            }
                        }
                    } else {
                        if (is_numeric($alias)) {
                            $select[] = $fields;
                        } else {
                            $select[] = $alias.'.'.$fields;
                        }
                    }
                }
                $sql .= 'SELECT '.implode(', ', $select);
            } else {
                $sql.='SELECT '.$params['select'];
            }
        }
        
        if (isset($params['from'])) {
            if (is_array($params['from'])) {
                $func = function($alias, $table) {
                    return $table.' '.$alias;
                };
                $from = array_map($func, array_keys($params['from']), $params['from']);
            } else {
                $from = array($params['from']);
            }
            $sql .= ' FROM '.implode(', ', $from);

        }

        if (isset($params['join'])) {
            if (is_array($params['join'])) {
                $sql.=' '.implode(' ', $params['join']);
            } else {
                $sql.=' '.$params['join'];
            }
        }
        
        if (isset($params['where']) && ! empty($params['where'])) {
            $sql_where = array();
            foreach($params['where'] as $val) {
                if (is_null($val)) {
                    continue;
                } else if (is_array($val)) {
                    $sql_where[] = $val[0].' '.$val[1].' '.self::prepare_data_to_sql($val[2]);
                } else {
                    $sql_where[] = $val;
                }
            }
            $sql.=' WHERE '.implode(' AND ', $sql_where);
        }
        
        if (isset($params['group_by'])) {
            $sql.=' GROUP BY '.$params['group_by'];
        }
        
        if (isset($params['having']) && ! empty($params['having'])) {
            $sql_having = array();
            foreach($params['having'] as $val) {
                if (is_null($val)) {
                    continue;
                } else if (is_array($val)) {
                    $sql_having[] = $val[0].' '.$val[1].' '.self::prepare_data_to_sql($val[2]);
                } else {
                    $sql_having[] = $val;
                }
            }
            $sql.=' HAVING '.implode(' AND ', $sql_having);
        }
        
        
        if (isset($params['order_by']) && ! empty($params['order_by'])) {
            $direction = (isset($params['order_by'][1]) ? $params['order_by'][1] : 'ASC');
            if (is_array($params['order_by'][0])) {
                $order = implode(' '.$direction.', ', $params['order_by'][0]).' '.$direction;
            } else {
                $order = $params['order_by'][0].' '.$direction;
            }
            
            $sql.=' ORDER BY '.$order;
        }
        
        if (isset($params['limit']) && !empty($params['limit'])) {
            $sql.=' LIMIT '.$params['limit'][0].' OFFSET '.$params['limit'][1];
        }

        return $sql;
    }
    
    
    
    public static function get_params($params, $key = FALSE, $value = FALSE) {
        try {
            $sql = self::get_sql_from_params($params);

            $result = DB::query(Database::SELECT, $sql)->execute();
            if ($key === FALSE) {
                return $result->as_array();
            } else {
                return $value == FALSE ? $result->as_array($key) : $result->as_array($key, $value);
            }
        } catch (Exception $e) {
            throw new Kohana_Exception($e);
            return FALSE;
        }
    }
    
    public static function get_params_value($params, $value) {
        if ( ($result = self::get_params_row($params)) === FALSE) return FALSE;
        return isset($result[$value]) ? $result[$value] : FALSE;
        
    }
    
    public static function get_params_row($params) {
        $result = self::get_params($params);
        return empty($result) ? FALSE : current($result);
    }
    
    public static function insert_data($table, $arr, $db_fields = FALSE) {
        try {
            $fields_set = self::prepare_data_to_insert($arr, $db_fields);
            $sql = 'INSERT INTO '.$table.' ('.implode(', ', array_keys($fields_set)).') VALUES ('.implode(', ', $fields_set).')';        
            $result = DB::query(Database::INSERT, $sql)->execute();

            if ($result[1] != 1) return FALSE;
            return $result[0];
        } catch (Exception $e) {
            throw new Kohana_Exception($e);
            return FALSE;
        }
    }
    
    public static function insert_ignore_data($table, $arr, $db_fields = FALSE) {
        try {
            $fields_set = self::prepare_data_to_insert($arr, $db_fields);
            $sql = 'INSERT IGNORE INTO '.$table.' ('.implode(', ', array_keys($fields_set)).') VALUES ('.implode(', ', $fields_set).')';        
            $result = DB::query(Database::INSERT, $sql)->execute();

            if ($result[1] != 1) return FALSE;
            return $result[0];
        } catch (Exception $e) {
            throw new Kohana_Exception($e);
            return FALSE;
        }
    }
    
    public static function insert_data_many($table, $arr, $fields_set, $chunk_limit = 50) {
        $sql = 'INSERT INTO '.$table.' ('.implode(', ', $fields_set).') VALUES ';
        self::_insert_data_many($sql, $arr, $chunk_limit);
    }
    
    public static function insert_ignore_data_many($table, $arr, $fields_set, $chunk_limit = 50) {
        $sql = 'INSERT IGNORE INTO '.$table.' ('.implode(', ', $fields_set).') VALUES ';
        self::_insert_data_many($sql, $arr, $chunk_limit);
    }
    
    private static function _insert_data_many($main_sql, $arr, $chunk_limit) {
        try {
            $arr = self::prepare_data_to_sql($arr);
            foreach(array_chunk($arr, $chunk_limit) as $chunk) {
                foreach ($chunk as $key => $arr) {
                    $chunk[$key] = implode(',', $arr);
                }
                $sql = $main_sql . "(".implode("), (", $chunk).")";
                $result = DB::query(Database::INSERT, $sql)->execute();
            }
        } catch (Exception $e) {
            throw new Kohana_Exception($e);
            return FALSE;
        }
    }
    
    
    
    public static function delete_data_many($table, $arr, $where, $where_param, $chunk_limit = 50) {
        try {
            $main_sql = 'DELETE FROM '.$table;
            $main_sql.=self::get_sql_from_params(array('where' => $where)).' AND '.$where_param.' IN ';    
            foreach(array_chunk($arr, $chunk_limit) as $chunk) {
                $sql = $main_sql . "(".implode(", ", $chunk).")";
                $result = DB::query(Database::DELETE, $sql)->execute();
            }
        } catch (Exception $e) {
            throw new Kohana_Exception($e);
            return FALSE;
        }
    }
    
    
    public static function update_data($table, $arr, $db_fields, $where) {
        try {
            $fields_set = self::prepare_data_to_update($arr, $db_fields);
            $sql = 'UPDATE '.$table.' SET '.implode(', ', $fields_set);
            $sql.=self::get_sql_from_params(array('where' => $where));
        
            return DB::query(Database::UPDATE, $sql)->execute();
        } catch (Exception $e) {
            throw new Kohana_Exception($e);
            return FALSE;
        }
    }
    
   
    public static function multiple_update($table, $keys, $values) {
        try {
            $keys_string = '('.implode(',', $keys).')';
            $keys_update_arr = array();
            foreach ($keys as $key) {
                $keys_update_arr[] = $key .'=VALUES('.$key.')';
            }
            $keys_update_string = implode(',', $keys_update_arr);
            $values = self::prepare_data_to_sql($values);
        
            foreach(array_chunk($values, 100) as $chunk){
                foreach ($chunk as $key => $arr) {
                    $chunk[$key] = implode(',', $arr);
                }
                $sql = 'INSERT INTO '.$table.' '.$keys_string.' VALUES ';
                $sql.= '('.implode('),(', $chunk).') ';
                $sql.= 'ON DUPLICATE KEY UPDATE '.$keys_update_string;
                $result = DB::query(Database::INSERT, $sql)->execute();
            }
            return TRUE;
        } catch (Exception $e) {
            throw new Kohana_Exception($e);
            return FALSE;
        }
    }
}
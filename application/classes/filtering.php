<?php

/**
 * Description of filter
 *
 * @author skowron-line
 * @date 2011-10-05 21:53:39
 */
class Filtering {
    /**
     * 
     * @var array 
     */
    protected $_data = array();
    /**
     *
     * @var array
     */
    protected $_filters = array();
    /**
     *
     * @param array $array
     * @return Filter object
     */
    public static function factory(array $array)
    {
        return new Filtering($array);
    }
    /**
     *
     * @param array $array 
     */
    public function __construct(array $array) 
    {
        $this->_data = $array;
    }
    /**
     *
     * @param string $field
     * @param string $filter
     * @return $this 
     */
    public function filter($field, $filter)
    {
        $this->_filters[$field][] = $filter;
        return $this;
    }
    /**
     *
     * @param string $field
     * @param array $filters
     * @return $this
     */
    public function filters($field, array $filters)
    {
        foreach($filters as $filter)
        {
            $this->filter($field, $filter);
        }
        return $this;
    }
    /**
     *
     * @return array - pair array(field => (array) filters) 
     */
    public function get_filters()
    {
        return $this->_filters;
    }
    /**
     *
     * @return array
     */
    public function get() 
    {
        $filtered = array();
        $i = 0;
        foreach($this->_filters as $field => $filters)
        {
            $i++;

            if($field == '*')
            {
                foreach($filters as $filter)
                {
                    if(function_exists($filter))
                    {
                        $this->_data = array_map($filter, $this->_data);
                        continue;
                    }
                    elseif(stristr($filter, '::'))
                    {
                        list($class, $method) = explode('::', $filter);
                        $this->_data = array_map(array($class, $method), $this->_data);
                        continue;
                    }
                }  
            }
            else
            {
                foreach($filters as $filter)
                {

                    if(function_exists($filter))
                    {
                        $this->_data[$field] = $filter($this->_data[$field]);
                        continue;
                    }
                    
                    elseif(stristr($filter, '::'))
                    {
                        
                        list($class, $method) = explode('::', $filter);
                        // ozs
                        $this->_data[$field] = $class::$method($this->_data[$field]);
                        continue;
                    }
                }
                
            }
        }
        
        return $this->_data;
    }
    
    public static function sanitize($text){
        if (is_array($text)) {
            foreach($text as $key => $sub_text) {
                $text[$key] = self::sanitize($sub_text);
            }
            return $text;
        }
        
        
        $text = trim($text);
        //$text = filter_var($text, FILTER_SANITIZE_SPECIAL_CHARS);
        $text = strip_tags($text);
        return $text;
    }
    
    public static function filename($text) {
        return basename($text);
    }
    
    public static function force_int($value) {
        if (is_array($value)) {
            return array_map('self::force_int', $value);
        } else {
            return intval($value);
        }
    }
    
    public static function currency($value) {
        //list($decimal) = array_values(localeconv());
        //$decimal2 = $decimal == '.' ? ',' : '.';
        
        return number_format(round(str_replace(',', '.', $value), 2), 2, '.', '');
    }
    
    public static function base_filtering($post) {
        return self::factory($post)->filters('*', array('Filtering::sanitize'))->get();
    }
    
}

?>

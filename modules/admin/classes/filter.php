<?php defined('SYSPATH') OR die('No direct access allowed.');
class Filter {
    const FIELD_INPUT = 1;
    const FIELD_DATE = 2;
    
    private $_letter_filter = FALSE;
    private $_filter;
    private $_default_sorting;
    private $_sorting;
    private $_sql_params = array();
    
    private $_fields = array();
    
    private $_model;
    
    public function __construct($model = FALSE) {
        if ($model !== FALSE) {
            $this->set_model($model);
        }
    }
    
    public function set_model($model) {
        $this->_model = $model;
    }
    
    public function set_default_sorting($default_sorting) {
        $this->_default_sorting = $default_sorting;
    }
    
    public function get_default_sorting() {
        return $this->_default_sorting;
    }
    
    private function _set_sorting($sorting) {
        $this->_sorting = $sorting;
    }
    
    public function get_sorting() {
        return $this->_sorting;
    }
    
    public function get_filter() {
        return $this->_filter;
    }
    
    public function set_sql_params($params) {
        $this->_sql_params = array_merge_recursive($this->_sql_params, $params);
    }
    
    public function get_sql_params() {
        return $this->_sql_params;
    }
    
    public function set_letter_filter_state($state) {
        if ($state === TRUE) {
            $this->_letter_filter = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'w', 'z');
        } else {
            $this->_letter_filter = FALSE;
        }
    }
    
    public function set_letter_filter($letter) {
        if ($letter === FALSE) {
            $this->remove_from_filter('letter_filter');
        } else {
            if ($this->_letter_filter === FALSE) {
                throw new Kohana_Exception('Filter: You can\'t set letter when letter filter is turned off');
                return FALSE;
            }
            if (in_array($letter, $this->_letter_filter)) {
                $this->add_to_filter('letter_filter', $letter);
            }
        }
    }
    
    public function remove_from_filter($key) {
        if (isset($this->_filter[$key])) {
            unset($this->_filter[$key]);
        }
    }
    
    public function add_to_filter($key, $value) {
        $this->_filter[$key] = $value;
    }
    
    public function set_filter($filter) {
        if ($filter !== FALSE) {
            $filter = unserialize($filter);
            $sorting = isset($filter['sorting']) ? $filter['sorting'] : $this->get_default_sorting(); 
            Admin_Functions::delete_field('filter');
        } else {
            $sorting = $this->get_default_sorting();
        }
        
        if ( ! isset($filter['search'])) {
            $filter['search'] = array();
        }
        $this->_set_sorting($sorting);
        $this->_filter = $filter;
    }
    
    public function set_filter_ajax($filter) {
        $sorting = isset($filter['sorting']) ? $filter['sorting'] : $this->get_default_sorting(); 
        $this->_set_sorting($sorting);
        $this->_filter = $filter;
    }
    
    public function set_sorting(&$list_header) {
        list($key, $value) = $this->get_sorting();
        if (isset($list_header[$key])) {
            $list_header[$key]['sort'] = $value;
            $this->add_to_filter('sorting', array ($key, $value));
        }
    }
    
    public function render_to_js() {
        Javascript::array_to_js($this->get_filter(), 'filter', array());
    }
    
    // FIELDS
    
    private function _add_field($name, $value, $type, $attributes) {
        if (isset($this->_fields[$name])) {
            throw new Kohana_Exception('Filter: field with name ['.$name.'] already exists in collection');
        }
        
        $this->_fields[$name] = array (
            'name' => $name,
            'type' => $type,
            'value' => $value,
            'attributes' => $attributes,
        );
        return $this;
    }
    
    public function add_input($name, $value = '', $attributes = array()) {
        $this->_add_field($name, $value, self::FIELD_INPUT, $attributes);
        return $this;
    }
    
    public function add_label($label) {
        $last_field = end($this->_fields);
        $this->_fields[$last_field['name']]['label'] = $label;
        return $this; 
    }
    
    public function add_date($name, $label, $value = array('', ''), $attributes = array()) {
        $_name_from = $name.'_from';
        $_name_to = $name.'_to';
        $_attributes = array (
            'class' => array_merge (
                array ('inline_block', 'date_input', 'static-filter', 'datepicker'), 
                Arr::get($attributes, 'class', array())
            ),
            'id' => Arr::get($attributes, 'id', 'filter_'.$_name_from),
            'rel' => Arr::get($attributes, 'rel', 'filter_'.$_name_to),
            'placeholder' => Arr::get($attributes, 'placeholder', Date::get_local_date_format()),
        );

        $this->_add_field($_name_from, $value[0], self::FIELD_DATE, $_attributes)->add_label($label);
        
        $_attributes = array (
            'class' => array_merge_recursive (
                array ('inline_block', 'date_input', 'static-filter', 'datepicker'), 
                Arr::get($attributes, 'class', array())
            ),
            'id' => Arr::get($attributes, 'id', 'filter_'.$_name_to),
            'rel' => Arr::get($attributes, 'rel', 'filter_'.$_name_from),
            'placeholder' => Arr::get($attributes, 'placeholder', Date::get_local_date_format()),
        );

        $this->_add_field($_name_to, $value[1], self::FIELD_DATE, $_attributes);
        return $this;
    }
    
    private function _prepare_fields($submodel) {
        $labels = $this->_model->get_labels($submodel);
        $_filter = $this->get_filter();

        foreach ($this->_fields as $field_name => $field) {
            $this->_fields[$field_name]['label'] = (isset($labels[$field_name])) ? $labels[$field_name] : Arr::get($this->_fields[$field_name], 'label', $field_name);
            $this->_fields[$field_name]['value'] = Arr::path($_filter, 'search.'.$field_name.'.value', '');
            
            $attributes = array (
                'class' => array('ac_search'),
                'autocomplete' => 'off',
            );
            
            $this->_fields[$field_name]['attributes'] = array_merge_recursive($this->_fields[$field_name]['attributes'], $attributes);
        }
    }
    
    public function generate($controller, $related, $submodel = '') {
        $this->_prepare_fields($submodel);
        
        $options = array (
            'fields' => $this->_fields,
            'controller' => $controller,
            'related' => $related.'-holder',
            'letter_filter' => $this->_letter_filter,
            '_filter' => $this->get_filter(),
        );
        
        $html = View::factory('blocks/filter/admin/default', $options);
        return $html;
    }
    
    
}
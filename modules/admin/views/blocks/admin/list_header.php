<?php

$max_total_width = 1060;
$number_of_elements = $total_width = 0;
$set_width_keys = $empty_width_keys = array();

foreach ($list_header as $key => $th) {
    if (isset($th['width'])) {
        $total_width+=$th['width'][0];
        $set_width_keys[] = $key;
    } else {
        $empty_width_keys[] = $key;
    }
    $number_of_elements++;
}



if (count($empty_width_keys) > 0) {
    if (count($set_width_keys) == 0) {
        $column_default_width = array(80);
    } else {
        // pobieramy srednia wartosc szerokosci dla podanych kolumn
        $column_default_width = intval($total_width / count($set_width_keys));
    }
    foreach($empty_width_keys as $key) {
        $list_header[$key]['width'] = array($column_default_width);
        $total_width+=$column_default_width;
    }
}

$ratio = $max_total_width / $total_width;
$count = 0;
$current_max_total_width = $max_total_width;
$last_element = $number_of_elements;
while ($ratio != 1) {
    $count++;
    $current_last_element = $i = $left = $reached_max_width = $total_width = 0;
    $max_total_width = $current_max_total_width;
    foreach($list_header as $key => $th) {
        $i++;
        //if ($i == $last_element) {
            //if (isset($th['width'][1])) {
                //$list_header[$key]['width'] = array($max_total_width - $left - $total_width, $th['width'][1]);
            //} else {
                //$list_header[$key]['width'] = array($max_total_width - $left - $total_width);
            //}
        //} else {
        if ($i == $last_element) {
            $new_width = $max_total_width - $left - $total_width;
        } else {
            $new_width = intval($ratio * $th['width'][0]);
        }
            if (isset($th['width'][1]) && $th['width'][0] == $th['width'][1]) {
                //$reached_max_width+=$th['width'][1];
                //$total_width+=$th['width'][1];
                $max_total_width-=$th['width'][1];
                continue;
            } elseif (isset($th['width'][1]) && $th['width'][1] < $new_width) {
                $list_header[$key]['width'][0] = $th['width'][1];
                //$max_total_width-=$th['width'][1];
                $left+=($new_width - $th['width'][1]);
                $reached_max_width+=$th['width'][1];
            } else {
                $list_header[$key]['width'][0] = $new_width;
                $current_last_element = $i;
            }
        //}
        $total_width+=$list_header[$key]['width'][0];
    }
    if ($current_last_element > $last_element) {
        $last_element = $current_last_element;
    }
    
    $max_total_width-=$left;
    $total_width-=$reached_max_width;

    $ratio = 1 + $left / $total_width;
   
}


foreach ($list_header as $th) {
    switch ($th['type']) {
        case 'checkbox':
            echo '<th width="'.$th['width'][0].'" scope="col"><input type="checkbox" class="'.$th['field'].'" name="'.$th['field'].'" /></th>';
        break;
        case 'sort_column':
            $class = 'sort';
            switch($th['sort']) {
                case 'inactive':
                case 'desc':
                case 'asc':
                    $class .= ' '.$th['sort'];
                break;
            }
            echo '<th id="'.$th['field'].'" width="'.$th['width'][0].'" scope="col"'.( ! empty($class) ? ' class="'.$class.'"' : '').'><span>'.$th['title'].'</span></th>';
        break;
        case 'column':
            echo '<th width="'.$th['width'][0].'" scope="col">'.$th['title'].'</th>';
        break;
    }
}
?>
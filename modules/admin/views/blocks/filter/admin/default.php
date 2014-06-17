<?php

echo Form::open(URL::site(Route::get('admin')->uri(array('controller' => $controller, 'action' => 'ac_search'))), array('rel' => $related));
?>
<div class="filter_content">
<?php

$total = count($fields);
$fields = array_values($fields);
for ($i = 0; $i < $total; $i++) {
    $field = $fields[$i];
    
    $field_name = $field['name'];
    $attributes = $field['attributes'];
    if (isset($attributes['class'])) {
        $attributes['class'] = implode(' ', $attributes['class']);
    }
     
?>
    <div class="floatLeft">
        <label><?php echo $field['label'];?></label>
            <?php
            switch($field['type']) {
                case Filter::FIELD_INPUT:
                    echo Form::input($field_name, $field['value'], $attributes);
                break;
                case Filter::FIELD_DATE:
                    echo Form::input($field_name, $field['value'], $attributes);
                    ?>
                    <img rel="<?php echo $attributes['id'];?>" class="calendar_icon" src="<?=URL::base(TRUE, FALSE);?>assets/modules/filter/images/calendar.gif" alt="<?php echo __('Select date');?>" title="<?php echo __('Choose date from calendar');?>" />
                    <?php
                    
                    $field = $fields[($i+1)];
                    $i++;
                    $field_name = $field['name'];
                    $attributes = $field['attributes'];
                    if (isset($attributes['class'])) {
                        $attributes['class'] = implode(' ', $attributes['class']);
                    }
                    echo Form::input($field_name, $field['value'], $attributes);
                    ?>
                    <img rel="<?php echo $attributes['id'];?>" class="calendar_icon" src="<?=URL::base(TRUE, FALSE);?>assets/modules/filter/images/calendar.gif" alt="<?php echo __('Select date');?>" title="<?php echo __('Choose date from calendar');?>" />
                    <?php
                break;
            }
            ?>
    </div>
<?php } ?>
    <div class="clear"></div>
    <div class="footer">
        <a href="?clear_filter" class="run-on-click" rel="clear_filter" title="<?php echo __('Clear search results');?>"><?php echo __('Clear search results');?></a>
    </div>
</div>
<?php echo Form::close(); ?>

<?php if ($letter_filter !== FALSE) { ?>
<div class="letter_filter pagination">
<?php foreach($letter_filter as $letter) { ?>
    <?php if (isset($_filter['letter_filter']) && $_filter['letter_filter'] == $letter) { ?>
        <a class="current" rel="<?php echo $letter;?>" title="<?php echo mb_strtoupper($letter);?>" href="<?php echo URL::site(Route::get('admin')->uri(array('controller' => $controller, 'action' => 'index', 'id' => 'letter', 'id2' => $letter)));?>"><?=mb_strtoupper($letter)?></a>
    <?php } else { ?>
        <a rel="<?php echo $letter;?>" title="<?php echo mb_strtoupper($letter);?>" href="<?php echo URL::site(Route::get('admin')->uri(array('controller' => $controller, 'action' => 'index', 'id' => 'letter', 'id2' => $letter)));?>"><?=mb_strtoupper($letter)?></a>
    <?php } ?>
<?php } ?>
</div>
<?php } ?>
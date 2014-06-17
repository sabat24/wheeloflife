<?php
$attributes = array (
    'name' => $name,
    'rel' => $validation_rule,
);
if ($ajax_validation === TRUE) {
    $attributes['class'][] = 'validate';
}

if ($ajax === TRUE) {
    $attributes['class'][] = 'ajax';
}

if ( isset($attributes['class'])) {
    $attributes['class'] = implode(' ', $attributes['class']);
}


echo Form::open($action, $attributes);

?>
<fieldset class="front_form">
    <table class="front_form">
        <tfoot>
            <tr>
                <td>
                    <a class="submit btn btn-primary" href="" title="<?php echo $submit_name;?>"><?php echo $submit_name;?></a>
                    <?php if ( ! empty($redirect_url)) { ?>
                    <a href="<?php echo $redirect_url;?>" title="<?php echo __('Go back');?>" class="btn btn-primary"><?php echo __('Go back');?></a>
                    <?php } ?>
                </td>
            </tr>
        </tfoot>
        <tbody>

<?php

foreach ($fields as $field_name => $field) {
    if ($field['hidden'] == 1) continue;
    
    $attributes = $field['attributes'];
    if ($ajax_validation === TRUE) {
        $attributes['class'][] = 'validate';
    }
    
    if ($field['type'] == Forms::FIELD_HIDDEN) {
        if ( isset($attributes['class'])) {
            $attributes['class'] = implode(' ', $attributes['class']);
        }
        echo Form::input($field_name, $field['value'], $attributes);
        continue;
    }
    
    $parent_classes = array($field_name);
    if (isset($field['error'])) {
        $parent_classes[] = 'error';
    }
?>
    <tr class="<?php echo implode(' ', $parent_classes);?>">
        <td><div class="label"><?php echo Arr::get($field, 'label', $field_name).($field['required'] === TRUE ? '<span class="required">*</span>' : '');?>:</div>
            <div class="information">
                <div class="inner"><?php echo Arr::get($field, 'error', '');?></div>
            </div>
        
        <div class="input">
        <?php
        
        

        if ($field['hidden'] == 2) {
            $attributes['disabled'] = 'disabled';
        }
        
        if ( isset($attributes['class'])) {
            $attributes['class'] = implode(' ', $attributes['class']);
        }
                
        switch($field['type']) {
            case Forms::FIELD_INPUT:
                if (isset($field['max_length'])) {
                    $attributes['maxlength'] = $field['max_length'];
                }
                
                echo Form::input($field_name, $field['value'], $attributes);
            break;
            case Forms::FIELD_PASSWORD:
                if (isset($field['max_length'])) {
                    $attributes['maxlength'] = $field['max_length'];
                }

                $attributes['type'] = 'password';
                echo Form::input($field_name, $field['value'], $attributes);
            break;
            case Forms::FIELD_SELECT:
                echo Form::select($field_name, $field['options'], $field['value'], $attributes);
            break;
        }
        ?>
        </div>
        </td>
        
    </tr>
<?php
}
?>
        </tbody>
    </table>
</fieldset><?php
echo Form::close();

?>   
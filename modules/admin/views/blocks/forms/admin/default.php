<?php
$attributes = array (
    'name' => $name,
    'rel' => $validation_rule,
);
if ($ajax_validation === TRUE) {
    $attributes['class'] = 'validate';
}
echo Form::open(NULL, $attributes);

?>
<fieldset class="default_form">
    <table class="default_form">
        <tfoot>
            <tr>
                <td colspan="2">
                    <input type="submit" value="<?php echo $submit_name;?>" class="submit" />
                    <?php if ( ! empty($redirect_url)) { ?>
                    <a href="<?php echo $redirect_url;?>" title="<?php echo __('Go back');?>" class="submit"><?php echo __('Go back');?></a>
                    <?php } ?>
                </td>
                <td></td>
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
        <td class="label"><?php echo Arr::get($field, 'label', $field_name).($field['required'] === TRUE ? '<span class="required">*</span>' : '');?>:</td>
        <td class="input">
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
        </td>
        <td class="information">
            <div class="left"></div>
            <div class="inner"><?php echo Arr::get($field, 'error', '');?></div>
            <div class="right"></div>
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
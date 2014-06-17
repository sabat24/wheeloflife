<div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all"<?php if ( ! empty($callback_url)) {?> data-loadcallback="load_<?php echo str_replace('-', '_', $id);?>" data-callbackurl="<?php echo $callback_url;?>"<?php } ?>>
    <div class="portlet-header fixed ui-widget-header ui-corner-top">
        <?php if ($minmax === TRUE) { ?>
        <span class="ui-icon minmax ui-icon-triangle-1-n"></span>
        <?php }?>
        <span class="list_content <?=$icon?>_icon"><?=$title?></span> <span class="chk_selected_total"><?php if ( ! empty($chk_selected_total)) { echo '('.$chk_selected_total.')';}?></span>
    </div>
            
    <div class="portlet-content nopadding" style="display: block;">
        <div id="<?=$id?>-holder">
        <?php if ( ! empty($view)) {
            if ($view instanceof View === TRUE) {
                echo $view;
            } else {
                echo View::factory($view);
            }
        }
        ?>
        </div>
	</div>
</div>
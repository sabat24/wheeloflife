<?php if (count($chart_categories) > 0) { ?>
<form id="save-order" class="default_form ajax" method="POST" action="<?php echo URL::site(Route::get('admin')->uri(array('controller' => 'charts', 'action' => 'chart_save_categories_order')));?>">
    <ul id="sortable" style="margin-top: 10px;">
    <?php foreach($chart_categories as $order => $chart_category) { ?>
        <li id="<?=$chart_category['cc_id']?>" class="ui-state-default"><span style="float:left; width: 700px;"><?php echo __('Order');?>: <?php echo $order.' - '.$chart_category['cc_name'];?></span><span style="float:right;"><a href="<?php echo URL::site(Route::get('admin')->uri(array('controller' => 'charts', 'action' => 'chart_remove_category', 'id' => $chart_category['cc_id'])));?>"  class="table-item-action confirm-delete no-ajax" rel="chart_category"><?php echo __('Remove');?></a> | <a href="<?php echo URL::site(Route::get('admin')->uri(array('controller' => 'charts', 'action' => 'chart_edit_category', 'id' => $chart_category['cc_id'])));?>" class="table-item-action"><?php echo __('Edit');?></a></span><div style="clear:both;"></div></li>
    <?php } ?>
    </ul>
    <input type="hidden" name="new_order" id="new-order" value="" />
    <div class="submit_footer">
    <input type="submit" value="<?php echo __('Save order');?>" class="submit" />
    </div>
</form>
<?php } ?>
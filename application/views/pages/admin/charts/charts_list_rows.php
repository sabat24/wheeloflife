<?php
foreach($charts as $key => $chart) {
$checkboxes_selected = Session::instance()->get('chk_chart', array());
        
?>
<tr>
    <td><label><input type="checkbox" name="chk_chart_<?php echo $chart['c_id'];?>" rel="charts/*" class="toogle-checkbox"<?php if (in_array($chart['c_id'], $checkboxes_selected)) { echo ' checked="checked"';}?> /></label></td>
    <td><?php echo $chart['c_id'];?></td>
    <td><?php echo HTML::chars($chart['u_name']);?></td>
    <td><?php echo $model_charts->get_chart_public_status($chart['c_public']); ?></td>
    <td><?php echo Date::local_format_date($chart['c_date_created']);?></td>
    <td class="actions action-list-menu-init">
        <ul>
            <li class="action-list-menu-releaser">
                <div class="action-list-menu hidden">
                    <div class="action-list-menu-releaser"></div>
                    <ul>
                        <?php if ($chart['c_public'] == 1) { ?>
                        <li><a class="menu_show save-filter" href="<?php echo URL::site(Route::get('static')->uri(array('action' => 'chart', 'id' => $chart['c_hash'])));?>" title="<?php echo __('View chart');?>"><?php echo __('View chart');?></a></li>
                        <?php } ?>
                        <li><a class="menu_delete confirm-delete" rel="chart" href="<?php echo URL::site(Route::get('admin')->uri(array('controller' => 'charts', 'action' => 'chart_remove', 'id' => $chart['c_id'])))?>" title="<?php echo __('Remove chart');?>"><?php echo __('Remove chart');?></a></li>
                    </ul>
                </div>
            </li>
        </ul>
    </td>
</tr>
<?php } ?>
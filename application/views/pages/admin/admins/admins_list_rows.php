<?php
foreach($users as $key => $user) {

        
?>
<tr>
    <td><?php echo $user['u_id'];?></td>
    <td><?php echo HTML::chars($user['u_email']);?></td>
    <td><?php echo HTML::chars($user['u_name']);?></td>
    <td><?php echo Date::local_format_date($user['u_date_created']);?></td>
    <td class="actions action-list-menu-init">
        <ul>
            <li class="action-list-menu-releaser">
                <div class="action-list-menu hidden">
                    <div class="action-list-menu-releaser"></div>
                    <ul>
                        <li><a class="menu_edit save-filter" href="<?php echo URL::site(Route::get('admin')->uri(array('controller' => 'admins', 'action' => 'admin_edit', 'id' => $user['u_id'])));?>" title="<?php echo __('Edit admin');?>"><?php echo __('Edit admin');?></a></li>
                        <li><a class="menu_delete confirm-delete" rel="user" href="<?php echo URL::site(Route::get('admin')->uri(array('controller' => 'admins', 'action' => 'admin_remove', 'id' => $user['u_id'])))?>" title="<?php echo __('Remove admin');?>"><?php echo __('Remove admin');?></a></li>
                    </ul>
                </div>
            </li>
        </ul>
    </td>
</tr>
<?php } ?>
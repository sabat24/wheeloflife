<?php
foreach($users as $key => $user) {
$checkboxes_selected = Session::instance()->get('chk_user', array());
        
?>
<tr>
    <td><label><input type="checkbox" name="chk_user_<?php echo $user['u_id'];?>" rel="users/*" class="toogle-checkbox"<?php if (in_array($user['u_id'], $checkboxes_selected)) { echo ' checked="checked"';}?> /></label></td>
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
                        <li><a class="menu_edit save-filter" href="<?php echo URL::site(Route::get('admin')->uri(array('controller' => 'users', 'action' => 'user_edit', 'id' => $user['u_id'])));?>" title="<?php echo __('Edit user');?>"><?php echo __('Edit user');?></a></li>
                        <li><a class="menu_delete confirm-delete" rel="user" href="<?php echo URL::site(Route::get('admin')->uri(array('controller' => 'users', 'action' => 'user_remove', 'id' => $user['u_id'])))?>" title="<?php echo __('Remove user');?>"><?php echo __('Remove user');?></a></li>
                    </ul>
                </div>
            </li>
        </ul>
    </td>
</tr>
<?php } ?>
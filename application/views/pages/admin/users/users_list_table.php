<table class="default-table" cellspacing="0" cellpadding="0">
    <thead>
        <tr class="sort">
            <?= View::factory('blocks/admin/list_header', array('list_header' => $list_header))?>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <td colspan="<?php echo count($list_header);?>">
                <a title="<?php echo __('Add new user');?>" class="add_new_user_inline save-filter" href="<?php echo URL::site(Route::get('admin')->uri(array('controller' => 'users', 'action' => 'add_user')));?>"><?php echo __('Add new user');?></a>
            </td>
        </tr>
    </tfoot>
    <tbody class="list-container">
    <?php
    if ( ! empty($view)) {
        echo $view;
    }
    ?>
    </tbody>
</table>

<div class="pagination-container" rel="list_content"><?php echo $pagination;?></div>
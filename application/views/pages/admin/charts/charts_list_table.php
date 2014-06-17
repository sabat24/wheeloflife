<table class="default-table" cellspacing="0" cellpadding="0">
    <thead>
        <tr class="sort">
            <?= View::factory('blocks/admin/list_header', array('list_header' => $list_header))?>
        </tr>
    </thead>
    <tbody class="list-container">
    <?php
    if ( ! empty($view)) {
        echo $view;
    }
    ?>
    </tbody>
    <tfoot>
                            <tr>
                            <td colspan="<?=count($list_header)?>">
                            <a title="<?php echo __('Uncheck all charts');?>" class="save-filter clear_selection_inline" href="<?= URL::site(Route::get('admin')->uri(array('controller' => 'charts', 'action' => 'uncheck_all_charts'))) ?>"><?php echo __('Uncheck all charts');?></a>
                              | <a title="<?php echo __('Export all to Excel');?>" class="save-filter export_excel_inline" href="<?= URL::site(Route::get('admin')->uri(array('controller' => 'charts', 'action' => 'export_to_excel'))) ?>"><?php echo __('Export all to Excel');?></a>
                             </td>
                             </tr>
                        </tfoot>
</table>

<div class="pagination-container" rel="list_content"><?php echo $pagination;?></div>
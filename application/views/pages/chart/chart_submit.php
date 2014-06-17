<div class="row">
        <div class="col-lg-8">
          <h2>Your Wheel of Life</h2>
          <div id="info">
            <?= View::factory('blocks/admin/hint')?>
          </div>
          <div id="main_chart"></div>
          <div class="pagination-container">
          <div id="pagination" class="pagination">
          
          <?php if ( ! empty($pagination)) { ?>
          <p><?php echo __('Your charts');?></p>
          <?php echo $pagination;?>
          <?php } ?>
            
          </div>
          </div>
          
          <p id="chart_panel">
          <?php if ($chart_data['_can_submit'] === TRUE) {?>
          <a class="btn btn-default" style="display: inline-block;" href="<?php echo URL::site(Route::get('static')->uri()) ;?>" title="<?php echo __('Create new chart');?>"><?php echo __('Create new chart');?></a>
          <?php } ?>
          <a class="<?php if ($chart_data['c_public'] == 1) { echo 'hidden ';}?>make-public btn btn-default" href="#public" title="<?php echo __('Make chart public');?>"><?php echo __('Make chart public');?></a>
          <a class="<?php if ($chart_data['c_public'] == 0) { echo 'hidden ';}?>make-private btn btn-default" href="#private" title="<?php echo __('Make chart private');?>"><?php echo __('Make chart private');?></a>
          </p>
        </div>
        <div class="col-lg-4" id="right-side">
          <?php echo View::factory('pages/chart/chart_submit_form')->set('form_html', $form_html);?>
          <div class="share_chart <?php if ($chart_data['c_public'] == 0) { echo 'hidden ';}?>">
          <p>Share your chart with others:</p>
          <input type="text" name="chart_url" class="form-control" readonly="readonly" value="<?php echo URL::site(Route::get('static')->uri(array('action' => 'chart', 'id' => $chart_data['c_hash'])), TRUE); ?>" />
          </div>
       </div>
</div>
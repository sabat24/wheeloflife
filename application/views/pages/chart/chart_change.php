<div class="row">
        <div class="col-lg-8">
          <h2>Your Wheel of Life</h2>
          <div id="info">
            <?= View::factory('blocks/admin/hint')?>
          </div>
          <div id="main_chart" class="chart_container"></div>
          <div class="pagination-container">
          <div id="pagination" class="pagination">
          <p><?php echo __('Your charts');?></p>
          <?php echo $pagination;?>
          </div>
          </div>
          <p id="chart_panel">
          <?php if ($chart_data['_can_submit'] === TRUE) {?>
          <a class="btn btn-default" style="display: inline-block;" href="<?php echo URL::site(Route::get('static')->uri()) ;?>" title="<?php echo __('Create new chart');?>"><?php echo __('Create new chart');?></a>
          <a rel="main_chart" class="btn btn-default chart-submit" style="display: inline-block;" href="#submit" title="<?php echo __('Submit this chart as a new one');?>"><?php echo __('Submit this chart as a new one');?></a>
          <?php } ?>
          <a class="<?php if ($chart_data['c_public'] == 1) { echo 'hidden ';}?>make-public btn btn-default" href="#public" title="<?php echo __('Make chart public');?>"><?php echo __('Make chart public');?></a>
          <a class="<?php if ($chart_data['c_public'] == 0) { echo 'hidden ';}?>make-private btn btn-default" href="#private" title="<?php echo __('Make chart private');?>"><?php echo __('Make chart private');?></a>
          </p>
        </div>
        <div class="col-lg-4" id="right-side">
          <div class="index">
          <h2>Instructions</h2>
          <p>You can change each value on the chart and submit it as a new one. Your old chart won't be modified.</p>
          <p>You can also change visibility your old chart by making the chart public or private.</p>
          </div>
          <div class="share_chart <?php if ($chart_data['c_public'] == 0) { echo 'hidden ';}?>">
          <p>Share your chart with others:</p>
          <input type="text" name="chart_url" class="form-control" readonly="readonly" value="<?php echo URL::site(Route::get('static')->uri(array('action' => 'chart', 'id' => $chart_data['c_hash'])), TRUE); ?>" />
          </div>
       </div>
</div>
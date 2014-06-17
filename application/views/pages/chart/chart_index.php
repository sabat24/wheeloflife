<div class="row">
        <div class="col-lg-8">
          <h2>Your Wheel of Life</h2>
          <div id="info">
            <?= View::factory('blocks/admin/hint')?>
          </div>
          <div id="main_chart" class="chart_container"></div>
          <div class="pagination-container">
          <div id="pagination" class="pagination">
          
          <?php if ( ! empty($pagination)) { ?>
          <p><?php echo __('Your charts');?></p>
          <?php echo $pagination;?>
          <?php } ?>
            
          </div>
          </div>
          <p id="chart_panel">
          <a id="clear" rel="main_chart" class="btn btn-default clear-chart" href="#clear" title="<?php echo __('Clear chart');?>"><?php echo __('Clear chart');?></a>
          <a id="create-new" class="btn btn-default hidden" href="<?php echo URL::site(Route::get('static')->uri()) ;?>" title="<?php echo __('Create new chart');?>"><?php echo __('Create new chart');?></a>
          <a rel="main_chart" class="btn btn-default chart-submit" href="#submit" title="<?php echo __('Submit chart');?>"><?php echo __('Submit chart');?></a>
          <a class="hidden make-public btn btn-default" href="#public" title="<?php echo __('Make chart public');?>"><?php echo __('Make chart public');?></a>
          <a class="hidden make-private btn btn-default" href="#private" title="<?php echo __('Make chart private');?>"><?php echo __('Make chart private');?></a>
          </p>
        </div>
        <div class="col-lg-4" id="right-side">
          <div class="index">
          <h2>Instructions</h2>
          <p>Click on chart...</p>
          <?php echo View::factory('pages/chart/user_last_chart')->set('chart_data', $chart_data);?>
          
          </div>
          <div class="form">
            <?php if ($form_html !== FALSE) {echo View::factory('pages/chart/chart_submit_form')->set('form_html', $form_html);}?>
          </div>
          <div class="share_chart hidden">
          <p>Share your chart with others:</p>
          <input type="text" name="chart_url" class="form-control" readonly="readonly" value="" />
          </div>
       </div>
</div>
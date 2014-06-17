<?php if ($chart_data !== FALSE) { ?>
<p></p>
<p>You can see your last chart by clicking the button below</p>
<a class="btn btn-default" href="<?php echo URL::site(Route::get('static')->uri(array('action' => 'chart', 'id' => $chart_data['c_hash'])));?>" title="<?php echo __('Go to your last chart');?>"><?php echo __('Go to your last chart');?></a>
<?php } ?>
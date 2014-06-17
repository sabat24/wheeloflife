<div class="row">
        <div class="col-lg-8">
          <h2>Wheel of Life</h2>
          <div id="info">
            <?= View::factory('blocks/admin/hint')?>
          </div>
          <div id="main_chart"></div>
          <div class="pagination-container">
          <div id="pagination" class="pagination">
          <?php echo $pagination;?>
          </div>
          </div>
        </div>
        <div class="col-lg-4" id="right-side">
          <div class="index">
          <h2>Instructions</h2>
          <p>You can create your own Wheel of Life chart.</p>
          <p><a class="btn btn-primary btn-lg" href="<?php echo URL::site(Route::get('static')->uri())?>">Create own chart</a></p>
          </div>
       </div>
</div>
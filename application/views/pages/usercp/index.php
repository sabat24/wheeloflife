<div class="row">
        <div class="col-lg-8">
          <h2>Edit account data</h2>
          <div id="info">
            <?= View::factory('blocks/admin/hint')?>
          </div>
          
          <div class="form">
            <?php echo $form_html;?>
          </div>
          
        </div>
        <div class="col-lg-4" id="right-side">
          <div class="index">
          <h2>Informations</h2>
          <?php echo View::factory('pages/chart/user_last_chart')->set('chart_data', $chart_data);?>
          
          </div>
       </div>
</div>
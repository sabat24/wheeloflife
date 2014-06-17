<!-- CONTENT START -->
    <div class="grid_16" id="content">
    <!--  TITLE START  --> 
    <div class="grid_14">
        <h1 class="users"><?= $title; ?></h1>
    </div>
    
    <div class="clear"></div>
    <!--  TITLE END  -->    
    <!-- #PORTLETS START -->
    <div id="info">
    <?= View::factory('blocks/admin/hint')?>
    </div>
    <div id="portlets">
        <?=Portlets::render_all();?>
    </div>
<!--  END #PORTLETS -->  
    <div class="clear"> </div>
<!-- END CONTENT-->    
  </div>
<div class="clear"> </div>

</div>
<!-- WRAPPER END -->
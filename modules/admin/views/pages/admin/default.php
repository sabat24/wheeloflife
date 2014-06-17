<!-- CONTENT START -->
    <div class="grid_16" id="content">
    <!--  TITLE START  --> 
    <div class="grid_14">
        <h1><?= $title; ?></h1>
    </div>
    
    <div class="clear"></div>
    <!--  TITLE END  -->    
    <!-- #PORTLETS START -->
    <div id="info">
    <?= View::factory('blocks/admin/hint')?>
    </div>
    <div id="content2">
        <?php echo $content;?>
    </div>
<!--  END #PORTLETS -->  
   </div>
    <div class="clear"> </div>
<!-- END CONTENT-->    
  </div>
<div class="clear"> </div>

</div>
<!-- WRAPPER END -->
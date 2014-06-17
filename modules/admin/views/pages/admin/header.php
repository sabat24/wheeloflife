<div class="container_16" id="wrapper">	
  	<!--LOGO-->
	<div class="grid_8" id="logo"><?php echo __('Administration panel');?></div>
    <div class="grid_8">
<!-- USER TOOLS START -->
      <div id="user_tools">
        <span>
            <?=$user['u_name']?> <a href="<?php echo URL::site(Route::get('admin/auth')->uri(array('action' => 'logout')))?>" title="<?php echo __('Logout');?>"><?php echo __('Logout');?></a>
        </span>
    </div>

    </div>
<!-- USER TOOLS END -->    
<div class="grid_16" id="header">
<!-- MENU START -->
<div id="menu">
	<?php echo $menu; ?>
</div>
<!-- MENU END -->
</div>
<div class="grid_16">

<!-- TABS START -->
    <div id="tabs">
         <div class="container">
            <ul>
                <?php echo $submenu; ?>            
           </ul>
        </div>
    </div>
<!-- TABS END -->    
</div>
<?php echo $hiddenmenu; ?>
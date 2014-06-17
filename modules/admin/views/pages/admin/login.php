 
 
<!-- Start: login-holder -->
<div id="login-holder">
	<!--  start loginbox ................................................................................. -->
	<div id="loginbox"<?php echo ($forgot === TRUE) ? ' class="hidden"' : ''?>>
        <?php echo View::factory('hint/block');?>
	<!--  start login-inner -->
	<div id="login-inner">
		<form method="post" action="<?php echo URL::site(Route::get('admin/auth')->uri(array('action' => 'login')))?>">
        <table border="0" cellpadding="0" cellspacing="0">
		<tr>
			<th><?php echo __('Username');?></th>
			<td><input type="text" class="login-inp" name="username" value="<?php echo Arr::get($data, 'username');?>" /></td>
		</tr>
		<tr>
			<th><?php echo __('Password');?></th>
			<td><input type="password" value="" name="password" class="login-inp" /></td>
		</tr>
		<tr>
			<th></th>
			<td valign="top"><input type="checkbox" class="checkbox-size" id="login-check" name="remember" /><label for="login-check"><?php echo __('Remember me');?></label></td>
		</tr>
		<tr>
			<th></th>
			<td><input type="image" class="submit-login"  /></td>
		</tr>
		</table>
		</form>
	</div>
 	<!--  end login-inner -->
	<div class="clear"></div>
	<a href="" class="forgot-pwd"><?php echo __('Forgot password?');?></a>
 </div>
 <!--  end loginbox -->
 
	<!--  start forgotbox ................................................................................... -->
	<div id="forgotbox"<?php echo ($forgot === TRUE) ? ' class="shown"' : ''?>>
		<div id="forgotbox-text"><?php echo __('Please send us your email and we\'ll reset your password.');?></div>
		<!--  start forgot-inner -->
		<div id="forgot-inner">
		<form method="post" action="<?php echo URL::site(Route::get('admin/auth')->uri(array('action' => 'forgot_password')))?>">
        <table border="0" cellpadding="0" cellspacing="0">
		<tr>
			<th><?php echo __('Email address:');?></th>
			<td><input type="text" value=""   class="login-inp" /></td>
		</tr>
		<tr>
			<th> </th>
			<td><input type="image" class="submit-login"  /></td>
		</tr>
		</table>
		</form>
		</div>
		<!--  end forgot-inner -->
		<div class="clear"></div>
		<a href="" class="back-login"><?php echo __('Back to login')?></a>
	</div>
	<!--  end forgotbox -->

</div>
<!-- End: login-holder -->

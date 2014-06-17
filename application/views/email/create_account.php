<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html" />
</head>
<body>
<table cellspacing="0" cellpadding="0" width="600" bgcolor="#ffffff" style="font-family:Arial, serif; font-size:12px; line-height:18px;">
	<tr>
		<td>
			<p>Hi</p>
			<p>Your account has been just created. You can log in to your account using the following data:</p>
			<table>
                <tr>
                    <td bgcolor="#e3e0d7">E-mail / login:</td>
                    <td bgcolor="#e3e0d7"><?php echo $user_data['user_email'];?></td>
                </tr>
                <tr>
                    <td bgcolor="#e3e0d7">Password:</td>
                    <td bgcolor="#e3e0d7"><?php echo $user_data['password'];?></td>
                </tr>
            </table>
            <p>You created <?php echo count($charts_data);?> chart<?php if (count($charts_data) > 1) { echo 's';} ?>.</p>
            <?php foreach($charts_data as $chart_data) { ?>
            <p>You can see your Wheel of Life chart by visiting the following address: <?php echo URL::base(TRUE, FALSE).URL::site(Route::get('static')->uri(array('action' => 'show', 'id' => $chart_data['c_hash'])));?></p>
            <?php if ($chart_data['c_public'] == 0) { ?>
            <p>Your chart is set as a private, so you need to login before you will be able to see your chart.</p>
            <?php } ?>
			<p style="height: 40px;"></p>
            <?php } ?>			
            <p style="font-size: 80%; color: grey;">E-mail was created: <?php echo Date::local_format_datetime();?></p>
		</td>
	</tr>
</table>
</body>
</html>
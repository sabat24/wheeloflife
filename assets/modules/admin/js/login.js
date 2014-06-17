// 2 - START LOGIN PAGE SHOW HIDE BETWEEN LOGIN AND FORGOT PASSWORD BOXES--------------------------------------

$(document).ready(function () {
	$(".forgot-pwd").click(function () {
	   $("#loginbox").hide();
	   $("#forgotbox").show();
	   History.pushState(null, document.title, main_url + 'admin/forgot_password');
       return false;
	});

	$(".back-login").click(function () {
	   $("#loginbox").show();
	   $("#forgotbox").hide();
	   History.pushState(null, document.title, main_url + 'admin/login');
       return false;
	});
});

// END ----------------------------- 2
<?php
function login_page_content($content) {
	global $post;
	/* Return */
	if ($post->post_name != 'login') {
		return $content;
	} else if (strpos($content, '[mp-ssv-frontend-members-login]') === false) {
		return $content;
	} else if (is_user_logged_in()) {
		$current_user = wp_get_current_user();
		$url = (is_ssl() ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'?logout=success';
		$link = '<a href="'.wp_logout_url($url).'">Logout</a>';
		$content = '<div class="notification">';
		$content .= $current_user->user_firstname;
		$content .= ' ';
		$content .= $current_user->user_lastname;
		$content .= ' you\'re already logged in. Do you want to ';
		$content .= $link;
		$content .= '?</div>';
		return $content;
	} else if (isset($_GET['logout']) && strpos($_GET['logout'], 'success') !== false) {
		$content = '<div class="notification">';
		$content .= 'Logout successful';
		$content .= '</div>';
	} else {
		$content = '';
	}
	$content .= '<form name="loginform" id="loginform" action="/wp-login.php" method="post">';
	$content .= '<table>';
	$content .= '<tr>';
	$content .= '<th><label for="user_login">Username / Email</label></th>';
	$content .= '<td><input type="text" name="log" id="user_login" class="input" value="" size="20"></td>';
	$content .= '</tr><tr>';
	$content .= '<th><label for="user_pass">Password</label></th>';
	$content .= '<td><input type="password" name="pwd" id="user_pass" class="input" value="" size="20"></td>';
	$content .= '</tr><tr>';
	$content .= '<th></th>';
	$content .= '<td><p class="login-remember"><label><input name="rememberme" type="checkbox" id="rememberme" value="forever" checked="checked"> Remember Me</label></p></td>';
	$content .= '</tr><tr>';
	$content .= '<th></th>';
	$content .= '<td><button class="mui-btn mui-btn--primary" type="submit" name="wp-submit" id="wp-submit" class="button-primary">Login</button></td>';
	$content .= '<input type="hidden" name="redirect_to" value="http://allterrain.nl/profile">';
	$content .= '</tr>';
	$content .= '</table>';
	$content .= '</form>';
	return $content;
}
add_filter( 'the_content', 'login_page_content' );
?>
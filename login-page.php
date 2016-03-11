<?php
function login_page_content($content) {
	global $post;
	/* Return */
	ob_start();
	if ($post->post_name != 'login') {
		return $content;
	} else if (strpos($content, '[mp-ssv-frontend-members-login]') === false) {
		return $content;
	} else if (is_user_logged_in()) {
		$current_user = wp_get_current_user();
		$url = (is_ssl() ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'?logout=success';
		$link = '<a href="'.wp_logout_url($url).'">Logout</a>';
		ob_start(); ?>
		<div class="notification">
			<?php echo $current_user->user_firstname.' '.$current_user->user_lastname.' you\'re already logged in. Do you want to '.$link.'?'; ?>
		</div>
		<?php
		return ob_get_clean();
	} else if (isset($_GET['logout']) && strpos($_GET['logout'], 'success') !== false) {
		?>
		<div class="notification">Logout successful</div>
		<?php
	}
	if (current_theme_supports('mui')) {
		?>
		<form name="loginform" id="loginform" action="/wp-login.php" method="post">
			<div class="mui-textfield mui-textfield--float-label">
				<input type="text" name="log" id="user_login">
				<label for="user_login">Username / Email</label>
			</div>
			<div class="mui-textfield mui-textfield--float-label">
				<input type="password" name="pwd" id="user_pass">
				<label for="user_pass">Password</label>
			</div>
			<div>
				<input name="rememberme" type="checkbox" id="rememberme" value="forever" checked="checked" style="width: auto; margin-right: 10px;">
				<label>Remember Me</label>
			</div>
			<button class="mui-btn mui-btn--primary" type="submit" name="wp-submit" id="wp-submit" class="button-primary">Login</button>
			<input type="hidden" name="redirect_to" value="http://allterrain.nl/profile">
		</form>
		<?php
	} else {
		?>
		<form name="loginform" id="loginform" action="/wp-login.php" method="post">
			<div class="mui-textfield mui-textfield--float-label">
				<label for="user_login">Username / Email</label>
				<input type="text" name="log" id="user_login">
			</div>
			<div class="mui-textfield mui-textfield--float-label">
				<label for="user_pass">Password</label>
				<input type="password" name="pwd" id="user_pass">
			</div>
			<div>
				<label>Remember Me</label>
				<input name="rememberme" type="checkbox" id="rememberme" value="forever" checked="checked" style="width: auto; margin-right: 10px;">
			</div>
			<button class="mui-btn mui-btn--primary" type="submit" name="wp-submit" id="wp-submit" class="button-primary">Login</button>
			<input type="hidden" name="redirect_to" value="http://allterrain.nl/profile">
		</form>
		<?php
	}
	$content = ob_get_clean();
	return $content;
}
add_filter( 'the_content', 'login_page_content' );
?>
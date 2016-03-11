<?php
if (!function_exists("add_mp_ssv_menu")) {
	function add_mp_ssv_menu() {
		add_menu_page('MP SSV Options', 'MP-SSV Options', 'manage_options', 'mp_ssv_settings', 'mp_ssv_settings_page');
		add_submenu_page( 'mp_ssv_settings', 'General', 'General', 'manage_options', 'mp_ssv_settings');
	}
	function mp_ssv_settings_page() {
		?>
		<div class="wrap">
			<h1>MP-SSV General Options</h1>
		</div>
		<?php
	}
	add_action('admin_menu', 'add_mp_ssv_menu');
}
?>
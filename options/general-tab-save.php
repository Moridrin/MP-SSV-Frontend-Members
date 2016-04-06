<?php
global $options;
update_option('mp_ssv_frontend_members_register_page', $_POST['mp_ssv_frontend_members_register_page']);
if (isset($_POST['mp_ssv_frontend_members_show_admin_bar_front'])) {
	update_option('mp_ssv_frontend_members_show_admin_bar_front', 'true');
} else {
	update_option('mp_ssv_frontend_members_show_admin_bar_front', 'false');
}
update_option('mp_ssv_recaptcha_site_key', $_POST['mp_ssv_recaptcha_site_key']);
update_option('mp_ssv_recaptcha_secret_key', $_POST['mp_ssv_recaptcha_secret_key']);
?>
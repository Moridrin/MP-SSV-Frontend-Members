<?php
global $options;
update_option('mp_ssv_frontend_members_register_page', $_POST['mp_ssv_frontend_members_register_page']);
if (isset($_POST['mp_ssv_frontend_members_guest_custom_roles_enabled'])) {
	update_option('mp_ssv_frontend_members_guest_custom_roles_enabled', 'true');
} else {
	update_option('mp_ssv_frontend_members_guest_custom_roles_enabled', 'false');
}
?>
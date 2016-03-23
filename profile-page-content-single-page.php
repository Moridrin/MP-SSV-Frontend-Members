<?php
function mp_ssv_profile_page_content_single_page() {
	global $wpdb;
	$current_user = wp_get_current_user();
	$table_name = $wpdb->prefix."mp_ssv_frontend_members_fields";
	$group = "";
	
	$url = (is_ssl() ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'?logout=success';
	$tabs = $wpdb->get_results("SELECT title FROM $table_name WHERE component = '[tab]'");
	$content = "";
	ob_start(); ?>
	<form name="members_profile_form" id="member_<?php echo $tab_title; ?>_form" action="/profile" method="post">
		<?php
		for ($i = 0; $i < count($tabs); $i++) {
			$tab = json_decode(json_encode($tabs[$i]),true);
			$tab_title = stripslashes($tab["title"]);
			$identifier = preg_replace('/[^A-Za-z0-9\-]/', '_', str_replace(" ", "_", $tab_title));
			$title_value = str_replace("_", " ", $tab_title);
			$fields_in_tab = $wpdb->get_results("SELECT * FROM $table_name WHERE tab = '$tab_title'");
			foreach ($fields_in_tab as $field) {
				$field = json_decode(json_encode($field),true);
				$title = stripslashes($field["title"]);
				$identifier = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '_', str_replace(" ", "_", $title)));
				$title_value = str_replace("_", " ", $title);
				$database_component = stripslashes($field["component"]);
				if ($database_component == "checkbox" || $database_component == "radio" || $database_component == "select") {
					$group = $database_component;
					$role_title = strtolower(str_replace(" ", "_", $title));
				}
				$is_role = $database_component == "[role checkbox]";
				$is_group = $database_component == "radio" || $database_component == "select";
				$is_header = $database_component == "[header]";
				$is_tab = $database_component == "[tab]";
				$is_image = strpos($database_component, "[image]") !== false;
				$is_events_registrations = $database_component == "[mp-ssv-events-registrations]";
				if ($is_tab) {
					mp_ssv_echo_tab_title($title);
				} else if ($is_header) {
					mp_ssv_echo_header($title);
				} else if ($is_group) {
					mp_ssv_echo_group($database_component, $identifier, $title, $current_user);
				} else if ($is_role) {
					mp_ssv_echo_role($identifier, $title, $current_user);
				} else if ($is_image) {
					mp_ssv_echo_image($database_component, $current_user, $identifier, $title);
				} else if ($is_events_registrations) {
					echo mp_ssv_profile_page_registrations_table_content();
				} else {
					$identifier = mp_ssv_get_identifier($database_component);
					$component_value = mp_ssv_get_component_value($identifier, $current_user);
					if (strpos($database_component, 'type="file"') !== false) {
						mp_ssv_echo_file($database_component, $title);
					} else {
						mp_ssv_echo_default($database_component, $component_value, $title);
					}
				}
			}
		}
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if (is_plugin_active('mp-ssv-events/mp-ssv-events.php') && get_option('mp_ssv_show_registrations_in_profile') == 'true') {
			mp_ssv_echo_tab_title("Registrations");
			echo mp_ssv_profile_page_registrations_table_content();
		}
		?>
		<button class="mui-btn mui-btn--primary" type="submit" name="submit" id="submit" class="button-primary">Save</button>
		<input type="hidden" name="what-to-save" value="All"/>
	</form>
	<?php
	$content .= ob_get_clean();
	return $content;
}
?>
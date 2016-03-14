<?php
function mp_ssv_profile_page_content_tabs() {
	global $wpdb;
	$current_user = wp_get_current_user();
	$table_name = $wpdb->prefix."mp_ssv_frontend_members_fields";
	$group = "";
	
	$url = (is_ssl() ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'?logout=success';
		$tabs = $wpdb->get_results("SELECT title FROM $table_name WHERE component = '[tab]'");
		$content = '<ul id="profile-menu" class="mui-tabs__bar mui-tabs__bar--justified">';
		for ($i = 0; $i < count($tabs); $i++) {
			$tab = json_decode(json_encode($tabs[$i]),true);
			$title = stripslashes($tab["title"]);
			$identifier = preg_replace('/[^A-Za-z0-9\-]/', '_', str_replace(" ", "_", $title));
			$title_value = str_replace("_", " ", $title);
			$li_class = "";
			$onload = "";
			if ($i == 0) { $li_class = ' class="mui--is-active"'; }
			$content .= '<li'.$li_class.'><a class="mui-btn mui-btn--flat" data-mui-toggle="tab" data-mui-controls="pane-'.$identifier.'">'.$title_value.'</a></li>';
		}
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if (is_plugin_active('mp-ssv-events/mp-ssv-events.php') && get_option('mp_ssv_show_registrations_in_profile') == 'true') {
			$content .= '<li><a class="mui-btn mui-btn--flat" data-mui-toggle="tab" data-mui-controls="pane-registrations">Registrations</a></li>';
		}
		$content .= '<li><a class="mui-btn mui-btn--flat mui-btn--danger" href="'.wp_logout_url($url).'">Logout</a></li>';
		$content .= "</ul>";
		for ($i = 0; $i < count($tabs); $i++) {
			$tab = json_decode(json_encode($tabs[$i]),true);
			$tab_title = stripslashes($tab["title"]);
			$identifier = preg_replace('/[^A-Za-z0-9\-]/', '_', str_replace(" ", "_", $tab_title));
			$title_value = str_replace("_", " ", $tab_title);
			ob_start(); ?>
			<div class="mui-tabs__pane<?php if ($i == 0) { echo " mui--is-active"; } ?>" id="pane-<?php echo $identifier; ?>">
				<form name="members_profile_form" id="member_<?php echo $tab_title; ?>_form" action="/profile" method="post" enctype="multipart/form-data">
					<?php 
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
					?>
					<button class="mui-btn mui-btn--primary" type="submit" name="submit" id="submit" class="button-primary">Save</button>
					<input type="hidden" name="what-to-save" value="<?php echo $tab_title; ?>"/>
				</form>
			</div>
			<?php $content .= ob_get_clean();

			if (isset($_POST['what-to-save'])) {
				if ($_POST['what-to-save'] == $tab_title) {
					$content .= '<script>$(document).ready(function mp_ssv_load() { mui.tabs.activate("pane-'.$tab_title.'"); });</script>';
				}
			}
		}
		ob_start(); ?>
	<div class="mui-tabs__pane" id="pane-registrations">
		<form name="members_profile_form" id="member_<?php echo $tab_title; ?>_form" action="/profile" method="post">
			<?php
			if (is_plugin_active('mp-ssv-events/mp-ssv-events.php') && get_option('mp_ssv_show_registrations_in_profile') == 'true') {
				echo mp_ssv_profile_page_registrations_table_content();
			}
			?>
			<button class="mui-btn mui-btn--primary" type="submit" name="submit" id="submit" class="button-primary">Save</button>
			<input type="hidden" name="what-to-save" value="<?php echo $tab_title; ?>"/>
		</form>
	</div>
	<?php
	$content .= ob_get_clean();
	return $content;
}
?>
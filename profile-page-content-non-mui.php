<?php
function mp_ssv_profile_page_content_non_mui() {
	global $wpdb;
	$can_edit = true;
	$current_user = null;
	if (isset($_GET['user_id'])) {
		$current_user = get_user_by('id', $_GET['user_id']);
	} else {
		$current_user = wp_get_current_user();	
	}
	if ($current_user != wp_get_current_user() && !current_user_can('edit_user')) {
		$can_edit = false;
	}
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
					mp_ssv_echo_non_mui_tab_title($title);
				} else if ($is_header) {
					mp_ssv_echo_non_mui_header($title);
				} else if ($is_group) {
					mp_ssv_echo_non_mui_group($database_component, $identifier, $title, $current_user, $can_edit);
				} else if ($is_role) {
					mp_ssv_echo_non_mui_role($identifier, $title, $current_user, $can_edit);
				} else if ($is_image) {
					mp_ssv_echo_non_mui_image($database_component, $current_user, $identifier, $title, $can_edit);
				} else if ($is_events_registrations) {
					echo mp_ssv_profile_page_registrations_table_content();
				} else {
					$identifier = mp_ssv_get_identifier($database_component);
					$component_value = mp_ssv_get_component_value($identifier, $current_user);
					mp_ssv_echo_non_mui_default($database_component, $component_value, $title, $can_edit);
				}
			}
		}
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if (is_plugin_active('mp-ssv-events/mp-ssv-events.php') && get_option('mp_ssv_show_registrations_in_profile') == 'true') {
			mp_ssv_echo_non_mui_tab_title("Registrations");
			echo mp_ssv_profile_page_registrations_table_content();
		}
		if ($can_edit) {
			?><button class="mui-btn mui-btn--primary" type="submit" name="submit" id="submit" class="button-primary">Save</button><?php
		}
		?>
		<input type="hidden" name="what-to-save" value="All"/>
	</form>
	<?php
	$content .= ob_get_clean();
	return $content;
}

function mp_ssv_echo_non_mui_tab_title($title) {
	echo '<H1>'.$title.'</H1>';
}

function mp_ssv_echo_non_mui_header($title) {
	echo '<H3>'.$title.'</H3>';
}

function mp_ssv_echo_non_mui_group($database_component, $identifier, $title, $current_user, $can_edit) {
	global $wpdb;
	$group_items_table_name = $wpdb->prefix."mp_ssv_frontend_members_fields_group_options";
	$group_options = $wpdb->get_results( 
		"SELECT *
			FROM $group_items_table_name"
	);
	if ($database_component == "select") {
		echo '<div>';
		echo '<label for="group_'.$identifier.'">'.$title.'</label>';
		if ($can_edit) {
			echo '<select id="'.$identifier.'" name="group_'.$identifier.'">';
		} else {
			echo '<select id="'.$identifier.'" name="group_'.$identifier.'" disabled>';
		}
	} else {

	}
	foreach ($group_options as $group_option) {
		$group_option = json_decode(json_encode($group_option),true);
		$group_option = stripslashes($group_option["option_text"]);
		$is_role = strpos($group_option, ";[role]") !== false;
		if ($is_role) {
			$group_option = str_replace(";[role]", "", $group_option);
			$group_option_label = $group_option;
			$group_option = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '_', str_replace(" ", "_", $group_option)));
			mp_ssv_echo_non_mui_group_option($group_option, $group_option_label, $current_user, $identifier, $is_role);
		} else {
			$group_option_label = $group_option;
			$group_option = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '_', str_replace(" ", "_", $group_option)));
			mp_ssv_echo_non_mui_group_option($group_option, $group_option_label, $current_user, $identifier, $is_role);
		}
	}
	if ($database_component == "select") {
		echo '</select>';
		echo '</div>';
	} else {

	}
}

function mp_ssv_echo_non_mui_group_option($group_option, $group_option_label, $current_user, $identifier, $is_role) {
	$role_tag = "";
	if ($is_role) {
		$role_tag = ';[role]';
	}
	$option = '<option';
	$option .= ' value="'.$group_option.$role_tag.'"';
	if($group_option.$role_tag == get_user_meta($current_user->ID, "group_".$identifier, true)) {
		$option .= " selected";
	}
	$option .= '>';
	$option .= $group_option_label;
	$option .= "</option>";
	echo $option;
}

function mp_ssv_echo_non_mui_role($identifier, $title, $current_user, $can_edit) {
	?>
	<div>
		<input id="<?php echo $identifier; ?>" type="checkbox" name="<?php echo $identifier; ?>" value="<?php echo $title; ?>" style="width: auto; margin-right: 10px;" <?php if(get_user_meta($current_user->ID, $identifier, true) == 1) { echo "checked"; } ?> <?php if (!$can_edit) { echo "disabled"; } ?>/>
		<label for="<?php echo $identifier; ?>"><?php echo $title; ?></label>
	</div>
	<?php
}

function mp_ssv_echo_non_mui_image($database_component, $current_user, $identifier, $title, $can_edit) {
	$required = strpos($database_component, "required") !== false && strlen(get_user_meta($current_user->ID, $identifier, true)) < 1;
	if ($can_edit) {
		?>
		<div>
			<label for="<?php echo $identifier; ?>"><?php echo $title; ?></label>
			<input id="<?php echo $identifier; ?>" type="file" name="<?php echo $identifier; ?>" accept="image/*" <?php if($required) { echo "required"; } ?>/>
		</div>
		<?php
	}
	if (strpos($database_component, "show_preview") !== false) {
		?>
		<img class="image-preview" src="<?php echo get_user_meta($current_user->ID, $identifier, true); ?>"/><br/>
		<?php
	}
}

function mp_ssv_echo_non_mui_default($database_component, $component_value, $title, $can_edit) {
	$component = explode(">", $database_component)[0];
	$component .= ' value="'.$component_value.'"';
	$component .= str_replace(explode(">", $database_component)[0], "", $database_component);
	?>
	<div>
		<label><?php echo $title; ?></label>
		<?php
		if (strpos($component, "readonly") == false && strpos($component, "disabled") == false && $can_edit == false) {
			$component = str_replace(">", "disabled >", $component);
		}
		echo $component;
		?>
	</div>
	<?php
}
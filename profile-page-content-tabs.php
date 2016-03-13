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
		if (function_exists('mp_ssv_profile_page_registrations_table_content')) {
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
				<form name="members_profile_form" id="member_<?php echo $tab_title; ?>_form" action="/profile" method="post">
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
						if ($is_tab) {
						} else if ($is_header) {
							mp_ssv_echo_header($title);
						} else if ($is_group) {
							mp_ssv_echo_group($database_component, $identifier, $title, $current_user);
						} else if ($is_role) {
							mp_ssv_echo_role($identifier, $title, $current_user);
						} else if ($is_image) {
							mp_ssv_echo_image($database_component, $current_user, $identifier, $title);
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
			if (function_exists('mp_ssv_profile_page_registrations_table_content')) {
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

function mp_ssv_echo_header($title) {
	echo '<legend>'.$title.'</legend>';
}

function mp_ssv_echo_group($database_component, $identifier, $title, $current_user) {
	global $wpdb;
	$group_items_table_name = $wpdb->prefix."mp_ssv_frontend_members_fields_group_options";
	$group_options = $wpdb->get_results( 
		"SELECT *
			FROM $group_items_table_name"
	);
	if ($database_component == "select") {
		echo '<div class="mui-select mui-textfield">';
		echo '<select id="'.$identifier.'" name="group_'.$identifier.'">';
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
			mp_ssv_echo_group_option($group_option, $group_option_label, $current_user, $identifier, $is_role);
		} else {
			$group_option_label = $group_option;
			$group_option = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '_', str_replace(" ", "_", $group_option)));
			mp_ssv_echo_group_option($group_option, $group_option_label, $current_user, $identifier, $is_role);
		}
	}
	if ($database_component == "select") {
		echo '</select>';
		echo '<label for="group_'.$identifier.'">'.$title.'</label>';
		echo '</div>';
	} else {

	}
}

function mp_ssv_echo_group_option($group_option, $group_option_label, $current_user, $identifier, $is_role) {
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
	$option .= '</option>';
	echo $option;
}

function mp_ssv_echo_role($identifier, $title, $current_user) {
	?>
	<div>
		<input id="<?php echo $identifier; ?>" type="checkbox" name="<?php echo $identifier; ?>" value="<?php echo $title; ?>" style="width: auto; margin-right: 10px;" <?php if(get_user_meta($current_user->ID, $identifier, true) == 1) { echo "checked"; } ?>/>
		<label for="<?php echo $identifier; ?>"><?php echo $title; ?></label>
	</div>
	<?php
}

function mp_ssv_echo_image($database_component, $current_user, $identifier, $title) {
	$required = strpos($database_component, "required") !== false && strlen(get_user_meta($current_user->ID, $identifier, true)) < 1;
	?>
	<div class="mui-textfield">
		<input id="<?php echo $identifier; ?>" type="file" name="<?php echo $identifier; ?>" accept="image/*" <?php if($required) { echo "required"; } ?>/>
		<label for="<?php echo $identifier; ?>"><?php echo $title; ?></label>
	</div>
	<?php
	if (strpos($database_component, "show_preview") !== false) {
		?>
		<img class="image-preview" src="<?php echo get_user_meta($current_user->ID, $identifier, true); ?>"/><br/>
		<?php
	}
}

function mp_ssv_echo_file($database_component, $title) {
	?>
	<div class="mui-textfield">
		<?php echo $database_component; ?>
		<label><?php echo $title; ?></label>
	</div>
	<?php
}

function mp_ssv_echo_default($database_component, $component_value, $title) {
	$component = explode(">", $database_component)[0];
	$component .= ' value="'.$component_value.'"';
	$component .= str_replace(explode(">", $database_component)[0], "", $database_component);
	?>
	<div class="mui-textfield <?php if (strpos($component, 'type="date"') == false) { echo "mui-textfield--float-label"; } ?>">
		<?php echo $component; ?>
		<label><?php echo $title; ?></label>
	</div>
	<?php
}

function mp_ssv_get_identifier($database_component) {
	$identifier = "";
	if (($database_component) != "" && strpos($database_component, "name=\"") !== false) {
		$identifier = preg_replace("/.*name=\"/","",stripslashes($database_component));
		$identifier = preg_replace("/\".*/","",$identifier);
		$identifier = strtolower($identifier);
	}
	return $identifier;
}

function mp_ssv_get_component_value($identifier, $current_user) {
	$component_value = "";
	if ($identifier == "user_login") {
		$component_value = get_userdata($current_user->ID)->user_login;
	} else if ($identifier == "user_nicename") {
		$component_value = get_userdata($current_user->ID)->user_nicename;
	} else if ($identifier == "user_email") {
		$component_value = get_userdata($current_user->ID)->user_email;
	} else if ($identifier == "display_name") {
		$component_value = get_userdata($current_user->ID)->display_name;
	} else {
		$component_value = get_user_meta($current_user->ID, $identifier, true);
	}
	return $component_value;
}
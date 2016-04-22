<?php
function mp_ssv_profile_page_login_redirect() {
	global $post;
	if ($post == null) {
		return;
	}
	$post_name_correct = $post->post_name == 'profile';
  if (!is_user_logged_in() && $post_name_correct) {
		wp_redirect("/login");
		exit;
  }
}
add_action('wp_head','mp_ssv_profile_page_login_redirect');

function mp_ssv_profile_page_setup($content) {
	global $post;
	if ($post->post_name != 'profile') {
		return $content;
	} else if (strpos($content, '[mp-ssv-frontend-members-profile]') === false) {
		return $content;
	}
	if (isset($_POST['what-to-save'])) {
		mp_ssv_save_members_profile($_POST['what-to-save']);
	}
	$content = mp_ssv_profile_page_content();
	return $content;
}
add_filter( 'the_content', 'mp_ssv_profile_page_setup' );

function mp_ssv_profile_page_content() {
	$content = "";
	if (current_theme_supports('mui')) {
		$content = '<div class="mui--hidden-xs">';
		$content .= mp_ssv_profile_page_content_tabs();
		$content .= '</div>';
		$content .= '<div class="mui--visible-xs-block">';
		$content .= mp_ssv_profile_page_content_single_page();
		$content .= '</div>';
	} else {
		$content .= mp_ssv_profile_page_content_non_mui();
	}
	return $content;
}

function mp_ssv_echo_tab_title($title) {
	echo '<H1>'.$title.'</H1>';
}

function mp_ssv_echo_header($title) {
	echo '<legend>'.$title.'</legend>';
}

function mp_ssv_echo_group($database_component, $identifier, $title, $current_user, $can_edit) {
	global $wpdb;
	$group_items_table_name = $wpdb->prefix."mp_ssv_frontend_members_fields_group_options";
	$group_options = $wpdb->get_results( 
		"SELECT *
			FROM $group_items_table_name
			WHERE `parent_group` = '$title'"
	);
	if ($database_component == "select") {
		echo '<div class="mui-select mui-textfield">';
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
	if($current_user != null && $group_option.$role_tag == get_user_meta($current_user->ID, "group_".$identifier, true)) {
		$option .= " selected";
	}
	$option .= '>';
	$option .= $group_option_label;
	$option .= '</option>';
	echo $option;
}

function mp_ssv_echo_role($identifier, $title, $current_user, $can_edit) {
	?>
	<div>
		<input id="<?php echo $identifier; ?>" type="checkbox" name="<?php echo $identifier; ?>" value="<?php echo $title; ?>" style="width: auto; margin-right: 10px;" <?php if($current_user != null && get_user_meta($current_user->ID, $identifier, true) == 1) { echo "checked"; } ?> <?php if (!$can_edit) { echo "disabled"; } ?>/>
		<label for="<?php echo $identifier; ?>"><?php echo $title; ?></label>
	</div>
	<?php
}

function mp_ssv_echo_image($database_component, $current_user, $identifier, $title, $can_edit) {
	$required;
	if (strpos($database_component, "required") !== false) {
		if ($current_user == null) {
			$required = true;
		} else if (strlen(get_user_meta($current_user->ID, $identifier, true)) < 1) {
			$required = true;
		} else {
			$required = false;
		}
	} else {
		$required = false;
	}
	if ($can_edit) {
		?>
		<div class="mui-textfield">
			<input id="<?php echo $identifier; ?>" type="file" name="<?php echo $identifier; ?>" accept="image/*" <?php if($required) { echo "required"; } ?>/>
			<label for="<?php echo $identifier; ?>"><?php echo $title; ?></label>
		</div>
		<?php
	}
	if (strpos($database_component, "show_preview") !== false) {
		if(!($current_user == null || strlen(get_user_meta($current_user->ID, $identifier, true)) < 1)) {
			?>
			<img class="image-preview" src="<?php echo get_user_meta($current_user->ID, $identifier, true); ?>"/><br/>
			<?php
		}
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

function mp_ssv_echo_default($database_component, $component_value, $title, $can_edit) {
	$component = explode(">", $database_component)[0];
	$component .= ' value="'.$component_value.'"';
	$component .= str_replace(explode(">", $database_component)[0], "", $database_component);
	?>
	<div class="mui-textfield <?php if (strpos($component, 'type="date"') == false) { echo "mui-textfield--float-label"; } ?>">
		<?php
			if (strpos($component, "readonly") == false && strpos($component, "disabled") == false && $can_edit == false) {
				$component = str_replace(">", "disabled >", $component);
			}
			echo $component;
		?>
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
	if ($current_user == null) {
		return "";
	}
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

include_once "profile-page-content-tabs.php";
include_once "profile-page-content-single-page.php";
include_once "profile-page-content-non-mui.php";

function mp_ssv_save_members_profile($what_to_save) {
	if (isset($_GET['user_id'])) {
		$user = get_user_by('id', $_GET['user_id']);
	} else {
		$user = wp_get_current_user();
	}
	foreach( $_POST as $name => $val ) {
		if (strpos($name, "_reset") !== false) {
			$name = str_replace("_reset", "", $name);
		}
		if (!mp_ssv_update_user_meta($user->ID, $name, $val)) {
			echo "Cannot change the user-login. Please concider setting the field display to 'read-only' or 'disabled'";
		}
	}
	if (!function_exists("mp_ssv_update_mailchimp_member")) {
		mp_ssv_update_mailchimp_member($user);
	}
}
?>
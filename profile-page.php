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
	global $wpdb;
	$current_user = wp_get_current_user();
	$table_name = $wpdb->prefix."mp_ssv_mailchimp_merge_fields";
	$merge_fields_to_sync = $wpdb->get_results("SELECT * FROM $table_name");
	$table_name = $wpdb->prefix."mp_ssv_frontend_members_fields";
	$tabs = $wpdb->get_results("SELECT * FROM $table_name WHERE component = '[tab]'");
	for ($i = 0; $i < count($tabs); $i++) {
		$tab = json_decode(json_encode($tabs[$i]),true);
		$tab_title = stripslashes($tab["title"]);
		if ($what_to_save == $tab_title || $what_to_save == "all") {
			$group_type = "";
			$group = "";
			$fields_in_tab = $wpdb->get_results("SELECT * FROM $table_name WHERE tab = '$tab_title'");
			foreach ($fields_in_tab as $field) {
				$field = json_decode(json_encode($field),true);
				$title = stripslashes($field["title"]);
				$identifier = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '_', str_replace(" ", "_", $title)));
				$title_value = str_replace("_", " ", $title);
				$database_component = stripslashes($field["component"]);
				$is_group = $database_component == "select" || $database_component == "radio";
				$is_role = $database_component == "[role checkbox]";
				$is_header = $database_component == "[header]";
				$is_tab = $database_component == "[tab]";
				$is_image = strpos($database_component, "[image]") !== false;
				if (!$is_header && !$is_tab) {
					if ($is_group) {
						$group_type = $database_component;
						$group = strtolower(str_replace(" ", "_", $title));
						$group_value = $_POST["group_".$group];
						$old_role = str_replace(";[role]", "", get_user_meta($current_user->ID, "group_".$group, true));
						if (strpos($group_value, ";[role]") !== false) {
							$group_value = str_replace(";[role]", "", $group_value);
							$group_value = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '_', str_replace(" ", "_", $group_value)));
							$current_user->remove_role($old_role);
							$current_user->add_role($group_value);
							update_user_meta($current_user->ID, "group_".$group, $group_value.";[role]");
						} else {
							$group_value = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '_', str_replace(" ", "_", $group_value)));
							update_user_meta($current_user->ID, "group_".$group, $group_value);
						}
						foreach ($merge_fields_to_sync as $row) {
							$row = json_decode(json_encode($row),true);
							if (in_array($group, $row)) {
								$mailchimp_tag = $row["mailchimp_tag"];
								$merge_fields[$mailchimp_tag] = $group_value;
							}
						}
					} else if ($is_role) {
						if (isset($_POST[$identifier])) {
							update_user_meta($current_user->ID, $identifier, 1);
							$current_user->add_role($identifier);
						} else {
							update_user_meta($current_user->ID, $identifier, 0);
							$current_user->remove_role($identifier);
						}
						foreach ($merge_fields_to_sync as $row) {
							$row = json_decode(json_encode($row),true);
							if (in_array($identifier, $row)) {
								$mailchimp_tag = $row["mailchimp_tag"];
								$merge_fields[$mailchimp_tag] = get_user_meta($current_user->ID, $identifier, true);
							}
						}
					} else if ($is_image) {
						if ( ! function_exists( 'wp_handle_upload' ) ) {
							require_once( ABSPATH . 'wp-admin/includes/file.php' );
						}
						$file_location = wp_handle_upload($_FILES[$identifier], array('test_form' => FALSE));
						if ($file_location && !isset($file_location['error'])) {
							update_user_meta($current_user->ID, $identifier, $file_location["url"]);	
							update_user_meta($current_user->ID, $identifier."_path", $file_location["file"]);	
						}
					} else if (($database_component) != "" && strpos($database_component, "name=\"") !== false && strpos($database_component, "readonly") == false) {
						if (strpos($database_component, 'type="file"') !== false) {
							if ( ! function_exists( 'wp_handle_upload' ) ) {
								require_once( ABSPATH . 'wp-admin/includes/file.php' );
							}
							$file_location = wp_handle_upload($_FILES[$identifier], array('test_form' => FALSE));
							if ($file_location && !isset($file_location['error'])) {
								unlink(get_user_meta($current_user->ID, $identifier."_path", true));
								update_user_meta($current_user->ID, $identifier, $file_location["url"]);	
								update_user_meta($current_user->ID, $identifier."_path", $file_location["file"]);	
							}
						} else {
							$identifier = preg_replace("/.*name=\"/","",stripslashes($database_component));
							$identifier = preg_replace("/\".*/","",$identifier);
							$identifier = strtolower($identifier);
							update_user_meta($current_user->ID, $identifier, $_POST[$identifier]);	
						}
						foreach ($merge_fields_to_sync as $row) {
							$row = json_decode(json_encode($row),true);
							if (in_array($identifier, $row)) {
								$mailchimp_tag = $row["mailchimp_tag"];
								$merge_fields[$mailchimp_tag] = get_user_meta($current_user->ID, $identifier, true);
							}
						}
					}
				}
			}
		}
	}
	$member = array();
	$merge_fields['FNAME'] = get_user_meta($current_user->ID, "first_name", true);
	$merge_fields['LNAME'] = get_user_meta($current_user->ID, "last_name", true);
	$member["email_address"] = $current_user->user_email;
	$member["status"] = "subscribed";
	$member["merge_fields"] = $merge_fields;
	mp_ssv_update_mailchimp_member($member);
}

if (!function_exists("mp_ssv_update_mailchimp_member")) {
	function mp_ssv_update_mailchimp_member($member) {
		$apiKey = get_option('mp_ssv_mailchimp_api_key');
		$listID = get_option('mailchimp_member_sync_list_id');

    $memberId = md5(strtolower($member['email_address']));
		$memberCenter = substr($apiKey,strpos($apiKey,'-')+1);
		$url = 'https://' . $memberCenter . '.api.mailchimp.com/3.0/lists/' . $listID . '/members/' . $memberId;
		$ch = curl_init($url);
		
		$json = json_encode($member);
		
		curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $apiKey);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
	
		$curl_results = json_decode(curl_exec($ch), true);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		return $httpCode;
	}
}
?>
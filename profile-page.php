<?php
function mp_ssv_profile_page_login_redirect() {
	global $post;
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
		save_members_profile($_POST['what-to-save']);
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

include_once "profile-page-content-tabs.php";
include_once "profile-page-content-single-page.php";
include_once "profile-page-content-non-mui.php";

function mp_ssv_save_members_profile($what_to_save) {
	global $wpdb;
	$current_user = wp_get_current_user();
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
					} else if ($is_role) {
						if (isset($_POST[$identifier])) {
							update_user_meta($current_user->ID, $identifier, 1);
							$current_user->add_role($identifier);
						} else {
							update_user_meta($current_user->ID, $identifier, 0);
							$current_user->remove_role($identifier);
						}
					} else if (($database_component) != "" && strpos($database_component, "name=\"") !== false && strpos($database_component, "readonly") == false) {
						if (strpos($database_component, 'type="file"') !== false) {
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
					}
				}
			}
		}
	}
}
?>
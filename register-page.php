<?php
function mp_ssv_register_page_setup($content) {
	global $post;
	if ($post->post_name != 'register') {
		return $content;
	} else if (strpos($content, '[mp-ssv-frontend-members-register]') === false) {
		return $content;
	}
	if (isset($_POST['what-to-save'])) {
		mp_ssv_save_member_registration($_POST['what-to-save']);
	}
	$content = mp_ssv_register_page_content();
	return $content;
}
add_filter( 'the_content', 'mp_ssv_register_page_setup' );


function mp_ssv_register_page_content() {
	global $wpdb;
	$table_name = $wpdb->prefix."mp_ssv_frontend_members_fields";
	$group = "";
	
	$url = (is_ssl() ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'?logout=success';
	$tabs = $wpdb->get_results("SELECT title FROM $table_name WHERE component = '[tab]'");
	$content = "";
	ob_start(); ?>
	<form name="members_profile_form" id="member_<?php echo $tab_title; ?>_form" action="/register" method="post" enctype="multipart/form-data">
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
				if ($is_tab) {
					mp_ssv_echo_tab_title($title);
				} else if ($is_header) {
					mp_ssv_echo_header($title);
				} else if ($is_group) {
					mp_ssv_echo_group($database_component, $identifier, $title, null);
				} else if ($is_role) {
					mp_ssv_echo_role($identifier, $title, null);
				} else if ($is_image) {
					mp_ssv_echo_image($database_component, null, $identifier, $title);
				} else {
					$identifier = mp_ssv_get_identifier($database_component);
					$component_value = mp_ssv_get_component_value($identifier, null);
					if (strpos($database_component, 'type="file"') !== false) {
						mp_ssv_echo_file($database_component, $title);
					} else {
						mp_ssv_echo_default($database_component, $component_value, $title);
					}
				}
			}
		}
		$content .= ob_get_clean();
		if (strpos($content,'name="password"') == false) {
			ob_start();
			?>
			<div class="mui-textfield mui-textfield--float-label">
				<input id="password" type="password" name="password" class="mui--is-empty mui--is-dirty" required>
				<label>Password</label>
			</div>
			<?php
		}
		$content .= ob_get_clean();
		if (strpos($content,'name="confirm_password"') == false) {
			ob_start();
			?>
			<div class="mui-textfield mui-textfield--float-label">
				<input id="confirm_password" type="password" name="confirm_password" class="mui--is-empty mui--is-dirty" required>
				<label>Confirm Password</label>
			</div>
			<?php
			$content .= ob_get_clean();
		}
		ob_start();
		?>
		<button class="mui-btn mui-btn--primary" type="submit" name="submit" id="submit" class="button-primary">Register</button>
		<input type="hidden" name="what-to-save" value="All"/>
	</form>
	<script type="text/javascript">
		document.addEventListener("DOMContentLoaded", function() {
				var password = document.getElementById("password");
				var confirm_password = document.getElementById("confirm_password");
				password.addEventListener("keyup", function() {
					confirm_password.pattern = this.value;
				}, false);
		}, false);
	</script>
	<?php
	$content .= ob_get_clean();
	$content = str_replace("readonly", "required", $content);
	return $content;
}

function mp_ssv_save_member_registration($what_to_save) {
	global $wpdb;
	$username = $_POST["user_login"];
	$password = $_POST["password"];
	$email = $_POST["user_email"];
	$user_id = wp_create_user($username, $password, $email);
	$current_user = get_user_by('id', $user_id);
	$table_name = $wpdb->prefix."mp_ssv_frontend_members_fields";
	$tabs = $wpdb->get_results("SELECT * FROM $table_name WHERE component = '[tab]'");
	for ($i = 0; $i < count($tabs); $i++) {
		$tab = json_decode(json_encode($tabs[$i]),true);
		$tab_title = stripslashes($tab["title"]);
		if ($what_to_save == "All") {
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
					} else if ($is_role) {
						if (isset($_POST[$identifier])) {
							update_user_meta($current_user->ID, $identifier, 1);
							$current_user->add_role($identifier);
						} else {
							update_user_meta($current_user->ID, $identifier, 0);
							$current_user->remove_role($identifier);
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
	//Register at MailChimp
}
?>
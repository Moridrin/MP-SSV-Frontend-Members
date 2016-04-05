<?php
function mp_ssv_register_page_setup($content) {
	global $post;
	if ($post->post_name != 'register') {
		return $content;
	} else if (strpos($content, '[mp-ssv-frontend-members-register]') === false) {
		return $content;
	}
	if (isset($_POST['what-to-save'])) {
		if ($_POST['g-recaptcha-response'] == "") {
			$content = "";
			?>
			My computer is thinking that you are a computer. If his isn't so please contact the webmaster.
			This ofthen happens when the recaptcha is not filled in correctly. Make sure that you do before submitting.
			<?php
		} else {
			$url = 'https://www.google.com/recaptcha/api/siteverify';
			$fields = array(
				'secret' => '6LfEqhwTAAAAAFHvzq8v6JBJs8Zm9lSZw_bTfN-f',
				'response' => $_POST['g-recaptcha-response']
			);
			$fields_string = "";
			foreach($fields as $key=>$value) {
				$fields_string .= $key.'='.$value.'&';
			}
			rtrim($fields_string, '&');
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL, $url);
			curl_setopt($ch,CURLOPT_POST, count($fields));
			curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			curl_close($ch);
			if (strpos($result, '"success": true') !== false) {
				mp_ssv_save_member_registration($_POST['what-to-save']);
				mp_ssv_redirect("login?register=success");
			} else {
				$content = "";
				?>
				My computer is thinking that you are a computer. If his isn't so please contact the webmaster.
				This ofthen happens when the recaptcha is not filled in correctly. Make sure that you do before submitting.
				<?php
			}
		}
	} else {
		$content = mp_ssv_register_page_content();
	}
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
				$is_events_registrations = $database_component == "[mp-ssv-events-registrations]";
				if ($is_tab) {
					mp_ssv_echo_tab_title($title);
				} else if ($is_header) {
					mp_ssv_echo_header($title);
				} else if ($is_group) {
					mp_ssv_echo_group($database_component, $identifier, $title, null, true);
				} else if ($is_role) {
					mp_ssv_echo_role($identifier, $title, null, true);
				} else if ($is_image) {
					if (strpos($database_component, "required") !== false || get_option('mp_ssv_frontend_members_register_page') == "same_as_profile_page") {
						mp_ssv_echo_image($database_component, null, $identifier, $title, true);
					}
				} else if (!$is_events_registrations) {
					if (strpos($database_component, "required") !== false || strpos($database_component, "readonly") !== false || get_option('mp_ssv_frontend_members_register_page') == "same_as_profile_page") {
						$identifier = mp_ssv_get_identifier($database_component);
						$component_value = mp_ssv_get_component_value($identifier, null);
						if (strpos($database_component, 'type="file"') !== false) {
							mp_ssv_echo_file($database_component, $title);
						} else {
							mp_ssv_echo_default($database_component, $component_value, $title, true);
						}
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
		<div class="g-recaptcha" data-sitekey="6LfEqhwTAAAAAFRdK9eDpaof1DEDGMFNWYmIbNEr"></div>
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
	if (!isset($_POST['what-to-save'])) {
		return;
	}
	global $wpdb;
	$member = array();
	$merge_fields = array('FNAME' => $_POST["first_name"], 'LNAME' => $_POST["last_name"]);
	$table_name = $wpdb->prefix."mp_ssv_mailchimp_merge_fields";
	$merge_fields_to_sync = $wpdb->get_results("SELECT * FROM $table_name");
	$table_name = $wpdb->prefix."mp_ssv_frontend_members_fields";
	$tabs = $wpdb->get_results("SELECT * FROM $table_name WHERE component = '[tab]'");
	
	$username = $_POST["user_login"];
	$password = $_POST["password"];
	$email = $_POST["user_email"];
	$user_id = wp_create_user($username, $password, $email);
	$current_user = get_user_by('id', $user_id);
	for ($i = 0; $i < count($tabs); $i++) {
		$tab = json_decode(json_encode($tabs[$i]),true);
		$tab_title = stripslashes($tab["title"]);
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
					$old_role = str_replace(";[role]", "", get_user_meta($user_id, "group_".$group, true));
					if (strpos($group_value, ";[role]") !== false) {
						$group_value = str_replace(";[role]", "", $group_value);
						$group_value = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '_', str_replace(" ", "_", $group_value)));
						$current_user->remove_role($old_role);
						$current_user->add_role($group_value);
						update_user_meta($user_id, "group_".$group, $group_value.";[role]");
					} else {
						$group_value = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '_', str_replace(" ", "_", $group_value)));
						update_user_meta($user_id, "group_".$group, $group_value);
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
						update_user_meta($user_id, $identifier, 1);
						$current_user->add_role($identifier);
					} else {
						update_user_meta($user_id, $identifier, 0);
						$current_user->remove_role($identifier);
					}
					foreach ($merge_fields_to_sync as $row) {
						$row = json_decode(json_encode($row),true);
						if (in_array($identifier, $row)) {
							$mailchimp_tag = $row["mailchimp_tag"];
							$merge_fields[$mailchimp_tag] = get_user_meta($user_id, $identifier, true);
						}
					}
				} else if ($is_image) {
					if ( ! function_exists( 'wp_handle_upload' ) ) {
						require_once( ABSPATH . 'wp-admin/includes/file.php' );
					}
					$file_location = wp_handle_upload($_FILES[$identifier], array('test_form' => FALSE));
					if ($file_location && !isset($file_location['error'])) {
						update_user_meta($user_id, $identifier, $file_location["url"]);	
						update_user_meta($user_id, $identifier."_path", $file_location["file"]);	
					}
				} else if (($database_component) != "" && strpos($database_component, "name=\"") !== false && strpos($database_component, "readonly") == false) {
					if (strpos($database_component, 'type="file"') !== false) {
						if ( ! function_exists( 'wp_handle_upload' ) ) {
							require_once( ABSPATH . 'wp-admin/includes/file.php' );
						}
						$file_location = wp_handle_upload($_FILES[$identifier], array('test_form' => FALSE));
						if ($file_location && !isset($file_location['error'])) {
							update_user_meta($user_id, $identifier, $file_location["url"]);	
							update_user_meta($user_id, $identifier."_path", $file_location["file"]);	
						}
					} else {
						$identifier = preg_replace("/.*name=\"/","",stripslashes($database_component));
						$identifier = preg_replace("/\".*/","",$identifier);
						$identifier = strtolower($identifier);
						update_user_meta($user_id, $identifier, $_POST[$identifier]);	
					}
					foreach ($merge_fields_to_sync as $row) {
						$row = json_decode(json_encode($row),true);
						if (in_array($identifier, $row)) {
							$mailchimp_tag = $row["mailchimp_tag"];
							$merge_fields[$mailchimp_tag] = get_user_meta($user_id, $identifier, true);
						}
					}
				}
			}
		}
	}
	if (!is_plugin_active('user-role-editor/user-role-editor.php')) {
		if (get_option("mp_ssv_frontend_members_show_admin_bar_front") == "true") {
			update_user_meta($user_id, "show_admin_bar_front", "true");
		} else {
			update_user_meta($user_id, "show_admin_bar_front", "false");
		}
	}
	update_user_meta($user_id, 'display_name', get_user_meta($user_id, "first_name", true)." ".get_user_meta($user_id, "last_name", true));
	$member["email_address"] = $email;
	$member["status"] = "subscribed";
	$member["merge_fields"] = $merge_fields;
	mp_ssv_subscribe_mailchimp_member($member);
	$to = $current_user->user_email;
	$subject = "Registration All Terrain";
	$message = "Dear ".$current_user->display_name.",\nWe're happy you've registered at our site. We also use the site to manage subscriptions to our mailing lists so if you would like to subscribe or unsubscribe, you can do so on your profile page.";
	wp_mail($to, $subject, $message);
	$to = "webmaster@moridrin.com";
	$subject = "New User Registration";
	$message = "Hello,\nA new user has registered on the site:";
	ob_start();
	print_r($member);
	$message .= ob_get_clean();
	wp_mail($to, $subject, $message);
}

if (!function_exists("mp_ssv_subscribe_mailchimp_member")) {
	function mp_ssv_subscribe_mailchimp_member($member) {
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
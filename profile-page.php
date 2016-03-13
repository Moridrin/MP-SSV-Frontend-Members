<?php
function mp_ssv_profile_page_setup($content) {
	global $post;
	if ($post->post_name != 'profile') {
		return $content;
	} else if (strpos($content, '[mp-ssv-frontend-members-profile]') === false) {
		return $content;
	}
	if (!is_user_logged_in()) {
		wp_redirect("/login");
		exit;
	} else {
		if (isset($_POST['what-to-save'])) {
			save_members_profile($_POST['what-to-save']);
		}
		$content = mp_ssv_profile_page_content();
	}
	return $content;
}
add_filter( 'the_content', 'mp_ssv_profile_page_setup' );

function mp_ssv_profile_page_content() {
	$content = "";
	if (current_theme_supports('mui')) {
		$content .= mp_ssv_profile_page_content_single_tab();
		//$content .= '<div class="mui--visible-xs-block">';
		//$content .= mp_ssv_profile_page_content_all_tabs();
		//$content .= '</div>';
	} else {
		//$content .= mp_ssv_profile_page_content_all_tabs_non_mui();
	}
	return $content;
}

include_once "profile-page-content-tabs.php";

function mp_ssv_profile_page_content_all_tabs() {
	global $wpdb;
	$current_user = wp_get_current_user();
	$table_name = $wpdb->prefix."mp_ssv_frontend_members_fields";
	$group = "";
	
	$url = (is_ssl() ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'?logout=success';
		$tabs = $wpdb->get_results("SELECT * FROM $table_name WHERE component = '[tab]'");
		$content = '';
		ob_start();
		?>
		<a class="mui-btn mui-btn--flat mui-btn--danger" href="<?php echo wp_logout_url($url); ?>" style="float: right;">Logout</a>
		<form name="members_profile_form" id="member_form" action="/profile" method="post">
		<?php
		$content .= ob_get_clean();
		for ($i = 0; $i < count($tabs); $i++) {
			$tab = json_decode(json_encode($tabs[$i]),true);
			$tab_title = stripslashes($tab["title"]);
			$identifier = preg_replace('/[^A-Za-z0-9\-]/', '_', str_replace(" ", "_", $tab_title));
			$title_value = str_replace("_", " ", $tab_title);
			$component = stripslashes($tab["component"]);
			$is_role = $component == "[role]";
			$is_header = $component == "[header]";
			$is_tab = $component == "[tab]";
			ob_start();
			$fields_in_tab = $wpdb->get_results("SELECT * FROM $table_name WHERE tab = '$tab_title'");
			foreach ($fields_in_tab as $field) {
				$field = json_decode(json_encode($field),true);
				$title = stripslashes($field["title"]);
				$identifier = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '_', str_replace(" ", "_", $title)));
				$title_value = str_replace("_", " ", $title);
				$database_component = stripslashes($field["component"]);
				if ($database_component == "checkbox" || $database_component == "radio") {
					$group = $database_component;
					$role_title = strtolower(str_replace(" ", "_", $title));
				}
				$is_role = $database_component == "[role]";
				$is_group = $database_component == "checkbox" || $database_component == "radio";
				$is_header = $database_component == "[header]";
				$is_tab = $database_component == "[tab]";
				if ($is_tab) {
				} else if ($is_header || $is_group) {
					echo '<legend>'.$title.'</legend>';
				} else if ($is_role && $group == "radio") {
					?>
					<div>
						<input id="<?php echo $identifier; ?>" type="radio" name="<?php echo "role_group_".$role_title; ?>" value="<?php echo $title; ?>" style="width: auto; margin-right: 10px;" <?php if (get_user_meta($current_user->ID, "role_group_".$role_title, true) == $title) { echo "checked"; } ?>/>
						<label for="<?php echo $identifier; ?>"><?php echo $title; ?></label>								
					</div>
					<?php
				} else if ($is_role && $group == "checkbox") {
					?>
					<div>
						<input id="<?php echo $identifier; ?>" type="checkbox" name="<?php echo $identifier; ?>" value="<?php echo $title; ?>" style="width: auto; margin-right: 10px;" <?php if(get_user_meta($current_user->ID, $identifier, true) == 1) { echo "checked"; } ?>/>
						<label for="<?php echo $identifier; ?>"><?php echo $title; ?></label>								
					</div>
					<?php
				} else {
							if (($database_component) != "" && strpos($database_component, "name=\"") !== false) {
								$identifier = preg_replace("/.*name=\"/","",stripslashes($database_component));
								$identifier = preg_replace("/\".*/","",$identifier);
								$identifier = strtolower($identifier);
							}
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
							if (strpos($database_component, 'type="file"') !== false) {
								?>
								<div class="mui-textfield">
									<?php echo $database_component; ?>
									<label><?php echo $title; ?></label>
								</div>
								<?php
							} else if (substr($database_component, 0, 4) == '<img') {
								if (($database_component) != "" && strpos($database_component, "src=\"") !== false) {
									$identifier = preg_replace("/.*src=\"/","",stripslashes($database_component));
									$identifier = preg_replace("/\".*/","",$identifier);
									$identifier = strtolower($identifier);
								}
								$component = str_replace($identifier, get_user_meta($current_user->ID, $identifier, true), $database_component);
								?>
								<div class="mui-textfield">
									<?php echo $component; ?>
								</div>
								<?php
							} else {
								$component = explode(">", $database_component)[0];
								$component .= ' value="'.$component_value.'"';
								$component .= str_replace(explode(">", $database_component)[0], "", $database_component);
								?>
								<div class="mui-textfield mui-textfield--float-label">
									<?php echo $component; ?>
									<label><?php echo $title; ?></label>
								</div>
								<?php
							}
						}
			}
			$content .= ob_get_clean();
		}
	$content .= profile_page_registrations_table_content();
	ob_start();
	?>
	<button class="mui-btn mui-btn--primary" type="submit" name="submit" id="submit" class="button-primary">Save</button>
	<input type="hidden" name="what-to-save" value="all"/>
	</form>
	<?php
	$content .= ob_get_clean();
	return $content;
}

function mp_ssv_profile_page_content_all_tabs_non_mui() {
	global $wpdb;
	$current_user = wp_get_current_user();
	$table_name = $wpdb->prefix."mp_ssv_frontend_members_fields";
	$group = "";
	
	$url = (is_ssl() ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'?logout=success';
		$tabs = $wpdb->get_results("SELECT * FROM $table_name WHERE component = '[tab]'");
		$content = '';
		ob_start();
		?>
		<a class="mui-btn mui-btn--flat mui-btn--danger" href="<?php echo wp_logout_url($url); ?>" style="float: right;">Logout</a>
		<form name="members_profile_form" id="member_form" action="/profile" method="post">
		<?php
		$content .= ob_get_clean();
		for ($i = 0; $i < count($tabs); $i++) {
			$tab = json_decode(json_encode($tabs[$i]),true);
			$tab_title = stripslashes($tab["title"]);
			$identifier = preg_replace('/[^A-Za-z0-9\-]/', '_', str_replace(" ", "_", $tab_title));
			$title_value = str_replace("_", " ", $tab_title);
			$component = stripslashes($tab["component"]);
			$is_role = $component == "[role]";
			$is_header = $component == "[header]";
			$is_tab = $component == "[tab]";
			ob_start();
			$fields_in_tab = $wpdb->get_results("SELECT * FROM $table_name WHERE tab = '$tab_title'");
			foreach ($fields_in_tab as $field) {
				$field = json_decode(json_encode($field),true);
				$title = stripslashes($field["title"]);
				$identifier = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '_', str_replace(" ", "_", $title)));
				$title_value = str_replace("_", " ", $title);
				$database_component = stripslashes($field["component"]);
				if ($database_component == "checkbox" || $database_component == "radio") {
					$group = $database_component;
					$role_title = strtolower(str_replace(" ", "_", $title));
				}
				$is_role = $database_component == "[role]";
				$is_group = $database_component == "checkbox" || $database_component == "radio";
				$is_header = $database_component == "[header]";
				$is_tab = $database_component == "[tab]";
				if ($is_tab) {
				} else if ($is_header || $is_group) {
					echo '<h2>'.$title.'</h2>';
				} else if ($is_role && $group == "radio") {
					?>
					<div>
						<label for="<?php echo $identifier; ?>"><?php echo $title; ?></label>								
						<input id="<?php echo $identifier; ?>" type="radio" name="<?php echo "role_group_".$role_title; ?>" value="<?php echo $title; ?>" style="width: auto; margin-right: 10px;" <?php if (get_user_meta($current_user->ID, "role_group_".$role_title, true) == $title) { echo "checked"; } ?>/>
					</div>
					<?php
				} else if ($is_role && $group == "checkbox") {
					?>
					<div>
						<label for="<?php echo $identifier; ?>"><?php echo $title; ?></label>								
						<input id="<?php echo $identifier; ?>" type="checkbox" name="<?php echo $identifier; ?>" value="<?php echo $title; ?>" style="width: auto; margin-right: 10px;" <?php if(get_user_meta($current_user->ID, $identifier, true) == 1) { echo "checked"; } ?>/>
					</div>
					<?php
				} else {
							if (($database_component) != "" && strpos($database_component, "name=\"") !== false) {
								$identifier = preg_replace("/.*name=\"/","",stripslashes($database_component));
								$identifier = preg_replace("/\".*/","",$identifier);
								$identifier = strtolower($identifier);
							}
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
							if (strpos($database_component, 'type="file"') !== false) {
								?>
								<div class="mui-textfield">
									<label><?php echo $title; ?></label>
									<?php echo $database_component; ?>
								</div>
								<?php
							} else if (substr($database_component, 0, 4) == '<img') {
								if (($database_component) != "" && strpos($database_component, "src=\"") !== false) {
									$identifier = preg_replace("/.*src=\"/","",stripslashes($database_component));
									$identifier = preg_replace("/\".*/","",$identifier);
									$identifier = strtolower($identifier);
								}
								$component = str_replace($identifier, get_user_meta($current_user->ID, $identifier, true), $database_component);
								?>
								<div class="mui-textfield">
									<?php echo $component; ?>
								</div>
								<?php
							} else {
								$component = explode(">", $database_component)[0];
								$component .= ' value="'.$component_value.'"';
								$component .= str_replace(explode(">", $database_component)[0], "", $database_component);
								?>
								<div class="mui-textfield mui-textfield--float-label">
									<label><?php echo $title; ?></label>
									<?php echo $component; ?>
								</div>
								<?php
							}
						}
			}
			$content .= ob_get_clean();
		}
	$content .= profile_page_registrations_table_content();
	ob_start();
	?>
	<button class="mui-btn mui-btn--primary" type="submit" name="submit" id="submit" class="button-primary">Save</button>
	<input type="hidden" name="what-to-save" value="all"/>
	</form>
	<?php
	$content .= ob_get_clean();
	return $content;
}

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
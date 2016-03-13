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
		$content = profile_page_content();
	}
	return $content;
}
add_filter( 'the_content', 'profile_page_setup' );

function mp_ssv_profile_page_content() {
	$content = "";
	if (current_theme_supports('mui')) {
		$content .= profile_page_content_single_tab();
		$content .= '<div class="mui--visible-xs-block">';
		$content .= profile_page_content_all_tabs();
		$content .= '</div>';
	} else {
		$content .= profile_page_content_all_tabs_non_mui();
	}
	return $content;
}

function mp_ssv_profile_page_content_single_tab() {
	global $wpdb;
	$current_user = wp_get_current_user();
	$table_name = $wpdb->prefix."mp_ssv_frontend_members_fields";
	$group = "";
	
	$url = (is_ssl() ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'?logout=success';
		$tabs = $wpdb->get_results("SELECT title FROM $table_name WHERE component = '[tab]'");
		$content = '<div class="mui--hidden-xs">';
		$content .= '<ul id="profile-menu" class="mui-tabs__bar mui-tabs__bar--justified">';
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
		if (function_exists('profile_page_registrations_table_content')) {
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
							echo '<legend>'.$title.'</legend>';
						} else if ($is_group) {
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
								if (strpos($group_option, ";[role]") !== false) {
									$group_option = str_replace(";[role]", "", $group_option);
									$group_option_label = $group_option;
									$group_option = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '_', str_replace(" ", "_", $group_option)));
									?><option value="<?php echo $group_option; ?>;[role]" <?php if($group_option.";[role]" == get_user_meta($current_user->ID, "group_".$identifier, true)) { echo "selected"; } ?>><?php echo $group_option_label; ?></option><?php
								} else {
									$group_option_label = $group_option;
									$group_option = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '_', str_replace(" ", "_", $group_option)));
									?><option value="<?php echo $group_option; ?>" <?php if($group_option."" == get_user_meta($current_user->ID, "group_".$identifier, true)) { echo "selected"; } ?>><?php echo $group_option_label; ?></option><?php
								}
							}
							if ($database_component == "select") {
								echo '</select>';
								echo '<label for="group_'.$identifier.'">'.$title.'</label>';
								echo '</div>';
							} else {
								
							}
						} else if ($is_role) {
							?>
							<div>
								<input id="<?php echo $identifier; ?>" type="checkbox" name="<?php echo $identifier; ?>" value="<?php echo $title; ?>" style="width: auto; margin-right: 10px;" <?php if(get_user_meta($current_user->ID, $identifier, true) == 1) { echo "checked"; } ?>/>
								<label for="<?php echo $identifier; ?>"><?php echo $title; ?></label>
							</div>
							<?php
						} else if ($is_image) {
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
								<div class="mui-textfield <?php if (strpos($component, 'type="date"') == false) { echo "mui-textfield--float-label"; } ?>">
									<?php echo $component; ?>
									<label><?php echo $title; ?></label>
								</div>
								<?php
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
			<?php echo profile_page_registrations_table_content(); ?>
			<button class="mui-btn mui-btn--primary" type="submit" name="submit" id="submit" class="button-primary">Save</button>
			<input type="hidden" name="what-to-save" value="<?php echo $tab_title; ?>"/>
		</form>
	</div>
	</div>
	<?php
	$content .= ob_get_clean();
	return $content;
}

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
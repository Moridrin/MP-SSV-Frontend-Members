<?php
function mp_ssv_profile_page_content_tabs() {
	global $wpdb;
	global $wp_roles;
	$can_edit = true;
	$user = null;
	if (isset($_GET['user_id'])) {
		$user = get_user_by('id', $_GET['user_id']);
	} else {
		$user = wp_get_current_user();
	}
	if ($user != wp_get_current_user() && !current_user_can('edit_user')) {
		$can_edit = false;
	}
	$table_fields = $wpdb->prefix."mp_ssv_frontend_members_fields";
	$table_field_meta = $wpdb->prefix."mp_ssv_frontend_members_field_meta";

	$url = (is_ssl() ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'?logout=success';
	$tabs = json_decode(json_encode($wpdb->get_results("SELECT * FROM $table_fields WHERE field_type = 'tab' ORDER BY field_index ASC;")), true);
	$content = '<ul id="profile-menu" class="mui-tabs__bar mui-tabs__bar--justified">';
	for ($i = 0; $i < count($tabs); $i++) {
		$tab = $tabs[$i];
		$tab_title = $tab["field_title"];
		$tab_identifier = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '_', str_replace(" ", "_", $tab_title)));
		$li_class = "";
		if ($i == 0) { $li_class = ' class="mui--is-active"'; }
		$content .= '<li'.$li_class.'><a class="mui-btn mui-btn--flat" data-mui-toggle="tab" data-mui-controls="pane-'.$tab_identifier.'">'.$tab_title.'</a></li>';
	}
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if (is_plugin_active('mp-ssv-events/mp-ssv-events.php') && get_option('mp_ssv_show_registrations_in_profile') == 'true') {
		$content .= '<li><a class="mui-btn mui-btn--flat" data-mui-toggle="tab" data-mui-controls="pane-registrations">Registrations</a></li>';
	}
	if (!isset($_GET['user_id'])) {
		$content .= '<li><a class="mui-btn mui-btn--flat mui-btn--danger" href="'.wp_logout_url($url).'">Logout</a></li>';
	}
	$content .= "</ul>";
	ob_start();
	for ($i = 0; $i < count($tabs); $i++) {
		$tab_title = $tabs[$i]["field_title"];
		$tab_identifier = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '_', str_replace(" ", "_", $tab_title)));
		?>
		<div class="mui-tabs__pane<?php if ($i == 0) { echo " mui--is-active"; } ?>" id="pane-<?php echo $tab_identifier; ?>">
			<form name="members_profile_form" id="member_<?php echo $tab_title; ?>_form" action="/profile" method="post" enctype="multipart/form-data">
				<?php
				$sql = "SELECT * ";
				$sql.= "FROM $table_fields ";
				$sql.= "WHERE field_index > ".$tabs[$i]["field_index"]." ";
				if ($i < count($tabs) - 1) {
					$sql.= "AND field_index < ".$tabs[$i+1]["field_index"]." ";
				}
				$sql.= "ORDER BY field_index ASC;";
				$components = json_decode(json_encode($wpdb->get_results($sql)), true);
				foreach($components as $component) {
					$id = $component["id"];
					$type = $component["field_type"];
					$title = $component["field_title"];
					if ($type == "input") {
						$input_type = mp_ssv_get_field_meta($id, "input_type");
						switch ($input_type) {
							case "text_group_select":
								$name = mp_ssv_get_field_meta($id, "name");
								$required = mp_ssv_get_field_meta($id, "required");
								$display = mp_ssv_get_field_meta($id, "display");
								$options = mp_ssv_get_group_fields($id);
								?>
								<div class="mui-select mui-textfield">
									<select id="<?php echo $id; ?>" name="<?php echo $name; ?>">
										<?php
										foreach ($options as $option) {
											?>
											<option value="<?php echo $option["value"]; ?>" <?php if ($option["value"] == mp_ssv_get_user_meta($user->ID, $name)) { echo "selected"; } ?>><?php echo $option["value"]; ?></option>
											<?php
										}
										?>
									</select>
									<label for="<?php echo $id; ?>"><?php echo $title; ?></label>
								</div>
								<?php
								break;
							case "role_group_select":
								$name = mp_ssv_get_field_meta($id, "name");
								$required = mp_ssv_get_field_meta($id, "required");
								$display = mp_ssv_get_field_meta($id, "display");
								$options = mp_ssv_get_group_fields($id);
								$roles = get_userdata($user->ID)->roles;
								?>
								<div class="mui-select mui-textfield">
									<select id="<?php echo $id; ?>" name="<?php echo $name; ?>">
										<?php
										foreach ($options as $option) {
											$role = $wp_roles->roles[$option["value"]]['name'];
											?>
											<option value="<?php echo $option["value"]; ?>" <?php if ($option["value"] == array_values($roles)[0]) { echo "selected"; } ?>><?php echo $role; ?></option>
											<?php
										}
										?>
									</select>
									<label for="<?php echo $id; ?>"><?php echo $title; ?></label>
								</div>
								<?php
								break;
							case "text_checkbox":
								$name = mp_ssv_get_field_meta($id, "name");
								$required = mp_ssv_get_field_meta($id, "required");
								$display = mp_ssv_get_field_meta($id, "display");
								?>
								<div class="mui-checkbox">
									<label>
										<input type="hidden" id="<?php echo $id; ?>_reset" name="<?php echo $name; ?>_reset" value="no"/>
										<input type="checkbox" id="<?php echo $id; ?>" name="<?php echo $name; ?>" value="yes" <?php if (mp_ssv_get_user_meta($user->ID, $name) == "yes") { echo "checked"; } ?> <?php if ($required == "yes") { echo "required"; } ?> <?php echo $display; ?>/>
										<?php echo $title; ?>
									</label>
								</div>
								<?php
								break;
							case "role_checkbox":
								$display = mp_ssv_get_field_meta($id, "display");
								$role = mp_ssv_get_field_meta($id, "role");
								?>
								<div class="mui-checkbox">
									<label>
										<input type="hidden" id="<?php echo $id; ?>_reset" name="<?php echo $role; ?>_role_reset" value="no"/>
										<input type="checkbox" id="<?php echo $id; ?>" name="<?php echo $role; ?>_role" value="yes" <?php if (mp_ssv_get_user_meta($user->ID, $role."_role")) { echo "checked"; } ?> <?php echo $display; ?>/>
										<?php echo $title; ?>
									</label>
								</div>
								<?php
								break;
							case "image":
								$name = mp_ssv_get_field_meta($id, "name");
								$required = mp_ssv_get_field_meta($id, "required");
								$preview = mp_ssv_get_field_meta($id, "preview");
								break;
							case "text":
								$name = mp_ssv_get_field_meta($id, "name");
								$required = mp_ssv_get_field_meta($id, "required");
								$display = mp_ssv_get_field_meta($id, "display");
								$placeholder = mp_ssv_get_field_meta($id, "placeholder");
								$value = mp_ssv_get_user_meta($user->ID, $name);
								?>
								<div class="mui-textfield <?php if ($placeholder == "") { echo "mui-textfield--float-label"; } ?>">
									<input type="text" id="<?php echo $id; ?>" name="<?php echo $name; ?>" value="<?php echo $value; ?>" <?php echo $display; ?> placeholder="<?php echo $placeholder; ?>" <?php if ($required == "yes") { echo "required"; } ?>/>
									<label><?php echo $title; ?></label>
								</div>
								<?php
								break;
							default:
								$name = mp_ssv_get_field_meta($id, "name");
								$input_type = mp_ssv_get_field_meta($id, "input_type_custom");
								$required = mp_ssv_get_field_meta($id, "required");
								$display = mp_ssv_get_field_meta($id, "display");
								$placeholder = mp_ssv_get_field_meta($id, "placeholder");
								$value = mp_ssv_get_user_meta($user->ID, $name);
								?>
								<div class="mui-textfield">
									<input type="<?php echo $input_type; ?>" id="<?php echo $id; ?>" name="<?php echo $name; ?>" value="<?php echo $value; ?>" <?php echo $display; ?> placeholder="<?php echo $placeholder; ?>" <?php if ($required == "yes") { echo "required"; } ?>/>
									<label><?php echo $title; ?></label>
								</div>
								<?php
								break;
						}
					} else if ($type == "header") {
						if ($title != "[mp-ssv-events-registrations]") {
							echo "<h1>".$title."</h1>";
						} else {
							echo mp_ssv_profile_page_registrations_table_content();
						}
					}
				}
				if ($can_edit) {
					?><button class="mui-btn mui-btn--primary" type="submit" name="submit" id="submit" class="button-primary">Save</button><?php
				}
				?>
				<input type="hidden" name="what-to-save" value="<?php echo $tab_identifier; ?>"/>
			</form>
		</div>
		<?php
		if (isset($_POST['what-to-save'])) {
			if ($_POST['what-to-save'] == $tab_identifier) {
				$content .= '<script>$(document).ready(function mp_ssv_load() { mui.tabs.activate("pane-'.$tab_identifier.'"); });</script>';
			}
		}
	}
	$content .= ob_get_clean();
	return $content;
}

function mp_ssv_echo_frontend_checkbox($id, $name, $selected, $is_role = false) {
	?>
	<div class="mui-checkbox">
		<input type="hidden" id="<?php echo $id."_reset"; ?>" name="<?php echo $name."_reset"; ?>" value="no"/>
		<label><input type="checkbox" id="<?php echo $id; ?>" name="<?php echo $name; ?>" value="yes" <?php if (mp_ssv_get_user_meta($user->ID, $name) == "yes") { echo "checked"; } ?>><?php echo $option["value"]; ?></label>
	</div>
	<?php
}
?>
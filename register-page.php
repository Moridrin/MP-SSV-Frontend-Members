<?php
function register_page_setup($content) {
	global $post;
	/* Return */
	if ($post->post_name != 'register') {
		return $content;
	} else if (strpos($content, '[mp-ssv-frontend-members-register]') === false) {
		return $content;
	}
	if (isset($_POST['what-to-save'])) {
		save_member_registration($_POST['what-to-save']);
	}
	$content = register_page_content();
	return $content;
}
add_filter( 'the_content', 'register_page_setup' );

function register_page_content() {
	global $wpdb;
	$user = wp_get_current_user();
	$content = "";
	$table_name = $wpdb->prefix."mp_ssv_frontend_members_fields";
	$role_group = "";
	
	if (current_theme_supports('mui')) {
		$tabs = $wpdb->get_results("SELECT * FROM $table_name WHERE component = '[tab]'");
		ob_start(); ?>
	<form name="members_registration_form" id="member_general_form" action="/register" method="post">
		<?php
			for ($i = 0; $i < count($tabs); $i++) {
				$tab = json_decode(json_encode($tabs[$i]),true);
				$tab_title = stripslashes($tab["title"]);
				$identifier = preg_replace('/[^A-Za-z0-9\-]/', '_', str_replace(" ", "_", $tab_title));
				$title_value = str_replace("_", " ", $tab_title);
				$component = stripslashes($tab["component"]);
				$is_role = $component == "[role]";
				$is_header = $component == "[header]";
				$is_tab = $component == "[tab]";
				echo "<h1>".$tab_title."</h1>";
				$fields_in_tab = $wpdb->get_results("SELECT * FROM $table_name WHERE tab = '$tab_title'");
				foreach ($fields_in_tab as $field) {
					$field = json_decode(json_encode($field),true);
					$title = stripslashes($field["title"]);
					$identifier = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '_', str_replace(" ", "_", $title)));
					$title_value = str_replace("_", " ", $title);
					$database_component = stripslashes($field["component"]);
					if ($database_component == "multi_select" || $database_component == "radio") {
						$role_group = $database_component;
						$role_title = strtolower(str_replace(" ", "_", $title));
					}
					$is_role = $database_component == "[role]";
					$is_role_group = $database_component == "multi_select" || $database_component == "radio";
					$is_header = $database_component == "[header]";
					$is_tab = $database_component == "[tab]";
					$is_image = strpos($database_component, "[image]") !== false;
					if ($is_tab) {
					} else if ($is_header || $is_role_group) {
						echo '<legend>'.$title.'</legend>';
					} else if ($is_role && $role_group == "radio") {
						?>
			<div>
				<input id="<?php echo $identifier; ?>" type="radio" name="<?php echo " role_group_".$role_title; ?>" value="<?php echo $title; ?>" style="width: auto; margin-right: 10px;" />
				<label for="<?php echo $identifier; ?>"><?php echo $title; ?></label>
			</div>
			<?php
					} else if ($is_role && $role_group == "multi_select") {
						?>
				<div>
					<input id="<?php echo $identifier; ?>" type="checkbox" name="<?php echo $identifier; ?>" value="<?php echo $title; ?>" style="width: auto; margin-right: 10px;" />
					<label for="<?php echo $identifier; ?>"><?php echo $title; ?></label>
				</div>
				<?php
					} else if ($is_image) {
						$required = strpos($database_component, "required") !== false;
						?>
						<div class="mui-textfield">
							<input id="<?php echo $identifier; ?>" type="file" name="<?php echo $identifier; ?>" accept="image/*" <?php if($required) { echo "required"; } ?>/>
							<label for="<?php echo $identifier; ?>"><?php echo $title; ?></label>
						</div>
						<?php
					} else {
						if (($database_component) != "" && strpos($database_component, "name=\"") !== false) {
							$identifier = preg_replace("/.*name=\"/","",stripslashes($database_component));
							$identifier = preg_replace("/\".*/","",$identifier);
							$identifier = strtolower($identifier);
						}
						$component = str_replace("readonly", "required", $database_component);
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
		<button class="mui-btn mui-btn--primary" type="submit" name="submit" id="submit" class="button-primary">Save</button>
		<input type="hidden" name="what-to-save" value="All" />
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
	}
	return $content;
}

function save_member_registration($what_to_save) {
	global $wpdb;
	$username = $_POST["user_login"];
	$password = $_POST["password"];
	$email = $_POST["user_email"];
	$user_id = wp_create_user($username, $password, $email);
	$user = get_user_by("id", $user_id);
	$table_name = $wpdb->prefix."mp_ssv_frontend_members_fields";
	$tabs = $wpdb->get_results("SELECT * FROM $table_name WHERE component = '[tab]'");
	for ($i = 0; $i < count($tabs); $i++) {
		$tab = json_decode(json_encode($tabs[$i]),true);
		$tab_title = stripslashes($tab["title"]);
		if ($what_to_save == $tab_title || $what_to_save == "All") {
			$role_group = "";
			$role_title = "";
			$fields_in_tab = $wpdb->get_results("SELECT * FROM $table_name WHERE tab = '$tab_title'");
			foreach ($fields_in_tab as $field) {
				$field = json_decode(json_encode($field),true);
				$title = stripslashes($field["title"]);
				$identifier = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '_', str_replace(" ", "_", $title)));
				$title_value = str_replace("_", " ", $title);
				$database_component = stripslashes($field["component"]);
				$is_role_group = $database_component == "multi_select" || $database_component == "radio";
				$is_role = $database_component == "[role]";
				$is_header = $database_component == "[header]";
				$is_tab = $database_component == "[tab]";
				if ($database_component == "multi_select" || $database_component == "radio") {
					$role_group = $database_component;
					$role_title = strtolower(str_replace(" ", "_", $title));
				}
				if (!$is_header && !$is_tab) {
					if ($is_role_group && $role_group == "radio") {
						update_user_meta($user_id, "role_group_".$role_title, $_POST["role_group_".$role_title]);
					} else if ($is_role && $role_group == "multi_select") {
						if (isset($_POST[$identifier])) {
							update_user_meta($user_id, $identifier, 1);
							$user->add_role($identifier);
						} else {
							update_user_meta($user_id, $identifier, 0);
							$user->remove_role($identifier);
						}
					} else if (($database_component) != "" && strpos($database_component, "name=\"") !== false && strpos($database_component, "readonly") == false) {
						$identifier = preg_replace("/.*name=\"/","",stripslashes($database_component));
						$identifier = preg_replace("/\".*/","",$identifier);
						$identifier = strtolower($identifier);
						update_user_meta($user_id, $identifier, $_POST[$identifier]);
					}
				}
			}
		}
	}
}
?>
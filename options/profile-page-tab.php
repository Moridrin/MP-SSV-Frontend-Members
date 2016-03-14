<form id="mp-ssv-frontend-members-options" name="mp-ssv-frontend-members-options" method="post" action="#">
	<table id="container" style="width: 100%; border-spacing: 10px 0; margin-bottom: 20px; margin-top: 20px;">
		<tbody class="sortable">
			<tr>
				<th style="width: 20px;"></th>
				<th style="text-align: left; width: 180px;" scope="row">Title</th>
				<th style="margin: 20px; text-align: left; min-width: 200px;" scope="row">Code</th>
			</tr>
			<?php
			global $wpdb;
			$table_name = $wpdb->prefix."mp_ssv_frontend_members_fields";
			$fields = $wpdb->get_results( 
				"SELECT *
					FROM $table_name"
			);
			foreach ($fields as $field) {
				$field = json_decode(json_encode($field),true);

				$title = stripslashes($field["title"]);
				$identifier = preg_replace('/[^A-Za-z0-9\-]/', '_', str_replace(" ", "_", $title));
				$title_value = str_replace("_", " ", $title);
				$component = stripslashes($field["component"]);
				$group = "";
				$is_role = $component == "[role checkbox]";
				$is_group = $component == "select" || $component == "radio";
				$is_header = $component == "[header]";
				$is_tab = $component == "[tab]";
				$is_image = strpos($component, "[image]") !== false;

				if (($component) != "" && strpos($component, "name=\"") !== false) {
					$identifier = preg_replace("/.*name=\"/","",stripslashes($component));
					$identifier = preg_replace("/\".*/","",$identifier);
				}
				?>
				<tr id="<?php echo $identifier; ?>" style="vertical-align: top;">
					<td style="cursor: move;">
						<img style="padding-right: 15px; margin: 10px 0;" src="<?php echo plugins_url( '../images/icon-menu.svg', __FILE__ ); ?>"/>
					</td>
					<?php if ($is_tab) { ?>
						<td>
							<input type="text" id="tab_<?php echo $identifier; ?>_title" name="tab_title_option_<?php echo $identifier; ?>" value="<?php echo $title_value; ?>"/>
						</td>
						<td>
							<input type="text" value="[tab]" disabled>
							<input type="hidden" name="tab_component_option_<?php echo $identifier; ?>" value="[tab]">
							<input type="hidden" name="tab_submit_option_<?php echo $identifier; ?>">
						</td>
					<?php } else if ($is_header) { ?>
						<td>
							<input type="text" id="header_<?php echo $identifier; ?>_title" name="header_title_option_<?php echo $identifier; ?>" value="<?php echo $title_value; ?>"/>
						</td>
						<td>
							<input type="text" value="[header]" disabled>
							<input type="hidden" name="header_component_option_<?php echo $identifier; ?>" value="[header]">
							<input type="hidden" name="header_submit_option_<?php echo $identifier; ?>">
						</td>
					<?php } else if ($is_role) { ?>
						<td>
							<select id="role_<?php echo $identifier; ?>_title" name="role_title_option_<?php echo $identifier; ?>">
								<option></option>
								<?php
								$roles = get_editable_roles();
								foreach ($roles as $role_name => $role_info) { ?>
									<option value="<?php echo $role_info['name']; ?>" <?php if($title == $role_info['name']) { echo "selected"; } ?>><?php echo $role_info['name']; ?></option>
								<?php } ?>
							</select>
						</td>
						<td>
							<input type="text" value="[role checkbox]" disabled>
							<input type="hidden" name="role_component_option_<?php echo $identifier; ?>" value="[role checkbox]">
							<input type="hidden" name="role_submit_option_<?php echo $identifier; ?>">
						</td>
					<?php } else if ($is_group) { ?>
						<td>
							<input type="text" id="group_<?php echo $identifier; ?>_title" name="group_title_option_<?php echo $identifier; ?>" value="<?php echo $title_value; ?>"/>
						</td>
						<td>
							<select id="group_<?php echo $identifier; ?>_title" name="group_component_option_<?php echo $identifier; ?>">
								<option value=""></option>
								<option value="radio" <?php if($component == "radio") { echo "selected"; } ?>>Radio</option>
								<option value="select" <?php if($component == "select") { echo "selected"; } ?>>Select</option>
							</select>
							<table id="container_<?php echo $identifier; ?>" style="width: 100%; border-spacing: 10px 0; margin-bottom: 20px; margin-top: 20px;">
								<tr></tr>
								<?php
								$table_name = $wpdb->prefix."mp_ssv_frontend_members_fields_group_options";
								$group_options = $wpdb->get_results( 
									"SELECT *
										FROM $table_name"
								);
								foreach ($group_options as $group_option) {
									$group_option = json_decode(json_encode($group_option),true);
									$group_option = stripslashes($group_option["option_text"]);
									if (strpos($group_option, ";[role]") !== false) {
										$group_option = str_replace(";[role]", "", $group_option);
										$group_option_identifier = preg_replace('/[^A-Za-z0-9\-]/', '_', str_replace(" ", "_", $group_option));
										$group_option_identifier = $identifier.$group_option_identifier;
										?>
										<tr>
											<td>
												<select id="group_option_<?php echo $group_option_identifier; ?>_title" name="group_option_item_<?php echo $group_option_identifier; ?>">
													<option></option>
													<?php
													$roles = get_editable_roles();
													foreach ($roles as $role_name => $role_info) { ?>
														<option value="<?php echo $role_info['name']; ?>;[role]" <?php if($group_option == $role_info['name']) { echo "selected"; } ?>><?php echo $role_info['name']; ?></option>
													<?php } ?>
												</select>
											</td>
											<td><input type="hidden" name="<?php echo "submit_group_option_".$group_option_identifier ?>"></td>
										</tr>
										<?php
									} else {
										$group_option_identifier = preg_replace('/[^A-Za-z0-9\-]/', '_', str_replace(" ", "_", $group_option));
										$group_option_identifier = $identifier.$group_option_identifier;
										?>
										<tr>
											<td>
												<input id="groep_option_<?php echo $group_option_identifier; ?>_title" name="group_option_item_<?php echo $group_option_identifier; ?>" value="<?php echo $group_option; ?>">
											</td>
											<td><input type="hidden" name="<?php echo "submit_group_option_".$group_option_identifier ?>"></td>
										</tr>
										<?php
									}
								}
								?>
							</table>
							<button type="button" id="add_option_button" onclick="mp_ssv_add_new_option(container_<?php echo $identifier; ?>)">Add Component</button>
							<?php if (esc_attr(stripslashes(get_option('mp_ssv_frontend_members_guest_custom_roles_enabled'))) == 'true') { ?>
								<button type="button" id="add_user_role_button" onclick="mp_ssv_add_new_user_role(container_<?php echo $identifier; ?>)">Add User Role</button>
							<?php } ?>
							<input type="hidden" name="group_submit_option_<?php echo $identifier; ?>">
						</td>
					<?php } else if ($is_image) { ?>
						<td>
							<input type="text" id="image_<?php echo $identifier; ?>_title" name="image_title_option_<?php echo $identifier; ?>" value="<?php echo $title_value; ?>"/>
						</td>
						<td>
							<input type="text" value="[image]" disabled><br/>
							<input type="hidden" name="image_component_option_<?php echo $identifier; ?>" value="[image]">
							<input type="checkbox" name="is_required_option_<?php echo $identifier; ?>" <?php if (strpos($component, "required")) { echo "checked"; } ?> style="margin: 0 10px;" value="on">Required<br/>
							<input type="checkbox" name="show_preview_option_<?php echo $identifier; ?>" <?php if (strpos($component, "show_preview")) { echo "checked"; } ?> style="margin: 0 10px;" value="on">Show Preview<br/>
							<input type="hidden" name="image_submit_option_<?php echo $identifier; ?>">
						</td>
					<?php } else { ?>
						<td>
							<input type="text" id="<?php echo $identifier."_title"; ?>" name="title_option_<?php echo $identifier; ?>" value="<?php echo $title_value; ?>"/>
						</td>
						<td>
							<textarea id="<?php echo $identifier."_component"; ?>" name="component_option_<?php echo $identifier; ?>" style="width: 100%;" onkeyup="sync_preview('<?php echo $identifier; ?>')"><?php echo $component; ?></textarea>
							<input type="hidden" name="submit_option_<?php echo $identifier; ?>">
						</td>
					<?php } ?>
				</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php if (get_theme_support('mui')) { ?>
		<button type="button" id="add_tab_button" onclick="mp_ssv_add_new_tab()">Add Tab</button>
	<?php } ?>
	<button type="button" id="add_header_button" onclick="mp_ssv_add_new_header()">Add Header</button>
	<button type="button" id="add_component_button" onclick="mp_ssv_add_new_component()">Add Component</button>
	<button type="button" id="add_group_button" onclick="mp_ssv_add_new_group()">Add Group</button>
	<button type="button" id="add_image_button" onclick="mp_ssv_add_new_image()">Add Image</button>
	<?php if (esc_attr(stripslashes(get_option('mp_ssv_frontend_members_guest_custom_roles_enabled'))) == 'true') { ?>
		<button type="button" id="add_user_role_button" onclick="mp_ssv_add_new_user_role_checkbox()">Add User Role</button>
	<?php }
	submit_button();
	?>
</form>
<script src="https://code.jquery.com/jquery-2.2.0.js"></script>
<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<script>
$(function() {
	$( ".sortable" ).sortable();
	$( ".sortable" ).disableSelection();
});
</script>
<script>
function mp_ssv_add_new_tab() {
	var id = Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);
	$("#container > tbody:last-child").append(
		$('<tr id="' + id + '" style="vertical-align: top;">').append(
			$('<td style="cursor: move;">').append(
				'<img style="padding-right: 15px; margin: 10px 0;" src="<?php echo plugins_url("../images/icon-menu.svg", __FILE__); ?>"/>'
			)
		).append(
			$('<td>').append(
				'<input type="text" id="' + id + '_title" name="title_option_' + id + '"/>'
			)
		).append(
			$('<td>').append(
				'<input type="text" value="[tab]" disabled>'
			).append (
				'<input type="hidden" name="component_option_' + id + '" value="[tab]">'
			)
		).append(
			$('<td>')
		).append(
			$('<td>').append(
				'<input type="hidden" name="submit_option_' + id + '">'
			)
		)
	);
}
function mp_ssv_add_new_header() {
	var id = Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);
	$("#container > tbody:last-child").append(
		$('<tr id="' + id + '" style="vertical-align: top;">').append(
			$('<td style="cursor: move;">').append(
				'<img style="padding-right: 15px; margin: 10px 0;" src="<?php echo plugins_url("../images/icon-menu.svg", __FILE__); ?>"/>'
			)
		).append(
			$('<td>').append(
				'<input type="text" id="' + id + '_title" name="title_option_' + id + '"/>'
			)
		).append(
			$('<td>').append(
				'<input type="text" value="[header]" disabled>'
			).append (
				'<input type="hidden" name="component_option_' + id + '" value="[header]">'
			)
		).append(
			$('<td>')
		).append(
			$('<td>').append(
				'<input type="hidden" name="submit_option_' + id + '">'
			)
		)
	);
}
function mp_ssv_add_new_component() {
	var id = Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);
	$("#container > tbody:last-child").append(
		$('<tr id="' + id + '" style="vertical-align: top;">').append(
			$('<td style="cursor: move;">').append(
				'<img style="padding-right: 15px; margin: 10px 0;" src="<?php echo plugins_url("../images/icon-menu.svg", __FILE__); ?>"/>'
			)
		).append(
			$('<td>').append(
				'<input type="text" id="' + id + '_title" name="title_option_' + id + '"/>'
			)
		).append(
			$('<td>').append(
				'<textarea id="' + id + '_component" name="component_option_' + id + '" style="width: 100%;" onkeyup="sync_preview(\'' + id + '\')"><input type="text" name=""></textarea>'
			).append(
				'<input type="hidden" name="submit_option_' + id + '">'
			)
		)
	);
}
function mp_ssv_add_new_group() {
	var id = Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);
	$("#container > tbody:last-child").append(
		$('<tr id="' + id + '" style="vertical-align: top;">').append(
			$('<td style="cursor: move;">').append(
				'<img style="padding-right: 15px; margin: 10px 0;" src="<?php echo plugins_url("../images/icon-menu.svg", __FILE__); ?>"/>'
			)
		).append(
			$('<td>').append(
				'<input type="text" id="' + id + '_title" name="title_option_' + id + '"/>'
			)
		).append(
			$('<td>').append(
				$('<select id="' + id + '_title" name="component_option_' + id + '"/><br/>')
					.append('<option value=""></option>')
					.append('<option value="radio">Radio</option>')
					.append('<option value="select" selected>Select</option>')
			).append(
				$('<table id="container_' + id + '" style="width: 100%; border-spacing: 10px 0; margin-bottom: 20px; margin-top: 20px;">').append(
					'<tbody>'
				)
			).append(
				'<button type="button" id="add_option_button" onclick="mp_ssv_add_new_option(container_' + id + ')">Add Component</button>'
			).append(
				<?php if (esc_attr(stripslashes(get_option('mp_ssv_frontend_members_guest_custom_roles_enabled'))) == 'true') { ?>'<button type="button" id="add_user_role_button" onclick="mp_ssv_add_new_user_role(container_' + id + ')">Add User Role</button>'<?php } ?>
			)
		).append(
			'<td>'
		).append(
			'<td>'
		).append(
			$('<td>').append(
				'<input type="hidden" name="submit_option_' + id + '">'
			)
		)
	);
}
function mp_ssv_add_new_image() {
	var id = Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);
	$("#container > tbody:last-child").append(
		$('<tr id="' + id + '" style="vertical-align: top;">').append(
			$('<td style="cursor: move;">').append(
				'<img style="padding-right: 15px; margin: 10px 0;" src="<?php echo plugins_url("../images/icon-menu.svg", __FILE__); ?>"/>'
			)
		).append(
			$('<td>').append(
				'<input type="text" id="' + id + '_title" name="title_option_' + id + '"/>'
			)
		).append(
			$('<td>').append(
				'<input type="text" value="[image]" disabled>'
			).append (
				'<input type="hidden" name="component_option_' + id + '" value="[image]">'
			).append (
				'<input type="checkbox" id="is_required_option_' + id + '" name="is_required_option_' + id + '" style="margin: 0 10px;" value="on"><label for="is_required_option_' + id + '">required</label>'
			).append (
				'<input type="checkbox" id="show_preview_option_' + id + '" name="show_preview_option_' + id + '" style="margin: 0 10px;" value="on"><label for="show_preview_option_' + id + '">Show Preview</label>'
			)
		).append(
			'<td>'
		).append(
			'<td>'
		).append(
			$('<td>').append(
				'<input type="hidden" name="submit_option_' + id + '">'
			)
		)
	);
}
function mp_ssv_add_new_user_role_checkbox() {
	var id = Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);
	$("#" + container.id + " > tbody:last-child").append(
		$('<tr id="' + container.id + "_" + id + '" style="vertical-align: top;">').append(
				$('<td style="cursor: move;">').append(
					'<img style="padding-right: 15px; margin: 10px 0;" src="<?php echo plugins_url("../images/icon-menu.svg", __FILE__); ?>"/>'
				)
		).append(
			$('<td>').append(
				$('<select id="' + container.id + "_" + id + '_title" name="title_option_' + container.id + "_" + id + '"/>')
					.append('<option></option>')
					<?php
					$roles = get_editable_roles();
					foreach ($roles as $role_name => $role_info) { ?>
						.append('<option value="<?php echo $role_info['name']; ?>"><?php echo $role_info['name']; ?></option>')
					<?php } ?>
			)
		).append(
				$('<td>').append(
					'<input type="text" value="[role checkbox]" disabled>'
				).append (
					'<input type="hidden" name="component_option_' + id + '" value="[role checkbox]">'
				)
		).append(
			$('<td>').append(
				'<input type="hidden" name="submit_option_' + container.id + "_" + id + '">'
			)
		)
	);
}
function mp_ssv_add_new_option(container) {
	var id = Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);
	$("#" + container.id + " > tbody:last-child").append(
		$('<tr id="' + container.id + "_" + id + '" style="vertical-align: top;">').append(
			$('<td>').append(
				'<input type="text" id="' + container.id + "_" + id + '_option" name="group_option_item_' + container.id + "_" + id + '"/>'
			)
		).append(
			$('<td>').append(
				'<input type="hidden" name="submit_group_option_' + container.id + "_" + id + '">'
			)
		)
	);
}
function mp_ssv_add_new_user_role(container) {
	var id = Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);
	$("#" + container.id + " > tbody:last-child").append(
		$('<tr id="' + container.id + "_" + id + '" style="vertical-align: top;">').append(
			$('<td>').append(
				$('<select id="' + container.id + "_" + id + '_title" name="group_option_item_' + container.id + "_" + id + '"/>')
					.append('<option></option>')
					<?php
					$roles = get_editable_roles();
					foreach ($roles as $role_name => $role_info) { ?>
						.append('<option value="<?php echo $role_info['name']; ?>;[role]"><?php echo $role_info['name']; ?></option>')
					<?php } ?>
			)
		).append(
			$('<td>').append(
				'<input type="hidden" name="submit_group_option_' + container.id + "_" + id + '">'
			)
		)
	);
}
</script>
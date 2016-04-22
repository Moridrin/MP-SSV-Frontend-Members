<form id="mp-ssv-frontend-members-options" name="mp-ssv-frontend-members-options" method="post" action="#">
	<table id="container" style="width: 100%; border-spacing: 10px 0; margin-bottom: 20px; margin-top: 20px; border-collapse: collapse;">
		<tbody class="sortable">
			<?php
			global $wpdb;
			$table_name = $wpdb->prefix."mp_ssv_frontend_members_fields";
			$fields = $wpdb->get_results( 
				"SELECT *
					FROM $table_name
					WHERE field_type != 'group_option'
					ORDER BY field_index ASC;"
			);
			foreach ($fields as $field) {
				$field = json_decode(json_encode($field), true);
				$id = $field["id"];
				?>
				<tr id="<?php echo $id; ?>" style="vertical-align: top; border-bottom: 1px solid gray; border-top: 1px solid gray;">
					<?php
					echo mp_ssv_td(mp_ssv_draggable_icon(), true);
					$title = $field["field_title"];
					echo mp_ssv_td(mp_ssv_text_input("Field Title", $id, $title));
					$type = $field["field_type"];
					if (get_theme_support('mui')) {
						echo mp_ssv_td(mp_ssv_select("Field Type", $id, $type, array("Tab", "Header", "Input"), array('onchange="mp_ssv_type_changed(\''.$id.'\')"')));
					} else {
						echo mp_ssv_td(mp_ssv_select("Field Type", $id, $type, array("Header", "Input")));
					}
					if ($type == "input") {
						$input_type = mp_ssv_get_field_meta($id, "input_type");
						echo mp_ssv_td(mp_ssv_select("Input Type", $id, $input_type, array("Text", "Text Group Select", "Role Group Select", "Text Checkbox", "Role Checkbox", "Image"), array('onchange="mp_ssv_input_type_changed(\''.$id.'\')"'), true));
						switch ($input_type) {
							case "text_group_select":
								$name = mp_ssv_get_field_meta($id, "name");
								echo mp_ssv_td(mp_ssv_text_input("Name", $id, $name));
								echo mp_ssv_td('<div class="'.$id.'_empty"></div>');
								$display = mp_ssv_get_field_meta($id, "display");
								echo mp_ssv_td(mp_ssv_select("Display", $id, $display, array("Normal", "ReadOnly", "Disabled")));
								$options = mp_ssv_get_group_fields($id);
								echo mp_ssv_td(mp_ssv_options($id, $options, "text"));
								break;
							case "role_group_select":
								$name = mp_ssv_get_field_meta($id, "name");
								echo mp_ssv_td(mp_ssv_text_input("Name", $id, $name));
								echo mp_ssv_td('<div class="'.$id.'_empty"></div>');
								$display = mp_ssv_get_field_meta($id, "display");
								echo mp_ssv_td(mp_ssv_select("Display", $id, $display, array("Normal", "ReadOnly", "Disabled")));
								$options = mp_ssv_get_group_fields($id);
								echo mp_ssv_td(mp_ssv_options($id, $options, "role"));
								break;
							case "text_checkbox":
								$name = mp_ssv_get_field_meta($id, "name");
								echo mp_ssv_td(mp_ssv_text_input("Name", $id, $name));
								$required = mp_ssv_get_field_meta($id, "required");
								echo mp_ssv_td(mp_ssv_checkbox("Required", $id, $required));
								$display = mp_ssv_get_field_meta($id, "display");
								echo mp_ssv_td(mp_ssv_select("Display", $id, $display, array("Normal", "ReadOnly", "Disabled")));
								break;
							case "role_checkbox":
								echo '<td style="vertical-align: middle; cursor: move;"><div class="'.$id.'_empty"></div></td>';
								echo '<td style="vertical-align: middle; cursor: move;"><div class="'.$id.'_empty"></div></td>';
								$display = mp_ssv_get_field_meta($id, "display");
								echo mp_ssv_td(mp_ssv_select("Display", $id, $display, array("Normal", "ReadOnly", "Disabled")));
								$role = mp_ssv_get_field_meta($id, "role");
								echo mp_ssv_td(mp_ssv_role_select($id, "Role", $role));
								break;
							case "image":
								$name = mp_ssv_get_field_meta($id, "name");
								echo mp_ssv_td(mp_ssv_text_input("Name", $id, $name));
								$required = mp_ssv_get_field_meta($id, "required");
								echo mp_ssv_td(mp_ssv_checkbox("Required", $id, $required));
								$preview = mp_ssv_get_field_meta($id, "preview");
								echo mp_ssv_td(mp_ssv_checkbox("Preview", $id, $preview));
								echo '<td style="vertical-align: middle; cursor: move;"><div class="'.$id.'_empty"></div></td>';
								break;
							case "text":
								$name = mp_ssv_get_field_meta($id, "name");
								echo mp_ssv_td(mp_ssv_text_input("Name", $id, $name));
								$required = mp_ssv_get_field_meta($id, "required");
								echo mp_ssv_td(mp_ssv_checkbox("Required", $id, $required));
								$display = mp_ssv_get_field_meta($id, "display");
								echo mp_ssv_td(mp_ssv_select("Display", $id, $display, array("Normal", "ReadOnly", "Disabled")));
								$placeholder = mp_ssv_get_field_meta($id, "placeholder");
								echo mp_ssv_td(mp_ssv_text_input("Placeholder", $id, $placeholder));
								break;
							default:
								$name = mp_ssv_get_field_meta($id, "name");
								echo mp_ssv_td(mp_ssv_text_input("Name", $id, $name));
								$required = mp_ssv_get_field_meta($id, "required");
								echo mp_ssv_td(mp_ssv_checkbox("Required", $id, $required));
								$display = mp_ssv_get_field_meta($id, "display");
								echo mp_ssv_td(mp_ssv_select("Display", $id, $display, array("Normal", "ReadOnly", "Disabled")));
								$placeholder = mp_ssv_get_field_meta($id, "placeholder");
								echo mp_ssv_td(mp_ssv_text_input("Placeholder", $id, $placeholder));
								break;
						}
					} else {
						echo mp_ssv_td('<div class="'.$id.'_empty"></div>');
						echo mp_ssv_td('<div class="'.$id.'_empty"></div>');
						echo mp_ssv_td('<div class="'.$id.'_empty"></div>');
						echo mp_ssv_td('<div class="'.$id.'_empty"></div>');
						echo mp_ssv_td('<div class="'.$id.'_empty"></div>');
					}
					?>
				</tr>
			<?php } ?>
		</tbody>
	</table>
	<button type="button" id="add_field_button" onclick="mp_ssv_add_new_field()">Add Field</button>
	<?php
	submit_button();
	?>
</form>
<script src="https://code.jquery.com/jquery-2.2.0.js"></script>
<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<script>
$(function() {
	$(".sortable").sortable();
	$(".sortable").disableSelection();
});
</script>
<script>
	<?php
	global $wpdb;
	require_once(ABSPATH.'wp-admin/includes/upgrade.php');
	$table = $wpdb->prefix."mp_ssv_frontend_members_fields";
	$column = $wpdb->get_col("SELECT id FROM $table ORDER BY id DESC LIMIT 0 , 1" );
	if (count($column) > 0 ) { ?>
		var id = <?php echo $column[0]; ?>;
	<?php } else { ?>
		var id = <?php echo 0; ?>;
	<?php } ?>
	function mp_ssv_add_new_field() {
		id++;
		$("#container > tbody:last-child").append(
			$('<tr id="' + id + '" style="vertical-align: top; border-bottom: 1px solid gray; border-top: 1px solid gray;">').append(
				$('<td style="vertical-align: middle; cursor: move;">').append(
					'<img style="padding-right: 15px; margin: 10px 0;" src="<?php echo plugins_url("../images/icon-menu.svg", __FILE__); ?>"/>'
				)
			).append(
				$('<td style="vertical-align: middle; cursor: move;">').append(
					'Title<br/><input type="text" id="' + id + '_field_title" name="' + id + '_field_title"/>'
				)
			).append(
				$('<td style="vertical-align: middle; cursor: move;">').append(
					'Type<br/><select id="' + id + '_field_type" name="' + id + '_field_type" onchange="mp_ssv_type_changed(\'' + id + '\')"><option value="tab">Tab</option><option value="header">Header</option><option value="input">Input</option></select>'
				)
			).append(
				'<td style="vertical-align: middle; cursor: move;"><div class="' + id + '_empty"></div></td>'
			).append(
				'<td style="vertical-align: middle; cursor: move;"><div class="' + id + '_empty"></div></td>'
			).append(
				'<td style="vertical-align: middle; cursor: move;"><div class="' + id + '_empty"></div></td>'
			).append(
				'<td style="vertical-align: middle; cursor: move;"><div class="' + id + '_empty"></div></td>'
			).append(
				'<td style="vertical-align: middle; cursor: move;"><div class="' + id + '_empty"></div></td>'
			)
		);
	}
</script>
<script>
	function mp_ssv_type_changed(sender_id) {
		var tr = document.getElementById(sender_id);
		var type = document.getElementById(sender_id + "_field_type").value;
		$("#" + sender_id + "_input_type").parent().remove();
		$("#" + sender_id + "_name").parent().remove();
		$("#" + sender_id + "_required").parent().remove();
		$("#" + sender_id + "_display").parent().remove();
		$("#" + sender_id + "_placeholder").parent().remove();
		$("#" + sender_id + "_preview").parent().remove();
		$("#" + sender_id + "_help_text").parent().remove();
		$("#" + sender_id + "_title_as_header").parent().remove();
		$("#" + sender_id + "_options").parent().remove();
		$("#" + sender_id + "_role").parent().remove();
		$("#" + sender_id + "_input_type_custom").parent().remove();
		$("." + sender_id + "_empty").parent().remove();
		if (type == "input") {
			$(tr).append(
				'<?php echo mp_ssv_td(mp_ssv_select("Input Type", '\' + sender_id + \'', "text", array("Text", "Text Group Select", "Role Group Select", "Text Checkbox", "Role Checkbox", "Image"), array("onchange=\"mp_ssv_input_type_changed(' + sender_id + ')\""), true)); ?>'
			).append(
				'<?php echo mp_ssv_td(mp_ssv_text_input("Name", '\' + sender_id + \'', "")); ?>'
			).append(
				'<?php echo mp_ssv_td(mp_ssv_checkbox("Required", '\' + sender_id + \'', "no")); ?>'
			).append(
				'<?php echo mp_ssv_td(mp_ssv_select("Display", '\' + sender_id + \'', "normal", array("Normal", "ReadOnly", "Disabled"))); ?>'
			).append(
				'<?php echo mp_ssv_td(mp_ssv_text_input("Placeholder", '\' + sender_id + \'', "")); ?>'
			);
		} else {
			$(tr).append(
				'<td style="vertical-align: middle; cursor: move;"><div class="' + sender_id + '_empty"></div></td>'
			).append(
				'<td style="vertical-align: middle; cursor: move;"><div class="' + sender_id + '_empty"></div></td>'
			).append(
				'<td style="vertical-align: middle; cursor: move;"><div class="' + sender_id + '_empty"></div></td>'
			).append(
				'<td style="vertical-align: middle; cursor: move;"><div class="' + sender_id + '_empty"></div></td>'
			).append(
				'<td style="vertical-align: middle; cursor: move;"><div class="' + sender_id + '_empty"></div></td>'
			);
		}
	}
</script>
<script>
	function mp_ssv_input_type_changed(sender_id) {
		var tr = document.getElementById(sender_id);
		var input_type_custom = document.getElementById(sender_id + "_input_type").parentElement;
		var input_type = document.getElementById(sender_id + "_input_type").value;
		$("#" + sender_id + "_name").parent().remove();
		$("#" + sender_id + "_required").parent().remove();
		$("#" + sender_id + "_display").parent().remove();
		$("#" + sender_id + "_placeholder").parent().remove();
		$("#" + sender_id + "_preview").parent().remove();
		$("#" + sender_id + "_help_text").parent().remove();
		$("#" + sender_id + "_title_as_header").parent().remove();
		$("#" + sender_id + "_options").parent().remove();
		$("#" + sender_id + "_role").parent().remove();
		$("#" + sender_id + "_input_type_custom").parent().remove();
		$("." + sender_id + "_empty").parent().remove();
		switch (input_type) {
			case "text_group_select":
				$(tr).append(
					'<?php echo mp_ssv_td(mp_ssv_text_input("Name", '\' + sender_id + \'', "")); ?>'
				).append(
					'<td style="vertical-align: middle; cursor: move;"><div class="' + sender_id + '_empty"></div></td>'
				).append(
					'<?php echo mp_ssv_td(mp_ssv_select("Display", '\' + sender_id + \'', "normal", array("Normal", "ReadOnly", "Disabled"))); ?>'
				).append(
				'<?php echo mp_ssv_td(mp_ssv_options('\' + sender_id + \'', array(), "text")); ?>'
				);
				break;
			case "role_group_select":
				$(tr).append(
					'<?php echo mp_ssv_td(mp_ssv_text_input("Name", '\' + sender_id + \'', "")); ?>'
				).append(
					'<td style="vertical-align: middle; cursor: move;"><div class="' + sender_id + '_empty"></div></td>'
				).append(
					'<?php echo mp_ssv_td(mp_ssv_select("Display", '\' + sender_id + \'', "normal", array("Normal", "ReadOnly", "Disabled"))); ?>'
				).append(
				'<?php echo mp_ssv_td(mp_ssv_options('\' + sender_id + \'', array(), "role")); ?>'
				);
				break;
			case "text_checkbox":
				$(tr).append(
					'<?php echo mp_ssv_td(mp_ssv_text_input("Name", '\' + sender_id + \'', "")); ?>'
				).append(
					'<td style="vertical-align: middle; cursor: move;"><div class="' + sender_id + '_empty"></div></td>'
				).append(
					'<?php echo mp_ssv_td(mp_ssv_select("Display", '\' + sender_id + \'', "normal", array("Normal", "ReadOnly", "Disabled"))); ?>'
				).append(
					'<td style="vertical-align: middle; cursor: move;"><div class="' + sender_id + '_empty"></div></td>'
				);
				break;
			case "role_checkbox":
				$(tr).append(
					'<td style="vertical-align: middle; cursor: move;"><div class="' + sender_id + '_empty"></div></td>'
				).append(
					'<td style="vertical-align: middle; cursor: move;"><div class="' + sender_id + '_empty"></div></td>'
				).append(
					'<?php echo mp_ssv_td(mp_ssv_select("Display", '\' + sender_id + \'', "normal", array("Normal", "ReadOnly", "Disabled"))); ?>'
				).append(
				'<?php echo mp_ssv_td(mp_ssv_role_select('\' + sender_id + \'', "Role", "")); ?>'
				);
				break;
			case "image":
				$(tr).append(
					'<?php echo mp_ssv_td(mp_ssv_text_input("Name", '\' + sender_id + \'', "")); ?>'
				).append(
					'<?php echo mp_ssv_td(mp_ssv_checkbox("Required", '\' + sender_id + \'', "no")); ?>'
				).append(
					'<?php echo mp_ssv_td(mp_ssv_checkbox("Preview", '\' + sender_id + \'', "no")); ?>'
				).append(
					'<td style="vertical-align: middle; cursor: move;"><div class="' + sender_id + '_empty"></div></td>'
				);
				break;
			case "text":
				$(tr).append(
					'<?php echo mp_ssv_td(mp_ssv_text_input("Name", '\' + sender_id + \'', "")); ?>'
				).append(
					'<?php echo mp_ssv_td(mp_ssv_checkbox("Required", '\' + sender_id + \'', "no")); ?>'
				).append(
					'<?php echo mp_ssv_td(mp_ssv_select("Display", '\' + sender_id + \'', "normal", array("Normal", "ReadOnly", "Disabled"))); ?>'
				).append(
					'<?php echo mp_ssv_td(mp_ssv_text_input("Placeholder", '\' + sender_id + \'', "")); ?>'
				);
				break;
			case "custom":
				$(input_type_custom).append(
					'<div><input type="text" id="' + sender_id + '_input_type_custom" name="' + sender_id + '_input_type_custom" value="<?php echo mp_ssv_get_field_meta('\' + sender_id + \'', "input_type_custom"); ?>"/></div>'
				);
				$(tr).append(
					'<?php echo mp_ssv_td(mp_ssv_text_input("Name", '\' + sender_id + \'', "")); ?>'
				).append(
					'<?php echo mp_ssv_td(mp_ssv_checkbox("Required", '\' + sender_id + \'', "no")); ?>'
				).append(
					'<?php echo mp_ssv_td(mp_ssv_select("Display", '\' + sender_id + \'', "normal", array("Normal", "ReadOnly", "Disabled"))); ?>'
				).append(
					'<?php echo mp_ssv_td(mp_ssv_text_input("Placeholder", '\' + sender_id + \'', "")); ?>'
				);
				break;
		}
	}
</script>
<script>
	function add_text_option(sender_id) {
		var li = document.getElementById(sender_id + "_add_option").parentElement;
		id++;
		$(li).before(
			'<li><input type="text" id="' + sender_id + '_' + id + '_option" name="' + sender_id + '_' + id + '_option"/></li>'
		);
	}
</script>
<script>
	function add_role_option(sender_id) {
		var li = document.getElementById(sender_id + "_add_option").parentElement;
		id++;
		$(li).before(
			'<li><?php $object_name = '\' + sender_id + \''."_".'\' + id + \''; echo mp_ssv_role_select($object_name, "option", "", false); ?></li>'
		);
	}
</script>

<?php
function mp_ssv_td($content) {
	ob_start();
	?><td style="vertical-align: middle; cursor: move;"><?php echo $content; ?></td><?php
	return ob_get_clean();
}

function mp_ssv_draggable_icon() {
	ob_start();
	?><img style="padding-right: 15px; margin: 10px 0;" src="<?php echo plugins_url( '../images/icon-menu.svg', __FILE__ ); ?>"/><?php
	return ob_get_clean();
}

function mp_ssv_text_input($title, $id, $value, $type = "text", $args = array()) {
	ob_start();
	$object_name = $id."_".strtolower(str_replace(" ", "_", $title));
	echo $title; ?><br/><input type="<?php echo $type; ?>" id="<?php echo $object_name; ?>" name="<?php echo $object_name; ?>" value="<?php echo $value; ?>" <?php foreach($args as $arg) { echo $arg; } ?>/><?php
	return ob_get_clean();
}

function mp_ssv_select($title, $id, $selected, $options, $args = array(), $allow_custom = false) {
	ob_start();
	if ($allow_custom) {
		$options[] = "Custom";
	}
	$object_name = $id."_".strtolower(str_replace(" ", "_", $title));
	$object_custom_name = $id."_".strtolower(str_replace(" ", "_", $title))."_custom";
	echo $title; ?><br/><select id="<?php echo $object_name; ?>" name="<?php echo $object_name; ?>" <?php foreach($args as $arg) { echo $arg; } ?>><?php foreach ($options as $option) { ?><option value="<?php echo strtolower(str_replace(" ", "_", $option)); ?>" <?php if ($selected == strtolower(str_replace(" ", "_", $option))) { echo "selected"; } ?>><?php echo $option; ?></option><?php } ?></select><?php if ($allow_custom && $selected == "custom") { ?><div><input type="text" id="<?php echo $object_custom_name; ?>" name="<?php echo $object_custom_name; ?>" value="<?php echo mp_ssv_get_field_meta($id, "input_type_custom"); ?>"/></div><?php }
	return ob_get_clean();
}

function mp_ssv_checkbox($title, $id, $value, $args = array()) {
	ob_start();
	$object_name = $id."_".strtolower(str_replace(" ", "_", $title));
	?><br/><input type="checkbox" id="<?php echo $object_name; ?>" name="<?php echo $object_name; ?>" value="yes" <?php if ($value == "yes") { echo "checked"; } ?> <?php foreach($args as $arg) { echo $arg; } ?>/> <?php echo $title;
	return ob_get_clean();
}

function mp_ssv_options($parent_id, $options, $type, $args = array()) {
	ob_start();
	?><ul id="<?php echo $parent_id; ?>_options" style="margin: 0;">Options<br/><?php foreach ($options as $option) { echo mp_ssv_option($parent_id, $option, $args); } ?><li><button type="button" id="<?php echo $parent_id; ?>_add_option" onclick="add_<?php echo $type; ?>_option(<?php echo $parent_id; ?>)">Add Option</button></li></ul><?php
	return ob_get_clean();
}

function mp_ssv_option($parent_id, $option, $args = array()) {
	ob_start();
	$object_name = $parent_id."_".$option["id"];
	if ($option["type"] == "role") {
		echo "<li>".mp_ssv_role_select($object_name, "option", $option["value"], false)."</li>";
	} else {
		?> <li><input type="text" id="<?php echo $object_name; ?>_option" name="<?php echo $object_name; ?>_option" value="<?php echo $option["value"]; ?>" <?php foreach($args as $arg) { echo $arg; } ?>/></li> <?php
	}
	return ob_get_clean();
}

function mp_ssv_hidden($id, $name, $value) {
	ob_start();
	$object_name = $id."_".$name;
	?><input type="hidden" id="<?php echo $object_name; ?>" name="<?php echo $object_name; ?>" value="<?php echo $value; ?>"<?php
	return ob_get_clean();
}

function mp_ssv_role_select($id, $title, $value, $with_title = true, $args = array()) {
	$object_name = $id."_".strtolower(str_replace(" ", "_", $title));
	ob_start();
	wp_dropdown_roles($value);
	$roles_options = ob_get_clean();
	$roles_options = trim(preg_replace('/\s\s+/', ' ', $roles_options));
	$roles_options = str_replace("'", '"', $roles_options);
	ob_start();
	if ($with_title) {
		echo $title; ?><br/><?php
	}
	?> <select id="<?php echo $object_name; ?>" name="<?php echo $object_name; ?>" <?php foreach($args as $arg) { echo $arg; } ?>><option value=""></option><?php echo $roles_options; ?></select> <?php
	return ob_get_clean();
}
?>
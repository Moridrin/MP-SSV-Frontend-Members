<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	FrontendMembersField::saveAllFromPost();
}
?>
<!--suppress JSUnusedLocalSymbols -->
<form id="mp-ssv-frontend-members-options" name="mp-ssv-frontend-members-options" method="post" action="#">
	<table id="container" style="width: 100%; border-spacing: 10px 0; margin-bottom: 20px; margin-top: 20px; border-collapse: collapse;">
		<tbody class="sortable">
		<?php
		$fields = FrontendMembersField::getAll();
		foreach ($fields as $field) {
            /* @var $field FrontendMembersField */
			echo $field->getOptionRow();
		}
		?>
		</tbody>
	</table>
	<button type="button" id="add_field_button" onclick="mp_ssv_add_new_field()">Add Field</button>
	<?php
	submit_button();
	?>
</form>
<!-- Make the rows draggable. -->
<script src="<?php echo plugins_url("/mp-ssv-frontend-members/include/jquery-2.2.0.js"); ?>"></script>
<script src="<?php echo plugins_url("/mp-ssv-frontend-members/include/jquery-ui.js"); ?>"></script>
<script>
	$(function () {
        var sortable = $(".sortable");
        sortable.sortable();
        sortable.disableSelection();
	});
</script>
<!-- Add new Field. -->
<script>
	<?php
	global $wpdb;
    /** @noinspection PhpIncludeInspection */
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	$table = $wpdb->prefix . "mp_ssv_frontend_members_fields";
	$max_database_index = $wpdb->get_var("SELECT MAX(id) FROM $table");
	print("var id;\n");
	if (count($max_database_index) > 0) {
		echo "id = " . $max_database_index . ";\n";
	} else {
		echo "id = 0\n";
	}
	$new_field_content = mp_ssv_get_td(mp_ssv_get_draggable_icon());
	$new_field_content .= mp_ssv_get_td(mp_ssv_get_text_input("Field Title", '\' + id + \'', "", "text", array("required")));
	$new_field_content .= mp_ssv_get_td(mp_ssv_get_select("Field Type", '\' + id + \'', "input", array("Tab", "Header", "Input"), array("onchange=\"mp_ssv_type_changed(' + id + ')\"")));
	$new_field_content .= mp_ssv_get_td(mp_ssv_get_select("Input Type", '\' + id + \'', "text", array("Text", "Text Select", "Role Select", "Text Checkbox", "Role Checkbox", "Image"), array("onchange=\"mp_ssv_input_type_changed(' + id + ')\""), true));
	$new_field_content .= mp_ssv_get_td(mp_ssv_get_text_input("Name", '\' + id + \'', "", "text", array("required")));
	$new_field_content .= mp_ssv_get_td(mp_ssv_get_checkbox("Required", '\' + id + \'', "no"));
	$new_field_content .= mp_ssv_get_td(mp_ssv_get_select("Display", '\' + id + \'', "normal", array("Normal", "ReadOnly", "Disabled")));
	$new_field_content .= mp_ssv_get_td(mp_ssv_get_text_input("Placeholder", '\' + id + \'', ""));
	$new_field_content .= mp_ssv_get_td(mp_ssv_get_checkbox("Registration Page", '\' + id + \'', "yes", array(), true));
	$new_field_content .= mp_ssv_get_td(mp_ssv_get_text_input("Field Class", '\' + id + \'', ""));
	$new_field_content .= mp_ssv_get_td(mp_ssv_get_text_input("Field Style", '\' + id + \'', ""));
	$new_field = mp_ssv_get_tr('\' + id + \'', $new_field_content);
	?>
	function mp_ssv_add_new_field() {
		id++;
        $("#container").find("> tbody:last-child").append('<?php echo $new_field ?>');
	}
</script>
<!-- Change Field Type. -->
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
        $("#" + sender_id + "_registration_page").parent().remove();
        $("#" + sender_id + "_field_class").parent().remove();
        $("#" + sender_id + "_field_style").parent().remove();
		if (type == "input") {
			$(tr).append(
				'<?php echo mp_ssv_get_td(mp_ssv_get_select("Input Type", '\' + sender_id + \'', "text", array("Text", "Text Group Select", "Role Group Select", "Text Checkbox", "Role Checkbox", "Image"), array("onchange=\"mp_ssv_input_type_changed(' + sender_id + ')\""), true)); ?>'
			).append(
                '<?php echo mp_ssv_get_td(mp_ssv_get_text_input("Name", '\' + sender_id + \'', "", 'text', array('required'))); ?>'
			).append(
				'<?php echo mp_ssv_get_td(mp_ssv_get_checkbox("Required", '\' + sender_id + \'', "no")); ?>'
			).append(
				'<?php echo mp_ssv_get_td(mp_ssv_get_select("Display", '\' + sender_id + \'', "normal", array("Normal", "ReadOnly", "Disabled"))); ?>'
			).append(
				'<?php echo mp_ssv_get_td(mp_ssv_get_text_input("Placeholder", '\' + sender_id + \'', "")); ?>'
			);
		} else {
			$(tr).append(
				'<?php echo mp_ssv_get_td('<div class="\' + sender_id + \'_empty"></div>'); ?>'
			).append(
				'<?php echo mp_ssv_get_td('<div class="\' + sender_id + \'_empty"></div>'); ?>'
			).append(
				'<?php echo mp_ssv_get_td('<div class="\' + sender_id + \'_empty"></div>'); ?>'
			).append(
				'<?php echo mp_ssv_get_td('<div class="\' + sender_id + \'_empty"></div>'); ?>'
			).append(
				'<?php echo mp_ssv_get_td('<div class="\' + sender_id + \'_empty"></div>'); ?>'
			);
		}
        $(tr).append(
            '<?php echo mp_ssv_get_td(mp_ssv_get_checkbox("Registration Page", '\' + sender_id + \'', "yes", array(), true)); ?>'
        ).append(
            '<?php echo mp_ssv_get_td(mp_ssv_get_text_input("Field Class", '\' + sender_id + \'', "")); ?>'
        ).append(
            '<?php echo mp_ssv_get_td(mp_ssv_get_text_input("Field Style", '\' + sender_id + \'', "")); ?>'
        );
	}
</script>
<!-- Change Input Type. -->
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
        $("#" + sender_id + "_registration_page").parent().remove();
        $("#" + sender_id + "_field_class").parent().remove();
        $("#" + sender_id + "_field_style").parent().remove();
		switch (input_type) {
			case "text_select":
				$(tr).append(
					'<?php echo mp_ssv_get_td(mp_ssv_get_text_input("Name", '\' + sender_id + \'', "", "text", array("required"))); ?>'
				).append(
                    '<?php echo mp_ssv_get_td(mp_ssv_get_options('\' + sender_id + \'', array(), "text")); ?>'
				).append(
                    '<?php echo mp_ssv_get_td(mp_ssv_get_select("Display", '\' + sender_id + \'', "normal", array("Normal", "ReadOnly", "Disabled"))); ?>'
				).append(
                    '<?php echo mp_ssv_get_td('<div class="\' + sender_id + \'_empty"></div>'); ?>'
				);
				break;
			case "role_select":
				$(tr).append(
					'<?php echo mp_ssv_get_td(mp_ssv_get_text_input("Name", '\' + sender_id + \'', "", "text", array("required"))); ?>'
				).append(
                    '<?php echo mp_ssv_get_td(mp_ssv_get_options('\' + sender_id + \'', array(), "role")); ?>'
				).append(
					'<?php echo mp_ssv_get_td(mp_ssv_get_select("Display", '\' + sender_id + \'', "normal", array("Normal", "ReadOnly", "Disabled"))); ?>'
				).append(
                    '<?php echo mp_ssv_get_td('<div class="\' + sender_id + \'_empty"></div>'); ?>'
				);
				break;
			case "text_checkbox":
				$(tr).append(
					'<?php echo mp_ssv_get_td(mp_ssv_get_text_input("Name", '\' + sender_id + \'', "", "text", array("required"))); ?>'
				).append(
                    '<?php echo mp_ssv_get_td(mp_ssv_get_checkbox("Required", '\' + sender_id + \'', "no")); ?>'
				).append(
					'<?php echo mp_ssv_get_td(mp_ssv_get_select("Display", '\' + sender_id + \'', "normal", array("Normal", "ReadOnly", "Disabled"))); ?>'
				).append(
					'<?php echo mp_ssv_get_td('<div class="\' + sender_id + \'_empty"></div>'); ?>'
				);
				break;
			case "role_checkbox":
				$(tr).append(
                    '<?php echo mp_ssv_get_td(mp_ssv_get_role_select('\' + sender_id + \'', "Role", "")); ?>'
				).append(
					'<?php echo mp_ssv_get_td('<div class="\' + sender_id + \'_empty"></div>'); ?>'
				).append(
					'<?php echo mp_ssv_get_td(mp_ssv_get_select("Display", '\' + sender_id + \'', "normal", array("Normal", "ReadOnly", "Disabled"))); ?>'
				).append(
                    '<?php echo mp_ssv_get_td('<div class="\' + sender_id + \'_empty"></div>'); ?>'
				);
				break;
			case "image":
				$(tr).append(
					'<?php echo mp_ssv_get_td(mp_ssv_get_text_input("Name", '\' + sender_id + \'', "", "text", array("required"))); ?>'
				).append(
					'<?php echo mp_ssv_get_td(mp_ssv_get_checkbox("Required", '\' + sender_id + \'', "no")); ?>'
				).append(
					'<?php echo mp_ssv_get_td(mp_ssv_get_checkbox("Preview", '\' + sender_id + \'', "no")); ?>'
				).append(
					'<?php echo mp_ssv_get_td('<div class="\' + sender_id + \'_empty"></div>'); ?>'
				);
				break;
			case "text":
				$(tr).append(
					'<?php echo mp_ssv_get_td(mp_ssv_get_text_input("Name", '\' + sender_id + \'', "", "text", array("required"))); ?>'
				).append(
					'<?php echo mp_ssv_get_td(mp_ssv_get_checkbox("Required", '\' + sender_id + \'', "no")); ?>'
				).append(
					'<?php echo mp_ssv_get_td(mp_ssv_get_select("Display", '\' + sender_id + \'', "normal", array("Normal", "ReadOnly", "Disabled"))); ?>'
				).append(
					'<?php echo mp_ssv_get_td(mp_ssv_get_text_input("Placeholder", '\' + sender_id + \'', "")); ?>'
				);
				break;
			case "custom":
				$(input_type_custom).append(
					'<div><?php echo mp_ssv_get_text_input("", '\' + sender_id + \'_input_type_custom', ""); ?></div>'
				);
				$(tr).append(
					'<?php echo mp_ssv_get_td(mp_ssv_get_text_input("Name", '\' + sender_id + \'', "", "text", array("required"))); ?>'
				).append(
					'<?php echo mp_ssv_get_td(mp_ssv_get_checkbox("Required", '\' + sender_id + \'', "no")); ?>'
				).append(
					'<?php echo mp_ssv_get_td(mp_ssv_get_select("Display", '\' + sender_id + \'', "normal", array("Normal", "ReadOnly", "Disabled"))); ?>'
				).append(
					'<?php echo mp_ssv_get_td(mp_ssv_get_text_input("Placeholder", '\' + sender_id + \'', "")); ?>'
				);
				break;
		}
        $(tr).append(
            '<?php echo mp_ssv_get_td(mp_ssv_get_checkbox("Registration Page", '\' + sender_id + \'', "yes", array(), true)); ?>'
        ).append(
            '<?php echo mp_ssv_get_td(mp_ssv_get_text_input("Field Class", '\' + sender_id + \'', "")); ?>'
        ).append(
            '<?php echo mp_ssv_get_td(mp_ssv_get_text_input("Field Style", '\' + sender_id + \'', "")); ?>'
        );
	}
</script>
<!-- Add Text Option. -->
<script>
	function add_text_option(sender_id) {
		id++;
		var li = document.getElementById(sender_id + "_add_option").parentElement;
		$(li).before(
			'<li><?php echo mp_ssv_get_option('\' + sender_id + \'', array('id' => '\' + id + \'', 'type' => 'text', 'value' => "")); ?></li>'
		);
	}
</script>
<!-- Add Role Option. -->
<script>
	function add_role_option(sender_id) {
		var li = document.getElementById(sender_id + "_add_option").parentElement;
		id++;
		<?php $object_name = '\' + sender_id + \'' . "_" . '\' + id + \''; ?>
		$(li).before(
			'<li><?php echo mp_ssv_get_role_select($object_name, "option", "", false); ?></li>'
		);
	}
</script>

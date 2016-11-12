<?php
if (!defined('ABSPATH')) {
    exit;
}
if (!current_user_can('manage_options')) {
    ?><p>You are unauthorized to view or edit this page.</p><?php
    return;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['form'] == 'fields' && check_admin_referer('ssv_save_frontend_members_profile_page_options')) {
    FrontendMembersField::saveAllFromPost();
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['form'] == 'option_columns' && check_admin_referer('ssv_save_frontend_members_profile_page_column_options')) {
    foreach (
        array('default',
              'style',
              'display__preview',
              'placeholder',
              'class',
        ) as $column
    ) {
        if (isset($_POST[$column])) {
            update_option('ssv_frontend_members_view_' . $column . '_column', 'true');
        } else {
            update_option('ssv_frontend_members_view_' . $column . '_column', 'false');
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['form'] == 'import_options' && check_admin_referer('ssv_save_frontend_members_profile_page_import_options')) {
    FrontendMembersField::importFieldsToRegister();
}
?>
<!--suppress JSUnusedLocalSymbols -->
<h1>Columns to Display</h1>
<form id="ssv-frontend-members-option-columns" name="ssv-frontend-members-option-columns" method="post" action="#">
    <input type="hidden" name="form" value="option_columns"/>
    <table id="container" style="width: 100%; border-spacing: 10px 0; margin-bottom: 20px; margin-top: 20px; border-collapse: collapse;">
        <tr>
            <td><input id="title" type="checkbox" name="title" value="yes" checked disabled/><label for="title">Title</td>
            <td><input id="input_type" type="checkbox" name="input_type" value="yes" checked disabled/><label for="input_type">Input Type</td>
            <td><input id="required__options" type="checkbox" name="required__options" value="yes" checked disabled/><label for="required__options">Required/Options</td>
            <td><input id="default" type="checkbox" name="default" value="yes" <?php if (get_option('ssv_frontend_members_view_default_column') == 'true') {
                    echo 'checked';
                } ?> /><label for="default">Default</td>
            <td><input id="class" type="checkbox" name="class" value="yes" <?php if (get_option('ssv_frontend_members_view_class_column') == 'true') {
                    echo 'checked';
                } ?> /><label for="class">Class</td>
        </tr>
        <tr>
            <td><input id="field_type" type="checkbox" name="field_type" value="yes" checked disabled/><label for="field_type">Field Type</td>
            <td><input id="name" type="checkbox" name="name" value="yes" checked disabled/><label for="name">Name</td>
            <td><input id="display__preview" type="checkbox" name="display__preview" value="yes" <?php if (get_option('ssv_frontend_members_view_display__preview_column') == 'true') {
                    echo 'checked';
                } ?> /><label for="display__preview">Display</td>
            <td><input id="placeholder" type="checkbox" name="placeholder" value="yes" <?php if (get_option('ssv_frontend_members_view_placeholder_column') == 'true') {
                    echo 'checked';
                } ?> /><label for="placeholder">Placeholder</td>
            <td><input id="style" type="checkbox" name="style" value="yes" <?php if (get_option('ssv_frontend_members_view_style_column') == 'true') {
                    echo 'checked';
                } ?> /><label for="style">Style</td>
        </tr>
    </table>
    <?php
    wp_nonce_field('ssv_save_frontend_members_profile_page_column_options');
    submit_button();
    ?>
</form>
<h1>Fields</h1>
<?php if ($_GET['tab'] == 'register_page'): ?>
    <form id="ssv-frontend-members-import-options" name="ssv-frontend-members-import-options" method="post" action="#">
        <input type="hidden" name="form" value="import_options"/>
        <?php wp_nonce_field('ssv_save_frontend_members_profile_page_import_options'); ?>
        <input type="submit" name="submit" id="submit" value="Import From Profile">
    </form>
<?php endif; ?>
<form id="ssv-frontend-members-options" name="ssv-frontend-members-options" method="post" action="#">
    <input type="hidden" name="form" value="fields"/>
    <table id="fields_container" style="width: 100%; border-spacing: 10px 0; margin-bottom: 20px; margin-top: 20px; border-collapse: collapse;">
        <tbody class="sortable">
        <?php
        if ($_GET['tab'] == 'register_page') {
            $fields = FrontendMembersField::getAll(array('registration_page' => 'yes'));
        } else {
            $fields = FrontendMembersField::getAll(array('registration_page' => 'no'));
        }
        foreach ($fields as $field) {
            /* @var $field FrontendMembersField */
            echo $field->getOptionRow();
        }
        ?>
        </tbody>
    </table>
    <button type="button" id="add_field_button" onclick="ssv_add_new_field()">Add Field</button>
    <?php
    wp_nonce_field('ssv_save_frontend_members_profile_page_options');
    submit_button();
    ?>
</form>
<!-- Make the rows draggable. -->
<?php wp_enqueue_script('jquery'); ?>
<?php wp_enqueue_script('jquery-ui-core'); ?>
<?php wp_enqueue_script('jquery-ui-tabs'); ?>
<?php wp_enqueue_script('jquery-ui-sortable'); ?>
<?php wp_enqueue_script('jquery-ui-draggable'); ?>
<script>
    var $ = jQuery.noConflict();
    $(function () {
        var sortable = $(".sortable");
        sortable.sortable();
        sortable.disableSelection();
    });
</script>
<!-- Add new Field -->
<script>
    var $ = jQuery.noConflict();
    <?php
    global $wpdb;
    /** @noinspection PhpIncludeInspection */
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $table = $wpdb->prefix . "ssv_frontend_members_fields";
    $max_database_index = $wpdb->get_var("SELECT MAX(id) FROM $table");
    print("var id;\n");
    if (count($max_database_index) > 0) {
        echo "id = " . $max_database_index . ";\n";
    } else {
        echo "id = 0\n";
    }
    $new_field_content = ssv_get_td(ssv_get_draggable_icon());
    $new_field_content .= ssv_get_hidden('\' + id + \'', "Registration Page", $_GET['tab'] == 'register_page' ? 'yes' : 'no');
    $new_field_content .= ssv_get_td(ssv_get_text_input("Field Title", '\' + id + \'', "", "text", array("required"), false));
    if ($_GET['tab'] == 'register_page') {
        $new_field_content .= ssv_get_td(ssv_get_select("Field Type", '\' + id + \'', "input", array("Header", "Input", "Label"), array("onchange=\"ssv_type_changed(' + id + ')\""), false, null, true, false));
    } else {
        $new_field_content .= ssv_get_td(ssv_get_select("Field Type", '\' + id + \'', "input", array("Tab", "Header", "Input", "Label"), array("onchange=\"ssv_type_changed(' + id + ')\""), false, null, true, false));
    }
    $new_field_content .= ssv_get_td(ssv_get_select("Input Type", '\' + id + \'', "text", array("Text", "Text Select", "Role Select", "Text Checkbox", "Role Checkbox", "Image"), array("onchange=\"ssv_input_type_changed(' + id + ')\""), true, null, true, false));
    $new_field_content .= ssv_get_td(ssv_get_text_input("Name", '\' + id + \'', "", "text", array("required"), false));
    $new_field_content .= ssv_get_td(ssv_get_checkbox("Required", '\' + id + \'', "no", array(), false, false));
    if (get_option('ssv_frontend_members_view_display__preview_column', 'true') == 'true') {
        $new_field_content .= ssv_get_td(ssv_get_select("Display", '\' + id + \'', "normal", array("Normal", "ReadOnly", "Disabled"), array(), false, null, true, false));
    } else {
        $new_field_content .= ssv_get_hidden('\' + id + \'', "Display", '');
    }
    if (get_option('ssv_frontend_members_view_default_column', 'true') == 'true') {
        $new_field_content .= ssv_get_td(ssv_get_text_input("Default Value", '\' + id + \'', '', 'text', array(), false));
    } else {
        $new_field_content .= ssv_get_hidden('\' + id + \'', "Default Value", '');
    }
    if (get_option('ssv_frontend_members_view_placeholder_column', 'true') == 'true') {
        $new_field_content .= ssv_get_td(ssv_get_text_input("Placeholder", '\' + id + \'', '', 'text', array(), false));
    } else {
        $new_field_content .= ssv_get_hidden('\' + id + \'', "Placeholder", '');
    }
    if (get_option('ssv_frontend_members_view_class_column', 'true') == 'true') {
        $new_field_content .= ssv_get_td(ssv_get_text_input('Field Class', '\' + id + \'', '', 'text', array(), false));
    } else {
        $new_field_content .= ssv_get_hidden('\' + id + \'', "Field Class", '');
    }
    if (get_option('ssv_frontend_members_view_style_column', 'true') == 'true') {
        $new_field_content .= ssv_get_td(ssv_get_text_input('Field Style', '\' + id + \'', '', 'text', array(), false));
    } else {
        $new_field_content .= ssv_get_hidden('\' + id + \'', "Field Style", '');
    }
    $new_field = ssv_get_tr('\' + id + \'', $new_field_content);
    ?>
    function ssv_add_new_field() {
        id++;
        $("#fields_container").find("> tbody:last-child").append('<?php echo $new_field ?>');
    }
</script>
<!-- Change Field Type -->
<!--suppress JSUnusedLocalSymbols -->
<script>
    var $ = jQuery.noConflict();
    function ssv_type_changed(sender_id) {
        var tr = document.getElementById(sender_id);
        var type = document.getElementById(sender_id + "_field_type").value;
        $("#" + sender_id + "_text").parent().remove();
        $("#" + sender_id + "_input_type").parent().remove();
        $("#" + sender_id + "_name").parent().remove();
        $("#" + sender_id + "_required").parent().remove();
        $("#" + sender_id + "_display").parent().remove();
        $("#" + sender_id + "_checked_by_default").parent().remove();
        $("#" + sender_id + "_default_value").parent().remove();
        $("#" + sender_id + "_placeholder").parent().remove();
        $("#" + sender_id + "_preview").parent().remove();
        $("#" + sender_id + "_help_text").parent().remove();
        $("#" + sender_id + "_title_as_header").parent().remove();
        $("#" + sender_id + "_options").parent().remove();
        $("#" + sender_id + "_role").parent().remove();
        $("#" + sender_id + "_input_type_custom").parent().remove();
        $("." + sender_id + "_empty").parent().remove();
        $("#" + sender_id + "_field_class").parent().remove();
        $("#" + sender_id + "_field_style").parent().remove();
        if (type == "input") {
            $(tr).append(
                '<?php echo ssv_get_td(ssv_get_select("Input Type", '\' + sender_id + \'', "text", array("Text", "Text Select", "Role Select", "Text Checkbox", "Role Checkbox", "Image"), array("onchange=\"ssv_input_type_changed(' + sender_id + ')\""), true, null, true, false)); ?>'
            ).append(
                '<?php echo ssv_get_td(ssv_get_text_input("Name", '\' + sender_id + \'', "", "text", array("required"), false)); ?>'
            ).append(
                '<?php echo ssv_get_td(ssv_get_checkbox("Required", '\' + sender_id + \'', "no", array(), false, false)); ?>'
            );
            <?php if (get_option('ssv_frontend_members_view_display__preview_column', 'true') == 'true'): ?>
            $(tr).append(
                '<?php echo ssv_get_td(ssv_get_select("Display", '\' + sender_id + \'', "normal", array("Normal", "ReadOnly", "Disabled"), array(), false, null, true, false)); ?>'
            );
            <?php endif; ?>
            <?php if (get_option('ssv_frontend_members_view_default_column', 'true') == 'true'): ?>
            $(tr).append(
                '<?php echo ssv_get_td(ssv_get_text_input("Default Value", '\' + sender_id + \'', "", 'text', array(), false)); ?>'
            );
            <?php endif; ?>
            <?php if (get_option('ssv_frontend_members_view_placeholder_column', 'true') == 'true'): ?>
            $(tr).append(
                '<?php echo ssv_get_td(ssv_get_text_input("Placeholder", '\' + sender_id + \'', "", 'text', array(), false)); ?>'
            );
            <?php endif; ?>
        } else if (type == 'label') {
            <?php
            $colspan = 3;
            if (get_option('ssv_frontend_members_view_display__preview_column', 'true') == 'true') {
                $colspan++;
            }
            if (get_option('ssv_frontend_members_view_default_column', 'true') == 'true') {
                $colspan++;
            }
            if (get_option('ssv_frontend_members_view_placeholder_column', 'true') == 'true') {
                $colspan++;
            }
            ?>
            $(tr).append(
                '<?php echo ssv_get_td(ssv_get_text_area("Text", '\' + sender_id + \'', "", "text", array("required"), false), $colspan); ?>'
            );
        } else {
            $(tr).append(
                '<?php echo ssv_get_td('<div class="\' + sender_id + \'_empty"></div>'); ?>'
            ).append(
                '<?php echo ssv_get_td('<div class="\' + sender_id + \'_empty"></div>'); ?>'
            ).append(
                '<?php echo ssv_get_td('<div class="\' + sender_id + \'_empty"></div>'); ?>'
            );
            <?php if (get_option('ssv_frontend_members_view_display__preview_column', 'true') == 'true'): ?>
            $(tr).append(
                '<?php echo ssv_get_td('<div class="\' + sender_id + \'_empty"></div>'); ?>'
            );
            <?php endif; ?>
            <?php if (get_option('ssv_frontend_members_view_default_column', 'true') == 'true'): ?>
            $(tr).append(
                '<?php echo ssv_get_td('<div class="\' + sender_id + \'_empty"></div>'); ?>'
            );
            <?php endif; ?>
            <?php if (get_option('ssv_frontend_members_view_placeholder_column', 'true') == 'true'): ?>
            $(tr).append(
                '<?php echo ssv_get_td('<div class="\' + sender_id + \'_empty"></div>'); ?>'
            );
            <?php endif; ?>
        }
        <?php if (get_option('ssv_frontend_members_view_class_column', 'true') == 'true'): ?>
        $(tr).append(
            '<?php echo ssv_get_td(ssv_get_text_input("Field Class", '\' + sender_id + \'', "", 'text', array(), false)); ?>'
        );
        <?php endif; ?>
        <?php if (get_option('ssv_frontend_members_view_style_column', 'true') == 'true'): ?>
        $(tr).append(
            '<?php echo ssv_get_td(ssv_get_text_input("Field Style", '\' + sender_id + \'', "", 'text', array(), false)); ?>'
        );
        <?php endif; ?>
    }
</script>
<!-- Change Input Type -->
<!--suppress JSUnusedLocalSymbols -->
<script>
    var $ = jQuery.noConflict();
    function ssv_input_type_changed(sender_id) {
        var tr = document.getElementById(sender_id);
        var input_type_custom = document.getElementById(sender_id + "_input_type").parentElement;
        var input_type = document.getElementById(sender_id + "_input_type").value;
        $("#" + sender_id + "_name").parent().remove();
        $("#" + sender_id + "_required").parent().remove();
        $("#" + sender_id + "_display").parent().remove();
        $("#" + sender_id + "_checked_by_default").parent().remove();
        $("#" + sender_id + "_default_value").parent().remove();
        $("#" + sender_id + "_placeholder").parent().remove();
        $("#" + sender_id + "_preview").parent().remove();
        $("#" + sender_id + "_help_text").parent().remove();
        $("#" + sender_id + "_title_as_header").parent().remove();
        $("#" + sender_id + "_options").parent().remove();
        $("#" + sender_id + "_role").parent().remove();
        $("#" + sender_id + "_input_type_custom").parent().remove();
        $("." + sender_id + "_empty").parent().remove();
        $("#" + sender_id + "_field_class").parent().remove();
        $("#" + sender_id + "_field_style").parent().remove();
        switch (input_type) {
            case "text_select":
                $(tr).append(
                    '<?php echo ssv_get_td(ssv_get_text_input("Name", '\' + sender_id + \'', "", "text", array("required"), false)); ?>'
                ).append(
                    '<?php echo ssv_get_td(ssv_get_options('\' + sender_id + \'', array(), "text", array(), false)); ?>'
                );
            <?php if (get_option('ssv_frontend_members_view_display__preview_column', 'true') == 'true'): ?>
                $(tr).append(
                    '<?php echo ssv_get_td(ssv_get_select("Display", '\' + sender_id + \'', "normal", array("Normal", "ReadOnly", "Disabled"), array(), false, null, true, false)); ?>'
                );
            <?php endif; ?>
            <?php if (get_option('ssv_frontend_members_view_default_column', 'true') == 'true'): ?>
                $(tr).append(
                    '<?php echo ssv_get_td('<div class="\' + sender_id + \'_empty"></div>'); ?>'
                );
            <?php endif; ?>
            <?php if (get_option('ssv_frontend_members_view_placeholder_column', 'true') == 'true'): ?>
                $(tr).append(
                    '<?php echo ssv_get_td('<div class="\' + sender_id + \'_empty"></div>'); ?>'
                );
            <?php endif; ?>


                break;
            case "role_select":
                $(tr).append(
                    '<?php echo ssv_get_td(ssv_get_text_input("Name", '\' + sender_id + \'', "", "text", array("required"), false)); ?>'
                ).append(
                    '<?php echo ssv_get_td(ssv_get_options('\' + sender_id + \'', array(), "role", array(), false)); ?>'
                );
            <?php if (get_option('ssv_frontend_members_view_display__preview_column', 'true') == 'true'): ?>
                $(tr).append(
                    '<?php echo ssv_get_td(ssv_get_select("Display", '\' + sender_id + \'', "normal", array("Normal", "ReadOnly", "Disabled"), array(), false, null, true, false)); ?>'
                );
            <?php endif; ?>
            <?php if (get_option('ssv_frontend_members_view_default_column', 'true') == 'true'): ?>
                $(tr).append(
                    '<?php echo ssv_get_td('<div class="\' + sender_id + \'_empty"></div>'); ?>'
                );
            <?php endif; ?>
            <?php if (get_option('ssv_frontend_members_view_placeholder_column', 'true') == 'true'): ?>
                $(tr).append(
                    '<?php echo ssv_get_td('<div class="\' + sender_id + \'_empty"></div>'); ?>'
                );
            <?php endif; ?>
                break;
            case "text_checkbox":
                $(tr).append(
                    '<?php echo ssv_get_td(ssv_get_text_input("Name", '\' + sender_id + \'', "", "text", array("required"), false)); ?>'
                ).append(
                    '<?php echo ssv_get_td(ssv_get_checkbox("Required", '\' + sender_id + \'', "no", array(), false, false)); ?>'
                );
            <?php if (get_option('ssv_frontend_members_view_display__preview_column', 'true') == 'true'): ?>
                $(tr).append(
                    '<?php echo ssv_get_td(ssv_get_select("Display", '\' + sender_id + \'', "normal", array("Normal", "ReadOnly", "Disabled"), array(), false, null, true, false)); ?>'
                );
            <?php endif; ?>
            <?php if (get_option('ssv_frontend_members_view_default_column', 'true') == 'true'): ?>
                $(tr).append(
                    '<?php echo ssv_get_td(ssv_get_checkbox("Checked by Default", '\' + sender_id + \'', "no", array(), false, false)); ?>'
                );
            <?php endif; ?>
                $(tr).append(
                    '<?php echo ssv_get_hidden('\' + sender_id + \'', "Placeholder", '', false); ?>'
                );
                $(tr).append(
                    '<?php echo ssv_get_td('<div class="\' + sender_id + \'_empty"></div>'); ?>'
                );
                break;
            case "role_checkbox":
                $(tr).append(
                    '<?php echo ssv_get_td(ssv_get_role_select('\' + sender_id + \'', "Role", "", true, array(), false)); ?>'
                ).append(
                    '<?php echo ssv_get_td('<div class="\' + sender_id + \'_empty"></div>'); ?>'
                );
            <?php if (get_option('ssv_frontend_members_view_display__preview_column', 'true') == 'true'): ?>
                $(tr).append(
                    '<?php echo ssv_get_td(ssv_get_select("Display", '\' + sender_id + \'', "normal", array("Normal", "ReadOnly", "Disabled"), array(), false, null, true, false)); ?>'
                );
            <?php endif; ?>
            <?php if (get_option('ssv_frontend_members_view_default_column', 'true') == 'true'): ?>
                $(tr).append(
                    '<?php echo ssv_get_td(ssv_get_checkbox("Checked by Default", '\' + sender_id + \'', "no", array(), false, false)); ?>'
                );
            <?php endif; ?>
                $(tr).append(
                    '<?php echo ssv_get_hidden('\' + sender_id + \'', "Placeholder", '', false); ?>'
                );
                $(tr).append(
                    '<?php echo ssv_get_td('<div class="\' + sender_id + \'_empty"></div>'); ?>'
                );
                break;
            case "image":
                $(tr).append(
                    '<?php echo ssv_get_td(ssv_get_text_input("Name", '\' + sender_id + \'', "", "text", array("required"), false)); ?>'
                ).append(
                    '<?php echo ssv_get_td(ssv_get_checkbox("Required", '\' + sender_id + \'', "no", array(), false, false)); ?>'
                );
            <?php if (get_option('ssv_frontend_members_view_display__preview_column', 'true') == 'true'): ?>
                $(tr).append(
                    '<?php echo ssv_get_td(ssv_get_checkbox("Preview", '\' + sender_id + \'', "no", array(), false, false)); ?>'
                );
            <?php endif; ?>
            <?php if (get_option('ssv_frontend_members_view_default_column', 'true') == 'true'): ?>
                $(tr).append(
                    '<?php echo ssv_get_td('<div class="\' + sender_id + \'_empty"></div>'); ?>'
                );
            <?php endif; ?>
            <?php if (get_option('ssv_frontend_members_view_placeholder_column', 'true') == 'true'): ?>
                $(tr).append(
                    '<?php echo ssv_get_td('<div class="\' + sender_id + \'_empty"></div>'); ?>'
                );
            <?php endif; ?>
                break;
            case "text":
                $(tr).append(
                    '<?php echo ssv_get_td(ssv_get_text_input("Name", '\' + sender_id + \'', "", "text", array("required"), false)); ?>'
                ).append(
                    '<?php echo ssv_get_td(ssv_get_checkbox("Required", '\' + sender_id + \'', "no", array(), false, false)); ?>'
                );
            <?php if (get_option('ssv_frontend_members_view_display__preview_column', 'true') == 'true'): ?>
                $(tr).append(
                    '<?php echo ssv_get_td(ssv_get_select("Display", '\' + sender_id + \'', "normal", array("Normal", "ReadOnly", "Disabled"), array(), false, null, true, false)); ?>'
                );
            <?php endif; ?>
            <?php if (get_option('ssv_frontend_members_view_default_column', 'true') == 'true'): ?>
                $(tr).append(
                    '<?php echo ssv_get_td(ssv_get_text_input("Default Value", '\' + sender_id + \'', "", 'text', array(), false)); ?>'
                );
            <?php endif; ?>
            <?php if (get_option('ssv_frontend_members_view_placeholder_column', 'true') == 'true'): ?>
                $(tr).append(
                    '<?php echo ssv_get_td(ssv_get_text_input("Placeholder", '\' + sender_id + \'', "", 'text', array(), false)); ?>'
                );
            <?php endif; ?>
                break;
            case "custom":
                $(input_type_custom).append(
                    '<div><?php echo ssv_get_text_input("", '\' + sender_id + \'_input_type_custom', "", "text", array("required"), false); ?></div>'
                );
                $(tr).append(
                    '<?php echo ssv_get_td(ssv_get_text_input("Name", '\' + sender_id + \'', "", "text", array("required"), false)); ?>'
                ).append(
                    '<?php echo ssv_get_td(ssv_get_checkbox("Required", '\' + sender_id + \'', "no", array(), false, false)); ?>'
                );
            <?php if (get_option('ssv_frontend_members_view_display__preview_column', 'true') == 'true'): ?>
                $(tr).append(
                    '<?php echo ssv_get_td(ssv_get_select("Display", '\' + sender_id + \'', "normal", array("Normal", "ReadOnly", "Disabled"), array(), false, null, true, false)); ?>'
                );
            <?php endif; ?>
            <?php if (get_option('ssv_frontend_members_view_default_column', 'true') == 'true'): ?>
                $(tr).append(
                    '<?php echo ssv_get_td(ssv_get_text_input("Default Value", '\' + sender_id + \'', "", 'text', array(), false)); ?>'
                );
            <?php endif; ?>
            <?php if (get_option('ssv_frontend_members_view_placeholder_column', 'true') == 'true'): ?>
                $(tr).append(
                    '<?php echo ssv_get_td(ssv_get_text_input("Placeholder", '\' + sender_id + \'', "", 'text', array(), false)); ?>'
                );
            <?php endif; ?>
                break;
        }
        $(tr).append(
            '<?php echo ssv_get_td(ssv_get_text_input("Field Class", '\' + sender_id + \'', "", 'text', array(), false)); ?>'
        ).append(
            '<?php echo ssv_get_td(ssv_get_text_input("Field Style", '\' + sender_id + \'', "", 'text', array(), false)); ?>'
        );
    }
</script>
<!-- Add Text Option. -->
<!--suppress JSUnusedLocalSymbols -->
<script>
    var $ = jQuery.noConflict();
    function add_text_option(sender_id) {
        id++;
        var li = document.getElementById(sender_id + "_add_option").parentElement;
        $(li).before(
            '<li><?php echo ssv_get_option('\' + sender_id + \'', array('id' => '\' + id + \'', 'type' => 'text', 'value' => ""), array(), false); ?></li>'
        );
    }
</script>
<!-- Add Role Option. -->
<!--suppress JSUnusedLocalSymbols -->
<script>
    var $ = jQuery.noConflict();
    function add_role_option(sender_id) {
        var li = document.getElementById(sender_id + "_add_option").parentElement;
        id++;
        <?php $object_name = '\' + sender_id + \'' . "_option" . '\' + id + \''; ?>
        $(li).before(
            '<li><?php echo ssv_get_role_select($object_name, "option", "", false, array(), false); ?></li>'
        );
    }
</script>

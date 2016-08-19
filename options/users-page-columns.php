<?php
if (!defined('ABSPATH')) {
    exit;
}
if (!current_user_can('manage_options')) {
    ?>
    <p>You are unauthorized to view or edit this page.</p>
    <?php
    return;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && check_admin_referer('ssv_save_frontend_members_users_page_columns_options')) {
    global $options;
    update_option('ssv_frontend_members_main_column', sanitize_text_field($_POST['ssv_frontend_members_main_column']));
    update_option('ssv_frontend_members_user_columns', sanitize_text_field(json_encode($_POST['ssv_frontend_members_user_columns'])));
}
?>
<form method="post" action="#">
    <table class="form-table">
        <tr>
            <th scope="row">Main Column</th>
            <td>
                <select name="ssv_frontend_members_main_column" title="Main Column">
                    <option value="plugin_default" <?php if (esc_attr(stripslashes(get_option('ssv_frontend_members_main_column'))) == 'plugin_default') {
                        echo "selected";
                    } ?>>Plugin Default
                    </option>
                    <option value="wordpress_default"<?php if (esc_attr(stripslashes(get_option('ssv_frontend_members_main_column'))) == 'wordpress_default') {
                        echo "selected";
                    } ?>>Wordpress Default
                    </option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row">Columns to Display</th>
            <td>
                <?php
                $selected = json_decode(get_option('ssv_frontend_members_user_columns'));
                $selected = $selected ?: array();
                $fieldNames = FrontendMembersField::getAllFieldNames();
                ?>
                <select size="<?= count($fieldNames) + 3 ?>" name="ssv_frontend_members_user_columns[]" multiple title="Columns to Display">
                    <?php
                    foreach ($fieldNames as $fieldName) {
                        echo '<option value="' . $fieldName . '" ';
                        if (in_array($fieldName, $selected)) {
                            echo 'selected';
                        }
                        echo '>' . $fieldName . '</option>';
                    }
                    echo '<option value="blank" disabled>--- WP Defaults ---</option>';
                    echo '<option value="wp_Role" ';
                    if (in_array('wp_Role', $selected)) {
                        echo 'selected';
                    }
                    echo '>Role</option>';
                    echo '<option value="wp_Posts" ';
                    if (in_array('wp_Posts', $selected)) {
                        echo 'selected';
                    }
                    echo '>Posts</option>';
                    ?>
                </select>
            </td>
        </tr>
    </table>
    <?php wp_nonce_field('ssv_save_frontend_members_users_page_columns_options'); ?>
    <?php submit_button(); ?>
</form>
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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && check_admin_referer('ssv_save_frontend_members_general_options')) {
    global $options;
    update_option('ssv_frontend_members_custom_register_page', filter_var($_POST['ssv_frontend_members_custom_register_page'], FILTER_VALIDATE_BOOLEAN));
    update_option('ssv_frontend_members_default_member_role', sanitize_text_field($_POST['ssv_frontend_members_default_member_role']));
    update_option('ssv_frontend_members_board_role', sanitize_text_field($_POST['ssv_frontend_members_board_role']));
    update_option('ssv_frontend_members_custom_users_filters', sanitize_text_field($_POST['ssv_frontend_members_custom_users_filters']));
}
?>
<form method="post" action="#">
    <table class="form-table">
        <tr>
            <th scope="row">Custom Register Page</th>
            <td>
                <label>
                    <input type="hidden" name="ssv_frontend_members_custom_register_page" value="false"/>
                    <input type="checkbox" name="ssv_frontend_members_custom_register_page" value="true" <?= get_option('ssv_frontend_members_custom_register_page', 'false') ? "checked" : '' ?>/>
                </label>
            </td>
        </tr>
        <tr>
            <th scope="row">Default Member Role</th>
            <td>
                <select name="ssv_frontend_members_default_member_role" title="Default Member Role">
                    <?php wp_dropdown_roles(esc_attr(stripslashes(get_option('ssv_frontend_members_default_member_role')))); ?>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row">Board Role</th>
            <td>
                <select name="ssv_frontend_members_board_role" title="Board Role">
                    <?php wp_dropdown_roles(esc_attr(stripslashes(get_option('ssv_frontend_members_board_role')))); ?>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row">Custom Users Filters</th>
            <td>
                <select name="ssv_frontend_members_custom_users_filters" title="Board Role">
                    <option value="hide" <?= get_option('ssv_frontend_members_custom_users_filters', 'under') == 'hide' ? 'selected' : '' ?>>Hide</option>
                    <option value="replace" <?= get_option('ssv_frontend_members_custom_users_filters', 'under') == 'replace' ? 'selected' : '' ?>>Replace User Role Links</option>
                    <option value="under" <?= get_option('ssv_frontend_members_custom_users_filters', 'under') == 'under' ? 'selected' : '' ?>>Under User Role Links</option>
                </select>
            </td>
        </tr>
    </table>
    <?php wp_nonce_field('ssv_save_frontend_members_general_options'); ?>
    <?php submit_button(); ?>
</form>
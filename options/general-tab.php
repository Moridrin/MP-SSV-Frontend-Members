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
    if (isset($_POST['ssv_frontend_members_custom_register_page'])) {
        update_option('ssv_frontend_members_custom_register_page', 'true');
    } else {
        update_option('ssv_frontend_members_custom_register_page', 'false');
    }
    update_option('ssv_frontend_members_default_member_role', sanitize_text_field($_POST['ssv_frontend_members_default_member_role']));
    update_option('ssv_frontend_members_board_role', sanitize_text_field($_POST['ssv_frontend_members_board_role']));
    if (isset($_POST['ssv_frontend_members_recaptcha'])) {
        update_option('ssv_frontend_members_recaptcha', 'true');
    } else {
        update_option('ssv_frontend_members_recaptcha', 'false');
    }
    update_option('ssv_recaptcha_site_key', sanitize_text_field($_POST['ssv_recaptcha_site_key']));
    update_option('ssv_recaptcha_secret_key', sanitize_text_field($_POST['ssv_recaptcha_secret_key']));
}
?>
<form method="post" action="#">
    <table class="form-table">
        <tr>
            <th scope="row">Custom Register Page</th>
            <td>
                <label>
                    <input type="checkbox" name="ssv_frontend_members_custom_register_page" value="true" <?php if (get_option('ssv_frontend_members_custom_register_page', 'false') == 'true') {
                        echo "checked";
                    } ?>/>
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
            <th scope="row">Enable Recapthca</th>
            <td>
                <label>
                    <input type="checkbox" name="ssv_frontend_members_recaptcha" value="true" <?php if (get_option('ssv_frontend_members_recaptcha') == 'true') {
                        echo "checked";
                    } ?>/>
                </label>
            </td>
        </tr>
        <tr>
            <th scope="row">reCAPTCHA Site Key</th>
            <td>
                <input type="text" name="ssv_recaptcha_site_key" value="<?php echo get_option('ssv_recaptcha_site_key'); ?>" title="reCAPTCHA Site Key">
            </td>
        </tr>
        <tr>
            <th scope="row">reCAPTCHA Secret Key</th>
            <td>
                <input type="text" name="ssv_recaptcha_secret_key" value="<?php echo get_option('ssv_recaptcha_secret_key'); ?>" title="reCAPTCHA Secret Key">
            </td>
        </tr>
    </table>
    <?php wp_nonce_field('ssv_save_frontend_members_general_options'); ?>
    <?php submit_button(); ?>
</form>
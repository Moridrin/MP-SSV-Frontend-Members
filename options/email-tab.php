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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && check_admin_referer('ssv_save_frontend_members_email_options')) {
    global $options;
    update_option('ssv_frontend_members_member_admin', sanitize_text_field($_POST['ssv_frontend_members_member_admin']));
    if (isset($_POST['ssv_frontend_members_new_member_registration_email'])) {
        update_option('ssv_frontend_members_new_member_registration_email', 'true');
    } else {
        update_option('ssv_frontend_members_new_member_registration_email', 'false');
    }
    if (isset($_POST['ssv_frontend_members_member_role_changed_email'])) {
        update_option('ssv_frontend_members_member_role_changed_email', 'true');
    } else {
        update_option('ssv_frontend_members_member_role_changed_email', 'false');
    }
}
?>
<form method="post" action="#">
    <table class="form-table">
        <tr>
            <th scope="row">Members Admin (email)</th>
            <td>
                <input type="email" name="ssv_frontend_members_member_admin" value="<?php echo get_option('ssv_frontend_members_member_admin'); ?>" title="Members Admin (email)">
            </td>
        </tr>
        <tr>
            <th scope="row">New Member Registration</th>
            <td>
                <label>
                    <input type="checkbox" name="ssv_frontend_members_new_member_registration_email" value="true" <?php if (get_option('ssv_frontend_members_new_member_registration_email') == 'true') {
                        echo "checked";
                    } ?>/>
                </label>
            </td>
        </tr>
        <tr>
            <th scope="row">Member Role Change</th>
            <td>
                <label>
                    <input type="checkbox" name="ssv_frontend_members_member_role_changed_email" value="true" <?php if (get_option('ssv_frontend_members_member_role_changed_email') == 'true') {
                        echo "checked";
                    } ?>/>
                </label>
            </td>
        </tr>
    </table>
    <?php wp_nonce_field('ssv_save_frontend_members_email_options'); ?>
    <?php submit_button(); ?>
</form>
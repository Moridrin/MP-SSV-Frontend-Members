<?php
if (!defined('ABSPATH')) {
    exit;
}
function ssv_change_password_page_content($content)
{
    global $post;
    if ($post->post_name != 'change-password') {
        return $content;
    } elseif (strpos($content, '[ssv-frontend-members-change-password]') === false) {
        return $content;
    } elseif (!is_user_logged_in()) {
        return 'You need to be logged in to change your password.' . ssv_redirect('login');
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && check_admin_referer('ssv_change_password')) {
        $member               = FrontendMember::get_current_user();
        $current_password     = $_POST['current_password'];
        $new_password         = $_POST['new_password'];
        $confirm_new_password = $_POST['confirm_new_password'];
        if (!$member->checkPassword($current_password)) {
            $message = new Message('Current Password Incorrect!', Message::ERROR_MESSAGE);
            $content = $message->getHTML();
        } elseif ($new_password !== $confirm_new_password) {
            $message = new Message('Passwords do not match!', Message::ERROR_MESSAGE);
            $content = $message->getHTML();
        } else {
            wp_set_password($new_password, $member->ID);
            $message = new Message('Passwords Successfully Changed!<br/>Please <a href="/login">login</a> again with your new password.', Message::NOTIFICATION_MESSAGE);
            $content = $message->getHTML();
        }
    } else {
        $content = '';
    }
    ob_start();
    if (current_theme_supports('materialize')) {
        ?>
        <!--suppress HtmlUnknownTarget -->
        <form name="change_password_form" id="change_password_form" action="/change-password" method="post">
            <div class="row">
                <div class="input-field col s12">
                    <input type="password" name="current_password" id="current_password">
                    <label for="current_password">Current Password</label>
                </div>
                <div class="input-field col s12">
                    <input type="password" name="new_password" id="new_password">
                    <label for="new_password">New Password</label>
                </div>
                <div class="input-field col s12">
                    <input type="password" name="confirm_new_password" id="confirm_new_password">
                    <label for="confirm_new_password">Confirm New Password</label>
                </div>
            </div>
            <?php wp_nonce_field('ssv_change_password'); ?>
            <button class="btn waves-effect waves-light" type="submit" name="wp-submit" id="wp-submit">Change Password</button>
        </form>
        <?php
    }
    $content .= ob_get_clean();
    return $content;
}

add_filter('the_content', 'ssv_change_password_page_content');
?>
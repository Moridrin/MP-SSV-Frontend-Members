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
        $member = FrontendMember::get_current_user();
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_new_password = $_POST['confirm_new_password'];
        if (!$member->checkPassword($current_password)) {
            $message = new Message('Current Password Incorrect!', Message::ERROR_MESSAGE);
            $content = $message->htmlPrint();
        } elseif ($new_password !== $confirm_new_password) {
            $message = new Message('Passwords do not match!', Message::ERROR_MESSAGE);
            $content = $message->htmlPrint();
        } else {
            wp_set_password($new_password, $member->ID);
            $message = new Message('Passwords Successfully Changed!<br/>Please <a href="/login">login</a> again with your new password.', Message::NOTIFICATION_MESSAGE);
            $content = $message->htmlPrint();
        }
    } else {
        $content = '';
    }
    ob_start();
    if (current_theme_supports('mui')) {
        ?>
        <!--suppress HtmlUnknownTarget -->
        <form name="change_password_form" id="change_password_form" action="/change-password" method="post">
            <div class="mui-textfield mui-textfield--float-label">
                <input type="password" name="current_password" id="current_password">
                <label for="current_password">Current Password</label>
            </div>
            <div class="mui-textfield mui-textfield--float-label">
                <input type="password" name="new_password" id="new_password">
                <label for="new_password">New Password</label>
            </div>
            <div class="mui-textfield mui-textfield--float-label">
                <input type="password" name="confirm_new_password" id="confirm_new_password">
                <label for="confirm_new_password">Confirm New Password</label>
            </div>
            <?php wp_nonce_field('ssv_change_password'); ?>
            <button class="mui-btn mui-btn--primary button-primary" type="submit" name="wp-submit" id="wp-submit">Change Password</button>
        </form>
        <?php
    } else {
        ?>
        <!--suppress HtmlUnknownTarget -->
        <form name="loginform" id="loginform" action="/wp-login.php" method="post">
            <div class="mui-textfield mui-textfield--float-label">
                <label for="current_password">Current Password</label>
                <input type="password" name="current_password" id="current_password">
            </div>
            <div class="mui-textfield mui-textfield--float-label">
                <label for="new_password">New Password</label>
                <input type="password" name="new_password" id="new_password">
            </div>
            <div class="mui-textfield mui-textfield--float-label">
                <label for="confirm_new_password">Confirm New Password</label>
                <input type="password" name="confirm_new_password" id="confirm_new_password">
            </div>
            <?php wp_nonce_field('ssv_change_password'); ?>
            <button class="mui-btn mui-btn--primary button-primary" type="submit" name="wp-submit" id="wp-submit">Change Password</button>
        </form>
        <?php
    }
    $content .= ob_get_clean();
    return $content;
}

add_filter('the_content', 'ssv_change_password_page_content');
?>
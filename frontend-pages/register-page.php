<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * This function sets up the profile page.
 *
 * @param string $content is the post content.
 *
 * @return string the edited post content.
 */
function ssv_register_page_setup($content)
{
    global $post;
    if ($post->post_name != 'register') {
        return $content;
    } else {
        if (strpos($content, '[ssv-frontend-members-register]') === false) {
            return $content;
        }
    }
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && check_admin_referer('ssv_create_members_profile')) {
        $content = ssv_create_members_profile()->htmlPrint();
        $content .= ssv_register_page_content();
    } else {
        $content = ssv_register_page_content();
    }

    return $content;
}

/**
 * @return string the content of the Profile Page.
 */
function ssv_register_page_content()
{
    ob_start();
    $items = FrontendMembersField::getAll(array('registration_page' => 'yes'));
    ?>
    <!--suppress HtmlUnknownTarget -->
    <form name="members_form" id="members_form" action="/register" method="post" enctype="multipart/form-data">
        <?php
        foreach ($items as $item) {
            if (!$item instanceof FrontendMembersFieldTab) {
                if (get_option('ssv_frontend_members_custom_register_page', 'false') != 'true' || $item->registration_page == 'yes') {
                    /** @noinspection PhpUndefinedMethodInspection */
                    echo $item->getHTML();
                }
            }
        }
        ?>
        <?php if (!is_user_logged_in() || (is_user_logged_in() && !FrontendMember::get_current_user()->isBoard())): ?>
            <div class="mui-textfield mui-textfield--float-label">
                <input id="password" type="password" name="password" class="mui--is-empty mui--is-dirty" required>
                <label for="password">Password</label>
            </div>
            <div class="mui-textfield mui-textfield--float-label">
                <input id="password_confirm" type="password" name="password_confirm" class="mui--is-empty mui--is-dirty" required>
                <label for="password_confirm">Confirm Password</label>
            </div>
            <?php if (get_option('ssv_frontend_members_recaptcha') == 'yes'): ?>
                <?php $site_key = get_option('ssv_recaptcha_site_key'); ?>
                <div class="g-recaptcha" data-sitekey="<?php echo $site_key; ?>"></div>
            <?php endif; ?>
        <?php endif; ?>
        <input type="hidden" name="register" value="yes"/>
        <button class="mui-btn mui-btn--primary" type="submit" name="submit" id="submit">Register</button>
        <?php wp_nonce_field('ssv_create_members_profile'); ?>
    </form>
    <?php

    return ob_get_clean();
}

function ssv_create_members_profile()
{
    if (is_user_logged_in() && FrontendMember::get_current_user()->isBoard()) {
        $password          = wp_generate_password();
        $_POST['password'] = $password;
        $email             = $_POST['email'];
        $display_name      = $_POST['first_name'] . ' ' . $_POST['last_name'];
    } elseif ($_POST['password'] != $_POST['password_confirm']) {
        return new Message('Password does not match', Message::ERROR_MESSAGE);
    }
    if (isset($_POST['iban']) && !ssv_is_valid_iban($_POST['iban'])) {
        return new Message('Invalid IBAN', Message::ERROR_MESSAGE);
    }
    if (get_option('ssv_frontend_members_recaptcha') == 'yes') {
        $secretKey    = get_option('ssv_recaptcha_secret_key');
        $response     = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . $secretKey . "&response=" . $_POST['g-recaptcha-response']);
        $responseKeys = json_decode($response, true);
        if (intval($responseKeys["success"]) !== 1) {
            return new Message('You failed the reCaptcha. Are you a robot?', Message::ERROR_MESSAGE);
        }
    }
    $user = FrontendMember::registerFromPOST();
    $items = FrontendMembersField::getAll(array('field_type' => 'input'));
    /** @var FrontendMembersFieldInput $item */
    foreach ($items as $item) {
        if ($item->isValueRequiredForMember() && !isset($_POST[$item->name]) && !isset($_POST[$item->name . '_reset'])) {
            return new Message($item->title . ' is required but there was no value given.', Message::ERROR_MESSAGE);
        }
        $value = isset($_POST[$item->name]) ? $_POST[$item->name] : $_POST[$item->name . '_reset'];
        $user->updateMeta($item->name, sanitize_text_field($value));
    }
    $user->updateMeta("display_name", $user->getMeta('first_name') . ' ' . $user->getMeta('last_name'));
    foreach ($_FILES as $name => $file) {
        if (!function_exists('wp_handle_upload')) {
            /** @noinspection PhpIncludeInspection */
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        $file_location = wp_handle_upload($file, array('test_form' => false));
        if ($file_location && !isset($file_location['error'])) {
            $user->updateMeta($name, $file_location["url"]);
            $user->updateMeta($name . '_path', $file_location["file"]);
        }
    }
    $user->remove_role('subscriber');
    $user->set_role(get_option('ssv_frontend_members_default_member_role'));
    $to      = get_option('ssv_frontend_members_member_admin');
    $subject = "New Member Registration";
    $url     = get_site_url() . '/profile/?user_id=' . $user->ID;
    $message = 'A new member has registered:<br/><br/><a href="' . esc_url($url) . '" target="_blank">' . $user->display_name . '</a><br/><br/>Greetings.';

    $headers = "From: $to" . "\r\n";
    add_filter('wp_mail_content_type', create_function('', 'return "text/html";'));
    wp_mail($to, $subject, $message, $headers);
    if (is_plugin_active('ssv-mailchimp/ssv-mailchimp.php')) {
        ssv_update_mailchimp_member($user);
    }
    if (is_user_logged_in() && FrontendMember::get_current_user()->isBoard()) {
        /** @noinspection PhpUndefinedVariableInspection */
        $to      = $email;
        $subject = 'Account registration';
        /** @noinspection PhpUndefinedVariableInspection */
        $message = 'Hello ' . $display_name . ',<br/><br/>';
        $message .= 'Your account for ' . get_bloginfo('name') . ' has been created.<br/>';
        $url = get_site_url() . '/login';
        $message .= 'You can sign in <a href="' . $url . '">here</a> with username: ' . $email . '<br/>';
        /** @noinspection PhpUndefinedVariableInspection */
        $message .= 'And password: ' . $password . '<br/>';
        $message .= 'Please update your profile with the necessary information.';
        wp_mail($to, $subject, $message);
    }
    unset($_POST);
    $return_message = 'You\'ve successfully registered.<br/>Click <a href="/login">here</a> to sign in.';
    return new Message($return_message, Message::NOTIFICATION_MESSAGE);
}

add_filter('the_content', 'ssv_register_page_setup', 9);
?>
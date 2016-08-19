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
        ssv_create_members_profile();
        ob_start();
        ?>
        <div class="mui-panel notification">
            You've successfully registered.
            Click <a href="/login">Here</a> to sign in.
        </div>
        <?php
        $content = ob_get_clean();
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
    $items = FrontendMembersField::getAll();
    ?>
    <form name="members_form" id="members_form" action="/register" method="post" enctype="multipart/form-data">
        <?php
        foreach ($items as $item) {
            if (!$item instanceof FrontendMembersFieldTab) {
                if (get_option('ssv_frontend_members_register_page') != 'custom' || $item->registration_page == 'yes') {
                    /** @noinspection PhpUndefinedMethodInspection */
                    echo $item->getHTML();
                }
            }
        }
        ?>
        <div class="mui-textfield mui-textfield--float-label">
            <input id="password" type="password" name="password" class="mui--is-empty mui--is-dirty" required>
            <label for="password">Password</label>
        </div>
        <div class="mui-textfield mui-textfield--float-label">
            <input id="password_confirm" type="password" name="password_confirm" class="mui--is-empty mui--is-dirty" required>
            <label for="password_confirm">Confirm Password</label>
        </div>
        <?php $site_key = get_option('ssv_recaptcha_site_key'); ?>
        <div class="g-recaptcha" data-sitekey="<?php echo $site_key; ?>"></div>
        <input type="hidden" name="register" value="yes"/>
        <button class="mui-btn mui-btn--primary" type="submit" name="submit" id="submit">Register</button>
        <?php wp_nonce_field('ssv_create_members_profile'); ?>
    </form>
    <?php

    return ob_get_clean();
}

function ssv_create_members_profile()
{
    if ($_POST['password'] != $_POST['password_confirm']) {
        echo 'Password does not match';

        return;
    }
    $secretKey = get_option('ssv_recaptcha_secret_key');
    $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . $secretKey . "&response=" . $_POST['g-recaptcha-response']);
    $responseKeys = json_decode($response, true);
    if (intval($responseKeys["success"]) !== 1) {
        echo '<h2>You failed the reCaptcha. Are you a robot?</h2>';

        return;
    }
    $user = FrontendMember::registerFromPOST();
    foreach ($_POST as $name => $val) {
        if (strpos($name, "_reset") !== false) {
            $name = str_replace("_reset", "", $name);
        }
        $user->updateMeta($name, sanitize_text_field($val));
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
    $user->add_role(get_option('ssv_frontend_members_default_member_role'));
    $to = get_option('ssv_member_admin');
    $subject = "New Member Registration";
    $url = get_site_url() . '/profile/?user_id=' . $user->ID;
    $message = 'A new member has registered:<br/><br/><a href="' . esc_url($url) . '" target="_blank">' . $user->display_name . '</a><br/><br/>Greetings.';

    $headers = "From: $to" . "\r\n";
    add_filter('wp_mail_content_type', create_function('', 'return "text/html";'));
    wp_mail($to, $subject, $message, $headers);
    if (is_plugin_active('ssv-mailchimp/ssv-mailchimp.php')) {
        ssv_update_mailchimp_member($user);
    }
    unset($_POST);
}

add_filter('the_content', 'ssv_register_page_setup', 9);
?>
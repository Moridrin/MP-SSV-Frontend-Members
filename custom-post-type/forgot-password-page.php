<?php

namespace mp_ssv_users;

use mp_ssv_general\Message;
use mp_ssv_general\SSV_General;
use mp_ssv_general\User;
use WP_User;

if (!defined('ABSPATH')) {
    exit;
}

function mp_ssv_user_get_fields($content)
{
    ob_start();
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        SSV_General::var_export('test', 1);
        $user = new User($_POST['user_login']);
        $siteName = get_bloginfo('name');
        $newPassword = wp_generate_password();
        $loginURL = SSV_General::getLoginURL();
        $changePasswordURL = SSV_General::getChangePasswordURL();
        wp_set_password($newPassword, $user->ID);
        $message = "You've requested a new password for the $siteName website.<br/>";
        $message .= "Your new password is:<br/>$newPassword<br/>. You can sign in here: $loginURL.<br/>And change your password here: $changePasswordURL.";
        $success = wp_mail($user->user_email, 'New Password', $message);
        if ($success) {
            $message = new Message('Email send to ' . $user->user_email);
            echo $message->getHTML();
        } else  {
            $message = new Message('Error sending email, please try again.');
            echo $message->getHTML();
        }
    }
    $users = get_users();
    if (current_theme_supports('materialize')) {
        ?>
        <form name="lostpasswordform" id="lostpasswordform" action="#" method="post">
            <div class="input-field col s12">
                <input type="text" name="user_login" id="user_login" list="email_addresses">
                <?php if (current_user_can('edit_users')): ?>
                    <datalist id="email_addresses">
                        <?php /** @var WP_User $user */ ?>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user->user_login ?>"><?= $user->display_name ?></option>
                        <?php endforeach; ?>
                    </datalist>
                <?php endif; ?>
                <label for="user_login">Username or Email</label>
            </div>
<!--            <input type="hidden" name="redirect_to" value="--><?php //echo get_site_url(); ?><!--"/>-->
            <button class="btn waves-effect waves-light" type="submit" name="wp-submit" id="wp-submit">Send New Password</button>
        </form>
        <?php
    } else {
        ?>
        <form name="lostpasswordform" id="lostpasswordform" action="#" method="post">
            <label for="user_login">Username or Email</label><br/>
            <input type="text" name="user_login" id="user_login" list="email_addresses"><br/><br/>
            <?php if (current_user_can('edit_users')): ?>
                <datalist id="email_addresses">
                    <?php /** @var WP_User $user */ ?>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user->user_login ?>"><?= $user->display_name ?></option>
                    <?php endforeach; ?>
                </datalist>
            <?php endif; ?>
            <button class="btn waves-effect waves-light" type="submit" name="wp-submit" id="wp-submit">Send New Password</button>
        </form>
        <?php
    }
    return str_replace(SSV_Users::TAG_LOST_PASSWORD, ob_get_clean(), $content);
}

<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 5-2-17
 * Time: 10:32
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param Form $form
 *
 * @return Message[]
 */
function mp_ssv_user_save_fields($form)
{
    if (!SSV_General::isValidPOST(SSV_Users::ADMIN_REFERER_PROFILE)) {
        return array();
    }
    if (empty($_POST) || !is_user_logged_in()) {
        return array(new Message('No values to save', Message::NOTIFICATION_MESSAGE));
    }

    if (isset($_GET['member']) && !User::isBoard()) {
        return array(new Message('You have no rights to view this user.', Message::ERROR_MESSAGE));
    }

    $form->setValues($_POST);
    $messages = $form->isValid();
    if ($messages === true) {
        $messages             = array();
        $user                 = User::getCurrent();
        $current_password     = $_POST['current_password'];
        $new_password         = $_POST['new_password'];
        $confirm_new_password = $_POST['confirm_new_password'];
        if (!$user->checkPassword($current_password)) {
            $messages[] = new Message('Current Password Incorrect!', Message::ERROR_MESSAGE);
        }
        if ($new_password !== $confirm_new_password) {
            $messages[] = new Message('Passwords do not match!', Message::ERROR_MESSAGE);
        }
        if (empty($messages)) {
            wp_set_password($new_password, $user->ID);
            $messages[] = new Message('<p>Passwords Successfully Changed! Please <a href="/login">login</a> again with your new password.</p>', Message::NOTIFICATION_MESSAGE);
        }
    }
    return $messages;
}

function mp_ssv_user_get_fields($content, $form)
{
    $html = '';
    if (isset($_GET['member'])) {
        $error = (new Message('<p>You cannot change the password for another user. Try <a href="/lost-password">Lost Password</a> to send an email with a link to reset the password.</p>', Message::ERROR_MESSAGE));
        return str_replace(SSV_Users::TAG_CHANGE_PASSWORD, $error, $content);
    }
    $html .= $form->getHTML(SSV_Users::ADMIN_REFERER_PROFILE);
    return str_replace(SSV_Users::TAG_CHANGE_PASSWORD, $html, $content);
}

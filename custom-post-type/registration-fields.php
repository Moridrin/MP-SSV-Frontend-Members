<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 26-1-17
 * Time: 8:55
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
    if (!SSV_General::isValidPOST(SSV_Users::ADMIN_REFERER_REGISTRATION)) {
        return array();
    }
    if (empty($_POST) && !is_user_logged_in()) {
        return array(new Message('No values to save', Message::NOTIFICATION_MESSAGE));
    }

    $form->setValues($_POST);
    $messages = $form->isValid();

    $username        = $form->getValue('username');
    $password        = $form->getValue('password');
    $confirmPassword = $form->getValue('password_confirm');
    $email           = $form->getValue('email');

    $requiredFieldsMessages = array();
    if ($password !== $confirmPassword) {
        $requiredFieldsMessages[] = new Message('Passwords mismatch.', Message::ERROR_MESSAGE);
    }
    if (email_exists($email)) {
        $requiredFieldsMessages[] = new Message('Email already used.', Message::ERROR_MESSAGE);
    }
    if (username_exists($username)) {
        $requiredFieldsMessages[] = new Message('Username already used.', Message::ERROR_MESSAGE);
    }
    if (!empty($requiredFieldsMessages)) {
        $messages = is_array($messages) ?: array();
        $messages = array_merge($messages, $requiredFieldsMessages);
    }

    if ($messages === true) {
        $user       = User::register($username, $password, $email);
        $form->user = $user;
        $messages   = $form->save();
        do_action('ssv_users_registered');
        if (get_option(SSV_Users::OPTION_NEW_MEMBER_ADMIN_EMAIL, true)) {
            $userAdmin = User::getByID(get_option(SSV_Users::OPTION_MEMBER_ADMIN, true));
            $to        = $userAdmin->user_email;
            $subject   = 'New User registration';
            $message   = '<p>Hello ' . $userAdmin->display_name . ',</p><br/>';
            $message .= '<p>A new user has registered for ' . get_bloginfo() . ':</p>';
            $message .= $form->getEmail();
            $message .= '</br></br>Send by WordPress (SSV Plugin).';
            $headers = array('Content-Type: text/html; charset=UTF-8');
            wp_mail($to, $subject, $message, $headers);
        }
        if (get_option(SSV_Users::OPTION_NEW_MEMBER_REGISTRANT_EMAIL, true)) {
            $userAdmin = User::getByID(get_option(SSV_Users::OPTION_MEMBER_ADMIN, true));
            $to        = $user->user_email;
            $subject   = 'Registration Successful';
            $message   = '<p>Hello ' . $user->display_name . ',</p><br/>';
            $message .= '<p>Your registration for ' . get_bloginfo() . ' was successful.</p>';
            $message .= '<p>You have registered with the following fields:</p>';
            $message .= $form->getEmail(false);
            $message .= '</br></br>Greetings, ' . $userAdmin->display_name . '.';
            $headers = array('Content-Type: text/html; charset=UTF-8');
            wp_mail($to, $subject, $message, $headers);
        }
    }

    if (empty($messages)) {
        $messages[] = new Message('Registration Successful.');
    } elseif (!empty($requiredFieldsMessages) && User::isBoard()) {
        $user         = User::register($username, $password, $email);
        $form->user   = $user;
        $saveMessages = $form->save();
        do_action('ssv_users_registered');
        $messages = array_merge($messages, $saveMessages);
        if (empty($saveMessages)) {
            $messages[] = new Message('Profile Forcibly Saved (As Board member).');
        } else {
            $messages[] = new Message('Profile Partially Saved (As Board member).');
        }
    }
    return $messages;
}

/**
 * @param string $content
 * @param Form   $form
 *
 * @return string HTML
 */
function mp_ssv_user_get_fields($content, $form)
{
    $html = '';
    if (isset($_GET['member'])) {
        if (!is_user_logged_in()) {
            return (new Message('You must sign in to view this profile.', Message::ERROR_MESSAGE))->getHTML();
        } elseif (!User::isBoard()) {
            $html .= (new Message('You have no access to view this profile.', Message::ERROR_MESSAGE))->getHTML();
        }
    }
    $html .= $form->getHTML(SSV_Users::ADMIN_REFERER_REGISTRATION, 'Register');
    return str_replace(SSV_Users::TAG_REGISTER_FIELDS, $html, $content);
}

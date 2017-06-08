<?php

namespace mp_ssv_users;

use mp_ssv_general\Form;
use mp_ssv_general\Message;
use mp_ssv_general\SSV_General;

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

    if (isset($_GET['member']) && !current_user_can('list_users')) {
        return array(new Message('You have no rights to view this user.', Message::ERROR_MESSAGE));
    }

    $tabID = null;
    if (isset($_POST['tab'])) {
        $tabID = $_POST['tab'];
    }

    $form->setValues($_POST);
    $messages = $form->isValid($tabID);
    if ($messages === true) {
        $messages = $form->save($tabID);
        do_action(SSV_General::HOOK_USERS_SAVE_MEMBER, $form->user);
        if (empty($messages)) {
            $messages = array(new Message('Profile Saved.'));
        }
    } elseif (current_user_can('edit_users')) {
        $saveMessages = $form->save($tabID);
        do_action(SSV_General::HOOK_USERS_SAVE_MEMBER, $form->user);
        $saveMessages = $saveMessages === true ? array() : $saveMessages;
        $messages     = array_merge($messages, $saveMessages);
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
        } elseif (!current_user_can('edit_users')) {
            $html .= (new Message('You have no access to view this profile.', Message::ERROR_MESSAGE))->getHTML();
        }
    }
    $form->setValues();
    $html .= $form->getHTML(SSV_Users::ADMIN_REFERER_PROFILE);
    return str_replace(SSV_Users::TAG_PROFILE_FIELDS, $html, $content);
}

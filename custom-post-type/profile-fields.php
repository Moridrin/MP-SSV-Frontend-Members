<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 26-1-17
 * Time: 8:55
 */

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

    $tabID = -1;
    if (isset($_POST['tab'])) {
        $tabID = $_POST['tab'];
    }

    $form->setValues($_POST);
    $messages = $form->isValid($tabID);
    if ($messages === true) {
        $messages = $form->save($tabID);
        if ($messages === true) {
            $messages = array(new Message('Profile Saved.'));
        }
    } elseif (User::isBoard()) {
        $saveMessages = $form->save($tabID);
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
        } elseif (!User::isBoard()) {
            $html .= (new Message('You have no access to view this profile.', Message::ERROR_MESSAGE))->getHTML();
        }
    }
    $form->setValues();
    $html .= $form->getHTML(SSV_Users::ADMIN_REFERER_PROFILE);
    return str_replace(SSV_Users::PROFILE_FIELDS_TAG, $html, $content);
}
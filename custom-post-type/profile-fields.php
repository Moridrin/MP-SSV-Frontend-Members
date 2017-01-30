<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 26-1-17
 * Time: 8:55
 */

/**
 * @param $fields
 * @param $values
 *
 * @return Message[]
 */
function mp_ssv_user_save_fields($fields, $values)
{
    if (!SSV_General::isValidPOST(SSV_Users::ADMIN_REFERER_PROFILE)) {
        return array();
    }
    if (empty($values) || !is_user_logged_in()) {
        return array(new Message('No values to save', Message::NOTIFICATION_MESSAGE));
    }

    if (isset($_GET['member'])) {
        if (User::getCurrent()->isBoard()) {
            $user = User::getByID($_GET['member']);
        } else {
            return array(new Message('You have no rights to view this user.', Message::ERROR_MESSAGE));
        }
    } else {
        $user = User::getCurrent();
    }

    $tabID = -1;
    if (isset($values['tab'])) {
        $tabID = $values['tab'];
    }

    $inputFields = array();
    $messages = array();
    foreach ($fields as $field) {
        if ($field instanceof TabField && $tabID == $field->id) {
            foreach ($field->fields as $childField) {
                if ($childField instanceof InputField) {
                    $childField->setValue($values);
                    $inputFields[] = $childField;
                    if ($childField->isValid() !== true) {
                        $messages = array_merge($messages, $childField->isValid());
                    }
                }
            }
        } elseif ($field instanceof InputField) {
            $field->setValue($values);
            $inputFields[] = $field;
            if ($field->isValid() !== true) {
                $messages = array_merge($messages, $field->isValid());
            }
        }
    }
    if (empty($messages) || $user->isBoard()) {
        $user->update($inputFields);
        $messages[] = new Message('Profile Updated.', Message::NOTIFICATION_MESSAGE);
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
        } elseif (!User::getCurrent()->isBoard()) {
            $html .= (new Message('You have no access to view this profile.', Message::ERROR_MESSAGE))->getHTML();
            $user = User::getCurrent();
        } else {
            $user = User::getByID($_GET['member']);
        }
    }
    $html = $form->getHTML(SSV_Users::ADMIN_REFERER_PROFILE);
    return str_replace(SSV_Users::PROFILE_FIELDS_TAG, $html, $content);
}
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
    if (!SSV_General::isValidPOST(SSV_Users::ADMIN_REFERER_REGISTRATION)) {
        return array(); //No Messages to show.
    }
    if (empty($values)) {
        return array(new Message('No values to save', Message::NOTIFICATION_MESSAGE));
    }

    $inputFields = array();
    $errors      = array();
    $username = '';
    $password = '';
    $email = '';
    foreach ($fields as $field) {
        if ($field instanceof InputField) {
            $field->setValue($values);
            if ($field->isValid() === true) {
                if ($field->name == 'username') {
                    $username = $field->value;
                } elseif ($field->name == 'password') {
                    $password = $field->value;
                } elseif ($field->name == 'email') {
                    $email = $field->value;
                } else {
                    $inputFields[] = $field;
                }
            } else {
                $errors = array_merge($errors, $field->isValid());
            }
        }
    }
    if (empty($messages) || (is_user_logged_in() && User::isBoard())) {
        $user = User::register($username, $password, $email);
        if ($user instanceof Message) {
            return array($user);
        }
        /** @var InputField $field */
        foreach ($inputFields as $field) {
            $response = $user->updateMeta($field->name, $field->value);
            if ($response !== true) {
                $messages[] = $response;
            }
        }
        $messages[] = new Message('Registration Successful.', Message::NOTIFICATION_MESSAGE);
//        SSV_General::redirect(get_permalink());
    }
    return $messages;
}

/**
 * @param $content
 * @param $fields
 *
 * @return string
 */
function mp_ssv_user_get_fields($content, $fields)
{
    $html = SSV_General::getCustomFieldsHTML($fields, SSV_Users::ADMIN_REFERER_REGISTRATION, 'Register');
    return str_replace(SSV_Users::REGISTER_FIELDS_TAG, $html, $content);
}

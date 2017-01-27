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
    $errors      = array();
    foreach ($fields as $field) {
        if ($field instanceof TabField && $tabID == $field->id) {
            foreach ($field->fields as $childField) {
                if ($childField instanceof InputField) {
                    $childField->setValue($values);
                    if ($childField->isValid() === true) {
                        $inputFields[] = $childField;
                    } else {
                        $errors = array_merge($errors, $childField->isValid());
                    }
                }
            }
        } elseif ($field instanceof InputField) {
            $field->setValue($values);
            if ($field->isValid() === true) {
                $inputFields[] = $field;
            } else {
                $errors = array_merge($errors, $field->isValid());
            }
        }
    }
    if (empty($messages) || $user->isBoard()) {
        $user->update($inputFields);
        $messages[] = new Message('Registration Successful.', Message::NOTIFICATION_MESSAGE);
        SSV_General::redirect(get_permalink());
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
    foreach ($fields as $field) {
        if ($field instanceof TabField) {
            foreach ($field->fields as $childField) {
                if ($childField instanceof InputField) {
                    $childField->value = '';
                }
            }
        } elseif ($field instanceof InputField) {
            $field->value = '';
        }
    }
    $html = SSV_General::getCustomFieldsHTML($fields, SSV_Users::ADMIN_REFERER_REGISTRATION, 'Register');
    return str_replace(SSV_Users::REGISTER_FIELDS_TAG, $html, $content);
}

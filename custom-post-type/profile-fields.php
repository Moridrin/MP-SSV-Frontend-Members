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
function mp_ssv_user_save_profile_fields($fields, $values)
{
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
                    if ($childField->isValid()) {
                        $inputFields[] = $childField;
                    } else {
                        $errors[] = $childField->isValid();
                    }
                }
            }
        } elseif ($field instanceof InputField) {
            $field->setValue($values);
            if ($field->isValid()) {
                $inputFields[] = $field;
            } else {
                $errors[] = $field->isValid();
            }
        }
    }
    if (!empty($errors)) {
        return $errors;
    } else {
        $user->update($inputFields);
        return array(new Message('Profile Updated.', Message::NOTIFICATION_MESSAGE));
    }
}

/**
 * @param $content
 * @param $fields
 *
 * @return string
 */
function mp_ssv_user_get_profile_fields($content, $fields)
{
    if (isset($_GET['member'])) {
        $user = User::getByID($_GET['member']);
    } else {
        $user = User::getCurrent();
    }
    foreach ($fields as $field) {
        if ($field instanceof TabField) {
            foreach ($field->fields as $childField) {
                if ($childField instanceof InputField && $user != null) {
                    $childField->value = $user->getMeta($childField->name);
                }
            }
        } elseif ($field instanceof InputField && $user != null) {
            $field->value = $user->getMeta($field->name);
        }
    }
    $html = SSV_General::getCustomFieldsHTML($fields, SSV_Users::ADMIN_REFERER_PROFILE);
    return str_replace(SSV_Users::PROFILE_FIELDS_TAG, $html, $content);
}

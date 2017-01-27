<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 26-1-17
 * Time: 8:55
 */

/**
 * @param $fields
 */
function mp_ssv_user_save_registration_fields($fields, $values)
{
    if (empty($values)) {
        return;
    }

    if (isset($_GET['member'])) {
        $user = User::getByID($_GET['member']);
    } else {
        $user = User::getCurrent();
    }

    $tabID = -1;
    if (isset($values['tab'])) {
        $tabID = $values['tab'];
    }

    foreach ($fields as $field) {
        if ($field instanceof TabField && $tabID == $field->id) {
            foreach ($field->fields as $childField) {
                if ($childField instanceof InputField && $user != null && isset($values[$childField->name])) {
                    $user->updateMeta($childField->name, $values[$childField->name]);
                }
            }
        } elseif ($field instanceof InputField && $user != null && isset($values[$field->name])) {
            $user->updateMeta($field->name, $values[$field->name]);
        }
    }
}

/**
 * @param $content
 * @param $fields
 *
*@return string
 */
function mp_ssv_user_get_registration_fields($content, $fields)
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
    $html = SSV_General::getCustomFieldsHTML($fields, SSV_Users::ADMIN_REFERER_REGISTRATION);
    return str_replace(SSV_Users::REGISTER_FIELDS_TAG, $html, $content);
}

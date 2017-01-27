<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 22-1-17
 * Time: 8:06
 */
#region Meta Boxes
/**
 * This method adds the custom Meta Boxes
 */
function mp_ssv_users_meta_boxes()
{
    global $post;
    $containsProfileTag      = strpos($post->post_content, SSV_Users::PROFILE_FIELDS_TAG) !== false;
    $containsRegistrationTag = strpos($post->post_content, SSV_Users::REGISTER_FIELDS_TAG) !== false;
    if ($containsProfileTag || $containsRegistrationTag) {
        add_meta_box('ssv_users_page_fields', 'Fields', 'ssv_users_page_fields', 'page', 'advanced', 'default');
    }
}

add_action('add_meta_boxes', 'mp_ssv_users_meta_boxes');

function ssv_users_page_fields()
{
    echo SSV_General::getCustomFieldsEditor(true);
}

#endregion

#region Save Meta
/**
 * @param $post_id
 *
 * @return int the post_id
 */
function mp_ssv_user_pages_save_meta($post_id)
{
    if (!current_user_can('edit_post', $post_id)) {
        return $post_id;
    }

    // Remove old fields
    $registrationIDs = get_post_meta($post_id, Field::ID_TAG, true);
    $registrationIDs = $registrationIDs ?: array();
    foreach ($registrationIDs as $id) {
        delete_post_meta($post_id, Field::PREFIX . $id);
    }

    // Save fields
    $registrationFields = SSV_General::getCustomFieldsFromPost();
    $registrationFields = $registrationFields ?: array();
    $registrationIDs    = array();
    foreach ($registrationFields as $id => $field) {
        /** @var Field $field */
        update_post_meta($post_id, Field::PREFIX . $id, $field->toJSON());
        $registrationIDs[] = $id;
    }
    update_post_meta($post_id, Field::ID_TAG, $registrationIDs);
    return $post_id;
}

add_action('save_post', 'mp_ssv_user_pages_save_meta');
#endregion

#region Set Content
function mp_ssv_user_pages_set_content($content)
{
    $fields = Field::fromMeta();
    $fields = $fields ?: array();
    if (strpos($content, SSV_Users::PROFILE_FIELDS_TAG) !== false) {
        require_once 'profile-fields.php';
    } elseif (strpos($content, SSV_Users::REGISTER_FIELDS_TAG) !== false) {
        require_once 'registration-fields.php';
    } else {
        return $content;
    }
    $messagesHTML = '';
    $messages = mp_ssv_user_save_fields($fields, $_POST);
    foreach ($messages as $message) {
        $messagesHTML .= $message->getHTML();
    }
    $content = $messagesHTML . mp_ssv_user_get_fields($content, $fields);
    return $content;
}

add_filter('the_content', 'mp_ssv_user_pages_set_content');

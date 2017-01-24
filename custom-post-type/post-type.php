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
    if (strpos($post->post_content, SSV_Users::CUSTOM_FIELDS_TAG) !== false) {
        add_meta_box('ssv_users_page_fields', 'Fields', 'ssv_users_page_fields', 'page', 'advanced', 'default');
    }
}

add_action('add_meta_boxes', 'mp_ssv_users_meta_boxes');

function ssv_users_page_fields()
{
    global $post;
    $fieldIDs = get_post_meta($post->ID, 'user_page_field_ids', true);
    $fields = array();
    if (is_array($fieldIDs)) {
        foreach ($fieldIDs as $id) {
            $fields[] = Field::fromJSON(get_post_meta($post->ID, 'user_page_fields_' . $id, true));
        }
        $id = Field::getMaxID($fields) + 1;
    } else {
        $id = 0;
    }
    SSV_General::getCustomFieldsContainer('user_page_fields', $id, true);
    if (is_array($fieldIDs)) {
        ?>
        <script>
            <?php foreach($fieldIDs as $id): ?>
            <?php $json = get_post_meta($post->ID, 'user_page_fields_' . $id, true); ?>
            <?php $field = json_decode($json); ?>
            <?php $fieldType = $field->field_type; ?>
            <?php $inputType = isset($field->input_type) ? $field->input_type : ''; ?>
            mp_ssv_add_new_field('<?= $fieldType ?>', '<?= $inputType ?>', 'custom-fields-placeholder', <?= $id ?>, 'user_page_fields', <?= $json ?>, true);
            <?php endforeach; ?>
        </script>
        <?php
    }
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
    $registrationFields = SSV_General::getCustomFieldsFromPost('user_page_fields');
    $registrationFields = $registrationFields ?: array();
    $registrationIDs    = array();
    foreach ($registrationFields as $id => $field) {
        /** @var Field $field */
        if (!empty($field->title)) {
            update_post_meta($post_id, 'user_page_fields_' . $id, $field->toJSON());
            $registrationIDs[] = $id;
        } else {
            delete_post_meta($post_id, 'user_page_fields_' . $id);
        }
    }
    update_post_meta($post_id, 'user_page_field_ids', $registrationIDs);
    return $post_id;
}

add_action('save_post', 'mp_ssv_user_pages_save_meta');
#endregion

#region Set Content
function mp_ssv_user_pages_set_content($content)
{
    global $post;
    $fieldIDs = get_post_meta($post->ID, 'user_page_field_ids', true);
    $fields   = array();
    foreach ($fieldIDs as $id) {
        $field    = get_post_meta($post->ID, 'user_page_fields_' . $id, true);
        $fields[] = Field::fromJSON($field);
    }
    ob_start();
    ?>
    <form action="<?= get_permalink() ?>" method="POST">
        <?= Field::getFormFields($fields); ?>
        <button type="submit" name="submit" class="btn waves-effect waves-light btn waves-effect waves-light--primary">Save</button
        <?php SSV_General::formSecurityFields(SSV_Users::ADMIN_REFERER_REGISTRATION, false, false); ?>
    </form>
    <?php
    return str_replace(SSV_Users::CUSTOM_FIELDS_TAG, ob_get_clean(), $content);
}

add_filter('the_content', 'mp_ssv_user_pages_set_content');

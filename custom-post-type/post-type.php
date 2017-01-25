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
    if (strpos($post->post_content, SSV_Users::PROFILE_FIELDS_TAG) !== false) {
        add_meta_box('ssv_users_page_fields', 'Fields', 'ssv_users_page_fields', 'page', 'advanced', 'default');
    }
}

add_action('add_meta_boxes', 'mp_ssv_users_meta_boxes');

function ssv_users_page_fields()
{
    SSV_General::getCustomFieldsContainer(true);
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
    if (isset($_GET['member'])) {
        $user = User::getByID($_GET['member']);
    } else {
        $user = User::getCurrent();
    }
    $fields = Field::getFromMeta();
    foreach ($fields as $field) {
        if ($field instanceof TabField) {
            foreach ($field->fields as $childField) {
                if ($childField instanceof InputField) {
                    $childField->value = $user->getMeta($childField->name);
                }
            }
        } elseif ($field instanceof InputField) {
            $field->value = $user->getMeta($field->name);
        }
    }
    ob_start();
    ?>
    <form action="<?= get_permalink() ?>" method="POST">
        <?= Field::getFormFromFields($fields); ?>
        <!--        <div class="input-field">-->
        <!--            <input type="password" id="password" name="password" class="validate invalid" required="">-->
        <!--            <label for="password" class="">Password*</label>-->
        <!--        </div>-->
        <!--        <div class="input-field">-->
        <!--            <input type="password" id="confirm_password" name="confirm_password" class="validate" required="">-->
        <!--            <label for="confirm_password">Confirm Password*</label>-->
        <!--        </div>-->
        <button type="submit" name="submit" class="btn waves-effect waves-light btn waves-effect waves-light--primary">Save</button
        <?php SSV_General::formSecurityFields(SSV_Users::ADMIN_REFERER_PROFILE, false, false); ?>
    </form>
    <?php
    return str_replace(SSV_Users::PROFILE_FIELDS_TAG, ob_get_clean(), $content);
}

add_filter('the_content', 'mp_ssv_user_pages_set_content');

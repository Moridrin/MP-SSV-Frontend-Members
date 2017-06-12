<?php
use mp_ssv_general\Form;
use mp_ssv_general\User;
use mp_ssv_users\SSV_Users;

if (!defined('ABSPATH')) {
    exit;
}

#region Meta Boxes
/**
 * This method adds the custom Meta Boxes
 */
function mp_ssv_users_meta_boxes()
{
    global $post;
    if (!$post) {
        return;
    }
    $containsProfileTag      = strpos($post->post_content, SSV_Users::TAG_PROFILE_FIELDS) !== false;
    $containsRegistrationTag = strpos($post->post_content, SSV_Users::TAG_REGISTER_FIELDS) !== false;
    if ($containsProfileTag || $containsRegistrationTag) {
        add_meta_box('ssv_users_page_fields', 'Fields', 'ssv_users_page_fields', 'page', 'advanced', 'default');
        add_meta_box('ssv_users_page_role', 'Page Role', 'ssv_users_page_role', 'page', 'side', 'default');
    }
}

add_action('add_meta_boxes', 'mp_ssv_users_meta_boxes');

function ssv_users_page_fields()
{
    global $post;
    $allowTabs = strpos($post->post_content, SSV_Users::TAG_PROFILE_FIELDS) !== false;
    $form      = Form::fromDatabase(false);
    echo $form->getEditor($allowTabs);
}

function ssv_users_page_role()
{
    global $post;
    ?>
    <p class="post-attributes-label-wrapper"><label class="post-attributes-label" for="page_role">This profile page is meant for:</label></p>
    <select id="page_role" name="page_role">
        <option value="-1" <?= get_post_meta($post->ID, SSV_Users::PAGE_ROLE_META, true) == -1 ? 'selected' : '' ?>>All</option>
        <?php wp_dropdown_roles(get_post_meta($post->ID, SSV_Users::PAGE_ROLE_META, true)); ?>
    </select>
    <?php
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

    // Save fields
    Form::saveEditorFromPost();

    // Page Role
    if (isset($_POST['page_role'])) {
        update_post_meta($post_id, SSV_Users::PAGE_ROLE_META, $_POST['page_role']);
    }

    return $post_id;
}

add_action('save_post', 'mp_ssv_user_pages_save_meta');
#endregion

#region Set Content
function mp_ssv_user_pages_set_content($content)
{
    if (strpos($content, SSV_Users::TAG_PROFILE_FIELDS) !== false) {
        $form = Form::fromDatabase();
        require_once 'profile-fields.php';
    } elseif (strpos($content, SSV_Users::TAG_REGISTER_FIELDS) !== false) {
        $form = Form::fromDatabase(false);
        $form->addFields(User::getDefaultFields(), false);
        require_once 'registration-fields.php';
    } elseif (strpos($content, SSV_Users::TAG_CHANGE_PASSWORD) !== false) {
        $form = Form::fromDatabase(false);
        $form->addFields(User::getPasswordChangeFields(), false);
        require_once 'change-password-page.php';
    } elseif (strpos($content, SSV_Users::TAG_LOGIN_FIELDS) !== false) {
        require_once 'login-fields.php';
        return mp_ssv_users\mp_ssv_user_get_fields($content);
    } elseif (strpos($content, SSV_Users::TAG_LOST_PASSWORD) !== false) {
        require_once 'forgot-password-page.php';
        return mp_ssv_users\mp_ssv_user_get_fields($content);
    } else {
        return $content;
    }
    $messagesHTML = '';
    $messages     = mp_ssv_users\mp_ssv_user_save_fields($form);
    foreach ($messages as $message) {
        $messagesHTML .= $message->getHTML();
    }
    $content = $messagesHTML . mp_ssv_users\mp_ssv_user_get_fields($content, $form);
    return $content;
}

add_filter('the_content', 'mp_ssv_user_pages_set_content');

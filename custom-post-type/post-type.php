<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 22-1-17
 * Time: 8:06
 */
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
    $form      = Form::fromDatabase(SSV_Users::CAPABILITY_ADMIN_EDIT_USERS);
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
        $form = Form::fromDatabase(SSV_Users::CAPABILITY_ADMIN_EDIT_USERS);

        $_SESSION['field_errors'] = array();
        if (isset($_GET['view']) && $_GET['view'] == 'directDebitPDF') {
            if (isset($_GET['user_id'])) {
                $user = User::getByID($_GET['user_id']);
            } else {
                $user = User::getCurrent();
            }
            $_SESSION["ABSPATH"]         = ABSPATH;
            $_SESSION["first_name"]      = $user->first_name;
            $_SESSION["initials"]        = $user->getMeta('initials');
            $_SESSION["last_name"]       = $user->last_name;
            $_SESSION["gender"]          = $user->getMeta('gender');
            $_SESSION["iban"]            = $user->getMeta('iban');
            $_SESSION["date_of_birth"]   = $user->getMeta('date_of_birth');
            $_SESSION["street"]          = $user->getMeta('street');
            $_SESSION["email"]           = $user->getMeta('email');
            $_SESSION["postal_code"]     = $user->getMeta('postal_code');
            $_SESSION["city"]            = $user->getMeta('city');
            $_SESSION["phone_number"]    = $user->getMeta('phone_number');
            $_SESSION["emergency_phone"] = $user->getMeta('emergency_phone');
            SSV_General::redirect(SSV_Users::URL . '/direct-debit-pdf.php');
        }

        require_once 'profile-fields.php';
    } elseif (strpos($content, SSV_Users::TAG_REGISTER_FIELDS) !== false) {
        $form = Form::fromDatabase(SSV_Users::CAPABILITY_ADMIN_EDIT_USERS, false);
        require_once 'registration-fields.php';
        $form->addFields(User::getDefaultFields(), false);
    } elseif (strpos($content, SSV_Users::TAG_CHANGE_PASSWORD) !== false) {
        $form = Form::fromDatabase(SSV_Users::CAPABILITY_ADMIN_EDIT_USERS, false);
        require_once 'change-password-page.php';
        $form->addFields(User::getPasswordChangeFields(), false);
    } elseif (strpos($content, SSV_Users::TAG_LOGIN_FIELDS) !== false) {
        require_once 'login-fields.php';
        return mp_ssv_user_get_fields($content);
    } elseif (strpos($content, SSV_Users::TAG_LOST_PASSWORD) !== false) {
        require_once 'forgot-password-page.php';
        return mp_ssv_user_get_fields($content);
    } else {
        return $content;
    }
    $messagesHTML = '';
    $messages     = mp_ssv_user_save_fields($form);
    foreach ($messages as $message) {
        $messagesHTML .= $message->getHTML();
    }
    $content = $messagesHTML . mp_ssv_user_get_fields($content, $form);
    return $content;
}

add_filter('the_content', 'mp_ssv_user_pages_set_content');

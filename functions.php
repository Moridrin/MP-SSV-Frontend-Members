<?php
use mp_ssv_general\ExternalUser;
use mp_ssv_general\SSV_General;
use mp_ssv_general\User;
use mp_ssv_users\SSV_Users;

if (!defined('ABSPATH')) {
    exit;
}

#region Register
function mp_ssv_users_register_plugin()
{
    if (empty(SSV_Users::getPageIDsWithTag(SSV_Users::TAG_REGISTER_FIELDS))) {
        /* Pages */
        $registerPost = array(
            'post_content' => SSV_Users::TAG_REGISTER_FIELDS,
            'post_name'    => 'register',
            'post_title'   => 'Register',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        );
        wp_insert_post($registerPost);
    }
    if (empty(SSV_Users::getPageIDsWithTag(SSV_Users::TAG_LOGIN_FIELDS))) {
        $loginPost = array(
            'post_content' => SSV_Users::TAG_LOGIN_FIELDS,
            'post_name'    => 'login',
            'post_title'   => 'Login',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        );
        wp_insert_post($loginPost);
    }
    if (empty(SSV_Users::getPageIDsWithTag(SSV_Users::TAG_PROFILE_FIELDS))) {
        $profilePost = array(
            'post_content' => SSV_Users::TAG_PROFILE_FIELDS,
            'post_name'    => 'profile',
            'post_title'   => 'Profile',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        );
        wp_insert_post($profilePost);
    }
    if (empty(SSV_Users::getPageIDsWithTag(SSV_Users::TAG_CHANGE_PASSWORD))) {
        $changePasswordPost = array(
            'post_content' => SSV_Users::TAG_CHANGE_PASSWORD,
            'post_name'    => 'change-password',
            'post_title'   => 'Change Password',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        );
        wp_insert_post($changePasswordPost);
    }
    if (empty(SSV_Users::getPageIDsWithTag(SSV_Users::TAG_LOST_PASSWORD))) {
        $lostPasswordPost = array(
            'post_content' => SSV_Users::TAG_LOST_PASSWORD,
            'post_name'    => 'lost-password',
            'post_title'   => 'Lost Password',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        );
        wp_insert_post($lostPasswordPost);
    }

    SSV_Users::resetOptions();
}

register_activation_hook(SSV_USERS_PATH . 'ssv-users.php', 'mp_ssv_users_register_plugin');
register_activation_hook(SSV_USERS_PATH . 'ssv-users.php', 'mp_ssv_general_register_plugin');
add_filter('send_password_change_email', '__return_false' );
#endregion

#region Unregister
function mp_ssv_users_unregister()
{
    global $wpdb;
    $customFieldsTag = SSV_Users::TAG_PROFILE_FIELDS;
    $table = $wpdb->prefix . 'posts';
    $results         = $wpdb->get_results("SELECT * FROM $table WHERE post_content LIKE '%$customFieldsTag%'");
    foreach ($results as $key => $row) {
        wp_delete_post($row->ID);
    }
}

register_deactivation_hook(SSV_USERS_PATH . 'ssv-users.php', 'mp_ssv_users_unregister');
#endregion

#region Reset Options
/**
 * This function will reset the events options if the admin referer originates from the SSV Events plugin.
 *
 * @param $admin_referer
 */
function mp_ssv_users_reset_options($admin_referer)
{
    if (!mp_ssv_starts_with($admin_referer, 'ssv_users__')) {
        return;
    }
    SSV_Users::resetOptions();
}

add_filter(SSV_General::HOOK_RESET_OPTIONS, 'mp_ssv_users_reset_options');
#endregion

#region Avatar
/**
 * This function gets the user avatar (profile picture).
 *
 * @param string $avatar      is the avatar component that is requested in this method.
 * @param mixed  $id_or_email is either the User ID (int) or the User Email (string).
 * @param int    $size        is the size of the requested avatar in px. Default this is 150.
 * @param null   $default     If the user does not have an avatar the default is returned.
 * @param string $alt         is the alt text of the <img> component.
 * @param array  $args        is an array of extra arguments that can be given.
 *
 * @return string The <img> component of the avatar.
 */
function ssv_users_avatar(
    $avatar,
    $id_or_email,
    /** @noinspection PhpUnusedParameterInspection */
    $size = 150,
    $default = null,
    /** @noinspection PhpUnusedParameterInspection */
    $alt = '',
    $args = array()
) {
    $user = false;

    if (is_numeric($id_or_email)) {
        $id   = (int)$id_or_email;
        $user = get_user_by('id', $id);
    } elseif (is_object($id_or_email)) {
        if (!empty($id_or_email->user_id)) {
            $id   = (int)$id_or_email->user_id;
            $user = get_user_by('id', $id);
        }
    } else {
        $user = get_user_by('email', $id_or_email);
    }

    if ($user && is_object($user)) {
        $args['url'] = esc_url(get_user_meta($user->ID, 'profile_picture', true));
    }

    return $avatar ?: $default;
}

add_filter('get_avatar', 'ssv_users_avatar', 1, 6);
#endregion

#region Custom Authentication
/**
 * This function overrides the normal WordPress login function. With this function you can login with both your
 * username and your email.
 *
 * @param WP_User $user     is the current user component.
 * @param string  $login    is either the Users Email or the Username.
 * @param string  $password is the password for the user.
 *
 * @return false|WP_Error|WP_User returns a WP_Error if the login fails and returns the WP_User component for the user
 *                                that just logged in if the login is successful.
 */
function ssv_users_authenticate($user, $login, $password)
{
    if (empty($login) || empty ($password)) {
        $error = new WP_Error();
        if (empty($login)) {
            $error->add('empty_username', __('<strong>ERROR</strong>: Email/Username field is empty.'));
        }
        if (empty($password)) {
            $error->add('empty_password', __('<strong>ERROR</strong>: Password field is empty.'));
        }

        return $error;
    }

    if (!$user) {
        $user = get_user_by('email', $login);
    }
    if (!$user) {
        $user = get_user_by('login', $login);
    }
    if (!$user) {
        $error = new WP_Error();
        $error->add('invalid', __('<strong>ERROR</strong>: Either the email/username or password you entered is invalid. The email you entered was: ' . $login));

        return $error;
    } else {
        if (!wp_check_password($password, $user->user_pass, $user->ID)) {
            $error = new WP_Error();
            $error->add('invalid', __('<strong>ERROR</strong>: The password you entered is invalid.'));

            return $error;
        } else {
            return $user;
        }
    }
}

add_filter('authenticate', 'ssv_users_authenticate', 20, 3);
#endregion

#region Set Profile Page Title
function mp_ssv_users_set_profile_page_title($title, $id)
{
    $pages       = SSV_Users::getPagesWithTag(SSV_Users::TAG_PROFILE_FIELDS);
    $correctPage = null;
    foreach ($pages as $page) {
        if ($page->ID == $id) {
            $correctPage = $page;
        }
    }
    if ($correctPage == null) {
        return $title;
    }
    if (isset($_GET['member']) && current_user_can('edit_users')) {
        if (!User::getByID($_GET['member'])) {
            return $title;
        }
        return User::getByID($_GET['member'])->display_name;
    }
    return $title;
}

add_filter('the_title', 'mp_ssv_users_set_profile_page_title', 20, 2);
#endregion

#region Export
function mp_ssv_users_generate_data()
{
    if (SSV_General::isValidPOST(SSV_Users::ADMIN_REFERER_EXPORT)) {
        // Fields
        if (isset($_POST['field_names'])) {
            $fields = SSV_General::sanitize($_POST['field_names'], 'text');
            $fields = empty($fields) ? array() : is_array($fields) ? $fields : explode(',', $fields);
            update_option(SSV_Users::OPTION_USER_EXPORT_COLUMNS, json_encode($fields));
        } else {
            $fields = json_decode(get_option(SSV_Users::OPTION_USER_EXPORT_COLUMNS));
        }
        if (empty($fields)) { //If nothing is specified, select all fields.
            $fields = SSV_Users::getInputFieldNames();
        }
        // Filters
        $filters = array();
        foreach ($_POST as $key => $value) {
            if (mp_ssv_starts_with($key, 'filter_')) {
                $filterKey           = str_replace('filter_', '', $key);
                if (!isset($_POST[$filterKey])) {
                    $filters[$filterKey] = array(
                            'after' => $_POST[$filterKey . '_after'],
                            'before' => $_POST[$filterKey . '_before'],
                    );
                } else {
                    $filters[$filterKey] = $_POST[$filterKey];
                }
            }
        }
        // Users
        $users = array();
        foreach (get_users() as $user) {
            $matchesFilters = true;
            if (get_user_meta($user->ID, 'lidnr', true)) {
                $user = new ExternalUser($user);
            } else {
                $user = new User($user);
            }
            foreach ($filters as $key => $value) {
                if ($value == '*') {
                    if (empty($user->getMeta($key))) {
                        $matchesFilters = false;
                        break;
                    }
                } elseif (is_array($value)) {
                    $actual = (new DateTime($user->getMeta($key)))->getTimestamp();
                    if (empty($user->getMeta($key))) {
                        $matchesFilters = false;
                        break;
                    }
                    if (!empty($value['after'])) {
                        $after = (new DateTime($value['after']))->getTimestamp();
                        if ($actual < $after) {
                            $matchesFilters = false;
                            break;
                        }
                    }
                    if (!empty($value['before'])) {
                        $before = (new DateTime($value['before']))->getTimestamp();
                        if ($actual > $before) {
                            $matchesFilters = false;
                            break;
                        }
                    }
                } elseif (strpos(strtolower($user->getMeta($key)), strtolower($value)) === false) {
                    $matchesFilters = false;
                    break;
                }
            }
            if ($matchesFilters) {
                $users[] = $user;
            }
        }
        SSV_Users::export($users, $fields);
    }
}

add_action('admin_init', 'mp_ssv_users_generate_data');
#endregion

#region Update Settings Message.
function mp_ssv_users_update_settings_notification()
{
    if (empty(get_option(SSV_Users::OPTION_MEMBER_ADMINS))) {
        ?>
        <div class="update-nag notice">
            <p>You still need to set a members admin for SSV Users.</p>
            <p><a href="/wp-admin/admin.php?page=ssv-users-settings&tab=email">Set Now</a></p>
        </div>
        <?php
    }
}

add_action('admin_notices', 'mp_ssv_users_update_settings_notification');
#endregion

#region Update Users Role Meta.
function mp_ssv_user_profile_update($user_id, $old_user_data)
{
    foreach ($old_user_data->roles as $role) {
        User::getByID($user_id)->updateMeta($role, 'false');
    }
    foreach (User::getByID($user_id)->roles as $role) {
        User::getByID($user_id)->updateMeta($role, 'true');
    }
}

add_action('profile_update', 'mp_ssv_user_profile_update', 10, 2);

function mp_ssv_user_role_added($user_id, $role)
{
    User::getByID($user_id)->updateMeta($role, 'true');
}

add_action('add_user_role', 'mp_ssv_user_role_added', 10, 3);

function mp_ssv_user_role_removed($user_id, $role)
{
    User::getByID($user_id)->updateMeta($role, 'false');
}

add_action('remove_user_role', 'mp_ssv_user_role_removed', 10, 3);
#endregion

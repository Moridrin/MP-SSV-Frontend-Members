<?php
/**
 * Plugin Name: SSV Users
 * Plugin URI: http://studentensurvival.com/ssv-users
 * Description: SSV Users is a plugin that allows you to manage members of a Students Sports Club the way you want to. With this plugin you can:
 * - Have a frontend registration and login page
 * - Customize member data fields,
 * - Easy manage, view and edit member profiles.
 * - Etc.
 * This plugin is fully compatible with the SSV library which can add functionality like: MailChimp, Events, etc.
 * Version: 1.4.1
 * Author: Jeroen Berkvens
 * Author URI: http://nl.linkedin.com/in/jberkvens/
 * License: WTFPL
 * License URI: http://www.wtfpl.net/txt/copying/
 */
if (!defined('ABSPATH')) {
    exit;
}

#region Require Once
require_once 'general/general.php';

require_once 'options/options.php';
#endregion

#region SSV_Users class
define('SSV_USERS_PATH', plugin_dir_path(__FILE__));
define('SSV_USERS_URL', plugins_url() . '/ssv-users/');

class SSV_Users
{
    #region Constants
    const PATH = SSV_USERS_PATH;
    const URL = SSV_USERS_URL;

    const HOOK_NEW_MEMBER = 'ssv_users__hook_new_registration';

    const OPTION_REGISTER_POST_ID = 'ssv_users__register_post_id';
    const OPTION_LOGIN_POST_ID = 'ssv_users__login_post_id';
    const OPTION_PROFILE_POST_ID = 'ssv_users__profile_post_id';
    const OPTION_CHANGE_PASSWORD_POST_ID = 'ssv_users__change_password_post_id';
    const OPTION_DEFAULT_MEMBER_ROLE = 'ssv_users__default_member_role';
    const OPTION_BOARD_ROLE = 'ssv_users__board_role';
    const OPTION_CUSTOM_USERS_FILTER = 'ssv_users__custom_users_filters';
    const OPTION_USERS_PAGE_MAIN_COLUMN = 'ssv_users__main_column';
    const OPTION_USER_COLUMNS = 'ssv_users__user_columns';
    const OPTION_MEMBER_ADMIN = 'ssv_users__member_admin';
    const OPTION_NEW_MEMBER_REGISTRATION_EMAIL = 'ssv_users__new_member_registration_email';
    const OPTION_MEMBER_ROLE_CHANGED_EMAIL = 'ssv_users__member_role_changed_email';

    const ADMIN_REFERER_OPTIONS = 'ssv_users__admin_referer_options';
    const ADMIN_REFERER_REGISTRATION = 'ssv_users__admin_referer_registration';
    #endregion

    #region resetOptions()
    /**
     * This function sets all the options for this plugin back to their default value
     */
    public static function resetOptions()
    {
        update_option(self::OPTION_DEFAULT_MEMBER_ROLE, 'subscriber');
        update_option(self::OPTION_BOARD_ROLE, 'administrator');
        update_option(self::OPTION_CUSTOM_USERS_FILTER, 'under');
        update_option(self::OPTION_USERS_PAGE_MAIN_COLUMN, 'plugin_default');
        update_option(self::OPTION_USER_COLUMNS, json_encode(array('wp_Role', 'wp_Posts')));
        update_option(self::OPTION_MEMBER_ADMIN, get_option('admin_email'));
        update_option(self::OPTION_NEW_MEMBER_REGISTRATION_EMAIL, true);
        update_option(self::OPTION_MEMBER_ROLE_CHANGED_EMAIL, true);
    }

    #endregion

    public static function CLEAN_INSTALL()
    {
        mp_ssv_users_unregister();
        mp_ssv_users_register_plugin();
    }

    /**
     * @return string[]
     */
    public static function getAllFieldNames()
    {
    }
}

#endregion

#region Register
function mp_ssv_users_register_plugin()
{
    /* Pages */
    $register_post    = array(
        'post_content' => '[ssv-frontend-members-register]',
        'post_name'    => 'register',
        'post_title'   => 'Register',
        'post_status'  => 'publish',
        'post_type'    => 'page',
    );
    $register_post_id = wp_insert_post($register_post);
    update_option(SSV_Users::OPTION_REGISTER_POST_ID, $register_post_id);
    $login_post    = array(
        'post_content' => '[ssv-frontend-members-login]',
        'post_name'    => 'login',
        'post_title'   => 'Login',
        'post_status'  => 'publish',
        'post_type'    => 'page',
    );
    $login_post_id = wp_insert_post($login_post);
    update_option(SSV_Users::OPTION_LOGIN_POST_ID, $login_post_id);
    $profile_post    = array(
        'post_content' => '[ssv-frontend-members-profile]',
        'post_name'    => 'profile',
        'post_title'   => 'My Profile',
        'post_status'  => 'publish',
        'post_type'    => 'page',
    );
    $profile_post_id = wp_insert_post($profile_post);
    update_option(SSV_Users::OPTION_PROFILE_POST_ID, $profile_post_id);
    $change_password_post    = array(
        'post_content' => '[ssv-frontend-members-change-password]',
        'post_name'    => 'change-password',
        'post_title'   => 'Change Password',
        'post_status'  => 'publish',
        'post_type'    => 'page',
    );
    $change_password_post_id = wp_insert_post($change_password_post);
    update_option(SSV_Users::OPTION_CHANGE_PASSWORD_POST_ID, $change_password_post_id);

    SSV_Users::resetOptions();
}

register_activation_hook(__FILE__, 'mp_ssv_users_register_plugin');
#endregion

#region Unregister
function mp_ssv_users_unregister()
{
    wp_delete_post(get_option(SSV_Users::OPTION_REGISTER_POST_ID), true);
    wp_delete_post(get_option(SSV_Users::OPTION_LOGIN_POST_ID), true);
    wp_delete_post(get_option(SSV_Users::OPTION_PROFILE_POST_ID), true);
    wp_delete_post(get_option(SSV_Users::OPTION_CHANGE_PASSWORD_POST_ID), true);
}

register_deactivation_hook(__FILE__, 'mp_ssv_users_unregister');
#endregion

#region Reset Options
/**
 * This function will reset the events options if the admin referer originates from the SSV Events plugin.
 *
 * @param $admin_referer
 */
function mp_ssv_users_reset_options($admin_referer)
{
    if (!starts_with($admin_referer, 'ssv_users__')) {
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
function ssv_users_avatar($avatar, $id_or_email, $size = 150, $default = null, $alt = '', $args = array())
{
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

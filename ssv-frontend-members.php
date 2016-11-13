<?php
if (!defined('ABSPATH')) {
    exit;
}
/**
 * Plugin Name: SSV Frontend Members
 * Plugin URI: http://studentensurvival.com/ssv-frontend-members
 * Description: SSV Frontend Members is a plugin that allows you to manage members of a Students Sports Club the way you want to. With this plugin you can:
 * - Have a frontend registration and login page
 * - Customize member data fields,
 * - Easy manage, view and edit member profiles.
 * - Etc.
 * This plugin is fully compatible with the SSV library which can add functionality like: MailChimp, Events, etc.
 * Version: 1.4.0
 * Author: Jeroen Berkvens
 * Author URI: http://nl.linkedin.com/in/jberkvens/
 * License: WTFPL
 * License URI: http://www.wtfpl.net/txt/copying/
 */

require_once 'general/general.php';

global $wpdb;
define('FRONTEND_MEMBERS_FIELDS_TABLE_NAME', $wpdb->prefix . "ssv_frontend_members_fields");
define('FRONTEND_MEMBERS_FIELD_META_TABLE_NAME', $wpdb->prefix . "ssv_frontend_members_field_meta");

require_once "models/FrontendMembersField.php";
require_once "frontend-pages/change-password-page.php";
require_once "frontend-pages/login-page.php";
require_once "frontend-pages/profile-page.php";
require_once "frontend-pages/register-page.php";
require_once "options/options.php";
require_once "content_filters.php";

/**
 * This function adds the Google recaptcha API javascript file to the header. This is needed to use recaptcha.
 */
function ssv_use_recaptcha()
{
    $url = plugins_url('ssv-frontend-members/include/google_recaptcha_api.js');
    echo '<script src="' . esc_url($url) . '"></script>';
}

add_action('wp_head', 'ssv_use_recaptcha');

/**
 * This function sets up the plugin:
 *  - Adding tables to the database.
 *  - Adding frontend pages (profile page, login page, register page).
 */
function ssv_register_ssv_frontend_members()
{
    /* Database */
    global $wpdb;
    /** @noinspection PhpIncludeInspection */
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $charset_collate = $wpdb->get_charset_collate();
    $table_name      = $wpdb->prefix . "ssv_frontend_members_fields";
    $wpdb->show_errors();
    $sql
        = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) NOT NULL PRIMARY KEY,
			field_index bigint(20) NOT NULL,
			field_type varchar(30) NOT NULL,
			field_title varchar(30),
			registration_page VARCHAR(5),
			field_class VARCHAR(255),
			field_style VARCHAR(255)
		) $charset_collate;";
    dbDelta($sql);
    $table_name = $wpdb->prefix . "ssv_frontend_members_field_meta";
    $sql
                = "CREATE TABLE IF NOT EXISTS $table_name (
			field_id bigint(20) NOT NULL,
			meta_key varchar(50) NOT NULL,
			meta_value varchar(255) NOT NULL,
			PRIMARY KEY (meta_key, field_id)
		) $charset_collate;";
    dbDelta($sql);

    FrontendMembersField::createStartData();

    /* Pages */
    $register_post    = array(
        'post_content' => '[ssv-frontend-members-register]',
        'post_name'    => 'register',
        'post_title'   => 'Register',
        'post_status'  => 'publish',
        'post_type'    => 'page',
    );
    $register_post_id = wp_insert_post($register_post);
    update_option('register_post_id', $register_post_id);
    $login_post    = array(
        'post_content' => '[ssv-frontend-members-login]',
        'post_name'    => 'login',
        'post_title'   => 'Login',
        'post_status'  => 'publish',
        'post_type'    => 'page',
    );
    $login_post_id = wp_insert_post($login_post);
    update_option('login_post_id', $login_post_id);
    $profile_post    = array(
        'post_content' => '[ssv-frontend-members-profile]',
        'post_name'    => 'profile',
        'post_title'   => 'My Profile',
        'post_status'  => 'publish',
        'post_type'    => 'page',
    );
    $profile_post_id = wp_insert_post($profile_post);
    update_option('profile_post_id', $profile_post_id);
    $change_password_post    = array(
        'post_content' => '[ssv-frontend-members-change-password]',
        'post_name'    => 'change-password',
        'post_title'   => 'Change Password',
        'post_status'  => 'publish',
        'post_type'    => 'page',
    );
    $change_password_post_id = wp_insert_post($change_password_post);
    update_option('change_password_post_id', $change_password_post_id);

    /* Options */
    update_option('ssv_frontend_members_custom_register_page', 'false');
    update_option('ssv_frontend_members_default_member_role', 'subscriber');
    update_option('ssv_frontend_members_board_role', 'administrator');
    update_option('ssv_frontend_members_main_column', 'plugin_default');
    update_option('ssv_frontend_members_user_columns', json_encode(array('wp_Role', 'wp_Posts')));
    update_option('ssv_frontend_members_member_admin', get_option('admin_email'));
    update_option('ssv_frontend_members_new_member_registration_email', 'true');
    update_option('ssv_frontend_members_member_role_changed_email', 'true');
}

register_activation_hook(__FILE__, 'ssv_register_ssv_frontend_members');

/**
 * This function disables the plugin:
 *  - Removing tables to the database.
 *  - Removing frontend pages (profile page, login page, register page).
 */
function ssv_unregister_ssv_frontend_members()
{
    wp_delete_post(get_option('register_post_id'), true);
    wp_delete_post(get_option('login_post_id'), true);
    wp_delete_post(get_option('profile_post_id'), true);
    wp_delete_post(get_option('change_password_post_id'), true);
    global $wpdb;
    /** @noinspection PhpIncludeInspection */
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $table_name = $wpdb->prefix . "ssv_frontend_members_fields";
    $sql        = "DROP TABLE $table_name;";
    $wpdb->query($sql);
    $table_name = $wpdb->prefix . "ssv_frontend_members_field_meta";
    $sql        = "DROP TABLE $table_name;";
    $wpdb->query($sql);
}

register_deactivation_hook(__FILE__, 'ssv_unregister_ssv_frontend_members');

/**
 * This function gets the user avatar (profile picture).
 *
 * @param string $avatar      is the avatar component that is requested in this method.
 * @param mixed  $id_or_email is either the User ID (int) or the User Email (string).
 * @param int    $size        is the size of the requested avatar in px. Default this is 150.
 * @param null   $default     If the user does not have an avatar the default is returned.
 * @param string $alt         is the alt text of the <img> component.
 *
 * @return string The <img> component of the avatar.
 */
function ssv_frontend_members_avatar($avatar, $id_or_email, $size = 150, $default = null, $alt = "")
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
        $custom_avatar = esc_url(get_user_meta($user->ID, 'profile_picture', true));
        if (isset($custom_avatar) && !empty($custom_avatar)) {
            $avatar = "<img alt='{$alt}' src='{$custom_avatar}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
        }
    }

    return $avatar ?: $default;
}

add_filter('get_avatar', 'ssv_frontend_members_avatar', 1, 5);

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
function ssv_authenticate($user, $login, $password)
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

add_filter('authenticate', 'ssv_authenticate', 20, 3);

function ssv_custom_user_column_types($contactmethods)
{
    $contactmethods['ssv_emergency_contact_name'] = 'Emergency Contact Name / Relation';
    return $contactmethods;
}

add_filter('user_contactmethods', 'ssv_custom_user_column_types', 10, 1);

function ssv_custom_user_column_values($val, $column_name, $user_id)
{
    $frontendMember = FrontendMember::get_by_id($user_id);
    if ($column_name == 'ssv_member') {
        $username_block = '';
        $username_block .= '<img style="float: left; margin-right: 10px; margin-top: 1px;" class="avatar avatar-32 photo" src="' . esc_url($frontendMember->getMeta('profile_picture')) . '" height="32" width="32"/>';
        $username_block .= '<strong>' . $frontendMember->getProfileLink('_blank') . '</strong><br/>';
        $directDebitPDF  = $frontendMember->getProfileURL() . '&view=directDebitPDF';
        $editURL         = 'user-edit.php?user_id=' . $frontendMember->ID . '&wp_http_referer=%2Fwp-admin%2Fusers.php';
        $capebilitiesURL = 'users.php?page=users-user-role-editor.php&object=user&user_id=' . $frontendMember->ID;
        $username_block .= '<div class="row-actions"><span class="direct_debit_pdf"><a href="' . esc_url($directDebitPDF) . '" target="_blank">PDF</a> | </span><span class="edit"><a href="' . esc_url($editURL) . '">Edit</a> | </span><span class="capabilities"><a href="' . esc_url($capebilitiesURL) . '">Capabilities</a></span></div>';
        return $username_block;
    } elseif (ssv_starts_with($column_name, 'ssv_')) {
        return $frontendMember->getMeta(str_replace('ssv_', '', $column_name));
    }
    return $val;
}

add_filter('manage_users_custom_column', 'ssv_custom_user_column_values', 10, 3);

function ssv_custom_user_columns($column_headers)
{
    unset($column_headers);
    $column_headers['cb'] = '<input type="checkbox" />';
    global $wpdb;
    if (get_option('ssv_frontend_members_main_column') == 'wordpress_default') {
        $column_headers['username'] = 'Username';
    } else {
        $url = $_SERVER['REQUEST_URI'];
        if (empty($_GET)) {
            $url .= '?orderby=name';
        } elseif (!isset($_GET['orderby'])) {
            $url .= '&orderby=name';
        } elseif (!isset($_GET['order'])) {
            $url .= '&order=DESC';
        } elseif ($_GET['order'] == 'DESC') {
            $url .= '&order=ASC';
        } else {
            $url .= '&order=DESC';
        }
        $column_headers['ssv_member'] = '<a href="' . $url . '">Member</a>';
    }
    $selected_columns = json_decode(get_option('ssv_frontend_members_user_columns'));
    $selected_columns = $selected_columns ?: array();
    foreach ($selected_columns as $column) {
        $sql   = 'SELECT field_id FROM ' . FRONTEND_MEMBERS_FIELD_META_TABLE_NAME . ' WHERE meta_key = "name" AND meta_value = "' . $column . '"';
        $sql   = 'SELECT field_title FROM ' . FRONTEND_MEMBERS_FIELDS_TABLE_NAME . ' WHERE id = (' . $sql . ')';
        $title = $wpdb->get_var($sql);
        if (ssv_starts_with($column, 'wp_')) {
            $column                              = str_replace('wp_', '', $column);
            $column_headers[strtolower($column)] = $column;
        } else {
            $column_headers['ssv_' . $column] = $title;
        }
    }
    return $column_headers;
}

add_action('manage_users_columns', 'ssv_custom_user_columns');

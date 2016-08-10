<?php
/**
 * Plugin Name: SSV Frontend Members
 * Plugin URI: http://moridrin.com/mp-ssv-frontend-members
 * Description: SSV Frontend Members is a plugin that allows you to manage members of a Students Sports Club without
 * giving them access to the WordPress backend. With this plugin you can:
 *  - Have a frontend registration and login page
 *  - Add (mandatory) member data fields,
 *  - Easy manage and export (sections) of the members list.
 *  - Etc.
 * Version: 1.0
 * Author: Jeroen Berkvens
 * Author URI: http://nl.linkedin.com/in/jberkvens/
 * License: WTFPL
 * License URI: http://www.wtfpl.net/txt/copying/
 */

require_once 'general/general.php';

global $wpdb;
define('FRONTEND_MEMBERS_FIELDS_TABLE_NAME', $wpdb->prefix . "mp_ssv_frontend_members_fields");
define('FRONTEND_MEMBERS_FIELD_META_TABLE_NAME', $wpdb->prefix . "mp_ssv_frontend_members_field_meta");

require_once "models/FrontendMembersField.php";
require_once "frontend-pages/login-page.php";
require_once "frontend-pages/profile-page.php";
require_once "frontend-pages/register-page.php";
require_once "options/options.php";
require_once "content_filters.php";

/**
 * This function adds the Google recaptcha API javascript file to the header. This is needed to use recaptcha.
 */
function mp_ssv_use_recaptcha()
{
    $url = plugins_url('mp-ssv-frontend-members/include/google_recaptcha_api.js');
    echo '<script src="' . $url . '"></script>';
}

add_action('wp_head', 'mp_ssv_use_recaptcha');

/**
 * This function sets up the plugin:
 *  - Adding tables to the database.
 *  - Adding frontend pages (profile page, login page, register page).
 */
function mp_ssv_register_mp_ssv_frontend_members()
{
    if (!is_plugin_active('general/general.php')) {
        wp_die('Sorry, but this plugin requires <a href="http://studentensurvival.com/plugins/general">SSV General</a> to be installed and active. <br><a href="' . admin_url('plugins.php') . '">&laquo; Return to Plugins</a>');
    }

    /* Database */
    global $wpdb;
    /** @noinspection PhpIncludeInspection */
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . "mp_ssv_frontend_members_fields";
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
    $table_name = $wpdb->prefix . "mp_ssv_frontend_members_field_meta";
    $sql
        = "CREATE TABLE IF NOT EXISTS $table_name (
			field_id bigint(20) NOT NULL,
			meta_key varchar(50) NOT NULL,
			meta_value varchar(255) NOT NULL,
			PRIMARY KEY (meta_key, field_id)
		) $charset_collate;";
    dbDelta($sql);

    FrontendMembersFieldTab::create(0, "General")->save();
    FrontendMembersFieldHeader::create(1, "Account")->save();
    FrontendMembersFieldInputText::create(2, "Email", "email", true)->save();
    FrontendMembersFieldHeader::create(3, "Personal Info")->save();
    FrontendMembersFieldInputText::create(4, "First Name", "first_name")->save();
    FrontendMembersFieldInputText::create(5, "Last Name", "last_name")->save();

    /* Pages */
    $register_post = array(
        'post_content' => '[mp-ssv-frontend-members-register]',
        'post_name'    => 'register',
        'post_title'   => 'Register',
        'post_status'  => 'publish',
        'post_type'    => 'page'
    );
    $register_post_id = wp_insert_post($register_post);
    update_option('register_post_id', $register_post_id);
    $login_post = array(
        'post_content' => '[mp-ssv-frontend-members-login]',
        'post_name'    => 'login',
        'post_title'   => 'Login',
        'post_status'  => 'publish',
        'post_type'    => 'page'
    );
    $login_post_id = wp_insert_post($login_post);
    update_option('login_post_id', $login_post_id);
    $profile_post = array(
        'post_content' => '[mp-ssv-frontend-members-profile]',
        'post_name'    => 'profile',
        'post_title'   => 'My Profile',
        'post_status'  => 'publish',
        'post_type'    => 'page'
    );
    $profile_post_id = wp_insert_post($profile_post);
    update_option('profile_post_id', $profile_post_id);
}

register_activation_hook(__FILE__, 'mp_ssv_register_mp_ssv_frontend_members');

/**
 * This function disables the plugin:
 *  - Removing tables to the database.
 *  - Removing frontend pages (profile page, login page, register page).
 */
function mp_ssv_unregister_mp_ssv_frontend_members()
{
    if (is_plugin_active('MP-SSV-Google-Apps/mp-ssv-google-apps.php')) {
        wp_die('Sorry, but this plugin is required by SSV Frontend Members. Deactivate SSV Frontend Members before deactivating this plugin. <br><a href="' . admin_url('plugins.php') . '">&laquo; Return to Plugins</a>');
    }
    wp_delete_post(get_option('register_post_id'), true);
    wp_delete_post(get_option('login_post_id'), true);
    wp_delete_post(get_option('profile_post_id'), true);
    global $wpdb;
    /** @noinspection PhpIncludeInspection */
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $table_name = $wpdb->prefix . "mp_ssv_frontend_members_fields";
    $sql = "DROP TABLE $table_name;";
    $wpdb->query($sql);
    $table_name = $wpdb->prefix . "mp_ssv_frontend_members_field_meta";
    $sql = "DROP TABLE $table_name;";
    $wpdb->query($sql);
}

register_deactivation_hook(__FILE__, 'mp_ssv_unregister_mp_ssv_frontend_members');

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
function mp_ssv_frontend_members_avatar($avatar, $id_or_email, $size = 150, $default = null, $alt = "")
{
    $user = false;

    if (is_numeric($id_or_email)) {
        $id = (int)$id_or_email;
        $user = get_user_by('id', $id);
    } elseif (is_object($id_or_email)) {
        if (!empty($id_or_email->user_id)) {
            $id = (int)$id_or_email->user_id;
            $user = get_user_by('id', $id);
        }
    } else {
        $user = get_user_by('email', $id_or_email);
    }

    if ($user && is_object($user)) {
        $custom_avatar = get_user_meta($user->ID, 'profile_picture', true);
        if (isset($custom_avatar) && !empty($custom_avatar)) {
            $avatar = "<img alt='{$alt}' src='{$custom_avatar}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
        }
    }

    return $avatar ?: $default;
}

add_filter('get_avatar', 'mp_ssv_frontend_members_avatar', 1, 5);

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
function mp_ssv_authenticate($user, $login, $password)
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

add_filter('authenticate', 'mp_ssv_authenticate', 20, 3);

function mp_ssv_custom_user_column_types($contactmethods)
{
    $contactmethods['mp_ssv_emergency_contact_name'] = 'Emergency Contact Name / Relation';
    return $contactmethods;
}

add_filter('user_contactmethods', 'mp_ssv_custom_user_column_types', 10, 1);

function mp_ssv_custom_user_column_values($val, $column_name, $user_id)
{
    $frontendMember = FrontendMember::get_by_id($user_id);
    if ($column_name == 'mp_ssv_member') {
        $username_block = '';
        $username_block .= '<img style="float: left; margin-right: 10px; margin-top: 1px;" class="avatar avatar-32 photo" src="' . $frontendMember->getMeta('profile_picture') . '" height="32" width="32"/>';
        $username_block .= '<strong>' . $frontendMember->getProfileLink() . '</strong><br/>';
        $editURL = 'user-edit.php?user_id=' . $frontendMember->ID . '&wp_http_referer=%2Fwp-admin%2Fusers.php';
        $capebilitiesURL = 'users.php?page=users-user-role-editor.php&object=user&user_id=' . $frontendMember->ID;
        $username_block .= '<div class="row-actions"><span class="edit"><a href="' . $editURL . '">Edit</a> | </span><span class="capabilities"><a href="' . $capebilitiesURL . '">Capabilities</a></span></div>';
        return $username_block;
    } elseif (mp_ssv_starts_with($column_name, 'mp_ssv_')) {
        return $frontendMember->getMeta(str_replace('mp_ssv_', '', $column_name));
    }
    return $val;
}

add_filter('manage_users_custom_column', 'mp_ssv_custom_user_column_values', 10, 3);

function mp_ssv_custom_user_columns($column_headers)
{
    unset($column_headers);
    $column_headers['cb'] = '<input type="checkbox" />';
    global $wpdb;
    if (get_option('mp_ssv_frontend_members_main_column') == 'wordpress_default') {
        $column_headers['username'] = 'Username';
    } else {
        $column_headers['mp_ssv_member'] = 'Member';
    }
    $selected_columns = json_decode(get_option('mp_ssv_frontend_members_user_columns'));
    $selected_columns = $selected_columns ?: array();
    foreach ($selected_columns as $column) {
        $sql = 'SELECT field_id FROM ' . FRONTEND_MEMBERS_FIELD_META_TABLE_NAME . ' WHERE meta_key = "name" AND meta_value = "' . $column . '"';
        $sql = 'SELECT field_title FROM ' . FRONTEND_MEMBERS_FIELDS_TABLE_NAME . ' WHERE id = (' . $sql . ')';
        $title = $wpdb->get_var($sql);
        if (mp_ssv_starts_with($column, 'wp_')) {
            $column = str_replace('wp_', '', $column);
            $column_headers[strtolower($column)] = $column;
        } else {
            $column_headers['mp_ssv_' . $column] = $title;
        }
    }
    return $column_headers;
}

add_action('manage_users_columns', 'mp_ssv_custom_user_columns');


add_action(
    'pre_user_query', function ($uqi) {
    global $wpdb;

    $search = '';
    if (isset($uqi->query_vars['search'])) {
        $search = trim($uqi->query_vars['search']);
    }

    if ($search) {
        $search = trim($search, '*');
        $the_search = '%' . $search . '%';

        $search_meta = $wpdb->prepare(
            "
        ID IN ( SELECT user_id FROM {$wpdb->usermeta}
        WHERE ( ( meta_key='first_name' OR meta_key='last_name' )
            AND {$wpdb->usermeta}.meta_value LIKE '%s' )
        )", $the_search
        );

        $uqi->query_where = str_replace(
            'WHERE 1=1 AND (',
            "WHERE 1=1 AND (" . $search_meta . " OR ",
            $uqi->query_where
        );
    }
}
);
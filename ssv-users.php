<?php
/**
 * Plugin Name: SSV Users
 * Plugin URI: https://bosso.nl/ssv-users/
 * Description: SSV Users is a plugin that allows you to manage members of a Students Sports Club the way you want to. With this plugin you can:
 * - Have a frontend registration and login page
 * - Customize member data fields,
 * - Easy manage, view and edit member profiles.
 * - Etc.
 * This plugin is fully compatible with the SSV library which can add functionality like: MailChimp, Events, etc.
 * Version: 3.1.1
 * Author: moridrin
 * Author URI: http://nl.linkedin.com/in/jberkvens/
 * License: WTFPL
 * License URI: http://www.wtfpl.net/txt/copying/
 */
namespace mp_ssv_users;
use mp_ssv_general\custom_fields\InputField;
use mp_ssv_general\Form;
use mp_ssv_general\SSV_General;
use mp_ssv_general\User;
use WP_Post;

if (!defined('ABSPATH')) {
    exit;
}
define('SSV_USERS_PATH', plugin_dir_path(__FILE__));
define('SSV_USERS_URL', plugins_url() . '/ssv-users/');

#region Require Once
require_once 'general/general.php';
require_once 'functions.php';

require_once 'options/options.php';
require_once 'users-page.php';
require_once 'custom-post-type/post-type.php';
#endregion

#region SSV_Users class
class SSV_Users
{
    #region Constants
    const PATH = SSV_USERS_PATH;
    const URL = SSV_USERS_URL;

    const TAG_REGISTER_FIELDS = '[ssv-users-register-fields]';
    const TAG_LOGIN_FIELDS = '[ssv-users-login-fields]';
    const TAG_PROFILE_FIELDS = '[ssv-users-profile-fields]';
    const TAG_CHANGE_PASSWORD = '[ssv-users-change-password-fields]';
    const TAG_LOST_PASSWORD = '[ssv-users-lost-password-fields]';

    const PAGE_ROLE_META = 'page_role';

    const OPTION_USERS_PAGE_MAIN_COLUMN = 'ssv_users__main_column';
    const OPTION_USER_COLUMNS = 'ssv_users__user_columns';
    const OPTION_USER_EXPORT_COLUMNS = 'ssv_users__user_export_columns';
    const OPTION_MEMBER_ADMINS = 'ssv_users__member_admin';
    const OPTION_NEW_MEMBER_REGISTRANT_EMAIL = 'ssv_users__new_member_registration_email';
    const OPTION_NEW_MEMBER_ADMIN_EMAIL = 'ssv_users__member_role_changed_email';

    const ADMIN_REFERER_OPTIONS = 'ssv_users__admin_referer_options';
    const ADMIN_REFERER_REGISTRATION = 'ssv_users__admin_referer_registration';
    const ADMIN_REFERER_PROFILE = 'ssv_users__admin_referer_profile';
    const ADMIN_REFERER_EXPORT = 'ssv_users__admin_referer_export';

    const CAPABILITY_EDIT_USERS = 'edit_users';
    const CAPABILITY_ADMIN_EDIT_USERS = 'admin_edit_users';

    #endregion

    #region resetOptions()
    /**
     * This function sets all the options for this plugin back to their default value
     */
    public static function resetOptions()
    {
        /** @var User $siteAdmin */
        update_option(self::OPTION_USERS_PAGE_MAIN_COLUMN, 'plugin_default');
        update_option(self::OPTION_USER_COLUMNS, json_encode(array('wp_Role', 'wp_Posts')));
        delete_option(self::OPTION_MEMBER_ADMINS);
        update_option(self::OPTION_NEW_MEMBER_REGISTRANT_EMAIL, true);
        update_option(self::OPTION_NEW_MEMBER_ADMIN_EMAIL, true);
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
    public static function getInputFieldNames()
    {
        $pages      = self::getPagesWithTag(self::TAG_PROFILE_FIELDS);
        $pages      = array_merge($pages, self::getPagesWithTag(self::TAG_REGISTER_FIELDS));
        $fieldNames = array();
        /** @var WP_Post $page */
        foreach ($pages as $page) {
            $form       = Form::fromDatabase('', false, $page);
            $fieldNames = array_merge($fieldNames, $form->getFieldProperty('name'));
        }
        $fieldNames = array_unique($fieldNames);
        asort($fieldNames);
        return $fieldNames;
    }

    /**
     * @return InputField[]
     */
    public static function getInputFields()
    {
        $pages  = self::getPagesWithTag(self::TAG_PROFILE_FIELDS);
        $pages  = array_merge($pages, self::getPagesWithTag(self::TAG_REGISTER_FIELDS));
        $fields = array();
        /** @var WP_Post $page */
        foreach ($pages as $page) {
            $form   = Form::fromDatabase('', false, $page);
            $fields = array_merge_recursive($fields, $form->getInputFields());
        }
        $fields = array_unique($fields);
        asort($fields);
        return $fields;
    }

    /**
     * @param $customFieldsTag
     *
     * @return WP_Post[]|null|object Database query results
     */
    public static function getPagesWithTag($customFieldsTag)
    {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM wp_posts WHERE post_content LIKE '%$customFieldsTag%'");
    }

    /**
     * @param $customFieldsTag
     *
     * @return array|null|object Database query results
     */
    public static function getPageIDsWithTag($customFieldsTag)
    {
        global $wpdb;
        $results = $wpdb->get_results("SELECT ID FROM wp_posts WHERE post_content LIKE '%$customFieldsTag%'");
        return array_column($results, 'ID');
    }

    public static function export($users, $fields)
    {
        if (ini_get('safe_mode') == false) {
            set_time_limit(0);
            ini_set('memory_limit', '2048M');
        }

        $filename = get_bloginfo('name') . ' users ' . date('Y-m-d H:i:s');
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename=' . $filename . '.csv');
        header('Content-Type: text/csv; charset=' . get_option('blog_charset'), true);

        // build the document headers ##
        $headers = array();
        foreach ($fields as $key => $field) {
            $headers[] = '"' . $field . '"';
        }
        ob_end_flush();

        // get the value in bytes allocated for Memory via php.ini ##
        // @link http://wordpress.org/support/topic/how-to-exporting-a-lot-of-data-out-of-memory-issue
        $memory_limit = ini_get('memory_limit');
        $memory_limit = trim($memory_limit);
        $last         = strtolower($memory_limit[strlen($memory_limit) - 1]);
        switch ($last) {
            case 'g':
                $memory_limit *= 1024;
                break;
            case 'm':
                $memory_limit *= 1024;
                break;
            case 'k':
                $memory_limit *= 1024;
                break;
        }
        $memory_limit = $memory_limit * .75;

        // we need to disable caching while exporting because we export so much data that it could blow the memory cache
        // if we can't override the cache here, we'll have to clear it later...
        if (function_exists('override_function')) {
            override_function('wp_cache_add', '$key, $data, $group="", $expire=0', '');
            override_function('wp_cache_set', '$key, $data, $group="", $expire=0', '');
            override_function('wp_cache_replace', '$key, $data, $group="", $expire=0', '');
            override_function('wp_cache_add_non_persistent_groups', '$key, $data, $group="", $expire=0', '');
        } elseif (function_exists('runkit_function_redefine')) {
            runkit_function_redefine('wp_cache_add', '$key, $data, $group="", $expire=0', '');
            runkit_function_redefine('wp_cache_set', '$key, $data, $group="", $expire=0', '');
            runkit_function_redefine('wp_cache_replace', '$key, $data, $group="", $expire=0', '');
            runkit_function_redefine('wp_cache_add_non_persistent_groups', '$key, $data, $group="", $expire=0', '');
        }
        echo implode(',', $headers) . "\n";
        foreach ($users as $user) {
            // check if we're hitting any Memory limits, if so flush them out ##
            // per http://wordpress.org/support/topic/how-to-exporting-a-lot-of-data-out-of-memory-issue?replies=2
            if (memory_get_usage(true) > $memory_limit) {
                wp_cache_flush();
            }

            $data     = array();
            $userMeta = (array)get_user_meta($user->ID);
            foreach ($fields as $field) {
                if (isset($userMeta[$field])) {
                    $value = $userMeta[$field][0];
                } else {
                    $value = isset($user->{$field}) ? $user->{$field} : null;
                }
                if (is_array($value)) {
                    $value = implode(';', $value);
                }
                $data[] = '"' . str_replace('"', '""', $value) . '"';
            }
            echo implode(',', $data) . "\n";
        }
        exit;
    }
}
#endregion

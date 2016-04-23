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

global $wpdb;
define('FRONTEND_MEMBERS_FIELDS_TABLE_NAME', $wpdb->prefix . "mp_ssv_frontend_members_fields");
define('FRONTEND_MEMBERS_FIELD_META_TABLE_NAME', $wpdb->prefix . "mp_ssv_frontend_members_field_meta");

include_once "frontend-pages/login-page.php";
include_once "frontend-pages/profile-page.php";
include_once "frontend-pages/register-page.php";
include_once "options/options.php";
require_once 'content_filters.php';

add_filter('the_content', 'mp_ssv_frontend_members_content_filters', 100);

if (function_exists("mp_ssv_use_recaptcha")) {
	/**
	 * This function adds the Google recaptcha API javascript file to the header. This is needed to use recaptcha.
	 */
	function mp_ssv_use_recaptcha()
	{
		echo '<script src="include/google_recaptcha_api.js"></script>';
	}

	add_action('wp_head', 'mp_ssv_use_recaptcha');
}

/**
 * This function sets up the plugin:
 *  - Adding tables to the database.
 *  - Adding frontend pages (profile page, login page, register page).
 */
function mp_ssv_register_mp_ssv_frontend_members()
{
	if (!is_plugin_active('mp-ssv-general/mp-ssv-general.php')) {
		wp_die('Sorry, but this plugin requires <a href="http://studentensurvival.com/plugins/mp-ssv-general">SSV General</a> to be installed and active. <br><a href="' . admin_url('plugins.php') . '">&laquo; Return to Plugins</a>');
	}

	/* Database */
	global $wpdb;
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	$charset_collate = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix . "mp_ssv_frontend_members_fields";
	$wpdb->show_errors();
	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			field_index bigint(20) NOT NULL,
			field_type varchar(30) NOT NULL,
			field_title varchar(30)
		) $charset_collate;";
	dbDelta($sql);
	$table_name = $wpdb->prefix . "mp_ssv_frontend_members_field_meta";
	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			field_id bigint(20) NOT NULL,
			meta_key varchar(255) NOT NULL,
			meta_value varchar(255) NOT NULL
		) $charset_collate;";
	dbDelta($sql);

	$first_name = FrontendMembersField::create(0, "input", "First Name");
	$first_name->setMeta("input_type", "text");
	$first_name->setMeta("name", "first_name");
	$first_name->setMeta("placeholder", "");
	$first_name->setMeta("required", "yes");
	$first_name->setMeta("display", "normal");

	$last_name = FrontendMembersField::create(0, "input", "Last Name");
	$last_name->setMeta("input_type", "text");
	$last_name->setMeta("name", "last_name");
	$last_name->setMeta("placeholder", "");
	$last_name->setMeta("required", "yes");
	$last_name->setMeta("display", "normal");

	/* Pages */
	$register_post = array(
		'post_content' => '[mp-ssv-frontend-members-register]',
		'post_name'    => 'register',
		'post_title'   => 'Register',
		'post_status'  => 'publish',
		'post_type'    => 'page'
	);
	wp_insert_post($register_post);
	$login_post = array(
		'post_content' => '[mp-ssv-frontend-members-login]',
		'post_name'    => 'login',
		'post_title'   => 'Login',
		'post_status'  => 'publish',
		'post_type'    => 'page'
	);
	wp_insert_post($login_post);
	$profile_post = array(
		'post_content' => '[mp-ssv-frontend-members-profile]',
		'post_name'    => 'profile',
		'post_title'   => 'My Profile',
		'post_status'  => 'publish',
		'post_type'    => 'page'
	);
	wp_insert_post($profile_post);
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
	$register_page = get_page_by_title('Register');
	wp_delete_post($register_page->ID, true);
	$login_page = get_page_by_title('Login');
	wp_delete_post($login_page->ID, true);
	$profile_page = get_page_by_title('My Profile');
	wp_delete_post($profile_page->ID, true);
	global $wpdb;
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

	return $avatar;
}

add_filter('get_avatar', 'mp_ssv_frontend_members_avatar', 1, 5);

/**
 * This function updates an already existing Mailchimp member.
 *
 * @param int    $memberId is the ID of the WordPress user.
 * @param string $listID   is the ID of the MailChimp list.
 *
 * @return mixed the http code returned by the curl command.
 */
function mp_ssv_update_mailchimp_frontend_member($memberId, $listID)
{
	$apiKey = get_option('mp_ssv_mailchimp_api_key');
	$memberId = md5(strtolower(get_userdata($memberId)->user_email));
	$memberCenter = substr($apiKey, strpos($apiKey, '-') + 1);
	$url = 'https://' . $memberCenter . '.api.mailchimp.com/3.0/lists/' . $listID . '/members/' . $memberId;

	$json = json_encode([
		'email_address' => get_userdata($memberId)->user_email,
		'status'        => "subscribed",
		'merge_fields'  => [
			'FNAME' => get_userdata($memberId)->first_name,
			'LNAME' => get_userdata($memberId)->last_name
		]
	]);

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $apiKey);
	curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

	curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	return $httpCode;
}

if (!function_exists("mp_ssv_unsubscribe_mailchimp_member")) {
	/**
	 * This function removes a member from the Mailchimp list.
	 *
	 * @param int $user_id is the ID of the WordPress user.
	 *
	 * @return mixed the http code returned by the curl command.
	 */
	function mp_ssv_unsubscribe_mailchimp_member($user_id)
	{
		$user = get_user_by("ID", $user_id);
		$apiKey = get_option('mp_ssv_mailchimp_api_key');
		$listID = get_option('mailchimp_member_sync_list_id');

		$memberId = md5(strtolower($user->user_email));
		$memberCenter = substr($apiKey, strpos($apiKey, '-') + 1);
		$url = 'https://' . $memberCenter . '.api.mailchimp.com/3.0/lists/' . $listID . '/members/' . $memberId;
		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $apiKey);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		json_decode(curl_exec($ch), true);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		return $httpCode;
	}
}
add_action('delete_user', 'mp_ssv_unsubscribe_mailchimp_member');

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

	$user = get_user_by('email', $login);
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
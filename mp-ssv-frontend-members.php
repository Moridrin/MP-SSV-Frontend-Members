<?php
/**
* Plugin Name: SSV Frontend Members
* Plugin URI: http://moridrin.com/mp-ssv-frontend-members
* Description: SSV Frontend Members is a plugin that allows you to manage members of a Students Sports Club without giving them access to the wordpress backend.
* With this plugin you can:
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

include_once "mp-ssv-frontend-members-login.php";
include_once "mp-ssv-frontend-members-profile.php";
include_once "mp-ssv-frontend-members-options.php";
include_once "mp-ssv-frontend-members-register.php";

function register_mp_ssv_frontend_members() {
	/* Database */
	global $wpdb;
	require_once(ABSPATH.'wp-admin/includes/upgrade.php');
	$charset_collate = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix."mp_ssv_frontend_members_fields";
	$sql = "
		CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			title varchar(30) NOT NULL,
			component varchar(255) NOT NULL,
			is_mandatory tinyint(1) NOT NULL DEFAULT 0,
			is_deletable tinyint(1) NOT NULL DEFAULT 1,
			UNIQUE KEY id (id)
		) $charset_collate;";
	dbDelta($sql);
	$wpdb->insert(
		$table_name,
		array(
			'title' => "First Name",
			'component' => '<input type="text" name="first_name"/>'
		),
		array(
			'%s',
			'%s'
		)
	);
	$wpdb->insert(
		$table_name,
		array(
			'title' => "Last Name",
			'component' => '<input type="text" name="first_name"/>'
		),
		array(
			'%s',
			'%s'
		)
	);

	/* Pages */
	$login_post = array(
		'post_content'   => '[mp-ssv-frontend-members-login]',
		'post_name'      => 'login',
		'post_title'     => 'Login',
		'post_status'    => 'publish',
		'post_type'      => 'page'
	);
	wp_insert_post($login_post);
	$register_post = array(
		'post_content'   => '[mp-ssv-frontend-members-register]',
		'post_name'      => 'register',
		'post_title'     => 'Register',
		'post_status'    => 'publish',
		'post_type'      => 'page'
	);
	wp_insert_post($register_post);
	$profile_post = array(
		'post_content'   => '[mp-ssv-frontend-members-profile]',
		'post_name'      => 'profile',
		'post_title'     => 'My Profile',
		'post_status'    => 'publish',
		'post_type'      => 'page'
	);
	wp_insert_post($profile_post);
}
register_activation_hook(__FILE__, 'register_mp_ssv_frontend_members');

function unregister_mp_ssv_frontend_members() {
	$register_page = get_page_by_title('Register');
	wp_delete_post($register_page->ID, true);
	$login_page = get_page_by_title('Login');
	wp_delete_post($login_page->ID, true);
	$profile_page = get_page_by_title('My Profile');
	wp_delete_post($profile_page->ID, true);
}
register_deactivation_hook(__FILE__, 'unregister_mp_ssv_frontend_members');

function app_output_buffer() {
	ob_start();
}
add_action('init', 'app_output_buffer');

function frontend_members_avatar($avatar, $id_or_email, $size, $default, $alt) {
    $user = false;
    $id = false;

    if (is_numeric($id_or_email)) {
        $id = (int) $id_or_email;
        $user = get_user_by('id' , $id);

    } elseif (is_object($id_or_email)) {
        if (!empty($id_or_email->user_id)) {
            $id = (int) $id_or_email->user_id;
            $user = get_user_by( 'id' , $id );
        }
    } else {
        $user = get_user_by( 'email', $id_or_email );   
    }

    if ($user && is_object($user)) {
        $custom_avatar = get_user_meta($user->ID, 'profile_picture', true);
        if (isset($custom_avatar) && !empty($custom_avatar)) {
            $avatar = "<img alt='{$alt}' src='{$custom_avatar}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
        }
    }

    return $avatar;
}
add_filter( 'get_avatar', 'frontend_members_avatar' , 1 , 5 );
?>
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

include_once "login-page.php";
include_once "profile-page.php";
include_once "register-page.php";
include_once "options/options.php";
require_once 'filter_content.php';
add_filter( 'the_content', 'mp_ssv_filter_frontend_members_content', 100);

function mp_ssv_use_recaptcha() {
	echo '<script src="https://www.google.com/recaptcha/api.js"></script>';
}
add_action('wp_head', 'mp_ssv_use_recaptcha');

function mp_ssv_register_mp_ssv_frontend_members() {
	if (!is_plugin_active('mp-ssv-general/mp-ssv-general.php')) {
		wp_die('Sorry, but this plugin requires <a href="http://studentensurvival.com/plugins/mp-ssv-general">SSV General</a> to be installed and active. <br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>');
	}
	/* Database */
	global $wpdb;
	require_once(ABSPATH.'wp-admin/includes/upgrade.php');
	$charset_collate = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix."mp_ssv_frontend_members_fields";
	$wpdb->show_errors();
	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			field_index bigint(20) NOT NULL,
			field_type varchar(30) NOT NULL,
			field_title varchar(30)
		) $charset_collate;";
	dbDelta($sql);
	$table_name = $wpdb->prefix."mp_ssv_frontend_members_field_meta";
	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			field_id bigint(20) NOT NULL,
			meta_key varchar(255) NOT NULL,
			meta_value varchar(255) NOT NULL
		) $charset_collate;";
	dbDelta($sql);

	$first_name_id = add_field(0, "input", "First Name");
	add_field_meta($first_name_id, "input_type", "text");
	add_field_meta($first_name_id, "name", "first_name");
	add_field_meta($first_name_id, "placeholder", "");
	add_field_meta($first_name_id, "required", "true");
	add_field_meta($first_name_id, "display", "normal");
	
	$last_name_id = add_field(1, "input", "Last Name");
	add_field_meta($last_name_id, "input_type", "text");
	add_field_meta($last_name_id, "name", "last_name");
	add_field_meta($last_name_id, "placeholder", "");
	add_field_meta($last_name_id, "required", "true");
	add_field_meta($last_name_id, "display", "normal");

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
register_activation_hook(__FILE__, 'mp_ssv_register_mp_ssv_frontend_members');

function mp_ssv_unregister_mp_ssv_frontend_members() {
	if (is_plugin_active('MP-SSV-Google-Apps/mp-ssv-google-apps.php')) {
		wp_die('Sorry, but this plugin is required by SSV Frontend Members. Deactivate SSV Frontend Members before deactivating this plugin. <br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>');
	}
	$register_page = get_page_by_title('Register');
	wp_delete_post($register_page->ID, true);
	$login_page = get_page_by_title('Login');
	wp_delete_post($login_page->ID, true);
	$profile_page = get_page_by_title('My Profile');
	wp_delete_post($profile_page->ID, true);
	global $wpdb;
	require_once(ABSPATH.'wp-admin/includes/upgrade.php');
	$charset_collate = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix."mp_ssv_frontend_members_fields";
	$sql = "DROP TABLE $table_name;";
	$wpdb->query($sql);
	$table_name = $wpdb->prefix."mp_ssv_frontend_members_field_meta";
	$sql = "DROP TABLE $table_name;";
	$wpdb->query($sql);
}
register_deactivation_hook(__FILE__, 'mp_ssv_unregister_mp_ssv_frontend_members');

function mp_ssv_frontend_members_avatar($avatar, $id_or_email, $size, $default, $alt) {
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
add_filter( 'get_avatar', 'mp_ssv_frontend_members_avatar' , 1 , 5 );

function mp_ssv_update_mailchimp_frontend_member($memberId, $listID) {
	$apiKey = get_option('mp_ssv_mailchimp_api_key');

	$memberId = md5(strtolower(get_userdata($memberId)->user_email));
	$memberCenter = substr($apiKey,strpos($apiKey,'-')+1);
	$url = 'https://' . $memberCenter . '.api.mailchimp.com/3.0/lists/' . $listID . '/members/' . $memberId;

	$json = json_encode([
		'email_address' => $member['email'],
		'status'        => $member['status'], // "subscribed","unsubscribed","cleaned","pending"
		'merge_fields'  => [
			'FNAME'     => $member['firstname'],
			'LNAME'     => $member['lastname']
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

	$result = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	return $result;
}

if (!function_exists("mp_ssv_unsubscribe_mailchimp_member")) {
	function mp_ssv_unsubscribe_mailchimp_member($user_id) {
		$user = get_user_by("ID", $user_id);
		echo $user->user_email;
		
		$apiKey = get_option('mp_ssv_mailchimp_api_key');
		$listID = get_option('mailchimp_member_sync_list_id');
		
		$memberId = md5(strtolower($user->user_email));
		$memberCenter = substr($apiKey,strpos($apiKey,'-')+1);
		$url = 'https://' . $memberCenter . '.api.mailchimp.com/3.0/lists/' . $listID . '/members/' . $memberId;
		$ch = curl_init($url);
		
		curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $apiKey);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		$curl_results = json_decode(curl_exec($ch), true);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		return $httpCode;
	}
}
add_action('delete_user', 'mp_ssv_unsubscribe_mailchimp_member');

function mp_ssv_frontend_members_register_errors($errors, $sanitized_user_login, $user_email) {
	$errors->add( 'spam_error', __( '<strong>ERROR</strong>: This is not registered in the frontend registration.', 'mpssv' ) );		
	return $errors;
}
add_filter('registration_errors', 'mp_ssv_frontend_members_register_errors', 10, 3);

function mp_ssv_authenticate($user, $login, $password){
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
		$error->add('invalid', __('<strong>ERROR</strong>: Either the email/username or password you entered is invalid. The email you entered was: '.$login));
		return $error;
	} else {
		if (!wp_check_password($password, $user->user_pass, $user->ID)){
			$error = new WP_Error();
			$error->add('invalid', __('<strong>ERROR</strong>: The password you entered is invalid.'));
			return $error;
		} else {
			return $user;
		}
	}
}
add_filter('authenticate', 'mp_ssv_authenticate', 20, 3);

function add_field($tab_index, $type, $title) {
	global $wpdb;
	require_once(ABSPATH.'wp-admin/includes/upgrade.php');
	$charset_collate = $wpdb->get_charset_collate();
	$table = $wpdb->prefix."mp_ssv_frontend_members_fields";
	$wpdb->insert(
		$table,
		array(
			'field_index' => $tab_index,
			'field_type' => $type,
			'field_title' => $title
		),
		array(
			'%d',
			'%s',
			'%s'
		)
	);
	return $wpdb->get_var("SELECT id FROM $table ORDER BY id DESC LIMIT 0 , 1" );
}

function add_field_meta($field_id, $key, $value) {
	global $wpdb;
	require_once(ABSPATH.'wp-admin/includes/upgrade.php');
	$charset_collate = $wpdb->get_charset_collate();
	$table = $wpdb->prefix."mp_ssv_frontend_members_field_meta";
	$wpdb->insert(
		$table,
		array(
			'field_id' => $field_id,
			'meta_key' => $key,
			'meta_value' => $value
		),
		array(
			'%d',
			'%s',
			'%s'
		)
	);
	return $wpdb->get_var("SELECT id FROM $table ORDER BY id DESC LIMIT 0 , 1" )[0];
}

function mp_ssv_get_field_meta($id, $key) {
	global $wpdb;
	$table_name = $wpdb->prefix."mp_ssv_frontend_members_field_meta";
	return json_decode(json_encode($wpdb->get_var( 
		"SELECT meta_value
			FROM $table_name
			WHERE field_id = '$id'
			AND meta_key = '$key';"
	)), true);
}

function mp_ssv_get_group_fields($id) {
	global $wpdb;
	$table_name = $wpdb->prefix."mp_ssv_frontend_members_field_meta";
	$options = json_decode(json_encode($wpdb->get_results( 
		"SELECT field_id
			FROM $table_name
			WHERE meta_key = 'parent_id'
			AND meta_value = '$id';"
	)), true);
	
	$table_name = $wpdb->prefix."mp_ssv_frontend_members_fields";
	$sql = "SELECT * FROM $table_name WHERE field_type = 'group_option' AND (";
	for ($i = 0; $i < count($options); $i++) {
		if ($i != 0) {
			$sql .= " OR ";
		}
		$sql .= "id = ".$options[$i]["field_id"];
	}
	$sql .= ") ORDER BY field_index ASC;";
	$group_fields = json_decode(json_encode($wpdb->get_results($sql)), true);
	$table_name = $wpdb->prefix."mp_ssv_frontend_members_field_meta";
	for ($i = 0; $i < count($group_fields); $i++) {
		$group_field_meta = json_decode(json_encode($wpdb->get_results( 
			"SELECT meta_key, meta_value
				FROM $table_name
				WHERE field_id = ".$group_fields[$i]["id"].";"
		)), true);
		foreach ($group_field_meta as $meta_item) {
			$group_fields[$i][$meta_item["meta_key"]] = $meta_item["meta_value"];
		}
	}
	return $group_fields;
}

function mp_ssv_get_user_meta($user_id, $meta_key, $single = true) {
	if ($meta_key == "email" || $meta_key == "email_address" || $meta_key == "user_email" || $meta_key == "member_email") {
		return get_user_by("ID", $user_id)->user_email;
	} else if ($meta_key == "name" || $meta_key == "display_name") {
		return get_user_by("ID", $user_id)->display_name;
	} else if ($meta_key == "login" || $meta_key == "username" || $meta_key == "user_name" || $meta_key == "user_login") {
		return get_user_by("ID", $user_id)->user_login;
	} else if (strpos($meta_key, "_role") !== false) {
		return in_array(str_replace("_role", "", $meta_key), get_user_by("ID", $user_id)->roles);
	} else {
		return get_user_meta($user_id, $meta_key, $single);
	}
}

function mp_ssv_update_user_meta($user_id, $meta_key, $value) {
	if ($meta_key == "email" || $meta_key == "email_address" || $meta_key == "user_email" || $meta_key == "member_email") {
		wp_update_user(array('ID' => $user_id, 'user_email' => $value));
		return true;
	} else if ($meta_key == "name" || $meta_key == "display_name") {
		wp_update_user(array('ID' => $user_id, 'display_name' => $value));
		return true;
	} else if ($meta_key == "login" || $meta_key == "username" || $meta_key == "user_name" || $meta_key == "user_login") {
		return false; //cannot change user_login
	} else if (strpos($meta_key, "_role_select") !== false) {
		$user = get_user_by("ID", $user_id);
		$old_role = mp_ssv_get_user_meta($user_id, str_replace("_role_select", "", $meta_key));
		$user->remove_role($old_role);
		$user->add_role($value);
	} else if (strpos($meta_key, "_role") !== false) {
		$user = get_user_by("ID", $user_id);
		$role = str_replace("_role", "", $meta_key);
		if ($value == "yes") {
			$user->add_role($role);
		} else {
			$user->remove_role($role);
		}
		return true;
	} else {
		update_user_meta($user_id, $meta_key, $value);
		return true;
	}
}
?>
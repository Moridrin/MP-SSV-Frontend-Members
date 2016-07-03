<?php
add_filter('the_content', 'mp_ssv_frontend_members_content_filters', 100);
function mp_ssv_frontend_members_content_filters($content) {
	if (strpos($content, '[mp_ssv_loop_committees]') !== false) {
		$index = 0;
		$counter = 0;
		while (strpos($content, '[mp_ssv_loop_committees]', $index) !== false) {
			$looping_string = explode('[mp_ssv_loop_committees]', $content)[$counter + 1];
			$looping_string = explode('[/mp_ssv_loop_committees]', $looping_string)[0];
			$replacingString = mp_ssv_loop_committees($looping_string);
			$content = str_replace($looping_string, $replacingString, $content);
			$index = strpos($content, '[mp_ssv_loop_committees]', $index) + 1;
			$counter++;
		}
	}
	$content = str_replace('[mp_ssv_loop_committees]', "", $content);
	$content = str_replace('[/mp_ssv_loop_committees]', "", $content);
	if (is_user_logged_in()) {
		$content = mp_ssv_members_filter($content);
	}
	return $content;
}

function mp_ssv_members_filter($content) {
	$users = get_users();
	foreach ($users as $user) {
		$search_term = $user->user_firstname." ".$user->user_lastname;
		$search_replace = '<a href="/profile/?user_id='.$user->ID.'">'.$user->user_firstname.' '.$user->user_lastname.'</a>';
		if (!is_null($search_term) && isset($search_term) && $search_term != "" && $search_term != " ") {
			$content = str_replace('="'.$search_term.'"', '#TMP_REPLACE#', $content);
			$content = str_replace($search_term, $search_replace, $content);
			$content = str_replace('#TMP_REPLACE#', '="'.$search_term.'"', $content);
		}
	}
	return $content;
}

function mp_ssv_loop_committees($looping_string) {
	if ( !function_exists('get_editable_roles') ) {
		require_once( ABSPATH . '/wp-admin/includes/user.php' );
	}
	$roles = get_editable_roles();
	$replacingString = "";
	foreach ($roles as $role_name => $role_info) {
		if (strpos($role_name, "committee")) {
			$loop_instance = $looping_string;
			if (strpos($looping_string, '$name') !== false) {
				$loop_instance = str_replace('$name', $role_info['name'], $loop_instance);
			}
			if (strpos($looping_string, '$email') !== false) {
				$loop_instance = str_replace('$email', $role_name."@allterrain.nl", $loop_instance);
			}
			if (strpos($looping_string, '$members_list') !== false) {
				$loop_instance = str_replace('$members_list', get_members_list($role_name), $loop_instance);
			}
			$replacingString .= $loop_instance;
		}
	}
	return $replacingString;
}

function get_members_list($role_name) {
	$members_list = "<ul>";
	$members = get_users('role='.$role_name);
	foreach ($members as $member) {
		$members_list .= "<li>".$member->display_name."</li>";
	}
	$members_list .= "</ul>";
	return $members_list;
}

add_filter( 'the_title', 'mp_ssv_profile_page_name');
function mp_ssv_profile_page_name ($title) {
	if( $title == 'My Profile' && isset($_GET['user_id']) && is_page()){
		$member = FrontendMember::get_by_id($_GET['user_id']);
		$title = $member->display_name;
	}
	return $title;
}

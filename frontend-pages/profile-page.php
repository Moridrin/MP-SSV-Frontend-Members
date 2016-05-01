<?php

/**
 * This function redirects the user to the login page if he/she is not signed in.
 */
function mp_ssv_profile_page_login_redirect()
{
	global $post;
	if ($post == null) {
		return;
	}
	$post_name_correct = $post->post_name == 'profile';
	if (!is_user_logged_in() && $post_name_correct) {
		wp_redirect("/login");
		exit;
	}
}

add_action('wp_head', 'mp_ssv_profile_page_login_redirect');

/**
 * This function sets up the profile page.
 *
 * @param string $content is the post content.
 *
 * @return string the edited post content.
 */
function mp_ssv_profile_page_setup($content)
{
	global $post;
	if ($post->post_name != 'profile') { //Not the Profile Page
		return $content;
	} else if (strpos($content, '[mp-ssv-frontend-members-profile]') === false) { //Not the Profile Page Tag
		return $content;
	}
	if (isset($_POST['what-to-save'])) {
		mp_ssv_save_members_profile($_POST['what-to-save']); //TODO Check if this works.
	}
	$content = mp_ssv_profile_page_content();

	return $content;
}

/**
 * @return string the content of the Profile Page.
 */
function mp_ssv_profile_page_content()
{
	if (current_theme_supports('mui')) {
		global $wpdb;
		$table = FRONTEND_MEMBERS_FIELDS_TABLE_NAME;
		$tabs = $wpdb->get_row(
			"SELECT *
					FROM $table
					WHERE field_type = 'tab';"
		);
		if (count($tabs) > 0) {
			$content = '<div class="mui--hidden-xs">';
			$content .= mp_ssv_profile_page_content_tabs();
			$content .= '</div>';
			$content .= '<div class="mui--visible-xs-block">';
//			$content .= mp_ssv_profile_page_content_single_page();
			$content .= '</div>';
		} else {
//			$content = mp_ssv_profile_page_content_single_page();
		}
	} else {
//		$content = mp_ssv_profile_page_content_non_mui();
	}

	return $content;
}

function mp_ssv_profile_page_content_tabs()
{
	$can_edit = false;
	if (isset($_GET['user_id'])) {
		$member = get_user_by('id', $_GET['user_id']);
	} else {
		$member = wp_get_current_user();
	}
	if ($member != wp_get_current_user() && !current_user_can('edit_user')) {
		$can_edit = false;
	}
	$member = new FrontendMember($member);
	ob_start();
	echo mp_ssv_get_profile_page_tab_select($member);
	$fields = FrontendMembersField::getAll();
	for ($i = 0; $i < count($fields); $i++) {
		$field = $fields[$i];
		if ($field instanceof FrontendMembersFieldTab) {
			if ($i == 0) {
				echo $field->getDivHeader(true); //Open the first (active) Tab-panel
			} else {
				echo "</div>"; //Close the previous Tab-panel
				echo $field->getDivHeader(); //Open the Tab-panel
			}
		} else {
			echo $field->getHTML($member, $can_edit);
		}
	}
	echo "</div>"; //Close the last Tab-panel

	return ob_get_clean();
}

/**
 * @param FrontendMember $member is to define if the logout button should be displayed.
 *
 * @return string containing a mui-tabs__bar.
 */
function mp_ssv_get_profile_page_tab_select($member)
{
	ob_start();
	$tabs = FrontendMembersField::getTabs();
	echo '<ul id="profile-menu" class="mui-tabs__bar mui-tabs__bar--justified">';
	for ($i = 0; $i < count($tabs); $i++) {
		$tab = $tabs[$i];
		if ($tab instanceof FrontendMembersFieldTab) {
			if ($i == 0) {
				echo $tab->getTabButton(true);
			} else {
				echo $tab->getTabButton();
			}
		}
	}
	if ($member->isCurrentUser()) {
		$url = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '?logout=success';
		echo '<li><a class="mui-btn mui-btn--flat mui-btn--danger" href="' . wp_logout_url($url) . '">Logout</a></li>';
	}
	echo '</ul>';

	return ob_get_clean();
}

function mp_ssv_save_members_profile($what_to_save)
{
	if (isset($_GET['user_id'])) {
		$user = get_user_by('id', $_GET['user_id']);
	} else {
		$user = wp_get_current_user();
	}
	foreach ($_POST as $name => $val) {
		if (strpos($name, "_reset") !== false) {
			$name = str_replace("_reset", "", $name);
		}
		if (!mp_ssv_update_user_meta($user->ID, $name, $val)) {
			echo "Cannot change the user-login. Please concider setting the field display to 'read-only' or 'disabled'";
		}
	}
	if (!function_exists("mp_ssv_update_mailchimp_member")) {
		mp_ssv_update_mailchimp_member($user);
	}
}

add_filter('the_content', 'mp_ssv_profile_page_setup');

?>
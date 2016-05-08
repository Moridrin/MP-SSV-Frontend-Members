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

add_action('wp_head', 'mp_ssv_profile_page_login_redirect', 9);

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
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		mp_ssv_save_members_profile();
	}
	$content = mp_ssv_profile_page_content();

	return $content;
}

/**
 * @return string the content of the Profile Page.
 */
function mp_ssv_profile_page_content()
{
	$tabs = FrontendMembersField::getTabs();
	if (current_theme_supports('mui')) {
		if (count($tabs) > 0) {
			$content = '<div class="mui--hidden-xs">';
			$content .= mp_ssv_profile_page_content_tabs();
			$content .= '</div>';
			$content .= '<div class="mui--visible-xs-block">';
			$content .= mp_ssv_profile_page_content_single_page();
			$content .= '</div>';
		} else {
			$content = mp_ssv_profile_page_content_single_page();
		}
	} else {
		$content = mp_ssv_profile_page_content_single_page();
	}

	return $content;
}

function mp_ssv_profile_page_content_tabs()
{
	$can_edit = false;
	if (isset($_GET['user_id'])) {
		$member = get_user_by('id', $_GET['user_id']);
		$action_url = '/profile/?user_id='.$member->ID;
	} else {
		$member = wp_get_current_user();
		$action_url = '/profile/';
	}
	if ($member == wp_get_current_user() || current_user_can('edit_user')) {
		$can_edit = true;
	}
	$member = new FrontendMember($member);
	ob_start();
	echo mp_ssv_get_profile_page_tab_select($member);
	$tabs = FrontendMembersField::getTabs();
	foreach ($tabs as $tab) {
		if ($tabs[0] == $tab) {
			$active_class = "mui--is-active";
		} else {
			$active_class = "";
		}
		?>
		<div class="mui-tabs__pane <?php echo $active_class; ?>" id="pane-<?php echo $tab->id; ?>">
			<form name="members_<?php echo $tab->title; ?>_form" id="member_<?php echo $tab->title; ?>_form" action="<?php echo $action_url ?>" method="post" enctype="multipart/form-data">
				<?php
				$items_in_tab = FrontendMembersField::getItemsInTab($tab);
				foreach ($items_in_tab as $item) {
					echo $item->getHTML($member);
				}
				?>
				<?php
				if ($can_edit) {
					?>
					<button class="mui-btn mui-btn--primary" type="submit" name="submit" id="submit" class="button-primary">Save</button>
					<?php
				}
				?>
			</form>
		</div>
		<?php
	}

	return ob_get_clean();
}

function mp_ssv_profile_page_content_single_page()
{
	$can_edit = false;
	if (isset($_GET['user_id'])) {
		$member = get_user_by('id', $_GET['user_id']);
	} else {
		$member = wp_get_current_user();
	}
	if ($member == wp_get_current_user() || current_user_can('edit_user')) {
		$can_edit = true;
	}
	$member = new FrontendMember($member);
	ob_start();
	$items = FrontendMembersField::getAll();
	?>
	<form name="members_form" id="members_form" action="/profile" method="post" enctype="multipart/form-data">
		<?php
		foreach ($items as $item) {
			if (!$item instanceof FrontendMembersFieldTab) {
				echo $item->getHTML($member);
			}
		}
		if ($can_edit) {
			echo '<button class="mui-btn mui-btn--primary" type="submit" name="submit" id="submit" class="button-primary">Save</button>';
		}
		?>
	</form>
	<?php
	if ($member->isCurrentUser()) {
		$url = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '?logout=success';
		echo '<button type="button" class="mui-btn mui-btn--flat mui-btn--danger" href="' . wp_logout_url($url) . '" >Logout</button>';
	}

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

function mp_ssv_save_members_profile()
{
	if (isset($_GET['user_id'])) {
		$user = get_user_by('id', $_GET['user_id']);
	} else {
		$user = wp_get_current_user();
	}
	$user = new FrontendMember($user);
	foreach ($_POST as $name => $val) {
		if (strpos($name, "_reset") !== false) {
			$name = str_replace("_reset", "", $name);
		}
		$update_success = $user->updateMeta($name, $val);
		if (!$update_success) {
			echo "Cannot change the user-login. Please consider setting the field display to 'disabled'";
		}
	}
	foreach ($_FILES as $name => $file) {
		if (!function_exists('wp_handle_upload')) {
			require_once(ABSPATH . 'wp-admin/includes/file.php');
		}
		$file_location = wp_handle_upload($file, array('test_form' => false));
		if ($file_location && !isset($file_location['error'])) {
			if ($user->getMeta($name) != $file_location['url']) {
				unlink($user->getMeta($name . '_path'));
				$user->updateMeta($name, $file_location["url"]);
				$user->updateMeta($name . '_path', $file_location["file"]);
			}
		}
	}
	unset($_POST);
}

add_filter('the_content', 'mp_ssv_profile_page_setup');

?>
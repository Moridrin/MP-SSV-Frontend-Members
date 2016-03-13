<?php
include_once "mp-ssv-general-options.php";
include_once "mp-ssv-mailchimp-options.php";
include_once "mailchimp-tab.php";

function mp_ssv_add_mp_ssv_frontend_members_options() {
	add_submenu_page( 'mp_ssv_settings', 'Frontend Members Options', 'Frontend Members', 'manage_options', __FILE__, 'mp_ssv_frontend_members_settings_page' );
}

function mp_ssv_frontend_members_settings_page() {
	global $options;
	$active_tab = "general";
	if(isset($_GET['tab'])) {
		$active_tab = $_GET['tab'];
	}
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		if ($active_tab == "general") {
			include_once "general-tab-save.php";
		} else if ($active_tab == "profile_page") {
			include_once "profile-page-tab-save.php";
		}
	}
	?>
	<div class="wrap">
		<h1>Frontend Members Options</h1>
		<h2 class="nav-tab-wrapper">
			<a href="?page=<?php echo __FILE__; ?>&tab=general" class="nav-tab <?php if ($active_tab == "general") { echo "nav-tab-active"; } ?>">General</a>
			<a href="?page=<?php echo __FILE__; ?>&tab=profile_page" class="nav-tab <?php if ($active_tab == "profile_page") { echo "nav-tab-active"; } ?>">Profile Page</a>
			<a href="?page=<?php echo __FILE__; ?>&tab=help" class="nav-tab <?php if ($active_tab == "help") { echo "nav-tab-active"; } ?>">Help</a>
		</h2>
		<?php
		if ($active_tab == "general") {
			include_once "general-tab.php";
		} else if ($active_tab == "profile_page") {
			include_once "profile-page-tab.php";
		} else if ($active_tab == "help") {
			include "help.php";
		}
		?>
	</div>
	<?php
}
add_action('admin_menu', 'mp_ssv_add_mp_ssv_frontend_members_options');
?>

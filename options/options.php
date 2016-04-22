<?php
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
			<a href="http://studentensurvival.com/mp-ssv/mp-ssv-frontend-members/" target="_blank" class="nav-tab">Help <img src="<?php echo plugin_dir_url('mp-ssv-general/images/link-new-tab.png'); ?>link-new-tab.png" width="14px" style="vertical-align:middle"></a>
		</h2>
		<?php
		if ($active_tab == "general") {
			include_once "general-tab.php";
		} else if ($active_tab == "profile_page") {
			include_once "profile-page-tab.php";
		}
		?>
	</div>
	<?php
}
add_action('admin_menu', 'mp_ssv_add_mp_ssv_frontend_members_options');
?>

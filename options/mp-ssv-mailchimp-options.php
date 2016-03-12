<?php
if (!function_exists("add_mp_ssv_mailchimp_menu")) {
	function add_mp_ssv_mailchimp_menu() {
		add_submenu_page( 'mp_ssv_settings', 'MailChimp Options', 'MailChimp', 'manage_options', "mp-ssv-mailchimp-options", 'mp_ssv_mailchimp_settings_page' );
	}
	function mp_ssv_mailchimp_settings_page() {
		$active_tab = "general";
		if(isset($_GET['tab'])) {
			$active_tab = $_GET['tab'];
		}
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if ($active_tab == "general") {
				mp_ssv_mailchimp_settings_page_general_save();
			} else if ($active_tab == "frontend_members") {
				mp_ssv_mailchimp_settings_page_frontend_members_tab_save();
			} else if ($active_tab == "events") {
				mp_ssv_mailchimp_settings_page_events_tab_save();
			}
		}
		?>
		<div class="wrap">
			<h1>MP-SSV MailChimp Options</h1>
			<h2 class="nav-tab-wrapper">
				<a href="?page=mp-ssv-mailchimp-options&tab=general" class="nav-tab <?php if ($active_tab == "general") { echo "nav-tab-active"; } ?>">General</a>
				<?php if (function_exists("mp_ssv_mailchimp_settings_page_frontend_members_tab")) { ?>
					<a href="?page=mp-ssv-mailchimp-options&tab=frontend_members" class="nav-tab <?php if ($active_tab == "frontend_members") { echo "nav-tab-active"; } ?>">Frontend Members</a>
				<?php } ?>
				<?php if (function_exists("mp_ssv_mailchimp_settings_page_events_tab")) { ?>
					<a href="?page=mp-ssv-mailchimp-options&tab=events" class="nav-tab <?php if ($active_tab == "events") { echo "nav-tab-active"; } ?>">Events</a>
				<?php } ?>
				<a href="?page=mp-ssv-mailchimp-options&tab=help" class="nav-tab <?php if ($active_tab == "help") { echo "nav-tab-active"; } ?>">Help</a>
			</h2>
		</div>
		<?php
		if ($active_tab == "general") {
			mp_ssv_mailchimp_settings_page_general();
		} else if ($active_tab == "frontend_members") {
			mp_ssv_mailchimp_settings_page_frontend_members_tab();
		} else if ($active_tab == "events") {
			mp_ssv_mailchimp_settings_page_events_tab();
		} else if ($active_tab == "help") {
			echo "tmp";
		}
	}
	add_action('admin_menu', 'add_mp_ssv_mailchimp_menu');
	
	
	function mp_ssv_mailchimp_settings_page_general() {
		?>
		<form method="post" action="#">
			<table class="form-table">
				<tr>
					<th scope="row">MailChimp API Key</th>
					<td>
						<input type="text" class="regular-text" name="mp_ssv_mailchimp_api_key" value="<?php echo get_option('mp_ssv_mailchimp_api_key'); ?>"/>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
		<?php
	}
	
	function mp_ssv_mailchimp_settings_page_general_save() {
		update_option('mp_ssv_mailchimp_api_key', $_POST['mp_ssv_mailchimp_api_key']);
	}
}

if (!function_exists("get_merge_fields_select")) {
	function get_merge_fields_select($id, $tag_name, $disabled, $mailchimp_merge_tags) {
		if ($id == "") {
			$s = uniqid('', true);
			$id = base_convert($s, 16, 36);
		}
		?><select name="mailchimp_<?php echo $id; ?>" <?php if ($disabled) { echo "disabled"; } ?>><?php
			foreach ($mailchimp_merge_tags as $tag) {
				echo '<option value="'.$tag.'" ';
				if ($tag == $tag_name) { echo "selected"; }
				echo '>'.$tag.'</option>';
			}
			?></select><?php
	}
}

if (!function_exists("get_merge_fields_select_for_javascript")) {
	function get_merge_fields_select_for_javascript($disabled, $mailchimp_merge_tags) {
		?><select name="mailchimp_' + id + '" <?php if ($disabled) { echo "disabled"; } ?>><?php
			foreach ($mailchimp_merge_tags as $tag) {
				echo '<option value="'.$tag.'" ';
				echo '>'.$tag.'</option>';
			}
			?></select><?php
	}
}

if (!function_exists("get_merge_fields")) {
	function get_merge_fields($listID) {
		$apiKey = get_option('mp_ssv_mailchimp_api_key');
	
		$memberCenter = substr($apiKey,strpos($apiKey,'-')+1);
		$url = 'https://' . $memberCenter . '.api.mailchimp.com/3.0/lists/' . $listID . '/merge-fields';
		$ch = curl_init($url);
	
		curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $apiKey);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	
		$curl_results = json_decode(curl_exec($ch), true)["merge_fields"];
		$results = array();
		foreach ($curl_results as $result => $value) {
			$results[] = $value["tag"];
		}
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
	
		return $results;
	}
}
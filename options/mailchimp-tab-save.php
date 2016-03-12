<?php
function mp_ssv_mailchimp_settings_page_frontend_members_tab_save() {
	global $wpdb;
	$table_name = $wpdb->prefix."mp_ssv_mailchimp_merge_fields";
	$wpdb->delete($table_name, array('is_deletable' => 1));
	$member_tag = "";
	$mailchimp_tag = "";
	foreach( $_POST as $id => $val ) {
		if ($id == "mailchimp_member_sync_list_id") {
			update_option('mailchimp_member_sync_list_id', $val);
		} else if (strpos($id, "member_") !== false) {
			$member_tag = $val;
		} else if (strpos($id, "mailchimp_") !== false) {
			$mailchimp_tag = $val;
		} else if (strpos($id, "submit_option_") !== false) {
			$wpdb->insert(
				$table_name,
				array(
					'member_tag' => $member_tag,
					'mailchimp_tag' => $mailchimp_tag
				),
				array(
					'%s',
					'%s'
				) 
			);
		}
	}
}
?>
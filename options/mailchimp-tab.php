<?php
include_once "mailchimp-tab-save.php";
function mp_ssv_mailchimp_settings_page_frontend_members_tab() {
	$mailchimp_merge_tags = get_merge_fields("7fdbdf25c4");
	
	global $wpdb;
	$table_name = $wpdb->prefix."mp_ssv_mailchimp_merge_fields";
	$fields = $wpdb->get_results(
		"SELECT *
			FROM $table_name"
	);
	?>
	<form method="post" action="#">
		<table class="form-table">
			<tr>
				<th scope="row">List ID</th>
				<td><input type="text" class="regular-text" name="mailchimp_member_sync_list_id" value="<?php echo get_option('mailchimp_member_sync_list_id'); ?>"/></td>
			</tr>
			<tr>
				<th scope="row">Membber Field Name</th>
				<th scope="row">*|MERGE|* tag</th>
			</tr>
			<tr>
				<td><input type="text" class="regular-text" name="member_first_name" value="first_name" readonly/></td>
				<td> <?php get_merge_fields_select("first_Name", "FNAME", true, $mailchimp_merge_tags); ?> </td>
			</tr>
			<tr>
				<td><input type="text" class="regular-text" name="member_last_name" value="last_name" readonly/></td>
				<td> <?php get_merge_fields_select("last_Name", "LNAME", true, $mailchimp_merge_tags); ?> </td>
			</tr>
			<?php 
			foreach ($fields as $field) {
				$field = json_decode(json_encode($field),true);
				$member_tag = stripslashes($field["member_tag"]);
				$mailchimp_tag = stripslashes($field["mailchimp_tag"]);
				?>
				<tr>
					<td><input type="text" class="regular-text" name="member_<?php echo $member_tag; ?>" value="<?php echo $member_tag; ?>"/></td>
					<td> <?php get_merge_fields_select($member_tag, $mailchimp_tag, false, $mailchimp_merge_tags); ?> </td>
					<td><input type="hidden" name="submit_option_first_name"></td>
				</tr>
				<?php
			}
			?>
		</table>
		<?php submit_button(); ?>
	</form>
	<?php
}
?>
<form method="post" action="#">
	<table class="form-table">
		<tr>
			<th scope="row">Register Page</th>
			<td>
				<select name="mp_ssv_frontend_members_register_page">
					<option value="same_as_profile_page" <?php if (esc_attr(stripslashes(get_option('mp_ssv_frontend_members_register_page'))) == 'same_as_profile_page') { echo "selected"; } ?>>Same as Profile Page</option>
					<option value="required_profile_page_fields_only"<?php if (esc_attr(stripslashes(get_option('mp_ssv_frontend_members_register_page'))) == 'required_profile_page_fields_only') { echo "selected"; } ?>>Required Profile Page fields Only</option>
					<option value="custom"<?php if (esc_attr(stripslashes(get_option('mp_ssv_frontend_members_register_page'))) == 'custom') { echo "selected"; } ?>>Custom</option>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row">Custom User Groups</th>
			<td><input type="checkbox" name="mp_ssv_frontend_members_guest_custom_roles_enabled" value="true" <?php if (get_option('mp_ssv_frontend_members_guest_custom_roles_enabled') == 'true') { echo "checked"; } ?>/></td>
		</tr>
	</table>
	<?php submit_button(); ?>
</form>
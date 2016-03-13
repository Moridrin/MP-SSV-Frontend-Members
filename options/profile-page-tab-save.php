<?php
global $wpdb;
$component_table_name = $wpdb->prefix."mp_ssv_frontend_members_fields";
$group_options_table_name = $wpdb->prefix."mp_ssv_frontend_members_fields_group_options";
$wpdb->delete($component_table_name, array('is_deletable' => 1));
$wpdb->delete($group_options_table_name, array('is_deletable' => 1));
$title = "";
$component = "";
$tab = "";
$group_option = "";
foreach( $_POST as $id => $val ) {
	//echo "<xmp>id:\t".$id."</xmp><xmp>val:\t".$val."</xmp><br/>";
	if (strpos($id, "title_option_") !== false) {
		$title = $val;
	} else if (strpos($id, "group_option_item_") !== false) {
		$group_option = $val;
	} else if (strpos($id, "component_option_") !== false) {
		$component = $val;
		if ($component == "[tab]") {
			$tab = $title;
		}
	} else if (strpos($id, "is_required_option_") !== false) {
		if ($val == "on") {
			$component .= "[image]required";
		}
	} else if (strpos($id, "show_preview_option_") !== false) {
		if ($val == "on") {
			$component .= "[image]show_preview";
		}
	} else if (strpos($id, "submit_group_option_") !== false) {
		if ($title != "" && $group_option != "") {
			$wpdb->insert(
				$group_options_table_name,
				array(
					'parent_group' => $title,
					'option_text' => $group_option
				),
				array(
					'%s',
					'%s'
				) 
			);
		}
		$group_option = "";
	} else if (strpos($id, "submit_option_") !== false) {
		if ($title != "" && $component != "") {
			$wpdb->insert(
				$component_table_name,
				array(
					'title' => $title,
					'component' => $component,
					'tab' => $tab
				),
				array(
					'%s',
					'%s',
					'%s'
				) 
			);
		}
		$title = "";
		$component = "";
	}
}
?>
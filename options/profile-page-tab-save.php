<?php
global $wpdb;
$table_name = $wpdb->prefix."mp_ssv_frontend_members_fields";
$wpdb->delete($table_name, array('is_deletable' => 1));
$title = "";
$component = "";
$tab = "";
foreach( $_POST as $id => $val ) {
	if (strpos($id, "title_option_") !== false) {
		$title = $val;
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
	} else if (strpos($id, "submit_option_") !== false) {
		if ($title != "" && $component != "") {
			$wpdb->insert(
				$table_name,
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
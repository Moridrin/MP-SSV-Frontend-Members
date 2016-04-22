<?php
global $wpdb;
$table_fields = $wpdb->prefix."mp_ssv_frontend_members_fields";
$table_field_meta = $wpdb->prefix."mp_ssv_frontend_members_field_meta";
$wpdb->show_errors();
$wpdb->update(
	$table_field_meta,
	array("meta_value" => "no"),
	array("meta_value" => "yes"),
	array('%s'),
	array('%s')
);
$replace_id = -1;
$replace_with = -1;
$field_index = 0;
$group_type = "";
foreach( $_POST as $name => $val ) {
	$id = explode("_", $name)[0];
	$name = str_replace($id."_", "", $name);
	$parent_id = -1;
	if (strpos($name, "_option") !== false) {
		$parent_id = $id;
		$id = str_replace("_option", "", $name);
		$name = "value";
	}
	if (strpos($name, "input_type") !== false && strpos($val, "group") !== false) {
		$group_type = explode("_", $val)[0];
	}
	if ($id == $replace_id) {
		$id = $replace_with;
	}
	if ($name == "submit") {
		//do nothing
	} else if ($name == "field_title" && $val == "") {
		$wpdb->delete(
			$table_fields,
			array("id" => $id),
			array('%d')
		);
		$wpdb->delete(
			$table_field_meta,
			array("field_id" => $id),
			array('%d')
		);
		$options = $wpdb->get_results( 
			"SELECT field_id
				FROM $table_field_meta
				WHERE meta_key = 'parent_id'
				AND meta_value = $id;"
		);
		$options = json_decode(json_encode($options), true);
		foreach ($options as $option) {
			$wpdb->delete(
				$table_fields,
				array("id" => $option["field_id"]),
				array('%d')
			);
			$wpdb->delete(
				$table_field_meta,
				array("field_id" => $option["field_id"]),
				array('%d')
			);
		}
	} else if ($name == "field_title") {
		$return = $wpdb->update(
			$table_fields,
			array("field_index" => $field_index),
			array("id" => $id),
			array('%d'),
			array('%d')
		);
		$field_index++;
		$test_id = json_decode(json_encode($wpdb->get_var( 
			"SELECT id
				FROM $table_fields
				WHERE id = $id;"
		)), true);
		if ($test_id == null) {
			$return = $wpdb->insert(
				$table_fields,
				array($name => $val),
				array('%s')
			);
			$replace_id = $id;
			$replace_with = $wpdb->insert_id;
		} else {
			$return = $wpdb->update(
				$table_fields,
				array($name => $val),
				array("id" => $id),
				array('%s'),
				array('%d')
			);
		}
		$return = $wpdb->update(
			$table_fields,
			array("field_index" => $field_index),
			array("id" => $id),
			array('%d'),
			array('%d')
		);
	} else if ($parent_id > -1 && $val == "") {
		$wpdb->delete(
			$table_fields,
			array("id" => $id),
			array('%d')
		);
		$wpdb->delete(
			$table_field_meta,
			array("field_id" => $id),
			array('%d')
		);
	} else if ($parent_id > -1) {
		$test_id = json_decode(json_encode($wpdb->get_var( 
			"SELECT id
				FROM $table_fields
				WHERE id = $id;"
		)), true);
		if ($test_id == null) {
			$wpdb->insert(
				$table_fields,
				array("field_index" => $id, "field_type" => "group_option", "field_title" => $val),
				array('%d', '%s', '%s')
			);
			$id = $wpdb->insert_id;
			$wpdb->insert(
				$table_field_meta,
				array("field_id" => $id, "meta_key" => "parent_id", "meta_value" => $parent_id),
				array('%d', '%s', '%s')
			);
			$wpdb->insert(
				$table_field_meta,
				array("field_id" => $id, "meta_key" => "type", "meta_value" => $group_type),
				array('%d', '%s', '%s')
			);
			$wpdb->insert(
				$table_field_meta,
				array("field_id" => $id, "meta_key" => "value", "meta_value" => $val),
				array('%d', '%s', '%s')
			);
		} else {
			$return = $wpdb->update(
				$table_fields,
				array("field_title" => $val),
				array("id" => $id),
				array('%s'),
				array('%d')
			);
			$return = $wpdb->update(
				$table_field_meta,
				array("meta_value" => $val),
				array("field_id" => $id, "meta_key" => $name),
				array('%s'),
				array('%d', '%s')
			);
		}
	} else if ($name == "field_type") {
		$return = $wpdb->update(
			$table_fields,
			array($name => $val),
			array("id" => $id),
			array('%s'),
			array('%d')
		);
	} else {
		if ($val == "") {
			$wpdb->delete(
				$table_field_meta,
				array("field_id" => $id, "meta_key" => $name),
				array('%d', '%s')
			);
		} else {
			$test_id = json_decode(json_encode($wpdb->get_results(
				"SELECT *
					FROM $table_field_meta
					WHERE field_id = $id
					AND meta_key = '$name';")), true);
			if ($test_id == null) {
				$wpdb->insert(
					$table_field_meta,
					array("field_id" => $id, "meta_key" => $name, "meta_value" => $val),
					array('%d', '%s', '%s')
				);
			} else {
				$wpdb->update(
					$table_field_meta,
					array("meta_value" => $val),
					array("field_id" => $id, "meta_key" => $name),
					array('%s'),
					array('%d', '%s')
				);
			}
		}
	}
}
?>
<?php
/**
 * Created by: Jeroen Berkvens
 * Date: 23-4-2016
 * Time: 13:57
 */

require_once "FrontendMembersFieldTab.php";
require_once "FrontendMembersFieldHeader.php";
require_once "FrontendMembersFieldInput.php";

class FrontendMembersField
{
	protected $id;
	protected $index;
	public $type;
	public $title;

	/**
	 * FrontendMembersField constructor.
	 *
	 * @param int    $id    identifies the field in the database.
	 * @param int    $index identifies the order in which it is displayed.
	 * @param string $type  is the type of FrontendMembersField.
	 * @param string $title is the title of this FrontendMembersField.
	 */
	protected function __construct($id, $index, $type, $title)
	{
		$this->id = $id;
		$this->index = $index;
		$this->type = $type;
		$this->title = $title;
	}

	/**
	 * This function creates a new FrontendMembersField and adds it to the database.
	 *
	 * @param int    $index is an id that specifies the display (/tab) order for the field.
	 * @param string $title is the title of this component.
	 * @param string $type  specifies the type of field. Either "tab", "header", "input" or "group_option".
	 *
	 * @return FrontendMembersField the just created instance.
	 */
	protected static function createField($index, $title, $type)
	{
		global $wpdb;
		$table = FRONTEND_MEMBERS_FIELDS_TABLE_NAME;
		$max_in_database = $wpdb->get_var('SELECT MAX(id) FROM ' . $table . ';');
		if ($max_in_database == null) {
			$id = 0;
		} else {
			$id = $max_in_database + 1;
		}
		$wpdb->insert(
			$table,
			array(
				'id'          => $id,
				'field_index' => $index,
				'field_type'  => $type,
				'field_title' => $title
			),
			array(
				'%d',
				'%d',
				'%s',
				'%s'
			)
		);

		return new FrontendMembersField($id, $index, $type, $title);
	}

	/**
	 * This function returns all tabs.
	 * @return array
	 */
	public static function getTabs()
	{
		global $wpdb;
		$table = FRONTEND_MEMBERS_FIELDS_TABLE_NAME;
		$tabs = array();
		$database_rows = json_decode(json_encode($wpdb->get_results("SELECT * FROM $table WHERE field_type = 'tab' ORDER BY field_index ASC;")), true);
		foreach ($database_rows as $database_row) {
			$tabs[] = FrontendMembersFieldTab::fromDatabaseFields($database_row);
		}

		return $tabs;
	}

	/**
	 * This function gets the field metadata specified by the key.
	 *
	 * @param string $key is the key defining what metadata should be returned.
	 *
	 * @return string the meta value linked to the given key.
	 */
	public function getMeta($key)
	{
		global $wpdb;
		$table = FRONTEND_MEMBERS_FIELD_META_TABLE_NAME;
		$value = $wpdb->get_var(
			"SELECT meta_value
			FROM $table
			WHERE field_id = '$this->id'
			AND meta_key = '$key';"
		);

		return $value;
	}

	/**
	 * This function gets the field metadata specified by the key.
	 *
	 * @param string $key is the key defining what metadata should be returned.
	 *
	 * @return string the meta value linked to the given key.
	 */
	public function getMetaFromPOST($key)
	{
		if (!isset($_POST[$this->id . "_" . $key])) {
			return "no";
		}

		return $_POST[$this->id . "_" . $key];
	}

	/**
	 * This function adds a property to this FrontendMembersField.
	 *
	 * @param string $key   is the key value that defines the property of the field.
	 * @param string $value is the value of the property.
	 */
	public function setMeta($key, $value)
	{
		global $wpdb;
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$table = FRONTEND_MEMBERS_FIELD_META_TABLE_NAME;
		$wpdb->insert(
			$table,
			array(
				'id'         => $this->id,
				'meta_key'   => $key,
				'meta_value' => $value
			),
			array(
				'%d',
				'%s',
				'%s'
			)
		);
	}

	/**
	 * @param int $id is the id to find the field in the database.
	 *
	 * @return FrontendMembersField|FrontendMembersFieldHeader|FrontendMembersFieldInputText|FrontendMembersFieldInputTextCheckbox|FrontendMembersFieldInputTextSelect|FrontendMembersFieldTab
	 */
	protected static function fromID($id)
	{
		global $wpdb;
		$table = FRONTEND_MEMBERS_FIELDS_TABLE_NAME;
		$database_fields = json_decode(json_encode($wpdb->get_row(
			"SELECT *
					FROM $table
					WHERE id = $id;"
		)), true);
		$field = self::fromDatabaseFields($database_fields);
		switch ($field->type) {
			case "tab":
				$field = new FrontendMembersFieldTab($field);
				break;
			case "header":
				$field = new FrontendMembersFieldHeader($field);
				break;
			case "input":
				$input_type = $field->getMeta("input_type");
				$name = $field->getMeta("name");
				$field = new FrontendMembersFieldInput($field, $input_type, $name);
				switch ($input_type) {
					case "custom":
						$field = new FrontendMembersFieldInputCustom($field, $field->getMeta('required'), $field->getMeta('display'), $field->getMeta('placeholder'));
						break;
					case "image":
						$field = new FrontendMembersFieldInputImage($field, $field->getMeta('required'), $field->getMeta('preview'));
						break;
					case "role_checkbox":
						$field = new FrontendMembersFieldInputRoleCheckbox($field, $field->getMeta('role'), $field->getMeta('display'));
						break;
					case "role_select":
						$field = new FrontendMembersFieldInputRoleSelect($field, $field->getOptions(), $field->getMeta('display'));
						break;
					case "text":
						$field = new FrontendMembersFieldInputText($field, $field->getMeta('required'), $field->getMeta('display'), $field->getMeta('placeholder'));
						break;
					case "text_checkbox":
						$field = new FrontendMembersFieldInputTextCheckbox($field, $field->getMeta('help_text'), $field->getMeta('display'));
						break;
					case "text_select":
						$field = new FrontendMembersFieldInputTextSelect($field, $field->getOptions(), $field->getMeta('display'));
						break;
				}
				break;
		}

		return $field;
	}

	/**
	 * @param array $database_fields the array returned by wpdb.
	 *
	 * @return FrontendMembersField
	 */
	protected static function fromDatabaseFields($database_fields)
	{
		return new FrontendMembersField($database_fields['id'], $database_fields['field_index'], $database_fields['field_type'], $database_fields['field_title']);
	}

	/**
	 * @param array $filters are applied to the SQL query.
	 *
	 * @return array of all the FrontendMembersFields.
	 */
	public static function getAll($filters = array("field_type" => '!group_option'))
	{
		global $wpdb;
		$table = FRONTEND_MEMBERS_FIELDS_TABLE_NAME;
		$sql = "SELECT id FROM $table";
		foreach ($filters as $filter => $value) {
			if (substr($value, 0, 1) == "!") {
				$sql .= " WHERE " . $filter . " != '" . $value . "'";
			} else {
				$sql .= " WHERE " . $filter . " = '" . $value . "'";
			}
		}
		$sql .= " ORDER BY id ASC;";

		$database_fields = json_decode(json_encode($wpdb->get_results($sql)), true);
		$fields = array();
		foreach ($database_fields as $database_field) {
			$field = self::fromID($database_field['id']);
			$fields[] = $field;
		}

		return $fields;
	}

	public static function getAllAsHTML($frontend_member, $can_edit, $is_current_user)
	{
		ob_start();
		$fields = self::getAll();
		echo '<ul id="profile-menu" class="mui-tabs__bar mui-tabs__bar--justified">';
		for ($i = 0; $i < count($fields); $i++) {
			$field = $fields[$i];
			if ($field instanceof FrontendMembersFieldTab) {
				if ($i == 0) {
					echo $field->getTabButton(true);
				} else {
					echo $field->getTabButton();
				}
			}
		}
		$url = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '?logout=success';
		echo '<li><a class="mui-btn mui-btn--flat mui-btn--danger" href="' . wp_logout_url($url) . '">Logout</a></li>';
		echo '</ul>';
		for ($i = 0; $i < count($fields); $i++) {
			$field = $fields[$i];
			if ($field instanceof FrontendMembersFieldTab) {
				if ($i == 0) {
					echo $field->getDivHeader(true);
				} else {
					echo "</div>"; //Close the previous Tab
					echo $field->getDivHeader();
				}
			} else {
				echo $field->getHTML($frontend_member, $can_edit);
			}
		}

		return ob_get_clean();
	}

	public static function saveAllFromPost()
	{
		$id = 0;
		foreach ($_POST as $name => $val) {
			if (strpos($name, "_field_title") !== false) {
				$id++;
				$_POST[str_replace("_field_title", "", $name) . "_field_index"] = $id;
				$field = self::fromPOST(str_replace("_field_title", "", $name));
				$field->save();
			}
		}
	}

	/**
	 * This function returns all the group options for this field.
	 * @return array|null with all options linked to this FrontendMembersField or null if this is not a group field.
	 */
	public function getOptions()
	{
		global $wpdb;
		$table = FRONTEND_MEMBERS_FIELD_META_TABLE_NAME;
		$options = json_decode(json_encode($wpdb->get_results(
			"SELECT id
			FROM $table
			WHERE meta_key = 'parent_id'
			AND meta_value = '$this->id';"
		)), true);

		$table = FRONTEND_MEMBERS_FIELDS_TABLE_NAME;
		$sql = "SELECT * FROM $table WHERE field_type = 'group_option' AND (";
		for ($i = 0; $i < count($options); $i++) {
			if ($i != 0) {
				$sql .= " OR ";
			}
			$sql .= "id = " . $options[$i]["id"];
		}
		$sql .= ") ORDER BY id ASC;";
		$group_fields = json_decode(json_encode($wpdb->get_results($sql)), true);
		$table_name = $wpdb->prefix . "mp_ssv_frontend_members_field_meta";
		for ($i = 0; $i < count($group_fields); $i++) {
			$group_field_meta = json_decode(json_encode($wpdb->get_results(
				"SELECT meta_key, meta_value
				FROM $table_name
				WHERE id = " . $group_fields[$i]["id"] . ";"
			)), true);
			foreach ($group_field_meta as $meta_item) {
				$group_fields[$i][$meta_item["meta_key"]] = $meta_item["meta_value"];
			}
		}

		return $group_fields;
	}

	/**
	 * @param string $content is the extra content that it gets from it's child.
	 * @param bool   $visible defines if this option row should be displayed (used to hide tab rows for themes that do not support mui).
	 *
	 * @return string a row that can be added to the profile page options table.
	 */
	protected function getOptionRowField($content, $visible = true)
	{
		ob_start();
		echo mp_ssv_get_td(mp_ssv_get_draggable_icon());
		echo mp_ssv_get_td(mp_ssv_get_text_input("Field Title", $this->id, $this->title));
		if (get_theme_support('mui')) {
			echo mp_ssv_get_td(mp_ssv_get_select("Field Type", $this->id, $this->type, array("Tab", "Header", "Input"), array('onchange="mp_ssv_type_changed(\'' . $this->id . '\')"')));
		} else {
			echo mp_ssv_get_td(mp_ssv_get_select("Field Type", $this->id, $this->type, array("Header", "Input"), array('onchange="mp_ssv_type_changed(\'' . $this->id . '\')"')));
		}
		echo $content;

		return mp_ssv_get_tr($this->id, ob_get_clean(), $visible);
	}


	/**
	 * This method returns a FrontendMembersField created from the POST values from a form.
	 *
	 * @param int $id is the id of the FrontendMembersField that should be created.
	 *
	 * @return FrontendMembersField|FrontendMembersFieldHeader|FrontendMembersFieldInputText|FrontendMembersFieldInputTextCheckbox|FrontendMembersFieldInputTextSelect|FrontendMembersFieldTab
	 */
	public static function fromPOST($id)
	{
		$variables = array();
		foreach ($_POST as $name => $value) {
			if (in_array($id, explode("_", $name))) {
				$variables[str_replace($id . "_", "", $name)] = $value;
			}
		}
		$field = new FrontendMembersField($id, $variables['field_index'], $variables["field_type"], $variables["field_title"]);
		unset($variables["id"]);
		unset($variables["field_type"]);
		unset($variables["field_title"]);
		switch ($field->type) {
			case "tab":
				$field = new FrontendMembersFieldTab($field);
				break;
			case "header":
				$field = new FrontendMembersFieldHeader($field);
				break;
			case "input":
				$input_type = $field->getMetaFromPOST("input_type");

				$name = $field->getMetaFromPOST("name");
				$field = new FrontendMembersFieldInput($field, $input_type, $name);
				switch ($input_type) {
					case "custom":
						$field = new FrontendMembersFieldInputCustom($field, $field->getMetaFromPOST('required'), $field->getMetaFromPOST('display'), $field->getMetaFromPOST('placeholder'));
						break;
					case "image":
						$field = new FrontendMembersFieldInputImage($field, $field->getMetaFromPOST('required'), $field->getMetaFromPOST('preview'));
						break;
					case "role_checkbox":
						$field = new FrontendMembersFieldInputRoleCheckbox($field, $field->getMetaFromPOST('role'), $field->getMetaFromPOST('display'));
						break;
					case "role_select":
						$field = new FrontendMembersFieldInputRoleSelect($field, $field->getOptions(), $field->getMetaFromPOST('display'));
						break;
					case "text":
						$field = new FrontendMembersFieldInputText($field, $field->getMetaFromPOST('required'), $field->getMetaFromPOST('display'), $field->getMetaFromPOST('placeholder'));
						break;
					case "text_checkbox":
						$field = new FrontendMembersFieldInputTextCheckbox($field, $field->getMetaFromPOST('help_text'), $field->getMetaFromPOST('display'));
						break;
					case "text_select":
						$field = new FrontendMembersFieldInputTextSelect($field, $field->getOptions(), $field->getMetaFromPOST('display'));
						break;
				}
				break;
		}

		return $field;
	}

	protected function save()
	{
		global $wpdb;
		$table = FRONTEND_MEMBERS_FIELDS_TABLE_NAME;
		$update = $wpdb->get_results(
			"SELECT id
					FROM $table
					WHERE id = $this->id;"
		);
		if (count($update) > 0) {
			$wpdb->update(
				$table,
				array("field_index" => $this->index, "field_type" => $this->type, "field_title" => $this->title),
				array("id" => $this->id),
				array('%d', '%s', '%s'),
				array('%d')
			);
		} else {
			$wpdb->insert(
				$table,
				array("id" => $this->id, "field_index" => $this->index, "field_type" => $this->type, "field_title" => $this->title),
				array('%d', '%d', '%s', '%s')
			);
		}
	}
}
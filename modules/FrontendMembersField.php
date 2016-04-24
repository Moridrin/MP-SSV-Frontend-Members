<?php
/**
 * Created by: Jeroen Berkvens
 * Date: 23-4-2016
 * Time: 13:57
 */

include "FrontendMembersFieldTab.php";
include "FrontendMembersFieldHeader.php";
include "FrontendMembersFieldInput.php";

class FrontendMembersField
{
	protected $id;
	protected $index;
	public $type;
	public $title;

	/**
	 * FrontendMembersField constructor.
	 *
	 * @param int    $id    identifies the database entry.
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
	 * @param int    $index is an index that specifies the display (/tab) order for the field.
	 * @param string $title is the title of this component.
	 * @param string $type  specifies the type of field. Either "tab", "header", "input" or "group_option".
	 *
	 * @return FrontendMembersField the just created instance.
	 */
	protected static function createField($index, $title, $type)
	{
		global $wpdb;
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$table = FRONTEND_MEMBERS_FIELDS_TABLE_NAME;
		$wpdb->insert(
			$table,
			array(
				'field_index' => $index,
				'field_type'  => $type,
				'field_title' => $title
			),
			array(
				'%d',
				'%s',
				'%s'
			)
		);

		$id = $wpdb->get_var(
			"SELECT id
			FROM $table
			ORDER BY id DESC
			LIMIT 0 , 1"
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
		$tabs = json_decode(json_encode($wpdb->get_results("SELECT * FROM $table WHERE field_type = 'tab' ORDER BY field_index ASC;")), true);

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

		return json_decode(json_encode($value), true);
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
				'field_id'   => $this->id,
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
		$database_field = json_decode(json_encode($wpdb->get_row(
			"SELECT *
					FROM $table
					WHERE id = $id;"
		)), true);
		$field = self::fromDatabase($database_field);
		switch ($field->type) {
			case "tab":
				return new FrontendMembersFieldTab($field);
				break;
			case "header":
				return new FrontendMembersFieldHeader($field);
				break;
			case "input":
				$input_type = $field->getMeta("input_type");
				$name = $field->getMeta("name");
				$field = new FrontendMembersFieldInput($field, $input_type, $name);
				switch ($input_type) {
					case "custom":
						return new FrontendMembersFieldInputCustom($field, $field->getMeta('required'), $field->getMeta('display'), $field->getMeta('placeholder'));
						break;
					case "image":
						return new FrontendMembersFieldInputImage($field, $field->getMeta('required'), $field->getMeta('preview'));
						break;
					case "role_checkbox":
						return new FrontendMembersFieldInputRoleCheckbox($field, $field->getMeta('role'), $field->getMeta('display'));
						break;
					case "role_select":
						return new FrontendMembersFieldInputRoleSelect($field, $field->getOptions(), $field->getMeta('display'));
						break;
					case "text":
						return new FrontendMembersFieldInputText($field, $field->getMeta('required'), $field->getMeta('display'), $field->getMeta('placeholder'));
						break;
					case "text_checkbox":
						return new FrontendMembersFieldInputTextCheckbox($field, $field->getMeta('help_text'), $field->getMeta('display'));
						break;
					case "text_select":
						return new FrontendMembersFieldInputTextSelect($field, $field->getOptions(), $field->getMeta('display'));
						break;
				}
				break;
		}

		return new FrontendMembersField($database_field['id'], $database_field['field_index'], $database_field['field_type'], $database_field['field_title']);
	}

	/**
	 * @param array $database_field the array returned by wpdb.
	 *
	 * @return FrontendMembersField
	 */
	private static function fromDatabase($database_field)
	{
		return new FrontendMembersField($database_field['id'], $database_field['field_index'], $database_field['field_type'], $database_field['field_title']);
	}

	/**
	 * @return array of all the FrontendMembersFields.
	 */
	public static function getAll()
	{
		global $wpdb;
		$table = FRONTEND_MEMBERS_FIELDS_TABLE_NAME;
		$database_fields = json_decode(json_encode($wpdb->get_results(
			"SELECT id
					FROM $table
					WHERE field_type != 'group_option'
					ORDER BY field_index ASC;"
		)), true);
		$fields = array();
		foreach ($database_fields as $database_field) {
			$field = self::fromID($database_field['id']);
			$fields[] = $field;
		}
		return $fields;
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
			"SELECT field_id
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
			$sql .= "id = " . $options[$i]["field_id"];
		}
		$sql .= ") ORDER BY field_index ASC;";
		$group_fields = json_decode(json_encode($wpdb->get_results($sql)), true);
		$table_name = $wpdb->prefix . "mp_ssv_frontend_members_field_meta";
		for ($i = 0; $i < count($group_fields); $i++) {
			$group_field_meta = json_decode(json_encode($wpdb->get_results(
				"SELECT meta_key, meta_value
				FROM $table_name
				WHERE field_id = " . $group_fields[$i]["id"] . ";"
			)), true);
			foreach ($group_field_meta as $meta_item) {
				$group_fields[$i][$meta_item["meta_key"]] = $meta_item["meta_value"];
			}
		}

		return $group_fields;
	}

	protected function getOptionRowField($content)
	{
		ob_start();
		?>
		<tr id="<?php echo $this->id; ?>" style="vertical-align: top; border-bottom: 1px solid gray; border-top: 1px solid gray;">
			<?php
			echo mp_ssv_td(mp_ssv_draggable_icon());
			echo mp_ssv_td(mp_ssv_text_input("Field Title", $this->id, $this->title));
			if (get_theme_support('mui')) {
				echo mp_ssv_td(mp_ssv_select("Field Type", $this->id, $this->type, array("Tab", "Header", "Input"), array('onchange="mp_ssv_type_changed(\'' . $this->id . '\')"')));
			} else {
				echo mp_ssv_td(mp_ssv_select("Field Type", $this->id, $this->type, array("Header", "Input"), array('onchange="mp_ssv_type_changed(\'' . $this->id . '\')"')));
			}
			echo $content;
			?>
		</tr>
		<?php
		return ob_get_clean();
	}
	
	protected function saveOptionRow(){
		
	}
}
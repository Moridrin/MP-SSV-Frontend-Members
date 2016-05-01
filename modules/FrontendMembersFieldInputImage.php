<?php

/**
 * Created by: Jeroen Berkvens
 * Date: 23-4-2016
 * Time: 16:10
 */
class FrontendMembersFieldInputImage extends FrontendMembersFieldInput
{
	public $required;
	public $preview;

	/**
	 * FrontendMembersFieldInputImage constructor.
	 *
	 * @param FrontendMembersFieldInput $field    is the parent field.
	 * @param bool                      $required is true if this is a required input field.
	 * @param bool                      $preview  is true if the already set image should be displayed as preview.
	 */
	protected function __construct($field, $required, $preview)
	{
		parent::__construct($field, $field->input_type, $field->name);
		$this->required = $required;
		$this->preview = $preview;
	}

	/**
	 * @param int    $index    is an index that specifies the display (/tab) order for the field.
	 * @param string $title    is the title of this component.
	 * @param string $name     is the name of the input field.
	 * @param bool   $required is true if this is a required input field.
	 * @param bool   $preview  is true if the already set image should be displayed as preview.
	 *
	 * @return FrontendMembersFieldInputImage
	 */
	public static function create($index, $title, $name, $required = false, $preview = true)
	{
		return new FrontendMembersFieldInputImage(parent::createInput($index, $title, 'image', $name), $required, $preview);
	}

	/**
	 * @return string row that can be added to the profile page options table.
	 */
	public function getOptionRow()
	{
		ob_start();
		echo mp_ssv_get_td(mp_ssv_get_text_input("Name", $this->id, $this->name));
		echo mp_ssv_get_td(mp_ssv_get_checkbox("Required", $this->id, $this->required));
		echo mp_ssv_get_td(mp_ssv_get_checkbox("Preview", $this->id, $this->preview));
		echo mp_ssv_get_td('<div class="' . $this->id . '_empty"></div>');
		$content = ob_get_clean();

		return parent::getOptionRowInput($content);
	}

	public function getHTML($frontend_member, $size = 150)
	{
		ob_start();
		$location = $frontend_member->getMeta($this->name);
		echo '<div class="mui-textfield">';
		if ($this->required == "yes" && $location == "") {
			echo '<input type="file" id="' . $this->id . '" name="' . $this->name . '" required/>';
		} else {
			echo '<input type="file" id="' . $this->id . '" name="' . $this->name . '" />';
		}
		if ($this->preview == "yes") {
			echo '<img src="' . $location . '" style="padding-top: 10px;" height="' . $size . '" width="' . $size . '">';
		}
		echo '</div>';

		return ob_get_clean();
	}

	public function save($remove = false)
	{
		parent::save($remove);
		global $wpdb;
		$table = FRONTEND_MEMBERS_FIELD_META_TABLE_NAME;
		$wpdb->replace(
			$table,
			array("field_id" => $this->id, "meta_key" => "required", "meta_value" => $this->required),
			array('%d', '%s', '%s')
		);
		$wpdb->replace(
			$table,
			array("field_id" => $this->id, "meta_key" => "preview", "meta_value" => $this->preview),
			array('%d', '%s', '%s')
		);
	}
}
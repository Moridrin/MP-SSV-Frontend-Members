<?php

/**
 * Created by: Jeroen Berkvens
 * Date: 23-4-2016
 * Time: 16:08
 */
class FrontendMembersFieldInputCustom extends FrontendMembersFieldInput
{

	public $required;
	public $display;
	public $placeholder;

	/**
	 * FrontendMembersFieldInputCustom constructor.
	 *
	 * @param FrontendMembersFieldInput $field       is the parent field.
	 * @param bool                      $required    is true if this is a required input field.
	 * @param string                    $display     is the way the input field is displayed (readonly, disabled or normal) default is normal.
	 * @param string                    $placeholder is the placeholder text that gives an example of what to enter.
	 */
	protected function __construct($field, $required, $display, $placeholder)
	{
		parent::__construct($field, $field->input_type, $field->name);
		$this->required = $required;
		$this->display = $display;
		$this->placeholder = $placeholder;
	}

	/**
	 * @param int    $index       is an index that specifies the display (/tab) order for the field.
	 * @param string $title       is the title of this component.
	 * @param string $input_type  is the input type of the input field.
	 * @param string $name        is the name of the input field.
	 * @param bool   $required    is true if this is a required input field.
	 * @param string $display     is the way the input field is displayed (readonly, disabled or normal) default is normal.
	 * @param string $placeholder is the placeholder text that gives an example of what to enter.
	 *
	 * @return FrontendMembersFieldInputCustom
	 */
	public static function create($index, $title, $input_type, $name, $required = false, $display = "normal", $placeholder = "")
	{
		return new FrontendMembersFieldInputCustom(parent::createInput($index, $title, $input_type, $name), $required, $display, $placeholder);
	}

	/**
	 * @return string row that can be added to the profile page options table.
	 */
	public function getOptionRow()
	{
		ob_start();
		echo mp_ssv_get_td(mp_ssv_get_text_input("Name", $this->id, $this->name));
		echo mp_ssv_get_td(mp_ssv_get_checkbox("Required", $this->id, $this->required));
		echo mp_ssv_get_td(mp_ssv_get_select("Display", $this->id, $this->display, array("Normal", "ReadOnly", "Disabled"), array(), true, $this->getMeta('input_type_custom')));
		echo mp_ssv_get_td(mp_ssv_get_text_input("Placeholder", $this->id, $this->placeholder));
		$content = ob_get_clean();

		return parent::getOptionRowInput($content);
	}

	public function getHTML()
	{
		if ($this->required == "yes") {
			return '<input type="' . $this->input_type . '" id="' . $this->id . '" name="' . $this->name . '" value="' . mp_ssv_get_user_meta($this->name) . '" placeholder="'.$this->placeholder.'" display="' . $this->display . '" required/>';
		} else {
			return '<input type="' . $this->input_type . '" id="' . $this->id . '" name="' . $this->name . '" value="' . mp_ssv_get_user_meta($this->name) . '" placeholder="'.$this->placeholder.'" display="' . $this->display . '" />';
		}
	}

	public function save()
	{
		parent::save();
		global $wpdb;
		$table = FRONTEND_MEMBERS_FIELD_META_TABLE_NAME;
		$wpdb->replace(
			$table,
			array("field_id" => $this->id, "meta_key" => "required", "meta_value" => $this->required),
			array('%d', '%s', '%s')
		);
		$wpdb->replace(
			$table,
			array("field_id" => $this->id, "meta_key" => "display", "meta_value" => $this->display),
			array('%d', '%s', '%s')
		);
		$wpdb->replace(
			$table,
			array("field_id" => $this->id, "meta_key" => "placeholder", "meta_value" => $this->placeholder),
			array('%d', '%s', '%s')
		);
	}
}
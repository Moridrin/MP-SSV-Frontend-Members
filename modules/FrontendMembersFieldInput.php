<?php
/**
 * Created by: Jeroen Berkvens
 * Date: 23-4-2016
 * Time: 16:01
 */

include "FrontendMembersFieldInputCustom.php";
include "FrontendMembersFieldInputImage.php";
include "FrontendMembersFieldInputRoleCheckbox.php";
include "FrontendMembersFieldInputRoleSelect.php";
include "FrontendMembersFieldInputText.php";
include "FrontendMembersFieldInputTextCheckbox.php";
include "FrontendMembersFieldInputTextSelect.php";

class FrontendMembersFieldInput extends FrontendMembersField
{
	protected $input_type;
	public $name;

	/**
	 * FrontendMembersFieldInput constructor.
	 *
	 * @param FrontendMembersField $field      is the parent field.
	 * @param int                  $input_type is the type of input field.
	 * @param string               $name       is the name of the input field.
	 */
	protected function __construct($field, $input_type, $name)
	{
		parent::__construct($field->id, $field->index, $field->type, $field->title);
		$this->input_type = $input_type;
		$this->name = $name;
	}

	/**
	 * @param int    $index      is an index that specifies the display (/tab) order for the field.
	 * @param string $title      is the title of this component.
	 * @param string $input_type is the input type of the input field.
	 * @param string $name       is the name of the input field.
	 *
	 * @return FrontendMembersFieldInput
	 */
	protected static function createInput($index, $title, $input_type, $name)
	{
		$field = parent::createField($index, $title, 'input');
		return new FrontendMembersFieldInput($field, $input_type, $name);
	}

	/**
	 * @param string $content is a string of all input columns.
	 *
	 * @return string row that can be added to the profile page options table.
	 */
	protected function getOptionRowInput($content)
	{
		ob_start();
		echo mp_ssv_td(mp_ssv_select("Input Type", $this->id, $this->input_type, array("Text", "Text Group Select", "Role Group Select", "Text Checkbox", "Role Checkbox", "Image"), array('onchange="mp_ssv_input_type_changed(\'' . $this->id . '\')"'), true));
		echo $content;
		$content = ob_get_clean();

		return parent::getOptionRowField($content);
	}
}
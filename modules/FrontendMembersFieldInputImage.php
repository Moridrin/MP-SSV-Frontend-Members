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
		echo mp_ssv_td(mp_ssv_text_input("Name", $this->id, $this->name));
		echo mp_ssv_td(mp_ssv_checkbox("Required", $this->id, $this->required));
		echo mp_ssv_td(mp_ssv_checkbox("Preview", $this->id, $this->preview));
		echo mp_ssv_td('<div class="' . $this->id . '_empty"></div>');
		$content = ob_get_clean();
		return parent::getOptionRowInput($content);
	}
}
<?php
/**
 * Created by: Jeroen Berkvens
 * Date: 23-4-2016
 * Time: 15:42
 */

class FrontendMembersFieldTab extends FrontendMembersField
{

	/**
	 * FrontendMembersFieldTab constructor.
	 *
	 * @param FrontendMembersField $field is the parent field.
	 */
	protected function __construct($field)
	{
		parent::__construct($field->id, $field->index, $field->type, $field->title);
	}

	/**
	 * @param int    $index is an index that specifies the display (/tab) order for the field.
	 * @param string $title is the title of this component.
	 *
	 * @return FrontendMembersFieldTab the newly created component.
	 */
	public static function create($index, $title)
	{
		return new FrontendMembersFieldTab(parent::createField($index, $title, 'tab'));
	}

	/**
	 * @return string row that can be added to the profile page options table.
	 */
	public function getOptionRow()
	{
		ob_start();
		echo mp_ssv_td('<div class="' . $this->id . '_empty"></div>');
		echo mp_ssv_td('<div class="' . $this->id . '_empty"></div>');
		echo mp_ssv_td('<div class="' . $this->id . '_empty"></div>');
		echo mp_ssv_td('<div class="' . $this->id . '_empty"></div>');
		echo mp_ssv_td('<div class="' . $this->id . '_empty"></div>');
		$content = ob_get_clean();
		return parent::getOptionRowField($content);
	}
}
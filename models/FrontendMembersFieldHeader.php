<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Created by: Jeroen Berkvens
 * Date: 23-4-2016
 * Time: 16:01
 */
class FrontendMembersFieldHeader extends FrontendMembersField
{

    /**
     * FrontendMembersFieldHeader constructor.
     *
     * @param FrontendMembersField $field is the parent field.
     */
    protected function __construct($field)
    {
        parent::__construct($field->id, $field->index, $field->type, $field->title, $field->registration_page, $field->class, $field->style);
    }

    /**
     * @param int    $index is an index that specifies the display (/tab) order for the field.
     * @param string $title is the title of this component.
     *
     * @return FrontendMembersFieldHeader the newly created component.
     */
    public static function create($index, $title)
    {
        return new FrontendMembersFieldHeader(parent::createField($index, $title, 'header'));
    }

    /**
     * @return string row that can be added to the profile page options table.
     */
    public function getOptionRow()
    {
        ob_start();
        echo ssv_get_td('<div class="' . $this->id . '_empty"></div>');
        echo ssv_get_td('<div class="' . $this->id . '_empty"></div>');
        echo ssv_get_td('<div class="' . $this->id . '_empty"></div>');
        echo ssv_get_td('<div class="' . $this->id . '_empty"></div>');
        if (get_option('ssv_frontend_members_view_advanced_profile_page', 'false') == 'true') {
            echo ssv_get_td('<div class="' . $this->id . '_empty"></div>');
        }
        $content = ob_get_clean();

        return parent::getOptionRowField($content);
    }

    public function getHTML()
    {
        ob_start();
        ?><h1 class="<?php echo $this->class; ?>" style="<?php echo $this->style; ?>"><?php echo $this->title; ?></h1><?php
        return ob_get_clean();
    }

    public function save($remove = false)
    {
        parent::save($remove);
    }
}
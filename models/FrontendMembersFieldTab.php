<?php
if (!defined('ABSPATH')) {
    exit;
}

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
        parent::__construct($field->id, $field->index, $field->type, $field->title, $field->registration_page, $field->class, $field->style);
    }

    /**
     * @param array $database_fields the array returned by wpdb.
     *
     * @return FrontendMembersFieldTab
     */
    public static function fromDatabaseFields($database_fields)
    {
        return new FrontendMembersFieldTab(parent::fromDatabaseFields($database_fields));
    }

    /**
     * @return string a row that can be added to the profile page options table.
     */
    public function getOptionRow()
    {
        ob_start();
        echo ssv_get_td('<div class="' . $this->id . '_empty"></div>');
        echo ssv_get_td('<div class="' . $this->id . '_empty"></div>');
        echo ssv_get_td('<div class="' . $this->id . '_empty"></div>');
        if (get_option('ssv_frontend_members_view_display__preview_column', 'true') == 'true') {
            echo ssv_get_td('<div class="' . $this->id . '_empty"></div>');
        }
        if (get_option('ssv_frontend_members_view_default_column', 'true') == 'true') {
            echo ssv_get_td('<div class="' . $this->id . '_empty"></div>');
        }
        if (get_option('ssv_frontend_members_view_placeholder_column', 'true') == 'true') {
            echo ssv_get_td('<div class="' . $this->id . '_empty"></div>');
        }
        $content = ob_get_clean();

        return parent::getOptionRowField($content, get_theme_support('mui'));
    }

    public function getTabButton($active = false)
    {
        if ($active) {
            return '<li class="mui--is-active"><a class="mui-btn mui-btn--flat" data-mui-toggle="tab" data-mui-controls="pane-' . $this->id . '"><p class="' . $this->class . '" style="' . $this->style . '">' . $this->title . '</p></a></li>';
        } else {
            return '<li><a class="mui-btn mui-btn--flat" data-mui-toggle="tab" data-mui-controls="pane-' . $this->id . '"><p class="' . $this->class . '" style="' . $this->style . '">' . $this->title . '</p></a></li>';
        }
    }

    public function getDivHeader($active = false)
    {
        if ($active) {
            return '<div class="mui-tabs__pane mui--is-active" id="pane-' . $this->id . '">';
        } else {
            return '<div class="mui-tabs__pane" id="pane-' . $this->id . '">';
        }
    }

    public function save($remove = false)
    {
        parent::save($remove);
    }
}
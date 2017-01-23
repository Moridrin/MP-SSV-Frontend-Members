<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 21-1-17
 * Time: 7:38
 */
if (SSV_General::isValidPOST(SSV_Users::ADMIN_REFERER_OPTIONS)) {
    if (isset($_POST['reset'])) {
        SSV_Users::CLEAN_INSTALL();
//            SSV_Users::resetOptions();
    } else {
        update_option(SSV_Users::OPTION_DEFAULT_MEMBER_ROLE, SSV_General::sanitize($_POST['default_member_role']));
        update_option(SSV_Users::OPTION_BOARD_ROLE, SSV_General::sanitize($_POST['board_role']));
        //Users Page Columns
        update_option(SSV_Users::OPTION_CUSTOM_USER_FILTER_LOCATION, SSV_General::sanitize($_POST['custom_users_filters']));
        update_option(SSV_Users::OPTION_USERS_PAGE_MAIN_COLUMN, SSV_General::sanitize($_POST['users_page_main_column']));
        update_option(SSV_Users::OPTION_USER_COLUMNS, json_encode(isset($_POST['user_columns']) ? $_POST['user_columns'] : ''));
        update_option(SSV_Users::OPTION_CUSTOM_USER_FILTERS, json_encode(isset($_POST['custom_user_filters']) ? $_POST['custom_user_filters'] : ''));
        //Email
//        update_option(SSV_Users::OPTION_MEMBER_ADMIN, $_POST['default_registration_status']);
//        update_option(SSV_Users::OPTION_NEW_MEMBER_REGISTRATION_EMAIL, $_POST['default_registration_status']);
//        update_option(SSV_Users::OPTION_MEMBER_ROLE_CHANGED_EMAIL, $_POST['default_registration_status']);
    }
}
?>
<form method="post" action="#">
    <table class="form-table">
        <tr>
            <th scope="row">Default Member Role</th>
            <td>
                <select name="default_member_role" title="Default Member Role">
                    <?php wp_dropdown_roles(get_option(SSV_Users::OPTION_DEFAULT_MEMBER_ROLE)); ?>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row">Board Role</th>
            <td>
                <select name="board_role" title="Board Role">
                    <?php wp_dropdown_roles(get_option(SSV_Users::OPTION_BOARD_ROLE)); ?>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row">Custom Users Filters</th>
            <td>
                <select name="custom_users_filters" title="Board Role">
                    <option value="hide" <?= get_option(SSV_Users::OPTION_CUSTOM_USER_FILTER_LOCATION, 'under') == 'hide' ? 'selected' : '' ?>>Hide</option>
                    <option value="replace" <?= get_option(SSV_Users::OPTION_CUSTOM_USER_FILTER_LOCATION, 'under') == 'replace' ? 'selected' : '' ?>>Replace User Role Links</option>
                    <option value="under" <?= get_option(SSV_Users::OPTION_CUSTOM_USER_FILTER_LOCATION, 'under') == 'under' ? 'selected' : '' ?>>Under User Role Links</option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row">Main Column</th>
            <td>
                <select name="users_page_main_column" title="Main Column">
                    <option value="plugin_default" <?= get_option(SSV_Users::OPTION_USERS_PAGE_MAIN_COLUMN) == 'plugin_default' ? 'selected' : '' ?>>Plugin Default</option>
                    <option value="wordpress_default"<?= get_option(SSV_Users::OPTION_USERS_PAGE_MAIN_COLUMN) == 'wordpress_default' ? 'selected' : '' ?>>WordPress Default</option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row">Columns to Display</th>
            <td>
                <?php
                $selected   = json_decode(get_option(SSV_Users::OPTION_USER_COLUMNS));
                $selected   = $selected ?: array();
                $fieldNames = SSV_Users::getInputFieldNames();
                ?>
                <select size="<?= count($fieldNames) + 3 ?>" name="user_columns[]" multiple title="Columns to Display">
                    <?php foreach ($fieldNames as $fieldName): ?>
                        <option value="<?= $fieldName ?>" <?= in_array($fieldName, $selected) ? 'selected' : '' ?>><?= $fieldName ?></option>
                    <?php endforeach; ?>
                    <option value="blank" disabled>--- WP Defaults ---</option>
                    <option value="wp_Role" <?= in_array('wp_Role', $selected) ? 'selected' : '' ?>>Role</option>
                    <option value="wp_Posts" <?= in_array('wp_Posts', $selected) ? 'selected' : '' ?>>Posts</option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row">Filters</th>
            <td>
                <?php
                $selected   = json_decode(get_option(SSV_Users::OPTION_CUSTOM_USER_FILTERS));
                $selected   = $selected ?: array();
                $fieldNames = SSV_Users::getInputFieldNames();
                ?>
                <select size="<?= count($fieldNames) ?>" name="custom_user_filters[]" multiple title="Filters">
                    <?php foreach ($fieldNames as $fieldName): ?>
                        <option value="<?= $fieldName ?>" <?= in_array($fieldName, $selected) ? 'selected' : '' ?>><?= $fieldName ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
    </table>
    <?php SSV_General::formSecurityFields(SSV_Users::ADMIN_REFERER_OPTIONS); ?>
</form>

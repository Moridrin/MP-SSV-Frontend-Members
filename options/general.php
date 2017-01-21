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
        update_option(SSV_Users::OPTION_CUSTOM_USERS_FILTER, SSV_General::sanitize($_POST['custom_users_filters']));
//        update_option(SSV_Users::OPTION_MAIN_COLUMN, $_POST['default_registration_status']);
//        update_option(SSV_Users::OPTION_USER_COLUMNS, $_POST['default_registration_status']);
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
                    <option value="hide" <?= get_option(SSV_Users::OPTION_CUSTOM_USERS_FILTER, 'under') == 'hide' ? 'selected' : '' ?>>Hide</option>
                    <option value="replace" <?= get_option(SSV_Users::OPTION_CUSTOM_USERS_FILTER, 'under') == 'replace' ? 'selected' : '' ?>>Replace User Role Links</option>
                    <option value="under" <?= get_option(SSV_Users::OPTION_CUSTOM_USERS_FILTER, 'under') == 'under' ? 'selected' : '' ?>>Under User Role Links</option>
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
                $selected   = json_decode(get_option('ssv_frontend_members_user_columns'));
                $selected   = $selected ?: array();
                $fieldNames = SSV_Users::getAllFieldNames();
                ?>
                <select size="<?= count($fieldNames) + 3 ?>" name="ssv_frontend_members_user_columns[]" multiple title="Columns to Display">
                    <?php
                    foreach ($fieldNames as $fieldName) {
                        echo '<option value="' . $fieldName . '" ';
                        if (in_array($fieldName, $selected)) {
                            echo 'selected';
                        }
                        echo '>' . $fieldName . '</option>';
                    }
                    echo '<option value="blank" disabled>--- WP Defaults ---</option>';
                    echo '<option value="wp_Role" ';
                    if (in_array('wp_Role', $selected)) {
                        echo 'selected';
                    }
                    echo '>Role</option>';
                    echo '<option value="wp_Posts" ';
                    if (in_array('wp_Posts', $selected)) {
                        echo 'selected';
                    }
                    echo '>Posts</option>';
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row">Filters</th>
            <td>
                <?php
                $selected   = json_decode(get_option('ssv_frontend_members_user_filters'));
                $selected   = $selected ?: array();
                $fieldNames = FrontendMembersField::getAllFieldNames();
                ?>
                <select size="<?= count($fieldNames) ?>" name="ssv_frontend_members_user_filters[]" multiple title="Filters">
                    <?php
                    foreach ($fieldNames as $fieldName) {
                        echo '<option value="' . $fieldName . '" ';
                        if (in_array($fieldName, $selected)) {
                            echo 'selected';
                        }
                        echo '>' . $fieldName . '</option>';
                    }
                    ?>
                </select>
            </td>
        </tr>
    </table>
    <?php SSV_General::formSecurityFields(SSV_Users::ADMIN_REFERER_OPTIONS); ?>
</form>

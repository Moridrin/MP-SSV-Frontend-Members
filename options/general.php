<?php
namespace mp_ssv_users\options;
use mp_ssv_general\SSV_General;
use mp_ssv_users\SSV_Users;

if (!defined('ABSPATH')) {
    exit;
}

$selected   = json_decode(get_option(SSV_Users::OPTION_USER_COLUMNS));
$selected   = $selected ?: array();
$fieldNames = SSV_Users::getInputFieldNames();
$fieldNames[] = 'wp_Role';
$fieldNames[] = 'wp_Posts';

if (SSV_General::isValidPOST(SSV_Users::ADMIN_REFERER_OPTIONS)) {
    if (isset($_POST['reset'])) {
        SSV_Users::resetOptions();
    } else {
        update_option(SSV_Users::OPTION_USERS_PAGE_MAIN_COLUMN, SSV_General::sanitize($_POST['users_page_main_column'], array('plugin_default', 'wordpress_default')));
        $userColumns = isset($_POST['user_columns']) ? SSV_General::sanitize($_POST['user_columns'], $fieldNames) : $fieldNames;
        $userColumns = empty($userColumns) ? array() : explode(',', $userColumns);
        update_option(SSV_Users::OPTION_USER_COLUMNS, json_encode($userColumns));
    }
}
?>
<form method="post" action="#">
    <table class="form-table">
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
                echo SSV_General::getListSelect('user_columns', $fieldNames, $selected);
                ?>
            </td>
        </tr>
    </table>
    <?= SSV_General::getFormSecurityFields(SSV_Users::ADMIN_REFERER_OPTIONS); ?>
</form>

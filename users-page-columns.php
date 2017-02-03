<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 3-2-17
 * Time: 6:29
 */

function mp_ssv_users_custom_user_columns($column_headers)
{
    unset($column_headers);
    $column_headers['cb'] = '<input type="checkbox" />';
    if (get_option(SSV_Users::OPTION_USERS_PAGE_MAIN_COLUMN) == 'wordpress_default') {
        $column_headers['username'] = 'Username';
    } else {
        $url = $_SERVER['REQUEST_URI'];
        if (empty($_GET)) {
            $url .= '?orderby=name';
        } elseif (!isset($_GET['orderby'])) {
            $url .= '&orderby=name';
        } elseif (!isset($_GET['order'])) {
            $url .= '&order=DESC';
        } elseif ($_GET['order'] == 'DESC') {
            $url .= '&order=ASC';
        } else {
            $url .= '&order=DESC';
        }
        $column_headers['ssv_member'] = '<a href="' . $url . '">Member</a>';
    }
    $selected_columns = json_decode(get_option(SSV_Users::OPTION_USER_COLUMNS));
    $selected_columns = $selected_columns ?: array();
    foreach ($selected_columns as $column) {
        if (starts_with($column, 'wp_')) {
            $column                              = str_replace('wp_', '', $column);
            $column_headers[strtolower($column)] = $column;
        } else {
            $column_headers['ssv_' . $column] = $column;
        }
    }
    return $column_headers;
}

add_action('manage_users_columns', 'mp_ssv_users_custom_user_columns');

function mp_ssv_users_custom_user_column_values($val, $column_name, $user_id)
{
    $user = User::getByID($user_id);
    if ($column_name == 'ssv_member') {
        $username_block = '';
        $username_block .= get_avatar($user->ID, 32, '', '', array('extra_attr' => 'style="float: left; margin-right: 5px; margin-top: 1px;"'));
        $username_block .= '<strong>' . $user->getProfileLink('_blank') . '</strong><br/>';
        $directDebitPDF  = $user->getProfileURL() . '&view=directDebitPDF';
        $editURL         = 'user-edit.php?user_id=' . $user->ID . '&wp_http_referer=%2Fwp-admin%2Fusers.php';
        $capebilitiesURL = 'users.php?page=users-user-role-editor.php&object=user&user_id=' . $user->ID;
        $username_block .= '<div class="row-actions"><span class="direct_debit_pdf"><a href="' . esc_url($directDebitPDF) . '" target="_blank">PDF</a> | </span><span class="edit"><a href="' . esc_url($editURL) . '">Edit</a> | </span><span class="capabilities"><a href="' . esc_url($capebilitiesURL) . '">Capabilities</a></span></div>';
        return $username_block;
    } elseif (starts_with($column_name, 'ssv_')) {
        return $user->getMeta(str_replace('ssv_', '', $column_name));
    }
    return $val;
}

add_filter('manage_users_custom_column', 'mp_ssv_users_custom_user_column_values', 10, 3);

function mp_ssv_users_custom_sortable_user_columns($columns) {
    $selected_columns = json_decode(get_option(SSV_Users::OPTION_USER_COLUMNS));
    $selected_columns[] = 'ssv_member';
    $selected_columns = $selected_columns ?: array();
    foreach ($selected_columns as $column) {
        if (starts_with($column, 'wp_')) {
            $column                              = str_replace('wp_', '', $column);
            $columns[strtolower($column)] = $column;
        } else {
            $columns['ssv_' . $column] = $column;
        }
    }
    return $columns;
}

add_filter('manage_users_sortable_columns', 'mp_ssv_users_custom_sortable_user_columns');

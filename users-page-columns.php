<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 3-2-17
 * Time: 6:29
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param      $url
 * @param User $user
 *
 * @return mixed
 */
function mp_ssv_users_custom_profile_url($url, $user)
{
    /** @var WP_Post[] $pages */
    $pages       = SSV_Users::getPagesWithTag(SSV_Users::TAG_PROFILE_FIELDS);
    $wildcardURL = null;
    foreach ($pages as $page) {
        $pageRole = get_post_meta($page->ID, SSV_Users::PAGE_ROLE_META, true);
        if ($pageRole == $user->roles[0]) {
            return get_permalink($page) . '?member=' . $user->ID;
        } elseif ($pageRole == -1) {
            $wildcardURL = get_permalink($page) . '?member=' . $user->ID;
        }
    }
    return $wildcardURL ?: $url;
}

add_filter(SSV_General::HOOK_USER_PROFILE_URL, 'mp_ssv_users_custom_profile_url', 10, 2);

function mp_ssv_users_custom_user_columns($column_headers)
{
    unset($column_headers);
    $column_headers['cb'] = '<input type="checkbox" />';
    if (get_option(SSV_Users::OPTION_USERS_PAGE_MAIN_COLUMN) == 'wordpress_default') {
        $column_headers['username'] = 'Username';
    } else {
        $column_headers['ssv_display_name'] = 'Member';
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
    if ($column_name == 'ssv_display_name') {
        $username_block = '';
        $username_block .= get_avatar($user->ID, 32, '', '', array('extra_attr' => 'style="float: left; margin-right: 5px; margin-top: 1px;"'));
        $username_block .= '<strong>' . $user->getProfileLink('_blank') . '</strong><br/>';
        $directDebitPDF  = $user->getProfileURL() . '&view=directDebitPDF';
        $editURL         = get_edit_user_link($user->ID);
        $capebilitiesURL = 'users.php?page=users-user-role-editor.php&object=user&user_id=' . $user->ID;
        $username_block .= '<div class="row-actions">';
//        $username_block .= '<span class="direct_debit_pdf"><a href="' . esc_url($directDebitPDF) . '" target="_blank">PDF</a> | </span>';
        $username_block .= '<span class="edit"><a href="' . esc_url($editURL) . '">Edit</a></span>';
        $username_block .= '</div>';
        return $username_block;
    } elseif (starts_with($column_name, 'ssv_')) {
        return $user->getMeta(str_replace('ssv_', '', $column_name));
    }
    return $val;
}

add_filter('manage_users_custom_column', 'mp_ssv_users_custom_user_column_values', 10, 3);

function mp_ssv_users_custom_sortable_user_columns($columns)
{
    $selected_columns   = json_decode(get_option(SSV_Users::OPTION_USER_COLUMNS));
    $selected_columns[] = 'display_name';
    $selected_columns   = $selected_columns ?: array();
    foreach ($selected_columns as $column) {
        if (starts_with($column, 'wp_')) {
            $column                       = str_replace('wp_', '', $column);
            $columns[strtolower($column)] = $column;
        } else {
            $columns['ssv_' . $column] = $column;
        }
    }
    return $columns;
}

add_filter('manage_users_sortable_columns', 'mp_ssv_users_custom_sortable_user_columns');

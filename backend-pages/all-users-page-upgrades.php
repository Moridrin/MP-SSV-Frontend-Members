<?php
if (!defined('ABSPATH')) {
    exit;
}

function ssv_add_ssv_frontend_members_users_page()
{
    add_submenu_page('users.php', 'All Users', 'All Users', 'edit_users', __FILE__, 'ssv_frontend_members_users_page');
}

function ssv_frontend_members_users_page()
{
    $active_tab = "general";
    if (isset($_GET['tab'])) {
        $active_tab = $_GET['tab'];
    }
    ?>
    <div class="wrap">
        <h1>Filters</h1>

        <h1>Frontend Members</h1>
        <h2 class="nav-tab-wrapper">
            <?php foreach (array('Member') as $member_type): ?>
                <a href="?page=<?php echo __FILE__; ?>&tab=<?php echo strtolower($member_type); ?>" class="nav-tab <?php if ($active_tab == strtolower($member_type)) {
                    echo "nav-tab-active";
                } ?>"><?php echo $member_type; ?></a>
            <?php endforeach; ?>
        </h2>
    </div>
    <?php
}

add_action('admin_menu', 'ssv_add_ssv_frontend_members_users_page');

function ssv_custom_user_column_values($val, $column_name, $user_id)
{
    $frontendMember = FrontendMember::get_by_id($user_id);
    if ($column_name == 'ssv_member') {
        $username_block = '';
        $username_block .= '<img style="float: left; margin-right: 10px; margin-top: 1px;" class="avatar avatar-32 photo" src="' . esc_url($frontendMember->getMeta('profile_picture')) . '" height="32" width="32"/>';
        $username_block .= '<strong>' . $frontendMember->getProfileLink('_blank') . '</strong><br/>';
        $directDebitPDF  = $frontendMember->getProfileURL() . '&view=directDebitPDF';
        $editURL         = 'user-edit.php?user_id=' . $frontendMember->ID . '&wp_http_referer=%2Fwp-admin%2Fusers.php';
        $capebilitiesURL = 'users.php?page=users-user-role-editor.php&object=user&user_id=' . $frontendMember->ID;
        $username_block .= '<div class="row-actions"><span class="direct_debit_pdf"><a href="' . esc_url($directDebitPDF) . '" target="_blank">PDF</a> | </span><span class="edit"><a href="' . esc_url($editURL) . '">Edit</a> | </span><span class="capabilities"><a href="' . esc_url($capebilitiesURL) . '">Capabilities</a></span></div>';
        return $username_block;
    } elseif (ssv_starts_with($column_name, 'ssv_')) {
        return $frontendMember->getMeta(str_replace('ssv_', '', $column_name));
    }
    return $val;
}

add_filter('manage_users_custom_column', 'ssv_custom_user_column_values', 10, 3);

function ssv_custom_user_columns($column_headers)
{
    unset($column_headers);
    $column_headers['cb'] = '<input type="checkbox" />';
    global $wpdb;
    if (get_option('ssv_frontend_members_main_column') == 'wordpress_default') {
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
    $selected_columns = json_decode(get_option('ssv_frontend_members_user_columns'));
    $selected_columns = $selected_columns ?: array();
    foreach ($selected_columns as $column) {
        $sql   = 'SELECT field_id FROM ' . FRONTEND_MEMBERS_FIELD_META_TABLE_NAME . ' WHERE meta_key = "name" AND meta_value = "' . $column . '"';
        $sql   = 'SELECT field_title FROM ' . FRONTEND_MEMBERS_FIELDS_TABLE_NAME . ' WHERE id = (' . $sql . ')';
        $title = $wpdb->get_var($sql);
        if (ssv_starts_with($column, 'wp_')) {
            $column                              = str_replace('wp_', '', $column);
            $column_headers[strtolower($column)] = $column;
        } else {
            $column_headers['ssv_' . $column] = $title;
        }
    }
    return $column_headers;
}

add_action('manage_users_columns', 'ssv_custom_user_columns');

function add_course_section_filter()
{
    $fields = FrontendMembersField::getAll(array('registration_page' => 'no', 'field_type' => 'input'));
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        foreach ($fields as $field) {
            /** @var FrontendMembersFieldInput $field */
            if (isset($_POST['clear_filters']) || !isset($_POST['filter_' . $field->name]) || empty($_POST['filter_' . $field->name])) {
                unset($_SESSION['filter_' . $field->name]);
            } else {
                $_SESSION['filter_' . $field->name] = $_POST['filter_' . $field->name];
            }
        }
    }
    $filters  = '<h1>Filters</h1>';
    $selected = json_decode(get_option('ssv_frontend_members_user_filters'));
    $selected = $selected ?: array();
    foreach ($fields as $field) {
        /** @var FrontendMembersFieldInput $field */
        if (in_array($field->name, $selected)) {
            $filters .= '<div style="display: inline-block; margin-right: 10px;">';
            $filters .= $field->getFilter();
            $filters .= '</div>';
        }
    }
    $filters .= '<br/>';
    $filters .= '<button type="submit" value="submit" class="button">Filter</button>';
    ?>
    <script>
        window.onload = function () {
            jQuery(document).ready(function ($) {
                var old_filter_area = $('.subsubsub');
                old_filter_area.after('<form name="filter_form" method="post"><div id="filter_area"></div></form>');
                old_filter_area.remove();
                var filter_area = $('#filter_area');
                filter_area.html('<?php echo $filters; ?>');
            });
        };
    </script>
    <?php
//    die('<br/><br/>bla');
}

add_action('admin_init', 'add_course_section_filter');

function filter_users_by_course_section($query)
{
//    die('<br/><br/>test');
    global $wpdb;
    if (strpos($_SERVER['REQUEST_URI'], 'users.php') !== false) {
        $filters = array();
        $fields = FrontendMembersField::getAll(array('registration_page' => 'no', 'field_type' => 'input'));
        foreach ($fields as $field) {
            /** @var FrontendMembersFieldInput $field */
            if (isset($_SESSION['filter_' . $field->name])) {
                $filters[$field->name] = $_SESSION['filter_' . $field->name];
            }
        }
        foreach ($filters as $filter => $value) {
            $table_alias = $filter . 'meta';
            $query->query_from .= " JOIN {$wpdb->usermeta} {$table_alias} ON {$table_alias}.user_id = {$wpdb->users}.ID AND {$table_alias}.meta_key = '{$filter}'";
            $query->query_where .= " AND {$table_alias}.meta_value LIKE '%{$value}%'";
        }
    }
}

add_filter('pre_user_query', 'filter_users_by_course_section');

//
//function add_course_section_filter()
//{
//    if (isset($_GET['ssv_meta_key']) && isset($_GET['ssv_meta_value'])) {
//        $meta_key = $_GET['ssv_meta_key'];
//        $meta_value = $_GET['ssv_meta_value'];
//    } else {
//        $meta_key = '';
//        $meta_value = '';
//    }
//    ?>
    <!--    <input type="text" name="ssv_meta_key" value="--><?php //echo $meta_key; ?><!--"/>-->
    <!--    <input type="text" name="ssv_meta_value" value="--><?php //echo $meta_value; ?><!--"/>-->
    <!--    --><?php
//    echo '<input type="submit" class="button" value="Filter">';
//}
//
//add_action('restrict_manage_users', 'add_course_section_filter');
//
//function filter_users_by_course_section($query)
//{
//    global $pagenow;
//
//    if (is_admin()
//        && 'users.php' == $pagenow
//        && isset($_GET['ssv_meta_key'])
//        && isset($_GET['ssv_meta_value'])
//    ) {
//        $meta_key    = $_GET['ssv_meta_key'];
//        $meta_value    = $_GET['ssv_meta_value'];
//        $meta_query = array(
//            array(
//                'key'   => $meta_key,
//                'value' => $meta_value,
//            ),
//        );
//        $query->set('meta_key', $meta_key);
//        $query->set('meta_query', $meta_query);
//    }
//}
//
//add_filter('pre_get_users', 'filter_users_by_course_section');

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

function add_course_section_filter()
{
    $meta_key   = isset($_SESSION['meta_key']) ? $_SESSION['meta_key'] : '';
    $meta_value = isset($_SESSION['meta_value']) ? $_SESSION['meta_value'] : '';
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['clear_filters']) || !isset($_POST['meta_value']) || empty($_POST['meta_value'])) {
            unset($_SESSION['meta_key']);
            unset($_SESSION['meta_value']);
            $meta_key = '';
            $meta_value = '';
        } else {
            $_SESSION['meta_key'] = $meta_key = $_POST['meta_key'];
            $_SESSION['meta_value'] = $meta_value = $_POST['meta_value'];
        }
    }
    $options = array('' => 'Select Key');
    $fields = FrontendMembersField::getAll(array('registration_page' => 'no', 'field_type' => 'input'));
    foreach ($fields as $field) {
        /** @var FrontendMembersFieldInput $field */
        $options[$field->name] = $field->title;
    }
    $filters = ssv_get_td(ssv_get_select('Meta Key', null, $meta_key, $options, array(), false, null, false));
    $filters .= ssv_get_td('<input type="text" id="meta_value" name="meta_value" value="'.$meta_value.'">');
    $filters .= ssv_get_td('<button type="submit" name="submit">Filter</button>');
    $filters .= ssv_get_td('<button type="submit" name="clear_filters">Clear Filters</button>');
    ?>
    <script>
        window.onload = function () {
            jQuery(document).ready(function ($) {
                var old_filter_area = $('.subsubsub');
                old_filter_area.after('<form name="filter_form" method="post"><table id="filter_area"></table></form>');
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
    if (strpos($_SERVER['REQUEST_URI'], 'users.php') !== false && isset($_SESSION["meta_value"])) {
        $filters = array($_SESSION["meta_key"] => $_SESSION["meta_value"]);
        foreach ($filters as $filter => $value) {
            $table_alias = $filter . 'meta';
            $query->query_from .= " JOIN {$wpdb->usermeta} {$table_alias} ON {$table_alias}.user_id = {$wpdb->users}.ID AND {$table_alias}.meta_key = '{$filter}'";
            $query->query_where = "WHERE {$table_alias}.meta_value LIKE '%{$value}%'";
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

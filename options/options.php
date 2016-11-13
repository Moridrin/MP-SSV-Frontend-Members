<?php
if (!defined('ABSPATH')) {
    exit;
}
function ssv_add_ssv_frontend_members_options()
{
    add_submenu_page('ssv_settings', 'Frontend Members Options', 'Frontend Members', 'manage_options', __FILE__, 'ssv_frontend_members_settings_page');
}

function ssv_frontend_members_settings_page()
{
    $active_tab = "general";
    if (isset($_GET['tab'])) {
        $active_tab = $_GET['tab'];
    }
    ?>
    <div class="wrap">
        <h1>Frontend Members Options</h1>
        <h2 class="nav-tab-wrapper">
            <a href="?page=<?php echo __FILE__; ?>&tab=general" class="nav-tab <?php if ($active_tab == "general") {
                echo "nav-tab-active";
            } ?>">General</a>
            <a href="?page=<?php echo __FILE__; ?>&tab=profile_page" class="nav-tab <?php if ($active_tab == "profile_page") {
                echo "nav-tab-active";
            } ?>">Profile Page</a>
            <?php if (get_option('ssv_frontend_members_custom_register_page', 'false') == 'true'): ?>
                <a href="?page=<?php echo __FILE__; ?>&tab=register_page" class="nav-tab <?php if ($active_tab == "register_page") {
                    echo "nav-tab-active";
                } ?>">Register Page</a>
            <?php endif; ?>
            <a href="?page=<?php echo __FILE__; ?>&tab=users_page_columns" class="nav-tab <?php if ($active_tab == "users_page_columns") {
                echo "nav-tab-active";
            } ?>">Users Page Columns</a>
            <a href="?page=<?php echo __FILE__; ?>&tab=email" class="nav-tab <?php if ($active_tab == "email") {
                echo "nav-tab-active";
            } ?>">Email</a>
            <a href="http://studentensurvival.com/ssv/ssv-frontend-members/" target="_blank" class="nav-tab">Help <img src="<?php echo plugin_dir_url(__DIR__); ?>general/images/link-new-tab.png" width="14px"
                                                                                                                       style="vertical-align:middle"></a>
        </h2>
        <?php
        switch ($active_tab) {
            case "general":
                require_once "general-tab.php";
                break;
            case "profile_page":
            case "register_page":
                require_once "profile-page-tab.php";
                break;
            case "users_page_columns":
                require_once "users-page-columns.php";
                break;
            case "email":
                require_once "email-tab.php";
                break;
        }
        ?>
    </div>
    <?php
}

add_action('admin_menu', 'ssv_add_ssv_frontend_members_options');
?>
